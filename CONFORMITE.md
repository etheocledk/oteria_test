# Conformité au cahier des charges OTERIA (Majeure ASD 2526, étude de cas Dev)

Projet évalué : **SecureDesk**, gestion des incidents de sécurité.
Pile : Laravel 13 (PHP 8.4), Inertia.js, Vue 3 (Laravel Mix), PostgreSQL, Docker.

## Consignes

| Exigence | Statut | Fichier(s) concerné(s) |
|---|---|---|
| Thème libre (thème et design) | Fait | `resources/js/Pages/**`, `resources/css/app.css` |
| Déployable facilement via un orchestrateur type Docker / Kubernetes | Fait (image pas encore construite : Docker Desktop était arrêté pendant le développement) | `Dockerfile`, `docker-compose.yml`, `docker/entrypoint.sh`, `docker/apache.conf`, `docker/php.ini`, `docker/security.conf`, `.dockerignore`, `database/seeders/DemoSeeder.php` |
| Frontend avec des composants en React | **Manquant** (choix assumé : composants **Vue 3**) | `resources/js/**/*.vue` |
| Backend en Python embarquant une API | **Manquant** (choix assumé : backend **PHP / Laravel**, sans API REST séparée : les données passent par Inertia) | `app/**`, `routes/web.php` |
| Une page de login | Fait | `resources/js/Pages/Auth/Login.vue`, `app/Http/Controllers/Auth/LoginController.php` |
| Une base de données | Fait | PostgreSQL : `database/migrations/**`, `app/Models/**` |
| Développé de façon sécurisée | Fait | voir `SECURITE.md` |
| Code générique, maintenable et opérationnel | Fait | `README.md` (installation, architecture), 25 tests (`tests/Feature/**`), formatage Pint |

## Livrables

| Exigence | Statut | Fichier(s) concerné(s) |
|---|---|---|
| Code source envoyé 24 h avant l'entretien | Hors code | (action manuelle) |
| Lancer le site en moins de 10 minutes pendant l'entretien | Fait (à vérifier avec Docker Desktop démarré) | `docker compose up --build`, voir `README.md` |
| Présenter architecture, fonctionnalités et mesures de sécurité | Fait | `README.md` (fonctionnalités, architecture MVC), `SECURITE.md` (mesure / fonctionnement / risque évité / fichier) |
| Pouvoir expliquer chaque ligne du code | Hors code | (préparation personnelle ; `SECURITE.md` répond aux questions « pourquoi cette mesure, quel risque ») |
