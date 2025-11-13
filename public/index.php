<?php
/**
 * Front controller - Point d'entrée unique de l'application
 * Gère le routing simple et l'exécution des pages
 */

// Afficher les erreurs temporairement pour déboguer
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require __DIR__ . '/../src/config.php';
require __DIR__ . '/../src/db.php';
require __DIR__ . '/../src/auth.php';

// Parse la route demandée
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Route: page d'accueil - sélection des portails
if ($path === '/') {
    require __DIR__ . '/../templates/brinks/portails.php';
    exit;
}

// Route: page de connexion
if ($path === '/login') {
    if (is_logged_in()) {
        header('Location: /dashboard');
        exit;
    }
    require __DIR__ . '/../templates/brinks/login.php';
    exit;
}

// Route: page de connexion pour portail entreprise
if ($path === '/login/entreprise') {
    if (is_logged_in()) {
        header('Location: /portail/entreprise');
        exit;
    }
    require __DIR__ . '/../templates/entreprise/login.php';
    exit;
}

// Route: page de connexion pour portail état
if ($path === '/login/etat') {
    if (is_logged_in()) {
        // si connecté mais pas autorisé, rediriger sur /
        $user = current_user();
        if (in_array($user['role'], ['etat', 'admin'])) {
            header('Location: /portail/etat');
        } else {
            header('Location: /');
        }
        exit;
    }
    require __DIR__ . '/../templates/etat/login.php';
    exit;
}

// Route: portail Brinks (redirection vers la page de connexion)
if ($path === '/portail/brinks') {
    header('Location: /login');
    exit;
}

// Route: déconnexion
if ($path === '/logout') {
    logout();
    header('Location: /');
    exit;
}

// Toutes les routes suivantes nécessitent authentification
if (!is_logged_in()) {
    // Si l'utilisateur essaie d'accéder à un portail, rediriger vers le login adapté
    if (strpos($path, '/portail/entreprise') === 0 || strpos($path, '/login/entreprise') === 0) {
        header('Location: /login/entreprise');
        exit;
    }
    if (strpos($path, '/portail/etat') === 0 || strpos($path, '/login/etat') === 0) {
        header('Location: /login/etat');
        exit;
    }

    // Par défaut, rediriger vers le login général BRINKS
    header('Location: /login');
    exit;
}

// Route: mon profil
if ($path === '/profil') {
    $user = current_user();
    $db = get_db();
    
    // Récupérer les infos complètes de l'utilisateur
    $stmt = $db->prepare('SELECT u.*, o.nom as organisation_nom FROM users u JOIN organisations o ON u.organisation_id = o.id WHERE u.id = ?');
    $stmt->execute([$user['id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Statistiques
    $stmt = $db->prepare('SELECT COUNT(*) as total FROM convois WHERE operateur_id = ?');
    $stmt->execute([$user['id']]);
    $total_convois = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM sanctions WHERE user_id = ? AND statut = 'active'");
    $stmt->execute([$user['id']]);
    $total_sanctions_actives = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM inscriptions_formation WHERE user_id = ? AND resultat = 'reussi'");
    $stmt->execute([$user['id']]);
    $formations_validees = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM inscriptions_formation WHERE user_id = ? AND resultat = 'reussi' AND date_expiration IS NOT NULL AND date_expiration < NOW()");
    $stmt->execute([$user['id']]);
    $formations_expirees = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stats = [
        'total_convois' => $total_convois,
        'total_sanctions_actives' => $total_sanctions_actives,
        'formations_validees' => $formations_validees,
        'formations_expirees' => $formations_expirees
    ];
    
    // Formations validées
    $stmt = $db->prepare("
        SELECT f.id as formation_id, f.titre, f.type, f.validite_mois, i.date_certificat, i.date_expiration
        FROM inscriptions_formation i
        JOIN sessions_formation s ON i.session_id = s.id
        JOIN formations f ON s.formation_id = f.id
        WHERE i.user_id = ? AND i.resultat = 'reussi'
        ORDER BY i.date_certificat DESC
    ");
    $stmt->execute([$user['id']]);
    $formations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Sanctions actives
    $stmt = $db->prepare("
        SELECT s.*
        FROM sanctions s
        WHERE s.user_id = ? AND s.statut = 'active'
        ORDER BY s.date_sanction DESC
    ");
    $stmt->execute([$user['id']]);
    $sanctions_actives = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Derniers convois
    $stmt = $db->prepare('SELECT * FROM convois WHERE operateur_id = ? ORDER BY created_at DESC LIMIT 5');
    $stmt->execute([$user['id']]);
    $derniers_convois = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    require __DIR__ . '/../templates/mon_profil.php';
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
    $total_sanctions_actives = 0;
    
    if ($user['role'] === 'admin') {
        $stmt = $db->query('SELECT COUNT(*) as total FROM users');
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $total_users = $result['total'];
        
        $stmt = $db->query("SELECT COUNT(*) as total FROM sanctions WHERE statut = 'active'");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $total_sanctions_actives = $result['total'];
    } else {
        // Les users voient leurs propres sanctions actives
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM sanctions WHERE user_id = ? AND statut = 'active'");
        $stmt->execute([$user['id']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $total_sanctions_actives = $result['total'];
    }
    
    require __DIR__ . '/../templates/dashboard.php';
    exit;
}

// Route: dashboard entreprise (organisation-specific)
if ($path === '/entreprise/dashboard') {
    $user = current_user();
    $db = get_db();

    // Vérifier que l'utilisateur appartient bien à une organisation
    if (!$user || empty($user['organisation_id'])) {
        header('Location: /');
        exit;
    }

    // Statistiques spécifiques à l'organisation
    $stmt = $db->prepare('SELECT COUNT(*) as total_convois FROM convois WHERE organisation_id = ?');
    $stmt->execute([$user['organisation_id']]);
    $total_convois = $stmt->fetch(PDO::FETCH_ASSOC)['total_convois'];

    $stmt = $db->prepare('SELECT COUNT(*) as total_operateurs FROM users WHERE organisation_id = ?');
    $stmt->execute([$user['organisation_id']]);
    $total_operateurs = $stmt->fetch(PDO::FETCH_ASSOC)['total_operateurs'];

    $stmt = $db->prepare('SELECT COUNT(*) as open_incidents FROM sanctions s JOIN users u ON s.user_id = u.id WHERE u.organisation_id = ? AND s.statut = "active"');
    $stmt->execute([$user['organisation_id']]);
    $open_incidents = $stmt->fetch(PDO::FETCH_ASSOC)['open_incidents'];

    // Récupérer les 10 derniers convois de l'organisation
    $stmt = $db->prepare('SELECT c.*, o.nom as organisation_nom FROM convois c JOIN organisations o ON c.organisation_id = o.id WHERE c.organisation_id = ? ORDER BY c.created_at DESC LIMIT 10');
    $stmt->execute([$user['organisation_id']]);
    $convois_org = $stmt->fetchAll(PDO::FETCH_ASSOC);

    require __DIR__ . '/../templates/entreprise/dashboard.php';
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

// Route: créer un utilisateur (admin uniquement) - DOIT être avant /utilisateurs
if ($path === '/utilisateurs/create' && $method === 'POST') {
    $user = current_user();
    if ($user['role'] !== 'admin') {
        http_response_code(403);
        echo "Accès refusé";
        exit;
    }
    
    try {
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
            $_SESSION['success'] = 'Utilisateur créé avec succès';
        } else {
            $_SESSION['error'] = 'Tous les champs sont requis';
        }
    } catch (Exception $e) {
        $_SESSION['error'] = 'Erreur: ' . $e->getMessage();
    }
    
    header('Location: /utilisateurs');
    exit;
}

// Route: supprimer un utilisateur (admin uniquement) - DOIT être avant /utilisateurs
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

// Route: voir le détail d'un utilisateur (admin uniquement) - DOIT être avant /utilisateurs
if (preg_match('#^/utilisateurs/(\d+)$#', $path, $matches)) {
    $user = current_user();
    if ($user['role'] !== 'admin') {
        http_response_code(403);
        echo "Accès refusé";
        exit;
    }
    
    $user_id = $matches[1];
    $db = get_db();
    
    // Récupérer l'utilisateur
    $stmt = $db->prepare('SELECT u.*, o.nom as organisation_nom FROM users u JOIN organisations o ON u.organisation_id = o.id WHERE u.id = ?');
    $stmt->execute([$user_id]);
    $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$utilisateur) {
        http_response_code(404);
        echo "Utilisateur introuvable";
        exit;
    }
    
    // Statistiques
    $stmt = $db->prepare('SELECT COUNT(*) as total FROM convois WHERE operateur_id = ?');
    $stmt->execute([$user_id]);
    $total_convois = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM sanctions WHERE user_id = ? AND statut = 'active'");
    $stmt->execute([$user_id]);
    $total_sanctions_actives = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM sanctions WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $total_sanctions = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM inscriptions_formation WHERE user_id = ? AND resultat = 'reussi'");
    $stmt->execute([$user_id]);
    $formations_validees = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stats = [
        'total_convois' => $total_convois,
        'total_sanctions_actives' => $total_sanctions_actives,
        'total_sanctions' => $total_sanctions,
        'formations_validees' => $formations_validees
    ];
    
    // Formations validées
    $stmt = $db->prepare("
        SELECT f.titre, f.type, f.validite_mois, i.date_certificat, i.date_expiration
        FROM inscriptions_formation i
        JOIN sessions_formation s ON i.session_id = s.id
        JOIN formations f ON s.formation_id = f.id
        WHERE i.user_id = ? AND i.resultat = 'reussi'
        ORDER BY i.date_certificat DESC
    ");
    $stmt->execute([$user_id]);
    $formations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Sanctions
    $stmt = $db->prepare("
        SELECT s.*, c.type as convoi_type
        FROM sanctions s
        JOIN convois c ON s.convoi_id = c.id
        WHERE s.user_id = ?
        ORDER BY s.date_sanction DESC
    ");
    $stmt->execute([$user_id]);
    $sanctions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Derniers convois opérés
    $stmt = $db->prepare('SELECT * FROM convois WHERE operateur_id = ? ORDER BY created_at DESC LIMIT 10');
    $stmt->execute([$user_id]);
    $derniers_convois = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    require __DIR__ . '/../templates/utilisateur_detail.php';
    exit;
}

// Route: gestion des utilisateurs (admin uniquement) - Liste
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

// Route: créer une sanction (admin uniquement)
if ($path === '/sanctions/create' && $method === 'POST') {
    $user = current_user();
    if ($user['role'] !== 'admin') {
        http_response_code(403);
        echo "Accès refusé";
        exit;
    }
    
    try {
        $db = get_db();
        $convoi_id = $_POST['convoi_id'] ?? null;
        $user_id = $_POST['user_id'] ?? null;
        $type = $_POST['type'] ?? 'avertissement';
        $motif = $_POST['motif'] ?? '';
        $montant = $_POST['montant'] ?? null;
        $date_sanction = $_POST['date_sanction'] ?? date('Y-m-d');
        $date_fin = $_POST['date_fin'] ?? null;
        
        if ($convoi_id && $user_id && $motif) {
            $stmt = $db->prepare('INSERT INTO sanctions (convoi_id, user_id, type, motif, montant, date_sanction, date_fin, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$convoi_id, $user_id, $type, $motif, $montant, $date_sanction, $date_fin, $user['id']]);
            $_SESSION['success'] = 'Sanction créée avec succès';
        } else {
            $_SESSION['error'] = 'Tous les champs obligatoires doivent être remplis';
        }
    } catch (Exception $e) {
        $_SESSION['error'] = 'Erreur: ' . $e->getMessage();
    }
    
    header('Location: /sanctions');
    exit;
}

// Route: modifier le statut d'une sanction (admin uniquement)
if (preg_match('#^/sanctions/(\d+)/status$#', $path, $matches) && $method === 'POST') {
    $user = current_user();
    if ($user['role'] !== 'admin') {
        http_response_code(403);
        echo "Accès refusé";
        exit;
    }
    
    $sanction_id = $matches[1];
    $statut = $_POST['statut'] ?? 'active';
    
    $db = get_db();
    $stmt = $db->prepare('UPDATE sanctions SET statut = ? WHERE id = ?');
    $stmt->execute([$statut, $sanction_id]);
    
    $_SESSION['success'] = 'Statut mis à jour';
    header('Location: /sanctions');
    exit;
}

// Route: supprimer une sanction (admin uniquement)
if (preg_match('#^/sanctions/(\d+)/delete$#', $path, $matches) && $method === 'POST') {
    $user = current_user();
    if ($user['role'] !== 'admin') {
        http_response_code(403);
        echo "Accès refusé";
        exit;
    }
    
    $sanction_id = $matches[1];
    
    $db = get_db();
    $stmt = $db->prepare('DELETE FROM sanctions WHERE id = ?');
    $stmt->execute([$sanction_id]);
    
    $_SESSION['success'] = 'Sanction supprimée';
    header('Location: /sanctions');
    exit;
}

// Route: gestion des sanctions
if ($path === '/sanctions') {
    $user = current_user();
    $db = get_db();
    
    // Les admins voient tout, les users voient uniquement leurs sanctions
    if ($user['role'] === 'admin') {
        $stmt = $db->prepare('
            SELECT s.*, 
                   u.nom as user_nom, u.prenom as user_prenom, 
                   o.nom as organisation_nom,
                   c.id as convoi_id, c.type as convoi_type,
                   creator.nom as creator_nom
            FROM sanctions s
            JOIN users u ON s.user_id = u.id
            JOIN organisations o ON u.organisation_id = o.id
            JOIN convois c ON s.convoi_id = c.id
            JOIN users creator ON s.created_by = creator.id
            ORDER BY s.created_at DESC
        ');
        $stmt->execute();
    } else {
        $stmt = $db->prepare('
            SELECT s.*, 
                   u.nom as user_nom, u.prenom as user_prenom, 
                   o.nom as organisation_nom,
                   c.id as convoi_id, c.type as convoi_type,
                   creator.nom as creator_nom
            FROM sanctions s
            JOIN users u ON s.user_id = u.id
            JOIN organisations o ON u.organisation_id = o.id
            JOIN convois c ON s.convoi_id = c.id
            JOIN users creator ON s.created_by = creator.id
            WHERE s.user_id = ?
            ORDER BY s.created_at DESC
        ');
        $stmt->execute([$user['id']]);
    }
    
    $sanctions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Pour le formulaire de création (admin uniquement)
    if ($user['role'] === 'admin') {
        $stmt = $db->query('SELECT id, type, created_at FROM convois ORDER BY created_at DESC LIMIT 50');
        $convois = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $stmt = $db->prepare('SELECT u.id, u.nom, u.prenom, o.nom as organisation_nom FROM users u JOIN organisations o ON u.organisation_id = o.id ORDER BY u.nom, u.prenom');
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $convois = [];
        $users = [];
    }
    
    require __DIR__ . '/../templates/sanctions.php';
    exit;
}

// Route: catalogue des formations
if ($path === '/formations') {
    $user = current_user();
    $db = get_db();
    
    $stmt = $db->query("SELECT * FROM formations WHERE statut = 'active' ORDER BY type, titre");
    $formations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Récupère les formations de l'utilisateur avec statut
    $stmt = $db->prepare("
        SELECT f.id as formation_id, f.titre, f.validite_mois, f.type,
               i.resultat, i.date_expiration, i.certificat_delivre
        FROM inscriptions_formation i
        JOIN sessions_formation s ON i.session_id = s.id
        JOIN formations f ON s.formation_id = f.id
        WHERE i.user_id = ? AND i.resultat = 'reussi'
        ORDER BY i.date_certificat DESC
    ");
    $stmt->execute([$user['id']]);
    $mes_formations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    require __DIR__ . '/../templates/formations.php';
    exit;
}

// Route: détail d'une formation + sessions disponibles
if (preg_match('#^/formations/(\d+)$#', $path, $matches)) {
    $formation_id = $matches[1];
    $user = current_user();
    $db = get_db();
    
    $stmt = $db->prepare("SELECT * FROM formations WHERE id = ?");
    $stmt->execute([$formation_id]);
    $formation = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$formation) {
        http_response_code(404);
        echo "Formation introuvable";
        exit;
    }
    
    // Sessions disponibles
    $stmt = $db->prepare("
        SELECT * FROM sessions_formation 
        WHERE formation_id = ? AND statut IN ('planifiee', 'en_cours')
        ORDER BY date_debut
    ");
    $stmt->execute([$formation_id]);
    $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Vérifier si l'utilisateur est déjà inscrit
    $stmt = $db->prepare("
        SELECT i.*, s.date_debut, s.date_fin 
        FROM inscriptions_formation i
        JOIN sessions_formation s ON i.session_id = s.id
        WHERE i.user_id = ? AND s.formation_id = ?
        ORDER BY i.inscrit_le DESC
        LIMIT 1
    ");
    $stmt->execute([$user['id'], $formation_id]);
    $mon_inscription = $stmt->fetch(PDO::FETCH_ASSOC);
    
    require __DIR__ . '/../templates/formation_detail.php';
    exit;
}

// Route: passer le QCM d'une formation
if (preg_match('#^/formations/(\d+)/qcm$#', $path, $matches)) {
    $formation_id = $matches[1];
    $user = current_user();
    $db = get_db();
    
    // Vérifier que l'utilisateur a une inscription active
    $stmt = $db->prepare("
        SELECT i.*, s.formation_id, f.questions_qcm, f.note_passage, f.titre
        FROM inscriptions_formation i
        JOIN sessions_formation s ON i.session_id = s.id
        JOIN formations f ON s.formation_id = f.id
        WHERE i.user_id = ? AND s.formation_id = ? AND i.presence = 'present' AND i.resultat = 'en_attente'
        ORDER BY i.inscrit_le DESC
        LIMIT 1
    ");
    $stmt->execute([$user['id'], $formation_id]);
    $inscription = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$inscription) {
        $_SESSION['error'] = "Vous devez d'abord participer à une session de formation";
        header('Location: /formations/' . $formation_id);
        exit;
    }
    
    if ($method === 'POST') {
        // Correction du QCM
        $questions = json_decode($inscription['questions_qcm'], true);
        $score = 0;
        $total = count($questions);
        
        foreach ($questions as $index => $question) {
            $reponse_user = (int)($_POST['question_' . $index] ?? -1);
            if ($reponse_user === $question['correct']) {
                $score++;
            }
        }
        
        $note = round(($score / $total) * 100);
        $resultat = $note >= $inscription['note_passage'] ? 'reussi' : 'echoue';
        
        // Calculer la date d'expiration si réussi
        $date_expiration = null;
        if ($resultat === 'reussi') {
            $stmt = $db->prepare("SELECT validite_mois FROM formations WHERE id = ?");
            $stmt->execute([$formation_id]);
            $formation = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($formation['validite_mois']) {
                $date_expiration = date('Y-m-d', strtotime('+' . $formation['validite_mois'] . ' months'));
            }
        }
        
        // Mise à jour de l'inscription
        $stmt = $db->prepare("
            UPDATE inscriptions_formation 
            SET note_qcm = ?, resultat = ?, certificat_delivre = ?, date_certificat = ?, date_expiration = ?
            WHERE id = ?
        ");
        $delivre = $resultat === 'reussi' ? 1 : 0;
        $date_cert = $resultat === 'reussi' ? date('Y-m-d') : null;
        $stmt->execute([$note, $resultat, $delivre, $date_cert, $date_expiration, $inscription['id']]);
        
        $_SESSION['qcm_result'] = [
            'note' => $note,
            'resultat' => $resultat,
            'score' => $score,
            'total' => $total
        ];
        
        header('Location: /formations/' . $formation_id);
        exit;
    }
    
    $formation = [
        'id' => $formation_id,
        'titre' => $inscription['titre'],
        'questions' => json_decode($inscription['questions_qcm'], true),
        'note_passage' => $inscription['note_passage']
    ];
    
    require __DIR__ . '/../templates/formation_qcm.php';
    exit;
}

// Route: s'inscrire à une session (POST)
if (preg_match('#^/sessions/(\d+)/inscrire$#', $path, $matches) && $method === 'POST') {
    $session_id = $matches[1];
    $user = current_user();
    $db = get_db();
    
    try {
        $db->beginTransaction();
        
        // Vérifier les places disponibles
        $stmt = $db->prepare("SELECT * FROM sessions_formation WHERE id = ? FOR UPDATE");
        $stmt->execute([$session_id]);
        $session = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$session || $session['places_restantes'] <= 0) {
            throw new Exception("Plus de places disponibles");
        }
        
        // Inscription
        $stmt = $db->prepare("INSERT INTO inscriptions_formation (session_id, user_id) VALUES (?, ?)");
        $stmt->execute([$session_id, $user['id']]);
        
        // Décrémenter les places
        $stmt = $db->prepare("UPDATE sessions_formation SET places_restantes = places_restantes - 1 WHERE id = ?");
        $stmt->execute([$session_id]);
        
        $db->commit();
        $_SESSION['success'] = 'Inscription confirmée';
    } catch (Exception $e) {
        $db->rollBack();
        $_SESSION['error'] = $e->getMessage();
    }
    
    header('Location: /formations/' . $session['formation_id']);
    exit;
}

// Route: admin - gérer les sessions
if ($path === '/admin/sessions' && current_user()['role'] === 'admin') {
    $db = get_db();
    
    $stmt = $db->query("
        SELECT s.*, f.titre as formation_titre, 
               COUNT(i.id) as nb_inscrits
        FROM sessions_formation s
        JOIN formations f ON s.formation_id = f.id
        LEFT JOIN inscriptions_formation i ON s.id = i.session_id
        GROUP BY s.id
        ORDER BY s.date_debut DESC
    ");
    $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt = $db->query("SELECT * FROM formations WHERE statut = 'active' ORDER BY titre");
    $formations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    require __DIR__ . '/../templates/admin_sessions.php';
    exit;
}

// Route: admin - créer une session
if ($path === '/admin/sessions/create' && $method === 'POST' && current_user()['role'] === 'admin') {
    $db = get_db();
    
    $formation_id = $_POST['formation_id'] ?? null;
    $date_debut = $_POST['date_debut'] ?? null;
    $date_fin = $_POST['date_fin'] ?? null;
    $lieu = $_POST['lieu'] ?? '';
    $formateur = $_POST['formateur'] ?? '';
    $places_max = (int)($_POST['places_max'] ?? 20);
    
    if ($formation_id && $date_debut && $date_fin && $lieu && $formateur) {
        $stmt = $db->prepare("
            INSERT INTO sessions_formation (formation_id, date_debut, date_fin, lieu, formateur, places_max, places_restantes)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$formation_id, $date_debut, $date_fin, $lieu, $formateur, $places_max, $places_max]);
        $_SESSION['success'] = 'Session créée';
    }
    
    header('Location: /admin/sessions');
    exit;
}

// Route: admin - gérer les formations (GET)
if ($path === '/admin/formations' && current_user()['role'] === 'admin') {
    $db = get_db();
    
    $stmt = $db->query("SELECT * FROM formations ORDER BY type, titre");
    $formations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    require __DIR__ . '/../templates/admin_formations.php';
    exit;
}

// Route: admin - créer une formation (POST)
if ($path === '/admin/formations/create' && $method === 'POST' && current_user()['role'] === 'admin') {
    $db = get_db();
    
    $titre = $_POST['titre'] ?? '';
    $description = $_POST['description'] ?? '';
    $duree_heures = (int)($_POST['duree_heures'] ?? 0);
    $type = $_POST['type'] ?? 'obligatoire';
    $validite_mois = $_POST['validite_mois'] ? (int)$_POST['validite_mois'] : null;
    $contenu_formation = $_POST['contenu_formation'] ?? '';
    $questions_qcm = $_POST['questions_qcm'] ?? '';
    $note_passage = (int)($_POST['note_passage'] ?? 70);
    
    if ($titre && $description && $duree_heures && $contenu_formation && $questions_qcm) {
        // Valider que questions_qcm est du JSON valide
        $questions_array = json_decode($questions_qcm, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $_SESSION['error'] = 'Le format des questions QCM est invalide (JSON attendu)';
            header('Location: /admin/formations');
            exit;
        }
        
        $stmt = $db->prepare("
            INSERT INTO formations (titre, description, duree_heures, type, validite_mois, contenu_formation, questions_qcm, note_passage)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$titre, $description, $duree_heures, $type, $validite_mois, $contenu_formation, $questions_qcm, $note_passage]);
        $_SESSION['success'] = 'Formation créée avec succès';
    } else {
        $_SESSION['error'] = 'Tous les champs obligatoires doivent être remplis';
    }
    
    header('Location: /admin/formations');
    exit;
}

// Route: admin - gérer les présences d'une session (GET)
if (preg_match('#^/admin/sessions/(\d+)/presences$#', $path, $matches) && current_user()['role'] === 'admin') {
    $session_id = $matches[1];
    $db = get_db();
    
    // Récupérer la session avec le titre de la formation
    $stmt = $db->prepare("
        SELECT s.*, f.titre as formation_titre
        FROM sessions_formation s
        JOIN formations f ON s.formation_id = f.id
        WHERE s.id = ?
    ");
    $stmt->execute([$session_id]);
    $session = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$session) {
        http_response_code(404);
        echo "Session introuvable";
        exit;
    }
    
    // Récupérer tous les inscrits avec leurs informations
    $stmt = $db->prepare("
        SELECT i.id as inscription_id, i.presence, i.note_qcm, i.resultat, i.inscrit_le,
               u.id as user_id, u.nom, u.prenom, u.email,
               o.nom as organisation_nom
        FROM inscriptions_formation i
        JOIN users u ON i.user_id = u.id
        JOIN organisations o ON u.organisation_id = o.id
        WHERE i.session_id = ?
        ORDER BY u.nom, u.prenom
    ");
    $stmt->execute([$session_id]);
    $inscrits = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($method === 'POST') {
        // Traiter la mise à jour des présences
        $presences = $_POST['presences'] ?? [];
        
        try {
            $db->beginTransaction();
            
            // Pour chaque inscrit, mettre à jour sa présence
            foreach ($inscrits as $inscrit) {
                $inscription_id = $inscrit['inscription_id'];
                $is_present = isset($presences[$inscription_id]);
                $presence_value = $is_present ? 'present' : 'absent';
                
                $stmt = $db->prepare("UPDATE inscriptions_formation SET presence = ? WHERE id = ?");
                $stmt->execute([$presence_value, $inscription_id]);
            }
            
            $db->commit();
            $_SESSION['success'] = 'Présences enregistrées avec succès';
            header('Location: /admin/sessions/' . $session_id . '/presences');
            exit;
            
        } catch (Exception $e) {
            $db->rollBack();
            $_SESSION['error'] = 'Erreur lors de l\'enregistrement : ' . $e->getMessage();
        }
    }
    
    require __DIR__ . '/../templates/admin_session_presences.php';
    exit;
}

// Route: portail entreprise (par organisation) - accessible à tous les utilisateurs connectés
if ($path === '/portail/entreprise') {
    $user = current_user();
    $db = get_db();

    // Statistiques par organisation
    $stmt = $db->prepare('SELECT COUNT(*) as total_convois FROM convois WHERE organisation_id = ?');
    $stmt->execute([$user['organisation_id']]);
    $total_convois = $stmt->fetch(PDO::FETCH_ASSOC)['total_convois'];

    $stmt = $db->prepare('SELECT COUNT(*) as total_users FROM users WHERE organisation_id = ?');
    $stmt->execute([$user['organisation_id']]);
    $total_users = $stmt->fetch(PDO::FETCH_ASSOC)['total_users'];

    $stmt = $db->prepare("SELECT COUNT(DISTINCT i.user_id) as total_certifies
        FROM inscriptions_formation i
        JOIN sessions_formation s ON i.session_id = s.id
        WHERE s.formation_id IS NOT NULL AND i.resultat = 'reussi' AND i.date_expiration > NOW() AND s.formation_id IS NOT NULL
        AND EXISTS (SELECT 1 FROM users u WHERE u.id = i.user_id AND u.organisation_id = ?)" );
    $stmt->execute([$user['organisation_id']]);
    $total_certifies = $stmt->fetch(PDO::FETCH_ASSOC)['total_certifies'];

    require __DIR__ . '/../templates/entreprise/portail.php';
    exit;
}

// Route: portail état (global) - restreint aux users avec rôle 'etat' ou 'admin'
if ($path === '/portail/etat') {
    $user = current_user();
    if (!in_array($user['role'], ['etat', 'admin'])) {
        http_response_code(403);
        echo "Accès refusé";
        exit;
    }

    $db = get_db();

    // Statistiques globales
    $stmt = $db->query('SELECT COUNT(*) as total_convois FROM convois');
    $total_convois = $stmt->fetch(PDO::FETCH_ASSOC)['total_convois'];

    $stmt = $db->query('SELECT COUNT(*) as total_users FROM users');
    $total_users = $stmt->fetch(PDO::FETCH_ASSOC)['total_users'];

    $stmt = $db->query("SELECT COUNT(*) as sanctions_actives FROM sanctions WHERE statut = 'active'");
    $sanctions_actives = $stmt->fetch(PDO::FETCH_ASSOC)['sanctions_actives'];

    // Taux de conformité aux formations obligatoires (approximatif)
    $stmt = $db->query("SELECT COUNT(DISTINCT i.user_id) as certifies FROM inscriptions_formation i WHERE i.resultat = 'reussi'");
    $certifies = $stmt->fetch(PDO::FETCH_ASSOC)['certifies'];

    require __DIR__ . '/../templates/etat/portail.php';
    exit;
}

// Route: portail administration - accès réservé aux admins
if ($path === '/portail/admin') {
    $user = current_user();
    if ($user['role'] !== 'admin') {
        http_response_code(403);
        echo "Accès refusé";
        exit;
    }

    $db = get_db();

    // Statistiques rapides
    $stmt = $db->query('SELECT COUNT(*) as total_users FROM users');
    $total_users = $stmt->fetch(PDO::FETCH_ASSOC)['total_users'];

    $stmt = $db->query('SELECT COUNT(*) as total_organisations FROM organisations');
    $total_organisations = $stmt->fetch(PDO::FETCH_ASSOC)['total_organisations'];

    // Liste des utilisateurs
    $stmt = $db->prepare('SELECT u.*, o.nom as organisation_nom FROM users u LEFT JOIN organisations o ON u.organisation_id = o.id ORDER BY u.created_at DESC');
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Pour le formulaire de création on fournit la liste des organisations
    $stmt = $db->query('SELECT id, nom FROM organisations ORDER BY nom');
    $organisations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    require __DIR__ . '/../templates/admin/portail.php';
    exit;
}

// 404 par défaut
http_response_code(404);
echo "Page non trouvée";
