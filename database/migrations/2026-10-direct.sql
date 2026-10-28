-- À exécuter une fois sur une base déjà installée (phpMyAdmin > SQL).
CREATE TABLE IF NOT EXISTS lives (
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
