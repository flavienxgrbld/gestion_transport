<?php
/**
 * Configuration de l'application
 * Utilisez des variables d'environnement ou éditez les valeurs par défaut
 */

return [
    'db_host' => getenv('localhost') ?: '127.0.0.1',
    'db_name' => getenv('gestion_convois') ?: 'gestion_convois',
    'db_user' => getenv('gestion_convois') ?: 'root',
    'db_pass' => getenv('@Dmin_password') ?: '',
    'db_port' => getenv('3306') ?: '3306',
];
