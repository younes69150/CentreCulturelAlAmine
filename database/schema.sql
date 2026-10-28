-- Centre Culturel Al Amine : schéma MySQL / MariaDB
-- À importer une seule fois (phpMyAdmin > Importer).

SET NAMES utf8mb4;

CREATE TABLE classes (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom        VARCHAR(100) NOT NULL,
    created_at DATETIME NOT NULL,
    UNIQUE KEY uq_classes_nom (nom)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE users (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    prenom        VARCHAR(100) NOT NULL,
    nom           VARCHAR(100) NOT NULL,
    email         VARCHAR(190) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role          ENUM('eleve','prof','admin') NOT NULL DEFAULT 'eleve',
    classe_id     INT UNSIGNED NULL,
    actif         TINYINT(1) NOT NULL DEFAULT 1,
    last_login_at DATETIME NULL,
    created_at    DATETIME NOT NULL,
    UNIQUE KEY uq_users_email (email),
    CONSTRAINT fk_users_classe FOREIGN KEY (classe_id) REFERENCES classes(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Codes d'inscription : seul un HMAC du code est stocké.
CREATE TABLE invite_codes (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code_hash  CHAR(64) NOT NULL,
    code_hint  VARCHAR(8) NOT NULL,
    label      VARCHAR(150) NULL,
    role       ENUM('eleve','prof','admin') NOT NULL DEFAULT 'eleve',
    classe_id  INT UNSIGNED NULL,
    expires_at DATETIME NULL,
    created_by INT UNSIGNED NULL,
    created_at DATETIME NOT NULL,
    used_by    INT UNSIGNED NULL,
    used_at    DATETIME NULL,
    revoked_at DATETIME NULL,
    UNIQUE KEY uq_codes_hash (code_hash),
    CONSTRAINT fk_codes_classe  FOREIGN KEY (classe_id)  REFERENCES classes(id) ON DELETE RESTRICT,
    CONSTRAINT fk_codes_creator FOREIGN KEY (created_by) REFERENCES users(id)   ON DELETE SET NULL,
    CONSTRAINT fk_codes_user    FOREIGN KEY (used_by)    REFERENCES users(id)   ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- classe_id NULL = visible par tous les inscrits.
CREATE TABLE news (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    titre      VARCHAR(200) NOT NULL,
    contenu    MEDIUMTEXT NOT NULL,
    classe_id  INT UNSIGNED NULL,
    epingle    TINYINT(1) NOT NULL DEFAULT 0,
    auteur_id  INT UNSIGNED NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    KEY idx_news_date (created_at),
    CONSTRAINT fk_news_classe FOREIGN KEY (classe_id) REFERENCES classes(id) ON DELETE RESTRICT,
    CONSTRAINT fk_news_auteur FOREIGN KEY (auteur_id) REFERENCES users(id)   ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE documents (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    titre        VARCHAR(200) NOT NULL,
    description  TEXT NULL,
    nom_original VARCHAR(255) NOT NULL,
    nom_stockage CHAR(64) NOT NULL,
    mime         VARCHAR(150) NOT NULL,
    taille       BIGINT UNSIGNED NOT NULL,
    classe_id    INT UNSIGNED NULL,
    auteur_id    INT UNSIGNED NULL,
    created_at   DATETIME NOT NULL,
    UNIQUE KEY uq_documents_stockage (nom_stockage),
    CONSTRAINT fk_docs_classe FOREIGN KEY (classe_id) REFERENCES classes(id) ON DELETE RESTRICT,
    CONSTRAINT fk_docs_auteur FOREIGN KEY (auteur_id) REFERENCES users(id)   ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Messages affichés à la connexion.
CREATE TABLE annonces (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    titre      VARCHAR(200) NOT NULL,
    contenu    TEXT NOT NULL,
    classe_id  INT UNSIGNED NULL,
    debut      DATETIME NULL,
    fin        DATETIME NULL,
    actif      TINYINT(1) NOT NULL DEFAULT 1,
    auteur_id  INT UNSIGNED NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    CONSTRAINT fk_annonces_classe FOREIGN KEY (classe_id) REFERENCES classes(id) ON DELETE RESTRICT,
    CONSTRAINT fk_annonces_auteur FOREIGN KEY (auteur_id) REFERENCES users(id)   ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Liens « mot de passe oublié » : seul un hachage du jeton est stocké.
CREATE TABLE password_resets (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NOT NULL,
    token_hash CHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at DATETIME NOT NULL,
    UNIQUE KEY uq_resets_token (token_hash),
    CONSTRAINT fk_resets_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Anti force brute (connexion, codes d'inscription).
CREATE TABLE attempts (
    id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cle        VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL,
    KEY idx_attempts (cle, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Mesure d'audience sans cookie : nombre de pages vues par jour (aucune donnée personnelle).
CREATE TABLE page_views (
    day   DATE NOT NULL,
    path  VARCHAR(100) NOT NULL,
    views INT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (day, path)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Cours en direct : lien de visio publié aux classes choisies.
CREATE TABLE lives (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    titre      VARCHAR(200) NOT NULL,
    classe_id  INT UNSIGNED NULL,
    debut      DATETIME NOT NULL,
    duree      SMALLINT UNSIGNED NOT NULL DEFAULT 60,
    lien       VARCHAR(500) NOT NULL,
    termine_at DATETIME NULL,
    auteur_id  INT UNSIGNED NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    KEY idx_lives_debut (debut),
    CONSTRAINT fk_lives_classe FOREIGN KEY (classe_id) REFERENCES classes(id) ON DELETE RESTRICT,
    CONSTRAINT fk_lives_auteur FOREIGN KEY (auteur_id) REFERENCES users(id)   ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
