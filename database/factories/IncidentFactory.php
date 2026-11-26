<?php

namespace Database\Factories;

use App\Enums\IncidentSeverity;
use App\Enums\IncidentStatus;
use App\Models\Incident;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Incident>
 */
class IncidentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->randomElement([
                'Tentative de phishing ciblant la comptabilité',
                'Connexions SSH suspectes sur le serveur web',
                'Poste infecté par un malware',
                'Fuite de données sur un bucket public',
                'Compte administrateur compromis',
                'Attaque par force brute sur le VPN',
                'Ransomware détecté sur un partage réseau',
                'Clé USB inconnue branchée sur un poste',
            ]),
            'description' => fake()->paragraph(4),
            'severity' => fake()->randomElement(IncidentSeverity::cases()),
            'status' => IncidentStatus::Open,
            'reporter_id' => User::factory(),
            'assignee_id' => null,
        ];
    }

    public function assignedTo(User $user): static
    {
        return $this->state(fn () => [
            'assignee_id' => $user->id,
            'status' => IncidentStatus::InProgress,
        ]);
    }
}
