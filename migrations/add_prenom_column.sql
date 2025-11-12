-- Ajouter la colonne prenom à la table users
USE gestion_convois;

ALTER TABLE users 
  ADD COLUMN prenom VARCHAR(150) AFTER nom;
