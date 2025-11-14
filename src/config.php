<?php
/**
 * Configuration de l'application
 * Utilisez des variables d'environnement ou éditez les valeurs par défaut
 */

return [
    'db_host' => getenv('DB_HOST') ?: 'localhost',
    'db_name' => getenv('DB_NAME') ?: 'gestion_convois',
    'db_user' => getenv('DB_USER') ?: 'root',
    'db_pass' => getenv('DB_PASS') ?: '@Dmin_password',
    'db_port' => getenv('DB_PORT') ?: '3306',
];
