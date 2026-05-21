<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaporanAPK extends Model 
{
    use HasFactory;

    protected $table = 'laporan_apk';
    protected $primaryKey = 'id_laporan_pemeliharaan'; 
    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = true;

    protected $fillable = [
        'id_qr',
        'id_barang',
        'id_user',
        'created_by_role',
        'username',
        'nama_barang',
        'tipe_barang',
        'tanggal_inspeksi',
        'lokasi_alat',
        'foto',
        'kondisi_fisik',
        'selang',
        'pressure_gauge',
        'safety_pin',
        'tindakan',
        'status',
        'catatan_validasi',
        'catatan_tindakan',
    ];

    protected $casts = [
        'tanggal_inspeksi' => 'datetime',
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'id_barang', 'id_barang');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }

    public function qrCode()
    {
         return $this->belongsTo(QrCode::class, 'id_qr', 'nomor_identifikasi');
    }
}
