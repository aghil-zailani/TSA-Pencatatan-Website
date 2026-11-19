<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\PengajuanBarang;
use App\Models\Transaksi;
use App\Models\User;
use App\Http\Middleware\EnsureSingleActiveSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CatatBarangKeluarTest extends TestCase
{
    use RefreshDatabase;

    public function test_submission_creates_a_pending_validation_report_without_changing_stock(): void
    {
        Cache::forget('auth.single_active_session_id');

        $user = User::create([
            'id' => '220201',
            'username' => 'outgoing-test',
            'email' => 'outgoing-test@example.com',
            'password' => Hash::make('password'),
            'role' => 'staff_gudang',
        ]);

        $barang = Barang::create([
            'id_barang' => 'OUT-TEST-01',
            'nama_barang' => 'APAR CO2',
            'tipe_barang' => 'Barang Jadi',
            'tipe_barang_kategori' => 'APAR',
            'jumlah_barang' => 8,
            'satuan' => 'unit',
        ]);
        $barangKedua = Barang::create([
            'id_barang' => 'OUT-TEST-02',
            'nama_barang' => 'Hydrant Nozzle',
            'tipe_barang' => 'Barang Jadi',
            'tipe_barang_kategori' => 'HYDRANT',
            'jumlah_barang' => 5,
            'satuan' => 'pcs',
        ]);

        $this->actingAs($user)
            ->post(route('staff_gudang.barang_keluar.store'), [
                'items' => [
                    ['id_barang' => $barang->id_barang, 'jumlah_barang' => 2],
                    ['id_barang' => $barangKedua->id_barang, 'jumlah_barang' => 3],
                ],
                'tujuan' => 'Gudang cabang',
                'keterangan' => 'Pemindahan stok',
            ])
            ->assertRedirect(route('staff_gudang.barang_keluar.create'))
            ->assertSessionHas('message');

        $reportId = Transaksi::query()->value('report_id');

        $this->assertDatabaseHas('transaksis', [
            'report_id' => $reportId,
            'id_barang' => $barang->id_barang,
            'jumlah_barang' => 2,
            'tujuan' => 'Gudang cabang',
            'keterangan' => 'Pemindahan stok',
            'status' => 'keluar',
        ]);
        $this->assertDatabaseHas('transaksis', [
            'report_id' => $reportId,
            'id_barang' => $barangKedua->id_barang,
            'jumlah_barang' => 3,
            'tujuan' => 'Gudang cabang',
            'keterangan' => 'Pemindahan stok',
            'status' => 'keluar',
        ]);

        $this->assertDatabaseHas('pengajuan_barangs', [
            'report_id' => $reportId,
            'id_barang' => $barang->id_barang,
            'jumlah_barang' => 2,
            'status' => 'proses',
            'created_by_id' => $user->id,
        ]);
        $this->assertDatabaseHas('pengajuan_barangs', [
            'report_id' => $reportId,
            'id_barang' => $barangKedua->id_barang,
            'jumlah_barang' => 3,
            'status' => 'proses',
            'created_by_id' => $user->id,
        ]);

        $this->assertSame(8, $barang->fresh()->jumlah_barang);
        $this->assertSame(5, $barangKedua->fresh()->jumlah_barang);
        $this->assertSame(2, PengajuanBarang::where('report_id', $reportId)->count());

        $this->withoutMiddleware(EnsureSingleActiveSession::class)
            ->get(route('supervisor.validasi.barang_keluar'))
            ->assertOk()
            ->assertSee(route('supervisor.validasi.keluar_detail', $reportId), false);

        $this->get(route('supervisor.validasi.keluar_detail', $reportId))
            ->assertOk()
            ->assertSee('Gudang cabang')
            ->assertSee('Pemindahan stok')
            ->assertSee('APAR CO2')
            ->assertSee('Hydrant Nozzle');
    }
}