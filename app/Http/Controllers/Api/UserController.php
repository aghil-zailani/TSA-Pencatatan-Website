<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function update(Request $request)
    {
        $user = $request->user(); 

        $rules = [];

        if (in_array($user->role, ['inspektor', 'supervisor_umum'])) {
            $rules['username'] = [
                'sometimes',
                'required',
                'string',
                Rule::unique('users')->ignore($user->id),
            ];
        }

        $rules['password'] = ['sometimes', 'required', 'min:6'];

        $validated = $request->validate($rules);

        if (isset($validated['username'])) {
            $user->username = $validated['username'];
        }

        if (isset($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return response()->json([
            'message' => 'Akun berhasil diperbarui',
            'user' => [
                'id'       => $user->id,
                'username' => $user->username,
                'email'    => $user->email,
                'role'     => $user->role,
            ]
        ]);
    }
}