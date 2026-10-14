<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ViewingRequest extends Model
{
    protected $fillable = [
        'room_id', 'enquiry_id', 'name', 'email', 'phone',
        'preferred_date', 'preferred_time', 'instructions', 'status',
    ];

    protected $casts = [
        'preferred_date' => 'date',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function enquiry(): BelongsTo
    {
        return $this->belongsTo(Enquiry::class);
    }
}
