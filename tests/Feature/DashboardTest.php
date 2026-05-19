<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_dashboard(): void
    {
        $this->get('/dashboard')
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertViewIs('dashboard.index')
            ->assertViewHas('days', 7)
            ->assertViewHas('summary');
    }

    public function test_hourly_data_returns_chart_structure(): void
    {
        $user = User::factory()->create();

        Visit::create([
            'visitor_id' => '00000000-0000-0000-0000-000000000001',
            'ip_address' => '127.0.0.1',
            'city' => 'Moscow',
            'device_type' => 'desktop',
            'page_url' => 'https://example.com',
            'visited_at' => now(),
        ]);

        $this->actingAs($user)
            ->getJson('/dashboard/data/hourly?days=7')
            ->assertOk()
            ->assertJsonStructure(['labels', 'values']);
    }

    public function test_cities_data_returns_chart_structure(): void
    {
        $user = User::factory()->create();

        Visit::create([
            'visitor_id' => '00000000-0000-0000-0000-000000000002',
            'ip_address' => '127.0.0.1',
            'city' => 'Berlin',
            'device_type' => 'mobile',
            'page_url' => 'https://example.com/page',
            'visited_at' => now(),
        ]);

        $this->actingAs($user)
            ->getJson('/dashboard/data/cities?days=7')
            ->assertOk()
            ->assertJsonStructure(['labels', 'values']);
    }

    public function test_invalid_days_parameter_defaults_to_seven(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/dashboard?days=99')
            ->assertOk()
            ->assertViewHas('days', 7);
    }
}
