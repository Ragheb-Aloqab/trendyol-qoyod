<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class EditProfileAndSessionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_access_profile_page(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->actingAs($user)->get('/admin/profile');

        $response->assertSuccessful();
    }

    public function test_profile_page_displays_and_manages_browser_sessions(): void
    {
        $user = User::factory()->create([
            'name' => 'Original Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
        ]);

        $component = Livewire::actingAs($user)
            ->test(\App\Filament\Pages\Auth\EditProfile::class);

        $currentSessionId = session()->getId();
        $otherSessionId = 'other_session_456';

        DB::table('sessions')->insert([
            [
                'id' => $currentSessionId,
                'user_id' => $user->id,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'payload' => serialize(['user_id' => $user->id]),
                'last_activity' => time(),
            ],
            [
                'id' => $otherSessionId,
                'user_id' => $user->id,
                'ip_address' => '192.168.1.50',
                'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1',
                'payload' => serialize(['user_id' => $user->id]),
                'last_activity' => time() - 3600,
            ],
        ]);

        $sessions = $component->instance()->getSessions();

        $this->assertCount(2, $sessions);

        $current = $sessions->firstWhere('id', $currentSessionId);
        $this->assertTrue($current['is_current']);
        $this->assertEquals('Windows', $current['platform']);
        $this->assertEquals('Google Chrome', $current['browser']);

        $other = $sessions->firstWhere('id', $otherSessionId);
        $this->assertFalse($other['is_current']);
        $this->assertEquals('iOS', $other['platform']);
        $this->assertEquals('Apple Safari', $other['browser']);
        $this->assertEquals('هاتف ذكي (Mobile)', $other['device']);

        // Revoke the other session
        $component->call('revokeSession', $otherSessionId);

        $this->assertDatabaseMissing('sessions', [
            'id' => $otherSessionId,
        ]);
        $this->assertDatabaseHas('sessions', [
            'id' => $currentSessionId,
        ]);
    }

    public function test_user_can_logout_other_devices(): void
    {
        $user = User::factory()->create();

        $component = Livewire::actingAs($user)
            ->test(\App\Filament\Pages\Auth\EditProfile::class);

        $currentSessionId = session()->getId();

        DB::table('sessions')->insert([
            [
                'id' => $currentSessionId,
                'user_id' => $user->id,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Chrome Windows',
                'payload' => 'payload',
                'last_activity' => time(),
            ],
            [
                'id' => 'other_1',
                'user_id' => $user->id,
                'ip_address' => '10.0.0.1',
                'user_agent' => 'Safari Mac',
                'payload' => 'payload',
                'last_activity' => time() - 100,
            ],
            [
                'id' => 'other_2',
                'user_id' => $user->id,
                'ip_address' => '10.0.0.2',
                'user_agent' => 'Firefox Linux',
                'payload' => 'payload',
                'last_activity' => time() - 200,
            ],
        ]);

        $component->call('logoutOtherDevices');

        $this->assertDatabaseCount('sessions', 1);
        $this->assertDatabaseHas('sessions', ['id' => $currentSessionId]);
    }
}
