<?php
$title = 'Dashboard Entreprise';
ob_start();
?>

<div class="card">
  <h2>Dashboard - <?= htmlspecialchars(current_user()['organisation_id'] ? 'Organisation #' . current_user()['organisation_id'] : '') ?></h2>
  <p>Bienvenue sur le tableau de bord dédié à votre organisation. Ce tableau affiche des indicateurs ciblés et des actions rapides.</p>

  <div class="stats" style="margin-top:16px;">
    <div class="stat-card">
      <div class="stat-label">Convois (organisation)</div>
      <div class="stat-value"><?= (int)$total_convois ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Opérateurs actifs</div>
      <div class="stat-value"><?= (int)$total_operateurs ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Incidents / Sanctions actives</div>
      <div class="stat-value"><?= (int)$open_incidents ?></div>
    </div>
  </div>

  <h3 style="margin-top:20px;">Derniers convois</h3>
  <?php if (empty($convois_org)): ?>
    <p class="muted">Aucun convoi récent pour votre organisation.</p>
  <?php else: ?>
    <table>
      <thead>
        <tr><th>ID</th><th>Type</th><th>Quantité réalisée</th><th>Statut</th><th>Date</th></tr>
      </thead>
      <tbody>
        <?php foreach ($convois_org as $c): ?>
          <tr>
            <td><a href="/convois/<?= $c['id'] ?>"><?= $c['id'] ?></a></td>
            <td><?= htmlspecialchars($c['type']) ?></td>
            <td><?= htmlspecialchars($c['quantite_realisee'] ?? '-') ?></td>
            <td><?= htmlspecialchars($c['statut']) ?></td>
            <td><?= htmlspecialchars($c['created_at']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>

  <h3 style="margin-top:20px;">Actions rapides</h3>
  <div style="display:flex;gap:10px;margin-top:10px;">
    <a class="btn" href="/formations">Catalogue formations</a>
  </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layout_organisation.php';
?>