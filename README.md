# Projet Pointage

Application de pointage et de rapports journaliers pour BeniDev Studio, avec deux rôles :
**DG (direction générale)** et **employé**.

Le projet est composé de deux parties séparées :

- `Backend - Pointage/` — API Laravel (authentification, pointages, rapports, gestion des employés)
- `Frontend - Pointage/` — Application TanStack Start (React) qui consomme l'API Laravel

---

## 1. Lancer le backend (Laravel)

**Première installation uniquement** (une seule fois après le `git clone`) :

```bash
cd "Backend - Pointage"
composer install
copy .env.example .env
php artisan key:generate
type nul > database\database.sqlite
php artisan migrate
```

⚠️ Sans ces étapes, `php artisan serve` ou `php artisan migrate` échoueront : le fichier
`.env` et la base `database.sqlite` ne sont **pas** envoyés sur GitHub (volontairement,
voir la section Notes), donc chaque personne qui clone le projet doit les créer une fois
chez elle.

**Créer le compte DG** (une seule fois, sur une base fraîchement migrée) :

```bash
curl -X POST http://127.0.0.1:8000/api/auth/bootstrap-dg -H "Content-Type: application/json" -H "Accept: application/json" -d "{\"email\":\"dgbenidev@gmail.com\",\"password\":\"dg123\",\"fullName\":\"Directeur General\"}"
```

(ou plus simplement : ouvrir la page `/dg` du frontend, elle appelle cette route
automatiquement tant qu'aucun DG n'existe.)

**Ensuite, à chaque lancement :**

```bash
cd "Backend - Pointage"
php artisan serve
```

→ L'API sera disponible sur **http://127.0.0.1:8000/api**

---

## 2. Lancer le frontend

```bash
cd "Frontend - Pointage"
bun run dev
```

→ L'application sera disponible sur l'URL affichée dans le terminal (en général **http://localhost:3000**)

⚠️ Le backend doit être démarré **avant** le frontend, sinon les appels à l'API échoueront.

---

## Identifiants du compte DG

- **Email :** dgbenidev@gmail.com
- **Mot de passe :** dg123

---

## Notes

- Base de données : SQLite (`Backend - Pointage/database/database.sqlite`), aucune configuration supplémentaire nécessaire.
- CORS déjà configuré pour accepter les requêtes du frontend en développement local.
- Pour créer des comptes employés, connectez-vous en tant que DG puis utilisez l'onglet **Équipe** de la console.

---

## Contrat de l'API (pour l'intégration frontend)

### Endpoints principaux

| Méthode | Route | Accès | Description |
|---|---|---|---|
| POST | `/auth/login` | public | Connexion (email + password) |
| GET | `/auth/dg-exists` | public | Indique si un compte DG existe déjà |
| POST | `/auth/bootstrap-dg` | public | Crée le tout premier compte DG |
| GET | `/auth/me` | auth | Utilisateur connecté |
| POST | `/auth/logout` | auth | Déconnexion (révoque le token) |
| GET | `/check-ins/today` | auth | Pointage du jour de l'utilisateur connecté |
| POST | `/check-ins` | auth | Enregistre le check-in (arrivée) du jour |
| POST | `/check-ins/checkout` | auth | Enregistre le check-out (sortie) du jour |
| GET | `/check-ins` | auth | Historique des pointages de l'utilisateur |
| GET | `/check-ins/all` | DG | Tous les pointages (filtres `?user_id=` / `?date=`) |
| GET | `/daily-reports` | auth | Rapports de l'utilisateur connecté |
| POST | `/daily-reports` | auth | Envoyer un rapport journalier |
| GET | `/daily-reports/{id}` | auth | Détail d'un rapport (propriétaire ou DG) |
| GET | `/daily-reports/all` | DG | Tous les rapports |
| GET | `/employees` | DG | Liste des employés |
| POST | `/employees` | DG | Créer un employé |
| DELETE | `/employees/{id}` | DG | Supprimer un employé |

### Format des dates/heures

- Date seule (`check_in_date`, `report_date`) : `"2026-07-28"`
- Date + heure (`check_in_time`, `check_out_time`, `createdAt`...) : ISO 8601 UTC, ex. `"2026-07-28T18:30:08.000000Z"`
- Toujours parser avec `new Date(...)` côté JS, jamais de format `JJ/MM/AAAA`.

### Codes d'erreur

| Code | Cas |
|---|---|
| 401 | Token absent/invalide/expiré sur une route protégée, ou identifiants de connexion incorrects |
| 403 | Route réservée au DG appelée par un employé, ou accès à une ressource appartenant à quelqu'un d'autre |
| 404 | Ressource inexistante |
| 409 | Double check-in ou double check-out le même jour |
| 422 | Erreur de validation (champ manquant/invalide) |
| 200 / 201 | Succès |
