<?php

namespace Tests\Feature;

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
  public function test_dashboard_page_rendered()
  {
      $user = User::factory()->create();
      $this->actingAs($user);

      $response = $this->get(route('dashboard'));

      $response->assertStatus(200);
      $response->assertViewIs('dashboard');
  }
}
