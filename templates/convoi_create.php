<?php
ob_start();
?>

<h2>Créer un convoi</h2>

<div style="max-width:600px">
  <form method="post" action="/convois/create">
    <div>
      <label for="type">Type de convoi</label>
      <select id="type" name="type" required>
        <option value="recolte">Récolte (récupération palettes → coffre)</option>
        <option value="traitement">Traitement (coffre → traiter → coffre)</option>
        <option value="revente">Revente (coffre → clients)</option>
      </select>
      <div class="muted" style="margin-top:6px">
        <strong>Récolte :</strong> récupère des palettes et les stocke dans le coffre<br>
        <strong>Traitement :</strong> prend des palettes du coffre pour traitement puis renvoie des cartons<br>
        <strong>Revente :</strong> prend des cartons du coffre pour envoi aux clients
      </div>
    </div>
    
    <div>
      <label for="quantite_prevue">Quantité prévue</label>
      <input type="number" id="quantite_prevue" name="quantite_prevue" min="0" required>
      <div class="muted" style="margin-top:6px">
        Nombre de palettes/cartons prévus (1 palette = 1 carton)
      </div>
    </div>
    
    <div>
      <label for="notes">Notes (optionnel)</label>
      <textarea id="notes" name="notes" rows="4" placeholder="Informations complémentaires..."></textarea>
    </div>
    
    <div style="margin-top:24px">
      <button class="btn" type="submit">Créer le convoi</button>
      <a href="/convois" style="margin-left:10px">Annuler</a>
    </div>
  </form>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>
