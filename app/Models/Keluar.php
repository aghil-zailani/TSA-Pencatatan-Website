<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Keluar extends Model
{
    use HasFactory;

    protected $table = 'transaksis';

    protected $fillable = ['id_barang','jumlah_barang','tujuan','keterangan'];

    const CREATED_AT = 'created_at';
    const UPDATE_AT = 'updated_at';

    public function barang()
    {
        // Asumsi foreign key di tabel ini adalah 'id_barang' dan primary key di tabel 'barangs' adalah 'id_barang'
        // Sesuaikan jika nama kolom atau model berbeda
        return $this->belongsTo(Barang::class, 'id_barang', 'id_barang');
    }
}
