<?php

namespace Tests\Unit;

use App\Models\PengajuanBarang;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PengajuanBarangTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_generates_a_unique_report_id_when_the_candidate_exists(): void
    {
        PengajuanBarang::create([
            'report_id' => 'RPT-ABC12345',
            'nama_laporan' => 'Test',
            'status' => '-',
            'catatan_penolakan' => '-',
        ]);

        $reportId = PengajuanBarang::generateUniqueReportId('RPT-', 8);

        $this->assertStringStartsWith('RPT-', $reportId);
        $this->assertNotSame('RPT-ABC12345', $reportId);
        $this->assertFalse(PengajuanBarang::where('report_id', $reportId)->exists());
    }
}
