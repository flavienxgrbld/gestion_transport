<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Gestion Transport'; ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        .nav { background: #333; color: white; padding: 1rem; }
        .nav a { color: white; margin-right: 1rem; text-decoration: none; }
    </style>
</head>
<body>
    <nav class="nav">
        <a href="/">Accueil</a>
        <?php if (is_logged_in()): ?>
            <a href="/logout">Déconnexion</a>
        <?php else: ?>
            <a href="/login">Connexion</a>
        <?php endif; ?>
    </nav>

    <div class="container">
        <?php echo $content ?? ''; ?>
    </div>
</body>
</html>
