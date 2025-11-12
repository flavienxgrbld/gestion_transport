<?php
$title = "Profil de " . htmlspecialchars($utilisateur['prenom'] . ' ' . $utilisateur['nom']);
ob_start();
?>

<div class="container">
  <div style="margin-bottom:24px">
    <a href="/utilisateurs" style="color:#007bff;text-decoration:none">← Retour à la liste</a>
  </div>

  <div style="display:flex;gap:24px;margin-bottom:24px;align-items:start">
    <!-- Photo de profil -->
    <div style="width:120px;height:120px;border-radius:50%;background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);display:flex;align-items:center;justify-content:center;color:white;font-size:48px;font-weight:bold">
      <?php echo strtoupper(substr($utilisateur['prenom'], 0, 1) . substr($utilisateur['nom'], 0, 1)); ?>
    </div>
    
    <!-- Informations principales -->
    <div style="flex:1">
      <h2 style="margin:0 0 8px 0"><?php echo htmlspecialchars($utilisateur['prenom'] . ' ' . $utilisateur['nom']); ?></h2>
      <div style="font-size:16px;color:#6c757d;margin-bottom:16px">
        <?php echo htmlspecialchars($utilisateur['email']); ?>
      </div>
      
      <div style="display:flex;gap:12px;margin-bottom:16px">
        <?php if ($utilisateur['role'] === 'admin'): ?>
          <span class="badge" style="background:#dc3545;color:white;padding:6px 12px;border-radius:20px;font-size:14px">
            👑 Administrateur
          </span>
        <?php else: ?>
          <span class="badge" style="background:#6c757d;color:white;padding:6px 12px;border-radius:20px;font-size:14px">
            👤 Utilisateur
          </span>
        <?php endif; ?>
        
        <span class="badge" style="background:#17a2b8;color:white;padding:6px 12px;border-radius:20px;font-size:14px">
          🏢 <?php echo htmlspecialchars($utilisateur['organisation_nom']); ?>
        </span>
      </div>
      
      <div style="color:#6c757d;font-size:14px">
        Membre depuis le <?php echo date('d/m/Y', strtotime($utilisateur['created_at'])); ?>
      </div>
    </div>
    
    <!-- Actions rapides -->
    <?php if (current_user()['role'] === 'admin' && $utilisateur['id'] != current_user()['id']): ?>
      <div>
        <form method="post" action="/utilisateurs/<?php echo $utilisateur['id']; ?>/delete" style="display:inline">
          <button type="submit" class="btn btn-danger" onclick="return confirm('Supprimer cet utilisateur ?')">
            🗑️ Supprimer
          </button>
        </form>
      </div>
    <?php endif; ?>
  </div>

  <!-- Statistiques -->
  <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:16px;margin-bottom:24px">
    <div class="stat-card" style="background:#e3f2fd">
      <div class="stat-value"><?php echo $stats['total_convois']; ?></div>
      <div class="stat-label">Convois opérés</div>
    </div>
    
    <div class="stat-card" style="background:<?php echo $stats['total_sanctions_actives'] > 0 ? '#ffebee' : '#e8f5e9'; ?>">
      <div class="stat-value"><?php echo $stats['total_sanctions_actives']; ?></div>
      <div class="stat-label">Sanctions actives</div>
    </div>
    
    <div class="stat-card" style="background:#fff3e0">
      <div class="stat-value"><?php echo $stats['total_sanctions']; ?></div>
      <div class="stat-label">Total sanctions</div>
    </div>
    
    <div class="stat-card" style="background:#f3e5f5">
      <div class="stat-value"><?php echo $stats['formations_validees']; ?></div>
      <div class="stat-label">Formations validées</div>
    </div>
  </div>

  <!-- Formations -->
  <?php if (!empty($formations)): ?>
    <div class="card" style="margin-bottom:24px">
      <h3 style="margin:0 0 16px 0">📚 Formations validées</h3>
      
      <div style="display:grid;gap:12px">
        <?php foreach ($formations as $f): ?>
          <?php
            $expire_bientot = false;
            $est_expire = false;
            $badge_color = '#28a745';
            $badge_text = 'Valide';
            
            if ($f['date_expiration']) {
              $jours_restants = (strtotime($f['date_expiration']) - time()) / 86400;
              if ($jours_restants < 0) {
                $est_expire = true;
                $badge_color = '#dc3545';
                $badge_text = 'Expirée';
              } elseif ($jours_restants < 30) {
                $expire_bientot = true;
                $badge_color = '#ffc107';
                $badge_text = 'Expire bientôt';
              }
            }
          ?>
          
          <div style="padding:16px;background:#f8f9fa;border-radius:8px;border-left:4px solid <?php echo $badge_color; ?>">
            <div style="display:flex;justify-content:space-between;align-items:start">
              <div style="flex:1">
                <div style="font-weight:600;margin-bottom:4px">
                  <?php echo htmlspecialchars($f['titre']); ?>
                </div>
                <div style="font-size:14px;color:#6c757d">
                  <?php if ($f['type'] === 'obligatoire'): ?>
                    <span style="color:#dc3545;font-weight:600">Obligatoire</span>
                  <?php elseif ($f['type'] === 'optionnelle'): ?>
                    <span style="color:#17a2b8">Optionnelle</span>
                  <?php else: ?>
                    <span style="color:#ffc107">Recyclage</span>
                  <?php endif; ?>
                  • Certifié le <?php echo date('d/m/Y', strtotime($f['date_certificat'])); ?>
                  <?php if ($f['date_expiration']): ?>
                    • Expire le <?php echo date('d/m/Y', strtotime($f['date_expiration'])); ?>
                  <?php endif; ?>
                </div>
              </div>
              
              <span class="badge" style="background:<?php echo $badge_color; ?>;color:white;padding:6px 12px;border-radius:4px;font-size:13px;white-space:nowrap">
                <?php echo $badge_text; ?>
              </span>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>

  <!-- Sanctions -->
  <?php if (!empty($sanctions)): ?>
    <div class="card" style="margin-bottom:24px">
      <h3 style="margin:0 0 16px 0">⚠️ Historique des sanctions</h3>
      
      <table>
        <thead>
          <tr>
            <th>Date</th>
            <th>Type</th>
            <th>Convoi</th>
            <th>Motif</th>
            <th>Montant</th>
            <th>Statut</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($sanctions as $s): ?>
            <tr>
              <td><?php echo date('d/m/Y', strtotime($s['date_sanction'])); ?></td>
              <td>
                <?php
                  $type_colors = [
                    'avertissement' => '#ffc107',
                    'blame' => '#ff9800',
                    'suspension' => '#dc3545',
                    'autre' => '#6c757d'
                  ];
                  $type_labels = [
                    'avertissement' => 'Avertissement',
                    'blame' => 'Blâme',
                    'suspension' => 'Suspension',
                    'autre' => 'Autre'
                  ];
                ?>
                <span class="badge" style="background:<?php echo $type_colors[$s['type']]; ?>;color:white;padding:4px 8px;border-radius:4px;font-size:12px">
                  <?php echo $type_labels[$s['type']]; ?>
                </span>
              </td>
              <td>
                <a href="/convois/<?php echo $s['convoi_id']; ?>" style="color:#007bff;text-decoration:none">
                  Convoi #<?php echo $s['convoi_id']; ?>
                </a>
              </td>
              <td><?php echo htmlspecialchars($s['motif']); ?></td>
              <td><?php echo $s['montant'] ? number_format($s['montant'], 0, ',', ' ') . ' €' : '-'; ?></td>
              <td>
                <?php
                  $statut_colors = [
                    'active' => '#dc3545',
                    'levee' => '#28a745',
                    'expiree' => '#6c757d'
                  ];
                  $statut_labels = [
                    'active' => 'Active',
                    'levee' => 'Levée',
                    'expiree' => 'Expirée'
                  ];
                ?>
                <span class="badge" style="background:<?php echo $statut_colors[$s['statut']]; ?>;color:white;padding:4px 8px;border-radius:4px;font-size:12px">
                  <?php echo $statut_labels[$s['statut']]; ?>
                </span>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>

  <!-- Derniers convois opérés -->
  <?php if (!empty($derniers_convois)): ?>
    <div class="card">
      <h3 style="margin:0 0 16px 0">🚚 Derniers convois opérés</h3>
      
      <table>
        <thead>
          <tr>
            <th>Date</th>
            <th>Type</th>
            <th>Quantité prévue</th>
            <th>Quantité réalisée</th>
            <th>Statut</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($derniers_convois as $c): ?>
            <tr>
              <td><?php echo date('d/m/Y H:i', strtotime($c['created_at'])); ?></td>
              <td>
                <?php
                  $type_colors = [
                    'recolte' => '#28a745',
                    'traitement' => '#ffc107',
                    'revente' => '#dc3545'
                  ];
                  $type_labels = [
                    'recolte' => 'Récolte',
                    'traitement' => 'Traitement',
                    'revente' => 'Revente'
                  ];
                ?>
                <span class="badge" style="background:<?php echo $type_colors[$c['type']]; ?>;color:white;padding:4px 8px;border-radius:4px;font-size:12px">
                  <?php echo $type_labels[$c['type']]; ?>
                </span>
              </td>
              <td><?php echo $c['quantite_prevue']; ?></td>
              <td><?php echo $c['quantite_realisee'] ?? '-'; ?></td>
              <td>
                <?php if ($c['statut'] === 'termine'): ?>
                  <span class="badge" style="background:#28a745;color:white;padding:4px 8px;border-radius:4px;font-size:12px">Terminé</span>
                <?php else: ?>
                  <span class="badge" style="background:#17a2b8;color:white;padding:4px 8px;border-radius:4px;font-size:12px">En cours</span>
                <?php endif; ?>
              </td>
              <td>
                <a href="/convois/<?php echo $c['id']; ?>" class="btn btn-primary btn-sm">Voir</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

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
  padding: 4px 12px;
  font-size: 14px;
}
</style>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>
