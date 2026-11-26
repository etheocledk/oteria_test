<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Log;

/**
 * Writes authentication events to the dedicated security log (storage/logs/security-*.log).
 */
class LogAuthenticationEvents
{
    public function handleLogin(Login $event): void
    {
        Log::channel('security')->info('Login succeeded', [
            'user_id' => $event->user->getAuthIdentifier(),
            'ip' => request()->ip(),
        ]);
    }

    public function handleFailed(Failed $event): void
    {
        // The submitted password is never logged.
        Log::channel('security')->warning('Login failed', [
            'email' => $event->credentials['email'] ?? null,
            'ip' => request()->ip(),
        ]);
    }

    public function handleLockout(Lockout $event): void
    {
        Log::channel('security')->alert('Login locked out after too many attempts', [
            'email' => $event->request->input('email'),
            'ip' => $event->request->ip(),
        ]);
    }

    public function handleLogout(Logout $event): void
    {
        Log::channel('security')->info('Logout', [
            'user_id' => $event->user?->getAuthIdentifier(),
            'ip' => request()->ip(),
        ]);
    }

    public function handleRegistered(Registered $event): void
    {
        Log::channel('security')->info('Account registered', [
            'user_id' => $event->user->getAuthIdentifier(),
            'ip' => request()->ip(),
        ]);
    }
}
