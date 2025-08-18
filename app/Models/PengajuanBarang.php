<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengajuanBarang extends Model
{
    protected $table = 'pengajuan_barangs';
    protected $primaryKey = 'id';

    protected $fillable = [
        'report_id',
        'id_barang',
        'nama_laporan',
        'nama_barang',
        'tipe_barang_kategori',
        'tipe_barang',
        'jenis_barang',
        'jumlah_barang',
        'media',
        'satuan',
        'kondisi_barang',
        'berat',
        'ukuran_barang',
        'panjang',
        'lebar',
        'tinggi',
        'merek',
        'status',
        'catatan_penolakan',
        'created_by_id', 
        'created_by_role',
    ];

    public function barang() {
        return $this->belongsTo(Barang::class, 'id_barang', 'id_barang');
    }
}
