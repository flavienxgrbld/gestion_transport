<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>BRINKS</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
      background: #f5f7fa;
      color: #333;
    }
    .nav {
      background: #1f4f8b;
      color: #fff;
      padding: 12px 20px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
    }
    .nav-links a {
      color: #fff;
      text-decoration: none;
      margin-left: 20px;
      font-size: 14px;
      position: relative;
      display: inline-block;
    }
    .nav-links a:hover {
      text-decoration: underline;
    }
    .dropdown {
      position: relative;
      display: inline-block;
      margin-left: 20px;
    }
    .dropdown-toggle {
      color: #fff;
      text-decoration: none;
      font-size: 14px;
      cursor: pointer;
      background: none;
      border: none;
      padding: 0;
    }
    .dropdown-toggle:hover {
      text-decoration: underline;
    }
    .dropdown-menu {
      display: none;
      position: absolute;
      top: 100%;
      left: 0;
      background: #fff;
      min-width: 200px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.2);
      border-radius: 4px;
      margin-top: 8px;
      z-index: 1000;
    }
    .dropdown-menu a {
      color: #333;
      display: block;
      padding: 10px 16px;
      text-decoration: none;
      margin: 0;
      border-bottom: 1px solid #eee;
    }
    .dropdown-menu a:last-child {
      border-bottom: none;
    }
    .dropdown-menu a:hover {
      background: #f5f7fa;
      text-decoration: none;
    }
    .dropdown:hover .dropdown-menu {
      display: block;
    }
    .container {
      max-width: 1200px;
      margin: 30px auto;
      padding: 20px;
      background: #fff;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
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
      color: #1f4f8b;
      text-decoration: none;
    }
    a:hover {
      text-decoration: underline;
    }
    .btn {
      display: inline-block;
      padding: 8px 16px;
      background: #1f4f8b;
      color: #fff;
      border-radius: 4px;
      text-decoration: none;
      font-size: 14px;
      border: none;
      cursor: pointer;
    }
    .btn:hover {
      background: #163a6a;
      text-decoration: none;
    }
    .btn-danger {
      background: #d9534f;
    }
    .btn-danger:hover {
      background: #c9302c;
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
    .muted {
      color: #6c757d;
      font-size: 14px;
    }
    h2 {
      margin-bottom: 20px;
      color: #1f4f8b;
    }
    h3 {
      margin: 24px 0 12px 0;
      color: #333;
      font-size: 18px;
    }
    form div {
      margin-bottom: 16px;
    }
    label {
      display: block;
      margin-bottom: 6px;
      font-weight: 500;
      font-size: 14px;
    }
    input[type="text"],
    input[type="email"],
    input[type="password"],
    input[type="number"],
    select,
    textarea {
      width: 100%;
      padding: 8px 12px;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-size: 14px;
      font-family: inherit;
    }
    textarea {
      resize: vertical;
      min-height: 80px;
    }
    .stats {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }
    .stat-card {
      padding: 20px;
      background: #f5f7fa;
      border-radius: 6px;
      border-left: 4px solid #1f4f8b;
    }
    .stat-label {
      font-size: 14px;
      color: #6c757d;
      margin-bottom: 8px;
    }
    .stat-value {
      font-size: 28px;
      font-weight: bold;
      color: #1f4f8b;
    }
    .card {
      background: #fff;
      border-radius: 8px;
      padding: 24px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
      margin-bottom: 24px;
    }
    .btn-primary {
      background: #1f4f8b;
      color: #fff;
    }
    .btn-primary:hover {
      background: #163a6a;
    }
    .btn-secondary {
      background: #6c757d;
      color: #fff;
    }
    .btn-secondary:hover {
      background: #5a6268;
    }
  </style>
</head>
<body>
  <div class="nav">
    <div class="nav-content">
      <div class="nav-brand">Gestion Convois - BRINKS</div>
      <div class="nav-links">
        <?php if (is_logged_in()): ?>
          <a href="/profil" style="margin-right:15px;font-weight:600">👤 <?php echo htmlspecialchars(current_user()['nom'] ?? current_user()['email']); ?></a>
          <a href="/dashboard">Dashboard</a>
          <a href="/convois">Convois</a>
          <a href="/coffre">Coffre</a>
          
          <div class="dropdown">
            <span class="dropdown-toggle">Formations ▾</span>
            <div class="dropdown-menu">
              <a href="/formations">📚 Catalogue</a>
              <?php if (current_user()['role'] === 'admin'): ?>
                <a href="/admin/formations">➕ Créer Formation</a>
                <a href="/admin/sessions">📅 Gérer Sessions</a>
              <?php endif; ?>
            </div>
          </div>
          
          <a href="/sanctions">Sanctions</a>
          <?php if (current_user()['role'] === 'admin'): ?>
            <a href="/utilisateurs">Utilisateurs</a>
          <?php endif; ?>
          <a href="/logout">Déconnexion</a>
        <?php else: ?>
          <a href="/login">Se connecter</a>
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
