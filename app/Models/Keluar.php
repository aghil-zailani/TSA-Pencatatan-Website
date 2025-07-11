<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Keluar extends Model
{
    use HasFactory;

    protected $table = 'transaksis';

    protected $primaryKey = 'id_transaksi';

    public $incrementing = true; // kalau auto increment
    protected $keyType = 'int';

    protected $fillable = ['id_barang','jumlah_barang','tujuan','keterangan'];

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'id_barang', 'id_barang');
    }
}
