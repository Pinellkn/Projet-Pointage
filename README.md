# Projet Pointage

Application de pointage et de rapports journaliers pour BeniDev Studio, avec deux rôles :
**DG (direction générale)** et **employé**.

Le projet est composé de deux parties séparées :

- `Backend - Pointage/` — API Laravel (authentification, pointages, rapports, gestion des employés)
- `Frontend - Pointage/` — Application TanStack Start (React) qui consomme l'API Laravel

---

## 1. Lancer le backend (Laravel)

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
