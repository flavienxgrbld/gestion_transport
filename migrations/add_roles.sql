-- Migration: Ajouter les rôles superviseur et etat
USE gestion_convois;

-- Modifier la colonne role pour inclure superviseur et etat
ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'user', 'superviseur', 'etat') NOT NULL DEFAULT 'user';
