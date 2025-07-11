<?php

namespace App\Exports;

use App\Models\Barang;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Maatwebsite\Excel\Events\AfterSheet;

class ExcelExport implements FromCollection, WithHeadings, WithEvents, ShouldAutoSize, WithDrawings
{
    public function collection()
    {
        // Get the data and transform it to include row numbers
        $data = Barang::select(
            'nama_barang',
            'tipe_barang',
            'jumlah_barang',
            'berat_barang',
            'harga_beli',
            'harga_jual',
            'satuan',
            'kondisi'
        )->get();

        // Transform data to include row numbers as first column
        $transformedData = [];
        foreach ($data as $index => $item) {
            $transformedData[] = [
                $index + 1, // Row number
                $item->nama_barang,
                $item->tipe_barang,
                $item->jumlah_barang,
                $item->berat_barang,
                $item->harga_beli,
                $item->harga_jual,
                $item->satuan,
                $item->kondisi
            ];
        }

        return collect($transformedData);
    }

    public function headings(): array
    {
        return [
            ['No', 'Nama Barang', 'Tipe Barang', 'Jumlah Stok', 'Berat', 'Harga Beli', 'Harga Jual', 'Satuan', 'Kondisi']
        ];
    }

    // public function drawings()
    // {
    //     $drawing = new Drawing();
    //     $drawing->setName('Logo');
    //     $drawing->setDescription('Logo TSA');
    //     $drawing->setPath(public_path('logo/tsa.png'));
    //     $drawing->setHeight(60);
    //     $drawing->setCoordinates('A1');
    //     return $drawing;
    // }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Geser seluruh data table ke bawah (row 5)
                $sheet->insertNewRowBefore(1, 4);

                // Pasang header perusahaan di baris 1-2
                $sheet->setCellValue('B1', 'PT. Tunas Siak Anugrah');
                $sheet->setCellValue('B2', 'Jl. Tengku Maharatu I Blok D No.05 Maharani, Rumbai Pekanbaru – RIAU 28264');

                $sheet->mergeCells('B1:I1');
                $sheet->mergeCells('B2:I2');

                $sheet->getStyle('B1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('B2')->getFont()->setSize(10);
                $sheet->getStyle('B1:B2')->getAlignment()->setHorizontal('center');

                $sheet->getStyle('A1:I3')->getBorders()->getOutline()
                    ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);

                // Style table header (row 5 after insert)
                $sheet->getStyle('A5:I5')->getFont()->setBold(true);
                $sheet->getStyle('A5:I5')->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('C00000');
                $sheet->getStyle('A5:I5')->getFont()->getColor()->setRGB('FFFFFF');
                $sheet->getStyle('A5:I5')->getAlignment()->setHorizontal('center');

                $rowCount = Barang::count();
                $lastRow = 5 + $rowCount; // Adjusted for the new row positions

                // Table borders (starting from row 5)
                $sheet->getStyle("A5:I{$lastRow}")->getBorders()->getAllBorders()
                    ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                // Set row heights
                $sheet->getRowDimension(1)->setRowHeight(30);
                $sheet->getRowDimension(2)->setRowHeight(20);
                $sheet->getRowDimension(3)->setRowHeight(10);
                $sheet->getRowDimension(5)->setRowHeight(25); // Header row

                // Atur lebar kolom
                $sheet->getColumnDimension('A')->setWidth(5);   // No
                $sheet->getColumnDimension('B')->setWidth(20);  // Nama Barang
                $sheet->getColumnDimension('C')->setWidth(15);  // Tipe Barang
                $sheet->getColumnDimension('D')->setWidth(12);  // Jumlah Stok
                $sheet->getColumnDimension('E')->setWidth(10);  // Berat
                $sheet->getColumnDimension('F')->setWidth(15);  // Harga Beli
                $sheet->getColumnDimension('G')->setWidth(15);  // Harga Jual
                $sheet->getColumnDimension('H')->setWidth(10);  // Satuan
                $sheet->getColumnDimension('I')->setWidth(12);  // Kondisi

                // Center nomor urut and align data
                $sheet->getStyle("A6:A{$lastRow}")->getAlignment()->setHorizontal('center');
                $sheet->getStyle("B6:I{$lastRow}")->getAlignment()->setVertical('center')->setWrapText(true);
            },
        ];
    }
}