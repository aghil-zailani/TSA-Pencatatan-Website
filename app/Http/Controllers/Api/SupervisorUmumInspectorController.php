<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupervisorUmumInspectorController extends Controller
{
    public function index(Request $request)
    {
        $inspectors = $request->user()
            ->inspectors()
            ->where('role', 'inspektor')
            ->get(['id', 'username', 'email', 'no_hp', 'created_at']);

        return response()->json(['inspectors' => $inspectors]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'no_hp' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $inspector = $request->user()->inspectors()->create([
            'username' => $validated['username'],
            'email' => $validated['email'],
            'no_hp' => $validated['no_hp'],
            'password' => $validated['password'],
            'role' => 'inspektor',
        ]);

        return response()->json([
            'message' => 'Inspektor berhasil ditambahkan',
            'inspector' => $this->inspectorData($inspector),
        ], 201);
    }

    public function update(Request $request, string $id)
    {
        $inspector = $this->findInspector($request, $id);
        $validated = $request->validate([
            'username' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'username')->ignore($inspector->id),
            ],
            'email' => [
                'sometimes',
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($inspector->id),
            ],
            'no_hp' => ['sometimes', 'required', 'string', 'max:20'],
            'password' => ['sometimes', 'required', 'string', 'min:8', 'confirmed'],
        ]);

        if ($validated === []) {
            return response()->json([
                'message' => 'Minimal satu field harus dikirim untuk diperbarui.',
            ], 422);
        }

        $inspector->fill($validated);
        $inspector->save();

        return response()->json([
            'message' => 'Data inspektor berhasil diperbarui',
            'inspector' => $this->inspectorData($inspector),
        ]);
    }

    public function destroy(Request $request, string $id)
    {
        $inspector = $this->findInspector($request, $id);
        $inspector->delete();

        return response()->json(['message' => 'Inspektor berhasil dihapus']);
    }

    private function findInspector(Request $request, string $id): User
    {
        return $request->user()
            ->inspectors()
            ->where('role', 'inspektor')
            ->findOrFail($id);
    }

    private function inspectorData(User $inspector): array
    {
        return [
            'id' => $inspector->id,
            'username' => $inspector->username,
            'email' => $inspector->email,
            'no_hp' => $inspector->no_hp,
            'role' => $inspector->role,
        ];
    }
}
