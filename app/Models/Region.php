<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Region extends Model
{
    protected $fillable = ['name', 'description'];

    public function districts(): HasMany
    {
        return $this->hasMany(District::class);
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }
}
