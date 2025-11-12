<?php
$title = 'Gestion des Formations';
ob_start();
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-mortarboard"></i> Gestion des Formations</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createFormationModal">
            <i class="bi bi-plus-circle"></i> Nouvelle Formation
        </button>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Liste des formations -->
    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="card-title mb-4">Formations existantes (<?= count($formations) ?>)</h5>
            
            <?php if (empty($formations)): ?>
                <p class="text-muted text-center py-4">Aucune formation créée pour le moment.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Titre</th>
                                <th>Type</th>
                                <th>Durée</th>
                                <th>Validité</th>
                                <th>Note passage</th>
                                <th>Statut</th>
                                <th>Créée le</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($formations as $formation): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($formation['titre']) ?></strong>
                                        <br>
                                        <small class="text-muted"><?= htmlspecialchars(substr($formation['description'], 0, 80)) ?>...</small>
                                    </td>
                                    <td>
                                        <?php
                                        $badges = [
                                            'obligatoire' => 'danger',
                                            'optionnelle' => 'info',
                                            'recyclage' => 'warning'
                                        ];
                                        $badge = $badges[$formation['type']] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?= $badge ?>"><?= ucfirst($formation['type']) ?></span>
                                    </td>
                                    <td><?= $formation['duree_heures'] ?>h</td>
                                    <td>
                                        <?= $formation['validite_mois'] ? $formation['validite_mois'] . ' mois' : '<span class="text-muted">Illimité</span>' ?>
                                    </td>
                                    <td><?= $formation['note_passage'] ?>%</td>
                                    <td>
                                        <span class="badge bg-<?= $formation['statut'] === 'active' ? 'success' : 'secondary' ?>">
                                            <?= ucfirst($formation['statut']) ?>
                                        </span>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($formation['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal: Créer une formation -->
<div class="modal fade" id="createFormationModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nouvelle Formation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="/admin/formations/create">
                <div class="modal-body">
                    <div class="row">
                        <!-- Colonne gauche -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Titre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="titre" required 
                                       placeholder="Ex: Formation Radio - Communication & Codes 10">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="description" rows="3" required
                                          placeholder="Description courte de la formation"></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Durée (heures) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="duree_heures" min="1" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Type <span class="text-danger">*</span></label>
                                    <select class="form-select" name="type" required>
                                        <option value="obligatoire">Obligatoire</option>
                                        <option value="optionnelle">Optionnelle</option>
                                        <option value="recyclage">Recyclage</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Validité (mois)</label>
                                    <input type="number" class="form-control" name="validite_mois" min="1"
                                           placeholder="Laisser vide si illimité">
                                    <small class="text-muted">Expiration du certificat</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Note de passage (%) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="note_passage" value="70" min="0" max="100" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Contenu de la formation <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="contenu_formation" rows="8" required
                                          placeholder="Détaillez le contenu (modules, chapitres, etc.)"></textarea>
                                <small class="text-muted">Utilisez HTML ou Markdown pour le formatage</small>
                            </div>
                        </div>

                        <!-- Colonne droite -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Questions QCM (JSON) <span class="text-danger">*</span></label>
                                <textarea class="form-control font-monospace" name="questions_qcm" rows="20" required 
                                          id="questionsQcm" style="font-size: 0.85rem;"></textarea>
                                <small class="text-muted">Format JSON attendu (voir exemple ci-dessous)</small>
                            </div>

                            <div class="alert alert-info">
                                <strong>Format JSON requis:</strong>
                                <pre class="mb-0 mt-2" style="font-size: 0.75rem;">[
  {
    "question": "Question 1 ?",
    "reponses": ["Réponse A", "Réponse B", "Réponse C"],
    "correct": 0
  },
  {
    "question": "Question 2 ?",
    "reponses": ["Réponse A", "Réponse B", "Réponse C"],
    "correct": 1
  }
]</pre>
                                <small class="d-block mt-2"><strong>Note:</strong> "correct" est l'index (0, 1, 2...) de la bonne réponse</small>
                            </div>

                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="validateJSON()">
                                <i class="bi bi-check-circle"></i> Valider le JSON
                            </button>
                            <span id="jsonValidation" class="ms-2"></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Créer la formation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function validateJSON() {
    const textarea = document.getElementById('questionsQcm');
    const validation = document.getElementById('jsonValidation');
    
    try {
        const json = JSON.parse(textarea.value);
        
        if (!Array.isArray(json)) {
            throw new Error('Le JSON doit être un tableau');
        }
        
        json.forEach((q, index) => {
            if (!q.question || !Array.isArray(q.reponses) || typeof q.correct !== 'number') {
                throw new Error(`Question ${index + 1}: format invalide`);
            }
            if (q.correct < 0 || q.correct >= q.reponses.length) {
                throw new Error(`Question ${index + 1}: index "correct" hors limites`);
            }
        });
        
        validation.innerHTML = '<span class="text-success"><i class="bi bi-check-circle-fill"></i> JSON valide (' + json.length + ' questions)</span>';
    } catch (e) {
        validation.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle-fill"></i> Erreur: ' + e.message + '</span>';
    }
}

// Pré-remplir avec un exemple
document.addEventListener('DOMContentLoaded', function() {
    const example = [
        {
            "question": "Exemple de question ?",
            "reponses": ["Réponse A", "Réponse B", "Réponse C"],
            "correct": 0
        }
    ];
    document.getElementById('questionsQcm').value = JSON.stringify(example, null, 2);
});
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>
