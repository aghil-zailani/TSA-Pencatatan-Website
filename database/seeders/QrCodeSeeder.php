<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Barang;
use App\Models\QrCode;

class QrCodeSeeder extends Seeder
{
    public function run(): void
    {
        $dataQr = [
            [
                'nama_barang' => 'APAR CO2 3Kg',
                'nomor_identifikasi' => 'APAR-CO2-003',
                'qr_code_path' => 'qr/apar_co2_3kg.png',
            ],
            [
                'nama_barang' => 'APAR Powder 6Kg',
                'nomor_identifikasi' => 'APAR-PWD-006',
                'qr_code_path' => 'qr/apar_powder_6kg.png',
            ],
            [
                'nama_barang' => 'Hydrant Valve',
                'nomor_identifikasi' => 'HYD-VALVE-001',
                'qr_code_path' => 'qr/hydrant_valve.png',
            ],
            [
                'nama_barang' => 'Hydrant Box',
                'nomor_identifikasi' => 'HYD-BOX-002',
                'qr_code_path' => 'qr/hydrant_box.png',
            ],
        ];

        foreach ($dataQr as $data) {
            $barang = Barang::where('nama_barang', $data['nama_barang'])->first();

            if ($barang) {
                QrCode::create([
                    'id_barang' => $barang->id_barang,
                    'nomor_identifikasi' => $data['nomor_identifikasi'],
                    'qr_code_path' => $data['qr_code_path'],
                    'tanggal_pembuatan' => now()->toDateString(),
                ]);
            }
        }
    }
}
