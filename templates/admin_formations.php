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
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <label class="form-label mb-0">Questions QCM <span class="text-danger">*</span></label>
                                <button type="button" class="btn btn-sm btn-success" onclick="addQuestion()">
                                    + Ajouter une question
                                </button>
                            </div>
                            
                            <div id="questionsContainer" style="max-height: 500px; overflow-y: auto;">
                                <!-- Les questions seront ajoutées ici dynamiquement -->
                            </div>
                            
                            <input type="hidden" name="questions_qcm" id="questionsQcmHidden" required>
                            <small class="text-muted">Minimum 5 questions recommandées</small>
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
let questionCounter = 0;

function addQuestion() {
    questionCounter++;
    const container = document.getElementById('questionsContainer');
    
    const questionDiv = document.createElement('div');
    questionDiv.className = 'card mb-3 question-card';
    questionDiv.dataset.questionId = questionCounter;
    questionDiv.innerHTML = `
        <div class="card-header d-flex justify-content-between align-items-center" style="background: #f8f9fa; padding: 10px 15px;">
            <strong class="question-number">Question ${questionCounter}</strong>
            <button type="button" class="btn btn-sm btn-danger" onclick="removeQuestion(${questionCounter})">
                Supprimer
            </button>
        </div>
        <div class="card-body" style="padding: 15px;">
            <div class="mb-3">
                <label class="form-label" style="font-size: 0.9rem;">Énoncé de la question</label>
                <input type="text" class="form-control question-text" placeholder="Ex: Quelle est la fréquence du canal 42 ?" required>
            </div>
            
            <div class="mb-2">
                <label class="form-label" style="font-size: 0.9rem;">Réponses possibles</label>
            </div>
            
            <div class="mb-2">
                <div class="form-check">
                    <input class="form-check-input answer-radio" type="radio" name="correct-${questionCounter}" value="0" required>
                    <input type="text" class="form-control form-control-sm d-inline-block answer-text" style="width: calc(100% - 30px); margin-left: 5px;" placeholder="Réponse A" required>
                </div>
            </div>
            
            <div class="mb-2">
                <div class="form-check">
                    <input class="form-check-input answer-radio" type="radio" name="correct-${questionCounter}" value="1">
                    <input type="text" class="form-control form-control-sm d-inline-block answer-text" style="width: calc(100% - 30px); margin-left: 5px;" placeholder="Réponse B" required>
                </div>
            </div>
            
            <div class="mb-2">
                <div class="form-check">
                    <input class="form-check-input answer-radio" type="radio" name="correct-${questionCounter}" value="2">
                    <input type="text" class="form-control form-control-sm d-inline-block answer-text" style="width: calc(100% - 30px); margin-left: 5px;" placeholder="Réponse C" required>
                </div>
            </div>
            
            <div class="mb-2">
                <div class="form-check">
                    <input class="form-check-input answer-radio" type="radio" name="correct-${questionCounter}" value="3">
                    <input type="text" class="form-control form-control-sm d-inline-block answer-text" style="width: calc(100% - 30px); margin-left: 5px;" placeholder="Réponse D (optionnelle)">
                </div>
            </div>
            
            <small class="text-muted">Cochez la bonne réponse</small>
        </div>
    `;
    
    container.appendChild(questionDiv);
    renumberQuestions();
    updateJSON();
}

function removeQuestion(id) {
    const cards = document.querySelectorAll('.question-card');
    cards.forEach(card => {
        if (parseInt(card.dataset.questionId) === id) {
            card.remove();
        }
    });
    renumberQuestions();
    updateJSON();
}

function renumberQuestions() {
    const cards = document.querySelectorAll('.question-card');
    cards.forEach((card, index) => {
        const numberSpan = card.querySelector('.question-number');
        if (numberSpan) {
            numberSpan.textContent = 'Question ' + (index + 1);
        }
    });
}

function updateJSON() {
    const questions = [];
    const questionDivs = document.querySelectorAll('#questionsContainer .question-card');
    
    questionDivs.forEach((div, index) => {
        const questionText = div.querySelector('.question-text').value;
        const answerInputs = div.querySelectorAll('.answer-text');
        const correctRadio = div.querySelector('.answer-radio:checked');
        
        const reponses = [];
        answerInputs.forEach(input => {
            if (input.value.trim()) {
                reponses.push(input.value.trim());
            }
        });
        
        if (questionText && reponses.length >= 2 && correctRadio) {
            questions.push({
                question: questionText,
                reponses: reponses,
                correct: parseInt(correctRadio.value)
            });
        }
    });
    
    document.getElementById('questionsQcmHidden').value = JSON.stringify(questions);
}

// Mettre à jour le JSON à chaque modification
document.addEventListener('DOMContentLoaded', function() {
    // Ajouter 3 questions par défaut
    addQuestion();
    addQuestion();
    addQuestion();
    
    // Écouter les changements sur le conteneur
    const container = document.getElementById('questionsContainer');
    container.addEventListener('input', updateJSON);
    container.addEventListener('change', updateJSON);
});

// Validation avant soumission
document.querySelector('form').addEventListener('submit', function(e) {
    updateJSON();
    const jsonValue = document.getElementById('questionsQcmHidden').value;
    
    if (!jsonValue || jsonValue === '[]') {
        e.preventDefault();
        alert('Vous devez ajouter au moins une question complète au QCM');
        return false;
    }
    
    const questions = JSON.parse(jsonValue);
    if (questions.length < 1) {
        e.preventDefault();
        alert('Vous devez ajouter au moins une question complète au QCM');
        return false;
    }
});
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>
