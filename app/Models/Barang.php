<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Barang extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_barang';

    protected $fillable = [
        'nama_barang',
        'jumlah_barang',
        'tipe_barang',
        'tipe_barang_kategori',
        'berat_barang',
        'satuan',
        'kondisi',
        'status',
        'harga_beli',
        'harga_jual',
        'ukuran_barang',
        'panjang',
        'lebar',
        'tinggi',
        'tanggal_kadaluarsa',
        'merek_barang',
    ];

    protected $casts = [
        'jumlah_barang' => 'integer',
        'harga_beli' => 'decimal:2',
        'harga_jual' => 'decimal:2',
        'berat_barang' => 'decimal:2',
    ];

    public function qrCodes()
    {
        return $this->hasMany(QrCode::class, 'id_barang', 'id_barang');
    }

}