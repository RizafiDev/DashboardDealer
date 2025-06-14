<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function show()
    {
        return view('auth.login');
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Gunakan guard 'karyawan' secara eksplisit
        if (Auth::guard('karyawan')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/absensi');
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->withInput($request->only('email')); // Tambahkan withInput
    }

    public function logout(Request $request)
    {
        // Gunakan guard 'karyawan' secara eksplisit
        Auth::guard('karyawan')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
