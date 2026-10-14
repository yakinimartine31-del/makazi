<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Area extends Model
{
    protected $fillable = ['ward_id', 'name', 'description'];

    public function ward(): BelongsTo
    {
        return $this->belongsTo(Ward::class);
    }
}
