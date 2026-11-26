<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::define('manage-users', fn (User $user): bool => $user->isAdmin());

        // Inertia serializes nested resources as responses: without this they would be wrapped in "data".
        JsonResource::withoutWrapping();
    }
}
