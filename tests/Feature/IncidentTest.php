<?php

namespace Tests\Feature;

use App\Enums\IncidentAction;
use App\Enums\IncidentSeverity;
use App\Enums\IncidentStatus;
use App\Enums\UserRole;
use App\Models\Incident;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class IncidentTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->role = UserRole::Admin;
        $admin->save();

        return $admin;
    }

    public function test_an_analyst_can_declare_an_incident(): void
    {
        $analyst = User::factory()->create();

        $response = $this->actingAs($analyst)->post('/incidents', [
            'title' => 'Phishing ciblé',
            'description' => 'Un e-mail frauduleux a été reçu.',
            'severity' => 'high',
        ]);

        $incident = Incident::firstOrFail();
        $response->assertRedirect("/incidents/{$incident->id}");

        $this->assertSame(IncidentSeverity::High, $incident->severity);
        $this->assertSame(IncidentStatus::Open, $incident->status);
        $this->assertTrue($incident->reporter->is($analyst));
        $this->assertDatabaseHas('incident_activities', [
            'incident_id' => $incident->id,
            'user_id' => $analyst->id,
            'action' => IncidentAction::Created->value,
        ]);
    }

    public function test_the_detail_page_exposes_reporter_assignee_and_history(): void
    {
        $reporter = User::factory()->create(['name' => 'Alice']);
        $assignee = User::factory()->create(['name' => 'Bruno']);
        $incident = Incident::factory()->recycle($reporter)->assignedTo($assignee)->create();
        $incident->recordActivity($reporter, IncidentAction::Created, 'Déclaré.');

        $this->actingAs($assignee)
            ->get("/incidents/{$incident->id}")
            ->assertInertia(fn (Assert $page) => $page
                ->component('Incidents/Show')
                ->where('incident.reporter.name', 'Alice')
                ->where('incident.assignee.name', 'Bruno')
                ->where('incident.activities.0.user', 'Alice')
                ->where('incident.can.update', true)
                ->where('incident.can.assign', false));
    }

    public function test_incident_creation_rejects_an_unknown_severity(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/incidents', ['title' => 'Test', 'description' => 'Test', 'severity' => 'apocalyptic'])
            ->assertSessionHasErrors('severity');

        $this->assertDatabaseCount('incidents', 0);
    }

    public function test_the_index_filters_by_severity(): void
    {
        Incident::factory()->create(['severity' => IncidentSeverity::Critical]);
        Incident::factory()->create(['severity' => IncidentSeverity::Low]);

        $this->actingAs(User::factory()->create())
            ->get('/incidents?severity=critical')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Incidents/Index')
                ->has('incidents.data', 1)
                ->where('incidents.data.0.severity', 'critical'));
    }

    public function test_search_input_is_treated_as_data_not_sql(): void
    {
        Incident::factory()->create(['title' => 'Serveur compromis']);

        $this->actingAs(User::factory()->create())
            ->get('/incidents?search='.urlencode("' OR 1=1 --"))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->has('incidents.data', 0));
    }

    public function test_an_analyst_cannot_edit_an_incident_they_are_not_involved_in(): void
    {
        $incident = Incident::factory()->create();

        $this->actingAs(User::factory()->create())
            ->put("/incidents/{$incident->id}", [
                'title' => 'Piraté',
                'description' => 'Piraté',
                'severity' => 'low',
                'status' => 'closed',
            ])
            ->assertForbidden();

        $this->assertNotSame('Piraté', $incident->fresh()->title);
    }

    public function test_the_assignee_can_resolve_the_incident_and_it_is_logged(): void
    {
        $analyst = User::factory()->create();
        $incident = Incident::factory()->assignedTo($analyst)->create(['severity' => IncidentSeverity::Medium]);

        $this->actingAs($analyst)->put("/incidents/{$incident->id}", [
            'title' => $incident->title,
            'description' => $incident->description,
            'severity' => 'medium',
            'status' => 'resolved',
        ])->assertRedirect("/incidents/{$incident->id}");

        $incident->refresh();
        $this->assertSame(IncidentStatus::Resolved, $incident->status);
        $this->assertNotNull($incident->resolved_at);
        $this->assertDatabaseHas('incident_activities', [
            'incident_id' => $incident->id,
            'action' => IncidentAction::StatusChanged->value,
            'description' => 'En cours → Résolu',
        ]);
    }

    public function test_reopening_an_incident_clears_resolved_at(): void
    {
        $reporter = User::factory()->create();
        $incident = Incident::factory()->recycle($reporter)->create([
            'status' => IncidentStatus::Resolved,
            'resolved_at' => now(),
        ]);

        $this->actingAs($reporter)->put("/incidents/{$incident->id}", [
            'title' => $incident->title,
            'description' => $incident->description,
            'severity' => $incident->severity->value,
            'status' => 'open',
        ]);

        $this->assertNull($incident->fresh()->resolved_at);
    }

    public function test_only_admins_can_assign_incidents(): void
    {
        $analyst = User::factory()->create();
        $incident = Incident::factory()->create();

        $this->actingAs($analyst)
            ->put("/incidents/{$incident->id}/assignment", ['assignee_id' => $analyst->id])
            ->assertForbidden();

        $this->actingAs($this->admin())
            ->put("/incidents/{$incident->id}/assignment", ['assignee_id' => $analyst->id])
            ->assertRedirect("/incidents/{$incident->id}");

        $incident->refresh();
        $this->assertTrue($incident->assignee->is($analyst));
        $this->assertSame(IncidentStatus::InProgress, $incident->status);
        $this->assertDatabaseHas('incident_activities', [
            'incident_id' => $incident->id,
            'action' => IncidentAction::Assigned->value,
        ]);
    }

    public function test_only_admins_can_delete_incidents(): void
    {
        $reporter = User::factory()->create();
        $incident = Incident::factory()->recycle($reporter)->create();

        $this->actingAs($reporter)->delete("/incidents/{$incident->id}")->assertForbidden();

        $this->actingAs($this->admin())->delete("/incidents/{$incident->id}")->assertRedirect('/incidents');
        $this->assertModelMissing($incident);
    }

    public function test_any_user_can_comment_on_an_incident(): void
    {
        $incident = Incident::factory()->create();
        $analyst = User::factory()->create();

        $this->actingAs($analyst)
            ->post("/incidents/{$incident->id}/comments", ['comment' => 'Logs analysés, rien de suspect.'])
            ->assertRedirect("/incidents/{$incident->id}");

        $this->assertDatabaseHas('incident_activities', [
            'incident_id' => $incident->id,
            'user_id' => $analyst->id,
            'action' => IncidentAction::Commented->value,
            'description' => 'Logs analysés, rien de suspect.',
        ]);
    }
}
