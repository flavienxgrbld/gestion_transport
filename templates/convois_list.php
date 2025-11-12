<?php
ob_start();
?>

<h2>Liste des convois</h2>

<div style="margin-bottom:20px">
  <a class="btn" href="/convois/create">➕ Créer un convoi</a>
</div>

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
        <th>Créé le</th>
        <th>Clôturé le</th>
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
          <td class="muted">
            <?php echo $c['date_terminated'] ? date('d/m/Y H:i', strtotime($c['date_terminated'])) : '-'; ?>
          </td>
          <td><a href="/convois/<?php echo $c['id']; ?>">Voir</a></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>
