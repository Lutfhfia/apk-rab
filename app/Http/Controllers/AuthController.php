<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;

class AuthController extends Controller
{
    // Menampilkan halaman login
    public function showLoginForm()
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user());
        }

        return view('auth.login');
    }

    // Memproses data login
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Logika Autentikasi Laravel
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Cek apakah user aktif
            if (!Auth::user()->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'Akun Anda telah dinonaktifkan. Hubungi administrator.',
                ]);
            }

            return $this->redirectByRole(Auth::user())
                ->with('success', 'Selamat Datang, ' . Auth::user()->name);
        }

        return back()->withErrors([
            'email' => 'Email atau Kata Sandi salah.',
        ])->onlyInput('email');
    }

    /**
     * Mengarahkan pengguna berdasarkan role mereka.
     */
    protected function redirectByRole($user)
    {
        $role = $user->role->value ?? $user->role;

        return match ($role) {
            'admin_keuangan'    => redirect()->intended(route('admin.dashboard')),
            'manajer_keuangan'  => redirect()->intended(route('manajer.dashboard')),
            'direktur'          => redirect()->intended(route('direktur.dashboard')),
            default             => redirect()->intended(route('admin.dashboard')),
        };
    }

    // 1. Tampilkan form lupa password
    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    // 2. Kirim link reset ke email
    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
                    ? back()->with(['status' => 'Link reset password telah dikirim ke email Anda.'])
                    : back()->withErrors(['email' => 'Kami tidak dapat menemukan pengguna dengan alamat email tersebut.']);
    }

    // 3. Tampilkan form reset password dari link
    public function showResetForm(Request $request, $token)
    {
        return view('auth.reset-password', ['token' => $token, 'email' => $request->email]);
    }

    // 4. Proses simpan password baru
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                // Hash kata sandi sekali secara eksplisit, lalu simpan melalui query DB
                // untuk menghindari cast 'hashed' pada model (yang akan melakukan double-hash)
                $hashedPassword = Hash::make($password);
                $rememberToken = Str::random(60);

                \Illuminate\Support\Facades\DB::table('users')
                    ->where('id', $user->id)
                    ->update([
                        'password' => $hashedPassword,
                        'remember_token' => $rememberToken,
                        'updated_at' => now(),
                    ]);

                $user->setRememberToken($rememberToken);
                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
                    ? redirect()->route('login')->with('success', 'Password berhasil direset! Silakan login.')
                    : back()->withErrors(['email' => [__($status)]]);
    }

    // Method logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
