<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

// Controller untuk proses login dan logout user
class LoginController extends Controller
{
    public function index(): View
    {
        return view('login');
    }

    public function authenticate(Request $request): JsonResponse
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('username', 'password');


        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            /** @var \App\Models\User $user */
            $user = Auth::user();
            // Create sanctum token for API access (follows Single Responsibility principle)
            $token = $user->createToken('auth-token')->plainTextToken;

            return response()->json([
                'status' => 'success',
                'message' => 'Login berhasil. Mengalihkan...',
                'redirect' => route('kasir'),
                'token' => $token, // Return token for API authentication
            ]);
        }

        // Jika gagal, kembalikan error
        return response()->json([
            'status' => 'error',
            'message' => 'Username atau password salah.',
        ], 401);
    }

    // Logout user yang sedang login
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout(); // Logout user
        $request->session()->invalidate(); // Hapus session
        $request->session()->regenerateToken(); // Regenerasi token CSRF

        return redirect('/login'); // Redirect ke halaman login
    }
}
