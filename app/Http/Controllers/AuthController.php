<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AuthController extends Controller
{
    /** KUET student email domain enforced at validation + DB level. */
    private const KUET_DOMAIN = '@stud.kuet.ac.bd';

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'      => ['required', 'string', 'max:100'],
            'email'     => [
                'required', 'string', 'email', 'max:150',
                'ends_with:' . self::KUET_DOMAIN,
                Rule::unique('users', 'email'),
            ],
            'mobile_no' => ['nullable', 'string', 'max:20'],
            'password'  => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'email.ends_with' => 'Only KUET students can register. Email must end with ' . self::KUET_DOMAIN,
            'email.unique'    => 'An account with this email already exists.',
        ]);

        $user = User::create([
            'name'          => $data['name'],
            'email'         => $data['email'],
            'mobile_no'     => $data['mobile_no'] ?? null,
            'password_hash' => Hash::make($data['password']),
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('products.index')
            ->with('status', 'Welcome to the KUET marketplace, ' . $user->name . '!');
    }

    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']])) {
            return back()
                ->withErrors(['email' => 'These credentials do not match our records.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        // Invisible router: the database record decides where you land.
        // Admins go to the admin dashboard, everyone else to the marketplace.
        if (Auth::user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->intended(route('products.index'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'You have been signed out.');
    }
}
