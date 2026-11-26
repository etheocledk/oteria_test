<?php

namespace App\Http\Middleware;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'appName' => config('app.name'),
            'auth' => [
                'user' => fn () => $request->user()
                    ? UserResource::make($request->user())->resolve()
                    : null,
                'isAdmin' => fn () => (bool) $request->user()?->isAdmin(),
            ],
        ];
    }
}
