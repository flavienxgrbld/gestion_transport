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
    
    // Compte les utilisateurs (pour les admins)
    $user = current_user();
    $total_users = 0;
    if ($user['role'] === 'admin') {
        $stmt = $db->query('SELECT COUNT(*) as total FROM users');
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $total_users = $result['total'];
    }
    
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
    
    $db = get_db();
    
    try {
        $db->beginTransaction();
        
        $stmt = $db->prepare('SELECT * FROM convois WHERE id = ? FOR UPDATE');
        $stmt->execute([$id]);
        $convoi = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$convoi) throw new Exception('Convoi introuvable');
        if ($convoi['statut'] === 'termine') throw new Exception('Convoi déjà clôturé');

        $stmt = $db->query('SELECT * FROM coffre LIMIT 1 FOR UPDATE');
        $coffre = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$coffre) throw new Exception('Coffre introuvable');

        // LOGIQUE SELON LE TYPE DE CONVOI
        if ($convoi['type'] === 'recolte') {
            // RÉCOLTE : ajoute des PALETTES
            $qte_palettes = (int)($_POST['quantite_palettes'] ?? 0);
            $note = $_POST['note'] ?? null;
            
            $new_palettes = $coffre['quantite_palettes'] + $qte_palettes;
            
            $stmt = $db->prepare('INSERT INTO mouvements (convoi_id, type, quantite, unite, note) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$id, 'ajout', $qte_palettes, 'palette', $note]);
            
            $stmt = $db->prepare('UPDATE coffre SET quantite_palettes = ? WHERE id = ?');
            $stmt->execute([$new_palettes, $coffre['id']]);
            
            $stmt = $db->prepare('UPDATE convois SET quantite_realisee = ?, quantite_palettes_entree = ?, statut = "termine", date_terminated = NOW(), operateur_id = ? WHERE id = ?');
            $stmt->execute([$qte_palettes, $qte_palettes, current_user()['id'], $id]);
            
        } elseif ($convoi['type'] === 'traitement') {
            // TRAITEMENT : retire des PALETTES et ajoute des CARTONS
            $qte_palettes_sortie = (int)($_POST['quantite_palettes'] ?? 0);
            $qte_cartons_entree = (int)($_POST['quantite_cartons'] ?? 0);
            $note = $_POST['note'] ?? null;
            
            if ($coffre['quantite_palettes'] < $qte_palettes_sortie) {
                throw new Exception('Palettes insuffisantes (stock: ' . $coffre['quantite_palettes'] . ', demandé: ' . $qte_palettes_sortie . ')');
            }
            
            $new_palettes = $coffre['quantite_palettes'] - $qte_palettes_sortie;
            $new_cartons = $coffre['quantite_cartons'] + $qte_cartons_entree;
            
            $stmt = $db->prepare('INSERT INTO mouvements (convoi_id, type, quantite, unite, note) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$id, 'retrait', $qte_palettes_sortie, 'palette', 'Sortie pour traitement']);
            
            $stmt = $db->prepare('INSERT INTO mouvements (convoi_id, type, quantite, unite, note) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$id, 'ajout', $qte_cartons_entree, 'carton', $note]);
            
            $stmt = $db->prepare('UPDATE coffre SET quantite_palettes = ?, quantite_cartons = ? WHERE id = ?');
            $stmt->execute([$new_palettes, $new_cartons, $coffre['id']]);
            
            $stmt = $db->prepare('UPDATE convois SET quantite_realisee = ?, quantite_palettes_sortie = ?, quantite_cartons_entree = ?, statut = "termine", date_terminated = NOW(), operateur_id = ? WHERE id = ?');
            $stmt->execute([$qte_cartons_entree, $qte_palettes_sortie, $qte_cartons_entree, current_user()['id'], $id]);
            
        } elseif ($convoi['type'] === 'revente') {
            // REVENTE : retire des CARTONS
            $qte_cartons = (int)($_POST['quantite_cartons'] ?? 0);
            $note = $_POST['note'] ?? null;
            
            if ($coffre['quantite_cartons'] < $qte_cartons) {
                throw new Exception('Cartons insuffisants (stock: ' . $coffre['quantite_cartons'] . ', demandé: ' . $qte_cartons . ')');
            }
            
            $new_cartons = $coffre['quantite_cartons'] - $qte_cartons;
            
            $stmt = $db->prepare('INSERT INTO mouvements (convoi_id, type, quantite, unite, note) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$id, 'retrait', $qte_cartons, 'carton', $note]);
            
            $stmt = $db->prepare('UPDATE coffre SET quantite_cartons = ? WHERE id = ?');
            $stmt->execute([$new_cartons, $coffre['id']]);
            
            $stmt = $db->prepare('UPDATE convois SET quantite_realisee = ?, quantite_cartons_sortie = ?, statut = "termine", date_terminated = NOW(), operateur_id = ? WHERE id = ?');
            $stmt->execute([$qte_cartons, $qte_cartons, current_user()['id'], $id]);
        }

        $db->commit();
        header('Location: /convois/' . $id);
        exit;
        
    } catch (Exception $e) {
        if ($db->inTransaction()) $db->rollBack();
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

// Route: gestion des utilisateurs (admin uniquement)
if ($path === '/utilisateurs') {
    $user = current_user();
    if ($user['role'] !== 'admin') {
        http_response_code(403);
        echo "Accès refusé";
        exit;
    }
    
    $db = get_db();
    $stmt = $db->prepare('SELECT u.*, o.nom as organisation_nom FROM users u JOIN organisations o ON u.organisation_id = o.id ORDER BY u.created_at DESC');
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt = $db->query('SELECT * FROM organisations ORDER BY nom');
    $organisations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    require __DIR__ . '/../templates/utilisateurs.php';
    exit;
}

// Route: créer un utilisateur (admin uniquement)
if ($path === '/utilisateurs/create' && $method === 'POST') {
    $user = current_user();
    if ($user['role'] !== 'admin') {
        http_response_code(403);
        echo "Accès refusé";
        exit;
    }
    
    $db = get_db();
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'user';
    $organisation_id = $_POST['organisation_id'] ?? null;
    
    if ($nom && $prenom && $email && $password && $organisation_id) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare('INSERT INTO users (nom, prenom, email, password, role, organisation_id) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$nom, $prenom, $email, $password_hash, $role, $organisation_id]);
    }
    
    header('Location: /utilisateurs');
    exit;
}

// Route: supprimer un utilisateur (admin uniquement)
if (preg_match('#^/utilisateurs/(\d+)/delete$#', $path, $matches) && $method === 'POST') {
    $user = current_user();
    if ($user['role'] !== 'admin') {
        http_response_code(403);
        echo "Accès refusé";
        exit;
    }
    
    $user_id = $matches[1];
    
    // Empêcher de supprimer son propre compte
    if ($user_id == $user['id']) {
        header('Location: /utilisateurs?error=self_delete');
        exit;
    }
    
    $db = get_db();
    $stmt = $db->prepare('DELETE FROM users WHERE id = ?');
    $stmt->execute([$user_id]);
    
    header('Location: /utilisateurs');
    exit;
}

// 404 par défaut
http_response_code(404);
echo "Page non trouvée";
