<?php

namespace App\Http\Controllers\Owner;

use App\Enums\ListingStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StoreListingRequest;
use App\Http\Requests\Owner\UpdateListingRequest;
use App\Models\Amenity;
use App\Models\Area;
use App\Models\District;
use App\Models\Region;
use App\Models\Room;
use App\Models\RoomImage;
use App\Models\RoomType;
use App\Models\Ward;
use App\Services\ListingFeeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ListingController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified', 'role:owner,admin']);
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        $rooms = Room::with(['roomType', 'region', 'district', 'coverImage', 'images', 'latestFee', 'latestPayment'])
            ->where('owner_id', $user->id)
            ->latest()
            ->paginate(10)
            ->through(function (Room $room) {
                $room->append('latest_payment_status');

                return $room;
            });

        return view('owner.listings.index', compact('rooms'));
    }

    public function create(Request $request): View
    {
        $room = new Room;
        $roomTypes = RoomType::orderBy('name')->get();
        $regions = Region::orderBy('name')->get();
        $districts = District::with('region')->orderBy('name')->get();
        $wards = Ward::orderBy('name')->get();
        $areas = Area::orderBy('name')->get();
        $amenities = Amenity::orderBy('name')->orderBy('id')->get()
            ->unique(fn (Amenity $amenity) => mb_strtolower(trim($amenity->name)))
            ->values();
        $selectedAmenities = old('amenities', []);

        return view('owner.listings.create', compact('room', 'roomTypes', 'regions', 'districts', 'wards', 'areas', 'amenities', 'selectedAmenities'));
    }

    public function show(Request $request, Room $room): View
    {
        $this->authorizeRoom($request, $room);

        $room->load([
            'roomType',
            'region',
            'district',
            'ward',
            'area',
            'amenities',
            'images' => fn ($query) => $query->orderBy('sort_order'),
            'latestFee',
            'payments' => fn ($query) => $query->latest(),
        ]);

        $room->setRelation(
            'amenities',
            $room->amenities->unique(fn (Amenity $amenity) => mb_strtolower(trim($amenity->name)))->values()
        );
        $performance = DB::table('listing_performance_events')
            ->selectRaw('event_type, COUNT(*) as total')
            ->where('room_id', $room->id)
            ->groupBy('event_type')
            ->pluck('total', 'event_type');
        $performance = [
            'impression' => (int) ($performance['impression'] ?? 0),
            'view' => (int) ($performance['view'] ?? 0),
            'click' => (int) ($performance['click'] ?? 0),
            'like' => DB::table('listing_likes')->where('room_id', $room->id)->count(),
            'whatsapp_click' => (int) ($performance['whatsapp_click'] ?? 0),
            'call_click' => (int) ($performance['call_click'] ?? 0),
            'instagram_click' => (int) ($performance['instagram_click'] ?? 0),
        ];

        return view('owner.listings.show', compact('room', 'performance'));
    }

    public function store(StoreListingRequest $request, ListingFeeService $listingFeeService): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $room = DB::transaction(function () use ($validated, $user, $request, $listingFeeService) {
            $room = Room::create(array_merge($validated, [
                'owner_id' => $user->id,
                'created_by' => $user->id,
                'created_by_role' => $user->hasRole('admin') ? 'admin' : 'owner',
                'listing_status' => 'draft',
                'payment_status' => 'unpaid',
                'fee_exempt' => $user->isAdmin(),
            ]));

            if (! empty($validated['amenities'] ?? null)) {
                $room->amenities()->sync($validated['amenities']);
            }

            if (! empty($request->file('images'))) {
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

        return redirect()->route('owner.listings.index')
            ->with('status', 'Listing saved as a draft. Pay the listing fee, then submit it for approval.');
    }

    public function submitForApproval(Request $request, Room $room): RedirectResponse
    {
        $this->authorizeRoom($request, $room);

        if (! in_array($room->listing_status, [ListingStatus::Draft->value, ListingStatus::Rejected->value], true)) {
            return back()->withErrors([
                'listing' => 'Only draft or rejected listings can be submitted for approval.',
            ]);
        }

        $fee = $room->listingFee()->latest()->first();
        if (! $fee || ! in_array($fee->status, [PaymentStatus::Paid, PaymentStatus::Waived], true)) {
            return back()->withErrors([
                'listing' => 'Pay the listing fee before submitting this listing for approval.',
            ]);
        }

        $room->update([
            'listing_status' => ListingStatus::PendingApproval,
            'rejected_at' => null,
            'rejection_reason' => null,
        ]);

        return redirect()->route('owner.listings.index')
            ->with('status', 'Listing submitted for admin approval. It will be published after approval.');
    }

    public function edit(Request $request, Room $room): View
    {
        $this->authorizeRoom($request, $room);

        $room->load(['roomType', 'region', 'district', 'ward', 'area', 'amenities', 'images']);
        $roomTypes = RoomType::orderBy('name')->get();
        $regions = Region::orderBy('name')->get();
        $districts = District::with('region')->orderBy('name')->get();
        $wards = Ward::orderBy('name')->get();
        $areas = Area::orderBy('name')->get();
        $amenities = Amenity::orderBy('name')->orderBy('id')->get()
            ->unique(fn (Amenity $amenity) => mb_strtolower(trim($amenity->name)))
            ->values();
        $selectedAmenities = old('amenities', $room->amenities->pluck('id')->toArray());

        return view('owner.listings.edit', compact('room', 'roomTypes', 'regions', 'districts', 'wards', 'areas', 'amenities', 'selectedAmenities'));
    }

    public function update(UpdateListingRequest $request, Room $room, ListingFeeService $listingFeeService): RedirectResponse
    {
        $this->authorizeRoom($request, $room);

        $validated = $request->validated();
        $transactionType = $validated['transaction_type'] ?? $room->transaction_type;
        $propertyType = $validated['property_type'] ?? $room->property_type;
        $nextPrice = $transactionType === 'sale'
            ? ($validated['sale_price'] ?? $room->sale_price)
            : ($validated['monthly_rent'] ?? $room->monthly_rent);
        $priceChanged = $transactionType !== $room->transaction_type
            || (float) $nextPrice !== (float) $room->listing_price;

        if ($propertyType !== 'room') {
            $validated['room_type_id'] = null;
        }

        if ($transactionType === 'sale') {
            $validated['monthly_rent'] = null;
            $validated['deposit'] = null;
        } else {
            $validated['sale_price'] = null;
            $validated['installment_allowed'] = false;
        }

        DB::transaction(function () use ($validated, $room, $request, $priceChanged, $listingFeeService) {
            $room->update($validated);
            if ($priceChanged) {
                $listingFeeService->repriceUnpaidFee($room);
            }

            if (array_key_exists('amenities', $validated)) {
                $room->amenities()->sync($validated['amenities'] ?? []);
            }

            if (! empty($request->file('images'))) {
                $existing = $room->images()->count();
                foreach ($request->file('images') as $index => $image) {
                    $path = $image->store('room-images', 'public');
                    RoomImage::create([
                        'room_id' => $room->id,
                        'image_path' => $path,
                        'disk' => 'public',
                        'is_cover' => $existing === 0 && $index === 0,
                        'sort_order' => $existing + $index,
                    ]);
                }
            }
        });

        return redirect()->route('owner.listings.edit', $room)
            ->with('status', 'Listing updated successfully.');
    }

    public function destroy(Request $request, Room $room): RedirectResponse
    {
        $this->authorizeRoom($request, $room);

        $room->delete();

        return redirect()->route('owner.listings.index')
            ->with('status', 'Listing deleted successfully.');
    }

    protected function authorizeRoom(Request $request, Room $room): void
    {
        $user = $request->user();
        if ($room->owner_id !== $user->id && ! $user->isAdmin()) {
            abort(403, 'You can only manage your own listings.');
        }
    }
}
