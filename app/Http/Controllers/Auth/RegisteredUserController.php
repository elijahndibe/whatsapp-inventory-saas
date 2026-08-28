<?php

namespace App\Http\Controllers\Auth;

use App\Actions\RegisterBusinessAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterBusinessRequest;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request. Registration creates the
     * business and its Owner user together — there is no intermediate
     * "signed up but no business yet" state to reason about elsewhere
     * in the app.
     */
    public function store(RegisterBusinessRequest $request, RegisterBusinessAction $action): RedirectResponse
    {
        $user = $action->execute($request->validated());

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('onboarding.show', absolute: false));
    }
}
