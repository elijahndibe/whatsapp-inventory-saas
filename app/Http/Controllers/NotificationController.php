<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function markRead(Request $request, string $notification): RedirectResponse
    {
        $found = $request->user()->notifications()->where('id', $notification)->first();
        abort_unless($found, 404);

        $found->markAsRead();

        return redirect($found->data['url'] ?? route('dashboard'));
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back();
    }
}
