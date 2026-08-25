<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::with(['business', 'roles'])
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%'.$request->string('search').'%')
                ->orWhere('email', 'like', '%'.$request->string('search').'%'))
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }
}
