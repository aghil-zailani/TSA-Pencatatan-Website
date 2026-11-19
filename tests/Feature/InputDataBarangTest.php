<?php

namespace Tests\Feature;

use App\Models\PengajuanBarang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InputDataBarangTest extends TestCase
{
    use RefreshDatabase;

    public function test_input_page_displays_barang_type_tabs_and_categories(): void
    {
        $this->actingAs($this->createUser())
            ->get(route('staff_gudang.input_data'))
            ->assertOk()
            ->assertSee('Barang Jadi')
            ->assertSee('Sparepart')
            ->assertSee('APAR')
            ->assertSee('HYDRANT');
    }

    public function test_barang_submission_is_sent_to_supervisor_validation(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)
            ->post(route('staff_gudang.input_data.store'), [
                'tipe_barang' => 'Sparepart',
                'nama_barang' => 'Nozzle Hydrant',
                'jumlah_barang' => 4,
                'ukuran_barang' => '2 inch',
                'satuan' => 'pcs',
                'kondisi' => 'Baik',
            ])
            ->assertRedirect(route('staff_gudang.input_data'))
            ->assertSessionHas('message');

        $this->assertDatabaseHas('pengajuan_barangs', [
            'nama_barang' => 'Nozzle Hydrant',
            'tipe_barang' => 'Sparepart',
            'tipe_barang_kategori' => null,
            'jumlah_barang' => 4,
            'kondisi_barang' => 'Baik',
            'status' => 'proses',
            'created_by_id' => $user->id,
        ]);

        $this->assertSame(1, PengajuanBarang::count());
    }

    public function test_barang_jadi_keeps_its_selected_category(): void
    {
        $this->actingAs($this->createUser())
            ->post(route('staff_gudang.input_data.store'), [
                'tipe_barang' => 'Barang Jadi',
                'tipe_barang_kategori' => 'APAR',
                'nama_barang' => 'APAR Powder 6 Kg',
                'jumlah_barang' => 2,
            ])
            ->assertRedirect(route('staff_gudang.input_data'));

        $this->assertDatabaseHas('pengajuan_barangs', [
            'nama_barang' => 'APAR Powder 6 Kg',
            'tipe_barang' => 'Barang Jadi',
            'tipe_barang_kategori' => 'APAR',
            'status' => 'proses',
        ]);
    }

    private function createUser(): User
    {
        Cache::forget('auth.single_active_session_id');

        return User::create([
            'id' => '220101',
            'username' => 'input-test',
            'email' => 'input-test@example.com',
            'password' => Hash::make('password'),
            'role' => 'staff_gudang',
        ]);
    }
}