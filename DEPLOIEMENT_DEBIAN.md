# Guide de déploiement sur serveur Debian

## Étape 1 : Préparer le serveur Debian

Connectez-vous en SSH à votre serveur Debian et exécutez :

```bash
# Mise à jour du système
sudo apt update
sudo apt upgrade -y

# Installation des dépendances
sudo apt install apache2 php php-fpm php-mysql php-cli php-common php-mbstring mariadb-server git -y

# Vérifier les versions installées
php -v
mysql --version
apache2 -v
```

## Étape 2 : Configurer MariaDB

```bash
# Sécuriser MariaDB
sudo mysql_secure_installation
# Répondez aux questions :
# - Set root password? [Y/n] Y (choisissez un mot de passe fort)
# - Remove anonymous users? [Y/n] Y
# - Disallow root login remotely? [Y/n] Y
# - Remove test database? [Y/n] Y
# - Reload privilege tables? [Y/n] Y

# Se connecter à MariaDB
sudo mysql -u root -p
```

Dans la console MariaDB, créez un utilisateur dédié (recommandé) :

```sql
CREATE USER 'gestion_convois'@'localhost' IDENTIFIED BY 'VotreMotDePasseSecurise';
GRANT ALL PRIVILEGES ON gestion_convois.* TO 'gestion_convois'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

## Étape 3 : Transférer les fichiers depuis Windows vers Debian

### Option A : Via Git (recommandé)

Sur votre machine Windows :

```powershell
# Dans le dossier du projet
cd J:\git\gestion_transport
git add .
git commit -m "Initial commit - scaffold complet"
git push origin main
```

Sur le serveur Debian :

```bash
cd /var/www
sudo git clone https://github.com/flavienxgrbld/gestion_transport.git
sudo chown -R www-data:www-data /var/www/gestion_transport
```

### Option B : Via SCP (si pas de Git distant)

Sur votre machine Windows (PowerShell) :

```powershell
# Remplacez user@serveur-ip par vos identifiants
scp -r J:\git\gestion_transport user@serveur-ip:/tmp/
```

Puis sur le serveur Debian :

```bash
sudo mv /tmp/gestion_transport /var/www/
sudo chown -R www-data:www-data /var/www/gestion_transport
```

### Option C : Via WinSCP (interface graphique)

1. Téléchargez WinSCP : https://winscp.net/
2. Connectez-vous à votre serveur
3. Glissez-déposez le dossier `J:\git\gestion_transport` vers `/var/www/`

## Étape 4 : Importer la base de données

```bash
# Importer le schéma initial
sudo mysql -u root -p < /var/www/gestion_transport/migrations/init.sql

# Créer le compte admin
sudo mysql -u root -p < /var/www/gestion_transport/migrations/create_admin.sql

# Vérifier que tout est créé
sudo mysql -u root -p -e "USE gestion_convois; SHOW TABLES;"
```

Vous devriez voir les tables : `coffre`, `convois`, `mouvements`, `organisations`, `users`

## Étape 5 : Configurer la connexion à la base de données

```bash
# Éditer le fichier de config
sudo nano /var/www/gestion_transport/src/config.php
```

Modifiez les paramètres :

```php
<?php
return [
    'db_host' => '127.0.0.1',
    'db_name' => 'gestion_convois',
    'db_user' => 'gestion_convois',  // ou 'root'
    'db_pass' => 'VotreMotDePasseSecurise',
    'db_port' => '3306',
];
```

Sauvegardez avec `Ctrl+O` puis `Entrée`, quittez avec `Ctrl+X`.

## Étape 6 : Configurer Apache

```bash
# Créer le fichier VirtualHost
sudo nano /etc/apache2/sites-available/gestion-convois.conf
```

Collez cette configuration (adaptez le ServerName à votre domaine/IP) :

```apache
<VirtualHost *:80>
    ServerName votre-domaine.com
    # ou ServerName 192.168.1.100 (votre IP)
    
    DocumentRoot /var/www/gestion_transport/public

    <Directory /var/www/gestion_transport/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    <Directory /var/www/gestion_transport>
        Require all denied
    </Directory>

    <Directory /var/www/gestion_transport/public>
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/gestion-convois-error.log
    CustomLog ${APACHE_LOG_DIR}/gestion-convois-access.log combined
</VirtualHost>
```

Sauvegardez et activez le site :

```bash
# Activer mod_rewrite (requis pour .htaccess)
sudo a2enmod rewrite

# Désactiver le site par défaut (optionnel)
sudo a2dissite 000-default.conf

# Activer votre site
sudo a2ensite gestion-convois.conf

# Vérifier la config Apache
sudo apache2ctl configtest
# Doit afficher "Syntax OK"

# Redémarrer Apache
sudo systemctl restart apache2

# Vérifier le statut
sudo systemctl status apache2
```

## Étape 7 : Vérifier les permissions

```bash
# Donner les bons droits
sudo chown -R www-data:www-data /var/www/gestion_transport
sudo chmod -R 755 /var/www/gestion_transport
sudo chmod 644 /var/www/gestion_transport/src/config.php
```

## Étape 8 : Tester l'application

### Test en ligne de commande (optionnel)

```bash
# Test de connexion à la DB
php -r "require '/var/www/gestion_transport/src/db.php'; \$db = get_db(); echo 'Connexion OK';"
```

### Test dans le navigateur

Ouvrez votre navigateur et accédez à :
- `http://votre-ip-serveur/` ou
- `http://votre-domaine.com/`

Vous devriez voir la page de connexion.

**Identifiants par défaut :**
- Email : `admin@brinks.local`
- Mot de passe : `Admin123!`

## Étape 9 : Sécurité post-installation

### Changer le mot de passe admin

Connectez-vous avec le compte admin, puis exécutez sur le serveur :

```bash
# Générer un nouveau hash de mot de passe
php -r "echo password_hash('VotreNouveauMotDePasse', PASSWORD_DEFAULT);"
```

Copiez le hash généré, puis :

```bash
sudo mysql -u root -p gestion_convois
```

Dans MariaDB :

```sql
UPDATE users SET password = '$2y$10$VotreLongHashGenere...' WHERE email = 'admin@brinks.local';
EXIT;
```

### Configurer le pare-feu (si UFW installé)

```bash
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw allow 22/tcp
sudo ufw enable
sudo ufw status
```

### Activer HTTPS avec Let's Encrypt (recommandé pour production)

```bash
sudo apt install certbot python3-certbot-apache -y
sudo certbot --apache -d votre-domaine.com
# Suivez les instructions
```

## Étape 10 : Créer des utilisateurs supplémentaires

Via la base de données :

```bash
# Générer un hash de mot de passe
php -r "echo password_hash('MotDePasseUser', PASSWORD_DEFAULT) . PHP_EOL;"

# Se connecter à MariaDB
sudo mysql -u root -p gestion_convois
```

Dans MariaDB :

```sql
-- Créer un utilisateur normal (organisation Police Nationale, ID=2)
INSERT INTO users (organisation_id, email, password, role, nom) VALUES 
  (2, 'agent@police.fr', '$2y$10$HashGenere...', 'user', 'Agent Police');

-- Créer un utilisateur Gendarmerie
INSERT INTO users (organisation_id, email, password, role, nom) VALUES 
  (3, 'agent@gendarmerie.fr', '$2y$10$HashGenere...', 'user', 'Agent Gendarmerie');

EXIT;
```

## Dépannage

### Erreur "Internal Server Error"

```bash
# Vérifier les logs Apache
sudo tail -f /var/log/apache2/gestion-convois-error.log

# Vérifier les permissions
sudo chown -R www-data:www-data /var/www/gestion_transport
```

### Erreur de connexion à la base de données

```bash
# Tester la connexion
sudo mysql -u gestion_convois -p gestion_convois

# Vérifier que PHP peut se connecter
php -r "new PDO('mysql:host=127.0.0.1;dbname=gestion_convois', 'gestion_convois', 'VotreMotDePasse');"
```

### Page blanche / erreur 500

```bash
# Activer l'affichage des erreurs temporairement
sudo nano /var/www/gestion_transport/public/index.php
```

Ajoutez au début (après `<?php`) :

```php
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

Puis rechargez la page pour voir l'erreur exacte.

### Mod_rewrite ne fonctionne pas

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

## Maintenance

### Sauvegardes automatiques de la base

```bash
# Créer un script de backup
sudo nano /usr/local/bin/backup-convois.sh
```

Contenu :

```bash
#!/bin/bash
mysqldump -u root -p'VotreMotDePasseRoot' gestion_convois > /var/backups/gestion_convois_$(date +%Y%m%d_%H%M%S).sql
find /var/backups/gestion_convois_*.sql -mtime +7 -delete
```

```bash
sudo chmod +x /usr/local/bin/backup-convois.sh
sudo mkdir -p /var/backups

# Ajouter au cron (tous les jours à 2h du matin)
sudo crontab -e
# Ajouter : 0 2 * * * /usr/local/bin/backup-convois.sh
```

### Voir les logs en temps réel

```bash
# Logs Apache
sudo tail -f /var/log/apache2/gestion-convois-error.log

# Logs système
sudo journalctl -f -u apache2
```

## Récapitulatif des commandes essentielles

```bash
# Redémarrer Apache
sudo systemctl restart apache2

# Voir les logs d'erreur
sudo tail -f /var/log/apache2/gestion-convois-error.log

# Backup de la DB
sudo mysqldump -u root -p gestion_convois > backup.sql

# Restaurer la DB
sudo mysql -u root -p gestion_convois < backup.sql

# Mettre à jour le code (si Git)
cd /var/www/gestion_transport
sudo git pull
sudo systemctl restart apache2
```

---

**Votre application est maintenant déployée !** 🎉

Accédez à `http://votre-serveur/` et connectez-vous avec `admin@brinks.local` / `Admin123!`
