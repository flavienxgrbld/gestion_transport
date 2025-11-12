<?php
$title = "Gestion des sanctions";
$current_user = current_user();
ob_start();
?>

<div class="container">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px">
    <h2>Gestion des sanctions</h2>
    <?php if ($current_user['role'] === 'admin'): ?>
      <button class="btn btn-primary" onclick="document.getElementById('modal-create').style.display='block'">
        + Créer une sanction
      </button>
    <?php endif; ?>
  </div>

  <?php if (empty($sanctions)): ?>
    <p class="muted">Aucune sanction enregistrée</p>
  <?php else: ?>
    <div class="card">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Date</th>
            <th>Utilisateur</th>
            <th>Organisation</th>
            <th>Type</th>
            <th>Convoi</th>
            <th>Motif</th>
            <th>Montant</th>
            <th>Statut</th>
            <?php if ($current_user['role'] === 'admin'): ?>
              <th>Actions</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($sanctions as $s): ?>
            <tr>
              <td>#<?php echo $s['id']; ?></td>
              <td><?php echo date('d/m/Y', strtotime($s['date_sanction'])); ?></td>
              <td>
                <strong><?php echo htmlspecialchars($s['user_nom'] . ' ' . ($s['user_prenom'] ?? '')); ?></strong>
              </td>
              <td><?php echo htmlspecialchars($s['organisation_nom']); ?></td>
              <td>
                <?php 
                  $badge_colors = [
                    'avertissement' => 'background:#ffc107;color:#000',
                    'blame' => 'background:#ff9800;color:#fff',
                    'suspension' => 'background:#dc3545;color:#fff',
                    'autre' => 'background:#6c757d;color:#fff'
                  ];
                  $color = $badge_colors[$s['type']] ?? 'background:#6c757d;color:#fff';
                ?>
                <span class="badge" style="<?php echo $color; ?>">
                  <?php echo ucfirst($s['type']); ?>
                </span>
              </td>
              <td>
                <a href="/convois/<?php echo $s['convoi_id']; ?>">
                  #<?php echo $s['convoi_id']; ?> - <?php echo ucfirst($s['convoi_type']); ?>
                </a>
              </td>
              <td>
                <span title="<?php echo htmlspecialchars($s['motif']); ?>">
                  <?php echo htmlspecialchars(mb_substr($s['motif'], 0, 50)) . (mb_strlen($s['motif']) > 50 ? '...' : ''); ?>
                </span>
              </td>
              <td>
                <?php echo $s['montant'] ? number_format($s['montant'], 2, ',', ' ') . ' €' : '-'; ?>
              </td>
              <td>
                <?php
                  $statut_colors = [
                    'active' => 'background:#28a745;color:#fff',
                    'levee' => 'background:#6c757d;color:#fff',
                    'expiree' => 'background:#ff9800;color:#fff'
                  ];
                  $statut_color = $statut_colors[$s['statut']] ?? 'background:#6c757d;color:#fff';
                ?>
                <span class="badge" style="<?php echo $statut_color; ?>">
                  <?php echo ucfirst($s['statut']); ?>
                </span>
              </td>
              <?php if ($current_user['role'] === 'admin'): ?>
                <td>
                  <div style="display:flex;gap:8px">
                    <?php if ($s['statut'] === 'active'): ?>
                      <form method="post" action="/sanctions/<?php echo $s['id']; ?>/status" style="display:inline">
                        <input type="hidden" name="statut" value="levee">
                        <button type="submit" class="btn btn-sm" style="background:#6c757d;padding:4px 8px;font-size:12px">
                          Lever
                        </button>
                      </form>
                    <?php endif; ?>
                    <form method="post" action="/sanctions/<?php echo $s['id']; ?>/delete" style="display:inline">
                      <button type="submit" class="btn btn-danger btn-sm" style="padding:4px 8px;font-size:12px" onclick="return confirm('Supprimer cette sanction ?')">
                        Supprimer
                      </button>
                    </form>
                  </div>
                </td>
              <?php endif; ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php if ($current_user['role'] === 'admin'): ?>
<!-- Modal de création de sanction -->
<div id="modal-create" style="display:none;position:fixed;z-index:1000;left:0;top:0;width:100%;height:100%;overflow:auto;background-color:rgba(0,0,0,0.4)">
  <div style="background-color:#fefefe;margin:3% auto;padding:0;border:1px solid #888;width:90%;max-width:700px;border-radius:8px">
    <div style="padding:16px 24px;background:#f8f9fa;border-bottom:1px solid #dee2e6;display:flex;justify-content:space-between;align-items:center">
      <h3 style="margin:0">Créer une sanction</h3>
      <span onclick="document.getElementById('modal-create').style.display='none'" style="cursor:pointer;font-size:28px;font-weight:bold;color:#aaa">&times;</span>
    </div>
    
    <form method="post" action="/sanctions/create" style="padding:24px">
      <div style="margin-bottom:16px">
        <label for="convoi_id">Convoi concerné *</label>
        <select id="convoi_id" name="convoi_id" required>
          <option value="">-- Sélectionner un convoi --</option>
          <?php foreach ($convois as $c): ?>
            <option value="<?php echo $c['id']; ?>">
              #<?php echo $c['id']; ?> - <?php echo ucfirst($c['type']); ?> 
              (<?php echo date('d/m/Y', strtotime($c['created_at'])); ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      
      <div style="margin-bottom:16px">
        <label for="user_id">Utilisateur sanctionné *</label>
        <select id="user_id" name="user_id" required>
          <option value="">-- Sélectionner un utilisateur --</option>
          <?php foreach ($users as $u): ?>
            <option value="<?php echo $u['id']; ?>">
              <?php echo htmlspecialchars($u['nom'] . ' ' . ($u['prenom'] ?? '')); ?> 
              (<?php echo htmlspecialchars($u['organisation_nom']); ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      
      <div style="margin-bottom:16px">
        <label for="type">Type de sanction *</label>
        <select id="type" name="type" required>
          <option value="avertissement">Avertissement</option>
          <option value="blame">Blâme</option>
          <option value="suspension">Suspension</option>
          <option value="autre">Autre</option>
        </select>
      </div>
      
      <div style="margin-bottom:16px">
        <label for="motif">Motif *</label>
        <textarea id="motif" name="motif" rows="4" required placeholder="Décrivez la raison de la sanction..."></textarea>
      </div>
      
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
        <div>
          <label for="montant">Montant (€)</label>
          <input type="number" id="montant" name="montant" step="0.01" min="0" placeholder="Optionnel">
          <div class="muted" style="margin-top:4px">Pour les amendes</div>
        </div>
        
        <div>
          <label for="date_sanction">Date de la sanction *</label>
          <input type="date" id="date_sanction" name="date_sanction" value="<?php echo date('Y-m-d'); ?>" required>
        </div>
      </div>
      
      <div style="margin-bottom:16px" id="date_fin_container" style="display:none">
        <label for="date_fin">Date de fin (suspension)</label>
        <input type="date" id="date_fin" name="date_fin">
        <div class="muted" style="margin-top:4px">Si la sanction a une durée limitée</div>
      </div>
      
      <div style="display:flex;gap:12px;margin-top:24px">
        <button type="submit" class="btn btn-primary">Créer la sanction</button>
        <button type="button" class="btn btn-secondary" onclick="document.getElementById('modal-create').style.display='none'">Annuler</button>
      </div>
    </form>
  </div>
</div>

<script>
// Afficher le champ date_fin si suspension est sélectionné
document.getElementById('type').addEventListener('change', function() {
  var dateFin = document.getElementById('date_fin_container');
  if (this.value === 'suspension') {
    dateFin.style.display = 'block';
  } else {
    dateFin.style.display = 'none';
  }
});
</script>
<?php endif; ?>

<style>
.badge {
  display: inline-block;
  padding: 4px 8px;
  font-size: 12px;
  font-weight: 600;
  border-radius: 4px;
}
.btn-sm {
  padding: 4px 12px;
  font-size: 14px;
}
.card {
  background: white;
  border-radius: 8px;
  overflow: hidden;
}
</style>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>
