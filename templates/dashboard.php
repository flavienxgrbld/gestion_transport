<?php
ob_start();
?>

<h2>Tableau de bord</h2>

<div class="stats">
  <div class="stat-card">
    <div class="stat-label">Palettes en stock</div>
    <div class="stat-value"><?php echo (int)($coffre['quantite_palettes'] ?? 0); ?></div>
    <div class="muted">dans le coffre BRINKS</div>
  </div>
  
  <div class="stat-card">
    <div class="stat-label">Cartons en stock</div>
    <div class="stat-value"><?php echo (int)($coffre['quantite_cartons'] ?? 0); ?></div>
    <div class="muted">dans le coffre BRINKS</div>
  </div>
  
  <div class="stat-card">
    <div class="stat-label">Convois en cours</div>
    <div class="stat-value">
      <?php 
        $ouverts = array_filter($convois, fn($c) => $c['statut'] === 'ouvert');
        echo count($ouverts);
      ?>
    </div>
    <div class="muted">non clôturés</div>
  </div>
  
  <div class="stat-card">
    <div class="stat-label">Organisation</div>
    <div class="stat-value" style="font-size:18px">
      <?php
        $db = get_db();
        $stmt = $db->prepare('SELECT nom FROM organisations WHERE id = ?');
        $stmt->execute([current_user()['organisation_id']]);
        $org = $stmt->fetch(PDO::FETCH_ASSOC);
        echo htmlspecialchars($org['nom']);
      ?>
    </div>
  </div>
</div>

<h3>Derniers convois</h3>

<?php if (empty($convois)): ?>
  <p class="muted">Aucun convoi enregistré</p>
<?php else: ?>
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Type</th>
        <th>Organisation</th>
        <th>Qté prévue</th>
        <th>Qté réalisée</th>
        <th>Statut</th>
        <th>Date création</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($convois as $c): ?>
        <tr>
          <td><?php echo $c['id']; ?></td>
          <td><strong><?php echo ucfirst($c['type']); ?></strong></td>
          <td><?php echo htmlspecialchars($c['organisation_nom']); ?></td>
          <td><?php echo (int)$c['quantite_prevue']; ?></td>
          <td><?php echo $c['quantite_realisee'] !== null ? (int)$c['quantite_realisee'] : '-'; ?></td>
          <td>
            <?php if ($c['statut'] === 'termine'): ?>
              <span style="color:#28a745">✓ Terminé</span>
            <?php else: ?>
              <span style="color:#ffc107">○ Ouvert</span>
            <?php endif; ?>
          </td>
          <td class="muted"><?php echo date('d/m/Y H:i', strtotime($c['created_at'])); ?></td>
          <td><a href="/convois/<?php echo $c['id']; ?>">Voir</a></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>

<div style="margin-top:24px">
  <a class="btn" href="/convois/create">➕ Créer un convoi</a>
  <a class="btn" href="/convois" style="background:#6c757d;margin-left:10px">Voir tous les convois</a>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>
