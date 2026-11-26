<?php

namespace App\Http\Requests;

use App\Enums\IncidentSeverity;
use App\Enums\IncidentStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateIncidentRequest extends FormRequest
{
    /**
     * @return array<string, array<int, ValidationRule|string>>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:10000'],
            'severity' => ['required', Rule::enum(IncidentSeverity::class)],
            'status' => ['required', Rule::enum(IncidentStatus::class)],
        ];
    }
}
