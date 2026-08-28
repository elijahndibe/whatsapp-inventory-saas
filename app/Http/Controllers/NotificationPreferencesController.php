<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationPreferencesController extends Controller
{
    /**
     * Per-user email toggles, not per-business — unlike the rest of
     * Settings, this updates the signed-in user, not $request->user()->business.
     */
    public function update(Request $request): RedirectResponse
    {
        $enabled = (array) $request->input('email', []);

        $preferences = collect(User::EMAIL_NOTIFICATION_TYPES)
            ->mapWithKeys(fn (string $type) => [$type => in_array($type, $enabled, true)])
            ->all();

        $request->user()->update(['notification_preferences' => $preferences]);

        // The Settings page reads its active tab from the URL fragment
        // client-side (Alpine) — a fragment isn't sent to the server, so it
        // has to be appended to the redirect target directly, not passed as
        // a route/query parameter.
        return redirect(route('settings.edit').'#notifications')->with('status', 'Notification preferences updated.');
    }
}
