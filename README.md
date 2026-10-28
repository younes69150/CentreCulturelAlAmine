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
   - `setup_key` : un mot secret temporaire ;
   - `url` : l'adresse du site (ex. `https://ecole-alamine.fr`), utilisée dans les e-mails ;
   - `mail.from` : une adresse d'envoi du domaine, créée dans cPanel > *Comptes de messagerie* (ex. `no-reply@ecole-alamine.fr`) ;
   - `legal` : nom de l'association, adresse, responsable, e-mail et téléphone de contact (mentions légales et bouton « Contacter l'école » de l'accueil) ;
   - `seo.google_verification` : voir l'étape 12.
6. **HTTPS** : cPanel > *Let's Encrypt SSL* (ou *AutoSSL*) pour le domaine. Le site force le HTTPS.
7. **PHP** : cPanel > *Sélecteur de version PHP*, version 8.1 ou plus. Pour envoyer de gros fichiers, augmentez
   `upload_max_filesize` et `post_max_size` (ex. 25M) et ajustez `uploads.max_mb` dans `config.php`.
8. **Premier administrateur** : ouvrez `https://votre-domaine/installation`, saisissez la `setup_key` et vos informations.
   La page se désactive dès qu'un compte existe.
9. **Ensuite** : *Administration > Classes* pour créer les classes, puis *Codes* pour générer et imprimer les codes à distribuer.

10. **Sauvegardes** : cPanel > *Tâches Cron*, ajoutez une tâche quotidienne (ex. 3 h du matin) :
    `php /home/VOTRE_UTILISATEUR/centreculturelalamine/bin/backup.php`
    Elle range la base (`.sql.gz`) et les fichiers (`.zip`) dans `storage/backups` et garde les 14 dernières.
    Le tableau de bord admin affiche la date de la dernière sauvegarde, en rouge si elle date de plus de 2 jours.
    Pensez à télécharger une copie de temps en temps (Gestionnaire de fichiers). O2switch fait aussi ses propres sauvegardes (JetBackup).
11. **E-mails** : cPanel > *Délivrabilité des e-mails*, vérifiez que SPF et DKIM sont valides pour le domaine, sinon les e-mails « mot de passe oublié » risquent d'arriver en spam.

12. **Google Search Console** : sur https://search.google.com/search-console, *Ajouter une propriété* > *Préfixe de l'URL* > `https://votre-domaine`.
    Choisissez la méthode *Balise HTML*, copiez seulement la valeur `content="…"` dans `seo.google_verification`, puis cliquez *Valider*.
    Ensuite *Sitemaps* > ajoutez `sitemap.xml`. Seules l'accueil et les pages légales sont indexées : tout le reste est privé (`robots.txt`).

Mise à jour d'une base déjà installée : exécutez les fichiers de `database/migrations/` qui ne l'ont pas encore été (phpMyAdmin > *SQL*).

Restaurer une sauvegarde : phpMyAdmin > *Importer* le fichier `base_….sql.gz` ; dézippez `fichiers_….zip` dans `storage/uploads`.

Mise à jour : cPanel > *Git Version Control* > *Manage* > *Pull*. `config.php` et les fichiers déposés ne sont jamais écrasés.

## Sécurité

- Mots de passe hachés (Argon2id), 10 caractères minimum.
- Codes d'inscription : 12 caractères aléatoires, stockés uniquement sous forme de HMAC, usage unique (verrou en base), expiration, annulation possible.
- Limitation des tentatives : 5 échecs de connexion par compte / 15 min, 30 par IP, 10 codes invalides par IP.
- Jeton CSRF sur tous les formulaires, cookies de session `HttpOnly` + `SameSite=Lax` + `Secure`, session expirée après 2 h d'inactivité.
- Requêtes SQL préparées, affichage systématiquement échappé (aucun HTML accepté dans les contenus).
- En-têtes : Content-Security-Policy stricte (aucun script en ligne), anti-iframe, HSTS, nosniff.
- Fichiers : liste blanche d'extensions **et** contrôle du contenu réel, noms aléatoires, rangés dans `storage/uploads` hors du site public, servis uniquement après vérification des droits, toujours en téléchargement.
- Mot de passe oublié : lien à usage unique valable 1 h (jeton haché en base), même réponse que le compte existe ou non, 3 demandes par heure et par adresse.
- RGPD : pages « Mentions légales », « Confidentialité » et « CGU », consentement à l'inscription (accord parental sous 15 ans), suppression définitive d'un compte par l'admin, polices hébergées sur le site (aucune ressource externe, aucun traceur), un seul cookie technique.
- Formulaires publics (connexion, inscription, mot de passe oublié) : champ piège invisible et délai minimal anti-robot, sans captcha.
- Statistiques (*Administration > Statistiques*) : compteur de pages vues par jour, sans cookie ni adresse IP, conservé 13 mois. Exempté de bandeau cookies (CNIL).
- Messages vocaux (*Administration > Messages*) : enregistrement au micro dans le navigateur (https obligatoire) ou fichier audio, 15 Mo max, contrôle du type réel ; écoute réservée aux destinataires du message.
- Cours en direct (*Administration > Direct*) : le site publie un lien de visio (salle Jitsi créée automatiquement, ou lien Meet/Zoom collé) aux seules classes choisies ; le lien apparaît 15 min avant le début et disparaît à la fin ou quand on clique « Terminer ».
- Le contenu d'une classe ne devient jamais public : une classe encore utilisée ne peut pas être supprimée.

## Développement local

```bash
mysql -u root -e "CREATE DATABASE cca CHARACTER SET utf8mb4"
mysql -u root cca < database/schema.sql
cp config/config.example.php config/config.php   # puis éditer
php bin/demo-data.php                            # données fictives (debug = true uniquement)
php -S 127.0.0.1:8000 -t public public/index.php
```

En local (`debug` = true), les e-mails ne partent pas : ils sont écrits dans `storage/logs/mails.log`.

Comptes fictifs : `admin@demo.local`, `prof@demo.local`, `eleve1@demo.local`, `eleve2@demo.local`, `adulte@demo.local`, mot de passe `demo123456`.

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
