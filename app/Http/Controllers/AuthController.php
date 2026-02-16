<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;


class AuthController extends Controller
{
    // Fungsi untuk registrasi user baru
    public function register(Request $request): JsonResponse
    {
        // Validasi input dari request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users,username',
            'email' => 'nullable|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // Membuat user baru di database
        $user = User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'] ?? null,
            'password' => Hash::make($validated['password']), // Enkripsi password
        ]);

        // Generate token autentikasi untuk user
        $token = $user->createToken('auth-token')->plainTextToken;

        // Kembalikan response JSON berisi user dan token
        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    // Fungsi untuk login user
    public function login(Request $request): JsonResponse
    {
        // Validasi input login
        $validated = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Cari user berdasarkan username
        $user = User::where('username', $validated['username'])->first();

        // Cek password, jika salah log dan lempar error
        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            Log::channel('business')->warning('Gagal login', [
                'username' => $validated['username'],
                'ip' => $request->ip(),
            ]);
            throw ValidationException::withMessages([
                'username' => ['Username atau password salah.'],
            ]);
        }

        // Generate token baru untuk user
        $token = $user->createToken('auth-token')->plainTextToken;

        // Kembalikan response JSON berisi user dan token
        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    // Fungsi untuk logout user (hapus token aktif)
    public function logout(Request $request): JsonResponse
    {
        // Hapus token akses yang sedang dipakai
        $request->user()->currentAccessToken()->delete();

        // Kembalikan response sukses logout
        return response()->json([
            'message' => 'Berhasil logout',
        ]);
    }
}
