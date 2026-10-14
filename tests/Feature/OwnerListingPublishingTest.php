<?php

namespace Tests\Feature;

use App\Enums\ListingStatus;
use App\Enums\PaymentStatus;
use App\Models\District;
use App\Models\ListingFee;
use App\Models\Region;
use App\Models\Role;
use App\Models\Room;
use App\Models\RoomImage;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OwnerListingPublishingTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_submit_a_paid_draft_for_admin_approval(): void
    {
        [$owner, $room] = $this->createOwnerAndListing(PaymentStatus::Paid);

        $this->actingAs($owner)
            ->post(route('owner.listings.submit', $room))
            ->assertRedirect(route('owner.listings.index'))
            ->assertSessionHas('status');

        $this->assertSame(ListingStatus::PendingApproval->value, $room->fresh()->listing_status);
    }

    public function test_owner_must_pay_the_listing_fee_before_submitting_for_approval(): void
    {
        [$owner, $room] = $this->createOwnerAndListing(PaymentStatus::Unpaid);

        $this->actingAs($owner)
            ->get(route('owner.listings.index'))
            ->assertOk()
            ->assertSee('Pay fee');

        $this->actingAs($owner)
            ->get(route('owner.payments.index'))
            ->assertOk()
            ->assertSee($room->title)
            ->assertSee('Pay Now');

        $this->post(route('owner.listings.submit', $room))
            ->assertRedirect()
            ->assertSessionHasErrors('listing');

        $this->assertSame(ListingStatus::Draft->value, $room->fresh()->listing_status);

        $this->post(route('owner.payments.pay', $room->listingFee()->first()))
            ->assertRedirect(route('owner.listings.index'));

        $this->assertSame(PaymentStatus::Paid, $room->listingFee()->first()->fresh()->status);
        $this->get(route('owner.payments.index'))
            ->assertOk()
            ->assertDontSee('Pay Now');

        $this->post(route('owner.listings.submit', $room))
            ->assertRedirect(route('owner.listings.index'))
            ->assertSessionHas('status');

        $this->assertSame(ListingStatus::PendingApproval->value, $room->fresh()->listing_status);
    }

    public function test_owner_cannot_submit_another_owners_listing(): void
    {
        [$owner, $room] = $this->createOwnerAndListing(PaymentStatus::Paid);
        $otherOwner = $this->createOwner();

        $this->actingAs($otherOwner)
            ->post(route('owner.listings.submit', $room))
            ->assertForbidden();

        $this->assertSame(ListingStatus::Draft->value, $room->fresh()->listing_status);
    }

    public function test_admin_can_publish_a_paid_listing_awaiting_approval(): void
    {
        [, $room] = $this->createOwnerAndListing(PaymentStatus::Paid);
        $room->update(['listing_status' => ListingStatus::PendingApproval->value]);

        Role::firstOrCreate(['name' => 'admin']);
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->post(route('admin.listings.approve', $room))
            ->assertRedirect()
            ->assertSessionHas('status', 'Listing approved and published.');

        $this->assertSame(ListingStatus::Published->value, $room->fresh()->listing_status);
    }

    public function test_owner_listing_forms_show_one_interactive_map_and_google_maps_link(): void
    {
        [$owner, $room] = $this->createOwnerAndListing(PaymentStatus::Unpaid);

        $this->actingAs($owner)
            ->get(route('owner.listings.create'))
            ->assertOk()
            ->assertSee('data-map-picker', false)
            ->assertSee('name="images[]"', false)
            ->assertSee('multiple', false)
            ->assertSee('data-multiple-media', false)
            ->assertSee('data-map-link', false)
            ->assertSee('Open location in Google Maps')
            ->assertDontSee('Google Maps preview')
            ->assertDontSee('data-map-preview', false);

        $this->get(route('owner.listings.edit', $room))
            ->assertOk()
            ->assertSee('data-map-picker', false)
            ->assertSee('name="images[]"', false)
            ->assertSee('multiple', false)
            ->assertSee('data-multiple-media', false)
            ->assertSee('data-map-link', false)
            ->assertSee('Open location in Google Maps')
            ->assertDontSee('Google Maps preview')
            ->assertDontSee('data-map-preview', false);
    }

    public function test_owner_can_add_multiple_room_images_to_an_existing_listing(): void
    {
        Storage::fake('public');
        [$owner, $room] = $this->createOwnerAndListing(PaymentStatus::Unpaid);
        $existingImage = RoomImage::create([
            'room_id' => $room->id,
            'image_path' => 'room-images/existing-cover.jpg',
            'disk' => 'public',
            'is_cover' => true,
            'sort_order' => 0,
        ]);

        $firstImage = UploadedFile::fake()->image('bedroom-one.jpg');
        $secondImage = UploadedFile::fake()->image('bedroom-two.jpg');

        $this->actingAs($owner)
            ->put(route('owner.listings.update', $room), [
                'images' => [$firstImage, $secondImage],
            ])
            ->assertRedirect(route('owner.listings.edit', $room));

        $images = $room->images()->orderBy('sort_order')->get();
        $this->assertCount(3, $images);
        $this->assertTrue($images[0]->is_cover);
        $this->assertSame($existingImage->id, $images[0]->id);
        $this->assertFalse($images[1]->is_cover);
        $this->assertFalse($images[2]->is_cover);
        $this->assertSame([0, 1, 2], $images->pluck('sort_order')->all());
        Storage::disk('public')->assertExists($images[1]->image_path);
        Storage::disk('public')->assertExists($images[2]->image_path);

        $this->get(route('owner.listings.show', $room))
            ->assertOk()
            ->assertSee('Photos and videos')
            ->assertSee('alt="Owner listing photo 1"', false)
            ->assertSee('alt="Owner listing photo 2"', false)
            ->assertSee('alt="Owner listing photo 3"', false);
    }

    public function test_owner_listing_detail_shows_management_and_payment_actions(): void
    {
        [$owner, $room] = $this->createOwnerAndListing(PaymentStatus::Unpaid);

        $this->actingAs($owner)
            ->get(route('owner.listings.show', $room))
            ->assertOk()
            ->assertSee('AURA-'.str_pad((string) $room->id, 6, '0', STR_PAD_LEFT))
            ->assertSee('Pay fee')
            ->assertSee('Property details')
            ->assertSee('Property location')
            ->assertSee('Listing Performance')
            ->assertSee('Impressions')
            ->assertSee('WhatsApp Clicks')
            ->assertSee('Instagram Clicks')
            ->assertSee('Listing actions')
            ->assertSee('Delete listing')
            ->assertDontSee('data-share-url=', false);
    }

    public function test_published_owner_listing_detail_links_to_the_public_listing(): void
    {
        [$owner, $room] = $this->createOwnerAndListing(PaymentStatus::Paid);
        $room->update([
            'listing_status' => ListingStatus::Published->value,
            'published_at' => now(),
            'approved_at' => now(),
        ]);

        $this->actingAs($owner)
            ->get(route('owner.listings.show', $room))
            ->assertOk()
            ->assertSee('View public listing')
            ->assertSee('data-share-listing', false)
            ->assertSee(route('listings.show', $room), false)
            ->assertSee('Your listing is live and visible to people browsing AURA.');
    }

    public function test_owner_listing_performance_shows_recorded_activity_counts(): void
    {
        [$owner, $room] = $this->createOwnerAndListing(PaymentStatus::Paid);
        $now = now();
        $events = [];
        for ($index = 0; $index < 4; $index++) {
            $events[] = [
                'room_id' => $room->id,
                'event_type' => 'impression',
                'visitor_hash' => hash('sha256', 'visitor-'.$index),
                'event_date' => $now->toDateString(),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        DB::table('listing_performance_events')->insert($events);
        DB::table('listing_performance_events')->insert([
            'room_id' => $room->id,
            'event_type' => 'whatsapp_click',
            'visitor_hash' => hash('sha256', 'whatsapp-visitor'),
            'event_date' => $now->toDateString(),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->actingAs($owner)
            ->get(route('owner.listings.show', $room))
            ->assertOk()
            ->assertSee('data-performance-value="impression">4', false)
            ->assertSee('data-performance-value="view">0', false)
            ->assertSee('data-performance-value="whatsapp_click">1', false)
            ->assertSee('data-performance-value="instagram_click">0', false);
    }

    public function test_owner_listing_performance_includes_its_homepage_overlay_activity(): void
    {
        [$owner, $room] = $this->createOwnerAndListing(PaymentStatus::Paid);
        $room->update([
            'listing_status' => ListingStatus::Published->value,
            'published_at' => now(),
            'approved_at' => now(),
        ]);

        $this->actingAs($owner);
        $this->get(route('home'))->assertOk();
        $this->get(route('listings.show', $room), ['X-Requested-With' => 'XMLHttpRequest'])->assertOk();
        $this->postJson(route('listings.performance.record', $room), ['event' => 'click'])
            ->assertOk();
        $this->postJson(route('listings.performance.record', $room), ['event' => 'whatsapp_click'])
            ->assertOk();
        $this->postJson(route('listings.performance.record', $room), ['event' => 'call_click'])
            ->assertOk();

        $this->get(route('owner.listings.show', $room))
            ->assertOk()
            ->assertSee('data-performance-value="impression">1', false)
            ->assertSee('data-performance-value="view">1', false)
            ->assertSee('data-performance-value="click">1', false)
            ->assertSee('data-performance-value="whatsapp_click">1', false)
            ->assertSee('data-performance-value="call_click">1', false);
    }

    /**
     * @return array{User, Room}
     */
    private function createOwnerAndListing(PaymentStatus $feeStatus): array
    {
        $owner = $this->createOwner();
        $region = Region::create(['name' => 'Dar es Salaam']);
        $district = District::create(['region_id' => $region->id, 'name' => 'Kinondoni']);
        $roomType = RoomType::create([
            'name' => 'Master Room',
            'slug' => 'master-room',
            'description' => 'A master room.',
        ]);

        $room = Room::create([
            'owner_id' => $owner->id,
            'created_by' => $owner->id,
            'created_by_role' => 'owner',
            'room_type_id' => $roomType->id,
            'region_id' => $region->id,
            'district_id' => $district->id,
            'title' => 'Owner listing',
            'description' => 'A listing for approval tests.',
            'property_type' => 'room',
            'transaction_type' => 'rent',
            'monthly_rent' => 500000,
            'listing_status' => ListingStatus::Draft->value,
        ]);

        ListingFee::create([
            'room_id' => $room->id,
            'amount' => 25000,
            'fee_percentage' => 5,
            'status' => $feeStatus,
        ]);

        return [$owner, $room];
    }

    private function createOwner(): User
    {
        Role::firstOrCreate(['name' => 'owner']);

        $owner = User::factory()->create();
        $owner->assignRole('owner');

        return $owner;
    }
}
