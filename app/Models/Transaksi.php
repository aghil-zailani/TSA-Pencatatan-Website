<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    protected $table = 'transaksis';

    protected $primaryKey = 'id_transaksi';
    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = true; 

    protected $fillable = [
        'report_id',
        'id_barang',
        'jumlah_barang',
        'tujuan',           
        'keterangan',       
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
