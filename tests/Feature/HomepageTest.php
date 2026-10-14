<?php

namespace Tests\Feature;

use App\Models\RoomType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomepageTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_displays_the_full_rental_marketplace_sections(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('storage/room-images/logo.jpeg')
            ->assertSee('AURA - Affordable Urban Relaxing Accommodations')
            ->assertSee('data-translate-language="en"', false)
            ->assertSee('data-translate-language="sw"', false)
            ->assertSee('English')
            ->assertSee('Kiswahili')
            ->assertSee('Tafuta nyumba')
            ->assertSee('Makazi yaliyotangazwa')
            ->assertSee('Tafuta nyumba kwa mkoa')
            ->assertSee('Bei za kupanga kwa mkoa')
            ->assertSee('Jinsi inavyofanya kazi')
            ->assertSee('Maswali yanayoulizwa mara kwa mara');
    }

    public function test_homepage_room_type_search_leads_to_filtered_room_results(): void
    {
        RoomType::create([
            'name' => 'Single Room',
            'slug' => 'single-room',
            'description' => 'A single room.',
        ]);

        $this->get(route('rooms.index', ['room_type' => 'single-room']))
            ->assertRedirect(route('home', ['room_type' => 'single-room']));

        $this->get(route('home', ['room_type' => 'single-room']))
            ->assertOk()
            ->assertSee('Hakuna matangazo yaliyopatikana');
    }

    public function test_rooms_url_redirects_to_the_homepage(): void
    {
        $this->get('/rooms')->assertRedirect(route('home'));
    }
}
