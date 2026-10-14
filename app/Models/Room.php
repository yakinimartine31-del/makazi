<?php

namespace App\Models;

use App\Enums\ListingStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    protected $fillable = [
        'owner_id', 'room_type_id', 'property_type', 'transaction_type', 'sale_price',
        'installment_allowed', 'title', 'description', 'rental_purpose',
        'room_size', 'bedrooms', 'bathroom_type', 'bathroom_sharing',
        'kitchen_availability', 'indoor_bathroom', 'other_features',
        'region_id', 'district_id', 'ward_id', 'area_id', 'landmark',
        'latitude', 'longitude', 'monthly_rent', 'deposit', 'payment_frequency',
        'minimum_rental_period', 'negotiable', 'additional_charges',
        'contact_name', 'preferred_contact_method', 'contact_phone', 'whatsapp_phone',
        'listing_status', 'payment_status', 'fee_exempt',
        'created_by', 'created_by_role', 'published_at', 'approved_at',
        'rejected_at', 'rejection_reason',
    ];

    protected $casts = [
        'monthly_rent' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'deposit' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'bathroom_sharing' => 'boolean',
        'indoor_bathroom' => 'boolean',
        'negotiable' => 'boolean',
        'fee_exempt' => 'boolean',
        'installment_allowed' => 'boolean',
        'published_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function ward(): BelongsTo
    {
        return $this->belongsTo(Ward::class);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(RoomImage::class);
    }

    public function coverImage(): BelongsTo
    {
        return $this->belongsTo(RoomImage::class)->where('is_cover', true);
    }

    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(Amenity::class, 'room_amenity');
    }

    public function listingFee(): HasMany
    {
        return $this->hasMany(ListingFee::class);
    }

    public function latestFee()
    {
        return $this->hasOne(ListingFee::class)->latestOfMany();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function latestPayment()
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function getLatestPaymentStatusAttribute(): ?string
    {
        $payment = $this->relationLoaded('latestPayment') ? $this->latestPayment : $this->latestPayment()->first();

        return $payment?->status?->label();
    }

    public function enquiries(): HasMany
    {
        return $this->hasMany(Enquiry::class);
    }

    public function viewingRequests(): HasMany
    {
        return $this->hasMany(ViewingRequest::class);
    }

    public function scopePublished($query)
    {
        return $query->where('listing_status', 'published');
    }

    public function scopeByOwner($query, int $ownerId)
    {
        return $query->where('owner_id', $ownerId);
    }

    public function getListingPriceAttribute(): ?string
    {
        return $this->transaction_type === 'sale' ? $this->sale_price : $this->monthly_rent;
    }

    public function getPropertyTypeLabelAttribute(): string
    {
        return match ($this->property_type) {
            'room' => 'Room',
            'house' => 'House',
            'residential_plot' => 'Residential plot',
            'commercial_plot' => 'Commercial plot',
            'farm' => 'Farm',
            'commercial_space' => 'Commercial space',
            'office' => 'Office',
            default => ucfirst(str_replace('_', ' ', $this->property_type ?? 'property')),
        };
    }

    public function isPubliclyVisible(): bool
    {
        return ListingStatus::from($this->listing_status)->isPubliclyVisible();
    }
}
