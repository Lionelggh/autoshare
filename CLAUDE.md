# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Environnement de développement

Ce projet tourne sous **XAMPP** (Apache + MySQL). Il n'y a pas de build, de compilation ni de gestionnaire de paquets.

- **URL locale** : `http://localhost/hope`
- **Base de données** : MySQL, nom `autopartage`, accessible via phpMyAdmin (`http://localhost/phpmyadmin`)
- **Réinitialiser la BDD** : Importer `database.sql` dans phpMyAdmin (attention : DROP TABLE sur `reservations`, `vehicules`, `utilisateurs`)
- **Mettre à jour le schéma sans réinitialiser** : Exécuter `update_db.php` via le navigateur ou phpMyAdmin

### Identifiants de test
- Admin : `admin@autopartage.com` / `admin123`
- Client : `jean.dupont@email.com` / `password`

## Architecture

Application PHP 8+ sans framework, architecture MVC partielle :

```
config/database.php   → Connexion PDO + démarrage session
includes/functions.php → Fonctions utilitaires globales (auth, flash, formatage, CSRF)
includes/header.php   → Header public (pages non-dashboard)
includes/sidebar.php  → Sidebar dashboard (admin + client), détecte le rôle via $_SESSION
includes/footer.php   → Footer commun
```

Chaque page PHP commence par :
```php
require_once '../config/database.php';   // fournit $pdo + session démarrée
require_once '../includes/functions.php'; // fonctions globales
requireAdmin(); // ou requireClient() selon le contexte
```

### Deux espaces distincts

| Espace | Dossier | Garde d'accès |
|--------|---------|---------------|
| Public / auth | `index.php`, `auth/` | — |
| Client connecté | `client/` | `requireClient()` |
| Administrateur | `admin/` | `requireAdmin()` |

### Base de données (6 tables)

- `utilisateurs` — rôle `client`/`admin`, soft-delete via `is_deleted`
- `vehicules` — soft-delete via `is_deleted`, statuts : `disponible`/`reserve`/`maintenance`
- `reservations` — statuts : `en_attente`/`confirmee`/`annulee`/`terminee` ; paiement : `non_paye`/`paye`
- `messages` — notifications système (admin → client ou système → admin)
- `chat_messages` — messagerie directe client ↔ admin
- `favoris` — likes de véhicules par les clients

### Flux de réservation

1. Client choisit un véhicule (`client/vehicles.php` → `client/vehicle_detail.php`)
2. Remplit le formulaire de dates (`client/reserve.php`) → crée une ligne `en_attente`
3. Admin confirme ou annule (`admin/reservations.php`) → notification `messages` envoyée au client
4. Si confirmée, client peut payer (`client/paiement.php`) : sur place ou en ligne simulée (OTP `1212`)
5. Client télécharge le reçu PDF (`client/recu.php`)

### Conventions clés

- Toutes les sorties HTML utilisent `clean()` (alias `htmlspecialchars`) pour éviter le XSS
- Toutes les requêtes SQL utilisent des requêtes préparées PDO
- Les redirections passent par `redirect('/chemin/relatif.php')` qui préfixe `BASE_URL` automatiquement
- Les messages flash sont stockés en session et consommés une seule fois via `getFlash()`
- La monnaie est le FCFA, formatée par `formatPrix()`
- Le CSRF est géré manuellement via `generateCSRF()` / `verifyCSRF()`

### Frontend

- CSS unique : `assets/css/style.css` (variables CSS custom, pas de framework)
- JS unique : `assets/js/app.js` (vanilla ES6 — scroll reveal, onglets, filtres, chat polling)
- Icônes : Font Awesome 6 via CDN
- Polices : Inter via Google Fonts
- Images véhicules : `assets/images/vehicules/` (référencées par nom de fichier dans la table `vehicules`)

### Dossier `scratch/`

Contient des scripts de diagnostic/utilitaires non destinés à la production (`check_db.php`, `check_schema.php`, `fix_admin.php`, `hash.php`). Ne pas les inclure dans le flux applicatif.
