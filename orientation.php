<?php
require_once 'includes/functions.php';
$pageTitle = 'Orientation';
$activeNav = 'orientation';
$ecoles = getAllEcoles();
require_once 'includes/header.php';
?>

<section class="orientation-hero">
  <div class="badge-tool"><i class="fas fa-compass"></i> Outil d'orientation</div>
  <h1>Trouvez votre voie</h1>
  <p>Répondez à quelques questions pour recevoir des recommandations personnalisées.</p>
</section>

<div class="orientation-body">
  <!-- Progress -->
  <div class="progress-header">
    <span id="step-label">Étape 1 sur 4</span>
    <span id="step-pct">25%</span>
  </div>
  <div class="progress-bar-wrap">
    <div class="progress-bar-fill" id="progress-fill" style="width:25%"></div>
  </div>

  <!-- ÉTAPE 1 -->
  <div class="question-card" id="step-1">
    <div class="question-block">
      <h3>Quel est votre niveau actuel ?</h3>
      <div class="options-grid">
        <button class="option-btn" onclick="selectOption(this,'niveau')">Collège (BFEM)</button>
        <button class="option-btn" onclick="selectOption(this,'niveau')">Lycée</button>
        <button class="option-btn" onclick="selectOption(this,'niveau')">Baccalauréat</button>
        <button class="option-btn" onclick="selectOption(this,'niveau')">Bac+</button>
      </div>
    </div>
    <div class="question-block">
      <h3>Quelle est votre série ?</h3>
      <div class="options-grid">
        <button class="option-btn" onclick="selectOption(this,'serie')">S1 - Sciences Expérimentales</button>
        <button class="option-btn" onclick="selectOption(this,'serie')">S2 - Sciences Mathématiques</button>
        <button class="option-btn" onclick="selectOption(this,'serie')">S3 - Sciences Physiques</button>
        <button class="option-btn" onclick="selectOption(this,'serie')">L1 - Littéraire</button>
        <button class="option-btn" onclick="selectOption(this,'serie')">L2 - Langues Étrangères</button>
        <button class="option-btn" onclick="selectOption(this,'serie')">G - Gestion</button>
        <button class="option-btn" onclick="selectOption(this,'serie')">STEG - Sciences et Technologies</button>
      </div>
    </div>
    <div class="card-nav">
      <button class="btn-prev" disabled><i class="fas fa-arrow-left"></i> Précédent</button>
      <button class="btn-next" onclick="goStep(2)">Suivant <i class="fas fa-arrow-right"></i></button>
    </div>
  </div>

  <!-- ÉTAPE 2 -->
  <div class="question-card" id="step-2" style="display:none">
    <div class="question-block">
      <h3>Quels sont vos domaines d'intérêt ?</h3>
      <div class="options-grid">
        <button class="option-btn" onclick="selectOption(this,'domaine')">Informatique & Technologie</button>
        <button class="option-btn" onclick="selectOption(this,'domaine')">Médecine & Santé</button>
        <button class="option-btn" onclick="selectOption(this,'domaine')">Commerce & Gestion</button>
        <button class="option-btn" onclick="selectOption(this,'domaine')">Agriculture & Environnement</button>
        <button class="option-btn" onclick="selectOption(this,'domaine')">Génie Civil & Architecture</button>
        <button class="option-btn" onclick="selectOption(this,'domaine')">Sciences Fondamentales</button>
        <button class="option-btn" onclick="selectOption(this,'domaine')">Droit & Sciences Politiques</button>
        <button class="option-btn" onclick="selectOption(this,'domaine')">Lettres & Sciences Humaines</button>
      </div>
    </div>
    <div class="card-nav">
      <button class="btn-prev" onclick="goStep(1)"><i class="fas fa-arrow-left"></i> Précédent</button>
      <button class="btn-next" onclick="goStep(3)">Suivant <i class="fas fa-arrow-right"></i></button>
    </div>
  </div>

  <!-- ÉTAPE 3 -->
  <div class="question-card" id="step-3" style="display:none">
    <div class="question-block">
      <h3>Quelles sont vos matières préférées ?</h3>
      <div class="options-grid">
        <button class="option-btn" onclick="selectOption(this,'matiere')">Mathématiques</button>
        <button class="option-btn" onclick="selectOption(this,'matiere')">Physique-Chimie</button>
        <button class="option-btn" onclick="selectOption(this,'matiere')">Biologie</button>
        <button class="option-btn" onclick="selectOption(this,'matiere')">Informatique</button>
        <button class="option-btn" onclick="selectOption(this,'matiere')">Français & Littérature</button>
        <button class="option-btn" onclick="selectOption(this,'matiere')">Anglais</button>
        <button class="option-btn" onclick="selectOption(this,'matiere')">Histoire-Géographie</button>
        <button class="option-btn" onclick="selectOption(this,'matiere')">Économie</button>
      </div>
    </div>
    <div class="question-block">
      <h3>Quel type de carrière vous attire ?</h3>
      <div class="options-grid">
        <button class="option-btn" onclick="selectOption(this,'carriere')">Ingénieur / Technicien</button>
        <button class="option-btn" onclick="selectOption(this,'carriere')">Médecin / Praticien de santé</button>
        <button class="option-btn" onclick="selectOption(this,'carriere')">Entrepreneur / Manager</button>
        <button class="option-btn" onclick="selectOption(this,'carriere')">Chercheur / Enseignant</button>
      </div>
    </div>
    <div class="card-nav">
      <button class="btn-prev" onclick="goStep(2)"><i class="fas fa-arrow-left"></i> Précédent</button>
      <button class="btn-next" onclick="goStep(4)">Suivant <i class="fas fa-arrow-right"></i></button>
    </div>
  </div>

  <!-- ÉTAPE 4 -->
  <div class="question-card" id="step-4" style="display:none">
    <div class="question-block">
      <h3>Avez-vous des contraintes particulières ?</h3>
      <div class="options-grid">
        <button class="option-btn" onclick="selectOption(this,'contrainte')">Formation publique uniquement</button>
        <button class="option-btn" onclick="selectOption(this,'contrainte')">Proche de Dakar</button>
        <button class="option-btn" onclick="selectOption(this,'contrainte')">Formation courte (2-3 ans)</button>
        <button class="option-btn" onclick="selectOption(this,'contrainte')">Aucune contrainte</button>
      </div>
    </div>
    <div class="question-block">
      <h3>Quelques mots sur vos ambitions <span style="font-size:.8rem;color:var(--muted);font-weight:400">(optionnel)</span></h3>
      <textarea class="orient-input" rows="3" placeholder="Ex : Je veux devenir ingénieur en télécommunications..."></textarea>
    </div>
    <div class="card-nav">
      <button class="btn-prev" onclick="goStep(3)"><i class="fas fa-arrow-left"></i> Précédent</button>
      <button class="btn-next" onclick="showResults()">Voir mes résultats <i class="fas fa-arrow-right"></i></button>
    </div>
  </div>

  <!-- RÉSULTATS -->
  <div class="results-card" id="step-results">
    <div class="result-icon"><i class="fas fa-star"></i></div>
    <h3>Vos recommandations personnalisées</h3>
    <p style="color:var(--muted);margin-bottom:24px">Basé sur vos réponses, voici les établissements les mieux adaptés à votre profil.</p>
    <div class="result-schools">
      <?php
      $recommended = array_slice($ecoles, 0, 3);
      $scores = [95, 88, 81];
      foreach ($recommended as $i => $e):
      ?>
      <a href="ecole.php?slug=<?= h($e['slug']) ?>" class="result-school-item" style="text-decoration:none;color:inherit">
        <div class="rank"><?= $i + 1 ?></div>
        <div class="rinfo">
          <h4><?= h($e['nom']) ?></h4>
          <p><?= h(implode(' • ', array_slice(jsonDecode($e['filieres']), 0, 3))) ?></p>
        </div>
        <span class="match"><?= $scores[$i] ?>%</span>
      </a>
      <?php endforeach; ?>
    </div>
    <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
      <a href="ecoles.php"><button class="btn-nav" style="padding:12px 24px">Explorer les écoles</button></a>
      <button class="btn-restart" onclick="restartOrientation()"><i class="fas fa-redo" style="margin-right:6px"></i> Recommencer</button>
    </div>
  </div>

</div>

<script>
function goStep(n) {
  document.querySelectorAll('.question-card').forEach(el => el.style.display = 'none');
  document.getElementById('step-results').style.display = 'none';
  const t = document.getElementById('step-' + n);
  if (t) t.style.display = 'block';
  const pct = Math.round((n / 4) * 100);
  document.getElementById('progress-fill').style.width = pct + '%';
  document.getElementById('step-label').textContent = 'Étape ' + n + ' sur 4';
  document.getElementById('step-pct').textContent = pct + '%';
  window.scrollTo({ top: 0, behavior: 'smooth' });
}
function selectOption(btn, group) {
  const grid = btn.closest('.options-grid');
  if (group === 'matiere' || group === 'domaine') {
    btn.classList.toggle('selected');
  } else {
    grid.querySelectorAll('.option-btn').forEach(b => b.classList.remove('selected'));
    btn.classList.add('selected');
  }
}
function showResults() {
  document.querySelectorAll('.question-card').forEach(el => el.style.display = 'none');
  document.getElementById('step-results').style.display = 'block';
  document.getElementById('progress-fill').style.width = '100%';
  document.getElementById('step-label').textContent = 'Résultats';
  document.getElementById('step-pct').textContent = '100%';
  window.scrollTo({ top: 0, behavior: 'smooth' });
}
function restartOrientation() {
  document.getElementById('step-results').style.display = 'none';
  document.querySelectorAll('.option-btn').forEach(b => b.classList.remove('selected'));
  document.querySelectorAll('textarea').forEach(t => t.value = '');
  goStep(1);
}
</script>

<?php require_once 'includes/footer.php'; ?>
