<?php
/**
 * Configuration de l'application
 * Utilisez des variables d'environnement ou éditez les valeurs par défaut
 */

return [
    'db_host' => getenv('DB_HOST') ?: '127.0.0.1',
    'db_name' => getenv('DB_NAME') ?: 'gestion_convois',
    'db_user' => getenv('DB_USER') ?: 'root',
    'db_pass' => getenv('DB_PASS') ?: '',
    'db_port' => getenv('DB_PORT') ?: '3306',
];
