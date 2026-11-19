<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Menentukan nama tabel yang digunakan oleh model ini.
     * Defaultnya adalah 'users', kita ubah menjadi 'user'.
     */
    protected $table = 'users';

    /**
     * Primary key untuk tabel ini.
     * Defaultnya adalah 'id', jadi ini tidak wajib diubah jika nama kolomnya 'id'.
     */
    protected $primaryKey = 'id';

    /**
     * Atribut yang diizinkan untuk diisi secara massal.
     */
    protected $fillable = [
        'id',
        'username',
        'email',
        'password',
        'role',
        'nama_instansi',
        'lokasi_instansi',
        'foto_instansi',
        'no_hp',
        'supervisor_umum_id',
    ];

    /**
     * Atribut yang harus disembunyikan saat serialisasi (misal, saat diubah ke JSON).
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Atribut yang harus di-cast ke tipe data tertentu.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function inspectors()
    {
        return $this->hasMany(self::class, 'supervisor_umum_id');
    }

    public function inspektors()
    {
        return $this->hasMany(Inspektor::class, 'id_supervisor');
    }

    public function supervisor()
    {
        return $this->belongsTo(self::class, 'supervisor_umum_id');
    }
}
