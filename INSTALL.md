# Installation du système de gestion de transport

## Prérequis

- PHP 8.0 ou supérieur
- MySQL/MariaDB
- Composer
- Serveur web (Apache/Nginx) avec mod_rewrite activé

## Installation

1. **Cloner le repository**
   ```bash
   git clone https://github.com/flavienxgrbld/gestion_transport.git
   cd gestion_transport
   ```

2. **Installer les dépendances PHP**
   ```bash
   composer install
   ```

3. **Configuration de la base de données**

   Créer une base de données MySQL nommée `gestion_transport`

   Exécuter le script de migration :
   ```bash
   mysql -u root -p gestion_transport < migrations/001_initial_schema.sql
   ```

4. **Configuration du serveur web**

   - Apache : Le fichier `.htaccess` est déjà configuré
   - Nginx : Ajouter cette configuration :
     ```
     server {
         listen 80;
         server_name localhost;
         root /path/to/gestion_transport/public;
         index index.php;

         location / {
             try_files $uri $uri/ /index.php?$query_string;
         }

         location ~ \.php$ {
             include fastcgi_params;
             fastcgi_pass 127.0.0.1:9000;
             fastcgi_index index.php;
             fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
         }
     }
     ```

5. **Démarrer le serveur**

   ```bash
   cd public
   php -S localhost:8000
   ```

   Ou utiliser votre serveur web configuré.

## Utilisation

- Accéder à http://localhost:8000
- Se connecter avec admin@brinks.fr (mot de passe à définir)
- Commencer à configurer les organisations et utilisateurs

## Structure du projet

- `public/` : Point d'entrée web
- `src/` : Code source PHP
- `templates/` : Templates HTML/PHP
- `migrations/` : Scripts SQL de migration
