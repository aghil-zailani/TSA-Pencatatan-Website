<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pengajuan extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'pengajuan';

    /**
     * @var string
     */
    protected $primaryKey = 'id_pengajuan';

    /**
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
     * @var array<string, string>
     */
    protected $casts = [
        'jumlah_diperbaiki' => 'integer',
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'id_barang', 'id');
    }
}