# Centre Culturel Al Amine

Site privé de l'école : actualités, documents et messages, réservés aux élèves et professeurs inscrits.

- **Inscription uniquement avec un code** à usage unique, généré par l'école (lié à une classe pour les élèves).
- **Actualités, documents, messages à la connexion** : visibles par tout le monde ou par une seule classe.
- **Rôles** : élève (consultation), professeur (publie, dépose, crée des codes élèves), administrateur (tout, plus classes et comptes).

Technique : PHP 8.1+ et MySQL/MariaDB, sans framework ni dépendance. Prévu pour un hébergement mutualisé (O2switch).

## Installation sur O2switch

1. **Base de données** : cPanel > *Bases de données MySQL*. Créez une base et un utilisateur, donnez-lui tous les droits sur la base.
2. **Tables** : cPanel > *phpMyAdmin* > sélectionnez la base > *Importer* > `database/schema.sql`.
3. **Fichiers** : cPanel > *Git Version Control* > *Create*, cochez *Clone a Repository*, URL du dépôt GitHub, chemin par ex. `centreculturelalamine` (hors de `public_html`).
   Dépôt privé : ajoutez d'abord une clé SSH de déploiement (cPanel > *Accès SSH*) sur GitHub.
   Sans Git : envoyez le dossier complet par le *Gestionnaire de fichiers* ou FTP.
4. **Racine du site** : cPanel > *Domaines*, modifiez le domaine et mettez comme racine `centreculturelalamine/public`.
   Ainsi, seul le dossier `public/` est accessible depuis Internet.
5. **Configuration** : copiez `config/config.example.php` en `config/config.php` et remplissez :
   - `db` : nom de la base, utilisateur, mot de passe ;
   - `key` : 64 caractères aléatoires (par ex. depuis *Terminal* : `php -r "echo bin2hex(random_bytes(32));"`) ;
   - `setup_key` : un mot secret temporaire.
6. **HTTPS** : cPanel > *Let's Encrypt SSL* (ou *AutoSSL*) pour le domaine. Le site force le HTTPS.
7. **PHP** : cPanel > *Sélecteur de version PHP*, version 8.1 ou plus. Pour envoyer de gros fichiers, augmentez
   `upload_max_filesize` et `post_max_size` (ex. 25M) et ajustez `uploads.max_mb` dans `config.php`.
8. **Premier administrateur** : ouvrez `https://votre-domaine/installation`, saisissez la `setup_key` et vos informations.
   La page se désactive dès qu'un compte existe.
9. **Ensuite** : *Administration > Classes* pour créer les classes, puis *Codes* pour générer et imprimer les codes à distribuer.

Mise à jour : cPanel > *Git Version Control* > *Manage* > *Pull*. `config.php` et les fichiers déposés ne sont jamais écrasés.

## Sécurité

- Mots de passe hachés (Argon2id), 10 caractères minimum.
- Codes d'inscription : 12 caractères aléatoires, stockés uniquement sous forme de HMAC, usage unique (verrou en base), expiration, annulation possible.
- Limitation des tentatives : 5 échecs de connexion par compte / 15 min, 30 par IP, 10 codes invalides par IP.
- Jeton CSRF sur tous les formulaires, cookies de session `HttpOnly` + `SameSite=Lax` + `Secure`, session expirée après 2 h d'inactivité.
- Requêtes SQL préparées, affichage systématiquement échappé (aucun HTML accepté dans les contenus).
- En-têtes : Content-Security-Policy stricte (aucun script en ligne), anti-iframe, HSTS, nosniff.
- Fichiers : liste blanche d'extensions **et** contrôle du contenu réel, noms aléatoires, rangés dans `storage/uploads` hors du site public, servis uniquement après vérification des droits, toujours en téléchargement.
- Le contenu d'une classe ne devient jamais public : une classe encore utilisée ne peut pas être supprimée.

## Développement local

```bash
mysql -u root -e "CREATE DATABASE cca CHARACTER SET utf8mb4"
mysql -u root cca < database/schema.sql
cp config/config.example.php config/config.php   # puis éditer
php -S 127.0.0.1:8000 -t public public/index.php
```

## Changer les couleurs

Toutes les couleurs sont en haut de `public/assets/css/style.css` (`--primary`, `--gold`, etc.). Le logo est `public/assets/img/logo.svg`.

## Arborescence

```
app/          code PHP (contrôleurs, vues, sécurité)
config/       config.php (non versionné)
database/     schema.sql
public/       seule partie exposée : index.php, CSS, JS, logo
storage/      fichiers déposés, sessions, journaux (non versionnés)
```
