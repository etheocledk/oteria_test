<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->role = UserRole::Admin;
        $admin->save();

        return $admin;
    }

    public function test_analysts_cannot_access_user_management(): void
    {
        $analyst = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($analyst)->get('/admin/users')->assertForbidden();
        $this->actingAs($analyst)->patch("/admin/users/{$analyst->id}", ['role' => 'admin'])->assertForbidden();
        $this->actingAs($analyst)->patch("/admin/users/{$other->id}", ['role' => 'admin'])->assertForbidden();

        $this->assertSame(UserRole::Analyst, $analyst->fresh()->role);
    }

    public function test_an_admin_can_promote_an_analyst(): void
    {
        $analyst = User::factory()->create();

        $this->actingAs($this->admin())
            ->patch("/admin/users/{$analyst->id}", ['role' => 'admin'])
            ->assertRedirect();

        $this->assertSame(UserRole::Admin, $analyst->fresh()->role);
    }

    public function test_an_admin_cannot_change_their_own_role(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->patch("/admin/users/{$admin->id}", ['role' => 'analyst'])
            ->assertSessionHasErrors('role');

        $this->assertSame(UserRole::Admin, $admin->fresh()->role);
    }
}
