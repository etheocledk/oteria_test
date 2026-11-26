# Mesures de sécurité de SecureDesk

Pour chaque mesure : ce qu'elle fait, le risque qu'elle évite et où la trouver dans le code.

## Authentification

| Mesure | Comment ça marche | Risque évité | Fichier |
|---|---|---|---|
| Hachage Argon2id des mots de passe | Le mot de passe n'est jamais stocké : on stocke une empreinte Argon2id (salée, coûteuse en mémoire et en temps). À la connexion, on recalcule et compare. | Vol de la base : les mots de passe ne sont pas lisibles et sont très lents à casser par force brute. | `.env` (`HASH_DRIVER=argon2id`), cast `'password' => 'hashed'` dans `app/Models/User.php` |
| Politique de mot de passe | 12 caractères minimum, avec majuscule, minuscule, chiffre et symbole, plus confirmation. | Mots de passe faibles devinés par dictionnaire. | `app/Http/Requests/Auth/RegisterRequest.php` |
| Limitation des tentatives de connexion | Après 5 échecs pour un couple e-mail + IP, la connexion est bloquée 60 s (événement `Lockout` journalisé). Les routes `login` et `register` ont aussi une limite par minute. | Attaque par force brute ou credential stuffing. | `app/Http/Requests/Auth/LoginRequest.php`, `routes/web.php` (`throttle`) |
| Message d'erreur unique | « Identifiants invalides » que l'e-mail existe ou non. | Énumération des comptes existants. | `LoginRequest::authenticate()` |
| Régénération de la session | Nouvel identifiant de session après connexion ; session invalidée à la déconnexion. | Fixation de session. | `app/Http/Controllers/Auth/LoginController.php` |
| Rôle imposé à l'inscription | Le rôle n'est pas dans les champs « fillable » : un visiteur ne peut pas s'inscrire admin en ajoutant `role=admin` à la requête. | Élévation de privilèges par mass assignment. | `#[Fillable]` dans `app/Models/User.php`, test `test_registration_creates_an_analyst_with_an_argon2id_hash` |

## Autorisations

| Mesure | Comment ça marche | Risque évité | Fichier |
|---|---|---|---|
| Contrôle d'accès par rôle | Une *policy* décide de chaque action : un analyste modifie seulement les incidents qu'il a déclarés ou qui lui sont affectés ; affecter et supprimer sont réservés à l'admin. Chaque contrôleur appelle `Gate::authorize()` avant d'agir. | Accès ou modification de données sans en avoir le droit (contrôle d'accès défaillant, OWASP A01). | `app/Policies/IncidentPolicy.php`, `app/Providers/AppServiceProvider.php` (`manage-users`) |
| Vérification côté serveur | Masquer un bouton dans l'interface ne suffit pas : le serveur refuse (HTTP 403) même si la requête est forgée à la main. | Contournement de l'interface. | tests `test_only_admins_can_*`, `test_analysts_cannot_access_user_management` |
| Pas d'auto-rétrogradation | Un admin ne peut pas changer son propre rôle. | Perte de tout accès administrateur. | `app/Http/Controllers/Admin/UserController.php` |

## Données et requêtes

| Mesure | Comment ça marche | Risque évité | Fichier |
|---|---|---|---|
| Validation de toutes les entrées | Types, longueurs maximales, valeurs autorisées (enum) vérifiés avant tout traitement. | Données invalides ou malveillantes en base. | `app/Http/Requests/*`, `$request->validate()` dans les contrôleurs |
| Requêtes paramétrées | L'ORM Eloquent envoie les valeurs séparément de la requête SQL (requêtes préparées PDO). | Injection SQL (OWASP A03). | `IncidentController::index()`, test `test_search_input_is_treated_as_data_not_sql` |
| Échappement à l'affichage | Vue échappe tout texte affiché avec `{{ }}` ; aucun `v-html` dans le projet. | XSS (injection de JavaScript). | `resources/js/Pages/Incidents/Show.vue` |
| Données filtrées vers le navigateur | Les *API Resources* listent explicitement les champs envoyés ; le hash du mot de passe est aussi masqué (`#[Hidden]`). | Fuite d'informations sensibles. | `app/Http/Resources/*` |

## Protection du navigateur

| Mesure | Comment ça marche | Risque évité | Fichier |
|---|---|---|---|
| Jeton CSRF | Laravel exige un jeton secret sur chaque requête POST/PUT/DELETE ; Inertia le renvoie automatiquement (cookie `XSRF-TOKEN`). | Requête malveillante envoyée depuis un autre site au nom de l'utilisateur (CSRF). | middleware web de Laravel |
| En-têtes HTTP de sécurité | `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`, `Referrer-Policy`, `Permissions-Policy`, HSTS en HTTPS. | Clickjacking, interprétation de fichiers avec le mauvais type, fuite d'URL. | `app/Http/Middleware/SecurityHeaders.php` |
| Cookies de session protégés | Cookies `HttpOnly` (inaccessibles au JavaScript) et `SameSite=Lax` ; sessions chiffrées dans Docker. | Vol de session via XSS, CSRF. | `config/session.php`, `docker-compose.yml` (`SESSION_ENCRYPT`) |

## Journalisation

| Mesure | Comment ça marche | Risque évité | Fichier |
|---|---|---|---|
| Journal de sécurité séparé | Connexions réussies et échouées, verrouillages, déconnexions, inscriptions, changements de rôle et suppressions d'incidents, avec IP et utilisateur ; jamais de mot de passe. Conservation de 90 jours. | Attaque non détectée ; impossibilité d'enquêter après coup. | `app/Listeners/LogAuthenticationEvents.php`, `config/logging.php` (canal `security`), `storage/logs/security-*.log` |
| Historique métier | Chaque action sur un incident est tracée avec son auteur et sa date. | Absence de traçabilité (qui a fait quoi). | `app/Models/Incident.php` (`recordActivity`, `recordChanges`) |

## Déploiement (Docker)

| Mesure | Comment ça marche | Risque évité | Fichier |
|---|---|---|---|
| Secrets hors de l'image | `.env` exclu de l'image ; `APP_KEY` et `DB_PASSWORD` sont fournis au démarrage. | Fuite de secrets si l'image est partagée. | `.dockerignore`, `docker-compose.yml` |
| Base de données non exposée | PostgreSQL n'a pas de port publié : seul le conteneur de l'application peut l'atteindre. | Attaque directe sur la base depuis le réseau. | `docker-compose.yml` |
| Mode production | `APP_DEBUG=false`, erreurs PHP non affichées, versions d'Apache et de PHP masquées, seul `public/` est servi. | Fuite d'informations techniques utiles à un attaquant. | `docker/php.ini`, `docker/security.conf`, `docker/apache.conf` |
| Image minimale | Node.js sert seulement à compiler le front (build multi-étapes) et les dépendances de développement ne sont pas installées. | Surface d'attaque réduite. | `Dockerfile` |

## Limites connues (pistes d'amélioration)

- Pas de double authentification (TOTP).
- Pas de politique CSP (`Content-Security-Policy`).
- Pas de HTTPS dans `docker-compose.yml` : à placer derrière un reverse proxy TLS en production.
