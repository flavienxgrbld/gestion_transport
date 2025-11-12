<?php
$title = "Gestion des sessions de formation";
ob_start();
?>

<div class="container">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px">
    <h2>📅 Gestion des sessions</h2>
    <button class="btn btn-primary" onclick="document.getElementById('modal-create').style.display='block'">
      + Créer une session
    </button>
  </div>

  <div class="card">
    <table>
      <thead>
        <tr>
          <th>Formation</th>
          <th>Dates</th>
          <th>Lieu</th>
          <th>Formateur</th>
          <th>Inscrits / Places</th>
          <th>Statut</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($sessions as $s): ?>
          <tr>
            <td><strong><?php echo htmlspecialchars($s['formation_titre']); ?></strong></td>
            <td>
              <?php echo date('d/m/Y', strtotime($s['date_debut'])); ?><br>
              <span class="muted">au <?php echo date('d/m/Y', strtotime($s['date_fin'])); ?></span>
            </td>
            <td><?php echo htmlspecialchars($s['lieu']); ?></td>
            <td><?php echo htmlspecialchars($s['formateur']); ?></td>
            <td>
              <strong><?php echo $s['nb_inscrits']; ?></strong> / <?php echo $s['places_max']; ?>
              <?php if ($s['places_restantes'] == 0): ?>
                <span class="badge" style="background:#dc3545;color:#fff;margin-left:4px">Complet</span>
              <?php endif; ?>
            </td>
            <td>
              <?php
                $statut_colors = [
                  'planifiee' => 'background:#ffc107;color:#000',
                  'en_cours' => 'background:#1f4f8b;color:#fff',
                  'terminee' => 'background:#28a745;color:#fff',
                  'annulee' => 'background:#6c757d;color:#fff'
                ];
                $color = $statut_colors[$s['statut']] ?? 'background:#6c757d;color:#fff';
              ?>
              <span class="badge" style="<?php echo $color; ?>">
                <?php echo ucfirst(str_replace('_', ' ', $s['statut'])); ?>
              </span>
            </td>
            <td>
              <?php if ($s['nb_inscrits'] > 0): ?>
                <a href="/admin/sessions/<?php echo $s['id']; ?>/presences" class="btn btn-primary btn-sm">
                  👥 Présences (<?php echo $s['nb_inscrits']; ?>)
                </a>
              <?php else: ?>
                <span class="muted" style="font-size:13px">Aucun inscrit</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal de création de session -->
<div id="modal-create" style="display:none;position:fixed;z-index:1000;left:0;top:0;width:100%;height:100%;overflow:auto;background-color:rgba(0,0,0,0.4)">
  <div style="background-color:#fefefe;margin:3% auto;padding:0;border:1px solid #888;width:90%;max-width:700px;border-radius:8px">
    <div style="padding:16px 24px;background:#f8f9fa;border-bottom:1px solid #dee2e6;display:flex;justify-content:space-between;align-items:center">
      <h3 style="margin:0">Créer une session de formation</h3>
      <span onclick="document.getElementById('modal-create').style.display='none'" style="cursor:pointer;font-size:28px;font-weight:bold;color:#aaa">&times;</span>
    </div>
    
    <form method="post" action="/admin/sessions/create" style="padding:24px">
      <div style="margin-bottom:16px">
        <label for="formation_id">Formation *</label>
        <select id="formation_id" name="formation_id" required>
          <option value="">-- Sélectionner une formation --</option>
          <?php foreach ($formations as $f): ?>
            <option value="<?php echo $f['id']; ?>">
              <?php echo htmlspecialchars($f['titre']); ?> (<?php echo $f['duree_heures']; ?>h)
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
        <div>
          <label for="date_debut">Date de début *</label>
          <input type="date" id="date_debut" name="date_debut" required>
        </div>
        <div>
          <label for="date_fin">Date de fin *</label>
          <input type="date" id="date_fin" name="date_fin" required>
        </div>
      </div>
      
      <div style="margin-bottom:16px">
        <label for="lieu">Lieu *</label>
        <input type="text" id="lieu" name="lieu" required placeholder="Exemple: Salle de formation - Siège Brinks">
      </div>
      
      <div style="margin-bottom:16px">
        <label for="formateur">Formateur *</label>
        <input type="text" id="formateur" name="formateur" required placeholder="Nom du formateur">
      </div>
      
      <div style="margin-bottom:16px">
        <label for="places_max">Nombre de places *</label>
        <input type="number" id="places_max" name="places_max" value="20" min="1" max="100" required>
      </div>
      
      <div style="display:flex;gap:12px;margin-top:24px">
        <button type="submit" class="btn btn-primary">Créer la session</button>
        <button type="button" class="btn btn-secondary" onclick="document.getElementById('modal-create').style.display='none'">Annuler</button>
      </div>
    </form>
  </div>
</div>

<style>
.badge {
  display: inline-block;
  padding: 4px 8px;
  font-size: 12px;
  font-weight: 600;
  border-radius: 4px;
}
.card {
  background: white;
  border-radius: 8px;
  overflow: hidden;
}
.btn-sm {
  padding: 6px 12px;
  font-size: 14px;
}
</style>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>
