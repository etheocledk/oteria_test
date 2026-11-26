<?php

namespace App\Models;

use App\Enums\IncidentAction;
use App\Enums\IncidentSeverity;
use App\Enums\IncidentStatus;
use Database\Factories\IncidentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['title', 'description', 'severity', 'status', 'assignee_id', 'resolved_at'])]
class Incident extends Model
{
    /** @use HasFactory<IncidentFactory> */
    use HasFactory;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'open',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'severity' => IncidentSeverity::class,
            'status' => IncidentStatus::class,
            'resolved_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    /**
     * @return HasMany<IncidentActivity, $this>
     */
    public function activities(): HasMany
    {
        return $this->hasMany(IncidentActivity::class);
    }

    /**
     * Keep resolved_at consistent with the current status.
     */
    protected static function booted(): void
    {
        static::saving(function (Incident $incident): void {
            if (! $incident->isDirty('status')) {
                return;
            }

            $incident->resolved_at = $incident->status->isFinished()
                ? ($incident->resolved_at ?? now())
                : null;
        });
    }

    /**
     * Record history entries describing the changes made by the last save.
     */
    public function recordChanges(User $user): void
    {
        if ($this->wasChanged('status')) {
            $previous = IncidentStatus::from($this->getPrevious()['status']);
            $this->recordActivity($user, IncidentAction::StatusChanged, "{$previous->label()} → {$this->status->label()}");
        }

        if ($this->wasChanged('severity')) {
            $previous = IncidentSeverity::from($this->getPrevious()['severity']);
            $this->recordActivity($user, IncidentAction::SeverityChanged, "{$previous->label()} → {$this->severity->label()}");
        }

        if ($this->wasChanged(['title', 'description'])) {
            $this->recordActivity($user, IncidentAction::Updated, 'Titre ou description mis à jour.');
        }
    }

    /**
     * Record an entry in the incident history.
     */
    public function recordActivity(?User $user, IncidentAction $action, string $description): IncidentActivity
    {
        return $this->activities()->create([
            'user_id' => $user?->id,
            'action' => $action,
            'description' => $description,
        ]);
    }
}
