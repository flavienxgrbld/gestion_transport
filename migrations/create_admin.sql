-- Création d'un utilisateur admin initial
-- Email: admin@brinks.local
-- Mot de passe: Admin123!
-- IMPORTANT: Changez ce mot de passe après la première connexion

USE gestion_convois;

-- Hash pour 'Admin123!' généré avec password_hash('Admin123!', PASSWORD_DEFAULT)
-- Note: vous devrez peut-être régénérer ce hash avec PHP si bcrypt change
INSERT INTO users (organisation_id, email, password, role, nom) VALUES 
  (1, 'admin@brinks.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Administrateur BRINKS');

-- Pour créer d'autres utilisateurs avec un mot de passe hashé, utilisez PHP:
-- <?php echo password_hash('VotreMotDePasse', PASSWORD_DEFAULT); ?>
