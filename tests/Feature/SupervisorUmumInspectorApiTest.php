<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SupervisorUmumInspectorApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_supervisor_umum_can_register_without_storing_password_confirmation(): void
    {
        Storage::fake('public');

        $response = $this->post('/api/register', [
            'nama_instansi' => 'Dinas Pemadam',
            'lokasi_instansi' => 'Bandung',
            'username' => 'supervisor-baru',
            'email' => 'supervisor@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'foto_instansi' => UploadedFile::fake()->image('logo.png'),
            'inspektors' => [
                ['nama' => 'Siti Aminah'],
                ['nama' => 'Budi Santoso'],
            ],
            'role' => 'supervisor',
        ]);

        $response->assertCreated()
            ->assertJsonPath('user.role', 'supervisor_umum')
            ->assertJsonPath('user.nama_instansi', 'Dinas Pemadam')
            ->assertJsonPath('user.lokasi_instansi', 'Bandung')
            ->assertJsonCount(2, 'user.inspektors')
            ->assertJsonStructure(['access_token', 'token_type']);

        $photoPath = User::where('username', 'supervisor-baru')->value('foto_instansi');
        $this->assertNotEmpty($photoPath);
        Storage::disk('public')->assertExists($photoPath);

        $supervisorId = User::where('username', 'supervisor-baru')->value('id');
        $this->assertDatabaseHas('users', [
            'username' => 'supervisor-baru',
            'role' => 'supervisor_umum',
            'nama_instansi' => 'Dinas Pemadam',
            'lokasi_instansi' => 'Bandung',
            'foto_instansi' => $photoPath,
        ]);
        $this->assertDatabaseHas('inspektors', [
            'id_supervisor' => $supervisorId,
            'nama' => 'Siti Aminah',
        ]);
        $this->assertDatabaseHas('inspektors', [
            'id_supervisor' => $supervisorId,
            'nama' => 'Budi Santoso',
        ]);
        $this->assertFalse(Schema::hasColumn('users', 'password_confirmation'));
        $this->assertTrue(Hash::check('password123', User::where('username', 'supervisor-baru')->value('password')));

        User::findOrFail($supervisorId)->delete();
        $this->assertDatabaseMissing('inspektors', [
            'id_supervisor' => $supervisorId,
        ]);
    }

    public function test_registration_without_optional_photo_or_inspectors_is_supported(): void
    {
        $this->postJson('/api/register', [
            'nama_instansi' => 'Dinas Pemadam',
            'lokasi_instansi' => 'Bandung',
            'username' => 'supervisor-tanpa-foto',
            'email' => 'supervisor-tanpa-foto@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertCreated()
            ->assertJsonPath('user.foto_instansi', null)
            ->assertJsonCount(0, 'user.inspektors');

        $this->assertDatabaseHas('users', [
            'username' => 'supervisor-tanpa-foto',
            'foto_instansi' => null,
        ]);
    }

    public function test_supervisor_umum_can_create_update_list_and_delete_owned_inspectors(): void
    {
        $supervisor = $this->createSupervisor('supervisor-satu');
        $this->actingAs($supervisor, 'sanctum');

        $createResponse = $this->postJson('/api/supervisor-umum/inspektor', [
            'username' => 'inspektor-satu',
            'email' => 'inspektor@example.com',
            'no_hp' => '+628123456789',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $createResponse->assertCreated()
            ->assertJsonPath('inspector.role', 'inspektor')
            ->assertJsonPath('inspector.no_hp', '+628123456789')
            ->assertJsonMissingPath('inspector.password');

        $inspectorId = $createResponse->json('inspector.id');
        $this->assertDatabaseHas('users', [
            'id' => $inspectorId,
            'supervisor_umum_id' => $supervisor->id,
            'role' => 'inspektor',
        ]);
        $this->assertTrue(Hash::check('password123', User::findOrFail($inspectorId)->password));

        $this->getJson('/api/supervisor-umum/inspektor')
            ->assertOk()
            ->assertJsonCount(1, 'inspectors')
            ->assertJsonPath('inspectors.0.id', $inspectorId);

        $this->patchJson("/api/supervisor-umum/inspektor/{$inspectorId}", [
            'email' => 'inspektor-baru@example.com',
            'password' => 'password456',
            'password_confirmation' => 'password456',
        ])->assertOk()
            ->assertJsonPath('inspector.email', 'inspektor-baru@example.com');

        $this->assertTrue(Hash::check('password456', User::findOrFail($inspectorId)->password));

        $this->deleteJson("/api/supervisor-umum/inspektor/{$inspectorId}")
            ->assertOk();

        $this->assertDatabaseMissing('users', ['id' => $inspectorId]);
    }

    public function test_supervisor_umum_cannot_access_another_supervisors_inspectors(): void
    {
        $firstSupervisor = $this->createSupervisor('supervisor-pertama');
        $secondSupervisor = $this->createSupervisor('supervisor-kedua');
        $inspector = $firstSupervisor->inspectors()->create([
            'username' => 'inspektor-milik-pertama',
            'email' => 'inspektor-milik-pertama@example.com',
            'no_hp' => '08123456789',
            'password' => 'password123',
            'role' => 'inspektor',
        ]);

        $this->actingAs($secondSupervisor, 'sanctum');

        $this->getJson('/api/supervisor-umum/inspektor')
            ->assertOk()
            ->assertJsonCount(0, 'inspectors');
        $this->patchJson("/api/supervisor-umum/inspektor/{$inspector->id}", [
            'username' => 'changed',
        ])->assertNotFound();
        $this->deleteJson("/api/supervisor-umum/inspektor/{$inspector->id}")
            ->assertNotFound();

        $this->assertDatabaseHas('users', [
            'id' => $inspector->id,
            'username' => 'inspektor-milik-pertama',
        ]);
    }

    private function createSupervisor(string $username): User
    {
        return User::create([
            'username' => $username,
            'email' => "{$username}@example.com",
            'password' => 'password123',
            'role' => 'supervisor_umum',
        ]);
    }
}
