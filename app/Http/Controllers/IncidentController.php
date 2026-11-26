<?php

namespace App\Http\Controllers;

use App\Enums\IncidentAction;
use App\Enums\IncidentSeverity;
use App\Enums\IncidentStatus;
use App\Http\Requests\StoreIncidentRequest;
use App\Http\Requests\UpdateIncidentRequest;
use App\Http\Resources\IncidentResource;
use App\Http\Resources\UserResource;
use App\Models\Incident;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class IncidentController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Incident::class);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'severity' => ['nullable', Rule::enum(IncidentSeverity::class)],
            'status' => ['nullable', Rule::enum(IncidentStatus::class)],
            'mine' => ['nullable', 'boolean'],
        ]);

        $incidents = Incident::query()
            ->with(['reporter', 'assignee'])
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                // Bound parameter: the search term is never concatenated into the SQL.
                $query->whereLike('title', "%{$search}%");
            })
            ->when($filters['severity'] ?? null, fn ($query, string $severity) => $query->where('severity', $severity))
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($request->boolean('mine'), fn ($query) => $query->where('assignee_id', $request->user()->id))
            ->latest()
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Incidents/Index', [
            'incidents' => IncidentResource::collection($incidents),
            'filters' => [
                'search' => $filters['search'] ?? '',
                'severity' => $filters['severity'] ?? '',
                'status' => $filters['status'] ?? '',
                'mine' => $request->boolean('mine'),
            ],
            'options' => $this->options(),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', Incident::class);

        return Inertia::render('Incidents/Create', [
            'options' => $this->options(),
        ]);
    }

    public function store(StoreIncidentRequest $request): RedirectResponse
    {
        Gate::authorize('create', Incident::class);

        $incident = new Incident($request->validated());
        $incident->reporter()->associate($request->user());
        $incident->save();

        $incident->recordActivity($request->user(), IncidentAction::Created, "Déclaré avec la criticité « {$incident->severity->label()} ».");

        Inertia::flash('success', 'Incident déclaré.');

        return redirect()->route('incidents.show', $incident);
    }

    public function show(Incident $incident): Response
    {
        Gate::authorize('view', $incident);

        $incident->load([
            'reporter',
            'assignee',
            'activities' => fn ($query) => $query->with('user')->latest('created_at')->latest('id'),
        ]);

        return Inertia::render('Incidents/Show', [
            'incident' => IncidentResource::make($incident)->resolve(),
            'assignableUsers' => fn () => Gate::allows('assign', $incident)
                ? UserResource::collection(User::query()->orderBy('name')->get())->resolve()
                : [],
        ]);
    }

    public function edit(Incident $incident): Response
    {
        Gate::authorize('update', $incident);

        return Inertia::render('Incidents/Edit', [
            'incident' => IncidentResource::make($incident)->resolve(),
            'options' => $this->options(),
        ]);
    }

    public function update(UpdateIncidentRequest $request, Incident $incident): RedirectResponse
    {
        Gate::authorize('update', $incident);

        $incident->update($request->validated());
        $incident->recordChanges($request->user());

        Inertia::flash('success', 'Incident mis à jour.');

        return redirect()->route('incidents.show', $incident);
    }

    public function destroy(Request $request, Incident $incident): RedirectResponse
    {
        Gate::authorize('delete', $incident);

        $incident->delete();

        Log::channel('security')->warning('Incident deleted', [
            'incident_id' => $incident->id,
            'title' => $incident->title,
            'user_id' => $request->user()->id,
            'ip' => $request->ip(),
        ]);

        Inertia::flash('success', 'Incident supprimé.');

        return redirect()->route('incidents.index');
    }

    /**
     * @return array{severities: array<int, array{value: string, label: string}>, statuses: array<int, array{value: string, label: string}>}
     */
    private function options(): array
    {
        return [
            'severities' => array_map(
                fn (IncidentSeverity $severity) => ['value' => $severity->value, 'label' => $severity->label()],
                IncidentSeverity::cases(),
            ),
            'statuses' => array_map(
                fn (IncidentStatus $status) => ['value' => $status->value, 'label' => $status->label()],
                IncidentStatus::cases(),
            ),
        ];
    }
}
