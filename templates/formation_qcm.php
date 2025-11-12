<?php
$title = "QCM - " . htmlspecialchars($formation['titre']);
ob_start();
?>

<div class="container" style="max-width:900px">
  <h2>📝 QCM - <?php echo htmlspecialchars($formation['titre']); ?></h2>
  
  <div class="alert" style="background:#fff3cd;border:1px solid #ffeaa7;color:#856404;padding:16px;border-radius:4px;margin-bottom:24px">
    <strong>⚠️ Important :</strong>
    <ul style="margin:8px 0 0 20px">
      <li>Vous devez obtenir au minimum <strong><?php echo $formation['note_passage']; ?>%</strong> pour valider</li>
      <li>Il y a <strong><?php echo count($formation['questions']); ?> questions</strong></li>
      <li>Une seule tentative possible</li>
      <li>Lisez attentivement chaque question</li>
    </ul>
  </div>

  <form method="post" action="/formations/<?php echo $formation['id']; ?>/qcm">
    <?php foreach ($formation['questions'] as $index => $q): ?>
      <div class="card" style="padding:20px;margin-bottom:20px">
        <h4 style="margin-bottom:16px">Question <?php echo ($index + 1); ?></h4>
        <p style="font-size:16px;margin-bottom:16px"><strong><?php echo htmlspecialchars($q['question']); ?></strong></p>
        
        <div style="display:flex;flex-direction:column;gap:10px">
          <?php foreach ($q['reponses'] as $idx_rep => $reponse): ?>
            <label style="display:flex;align-items:center;padding:12px;background:#f8f9fa;border-radius:4px;cursor:pointer">
              <input type="radio" name="question_<?php echo $index; ?>" value="<?php echo $idx_rep; ?>" required 
                     style="margin-right:10px;width:18px;height:18px">
              <span><?php echo htmlspecialchars($reponse); ?></span>
            </label>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>

    <div style="margin-top:32px;text-align:center">
      <button type="submit" class="btn btn-primary" style="padding:12px 32px;font-size:16px" 
              onclick="return confirm('Êtes-vous sûr de vouloir valider vos réponses ? Vous ne pourrez plus modifier.')">
        ✅ Valider le QCM
      </button>
    </div>
  </form>
</div>

<style>
.card {
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
input[type="radio"]:checked + span {
  font-weight: 600;
  color: #1f4f8b;
}
label:has(input[type="radio"]:checked) {
  background: #e3f2fd !important;
  border: 2px solid #1f4f8b;
}
</style>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>
