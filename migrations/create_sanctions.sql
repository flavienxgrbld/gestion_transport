-- Création de la table des sanctions
USE gestion_convois;

CREATE TABLE sanctions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  convoi_id INT NOT NULL COMMENT 'Convoi concerné',
  user_id INT NOT NULL COMMENT 'Utilisateur sanctionné',
  type ENUM('avertissement','blame','suspension','autre') NOT NULL DEFAULT 'avertissement',
  motif TEXT NOT NULL COMMENT 'Raison de la sanction',
  montant DECIMAL(10,2) NULL COMMENT 'Montant éventuel (amende)',
  date_sanction DATE NOT NULL,
  date_fin DATE NULL COMMENT 'Date de fin (pour suspension)',
  statut ENUM('active','levee','expiree') NOT NULL DEFAULT 'active',
  created_by INT NOT NULL COMMENT 'Admin qui a créé la sanction',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (convoi_id) REFERENCES convois(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
  INDEX idx_user (user_id),
  INDEX idx_convoi (convoi_id),
  INDEX idx_statut (statut)
) ENGINE=InnoDB;
