<?php

namespace App\Http\Resources;

use App\Models\IncidentActivity;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin IncidentActivity
 */
class IncidentActivityResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'action' => $this->action->value,
            'action_label' => $this->action->label(),
            'description' => $this->description,
            'user' => $this->user?->name,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
