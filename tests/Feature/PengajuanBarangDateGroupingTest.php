<?php

namespace Tests\Feature;

use App\Models\PengajuanBarang;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PengajuanBarangDateGroupingTest extends TestCase
{
    use RefreshDatabase;

    public function test_grouped_report_uses_latest_submission_date(): void
    {
        DB::table('pengajuan_barangs')->insert([
            [
                'report_id' => 'RPT-TEST',
                'nama_laporan' => 'Laporan Barang Masuk',
                'status' => 'proses',
                'created_at' => '2026-02-18 10:00:00',
                'updated_at' => '2026-02-18 10:00:00',
            ],
            [
                'report_id' => 'RPT-TEST',
                'nama_laporan' => 'Laporan Barang Masuk',
                'status' => 'proses',
                'created_at' => '2026-07-07 15:08:46',
                'updated_at' => '2026-07-07 15:08:46',
            ],
        ]);

        $result = PengajuanBarang::select('report_id', 'status', DB::raw('COUNT(*) as total_items'), DB::raw('MAX(created_at) as created_at'))
            ->where('status', 'proses')
            ->groupBy('report_id', 'status')
            ->first();

        $this->assertSame('2026-07-07 15:08:46', $result->created_at->toDateTimeString());
    }
}
