<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListingFee extends Model
{
    protected $fillable = ['room_id', 'amount', 'fee_percentage', 'status'];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee_percentage' => 'decimal:2',
        'status' => PaymentStatus::class,
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
