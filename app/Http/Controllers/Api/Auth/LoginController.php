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
        
        $request->validate([
            'id' => 'required|string',
            'password' => 'required|string',
        ]);

        
        $user = User::where('id', $request->id)->first();

        
        if (! $user || ! Hash::check($request->password, $user->password)) {
            
            throw ValidationException::withMessages([
                'id' => ['ID Perusahaan atau Password salah.'],
            ]);
        }

        
        $token = $user->createToken('api-token-'.$user->id)->plainTextToken;

        
        $userData = [
            'id' => $user->id, 
            'username' => $user->username, 
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

    public function loginAndroid(Request $request)
    {
        
        $request->validate([
            'username' => 'required|string', 
            'password' => 'required|string',
        ]);

        
        $user = User::where('username', $request->username)->first(); 

        
        if (! $user || ! Hash::check($request->password, $user->password)) {
            
            throw ValidationException::withMessages([
                'username' => ['Username atau Password salah.'],
            ]);
        }

        
        $token = $user->createToken('api-token-'.$user->id)->plainTextToken;

        
        $userData = [
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'role' => $user->role,
        ];

        return response()->json([
            'message' => 'Login berhasil',
            'user' => $userData,
            'token_type' => 'Bearer',
            'access_token' => $token,
        ],200);
    }
}