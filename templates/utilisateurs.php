<?php
$title = "Gestion des utilisateurs";
ob_start();
?>

<div class="container">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px">
    <h2>Gestion des utilisateurs</h2>
    <button class="btn btn-primary" onclick="document.getElementById('modal-create').style.display='block'">
      + Créer un utilisateur
    </button>
  </div>

  <?php if (isset($_GET['error']) && $_GET['error'] === 'self_delete'): ?>
    <div class="alert alert-danger" style="margin-bottom:20px;padding:12px;background:#f8d7da;border:1px solid #f5c6cb;border-radius:4px;color:#721c24">
      ⚠️ Vous ne pouvez pas supprimer votre propre compte
    </div>
  <?php endif; ?>

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
            <td><?php echo htmlspecialchars($u['nom']); ?></td>
            <td><?php echo htmlspecialchars($u['prenom']); ?></td>
            <td><?php echo htmlspecialchars($u['email']); ?></td>
            <td>
              <?php if ($u['role'] === 'admin'): ?>
                <span class="badge badge-danger">Admin</span>
              <?php else: ?>
                <span class="badge badge-secondary">Utilisateur</span>
              <?php endif; ?>
            </td>
            <td><?php echo htmlspecialchars($u['organisation_nom']); ?></td>
            <td><?php echo date('d/m/Y', strtotime($u['created_at'])); ?></td>
            <td>
              <?php if ($u['id'] != current_user()['id']): ?>
                <form method="post" action="/utilisateurs/<?php echo $u['id']; ?>/delete" style="display:inline">
                  <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer cet utilisateur ?')">
                    Supprimer
                  </button>
                </form>
              <?php else: ?>
                <span class="muted">Vous</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal de création d'utilisateur -->
<div id="modal-create" style="display:none;position:fixed;z-index:1000;left:0;top:0;width:100%;height:100%;overflow:auto;background-color:rgba(0,0,0,0.4)">
  <div style="background-color:#fefefe;margin:5% auto;padding:0;border:1px solid #888;width:90%;max-width:600px;border-radius:8px">
    <div style="padding:16px 24px;background:#f8f9fa;border-bottom:1px solid #dee2e6;display:flex;justify-content:space-between;align-items:center">
      <h3 style="margin:0">Créer un utilisateur</h3>
      <span onclick="document.getElementById('modal-create').style.display='none'" style="cursor:pointer;font-size:28px;font-weight:bold;color:#aaa">&times;</span>
    </div>
    
    <form method="post" action="/utilisateurs/create" style="padding:24px">
      <div style="margin-bottom:16px">
        <label for="nom">Nom</label>
        <input type="text" id="nom" name="nom" required>
      </div>
      
      <div style="margin-bottom:16px">
        <label for="prenom">Prénom</label>
        <input type="text" id="prenom" name="prenom" required>
      </div>
      
      <div style="margin-bottom:16px">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required>
      </div>
      
      <div style="margin-bottom:16px">
        <label for="password">Mot de passe</label>
        <input type="password" id="password" name="password" required minlength="8">
        <div class="muted" style="margin-top:4px">Minimum 8 caractères</div>
      </div>
      
      <div style="margin-bottom:16px">
        <label for="role">Rôle</label>
        <select id="role" name="role" required>
          <option value="user">Utilisateur</option>
          <option value="admin">Administrateur</option>
        </select>
      </div>
      
      <div style="margin-bottom:16px">
        <label for="organisation_id">Organisation</label>
        <select id="organisation_id" name="organisation_id" required>
          <option value="">-- Sélectionner --</option>
          <?php foreach ($organisations as $org): ?>
            <option value="<?php echo $org['id']; ?>"><?php echo htmlspecialchars($org['nom']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      
      <div style="display:flex;gap:12px;margin-top:24px">
        <button type="submit" class="btn btn-primary">Créer l'utilisateur</button>
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
.badge-danger {
  background: #dc3545;
  color: white;
}
.badge-secondary {
  background: #6c757d;
  color: white;
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
