<?php
ob_start();
?>

<div style="margin-bottom:20px">
  <a href="/convois">← Retour à la liste</a>
</div>

<h2>Convoi #<?php echo $convoi['id']; ?> - <?php echo ucfirst($convoi['type']); ?></h2>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:30px">
  <div>
    <h3>Informations générales</h3>
    <p><strong>Type :</strong> <?php echo ucfirst($convoi['type']); ?></p>
    <p><strong>Organisation :</strong> <?php echo htmlspecialchars($convoi['organisation_nom']); ?></p>
    <p><strong>Statut :</strong> 
      <?php if ($convoi['statut'] === 'termine'): ?>
        <span style="color:#28a745">✓ Terminé</span>
      <?php else: ?>
        <span style="color:#ffc107">○ Ouvert</span>
      <?php endif; ?>
    </p>
    <p><strong>Créé le :</strong> <?php echo date('d/m/Y à H:i', strtotime($convoi['created_at'])); ?></p>
    <?php if ($convoi['date_terminated']): ?>
      <p><strong>Clôturé le :</strong> <?php echo date('d/m/Y à H:i', strtotime($convoi['date_terminated'])); ?></p>
    <?php endif; ?>
  </div>
  
  <div>
    <h3>Quantités</h3>
    <p><strong>Quantité prévue :</strong> <?php echo (int)$convoi['quantite_prevue']; ?></p>
    <p><strong>Quantité réalisée :</strong> 
      <?php echo $convoi['quantite_realisee'] !== null ? (int)$convoi['quantite_realisee'] : 'Non clôturé'; ?>
    </p>
    <?php if ($convoi['notes']): ?>
      <p><strong>Notes :</strong><br><?php echo nl2br(htmlspecialchars($convoi['notes'])); ?></p>
    <?php endif; ?>
  </div>
</div>

<?php if ($convoi['statut'] === 'ouvert'): ?>
  <div style="background:#fff3cd;padding:16px;border-radius:6px;border-left:4px solid #ffc107;margin-bottom:30px">
    <h3>⚠️ Clôturer le convoi</h3>
    <p class="muted" style="margin-bottom:16px">
      Une fois clôturé, le convoi ne pourra plus être modifié et l'inventaire du coffre sera mis à jour automatiquement.
    </p>
    
    <form method="post" action="/convois/<?php echo $convoi['id']; ?>/close" style="max-width:400px">
      <div>
        <label for="quantite_realisee">Quantité réalisée</label>
        <input type="number" id="quantite_realisee" name="quantite_realisee" min="0" required>
      </div>
      
      <div>
        <label for="note">Note de clôture (optionnel)</label>
        <textarea id="note" name="note" rows="3" placeholder="Observations, incidents, etc."></textarea>
      </div>
      
      <div style="margin-top:16px">
        <button class="btn btn-danger" type="submit" onclick="return confirm('Confirmer la clôture du convoi ? Cette action est irréversible.')">
          Clôturer définitivement
        </button>
      </div>
    </form>
  </div>
<?php else: ?>
  <h3>Mouvements associés</h3>
  
  <?php if (empty($mouvements)): ?>
    <p class="muted">Aucun mouvement enregistré</p>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>Date</th>
          <th>Type</th>
          <th>Quantité</th>
          <th>Note</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($mouvements as $m): ?>
          <tr>
            <td><?php echo date('d/m/Y H:i', strtotime($m['date'])); ?></td>
            <td>
              <?php if ($m['type'] === 'ajout'): ?>
                <span style="color:#28a745">➕ Ajout</span>
              <?php else: ?>
                <span style="color:#d9534f">➖ Retrait</span>
              <?php endif; ?>
            </td>
            <td><?php echo (int)$m['quantite']; ?></td>
            <td><?php echo htmlspecialchars($m['note'] ?? '-'); ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
<?php endif; ?>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>
