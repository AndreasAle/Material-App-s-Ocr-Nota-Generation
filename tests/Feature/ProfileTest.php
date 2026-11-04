<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_page_is_displayed(): void
    {
        $this->withoutVite();
        $user = User::factory()->create(['role' => 'staff', 'is_active' => true]);

        $this->actingAs($user)->get('/settings')->assertOk();
    }
}
