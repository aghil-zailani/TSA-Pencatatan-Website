<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pengajuan extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terhubung dengan model ini.
     * Perlu didefinisikan karena nama tabel 'pengajuan' (singular) tidak mengikuti konvensi Laravel (plural 'pengajuans').
     *
     * @var string
     */
    protected $table = 'pengajuan';

    /**
     * Primary key untuk model ini.
     * Perlu didefinisikan karena nama primary key bukan 'id'.
     *
     * @var string
     */
    protected $primaryKey = 'id_pengajuan';

    /**
     * Atribut yang diizinkan untuk diisi secara massal (mass assignment).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_barang',
        'status',
        'jumlah_diperbaiki',
        'tanggal_mulai',
        'tanggal_selesai',
    ];

    /**
     * Atribut yang harus di-cast ke tipe data tertentu.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'jumlah_diperbaiki' => 'integer',
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    /**
     * Mendefinisikan relasi "belongsTo" ke model Barang.
     * Satu pengajuan dimiliki oleh satu barang.
     */
    public function barang(): BelongsTo
    {
        // Parameter kedua adalah foreign key di tabel 'pengajuan' (id_barang).
        // Parameter ketiga adalah owner key (primary key) di tabel 'barangs' (id).
        // Pastikan nama kolom PK di tabel 'barangs' Anda sudah benar.
        return $this->belongsTo(Barang::class, 'id_barang', 'id');
    }
}