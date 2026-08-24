<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateBusinessSettingsRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BusinessSettingsController extends Controller
{
    public function edit(Request $request): View
    {
        $this->authorize('manage settings');

        return view('settings.edit', ['business' => $request->user()->business]);
    }

    public function update(UpdateBusinessSettingsRequest $request): RedirectResponse
    {
        $business = $request->user()->business;
        $data = $request->validated();

        // Never overwrite the stored access token with blank just because
        // the field was left empty in the form — the current value is
        // deliberately never rendered back into the input.
        if (blank($data['whatsapp_access_token'] ?? null)) {
            unset($data['whatsapp_access_token']);
        }

        if ($request->hasFile('logo')) {
            if ($business->logo) {
                Storage::disk('public')->delete($business->logo);
            }
            $data['logo'] = $request->file('logo')->store('businesses', 'public');
        } else {
            unset($data['logo']);
        }

        $business->update($data);

        return redirect()->route('settings.edit')->with('status', 'Settings updated.');
    }
}
