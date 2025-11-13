<?php
// Layout pour le portail État — style sobre
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Portail État</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif; background: #f7f7f9; color: #222; }
    .nav { background: #3b3b3b; color: #fff; padding: 12px 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.06); }
    .nav-content { max-width: 1200px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; }
    .nav-brand { font-size: 18px; font-weight: bold; }
    .nav-links a { color: #fff; text-decoration: none; margin-left: 20px; font-size: 14px; }
    .container { max-width: 1200px; margin: 30px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
    .btn { display: inline-block; padding: 8px 16px; background: #3b3b3b; color: #fff; border-radius: 4px; text-decoration: none; font-size: 14px; }
    .card { background: #fff; border-radius: 8px; padding: 24px; box-shadow: 0 2px 6px rgba(0,0,0,0.04); margin-bottom: 24px; }
  </style>
</head>
<body>
  <div class="nav">
    <div class="nav-content">
      <div class="nav-brand">Portail État</div>
      <div class="nav-links">
        <?php if (is_logged_in()): ?>
          <a href="/profil"><?= htmlspecialchars(current_user()['nom'] ?? current_user()['email']); ?></a>
          <a href="/portail/etat">Tableau de bord</a>
          <a href="/convois">Convois</a>
          <a href="/logout">Déconnexion</a>
        <?php else: ?>
          <a href="/login/etat">Connexion État</a>
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
