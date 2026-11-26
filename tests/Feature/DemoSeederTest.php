<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Incident;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_demo_accounts_and_incidents_once(): void
    {
        $this->seed(DemoSeeder::class);
        $this->seed(DemoSeeder::class);

        $this->assertSame(4, User::count());
        $this->assertSame(8, Incident::count());
        $this->assertSame(UserRole::Admin, User::where('email', 'admin@securedesk.test')->firstOrFail()->role);
    }

    public function test_it_does_not_touch_an_existing_installation(): void
    {
        User::factory()->create();

        $this->seed(DemoSeeder::class);

        $this->assertSame(1, User::count());
        $this->assertSame(0, Incident::count());
    }
}
