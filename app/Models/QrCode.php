<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Barang;

class QrCode extends Model
{
    use HasFactory;

    protected $table = 'qr_codes';
    protected $primaryKey = 'id_qr_code';
    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = true; 

    protected $fillable = [
        'id_barang',          
        'nomor_identifikasi', 
        'qr_code_path',       
        'tanggal_pembuatan',  
        'status_qr',
    ];

    protected $casts = [
        'tanggal_pembuatan' => 'datetime',
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'id_barang', 'id_barang');
    }

    const STATUS_AKTIF = 'Aktif';
    const STATUS_TIDAK_AKTIF = 'Tidak Aktif';
}
