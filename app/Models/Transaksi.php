<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    protected $table = 'transaksis';

    protected $primaryKey = 'id_transaksi';

    public $timestamps = true; 

    protected $fillable = [
        'id_barang',
        'jumlah_barang',
        'status',
        'catatan_penolakan',
        'created_at',
        'updated_at'
    ];

    // Contoh relasi (jika ada)
    public function barang()
    {
        return $this->belongsTo(Barang::class, 'id_barang');
    }

}
