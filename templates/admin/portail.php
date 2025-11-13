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

<?php
$content = ob_get_clean();
require __DIR__ . '/../layout_admin.php';
?>
