<?php

namespace Tests\Feature;

use App\Models\Barang;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateHargaNullableTipeBarangTest extends TestCase
{
    use RefreshDatabase;

    public function test_update_harga_only_updates_target_barang_by_id(): void
    {
        // Create multiple barangs with similar names
        $barang1 = Barang::create([
            'id_barang' => 'TEST-001',
            'nama_barang' => 'BARANG JADI',
            'tipe_barang' => 'APAR',
            'berat_barang' => null,
            'harga_beli' => 100000,
        ]);

        $barang2 = Barang::create([
            'id_barang' => 'TEST-002',
            'nama_barang' => 'BARANG JADI',
            'tipe_barang' => 'APAR',
            'berat_barang' => null,
            'harga_beli' => 100000,
        ]);

        // Update only barang1
        $this->actingAs($this->createSupervisor())->post(route('supervisor.updateHarga'), [
            'id_barang' => 'TEST-001',
            'harga_beli' => 150000,
        ])->assertStatus(200)
        ->assertJson(['success' => true]);

        // Verify only barang1 is updated
        $this->assertDatabaseHas('barangs', [
            'id_barang' => 'TEST-001',
            'harga_beli' => 150000,
        ]);

        // Verify barang2 is NOT updated
        $this->assertDatabaseHas('barangs', [
            'id_barang' => 'TEST-002',
            'harga_beli' => 100000,
        ]);
    }

    private function createSupervisor()
    {
        return \App\Models\User::create([
            'name' => 'Supervisor Test',
            'email' => 'supervisor@test.com',
            'password' => bcrypt('password'),
            'role' => 'supervisor',
        ]);
    }
}
