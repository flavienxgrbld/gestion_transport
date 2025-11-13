<?php
$title = 'Portail Administration';
ob_start();
?>

<div class="card">
  <h2>Portail Administration</h2>
  <p>Accès administrateur — gestion centrale des utilisateurs et organisations.</p>

  <div class="stats" style="margin-top:16px;display:flex;gap:20px;flex-wrap:wrap;">
    <div class="stat-card">
      <div class="stat-label">Utilisateurs</div>
      <div class="stat-value"><?= (int)$total_users ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Organisations</div>
      <div class="stat-value"><?= (int)$total_organisations ?></div>
    </div>
  </div>

  <h3 style="margin-top:20px;">Organisations</h3>
  <button class="btn" onclick="document.getElementById('modal-create-org').style.display='block'" style="margin-bottom:12px;">
    + Créer une organisation
  </button>
  <div class="card">
    <table>
      <thead>
        <tr>
          <th>Nom</th>
          <th>Type</th>
          <th>Créé le</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($organisations as $org): ?>
          <tr>
            <td><?= htmlspecialchars($org['nom']) ?></td>
            <td><?= htmlspecialchars($org['type'] ?? 'N/A') ?></td>
            <td><?= htmlspecialchars(date('d/m/Y', strtotime($org['created_at'] ?? 'now'))) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <h3 style="margin-top:20px;">Tous les utilisateurs</h3>
  <div class="card">
    <table>
      <thead>
        <tr>
          <th>Nom</th>
          <th>Prénom</th>
          <th>Email</th>
          <th>Rôle</th>
          <th>Organisation</th>
          <th>Créé le</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u): ?>
          <tr>
            <td><?= htmlspecialchars($u['nom']) ?></td>
            <td><?= htmlspecialchars($u['prenom']) ?></td>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td><?= htmlspecialchars($u['role']) ?></td>
            <td><?= htmlspecialchars($u['organisation_nom'] ?? '') ?></td>
            <td><?= htmlspecialchars(date('d/m/Y', strtotime($u['created_at'] ?? 'now'))) ?></td>
            <td>
              <a class="btn" href="/utilisateurs/<?= $u['id'] ?>">Profil</a>
              <?php if ($u['id'] != current_user()['id']): ?>
                <form method="post" action="/utilisateurs/<?= $u['id'] ?>/delete" style="display:inline">
                  <button type="submit" class="btn" style="background:#d9534f;margin-left:8px" onclick="return confirm('Supprimer cet utilisateur ?')">Supprimer</button>
                </form>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal de création d'organisation -->
<div id="modal-create-org" style="display:none;position:fixed;z-index:1000;left:0;top:0;width:100%;height:100%;overflow:auto;background-color:rgba(0,0,0,0.4)">
  <div style="background-color:#fefefe;margin:5% auto;padding:0;border:1px solid #888;width:90%;max-width:600px;border-radius:8px">
    <div style="padding:16px 24px;background:#f8f9fa;border-bottom:1px solid #dee2e6;display:flex;justify-content:space-between;align-items:center">
      <h3 style="margin:0">Créer une organisation</h3>
      <span onclick="document.getElementById('modal-create-org').style.display='none'" style="cursor:pointer;font-size:28px;font-weight:bold;color:#aaa">&times;</span>
    </div>
    
    <form method="post" action="/admin/organisations/create" style="padding:24px">
      <div style="margin-bottom:16px">
        <label for="nom">Nom de l'organisation</label>
        <input type="text" id="nom" name="nom" required>
      </div>
      
      <div style="margin-bottom:16px">
        <label for="type">Type</label>
        <select id="type" name="type" required>
          <option value="">-- Sélectionner --</option>
          <option value="operateur">Opérateur</option>
          <option value="police">Police</option>
          <option value="gendarmerie">Gendarmerie</option>
          <option value="autre">Autre</option>
        </select>
      </div>
      
      <div style="display:flex;gap:12px;margin-top:24px">
        <button type="submit" class="btn">Créer l'organisation</button>
        <button type="button" class="btn" style="background:#6c757d" onclick="document.getElementById('modal-create-org').style.display='none'">Annuler</button>
      </div>
    </form>
  </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layout_admin.php';
?>
