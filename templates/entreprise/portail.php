<?php
$title = 'Portail Entreprise';
ob_start();
?>

<div class="card">
  <h2>Portail Entreprise</h2>
  <p>Organisation: <strong><?= htmlspecialchars(current_user()['organisation_id']) ?></strong></p>
  <div class="stats" style="margin-top:16px;">
    <div class="stat-card">
      <div class="stat-label">Convois (total organisation)</div>
      <div class="stat-value"><?= (int)$total_convois ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Utilisateurs (organisation)</div>
      <div class="stat-value"><?= (int)$total_users ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Utilisateurs certifiés (formations valides)</div>
      <div class="stat-value"><?= (int)$total_certifies ?></div>
    </div>
  </div>

  <h3 style="margin-top:20px;">Actions rapides</h3>
  <div style="display:flex;gap:10px;margin-top:10px;">
    <a class="btn" href="/entreprise/dashboard">Voir dashboard entreprise</a>
    <a class="btn" href="/utilisateurs">Voir utilisateurs</a>
    <a class="btn" href="/formations">Catalogue formations</a>
  </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
?>
