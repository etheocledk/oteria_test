<?php

namespace App\Http\Controllers;

use App\Enums\IncidentAction;
use App\Models\Incident;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class IncidentCommentController extends Controller
{
    public function store(Request $request, Incident $incident): RedirectResponse
    {
        Gate::authorize('comment', $incident);

        $validated = $request->validate([
            'comment' => ['required', 'string', 'max:5000'],
        ]);

        $incident->recordActivity($request->user(), IncidentAction::Commented, $validated['comment']);

        return redirect()->route('incidents.show', $incident);
    }
}
