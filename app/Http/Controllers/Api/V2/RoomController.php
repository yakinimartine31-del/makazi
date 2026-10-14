<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\RoomResource;
use App\Models\Amenity;
use App\Models\Room;
use App\Models\RoomImage;
use App\Models\RoomType;
use App\Services\ListingFeeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RoomController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Room::with(['roomType', 'region', 'district', 'coverImage', 'amenities'])
            ->where('listing_status', 'published');

        if ($request->filled('location')) {
            $query->where(function ($q) use ($request) {
                $location = $request->location;
                $q->whereHas('region', fn($q) => $q->where('name', 'like', "%$location%"))
                  ->orWhereHas('district', fn($q) => $q->where('name', 'like', "%$location%"))
                  ->orWhereHas('ward', fn($q) => $q->where('name', 'like', "%$location%"))
                  ->orWhereHas('area', fn($q) => $q->where('name', 'like', "%$location%"));
            });
        }

        if ($request->filled('min_price')) {
            $query->where('monthly_rent', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('monthly_rent', '<=', $request->max_price);
        }

        if ($request->filled('room_type_id')) {
            $query->where('room_type_id', $request->room_type_id);
        }

        if ($request->filled('bathroom_type')) {
            $query->where('bathroom_type', $request->bathroom_type);
        }

        if ($request->filled('furnish_status')) {
            $query->where('furnish_status', $request->furnish_status);
        }

        if ($request->filled('amenity_id')) {
            $query->whereHas('amenities', fn($q) => $q->where('amenities.id', $request->amenity_id));
        }

        $rooms = $query->latest()->paginate($request->get('per_page', 12));

        return response()->json([
            'data' => RoomResource::collection($rooms),
            'meta' => [
                'current_page' => $rooms->currentPage(),
                'last_page' => $rooms->lastPage(),
                'per_page' => $rooms->perPage(),
                'total' => $rooms->total(),
            ],
        ]);
    }

    public function show(Room $room): JsonResponse
    {
        if (!$room->isPubliclyVisible()) {
            return response()->json(['message' => 'Room not found'], 404);
        }

        return response()->json([
            'data' => new RoomResource($room->load(['roomType', 'region', 'district', 'ward', 'area', 'coverImage', 'images', 'amenities'])),
        ]);
    }

    public function store(Request $request, ListingFeeService $listingFeeService): JsonResponse
    {
        $user = $request->user();

        if (!$user->isOwner() && !$user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'room_type_id' => 'required|exists:room_types,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'rental_purpose' => 'nullable|string|max:100',
            'room_size' => 'nullable|string|max:50',
            'bedrooms' => 'nullable|integer',
            'bathroom_type' => 'nullable|string|max:100',
            'bathroom_sharing' => 'nullable|boolean',
            'kitchen_availability' => 'nullable|string|max:100',
            'indoor_bathroom' => 'nullable|boolean',
            'other_features' => 'nullable|string',
            'region_id' => 'required|exists:regions,id',
            'district_id' => 'required|exists:districts,id',
            'ward_id' => 'nullable|exists:wards,id',
            'area_id' => 'nullable|exists:areas,id',
            'landmark' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'monthly_rent' => 'required|numeric|min:0',
            'deposit' => 'nullable|numeric|min:0',
            'payment_frequency' => 'nullable|string|max:50',
            'minimum_rental_period' => 'nullable|integer',
            'negotiable' => 'nullable|boolean',
            'additional_charges' => 'nullable|string',
            'amenities' => 'nullable|array',
            'amenities.*' => 'exists:amenities,id',
             'images' => 'nullable|array',
              'images.*' => 'file|mimes:jpeg,png,jpg,gif,avif,mp4,webm,ogg|max:2048',
        ]);

        $room = DB::transaction(function () use ($validated, $user, $listingFeeService, $request) {
            $room = Room::create(array_merge($validated, [
                'owner_id' => $user->id,
                'created_by' => $user->id,
                'created_by_role' => $user->hasRole('admin') ? 'admin' : 'owner',
                'listing_status' => 'draft',
                'payment_status' => 'unpaid',
                'fee_exempt' => $user->isAdmin(),
            ]));

            if (!empty($validated['amenities'])) {
                $room->amenities()->sync($validated['amenities']);
            }

            if (!empty($request->file('images'))) {
                foreach ($request->file('images') as $index => $image) {
                    $path = $image->store('room-images', 'public');
                    RoomImage::create([
                        'room_id' => $room->id,
                        'image_path' => $path,
                        'disk' => 'public',
                        'is_cover' => $index === 0,
                        'sort_order' => $index,
                    ]);
                }
            }

            $listingFeeService->chargeForOwner($room);

            return $room->load(['roomType', 'region', 'district', 'coverImage', 'amenities']);
        });

        return response()->json([
            'data' => new RoomResource($room),
            'message' => 'Room created successfully',
        ], 201);
    }

    public function update(Request $request, Room $room): JsonResponse
    {
        $user = $request->user();

        if ($room->owner_id !== $user->id && !$user->isAdmin()) {
            return response()->json(['message' => 'You can only edit your own listings'], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'monthly_rent' => 'sometimes|required|numeric|min:0',
            'listing_status' => 'sometimes|required|in:draft,pending_payment,pending_approval,published,rejected,suspended,rented,unavailable',
        ]);

        $room->update($validated);

        return response()->json([
            'data' => new RoomResource($room),
            'message' => 'Room updated successfully',
        ]);
    }

    public function destroy(Request $request, Room $room): JsonResponse
    {
        $user = $request->user();

        if ($room->owner_id !== $user->id && !$user->isAdmin()) {
            return response()->json(['message' => 'You can only delete your own listings'], 403);
            }

        $room->delete();

        return response()->json([
            'message' => 'Room deleted successfully',
        ]);
    }
}
