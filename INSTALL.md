# Guide d'installation - Gestion Convois

## Test rapide en local (Windows avec PHP intégré)

Si vous avez PHP installé localement, vous pouvez tester l'application immédiatement :

### 1. Vérifier que PHP est installé

```powershell
php -v
```

Si la commande ne fonctionne pas, installez PHP depuis : https://windows.php.net/download/

### 2. Configurer MariaDB/MySQL

Installez MariaDB ou MySQL localement (ou utilisez XAMPP/WAMP).

### 3. Importer la base de données

```powershell
# Depuis le dossier du projet
mysql -u root -p < migrations/init.sql
mysql -u root -p < migrations/create_admin.sql
```

Ou depuis phpMyAdmin : importez les deux fichiers SQL.

### 4. Configurer la connexion DB

Éditez `src/config.php` et modifiez les paramètres :

```php
return [
    'db_host' => '127.0.0.1',
    'db_name' => 'gestion_convois',
    'db_user' => 'root',
    'db_pass' => 'votre_mot_de_passe_mysql',
    'db_port' => '3306',
];
```

### 5. Lancer le serveur de développement PHP

```powershell
cd J:\git\gestion_transport
php -S localhost:8000 -t public
```

### 6. Accéder à l'application

Ouvrez votre navigateur : http://localhost:8000

**Identifiants par défaut :**
- Email : `admin@brinks.local`
- Mot de passe : `Admin123!`

---

## Déploiement sur serveur Debian avec Apache

### 1. Installer les dépendances

```bash
sudo apt update
sudo apt install apache2 php php-fpm php-mysql mariadb-server -y
```

### 2. Copier les fichiers

```bash
sudo mkdir -p /var/www/gestion_transport
sudo cp -r /chemin/vers/votre/projet/* /var/www/gestion_transport/
sudo chown -R www-data:www-data /var/www/gestion_transport
```

### 3. Configurer Apache VirtualHost

Créez `/etc/apache2/sites-available/gestion-convois.conf` :

```apache
<VirtualHost *:80>
    ServerName gestion-convois.local
    DocumentRoot /var/www/gestion_transport/public

    <Directory /var/www/gestion_transport/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/gestion-convois-error.log
    CustomLog ${APACHE_LOG_DIR}/gestion-convois-access.log combined
</VirtualHost>
```

Activez le site et mod_rewrite :

```bash
sudo a2enmod rewrite
sudo a2ensite gestion-convois
sudo systemctl restart apache2
```

### 4. Configurer MariaDB

```bash
sudo mysql_secure_installation
sudo mysql -u root -p < /var/www/gestion_transport/migrations/init.sql
sudo mysql -u root -p < /var/www/gestion_transport/migrations/create_admin.sql
```

### 5. Configurer la connexion DB

Éditez `/var/www/gestion_transport/src/config.php` avec les bonnes credentials.

### 6. Tester

Accédez à http://votre-serveur/ (ou l'IP du serveur).

---

## Fonctionnalités disponibles

### Dashboard
- Vue d'ensemble de l'inventaire du coffre
- Liste des derniers convois
- Statistiques rapides

### Convois
- Créer un nouveau convoi (récolte, traitement, revente)
- Consulter les détails d'un convoi
- Clôturer un convoi (met à jour l'inventaire automatiquement)
- Liste complète avec filtrage

### Coffre
- Inventaire actuel (quantité disponible)
- Historique de tous les mouvements (ajouts/retraits)
- Traçabilité complète

### Sécurité
- Authentification par email/mot de passe
- Mots de passe hashés (bcrypt)
- Transactions DB pour éviter les incohérences
- Protection contre inventaire négatif

---

## Prochaines étapes (optionnel)

1. **Gestion des utilisateurs** : ajouter une page admin pour créer/modifier/supprimer des utilisateurs
2. **Filtres avancés** : filtrer convois par date, type, organisation
3. **Export PDF** : générer des rapports PDF de convois
4. **Notifications email** : alerter quand inventaire faible
5. **API REST** : exposer des endpoints JSON pour intégrations futures

---

## Besoin d'aide ?

Consultez le `README.md` principal ou les commentaires dans les fichiers sources.
