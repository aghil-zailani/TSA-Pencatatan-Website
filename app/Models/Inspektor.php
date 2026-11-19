<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inspektor extends Model
{
    protected $table = 'inspektors';

    protected $primaryKey = 'id_inspektor';

    protected $fillable = [
        'id_supervisor',
        'nama',
    ];

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_supervisor');
    }
}
