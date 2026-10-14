<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Region;
use App\Models\Role;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use App\Services\ListingPerformanceTracker;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request, ListingPerformanceTracker $performanceTracker): View
    {
        $validated = $request->validate([
            'location' => ['nullable', 'string', 'max:255'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0', 'gte:min_price'],
            'room_type' => ['nullable', 'string', 'exists:room_types,slug'],
            'property_type' => ['nullable', 'in:room,house,residential_plot,commercial_plot,farm,commercial_space,office'],
            'transaction_type' => ['nullable', 'in:rent,sale'],
            'installment' => ['nullable', 'boolean'],
        ]);

        $regions = Region::orderBy('name')->get();
        $published = fn ($query) => $query->where('listing_status', 'published');
        $listingCount = Room::where($published)->count();
        $ownerRoleId = Role::where('name', 'owner')->value('id');
        $ownerCount = $ownerRoleId
            ? User::whereHas('roles', fn ($query) => $query->whereKey($ownerRoleId))
                ->whereHas('ownedRooms', $published)
                ->count()
            : 0;
        $roomTypes = RoomType::orderBy('name')->get();
        $listingRelations = ['region', 'district', 'roomType', 'images'];
        $hasSearchFilters = collect($validated)->contains(fn ($value) => $value !== null && $value !== '');
        $searchResults = null;

        if ($hasSearchFilters) {
            $location = trim($validated['location'] ?? '');
            $searchResults = Room::with($listingRelations)
                ->where($published)
                ->when($validated['property_type'] ?? null, fn ($query, $type) => $query->where('property_type', $type))
                ->when($validated['transaction_type'] ?? null, fn ($query, $type) => $query->where('transaction_type', $type))
                ->when($validated['installment'] ?? false, fn ($query) => $query->where('installment_allowed', true))
                ->when($location !== '', function ($query) use ($location): void {
                    $query->where(function ($query) use ($location): void {
                        $query->where('title', 'like', "%{$location}%")
                            ->orWhere('landmark', 'like', "%{$location}%")
                            ->orWhereHas('region', fn ($query) => $query->where('name', 'like', "%{$location}%"))
                            ->orWhereHas('district', fn ($query) => $query->where('name', 'like', "%{$location}%"));
                    });
                })
                ->when(isset($validated['min_price']) || isset($validated['max_price']), function ($query) use ($validated): void {
                    $applyPriceRange = function ($query, string $column) use ($validated): void {
                        $query->when(isset($validated['min_price']), fn ($query) => $query->where($column, '>=', $validated['min_price']))
                            ->when(isset($validated['max_price']), fn ($query) => $query->where($column, '<=', $validated['max_price']));
                    };

                    if (isset($validated['transaction_type'])) {
                        $applyPriceRange($query, $validated['transaction_type'] === 'sale' ? 'sale_price' : 'monthly_rent');
                    } else {
                        $query->where(function ($query) use ($applyPriceRange): void {
                            $query->where(fn ($query) => $applyPriceRange($query, 'monthly_rent'))
                                ->orWhere(fn ($query) => $applyPriceRange($query, 'sale_price'));
                        });
                    }
                })
                ->when($validated['room_type'] ?? null, fn ($query, $slug) => $query->whereHas(
                    'roomType',
                    fn ($query) => $query->where('slug', $slug)
                ))
                ->latest()
                ->paginate(12)
                ->withQueryString();
        }

        $featuredListings = Room::with($listingRelations)
            ->where($published)
            ->latest()
            ->limit(4)
            ->get();
        $recentListings = Room::with($listingRelations)
            ->where($published)
            ->latest()
            ->skip(4)
            ->limit(4)
            ->get();
        $visibleListings = $searchResults
            ? $searchResults->getCollection()
            : $featuredListings->concat($recentListings);
        foreach ($visibleListings->unique('id') as $listing) {
            $performanceTracker->record($listing, 'impression', $request);
        }
        $popularRegions = Region::withCount(['rooms' => $published])
            ->orderByDesc('rooms_count')
            ->orderBy('name')
            ->limit(8)
            ->get();
        $activeOwners = $ownerRoleId
            ? User::whereHas('roles', fn ($query) => $query->whereKey($ownerRoleId))
                ->withCount(['ownedRooms as published_rooms_count' => $published])
                ->whereHas('ownedRooms', $published)
                ->orderByDesc('published_rooms_count')
                ->limit(6)
                ->get()
            : collect();
        $rentGuides = Room::query()
            ->where($published)
            ->where('transaction_type', 'rent')
            ->join('regions', 'rooms.region_id', '=', 'regions.id')
            ->selectRaw('regions.name as region_name, COUNT(*) as listing_count, MIN(rooms.monthly_rent) as min_rent, MAX(rooms.monthly_rent) as max_rent, AVG(rooms.monthly_rent) as average_rent')
            ->groupBy('regions.name')
            ->orderByDesc('listing_count')
            ->limit(4)
            ->get();

        return view('home.index', compact(
            'regions',
            'listingCount',
            'ownerCount',
            'roomTypes',
            'searchResults',
            'hasSearchFilters',
            'featuredListings',
            'recentListings',
            'popularRegions',
            'activeOwners',
            'rentGuides'
        ));
    }
}
