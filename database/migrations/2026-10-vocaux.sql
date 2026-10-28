-- À exécuter une fois sur une base déjà installée (phpMyAdmin > SQL).
-- Messages vocaux : fichier audio facultatif joint à un message de connexion.
ALTER TABLE annonces
    ADD COLUMN audio_stockage CHAR(64) NULL AFTER contenu,
    ADD COLUMN audio_mime VARCHAR(100) NULL AFTER audio_stockage;
