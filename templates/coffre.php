<?php
ob_start();
?>

<h2>Coffre : <?php echo htmlspecialchars($coffre['nom']); ?></h2>

<div class="stat-card" style="margin-bottom:30px;max-width:400px">
  <div class="stat-label">Quantité actuelle en stock</div>
  <div class="stat-value"><?php echo (int)$coffre['quantite_actuelle']; ?></div>
  <div class="muted">palettes/cartons disponibles</div>
</div>

<h3>Historique des mouvements</h3>

<?php if (empty($mouvements)): ?>
  <p class="muted">Aucun mouvement enregistré</p>
<?php else: ?>
  <table>
    <thead>
      <tr>
        <th>Date</th>
        <th>Convoi</th>
        <th>Type convoi</th>
        <th>Type mouvement</th>
        <th>Quantité</th>
        <th>Note</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($mouvements as $m): ?>
        <tr>
          <td><?php echo date('d/m/Y H:i', strtotime($m['date'])); ?></td>
          <td><a href="/convois/<?php echo $m['convoi_id']; ?>">#<?php echo $m['convoi_id']; ?></a></td>
          <td><?php echo ucfirst($m['convoi_type']); ?></td>
          <td>
            <?php if ($m['type'] === 'ajout'): ?>
              <span style="color:#28a745">➕ Ajout</span>
            <?php else: ?>
              <span style="color:#d9534f">➖ Retrait</span>
            <?php endif; ?>
          </td>
          <td><strong><?php echo (int)$m['quantite']; ?></strong></td>
          <td class="muted"><?php echo htmlspecialchars($m['note'] ?? '-'); ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>
