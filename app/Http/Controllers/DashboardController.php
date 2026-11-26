<?php

namespace App\Http\Controllers;

use App\Enums\IncidentSeverity;
use App\Enums\IncidentStatus;
use App\Http\Resources\IncidentResource;
use App\Models\Incident;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $openStatuses = [IncidentStatus::Open, IncidentStatus::InProgress];

        $bySeverity = Incident::query()
            ->whereIn('status', $openStatuses)
            ->selectRaw('severity, count(*) as total')
            ->groupBy('severity')
            ->pluck('total', 'severity');

        $byStatus = Incident::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return Inertia::render('Dashboard', [
            'severityCounts' => collect(IncidentSeverity::cases())->map(fn (IncidentSeverity $severity) => [
                'value' => $severity->value,
                'label' => $severity->label(),
                'total' => (int) ($bySeverity[$severity->value] ?? 0),
            ]),
            'statusCounts' => collect(IncidentStatus::cases())->map(fn (IncidentStatus $status) => [
                'value' => $status->value,
                'label' => $status->label(),
                'total' => (int) ($byStatus[$status->value] ?? 0),
            ]),
            'myOpenCount' => $request->user()->assignedIncidents()->whereIn('status', $openStatuses)->count(),
            'unassignedCount' => Incident::query()->whereIn('status', $openStatuses)->whereNull('assignee_id')->count(),
            'recentIncidents' => IncidentResource::collection(
                Incident::query()->with(['reporter', 'assignee'])->latest()->latest('id')->limit(5)->get()
            ),
        ]);
    }
}
