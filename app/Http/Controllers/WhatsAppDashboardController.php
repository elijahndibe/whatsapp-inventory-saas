<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The dedicated "WhatsApp" section of the seller dashboard (product spec:
 * WhatsApp ordering is core, not a Settings sub-tab). The actual connect/
 * disconnect flow (FB Embedded Signup JS, forms) stays in Settings as the
 * single source of truth — see WhatsAppConnectController and
 * settings/edit.blade.php — this page just surfaces status plus the
 * store-sharing tools that belong here.
 */
class WhatsAppDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('manage settings');

        return view('whatsapp.index', ['business' => $request->user()->business]);
    }
}
