<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FormConfig extends Model
{
    use HasFactory;

    protected $table = 'master_data';

    // Tidak perlu created_at dan updated_at jika Anda tidak menggunakannya
    public $timestamps = false;
}
