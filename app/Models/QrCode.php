<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Barang;

class QrCode extends Model
{
    use HasFactory;

    protected $table = 'qr_codes'; // Nama tabel jamak
    protected $primaryKey = 'id_qr_code'; // Sesuai ERD
    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = true; // Jika ada created_at, updated_at di tabel qr_code

    protected $fillable = [
        'id_barang',          // Foreign Key ke tabel barang
        'nomor_identifikasi', // Data yang akan dipindai dari QR Code
        'qr_code_path',       // Path ke gambar QR code (opsional, jika disimpan)
        'tanggal_pembuatan',  // Tanggal pembuatan QR
    ];

    protected $casts = [
        'tanggal_pembuatan' => 'datetime',
    ];

    // Relasi ke model Barang
    public function barang()
    {
        return $this->belongsTo(Barang::class, 'id_barang', 'id_barang');
    }
}
