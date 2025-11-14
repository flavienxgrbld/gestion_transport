-- Migration initiale : création des tables de base

CREATE DATABASE IF NOT EXISTS gestion_transport;
USE gestion_transport;

-- Table des organisations
CREATE TABLE organisations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(255) NOT NULL,
    type ENUM('operateur', 'police', 'gendarmerie', 'autre') NOT NULL,
    couleur_primaire VARCHAR(7) DEFAULT '#19692d',
    couleur_secondaire VARCHAR(7) DEFAULT '#f4fff7',
    logo_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des utilisateurs
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user', 'superviseur', 'etat') DEFAULT 'user',
    organisation_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (organisation_id) REFERENCES organisations(id)
);

-- Table des formations
CREATE TABLE formations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    titre VARCHAR(255) NOT NULL,
    description TEXT,
    type ENUM('obligatoire', 'optionnel') DEFAULT 'obligatoire',
    duree_heures INT NOT NULL,
    validite_mois INT,
    contenu_formation TEXT,
    questions_qcm JSON,
    note_passage INT DEFAULT 70,
    statut ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des sessions de formation
CREATE TABLE sessions_formation (
    id INT PRIMARY KEY AUTO_INCREMENT,
    formation_id INT NOT NULL,
    date_debut DATETIME NOT NULL,
    date_fin DATETIME NOT NULL,
    lieu VARCHAR(255),
    formateur VARCHAR(255),
    places_max INT DEFAULT 20,
    places_restantes INT DEFAULT 20,
    statut ENUM('planifiee', 'en_cours', 'terminee', 'annulee') DEFAULT 'planifiee',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (formation_id) REFERENCES formations(id)
);

-- Table des inscriptions aux formations
CREATE TABLE inscriptions_formation (
    id INT PRIMARY KEY AUTO_INCREMENT,
    session_id INT NOT NULL,
    user_id INT NOT NULL,
    inscrit_le TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    presence ENUM('present', 'absent') DEFAULT NULL,
    note_qcm INT DEFAULT NULL,
    resultat ENUM('reussi', 'echoue', 'en_attente') DEFAULT 'en_attente',
    certificat_delivre BOOLEAN DEFAULT FALSE,
    date_certificat DATE DEFAULT NULL,
    date_expiration DATE DEFAULT NULL,
    FOREIGN KEY (session_id) REFERENCES sessions_formation(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    UNIQUE KEY unique_inscription (session_id, user_id)
);

-- Insertion d'organisations de base
INSERT INTO organisations (nom, type) VALUES
('BRINKS', 'operateur'),
('Police Nationale', 'police'),
('Gendarmerie', 'gendarmerie');

-- Insertion d'un utilisateur admin de base
INSERT INTO users (nom, prenom, email, password, role, organisation_id) VALUES
('Admin', 'System', 'admin@brinks.fr', '$2y$10$hashedpassword', 'admin', 1);