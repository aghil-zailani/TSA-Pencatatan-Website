<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Keluar extends Model
{
    use HasFactory;

    protected $table = 'transaksis';

    protected $primaryKey = 'id_transaksi'; // pakai id_transaksi kalau mau akses detail, TAPI untuk group by, sebaiknya abaikan primaryKey

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'report_id',
        'id_barang',
        'jumlah_barang',
        'tujuan',
        'keterangan',
        'status',
        'catatan_penolakan'
    ];

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'id_barang', 'id_barang');
    }

    public function details()
    {
        return $this->hasMany(Transaksi::class, 'report_id', 'report_id');
    }
}
