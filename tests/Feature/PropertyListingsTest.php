<?php

namespace Tests\Feature;

use App\Models\Amenity;
use App\Models\District;
use App\Models\Region;
use App\Models\Room;
use App\Models\RoomImage;
use App\Models\RoomType;
use App\Models\Setting;
use App\Models\User;
use App\Services\ListingFeeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PropertyListingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_room_search_filters_sale_properties_by_category_and_price(): void
    {
        $saleListing = $this->createListing([
            'title' => 'Residential Plot for Sale',
            'property_type' => 'residential_plot',
            'transaction_type' => 'sale',
            'sale_price' => 25_000_000,
            'monthly_rent' => null,
        ]);
        $this->createListing([
            'title' => 'Rental House',
            'property_type' => 'house',
            'transaction_type' => 'rent',
            'monthly_rent' => 500_000,
        ]);

        $this->get(route('home', [
            'property_type' => 'residential_plot',
            'transaction_type' => 'sale',
            'min_price' => 20_000_000,
        ]))
            ->assertOk()
            ->assertSee($saleListing->title)
            ->assertDontSee('Rental House')
            ->assertSee('25,000,000');

        $this->get(route('home', ['min_price' => 20_000_000]))
            ->assertOk()
            ->assertSee($saleListing->title)
            ->assertDontSee('Rental House');
    }

    public function test_homepage_search_url_filters_to_matching_bedsit_listings(): void
    {
        $bedsit = RoomType::create([
            'name' => 'Bedsit',
            'slug' => 'bedsit',
            'description' => 'A bedsit.',
        ]);
        $masterRoom = RoomType::create([
            'name' => 'Master Room',
            'slug' => 'master-room',
            'description' => 'A master room.',
        ]);

        $matchingListing = $this->createListing([
            'title' => 'Published bedsit match',
            'property_type' => 'room',
            'room_type_id' => $bedsit->id,
        ]);
        $this->createListing([
            'title' => 'Published master room mismatch',
            'property_type' => 'room',
            'room_type_id' => $masterRoom->id,
        ]);

        $this->get('/?location=&property_type=room&transaction_type=&room_type=bedsit&min_price=&max_price=')
            ->assertOk()
            ->assertSee('1 matangazo yamepatikana')
            ->assertSee($matchingListing->title)
            ->assertDontSee('Published master room mismatch');
    }

    public function test_published_listing_details_show_photos_features_contact_and_related_listings(): void
    {
        $listing = $this->createListing([
            'title' => 'Bright family house',
            'description' => 'A spacious home close to local shops.',
            'contact_name' => 'AURA Property Contact',
            'contact_phone' => '+255700123456',
            'whatsapp_phone' => '+255700123456',
            'bedrooms' => 3,
            'deposit' => 500_000,
            'latitude' => -6.7924,
            'longitude' => 39.2083,
        ]);
        $relatedListing = $this->createListing(['title' => 'Nearby family home']);
        $image = RoomImage::create([
            'room_id' => $listing->id,
            'image_path' => 'room-images/test-home.jpg',
            'is_cover' => true,
        ]);
        RoomImage::create([
            'room_id' => $listing->id,
            'image_path' => 'room-images/test-home-inside.jpg',
        ]);
        $amenity = Amenity::create(['name' => 'Water supply']);
        $listing->amenities()->attach($amenity);

        $this->get(route('listings.show', $listing))
            ->assertOk()
            ->assertSee('Bright family house')
            ->assertSee('AURA-'.str_pad((string) $listing->id, 6, '0', STR_PAD_LEFT))
            ->assertSee($image->url, false)
            ->assertSee('Next photo')
            ->assertSee('A spacious home close to local shops.')
            ->assertSee('Water supply')
            ->assertSee('AURA Property Contact')
            ->assertSee('+255700123456')
            ->assertSee('https://www.google.com/maps?q=-6.79240000%2C39.20830000&output=embed', false)
            ->assertSee('Fungua kwenye Google Maps')
            ->assertSee('Nearby family home')
            ->assertSee('Vyumba');

        $this->get(route('home'))
            ->assertOk()
            ->assertSee(route('listings.show', $listing), false);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee(route('listings.show', $listing), false);
    }

    public function test_listing_map_searches_by_address_when_coordinates_are_missing(): void
    {
        $listing = $this->createListing([
            'landmark' => 'Near the market',
        ]);

        $this->get(route('listings.show', $listing))
            ->assertOk()
            ->assertSee('https://www.google.com/maps?q=Near%20the%20market%2C%20Kinondoni%2C%20Dar%20es%20Salaam%2C%20Tanzania&output=embed', false);
    }

    public function test_unpublished_listing_detail_page_returns_not_found(): void
    {
        $listing = $this->createListing([
            'listing_status' => 'pending_approval',
        ]);

        $this->get(route('listings.show', $listing))->assertNotFound();
        $this->get('/rooms/'.$listing->id)->assertRedirect(route('home'));
    }

    public function test_listing_performance_tracks_unique_daily_impressions_views_and_lead_clicks(): void
    {
        $listing = $this->createListing([
            'contact_phone' => '+255700123456',
            'whatsapp_phone' => '+255700123456',
        ]);
        $this->actingAs(User::factory()->create());

        $this->get(route('home'))->assertOk();
        $this->get(route('home'))->assertOk();
        $this->get(route('listings.show', $listing))->assertOk();
        $this->get(route('listings.show', $listing))->assertOk();

        foreach (['click', 'whatsapp_click', 'call_click', 'instagram_click'] as $event) {
            $this->postJson(route('listings.performance.record', $listing), ['event' => $event])
                ->assertOk()
                ->assertJson(['recorded' => true]);
            $this->postJson(route('listings.performance.record', $listing), ['event' => $event])
                ->assertOk();
        }

        $this->assertDatabaseCount('listing_performance_events', 6);
        $this->assertDatabaseHas('listing_performance_events', [
            'room_id' => $listing->id,
            'event_type' => 'impression',
        ]);
        $this->assertDatabaseHas('listing_performance_events', [
            'room_id' => $listing->id,
            'event_type' => 'view',
        ]);
        $this->assertSame(1, DB::table('listing_performance_events')
            ->where('room_id', $listing->id)
            ->where('event_type', 'whatsapp_click')
            ->count());
    }

    public function test_listing_likes_can_be_toggled_and_are_counted_once_per_visitor(): void
    {
        $listing = $this->createListing();
        $this->actingAs(User::factory()->create());

        $this->postJson(route('listings.performance.like', $listing))
            ->assertOk()
            ->assertJson(['liked' => true, 'likes' => 1]);
        $this->postJson(route('listings.performance.like', $listing))
            ->assertOk()
            ->assertJson(['liked' => false, 'likes' => 0]);

        $this->assertDatabaseCount('listing_likes', 0);
    }

    public function test_installment_filter_only_returns_listings_accepting_installments(): void
    {
        $this->createListing([
            'title' => 'Installment Plot',
            'property_type' => 'residential_plot',
            'transaction_type' => 'sale',
            'sale_price' => 10_000_000,
            'monthly_rent' => null,
            'installment_allowed' => true,
        ]);
        $this->createListing([
            'title' => 'Full Payment Plot',
            'property_type' => 'residential_plot',
            'transaction_type' => 'sale',
            'sale_price' => 12_000_000,
            'monthly_rent' => null,
        ]);

        $this->get(route('home', ['installment' => 1]))
            ->assertOk()
            ->assertSee('Installment Plot')
            ->assertDontSee('Full Payment Plot');
    }

    public function test_listing_fee_uses_the_configured_percentage_of_sale_price(): void
    {
        Setting::create([
            'key' => 'listing_fee_percentage',
            'type' => 'float',
            'value' => '2.5',
        ]);
        $listing = $this->createListing([
            'property_type' => 'farm',
            'transaction_type' => 'sale',
            'sale_price' => 40_000_000,
            'monthly_rent' => null,
        ]);

        $fee = app(ListingFeeService::class)->calculateAndCreate($listing);

        $this->assertSame('1000000.00', $fee->amount);
        $this->assertSame('2.50', $fee->fee_percentage);
    }

    public function test_unpaid_fee_is_recalculated_when_a_listing_price_changes(): void
    {
        Setting::create([
            'key' => 'listing_fee_percentage',
            'type' => 'float',
            'value' => '2.5',
        ]);
        $listing = $this->createListing([
            'property_type' => 'farm',
            'transaction_type' => 'sale',
            'sale_price' => 40_000_000,
            'monthly_rent' => null,
        ]);
        $service = app(ListingFeeService::class);
        $fee = $service->chargeForOwner($listing);

        $listing->update(['sale_price' => 50_000_000]);
        $service->repriceUnpaidFee($listing);

        $this->assertSame('1250000.00', $fee->fresh()->amount);
    }

    private function createListing(array $overrides = []): Room
    {
        $owner = User::factory()->create();
        $region = Region::firstOrCreate(['name' => 'Dar es Salaam']);
        $district = District::firstOrCreate(['region_id' => $region->id, 'name' => 'Kinondoni']);

        return Room::create(array_merge([
            'owner_id' => $owner->id,
            'created_by' => $owner->id,
            'created_by_role' => 'owner',
            'title' => 'Test property',
            'description' => 'A property listing for testing.',
            'region_id' => $region->id,
            'district_id' => $district->id,
            'listing_status' => 'published',
            'property_type' => 'house',
            'transaction_type' => 'rent',
            'monthly_rent' => 500_000,
        ], $overrides));
    }
}
