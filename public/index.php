<?php
// Démarrage de la session
session_start();

// Inclusion des fonctions communes
require_once __DIR__ . '/../src/functions.php';

// Routage simple
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Route: page d'accueil
if ($path === '/' || $path === '/index.php') {
    $title = 'Accueil';
    ob_start();
    ?>
    <h1>Bienvenue sur le système de gestion de transport</h1>
    <p>Système de gestion des convois et formations pour BRINKS.</p>

    <?php if (!is_logged_in()): ?>
        <p><a href="/login">Se connecter</a></p>
    <?php else: ?>
        <p>Connecté en tant que <?php echo htmlspecialchars(current_user()['nom']); ?></p>
        <p><a href="/logout">Se déconnecter</a></p>
    <?php endif; ?>
    <?php
    $content = ob_get_clean();
    require_once __DIR__ . '/../templates/layout.php';
    exit;
}

// Route: connexion (temporaire - à développer)
if ($path === '/login') {
    $title = 'Connexion';
    ob_start();
    ?>
    <h1>Connexion</h1>
    <form method="post" action="/login">
        <div>
            <label>Email:</label>
            <input type="email" name="email" required>
        </div>
        <div>
            <label>Mot de passe:</label>
            <input type="password" name="password" required>
        </div>
        <button type="submit">Se connecter</button>
    </form>
    <?php
    $content = ob_get_clean();
    require_once __DIR__ . '/../templates/layout.php';
    exit;
}

// Route: déconnexion
if ($path === '/logout') {
    session_destroy();
    redirect('/');
}

// Route 404
http_response_code(404);
echo "Page non trouvée";
