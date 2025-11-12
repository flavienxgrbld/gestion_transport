<?php
$title = htmlspecialchars($formation['titre']);
$current_user = current_user();
ob_start();
?>

<div class="container">
  <a href="/formations" style="margin-bottom:16px;display:inline-block">← Retour au catalogue</a>
  
  <h2><?php echo htmlspecialchars($formation['titre']); ?></h2>
  
  <div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;margin-top:24px">
    <div>
      <?php if (isset($_SESSION['qcm_result'])): ?>
        <?php $result = $_SESSION['qcm_result']; unset($_SESSION['qcm_result']); ?>
        <div class="alert" style="background:<?php echo $result['resultat'] === 'reussi' ? '#d4edda' : '#f8d7da'; ?>;border:1px solid <?php echo $result['resultat'] === 'reussi' ? '#c3e6cb' : '#f5c6cb'; ?>;color:<?php echo $result['resultat'] === 'reussi' ? '#155724' : '#721c24'; ?>;padding:16px;border-radius:4px;margin-bottom:20px">
          <h3><?php echo $result['resultat'] === 'reussi' ? '✅ QCM réussi !' : '❌ QCM échoué'; ?></h3>
          <p style="margin:8px 0">
            Note obtenue : <strong><?php echo $result['note']; ?>/100</strong> 
            (<?php echo $result['score']; ?>/<?php echo $result['total']; ?> bonnes réponses)
          </p>
          <?php if ($result['resultat'] === 'reussi'): ?>
            <p>🎓 Votre certificat a été délivré</p>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <div class="card" style="padding:24px;margin-bottom:24px">
        <h3>Description</h3>
        <p><?php echo nl2br(htmlspecialchars($formation['description'])); ?></p>
        
        <?php if ($formation['contenu_formation']): ?>
          <h3 style="margin-top:24px">Contenu de la formation</h3>
          <div style="white-space:pre-line;background:#f8f9fa;padding:16px;border-radius:4px;font-size:14px">
<?php echo htmlspecialchars($formation['contenu_formation']); ?>
          </div>
        <?php endif; ?>
      </div>

      <?php if (!empty($sessions)): ?>
        <div class="card" style="padding:24px">
          <h3>📅 Sessions disponibles</h3>
          <table style="margin-top:16px">
            <thead>
              <tr>
                <th>Dates</th>
                <th>Lieu</th>
                <th>Formateur</th>
                <th>Places</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($sessions as $s): ?>
                <tr>
                  <td>
                    Du <?php echo date('d/m/Y', strtotime($s['date_debut'])); ?><br>
                    au <?php echo date('d/m/Y', strtotime($s['date_fin'])); ?>
                  </td>
                  <td><?php echo htmlspecialchars($s['lieu']); ?></td>
                  <td><?php echo htmlspecialchars($s['formateur']); ?></td>
                  <td>
                    <?php echo $s['places_restantes']; ?> / <?php echo $s['places_max']; ?>
                  </td>
                  <td>
                    <?php if ($s['places_restantes'] > 0): ?>
                      <form method="post" action="/sessions/<?php echo $s['id']; ?>/inscrire">
                        <button type="submit" class="btn btn-sm">S'inscrire</button>
                      </form>
                    <?php else: ?>
                      <span class="muted">Complet</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <div class="alert" style="background:#f8d7da;border:1px solid #f5c6cb;color:#721c24;padding:12px;border-radius:4px">
          ⚠️ Aucune session planifiée pour le moment
        </div>
      <?php endif; ?>
    </div>

    <div>
      <div class="card" style="padding:20px;margin-bottom:16px">
        <h4>Informations</h4>
        <ul style="list-style:none;padding:0;margin:16px 0 0 0">
          <li style="margin-bottom:12px">
            <strong>Type:</strong>
            <span class="badge" style="background:<?php echo $formation['type'] === 'obligatoire' ? '#dc3545' : ($formation['type'] === 'recyclage' ? '#ff9800' : '#28a745'); ?>;color:#fff;margin-left:8px">
              <?php echo ucfirst($formation['type']); ?>
            </span>
          </li>
          <li style="margin-bottom:12px">
            <strong>Durée:</strong> <?php echo $formation['duree_heures']; ?>h
          </li>
          <li style="margin-bottom:12px">
            <strong>Validité:</strong> <?php echo $formation['validite_mois'] ? $formation['validite_mois'] . ' mois' : 'Permanent'; ?>
          </li>
          <li style="margin-bottom:12px">
            <strong>Note de passage:</strong> <?php echo $formation['note_passage']; ?>/100
          </li>
        </ul>
      </div>

      <?php if ($mon_inscription): ?>
        <div class="card" style="padding:20px">
          <h4>Votre statut</h4>
          <div style="margin-top:16px">
            <?php if ($mon_inscription['resultat'] === 'reussi'): ?>
              <span class="badge" style="background:#28a745;color:#fff;font-size:14px">✓ Formation validée</span>
              <p class="muted" style="margin-top:8px;font-size:13px">
                Certificat délivré le <?php echo date('d/m/Y', strtotime($mon_inscription['date_certificat'])); ?>
              </p>
            <?php elseif ($mon_inscription['presence'] === 'present' && $mon_inscription['resultat'] === 'en_attente'): ?>
              <a href="/formations/<?php echo $formation['id']; ?>/qcm" class="btn btn-primary" style="width:100%">
                📝 Passer le QCM
              </a>
            <?php else: ?>
              <span class="badge" style="background:#ff9800;color:#fff">Inscrit</span>
              <p class="muted" style="margin-top:8px;font-size:13px">
                Session du <?php echo date('d/m/Y', strtotime($mon_inscription['date_debut'])); ?>
              </p>
            <?php endif; ?>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>
