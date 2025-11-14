<?php
// Fonctions communes de l'application

function is_logged_in(): bool {
    return isset($_SESSION['user_id']);
}

function current_user(): ?array {
    return $_SESSION['user'] ?? null;
}

function redirect(string $url): void {
    header("Location: $url");
    exit;
}

function get_db(): PDO {
    static $db = null;
    if ($db === null) {
        $db = new PDO('mysql:host=localhost;dbname=gestion_transport', 'root', '');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    return $db;
}
?>