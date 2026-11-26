<?php

namespace Database\Seeders;

use App\Enums\IncidentAction;
use App\Enums\IncidentSeverity;
use App\Enums\IncidentStatus;
use App\Enums\UserRole;
use App\Models\Incident;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Demo accounts and incidents for presentations.
 *
 * It does not use factories (Faker is a development-only dependency), so it also
 * runs in the production Docker image. Demo password for every account: "password".
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (User::query()->exists()) {
            $this->command?->info('Accounts already exist: demo data skipped.');

            return;
        }

        $admin = $this->createUser('Admin SecureDesk', 'admin@securedesk.test', UserRole::Admin);
        $alice = $this->createUser('Alice Martin', 'alice@securedesk.test', UserRole::Analyst);
        $bruno = $this->createUser('Bruno Leroy', 'bruno@securedesk.test', UserRole::Analyst);
        $chloe = $this->createUser('Chloé Durand', 'chloe@securedesk.test', UserRole::Analyst);

        /** @var array<int, array{0: string, 1: string, 2: IncidentSeverity, 3: IncidentStatus, 4: User, 5: ?User}> $incidents */
        $incidents = [
            ['Ransomware détecté sur un partage réseau', 'Des fichiers du partage « Compta » ont été chiffrés avec l\'extension .lock. Une note de rançon est présente.', IncidentSeverity::Critical, IncidentStatus::InProgress, $admin, $alice],
            ['Compte administrateur compromis', 'Connexion au compte admin du CRM depuis une adresse IP étrangère à 3 h du matin.', IncidentSeverity::Critical, IncidentStatus::Open, $bruno, null],
            ['Tentative de phishing ciblant la comptabilité', 'Plusieurs e-mails imitant notre banque demandent une mise à jour des coordonnées bancaires.', IncidentSeverity::High, IncidentStatus::InProgress, $chloe, $bruno],
            ['Attaque par force brute sur le VPN', 'Plus de 2 000 tentatives de connexion échouées en une heure sur la passerelle VPN.', IncidentSeverity::High, IncidentStatus::Open, $alice, null],
            ['Connexions SSH suspectes sur le serveur web', 'Le journal auth.log montre des connexions SSH réussies avec un compte de service inhabituel.', IncidentSeverity::Medium, IncidentStatus::InProgress, $admin, $chloe],
            ['Clé USB inconnue branchée sur un poste', 'Un salarié a trouvé une clé USB dans le parking et l\'a branchée sur son poste.', IncidentSeverity::Medium, IncidentStatus::Open, $bruno, null],
            ['Fuite de données sur un bucket public', 'Un bucket de stockage contenant des exports clients était accessible publiquement.', IncidentSeverity::High, IncidentStatus::Resolved, $alice, $alice],
            ['Poste infecté par un malware', 'L\'antivirus a mis en quarantaine un cheval de Troie sur le poste RH-04.', IncidentSeverity::Low, IncidentStatus::Closed, $chloe, $chloe],
        ];

        foreach ($incidents as [$title, $description, $severity, $status, $reporter, $assignee]) {
            $incident = new Incident([
                'title' => $title,
                'description' => $description,
                'severity' => $severity,
                'status' => $status,
            ]);
            $incident->reporter()->associate($reporter);
            $incident->assignee()->associate($assignee);
            $incident->save();

            $incident->recordActivity($reporter, IncidentAction::Created, "Déclaré avec la criticité « {$severity->label()} ».");

            if ($assignee) {
                $incident->recordActivity($admin, IncidentAction::Assigned, "Affecté à {$assignee->name}.");
            }
        }
    }

    private function createUser(string $name, string $email, UserRole $role): User
    {
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => 'password',
        ]);
        $user->role = $role;
        $user->save();

        return $user;
    }
}
