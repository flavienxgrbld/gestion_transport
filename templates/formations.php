<?php
$title = "Catalogue de formations";
$current_user = current_user();
ob_start();
?>

<div class="container">
  <h2>📚 Catalogue de formations</h2>
  
  <?php if (!empty($mes_formations)): ?>
    <div class="alert" style="background:#d4edda;border:1px solid #c3e6cb;color:#155724;padding:12px;border-radius:4px;margin-bottom:20px">
      ✅ Vous avez validé <?php echo count($mes_formations); ?> formation(s)
    </div>
  <?php endif; ?>

  <div style="margin-bottom:30px">
    <h3>Mes formations</h3>
    <?php if (empty($mes_formations)): ?>
      <p class="muted">Aucune formation validée pour le moment</p>
    <?php else: ?>
      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px">
        <?php 
        $formations_map = [];
        foreach ($mes_formations as $mf) {
          $formations_map[$mf['formation_id']] = $mf;
        }
        ?>
        <?php foreach ($mes_formations as $mf): ?>
          <div class="card" style="padding:16px;border-left:4px solid #28a745">
            <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:8px">
              <strong><?php echo htmlspecialchars($mf['titre']); ?></strong>
              <?php
                $badge_color = '#28a745';
                $badge_text = 'Valide';
                if ($mf['date_expiration']) {
                  $jours_restants = (strtotime($mf['date_expiration']) - time()) / 86400;
                  if ($jours_restants < 0) {
                    $badge_color = '#dc3545';
                    $badge_text = 'Expirée';
                  } elseif ($jours_restants < 30) {
                    $badge_color = '#ff9800';
                    $badge_text = 'Expire bientôt';
                  }
                }
              ?>
              <span class="badge" style="background:<?php echo $badge_color; ?>;color:#fff">
                <?php echo $badge_text; ?>
              </span>
            </div>
            <div class="muted" style="font-size:13px">
              <?php if ($mf['date_expiration']): ?>
                Expire le <?php echo date('d/m/Y', strtotime($mf['date_expiration'])); ?>
              <?php else: ?>
                Certificat permanent
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <h3>Formations disponibles</h3>
  
  <?php
    $obligatoires = array_filter($formations, fn($f) => $f['type'] === 'obligatoire');
    $optionnelles = array_filter($formations, fn($f) => $f['type'] === 'optionnelle');
    $recyclages = array_filter($formations, fn($f) => $f['type'] === 'recyclage');
  ?>

  <?php if (!empty($obligatoires)): ?>
    <div style="margin-bottom:30px">
      <h4 style="color:#dc3545;margin-bottom:16px">🔴 Formations obligatoires</h4>
      <div class="card">
        <table>
          <thead>
            <tr>
              <th>Formation</th>
              <th>Durée</th>
              <th>Validité</th>
              <th>Statut</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($obligatoires as $f): ?>
              <?php 
                $est_valide = isset($formations_map[$f['id']]);
                $est_expire = false;
                if ($est_valide && $formations_map[$f['id']]['date_expiration']) {
                  $est_expire = strtotime($formations_map[$f['id']]['date_expiration']) < time();
                }
              ?>
              <tr>
                <td>
                  <strong><?php echo htmlspecialchars($f['titre']); ?></strong>
                  <div class="muted" style="font-size:13px">
                    <?php echo htmlspecialchars(mb_substr($f['description'], 0, 80)) . '...'; ?>
                  </div>
                </td>
                <td><?php echo $f['duree_heures']; ?>h</td>
                <td><?php echo $f['validite_mois'] ? $f['validite_mois'] . ' mois' : 'Permanent'; ?></td>
                <td>
                  <?php if (!$est_valide): ?>
                    <span class="badge" style="background:#dc3545;color:#fff">Non effectuée</span>
                  <?php elseif ($est_expire): ?>
                    <span class="badge" style="background:#dc3545;color:#fff">Expirée</span>
                  <?php else: ?>
                    <span class="badge" style="background:#28a745;color:#fff">✓ Valide</span>
                  <?php endif; ?>
                </td>
                <td>
                  <a href="/formations/<?php echo $f['id']; ?>" class="btn btn-sm">Voir</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endif; ?>

  <?php if (!empty($recyclages)): ?>
    <div style="margin-bottom:30px">
      <h4 style="color:#ff9800;margin-bottom:16px">🟡 Recyclages</h4>
      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(350px,1fr));gap:16px">
        <?php foreach ($recyclages as $f): ?>
          <div class="card" style="padding:20px;border-left:4px solid #ff9800">
            <h4 style="margin-bottom:8px"><?php echo htmlspecialchars($f['titre']); ?></h4>
            <p class="muted" style="margin-bottom:12px;font-size:14px">
              <?php echo htmlspecialchars(mb_substr($f['description'], 0, 100)) . '...'; ?>
            </p>
            <div style="display:flex;justify-content:space-between;align-items:center">
              <span class="muted"><?php echo $f['duree_heures']; ?>h</span>
              <a href="/formations/<?php echo $f['id']; ?>" class="btn btn-sm">En savoir plus</a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>

  <?php if (!empty($optionnelles)): ?>
    <div style="margin-bottom:30px">
      <h4 style="color:#28a745;margin-bottom:16px">🟢 Formations optionnelles</h4>
      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(350px,1fr));gap:16px">
        <?php foreach ($optionnelles as $f): ?>
          <div class="card" style="padding:20px;border-left:4px solid #28a745">
            <h4 style="margin-bottom:8px"><?php echo htmlspecialchars($f['titre']); ?></h4>
            <p class="muted" style="margin-bottom:12px;font-size:14px">
              <?php echo htmlspecialchars(mb_substr($f['description'], 0, 100)) . '...'; ?>
            </p>
            <div style="display:flex;justify-content:space-between;align-items:center">
              <span class="muted"><?php echo $f['duree_heures']; ?>h</span>
              <a href="/formations/<?php echo $f['id']; ?>" class="btn btn-sm">En savoir plus</a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>
</div>

<style>
.card {
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.badge {
  display: inline-block;
  padding: 4px 10px;
  font-size: 12px;
  font-weight: 600;
  border-radius: 4px;
}
.btn-sm {
  padding: 6px 12px;
  font-size: 13px;
}
</style>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>
