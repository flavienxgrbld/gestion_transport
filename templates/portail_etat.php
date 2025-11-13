<?php
$title = 'Portail État';
ob_start();
?>

<div class="card">
  <h2>Portail État</h2>
  <p>Utilisateur: <strong><?= htmlspecialchars(current_user()['nom'] ?? current_user()['email']) ?></strong></p>
  <div class="stats" style="margin-top:16px;">
    <div class="stat-card">
      <div class="stat-label">Convois (total)</div>
      <div class="stat-value"><?= (int)$total_convois ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Utilisateurs (total)</div>
      <div class="stat-value"><?= (int)$total_users ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Sanctions actives</div>
      <div class="stat-value"><?= (int)$sanctions_actives ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Utilisateurs certifiés (total)</div>
      <div class="stat-value"><?= (int)$certifies ?></div>
    </div>
  </div>

  <h3 style="margin-top:20px;">Actions rapides</h3>
  <div style="display:flex;gap:10px;margin-top:10px;">
    <a class="btn" href="/convois">Voir convois</a>
    <a class="btn" href="/sanctions">Voir sanctions</a>
    <a class="btn" href="/admin/sessions">Gérer sessions</a>
  </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>