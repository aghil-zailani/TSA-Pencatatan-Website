<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureSingleActiveSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class EnsureSingleActiveSessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_previous_session_is_logged_out_after_a_new_session_becomes_active(): void
    {
        $user = User::create([
            'id' => '220101',
            'username' => 'session-test',
            'email' => 'session-test@example.com',
            'password' => Hash::make('password'),
            'role' => 'supervisor',
        ]);

        $this->actingAs($user);
        Cache::put(EnsureSingleActiveSession::CACHE_KEY, 'newer-session');

        $this->post(route('logout'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('loginError');

        $this->assertGuest();
    }
}
