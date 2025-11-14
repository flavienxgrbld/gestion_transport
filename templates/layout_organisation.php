<?php
// Layout dynamique par organisation
// Récupère les paramètres de branding de l'organisation de l'utilisateur connecté

$brand = 'Mon Entreprise';
$couleur_primaire = '#19692d';
$couleur_secondaire = '#f4fff7';
$logo_url = null;

if (function_exists('is_logged_in') && is_logged_in()) {
    $u = current_user();
    if (!empty($u['organisation_id']) && function_exists('get_db')) {
        try {
            $db = get_db();
            $stmt = $db->prepare('SELECT nom, couleur_primaire, couleur_secondaire, logo_url FROM organisations WHERE id = ? LIMIT 1');
            $stmt->execute([$u['organisation_id']]);
            $org = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($org) {
                $brand = $org['nom'] ?? $brand;
                $couleur_primaire = $org['couleur_primaire'] ?? $couleur_primaire;
                $couleur_secondaire = $org['couleur_secondaire'] ?? $couleur_secondaire;
                $logo_url = $org['logo_url'] ?? $logo_url;
            }
        } catch (Exception $e) { /* ignore */ }
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= htmlspecialchars($brand) ?></title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { 
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif; 
      background: <?= htmlspecialchars($couleur_secondaire) ?>; 
      color: #233; 
    }
    .nav { 
      background: <?= htmlspecialchars($couleur_primaire) ?>; 
      color: #fff; 
      padding: 12px 20px; 
      box-shadow: 0 2px 4px rgba(0,0,0,0.06); 
    }
    .nav-content { 
      max-width: 1200px; 
      margin: 0 auto; 
      display: flex; 
      justify-content: space-between; 
      align-items: center; 
    }
    .nav-brand { 
      font-size: 18px; 
      font-weight: bold; 
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .nav-brand img {
      height: 32px;
      width: auto;
    }
    .nav-links a { 
      color: #fff; 
      text-decoration: none; 
      margin-left: 20px; 
      font-size: 14px; 
    }
    .nav-links a:hover {
      text-decoration: underline;
    }
    .container { 
      max-width: 1200px; 
      margin: 30px auto; 
      padding: 20px; 
      background: #fff; 
      border-radius: 8px; 
      box-shadow: 0 2px 8px rgba(0,0,0,0.06); 
    }
    .btn { 
      display: inline-block; 
      padding: 8px 16px; 
      background: <?= htmlspecialchars($couleur_primaire) ?>; 
      color: #fff; 
      border-radius: 4px; 
      text-decoration: none; 
      font-size: 14px; 
      border: none;
      cursor: pointer;
    }
    .btn:hover {
      opacity: 0.9;
      text-decoration: none;
    }
    .card { 
      background: #fff; 
      border-radius: 8px; 
      padding: 24px; 
      box-shadow: 0 2px 6px rgba(0,0,0,0.04); 
      margin-bottom: 24px; 
    }
    .stat-card { 
      padding: 20px; 
      background: <?= htmlspecialchars($couleur_secondaire) ?>; 
      border-radius: 6px; 
      border-left: 4px solid <?= htmlspecialchars($couleur_primaire) ?>; 
    }
    .stats {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }
    .stat-label {
      font-size: 14px;
      color: #6c757d;
      margin-bottom: 8px;
    }
    .stat-value {
      font-size: 28px;
      font-weight: bold;
      color: <?= htmlspecialchars($couleur_primaire) ?>;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 16px;
    }
    th {
      background: #f5f7fa;
      font-weight: 600;
      text-align: left;
      padding: 12px;
      border-bottom: 2px solid #ddd;
    }
    td {
      padding: 10px 12px;
      border-bottom: 1px solid #eee;
    }
    tr:hover {
      background: #f9fafb;
    }
    a {
      color: <?= htmlspecialchars($couleur_primaire) ?>;
      text-decoration: none;
    }
    a:hover {
      text-decoration: underline;
    }
    .error {
      padding: 12px;
      background: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
      border-radius: 4px;
      margin-bottom: 16px;
    }
    .success {
      padding: 12px;
      background: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
      border-radius: 4px;
      margin-bottom: 16px;
    }
  </style>
</head>
<body>
  <div class="nav">
    <div class="nav-content">
      <div class="nav-brand">
        <?php if ($logo_url): ?>
          <img src="<?= htmlspecialchars($logo_url) ?>" alt="<?= htmlspecialchars($brand) ?>">
        <?php endif; ?>
        <span><?= htmlspecialchars($brand) ?></span>
      </div>
      <div class="nav-links">
        <?php if (is_logged_in()): ?>
          <a href="/profil"><?= htmlspecialchars(current_user()['nom'] ?? current_user()['email']); ?></a>
          <a href="/entreprise/dashboard">Dashboard</a>
          <a href="/convois">Convois</a>
          <a href="/formations">Formations</a>
          <a href="/logout">Déconnexion</a>
        <?php else: ?>
          <a href="/login/entreprise">Connexion</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <div class="container">
    <?php if (!empty($_SESSION['error'])): ?>
      <div class="error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['success'])): ?>
      <div class="success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <?php echo $content; ?>
  </div>
</body>
</html>
