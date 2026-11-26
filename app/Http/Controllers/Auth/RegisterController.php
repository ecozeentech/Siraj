<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserProvisioner;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store(Request $request, UserProvisioner $provisioner)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email:rfc', 'max:190', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:32', 'regex:/^\+?[0-9\s\-]{7,20}$/', 'unique:users,phone'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'terms' => ['accepted'],
        ], ['terms.accepted' => 'Please accept the Terms and Privacy Policy to continue.']);

        $user = User::query()->create([
            'name' => $data['name'], 'email' => strtolower($data['email']), 'phone' => $data['phone'] ?? null, 'password' => $data['password'],
        ]);
        $provisioner->provision($user);

        event(new Registered($user));
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route(config('siraj.require_verified_email') ? 'verification.notice' : 'app.onboarding.show');
    }
}
