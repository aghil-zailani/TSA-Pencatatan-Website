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
        'jumlah_barang',
        'satuan',
        'kondisi_barang',
        'berat',
        'tanggal_kadaluarsa',
        'ukuran_barang',
        'panjang',
        'lebar',
        'tinggi',
        'merek',
        'status',
        'catatan_penolakan'
    ];

    public function barang() {
        return $this->belongsTo(Barang::class, 'id_barang', 'id_barang');
    }
}
