<?php

namespace App\Enums;

enum IncidentAction: string
{
    case Created = 'created';
    case Updated = 'updated';
    case StatusChanged = 'status_changed';
    case SeverityChanged = 'severity_changed';
    case Assigned = 'assigned';
    case Commented = 'commented';

    public function label(): string
    {
        return match ($this) {
            self::Created => 'Incident créé',
            self::Updated => 'Incident modifié',
            self::StatusChanged => 'Statut modifié',
            self::SeverityChanged => 'Criticité modifiée',
            self::Assigned => 'Affectation modifiée',
            self::Commented => 'Commentaire ajouté',
        };
    }
}
