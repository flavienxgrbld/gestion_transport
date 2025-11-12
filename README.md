# Gestion Convois - MVP

Application web PHP pour gérer les convois de palettes/cartons (récolte, traitement, revente) avec un coffre central BRINKS.

## Pré-requis
- PHP 8.0+ (avec extensions PDO, pdo_mysql)
- MariaDB 10.6+
- Apache2 avec mod_rewrite (ou PHP-FPM)

## Installation rapide

### 1. Configuration Apache
Configurez un VirtualHost pointant vers le dossier `public/` comme DocumentRoot :

```apache
<VirtualHost *:80>
    ServerName gestion-convois.local
    DocumentRoot /var/www/gestion_transport/public
    
    <Directory /var/www/gestion_transport/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### 2. Base de données
Importez le schéma initial :

```bash
mysql -u root -p < migrations/init.sql
```

Puis créez un utilisateur admin (voir `migrations/create_admin.sql`).

### 3. Configuration
Éditez `src/config.php` avec vos identifiants MariaDB ou utilisez des variables d'environnement :

```bash
export DB_HOST=127.0.0.1
export DB_NAME=gestion_convois
export DB_USER=root
export DB_PASS=votre_mot_de_passe
```

### 4. Permissions
Donnez les droits au serveur web sur le dossier `storage/` (si vous l'utilisez plus tard).

## Utilisation

### Connexion
- URL : http://localhost/ (ou votre domaine configuré)
- Email par défaut : `admin@brinks.local`
- Mot de passe par défaut : `Admin123!` (changez-le après la première connexion)

### Fonctionnalités principales
- **Dashboard** : vue d'ensemble de l'inventaire du coffre et derniers convois
- **Convois** : créer, consulter et clôturer des convois (récolte/traitement/revente)
- **Coffre** : voir l'inventaire actuel et l'historique des mouvements
- **Clôture** : valider un convoi met à jour l'inventaire automatiquement avec transaction DB

## Structure du projet

```
gestion_transport/
├── migrations/          # Scripts SQL d'initialisation
│   ├── init.sql        # Schéma de base
│   └── create_admin.sql # Compte admin initial
├── public/             # Point d'entrée web (DocumentRoot Apache)
│   ├── .htaccess       # Règles de réécriture
│   └── index.php       # Front controller
├── src/                # Logique métier
│   ├── config.php      # Configuration DB
│   ├── db.php          # Connexion PDO
│   └── auth.php        # Authentification
├── templates/          # Vues HTML
│   ├── layout.php
│   ├── login.php
│   ├── dashboard.php
│   ├── convois_list.php
│   ├── convoi_create.php
│   ├── convoi_view.php
│   └── coffre.php
└── README.md
```

## Sécurité

- Les mots de passe sont hashés avec `password_hash()` (bcrypt/argon2)
- Toutes les routes protégées nécessitent authentification
- Les clôtures de convoi utilisent des transactions DB pour éviter les conditions de course
- Inventaire négatif bloqué automatiquement

## Notes techniques

- Pas d'ORM : utilise PDO natif pour simplicité et performance
- Server-rendered : templates PHP simples (Blade-like)
- Multi-organisation : BRINKS, Police Nationale, Gendarmerie
- 1 palette = 1 carton (pas de conversion, suivi uniquement des quantités)

## Support

Pour toute question technique, consultez les fichiers sources commentés dans `src/` et `public/index.php`.
