<?php

namespace App\Http\Controllers;

use App\Models\Reader;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);
        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'El correo o la contraseña no son correctos.'])->onlyInput('email');
        }

        $request->session()->regenerate();
        return redirect()->intended(Auth::user()->role === 'admin' ? route('admin.dashboard') : route('lector.prestamos'));
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'document_number' => ['nullable', 'string', 'max:255', 'unique:readers,document_number'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email', 'unique:readers,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['first_name'].' '.$data['last_name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => 'reader',
            ]);
            Reader::create([
                'user_id' => $user->id,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'card_number' => 'L-'.str_pad((string) $user->id, 6, '0', STR_PAD_LEFT),
                'document_number' => $data['document_number'] ?? null,
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'status' => 'active',
            ]);
            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();
        return redirect()->route('lector.prestamos')->with('success', 'Tu cuenta de lector está lista.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('inicio');
    }
}
