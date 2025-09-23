<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BarangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('barangs')->insert([
            [
                'created_by_role' => 'supervisor_umum',
                'created_by_id' => '3',
                'nama_barang' => 'Pompa Air',
                'jumlah_barang' => 10,
                'tipe_barang' => 'Barang Jadi',
                'berat_barang' => 12.5,
                'satuan' => 'unit',
                'kondisi' => 'bagus',
                'harga_beli' => 500000,
                'harga_jual' => 650000,
                'ukuran_barang' => 'sedang',
                'merek_barang' => 'Toshiba',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'created_by_role' => 'supervisor_umum',
                'created_by_id' => '3',
                'nama_barang' => 'Pipa PVC',
                'jumlah_barang' => 100,
                'tipe_barang' => 'Sparepart',
                'berat_barang' => 2.3,
                'satuan' => 'meter',
                'kondisi' => 'bagus',
                'harga_beli' => 12000,
                'harga_jual' => 15000,
                'ukuran_barang' => '1 inch',
                'merek_barang' => 'Rucika',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'created_by_role' => 'supervisor_umum',
                'created_by_id' => '3',
                'nama_barang' => 'APAR CO2 3Kg',
                'jumlah_barang' => 5,
                'tipe_barang' => 'APAR',
                'berat_barang' => 5.0,
                'satuan' => 'unit',
                'kondisi' => 'bagus',
                'harga_beli' => 800000,
                'harga_jual' => 1000000,
                'ukuran_barang' => '3Kg',
                'merek_barang' => 'Chubb',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'created_by_role' => 'staff_gudang',
                'created_by_id' => '2',
                'nama_barang' => 'APAR Powder 6Kg',
                'jumlah_barang' => 7,
                'tipe_barang' => 'APAR',
                'berat_barang' => 9.0,
                'satuan' => 'unit',
                'kondisi' => 'perlu cek',
                'harga_beli' => 950000,
                'harga_jual' => 1150000,
                'ukuran_barang' => '6Kg',
                'merek_barang' => 'Yamato',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'created_by_role' => 'staff_gudang',
                'created_by_id' => '2',
                'nama_barang' => 'Hydrant Valve',
                'jumlah_barang' => 3,
                'tipe_barang' => 'HYDRANT',
                'berat_barang' => 6.2,
                'satuan' => 'unit',
                'kondisi' => 'bagus',
                'harga_beli' => 700000,
                'harga_jual' => 850000,
                'ukuran_barang' => '2.5 inch',
                'merek_barang' => 'AVK',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'created_by_role' => 'staff_gudang',
                'created_by_id' => '2',
                'nama_barang' => 'Hydrant Box',
                'jumlah_barang' => 2,
                'tipe_barang' => 'HYDRANT',
                'berat_barang' => 20.0,
                'satuan' => 'unit',
                'kondisi' => 'perlu cek',
                'harga_beli' => 1200000,
                'harga_jual' => 1400000,
                'ukuran_barang' => 'standar',
                'merek_barang' => 'Crown',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
