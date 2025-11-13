<?php
$title = 'Portails';
ob_start();
?>

<div class="card">
  <h2>Bienvenue — Choisis un portail</h2>
  <p>Sélectionnez le portail adapté à votre profil :</p>

  <div style="display:flex;gap:20px;margin-top:18px;flex-wrap:wrap;">
    <div class="card" style="padding:18px;min-width:220px;flex:1;">
      <h3>Portail Entreprise</h3>
      <p>Accédez aux statistiques et outils dédiés à votre organisation.</p>
      <a class="btn" href="/portail/entreprise">Aller au portail Entreprise</a>
    </div>

    <div class="card" style="padding:18px;min-width:220px;flex:1;">
      <h3>Portail État</h3>
      <p>Accès réservé aux comptes d'État et administrateurs pour consulter les indicateurs nationaux.</p>
      <a class="btn" href="/portail/etat">Aller au portail État</a>
    </div>

    <div class="card" style="padding:18px;min-width:220px;flex:1;">
      <h3>Portail BRINKS</h3>
      <p>Accès général BRINKS — vous serez redirigé vers la page de connexion.</p>
      <a class="btn btn-secondary" href="/portail/brinks">Aller au portail BRINKS</a>
    </div>
  </div>

  <p style="margin-top:18px;color:#666;font-size:0.9rem;">Remarque : certains portails nécessitent une connexion et des droits spécifiques.</p>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>