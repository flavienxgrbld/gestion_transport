<?php
/**
 * Fonctions d'authentification
 */

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function current_user() {
    if (!is_logged_in()) {
        return null;
    }
    
    // Cache l'utilisateur en session
    if (isset($_SESSION['user'])) {
        return $_SESSION['user'];
    }
    
    $db = get_db();
    $stmt = $db->prepare('SELECT id, organisation_id, email, role, nom FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        $_SESSION['user'] = $user;
    }
    
    return $user;
}

function login($email, $password, $allowed_roles = null) {
    $db = get_db();
    $stmt = $db->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        return false;
    }
    
    // Vérifier le rôle si des rôles autorisés sont spécifiés
    if ($allowed_roles !== null && !in_array($user['role'], $allowed_roles)) {
        return false;
    }
    
    if (password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        unset($_SESSION['user']); // Force refresh
        return true;
    }
    
    return false;
}

function logout() {
    session_unset();
    session_destroy();
}
