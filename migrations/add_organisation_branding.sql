-- Migration: Ajouter paramètres de branding par organisation
USE gestion_convois;

-- Ajouter colonnes de branding à la table organisations
ALTER TABLE organisations
ADD COLUMN couleur_primaire VARCHAR(7) DEFAULT '#19692d' COMMENT 'Couleur principale (hex)',
ADD COLUMN couleur_secondaire VARCHAR(7) DEFAULT '#f4fff7' COMMENT 'Couleur de fond (hex)',
ADD COLUMN logo_url VARCHAR(255) DEFAULT NULL COMMENT 'URL du logo';
