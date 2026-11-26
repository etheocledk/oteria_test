<?php

namespace App\Http\Controllers;

use App\Enums\IncidentAction;
use App\Enums\IncidentStatus;
use App\Models\Incident;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class IncidentAssignmentController extends Controller
{
    public function update(Request $request, Incident $incident): RedirectResponse
    {
        Gate::authorize('assign', $incident);

        $validated = $request->validate([
            'assignee_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $assignee = isset($validated['assignee_id']) ? User::findOrFail($validated['assignee_id']) : null;

        $incident->assignee()->associate($assignee);

        if ($assignee && $incident->status === IncidentStatus::Open) {
            $incident->status = IncidentStatus::InProgress;
        }

        $incident->save();

        $incident->recordActivity(
            $request->user(),
            IncidentAction::Assigned,
            $assignee ? "Affecté à {$assignee->name}." : 'Affectation retirée.',
        );

        if ($incident->wasChanged('status')) {
            $incident->recordChanges($request->user());
        }

        Inertia::flash('success', 'Affectation mise à jour.');

        return redirect()->route('incidents.show', $incident);
    }
}
