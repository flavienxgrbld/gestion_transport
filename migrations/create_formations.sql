-- Création des tables pour le système de formations
USE gestion_convois;

-- S'assurer que le charset est correct
SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- Table des formations (catalogue)
CREATE TABLE formations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  titre VARCHAR(255) NOT NULL,
  description TEXT NOT NULL,
  duree_heures DECIMAL(4,1) NOT NULL COMMENT 'Durée en heures',
  type ENUM('obligatoire','optionnelle','recyclage') NOT NULL DEFAULT 'obligatoire',
  validite_mois INT NULL COMMENT 'Durée de validité en mois (NULL = permanent)',
  prerequis TEXT NULL COMMENT 'Formations prérequises',
  contenu_formation TEXT NULL COMMENT 'Contenu détaillé de la formation',
  questions_qcm JSON NULL COMMENT 'Questions du QCM au format JSON',
  note_passage INT DEFAULT 70 COMMENT 'Note minimale pour valider (sur 100)',
  statut ENUM('active','archivee') NOT NULL DEFAULT 'active',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_type (type),
  INDEX idx_statut (statut)
) ENGINE=InnoDB;

-- Table des sessions de formation
CREATE TABLE sessions_formation (
  id INT AUTO_INCREMENT PRIMARY KEY,
  formation_id INT NOT NULL,
  date_debut DATE NOT NULL,
  date_fin DATE NOT NULL,
  lieu VARCHAR(255) NOT NULL,
  formateur VARCHAR(150) NOT NULL,
  places_max INT DEFAULT 20,
  places_restantes INT DEFAULT 20,
  statut ENUM('planifiee','en_cours','terminee','annulee') NOT NULL DEFAULT 'planifiee',
  notes TEXT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (formation_id) REFERENCES formations(id) ON DELETE CASCADE,
  INDEX idx_formation (formation_id),
  INDEX idx_dates (date_debut, date_fin),
  INDEX idx_statut (statut)
) ENGINE=InnoDB;

-- Table des inscriptions et résultats
CREATE TABLE inscriptions_formation (
  id INT AUTO_INCREMENT PRIMARY KEY,
  session_id INT NOT NULL,
  user_id INT NOT NULL,
  presence ENUM('inscrit','present','absent','excuse') NOT NULL DEFAULT 'inscrit',
  note_qcm INT NULL COMMENT 'Note sur 100',
  resultat ENUM('en_attente','reussi','echoue') NOT NULL DEFAULT 'en_attente',
  certificat_delivre BOOLEAN DEFAULT FALSE,
  date_certificat DATE NULL,
  date_expiration DATE NULL COMMENT 'Calculée automatiquement selon validité',
  commentaire TEXT NULL,
  inscrit_le DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (session_id) REFERENCES sessions_formation(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  UNIQUE KEY unique_inscription (session_id, user_id),
  INDEX idx_user (user_id),
  INDEX idx_resultat (resultat),
  INDEX idx_expiration (date_expiration)
) ENGINE=InnoDB;

-- Insertion de la première formation : Radio Communication & Codes 10
INSERT INTO formations (
  titre, 
  description, 
  duree_heures, 
  type, 
  validite_mois, 
  contenu_formation,
  questions_qcm,
  note_passage
) VALUES (
  'Formation Radio – Communication & Codes 10',
  'Former les agents Brink''s à la communication radio professionnelle pour assurer la sécurité, la coordination et l''efficacité des convois. Une communication claire et concise évite les erreurs et renforce la sécurité de l''équipe.',
  4.0,
  'obligatoire',
  12,
  'Module 1 – Règles générales de communication
1. Toujours identifier ton indicatif avant de parler
2. Parler calmement, sans crier
3. Garder les messages courts et précis
4. Respecter la hiérarchie : priorité aux messages de sécurité
5. Éviter les discussions inutiles sur le canal principal

Module 2 – Utilisation correcte de la radio
• La radio doit être active et fonctionnelle avant chaque mission
• En cas de perte de signal ou batterie faible, prévenir immédiatement le chef d''équipe
• Toujours confirmer un ordre reçu par un "10-4"
• En cas d''indisponibilité, indiquer "10-6"

Module 3 – Codes radio standards Brink''s
10-4: Affirmatif
10-5: Négatif
10-6: Indisponible radio
10-12: En attente d''ordre
10-14: Début de convoi
10-15: Fin de convoi
10-16: Transport d''un civil
10-20: Position actuelle
10-25: Rapport de situation
10-52: Demande de médecin
10-99: Toutes les unités sur zone

Module 4 – Bonnes pratiques
• Toujours répéter un message critique
• Rester calme même en situation de stress
• Fermer la communication par "Terminé" ou "Reçu"
• En cas d''urgence, le 10-99 est prioritaire

Module 5 – Erreurs à éviter
- Parler sans autorisation sur un canal prioritaire
- Couper la parole à un supérieur
- Utiliser un ton agressif
- Oublier d''utiliser les codes radio',
  '[
    {"question": "Que signifie le code 10-4 ?", "reponses": ["Négatif", "Affirmatif", "Indisponible radio"], "correct": 1},
    {"question": "Que signifie le code 10-6 ?", "reponses": ["Indisponible radio", "Début de convoi", "Rapport de situation"], "correct": 0},
    {"question": "Quelle phrase est correcte sur la radio ?", "reponses": ["J''suis là, t''inquiète", "Équipe Alpha, 10-14, départ du convoi", "Yo, on y va les gars"], "correct": 1},
    {"question": "Quand utiliser le 10-15 ?", "reponses": ["Début du convoi", "Fin du convoi", "Transport d''un civil"], "correct": 1},
    {"question": "Que signifie 10-20 ?", "reponses": ["Votre position", "Début de convoi", "Demande de médecin"], "correct": 0},
    {"question": "Quelle est la priorité sur le canal radio ?", "reponses": ["Les blagues entre collègues", "Les communications de sécurité", "Les discussions"], "correct": 1},
    {"question": "Comment confirmer un ordre reçu ?", "reponses": ["Ok mec", "10-4", "Reçu, chef !"], "correct": 1},
    {"question": "Que signifie le 10-99 ?", "reponses": ["Début de mission", "Toutes les unités sur zone", "Fin de mission"], "correct": 1},
    {"question": "Quelle erreur est à éviter ?", "reponses": ["Parler calmement", "Parler sans code radio", "Utiliser 10-4 pour confirmer"], "correct": 1},
    {"question": "Quelle est la bonne attitude en communication ?", "reponses": ["Courte, claire, respectueuse", "Longue et détaillée", "Humoristique et libre"], "correct": 0}
  ]',
  70
);
