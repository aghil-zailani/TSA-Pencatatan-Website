<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        // PERUBAHAN 1: Validasi sekarang untuk field 'id'
        $request->validate([
            'id' => 'required|string',
            'password' => 'required|string',
        ]);

        // PERUBAHAN 2: Cari user berdasarkan kolom 'id' di database
        $user = User::where('id', $request->id)->first();

        // Cek apakah user ditemukan dan password cocok
        if (! $user || ! Hash::check($request->password, $user->password)) {
            // PERUBAHAN 3: Pesan error merujuk ke 'id'
            throw ValidationException::withMessages([
                'id' => ['ID Perusahaan atau Password salah.'],
            ]);
        }

        // PERUBAHAN 4: Buat nama token menggunakan user->id
        $token = $user->createToken('api-token-'.$user->id)->plainTextToken;

        // Sesuaikan data user yang dikembalikan
        $userData = [
            'id' => $user->id, // Menggunakan 'id' sesuai kolom database
            'username' => $user->username, // Kolom username dari database Anda
            'email' => $user->email,
            'role' => $user->role,
        ];

        return response()->json([
            'message' => 'Login berhasil',
            'user' => $userData,
            'token_type' => 'Bearer',
            'access_token' => $token,
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'message' => 'Berhasil logout dari API'
        ], 200);
    }
}