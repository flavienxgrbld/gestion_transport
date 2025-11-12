<?php
$title = "Gestion des présences - " . htmlspecialchars($session['formation_titre']);
ob_start();
?>

<div class="container">
  <div style="margin-bottom:24px">
    <a href="/admin/sessions" style="color:#007bff;text-decoration:none">← Retour aux sessions</a>
  </div>

  <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:24px">
    <div>
      <h2 style="margin:0 0 8px 0">Gestion des présences</h2>
      <div style="font-size:18px;font-weight:600;color:#1f4f8b;margin-bottom:8px">
        <?php echo htmlspecialchars($session['formation_titre']); ?>
      </div>
      <div style="color:#6c757d;font-size:14px">
        📅 Du <?php echo date('d/m/Y', strtotime($session['date_debut'])); ?> 
        au <?php echo date('d/m/Y', strtotime($session['date_fin'])); ?>
        <br>
        📍 <?php echo htmlspecialchars($session['lieu']); ?>
        <br>
        👨‍🏫 Formateur : <?php echo htmlspecialchars($session['formateur']); ?>
      </div>
    </div>
    
    <div style="text-align:right">
      <div style="font-size:14px;color:#6c757d;margin-bottom:4px">Statut de la session</div>
      <?php
        $statut_colors = [
          'planifiee' => '#17a2b8',
          'en_cours' => '#ffc107',
          'terminee' => '#28a745',
          'annulee' => '#dc3545'
        ];
        $statut_labels = [
          'planifiee' => 'Planifiée',
          'en_cours' => 'En cours',
          'terminee' => 'Terminée',
          'annulee' => 'Annulée'
        ];
      ?>
      <span class="badge" style="background:<?php echo $statut_colors[$session['statut']]; ?>;color:white;padding:8px 16px;border-radius:4px;font-size:16px">
        <?php echo $statut_labels[$session['statut']]; ?>
      </span>
    </div>
  </div>

  <!-- Statistiques -->
  <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:16px;margin-bottom:24px">
    <div class="stat-card" style="background:#e3f2fd">
      <div class="stat-value"><?php echo count($inscrits); ?></div>
      <div class="stat-label">Inscrits</div>
    </div>
    
    <div class="stat-card" style="background:#e8f5e9">
      <div class="stat-value"><?php echo count(array_filter($inscrits, fn($i) => $i['presence'] === 'present')); ?></div>
      <div class="stat-label">Présents</div>
    </div>
    
    <div class="stat-card" style="background:#ffebee">
      <div class="stat-value"><?php echo count(array_filter($inscrits, fn($i) => $i['presence'] === 'absent')); ?></div>
      <div class="stat-label">Absents</div>
    </div>
    
    <div class="stat-card" style="background:#f3e5f5">
      <div class="stat-value"><?php echo count(array_filter($inscrits, fn($i) => $i['resultat'] === 'reussi')); ?></div>
      <div class="stat-label">Certifiés</div>
    </div>
  </div>

  <?php if (empty($inscrits)): ?>
    <div class="card" style="text-align:center;padding:48px">
      <div style="font-size:48px;margin-bottom:16px">👥</div>
      <div style="font-size:18px;color:#6c757d;margin-bottom:8px">Aucun inscrit pour cette session</div>
      <div style="font-size:14px;color:#6c757d">Les utilisateurs doivent s'inscrire via le catalogue des formations</div>
    </div>
  <?php else: ?>
    <div class="card">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
        <h3 style="margin:0">Liste des participants (<?php echo count($inscrits); ?>)</h3>
        
        <div style="display:flex;gap:8px">
          <button type="button" class="btn btn-secondary btn-sm" onclick="toggleAll(true)">
            ✓ Tout cocher
          </button>
          <button type="button" class="btn btn-secondary btn-sm" onclick="toggleAll(false)">
            ✗ Tout décocher
          </button>
        </div>
      </div>

      <form method="post" action="/admin/sessions/<?php echo $session['id']; ?>/presences" id="presenceForm">
        <table>
          <thead>
            <tr>
              <th style="width:50px">Présent</th>
              <th>Nom</th>
              <th>Prénom</th>
              <th>Email</th>
              <th>Organisation</th>
              <th>Inscrit le</th>
              <th>Présence actuelle</th>
              <th>QCM</th>
              <th>Résultat</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($inscrits as $inscrit): ?>
              <tr>
                <td style="text-align:center">
                  <input 
                    type="checkbox" 
                    name="presences[<?php echo $inscrit['inscription_id']; ?>]" 
                    value="1"
                    <?php echo $inscrit['presence'] === 'present' ? 'checked' : ''; ?>
                    style="width:20px;height:20px;cursor:pointer"
                    class="presence-checkbox"
                  >
                </td>
                <td><strong><?php echo htmlspecialchars($inscrit['nom']); ?></strong></td>
                <td><?php echo htmlspecialchars($inscrit['prenom']); ?></td>
                <td><?php echo htmlspecialchars($inscrit['email']); ?></td>
                <td><?php echo htmlspecialchars($inscrit['organisation_nom']); ?></td>
                <td><?php echo date('d/m/Y H:i', strtotime($inscrit['inscrit_le'])); ?></td>
                <td>
                  <?php if ($inscrit['presence'] === 'present'): ?>
                    <span class="badge" style="background:#28a745;color:white;padding:4px 8px;border-radius:4px;font-size:12px">
                      ✓ Présent
                    </span>
                  <?php elseif ($inscrit['presence'] === 'absent'): ?>
                    <span class="badge" style="background:#dc3545;color:white;padding:4px 8px;border-radius:4px;font-size:12px">
                      ✗ Absent
                    </span>
                  <?php else: ?>
                    <span class="badge" style="background:#6c757d;color:white;padding:4px 8px;border-radius:4px;font-size:12px">
                      En attente
                    </span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if ($inscrit['note_qcm'] !== null): ?>
                    <strong><?php echo $inscrit['note_qcm']; ?>%</strong>
                  <?php else: ?>
                    <span style="color:#6c757d">-</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if ($inscrit['resultat'] === 'reussi'): ?>
                    <span class="badge" style="background:#28a745;color:white;padding:4px 8px;border-radius:4px;font-size:12px">
                      ✓ Réussi
                    </span>
                  <?php elseif ($inscrit['resultat'] === 'echoue'): ?>
                    <span class="badge" style="background:#dc3545;color:white;padding:4px 8px;border-radius:4px;font-size:12px">
                      ✗ Échoué
                    </span>
                  <?php else: ?>
                    <span class="badge" style="background:#ffc107;color:#333;padding:4px 8px;border-radius:4px;font-size:12px">
                      En attente
                    </span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <div style="margin-top:24px;padding:16px;background:#fff3cd;border-radius:4px;border-left:4px solid #ffc107;margin-bottom:24px">
          <div style="font-weight:600;margin-bottom:8px">ℹ️ Instructions :</div>
          <ul style="margin:0;padding-left:20px;color:#856404">
            <li>Cochez les participants présents à la session de formation</li>
            <li>Seuls les participants marqués comme "Présent" pourront passer le QCM</li>
            <li>Les participants peuvent passer le QCM immédiatement après validation des présences</li>
            <li>Une fois le QCM réussi (≥70%), le certificat est délivré automatiquement</li>
          </ul>
        </div>

        <div style="display:flex;gap:12px;justify-content:flex-end">
          <a href="/admin/sessions" class="btn btn-secondary">Annuler</a>
          <button type="submit" class="btn btn-primary" style="font-size:16px;padding:12px 24px">
            💾 Enregistrer les présences
          </button>
        </div>
      </form>
    </div>
  <?php endif; ?>
</div>

<script>
function toggleAll(checked) {
  const checkboxes = document.querySelectorAll('.presence-checkbox');
  checkboxes.forEach(cb => cb.checked = checked);
}

// Confirmation avant soumission
document.getElementById('presenceForm')?.addEventListener('submit', function(e) {
  const checkedCount = document.querySelectorAll('.presence-checkbox:checked').length;
  const totalCount = document.querySelectorAll('.presence-checkbox').length;
  
  if (!confirm(`Confirmer l'enregistrement des présences ?\n\n${checkedCount} présent(s) sur ${totalCount} inscrit(s)`)) {
    e.preventDefault();
  }
});
</script>

<style>
.stat-card {
  padding: 24px;
  border-radius: 8px;
  text-align: center;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.stat-value {
  font-size: 36px;
  font-weight: bold;
  color: #333;
  margin-bottom: 8px;
}

.stat-label {
  font-size: 14px;
  color: #6c757d;
  text-transform: uppercase;
  font-weight: 600;
}

.btn-sm {
  padding: 6px 12px;
  font-size: 14px;
}

table tr:hover {
  background: #f8f9fa;
}
</style>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>
