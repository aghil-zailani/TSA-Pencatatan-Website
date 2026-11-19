<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'nama_instansi' => ['required', 'string', 'max:255'],
            'lokasi_instansi' => ['required', 'string', 'max:1000'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'foto_instansi' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'inspektors' => ['nullable', 'array'],
            'inspektors.*.nama' => ['required', 'string', 'max:255'],
        ]);

        $photoPath = null;
        if ($request->hasFile('foto_instansi')) {
            $photoPath = $request->file('foto_instansi')->store('foto_instansi', 'public');

            if ($photoPath === false) {
                throw new RuntimeException('Foto instansi gagal disimpan.');
            }
        }

        try {
            $user = DB::transaction(function () use ($validated, $photoPath) {
                $user = User::create([
                    'nama_instansi' => $validated['nama_instansi'],
                    'lokasi_instansi' => $validated['lokasi_instansi'],
                    'foto_instansi' => $photoPath,
                    'username' => $validated['username'],
                    'email' => $validated['email'],
                    'password' => $validated['password'],
                    'role' => 'supervisor_umum',
                ]);

                foreach ($validated['inspektors'] ?? [] as $inspektor) {
                    $user->inspektors()->create([
                        'nama' => $inspektor['nama'],
                    ]);
                }

                return $user;
            });
        } catch (Throwable $exception) {
            if ($photoPath !== null) {
                Storage::disk('public')->delete($photoPath);
            }

            throw $exception;
        }

        $token = $user->createToken('api-token-'.$user->id)->plainTextToken;
        $inspektors = $user->inspektors()->get(['id_inspektor', 'nama']);

        return response()->json([
            'message' => 'Registrasi berhasil',
            'user' => [
                'id' => $user->id,
                'nama_instansi' => $user->nama_instansi,
                'lokasi_instansi' => $user->lokasi_instansi,
                'foto_instansi' => $user->foto_instansi,
                'foto_instansi_url' => $user->foto_instansi
                    ? Storage::disk('public')->url($user->foto_instansi)
                    : null,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role,
                'inspektors' => $inspektors,
            ],
            'token_type' => 'Bearer',
            'access_token' => $token,
        ], 201);
    }
}
