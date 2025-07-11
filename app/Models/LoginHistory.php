<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'login_at',
    ];

    // Menonaktifkan updated_at karena kita hanya perlu login_at
    const UPDATED_AT = null;
    const CREATED_AT = 'login_at'; // Menggunakan login_at sebagai timestamp pembuatan

    // Relasi ke model User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}