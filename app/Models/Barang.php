<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Barang extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'barangs'; // Atau nama tabel Anda jika berbeda (misalnya, 'barang' jika tidak jamak)

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id_barang'; // Sesuai dengan ERD Anda

    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true; // Jika id_barang adalah auto-increment

    /**
     * The data type of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'int'; // atau 'bigint' jika Anda menggunakan bigIncrements di migrasi

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true; // Asumsi Anda menggunakan created_at dan updated_at

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama_barang',
        'jumlah_barang',
        'tipe_barang',
        'berat_barang',
        'satuan',
        'kondisi',
        'harga_beli',
        'harga_jual',
        'ukuran_barang',
        'merek_barang',
        // 'deskripsi' tidak ada di ERD Anda, hapus jika tidak diperlukan
        // atau tambahkan kolom 'deskripsi' ke migrasi tabel barang jika memang ada
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'harga_beli' => 'decimal:2', // Contoh jika Anda ingin menyimpan harga dengan 2 angka desimal
        'harga_jual' => 'decimal:2', // Sesuaikan dengan tipe data di database Anda
        'jumlah_barang' => 'integer',
        'berat_barang' => 'decimal:2', // Contoh, sesuaikan dengan kebutuhan
    ];

    // Jika Anda memiliki relasi dengan model lain, definisikan di sini
    // Contoh relasi ke tabel transaksi (barang keluar)
    // public function transaksis()
    // {
    //     return $this->hasMany(Transaksi::class, 'id_barang', 'id_barang');
    //     // Ganti Transaksi::class dengan nama model transaksi Anda
    // }
}