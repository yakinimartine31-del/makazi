<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'rental_purpose' => $this->rental_purpose,
            'monthly_rent' => $this->monthly_rent,
            'deposit' => $this->deposit,
            'payment_frequency' => $this->payment_frequency,
            'negotiable' => $this->negotiable,
            'additional_charges' => $this->additional_charges,
            'room_size' => $this->room_size,
            'bedrooms' => $this->bedrooms,
            'bathroom_type' => $this->bathroom_type,
            'bathroom_sharing' => $this->bathroom_sharing,
            'kitchen_availability' => $this->kitchen_availability,
            'indoor_bathroom' => $this->indoor_bathroom,
            'other_features' => $this->other_features,
            'listing_status' => $this->listing_status,
            'payment_status' => $this->payment_status,
            'published_at' => $this->published_at,
            'room_type' => new RoomTypeResource($this->whenLoaded('roomType')),
            'region' => new RegionResource($this->whenLoaded('region')),
            'district' => new DistrictResource($this->whenLoaded('district')),
            'ward' => new WardResource($this->whenLoaded('ward')),
            'area' => new AreaResource($this->whenLoaded('area')),
            'cover_image' => new RoomImageResource($this->whenLoaded('coverImage')),
            'images' => RoomImageResource::collection($this->whenLoaded('images')),
            'amenities' => AmenityResource::collection($this->whenLoaded('amenities')),
            'owner' => new UserResource($this->whenLoaded('owner')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
