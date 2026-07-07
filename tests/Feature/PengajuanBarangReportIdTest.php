<?php

namespace Tests\Feature;

use App\Models\PengajuanBarang;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PengajuanBarangReportIdTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_allows_multiple_rows_to_share_the_same_report_id(): void
    {
        PengajuanBarang::create([
            'report_id' => 'RPT-AAAAAAAA',
            'status' => '-',
        ]);

        PengajuanBarang::create([
            'report_id' => 'RPT-AAAAAAAA',
            'status' => '-',
        ]);

        $this->assertSame(2, PengajuanBarang::where('report_id', 'RPT-AAAAAAAA')->count());
    }
}
