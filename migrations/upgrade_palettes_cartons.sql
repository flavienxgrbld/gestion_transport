-- Migration pour séparer palettes et cartons dans le coffre
-- À exécuter APRÈS avoir sauvegardé vos données existantes

USE gestion_convois;

-- Modifier la table coffre pour avoir deux compteurs
ALTER TABLE coffre 
  ADD COLUMN quantite_palettes INT NOT NULL DEFAULT 0 AFTER quantite_actuelle,
  ADD COLUMN quantite_cartons INT NOT NULL DEFAULT 0 AFTER quantite_palettes;

-- Migrer les données existantes (adapter selon votre situation)
-- Par défaut, on met tout dans les palettes
UPDATE coffre SET quantite_palettes = quantite_actuelle, quantite_cartons = 0;

-- Optionnel : supprimer l'ancienne colonne après vérification
-- ALTER TABLE coffre DROP COLUMN quantite_actuelle;

-- Modifier la table mouvements pour spécifier le type d'unité
ALTER TABLE mouvements 
  ADD COLUMN unite ENUM('palette','carton') NOT NULL DEFAULT 'palette' AFTER type;

-- Modifier la table convois pour stocker séparément entrées/sorties
ALTER TABLE convois
  ADD COLUMN quantite_palettes_entree INT DEFAULT 0 AFTER quantite_realisee,
  ADD COLUMN quantite_palettes_sortie INT DEFAULT 0 AFTER quantite_palettes_entree,
  ADD COLUMN quantite_cartons_entree INT DEFAULT 0 AFTER quantite_palettes_sortie,
  ADD COLUMN quantite_cartons_sortie INT DEFAULT 0 AFTER quantite_cartons_entree;

-- Commentaires pour documentation
COMMENT ON COLUMN convois.quantite_palettes_entree IS 'Palettes ajoutées au coffre (récolte)';
COMMENT ON COLUMN convois.quantite_palettes_sortie IS 'Palettes retirées du coffre (traitement)';
COMMENT ON COLUMN convois.quantite_cartons_entree IS 'Cartons ajoutés au coffre (traitement)';
COMMENT ON COLUMN convois.quantite_cartons_sortie IS 'Cartons retirés du coffre (revente)';
