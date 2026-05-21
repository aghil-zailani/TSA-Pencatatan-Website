<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $now = Carbon::now();

        $data = [
            // --- Kategori: APAR ---
            [
                'category'      => 'APAR',
                'label_display' => 'Nama Barang',
                'input_type'    => 'text',
                'field_order'   => 1,
                'is_required'   => 1, 
                'value'         => 'nama_barang',
                'is_active'     => 1,
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'category'      => 'APAR',
                'label_display' => 'Tipe Barang',
                'input_type'    => 'text',
                'field_order'   => 2,
                'is_required'   => 1,
                'value'         => 'tipe_barang',
                'is_active'     => 1,
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'category'      => 'APAR',
                'label_display' => 'Jumlah Barang',
                'input_type'    => 'number',
                'field_order'   => 3,
                'is_required'   => 1,
                'value'         => 'jumlah_barang',
                'is_active'     => 1,
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'category'      => 'APAR',
                'label_display' => 'Jenis Barang',
                'input_type'    => 'text',
                'field_order'   => 4,
                'is_required'   => 1,
                'value'         => 'jenis_barang',
                'is_active'     => 1,
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'category'      => 'APAR',
                'label_display' => 'Kondisi',
                'input_type'    => 'text',
                'field_order'   => 5,
                'is_required'   => 1,
                'value'         => 'kondisi',
                'is_active'     => 1,
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'category'      => 'APAR',
                'label_display' => 'Media',
                'input_type'    => 'text',
                'field_order'   => 6,
                'is_required'   => 1,
                'value'         => 'media',
                'is_active'     => 1,
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'category'      => 'APAR',
                'label_display' => 'Berat',
                'input_type'    => 'text',
                'field_order'   => 7,
                'is_required'   => 1,
                'value'         => 'berat',
                'is_active'     => 1,
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'category'      => 'APAR',
                'label_display' => 'Satuan',
                'input_type'    => 'text',
                'field_order'   => 8,
                'is_required'   => 1,
                'value'         => 'satuan',
                'is_active'     => 1,
                'created_at'    => $now,
                'updated_at'    => $now,
            ],

            // --- Kategori: Sparepart ---
            [
                'category'      => 'Sparepart',
                'label_display' => 'Nama Barang',
                'input_type'    => 'text',
                'field_order'   => 1,
                'is_required'   => 0,
                'value'         => 'nama_barang',
                'is_active'     => 1,
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'category'      => 'Sparepart',
                'label_display' => 'Tipe Barang',
                'input_type'    => 'text',
                'field_order'   => 2,
                'is_required'   => 1,
                'value'         => 'tipe_barang',
                'is_active'     => 1,
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'category'      => 'Sparepart',
                'label_display' => 'Jumlah Barang',
                'input_type'    => 'text',
                'field_order'   => 3,
                'is_required'   => 1,
                'value'         => 'jumlah_barang',
                'is_active'     => 1,
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'category'      => 'Sparepart',
                'label_display' => 'Ukuran Barang',
                'input_type'    => 'text',
                'field_order'   => 4,
                'is_required'   => 1,
                'value'         => 'ukuran_barang',
                'is_active'     => 1,
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'category'      => 'Sparepart',
                'label_display' => 'Satuan',
                'input_type'    => 'text',
                'field_order'   => 5,
                'is_required'   => 1,
                'value'         => 'satuan',
                'is_active'     => 1,
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'category'      => 'Sparepart',
                'label_display' => 'Kondisi',
                'input_type'    => 'text',
                'field_order'   => 6,
                'is_required'   => 1,
                'value'         => 'kondisi',
                'is_active'     => 1,
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
        ];

        DB::table('master_data')->insert($data);
    }
}