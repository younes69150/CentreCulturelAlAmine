-- À exécuter une fois sur une base déjà installée (phpMyAdmin > SQL).
CREATE TABLE IF NOT EXISTS page_views (
    day   DATE NOT NULL,
    path  VARCHAR(100) NOT NULL,
    views INT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (day, path)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
