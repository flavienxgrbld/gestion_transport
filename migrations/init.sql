-- Initial schema for Gestion Convois (MariaDB)
-- Encodage UTF-8, dates en français

CREATE DATABASE IF NOT EXISTS gestion_convois DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE gestion_convois;

-- Table des organisations (BRINKS, Police, Gendarmerie)
CREATE TABLE organisations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(150) NOT NULL,
  type VARCHAR(50) COMMENT 'operateur, police, gendarmerie',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Table des utilisateurs
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  organisation_id INT NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL COMMENT 'bcrypt hash',
  role ENUM('admin','user') NOT NULL DEFAULT 'user',
  nom VARCHAR(150),
  prenom VARCHAR(150),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (organisation_id) REFERENCES organisations(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Table du coffre (stockage central BRINKS)
CREATE TABLE coffre (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(100) NOT NULL,
  quantite_actuelle INT NOT NULL DEFAULT 0 COMMENT 'Quantité totale (ancienne version)',
  quantite_palettes INT NOT NULL DEFAULT 0 COMMENT 'Stock de palettes',
  quantite_cartons INT NOT NULL DEFAULT 0 COMMENT 'Stock de cartons',
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Table des convois
CREATE TABLE convois (
  id INT AUTO_INCREMENT PRIMARY KEY,
  organisation_id INT NOT NULL COMMENT 'Organisation responsable du convoi',
  type ENUM('recolte','traitement','revente') NOT NULL,
  quantite_prevue INT NOT NULL DEFAULT 0,
  quantite_realisee INT DEFAULT NULL COMMENT 'Rempli à la clôture',
  quantite_palettes_entree INT DEFAULT 0 COMMENT 'Palettes ajoutées (récolte)',
  quantite_palettes_sortie INT DEFAULT 0 COMMENT 'Palettes retirées (traitement)',
  quantite_cartons_entree INT DEFAULT 0 COMMENT 'Cartons ajoutés (traitement)',
  quantite_cartons_sortie INT DEFAULT 0 COMMENT 'Cartons retirés (revente)',
  statut ENUM('ouvert','termine') NOT NULL DEFAULT 'ouvert',
  date_planned DATETIME NULL COMMENT 'Date prévue du convoi',
  date_terminated DATETIME NULL COMMENT 'Date de clôture effective',
  operateur_id INT NULL COMMENT 'Utilisateur qui a clôturé',
  notes TEXT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (organisation_id) REFERENCES organisations(id) ON DELETE RESTRICT,
  FOREIGN KEY (operateur_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_statut (statut),
  INDEX idx_type (type),
  INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- Table des mouvements (historique des ajouts/retraits au coffre)
CREATE TABLE mouvements (
  id INT AUTO_INCREMENT PRIMARY KEY,
  convoi_id INT NOT NULL,
  type ENUM('ajout','retrait') NOT NULL COMMENT 'ajout pour récolte, retrait pour traitement/revente',
  quantite INT NOT NULL,
  unite ENUM('palette','carton') NOT NULL DEFAULT 'palette' COMMENT 'Type d''unité',
  date DATETIME DEFAULT CURRENT_TIMESTAMP,
  note TEXT NULL,
  FOREIGN KEY (convoi_id) REFERENCES convois(id) ON DELETE CASCADE,
  INDEX idx_convoi (convoi_id),
  INDEX idx_date (date)
) ENGINE=InnoDB;

-- Seed initial : organisations et coffre
INSERT INTO organisations (nom, type) VALUES 
  ('BRINKS', 'operateur'),
  ('Police Nationale', 'police'),
  ('Gendarmerie', 'gendarmerie');

INSERT INTO coffre (nom, quantite_actuelle) VALUES ('Coffre principal BRINKS', 0);
