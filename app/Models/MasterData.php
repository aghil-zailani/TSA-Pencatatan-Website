<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterData extends Model
{
    use HasFactory;

    // Tentukan nama tabel jika tidak mengikuti konvensi Laravel (plural dari nama model)
    protected $table = 'master_data';

    // Tentukan kolom-kolom yang boleh diisi secara massal (mass assignable)
    protected $fillable = [
        'category',
        'value',
        'is_active',
    ];

    // Tentukan tipe data untuk kolom-kolom tertentu (opsional, tapi bagus untuk boolean)
    protected $casts = [
        'is_active' => 'boolean',
    ];
}