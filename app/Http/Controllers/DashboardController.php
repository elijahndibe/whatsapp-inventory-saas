<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $business = $request->user()->business;

        return view('dashboard', [
            'business' => $business,
        ]);
    }
}
