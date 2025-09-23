<?php

namespace App\Exports;

use App\Models\LaporanAPK;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LaporanExport implements FromCollection, WithStyles, WithEvents
{
    protected $data;

    public function collection()
    {
        // Simpan data tapi jangan return supaya tidak auto-render
        $this->data = LaporanAPK::whereIn('status', ['Diterima', 'Ditolak'])
            ->where('created_by_role', 'staff_gudang')
            ->orderByDesc('updated_at')
            ->get()
            ->map(function ($item, $key) {
                return [
                    $key + 1,
                    $item->id_qr,
                    $item->created_by_role,
                    $item->nama_barang,
                    $item->tipe_barang,
                    $item->tanggal_inspeksi ? $item->tanggal_inspeksi->format('d M Y H:i') : 'N/A',
                    $item->lokasi_alat,
                    $item->kondisi_fisik,
                    $item->selang,
                    $item->pressure_gauge,
                    $item->safety_pin,
                    $item->tindakan,
                    $item->catatan_tindakan,
                    ucfirst(strtolower($item->status)),
                    $item->catatan_validasi,
                ];
            });

        return collect([]); // kosong → biar tidak ada row duplikat
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Judul
                $sheet->mergeCells('A1:O1');
                $sheet->setCellValue('A1', 'PT Tunas Siak Anugrah');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                ]);

                $sheet->mergeCells('A2:O2');
                $sheet->setCellValue('A2', 'Laporan Inspeksi Bulanan');
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 13],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                ]);

                // Header tabel (baris 4)
                $headings = [
                    'No','ID QR','Role Pembuat','Nama Barang','Tipe Barang',
                    'Tanggal Inspeksi','Lokasi Alat','Kondisi Fisik','Selang',
                    'Pressure Gauge','Safety Pin','Tindakan','Catatan Tindakan',
                    'Status','Catatan Validasi'
                ];
                $sheet->fromArray([$headings], null, 'A4');

                // Data mulai baris 5
                $rows = $this->data->toArray();
                $sheet->fromArray($rows, null, 'A5');

                // Styling header
                $sheet->getStyle('A4:O4')->applyFromArray([
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'color' => ['rgb' => '4CAF50'],
                    ],
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                        'size'  => 12,
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // Freeze header
                $sheet->freezePane('A5');

                // Auto size kolom
                foreach (range('A', 'O') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                // Border
                $lastRow = $sheet->getHighestRow();
                $lastCol = $sheet->getHighestColumn();
                $sheet->getStyle("A4:{$lastCol}{$lastRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                    ],
                ]);

                // Zebra effect
                for ($row = 5; $row <= $lastRow; $row++) {
                    if ($row % 2 == 0) {
                        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                            'fill' => [
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'color' => ['rgb' => 'F9F9F9'],
                            ],
                        ]);
                    }
                }

                // Filter header
                $sheet->setAutoFilter("A4:{$lastCol}4");

                // Logo BUMN
                $drawing1 = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                $drawing1->setPath(public_path('logo/logo_bumn.png'));
                $drawing1->setHeight(60);
                $drawing1->setCoordinates('A1');
                $drawing1->setWorksheet($sheet);

                // Logo Pertamina
                $drawing2 = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                $drawing2->setPath(public_path('logo/logo_pertamina.png'));
                $drawing2->setHeight(60);
                $drawing2->setCoordinates('O1');
                $drawing2->setWorksheet($sheet);
            },
        ];
    }
}
