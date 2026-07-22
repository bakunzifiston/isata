<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_org_user_can_view_event_messages_index(): void
    {
        $org = Organization::create(['name' => 'Test Org', 'slug' => 'test-org']);
        $user = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'organization_id' => $org->id,
            'role' => User::ROLE_ADMIN,
        ]);
        $event = Event::create([
            'organization_id' => $org->id,
            'name' => 'Town hall',
            'date' => now()->addWeek()->toDateString(),
            'status' => 'scheduled',
        ]);

        $this->actingAs($user)
            ->get(route('events.messages.index', $event))
            ->assertOk();
    }

    public function test_org_user_can_open_create_message_form(): void
    {
        $org = Organization::create(['name' => 'Test Org', 'slug' => 'test-org-2']);
        $user = User::create([
            'name' => 'Staff',
            'email' => 'staff@example.com',
            'password' => bcrypt('password'),
            'organization_id' => $org->id,
            'role' => User::ROLE_STAFF,
        ]);
        $event = Event::create([
            'organization_id' => $org->id,
            'name' => 'Workshop',
            'date' => now()->addWeek()->toDateString(),
            'status' => 'scheduled',
        ]);

        $this->actingAs($user)
            ->get(route('events.messages.create', $event))
            ->assertOk();
    }

    public function test_user_cannot_view_messages_for_another_orgs_event(): void
    {
        $orgA = Organization::create(['name' => 'Org A', 'slug' => 'org-a']);
        $orgB = Organization::create(['name' => 'Org B', 'slug' => 'org-b']);
        $user = User::create([
            'name' => 'Admin A',
            'email' => 'admin-a@example.com',
            'password' => bcrypt('password'),
            'organization_id' => $orgA->id,
            'role' => User::ROLE_ADMIN,
        ]);
        $event = Event::create([
            'organization_id' => $orgB->id,
            'name' => 'Private event',
            'date' => now()->addWeek()->toDateString(),
            'status' => 'scheduled',
        ]);

        $this->actingAs($user)
            ->get(route('events.messages.index', $event))
            ->assertForbidden();
    }
}
