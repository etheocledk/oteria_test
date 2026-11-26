<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('manage-users');

        return Inertia::render('Admin/Users/Index', [
            'users' => UserResource::collection(
                User::query()->withCount('assignedIncidents')->orderBy('name')->paginate(20)
            ),
            'roles' => array_map(
                fn (UserRole $role) => ['value' => $role->value, 'label' => $role->label()],
                UserRole::cases(),
            ),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('manage-users');

        $validated = $request->validate([
            'role' => ['required', Rule::enum(UserRole::class)],
        ]);

        // Prevents an administrator from locking themselves out of the admin area.
        if ($user->is($request->user())) {
            return back()->withErrors(['role' => 'Vous ne pouvez pas modifier votre propre rôle.']);
        }

        $previousRole = $user->role;
        $user->role = UserRole::from($validated['role']);
        $user->save();

        Log::channel('security')->notice('User role changed', [
            'target_user_id' => $user->id,
            'from' => $previousRole->value,
            'to' => $user->role->value,
            'by_user_id' => $request->user()->id,
            'ip' => $request->ip(),
        ]);

        Inertia::flash('success', "Rôle de {$user->name} mis à jour.");

        return back();
    }
}
