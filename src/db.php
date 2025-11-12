<?php
/**
 * Connexion à la base de données via PDO
 */

function get_db() {
    static $pdo = null;
    
    if ($pdo) {
        return $pdo;
    }
    
    $cfg = require __DIR__ . '/config.php';
    
    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        $cfg['db_host'],
        $cfg['db_port'],
        $cfg['db_name']
    );
    
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    try {
        $pdo = new PDO($dsn, $cfg['db_user'], $cfg['db_pass'], $options);
    } catch (PDOException $e) {
        die('Erreur de connexion à la base de données: ' . $e->getMessage());
    }
    
    return $pdo;
}
