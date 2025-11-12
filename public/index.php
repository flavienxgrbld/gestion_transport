<?php
/**
 * Front controller - Point d'entrée unique de l'application
 * Gère le routing simple et l'exécution des pages
 */

session_start();

require __DIR__ . '/../src/config.php';
require __DIR__ . '/../src/db.php';
require __DIR__ . '/../src/auth.php';

// Parse la route demandée
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Route: page de connexion
if ($path === '/' || $path === '/login') {
    if (is_logged_in()) {
        header('Location: /dashboard');
        exit;
    }
    require __DIR__ . '/../templates/login.php';
    exit;
}

// Route: déconnexion
if ($path === '/logout') {
    logout();
    header('Location: /login');
    exit;
}

// Toutes les routes suivantes nécessitent authentification
if (!is_logged_in()) {
    header('Location: /login');
    exit;
}

// Route: dashboard principal
if ($path === '/dashboard') {
    $db = get_db();
    
    // Récupère les infos du coffre
    $stmt = $db->query('SELECT * FROM coffre LIMIT 1');
    $coffre = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Récupère les 10 derniers convois
    $stmt = $db->prepare('SELECT c.*, o.nom as organisation_nom FROM convois c JOIN organisations o ON c.organisation_id = o.id ORDER BY c.created_at DESC LIMIT 10');
    $stmt->execute();
    $convois = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    require __DIR__ . '/../templates/dashboard.php';
    exit;
}

// Route: liste des convois
if ($path === '/convois') {
    $db = get_db();
    $stmt = $db->prepare('SELECT c.*, o.nom as organisation_nom FROM convois c JOIN organisations o ON c.organisation_id = o.id ORDER BY c.created_at DESC');
    $stmt->execute();
    $convois = $stmt->fetchAll(PDO::FETCH_ASSOC);
    require __DIR__ . '/../templates/convois_list.php';
    exit;
}

// Route: voir un convoi spécifique
if (preg_match('#^/convois/(\d+)$#', $path, $m)) {
    $id = (int)$m[1];
    $db = get_db();
    
    $stmt = $db->prepare('SELECT c.*, o.nom as organisation_nom FROM convois c JOIN organisations o ON c.organisation_id = o.id WHERE c.id = ?');
    $stmt->execute([$id]);
    $convoi = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$convoi) {
        http_response_code(404);
        echo "Convoi introuvable";
        exit;
    }
    
    // Récupère les mouvements associés
    $stmt = $db->prepare('SELECT * FROM mouvements WHERE convoi_id = ? ORDER BY date DESC');
    $stmt->execute([$id]);
    $mouvements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    require __DIR__ . '/../templates/convoi_view.php';
    exit;
}

// Route: créer un convoi
if ($path === '/convois/create') {
    if ($method === 'POST') {
        $type = $_POST['type'] ?? 'recolte';
        $quantite_prevue = (int)($_POST['quantite_prevue'] ?? 0);
        $notes = $_POST['notes'] ?? null;
        
        $db = get_db();
        $stmt = $db->prepare('INSERT INTO convois (organisation_id, type, quantite_prevue, notes) VALUES (?, ?, ?, ?)');
        $stmt->execute([current_user()['organisation_id'], $type, $quantite_prevue, $notes]);
        
        header('Location: /convois');
        exit;
    }
    
    require __DIR__ . '/../templates/convoi_create.php';
    exit;
}

// Route: clôturer un convoi
if (preg_match('#^/convois/(\d+)/close$#', $path, $m)) {
    $id = (int)$m[1];
    
    if ($method !== 'POST') {
        http_response_code(405);
        exit;
    }
    
    $quantite_realisee = (int)($_POST['quantite_realisee'] ?? 0);
    $note = $_POST['note'] ?? null;
    
    $db = get_db();
    
    // Transaction pour garantir cohérence des données
    try {
        $db->beginTransaction();
        
        // Verrouille le convoi
        $stmt = $db->prepare('SELECT * FROM convois WHERE id = ? FOR UPDATE');
        $stmt->execute([$id]);
        $convoi = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$convoi) {
            throw new Exception('Convoi introuvable');
        }
        
        if ($convoi['statut'] === 'termine') {
            throw new Exception('Convoi déjà clôturé');
        }

        // Détermine le type de mouvement
        $type = ($convoi['type'] === 'recolte') ? 'ajout' : 'retrait';

        // Verrouille le coffre
        $stmt = $db->query('SELECT * FROM coffre LIMIT 1 FOR UPDATE');
        $coffre = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$coffre) {
            throw new Exception('Coffre introuvable');
        }

        // Calcule la nouvelle quantité
        $newQuantite = $coffre['quantite_actuelle'] + (($type === 'ajout') ? $quantite_realisee : -$quantite_realisee);
        
        if ($newQuantite < 0) {
            throw new Exception('Impossible de clôturer : quantité insuffisante dans le coffre (stock actuel: ' . $coffre['quantite_actuelle'] . ', demandé: ' . $quantite_realisee . ')');
        }

        // Insère le mouvement
        $stmt = $db->prepare('INSERT INTO mouvements (convoi_id, type, quantite, note) VALUES (?, ?, ?, ?)');
        $stmt->execute([$id, $type, $quantite_realisee, $note]);

        // Met à jour le coffre
        $stmt = $db->prepare('UPDATE coffre SET quantite_actuelle = ? WHERE id = ?');
        $stmt->execute([$newQuantite, $coffre['id']]);

        // Met à jour le convoi
        $stmt = $db->prepare('UPDATE convois SET quantite_realisee = ?, statut = "termine", date_terminated = NOW(), operateur_id = ? WHERE id = ?');
        $stmt->execute([$quantite_realisee, current_user()['id'], $id]);

        $db->commit();
        
        header('Location: /convois/' . $id);
        exit;
        
    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        
        $_SESSION['error'] = $e->getMessage();
        header('Location: /convois/' . $id);
        exit;
    }
}

// Route: page du coffre
if ($path === '/coffre') {
    $db = get_db();
    
    $stmt = $db->query('SELECT * FROM coffre LIMIT 1');
    $coffre = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $stmt = $db->prepare('SELECT m.*, c.type as convoi_type FROM mouvements m LEFT JOIN convois c ON m.convoi_id = c.id ORDER BY m.date DESC');
    $stmt->execute();
    $mouvements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    require __DIR__ . '/../templates/coffre.php';
    exit;
}

// 404 par défaut
http_response_code(404);
echo "Page non trouvée";
