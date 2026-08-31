<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     *
     * An Owner can't just vanish: if teammates still exist on the
     * business, they'd be left with no one holding owner-only permissions
     * (staff, settings, payments — see RolesAndPermissionsSeeder). Those
     * cases are blocked, pointing at Transfer Ownership on the Staff page
     * instead. A sole Owner with no other staff has nothing left to hand
     * off to, so deletion proceeds — but the business itself is closed,
     * not deleted, so its historical orders/payments (and the platform's
     * own already-recognized GMV/commission on them) survive for
     * admin/accounting purposes. See the businesses.status migration for
     * the full reasoning.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();
        $business = $user->business;

        if ($business && $user->hasRole('Owner')) {
            $remainingStaff = $business->users()->where('id', '!=', $user->id)->count();

            if ($remainingStaff > 0) {
                throw ValidationException::withMessages([
                    'business' => "You're the Owner of {$business->name}, and other staff still have access. Transfer ownership to a teammate from the Staff page, or remove all staff, before deleting your account.",
                ])->errorBag('userDeletion');
            }
        }

        Auth::logout();

        if ($business && $user->hasRole('Owner')) {
            $business->update(['status' => 'closed', 'closed_at' => now()]);
        }

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
