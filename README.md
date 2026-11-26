# SecureDesk

Plateforme de déclaration et de suivi des **incidents de sécurité** d'une entreprise : les analystes déclarent les incidents, l'administrateur les affecte, chaque action est historisée.

## Lancer le projet avec Docker (recommandé)

Prérequis : Docker Desktop démarré.

```sh
cp .env.example .env         
docker compose up --build
```

- `APP_KEY` : clé de chiffrement de l'application (32 octets aléatoires). Pour en générer une sans PHP installé :
  `docker run --rm php:8.4-cli php -r "echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;"`
- `DB_PASSWORD` : mot de passe de la base PostgreSQL du conteneur.

Le site est ensuite disponible sur **http://localhost:8080**. Au démarrage, le conteneur applique les migrations et, si la base est vide, crée les données de démonstration (`SEED_DEMO_DATA=false` pour les désactiver).

Arrêter : `docker compose down` (ajouter `-v` pour supprimer aussi la base de données).

## Comptes de démonstration

Mot de passe pour tous : `password` (uniquement pour la démo).

| E-mail | Rôle |
|---|---|
| admin@securedesk.test | Administrateur |
| alice@securedesk.test | Analyste |
| bruno@securedesk.test | Analyste |
| chloe@securedesk.test | Analyste |

## Lancer le projet sans Docker (développement)

Prérequis : PHP 8.4 (extension `pdo_pgsql`), Composer, Node.js, PostgreSQL.

```sh
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed    
npm run dev                    
php artisan serve              
```

Tests : `php artisan test` (ils utilisent la base PostgreSQL `ontario_db_testing`, voir `phpunit.xml`).

## Fonctionnalités

- Inscription et connexion (un nouveau compte est toujours **analyste**).
- Tableau de bord : incidents ouverts par criticité et par statut, incidents qui me sont affectés, incidents non affectés.
- Incidents : déclaration, liste avec recherche et filtres, détail, modification, suppression (admin).
- Criticité : faible, moyenne, élevée, critique. Statut : ouvert, en cours, résolu, clôturé.
- Affectation d'un incident à un membre (admin uniquement).
- Historique de chaque incident : création, changements de statut et de criticité, affectations, commentaires.
- Gestion des rôles des utilisateurs (admin uniquement).

## Architecture

Application **MVC** Laravel dont les vues sont des composants **Vue 3**, reliés par **Inertia.js** : un contrôleur Laravel renvoie le nom d'une page Vue et ses données, Inertia l'affiche côté navigateur sans recharger la page.

```
Navigateur (Vue 3) ──HTTP──▶ routes/web.php ──▶ Contrôleur ──▶ Policy (droits) ──▶ Modèle Eloquent ──▶ PostgreSQL
        ▲                                            │
        └───────────── page Vue + données JSON ◀─────┘
```

| Couche | Dossier | Rôle |
|---|---|---|
| Modèle | `app/Models`, `app/Enums` | Tables `users`, `incidents`, `incident_activities` ; criticités, statuts et rôles |
| Vue | `resources/js/Pages`, `resources/js/Components`, `resources/js/Layouts` | Pages et composants Vue |
| Contrôleur | `app/Http/Controllers` | Reçoit la requête, vérifie les droits, appelle le modèle, choisit la page |
| Validation | `app/Http/Requests` | Règles de validation des formulaires |
| Autorisations | `app/Policies/IncidentPolicy.php`, `app/Providers/AppServiceProvider.php` | Qui a le droit de faire quoi |
| Mise en forme des données | `app/Http/Resources` | Choisit les champs envoyés au navigateur (jamais le mot de passe) |
| Base de données | `database/migrations`, `database/seeders` | Schéma et données de démo |
| Déploiement | `Dockerfile`, `docker-compose.yml`, `docker/` | Image de l'application et base PostgreSQL |

Les mesures de sécurité sont détaillées dans [SECURITE.md](SECURITE.md).
