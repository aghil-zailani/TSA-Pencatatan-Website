<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stok extends Model
{
    use HasFactory;

    protected $table = 'stoks';

    protected $fillable = ['namaBarang', 'stokBarang', 'type', 'deskripsi', 'berat', 'harga', 'jumlahStok', 'tanggal'];

    const CREATED_AT = 'created_at';
    const UPDATE_AT = 'updated_at';
}
