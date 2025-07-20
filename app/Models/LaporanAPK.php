<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaporanAPK extends Model // Nama model diubah
{
    use HasFactory;

    protected $table = 'laporan_apk'; // Nama tabel diubah
    protected $primaryKey = 'id_laporan_pemeliharaan'; // Sesuai ERD Anda
    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = true;

    protected $fillable = [
        'id_qr',
        'id_barang',
        'id_user',
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
        // 'timeline' di ERD bisa jadi fitur terpisah atau bagian dari status/logs
    ];

    protected $casts = [
        'tanggal_inspeksi' => 'datetime',
    ];

    // Relasi ke model Barang
    public function barang()
    {
        return $this->belongsTo(Barang::class, 'id_barang', 'id_barang');
    }

    // Relasi ke model User
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }

    // Relasi ke model QrCode
    public function qrCode()
    {
         return $this->belongsTo(QrCode::class, 'id_qr', 'nomor_identifikasi');
    }
}