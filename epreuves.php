<?php
require_once 'includes/functions.php';
$pageTitle = 'Anciennes Épreuves';
$activeNav = 'epreuves';

// ── TÉLÉCHARGEMENT ──
if (isset($_GET['download']) && is_numeric($_GET['download'])) {
    $db  = getDB();
    $id  = (int)$_GET['download'];

    // Récupérer avec gestion douce si lien_externe n'existe pas encore
    try {
        $stmt = $db->prepare("SELECT matiere, annee, fichier, lien_externe FROM epreuves WHERE id = ?");
        $stmt->execute([$id]);
    } catch (\PDOException $e) {
        $stmt = $db->prepare("SELECT matiere, annee, fichier, '' AS lien_externe FROM epreuves WHERE id = ?");
        $stmt->execute([$id]);
    }
    $ep = $stmt->fetch();

    if ($ep) {
        $db->prepare("UPDATE epreuves SET nb_telechargements = nb_telechargements + 1 WHERE id = ?")->execute([$id]);

        if (!empty($ep['lien_externe'])) {
            header('Location: ' . $ep['lien_externe']); exit;
        }
        if (!empty($ep['fichier'])) {
            $path = __DIR__ . '/uploads/epreuves/' . basename($ep['fichier']);
            if (file_exists($path)) {
                $nom = preg_replace('/[^A-Za-z0-9\-_.]/', '-', $ep['matiere'] . '-' . $ep['annee'] . '.pdf');
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="' . $nom . '"');
                header('Content-Length: ' . filesize($path));
                readfile($path); exit;
            }
        }
    }
    header('Location: epreuves.php?msg=nodoc'); exit;
}

// ── FILTRES ──
$search     = trim($_GET['q']            ?? '');
$annee      = trim($_GET['annee']        ?? '');
$matiere    = trim($_GET['matiere']      ?? '');
$concoursId = trim($_GET['concours_id']  ?? '');

$epreuves = getAllEpreuves($search, $annee, $matiere, $concoursId);

$db           = getDB();
$annees       = $db->query("SELECT DISTINCT annee FROM epreuves ORDER BY annee DESC")->fetchAll(PDO::FETCH_COLUMN);
$matieresList = $db->query("SELECT DISTINCT matiere FROM epreuves ORDER BY matiere")->fetchAll(PDO::FETCH_COLUMN);
$concoursList = $db->query("SELECT id, nom FROM concours ORDER BY nom")->fetchAll();

require_once 'includes/header.php';
?>

<section class="page-hero" style="background:linear-gradient(135deg,#f97316 0%,#fb923c 100%)">
  <h1>Anciennes Épreuves</h1>
  <p>Téléchargez gratuitement les sujets des années précédentes pour mieux préparer vos concours.</p>
</section>

<?php if (($_GET['msg'] ?? '') === 'nodoc'): ?>
<div style="max-width:820px;margin:18px auto;padding:0 2rem">
  <div style="background:#fff7ed;border:1px solid #fed7aa;border-radius:10px;padding:13px 18px;color:#c2410c;font-size:.87rem;display:flex;align-items:center;gap:10px">
    <i class="fas fa-exclamation-triangle"></i>
    Ce fichier n'est pas encore disponible. Revenez bientôt !
  </div>
</div>
<?php endif; ?>

<!-- FILTRES -->
<div class="filter-bar">
  <form method="GET" action="epreuves.php">
    <div class="filter-bar-inner">
      <div class="search-wrap">
        <i class="fas fa-search"></i>
        <input type="text" name="q" value="<?= h($search) ?>" placeholder="Rechercher une épreuve..."/>
      </div>
      <select name="annee" class="select-filter">
        <option value="">Toutes les années</option>
        <?php foreach ($annees as $a): ?>
          <option value="<?= h($a) ?>" <?= $annee == $a ? 'selected' : '' ?>><?= h($a) ?></option>
        <?php endforeach; ?>
      </select>
      <select name="matiere" class="select-filter">
        <option value="">Toutes les matières</option>
        <?php foreach ($matieresList as $m): ?>
          <option value="<?= h($m) ?>" <?= $matiere === $m ? 'selected' : '' ?>><?= h($m) ?></option>
        <?php endforeach; ?>
      </select>
      <select name="concours_id" class="select-filter">
        <option value="">Tous les concours</option>
        <?php foreach ($concoursList as $c): ?>
          <option value="<?= (int)$c['id'] ?>" <?= $concoursId == $c['id'] ? 'selected' : '' ?>><?= h($c['nom']) ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn-filter-submit"><i class="fas fa-search"></i> Filtrer</button>
      <?php if ($search || $annee || $matiere || $concoursId): ?>
        <a href="epreuves.php" style="color:var(--muted);font-size:.85rem;display:flex;align-items:center;gap:4px;white-space:nowrap">
          <i class="fas fa-times"></i> Réinitialiser
        </a>
      <?php endif; ?>
    </div>
  </form>
</div>

<!-- LISTE -->
<div class="epreuves-section">
  <div class="result-count">
    <?= count($epreuves) ?> épreuve<?= count($epreuves) > 1 ? 's' : '' ?> trouvée<?= count($epreuves) > 1 ? 's' : '' ?>
  </div>

  <?php if (empty($epreuves)): ?>
    <div class="empty-state">
      <i class="fas fa-file-alt"></i>
      <p>Aucune épreuve trouvée.</p>
    </div>
  <?php else: ?>
  <div class="epreuve-list">
    <?php foreach ($epreuves as $ep):
      $hasLink = !empty($ep['lien_externe'] ?? '');
      $hasFile = !empty($ep['fichier'] ?? '');
      $dispo   = $hasLink || $hasFile;
    ?>
    <div class="epreuve-row">
      <div class="epreuve-icon"><i class="fas fa-file-alt"></i></div>
      <div class="epreuve-info">
        <h4><?= h($ep['matiere']) ?> &mdash; <?= h($ep['concours_nom']) ?></h4>
        <div class="epreuve-meta">
          <span class="epreuve-year"><?= h($ep['annee']) ?></span>
          <span class="epreuve-tag <?= h($ep['tag_classe']) ?>"><?= h($ep['matiere']) ?></span>
          <a href="ecole.php?slug=<?= h($ep['ecole_slug']) ?>" class="epreuve-concours" style="color:var(--muted)">
            <?= h($ep['ecole_nom']) ?>
          </a>
          <?php if (($ep['nb_telechargements'] ?? 0) > 0): ?>
            <span style="font-size:.75rem;color:var(--muted)">
              <i class="fas fa-download" style="font-size:.7rem"></i> <?= (int)$ep['nb_telechargements'] ?>
            </span>
          <?php endif; ?>
          <?php if (!$dispo): ?>
            <span style="font-size:.72rem;font-weight:600;background:#fef9c3;color:#854d0e;padding:2px 8px;border-radius:20px">
              Bientôt disponible
            </span>
          <?php endif; ?>
        </div>
      </div>

      <?php if ($dispo): ?>
        <a href="epreuves.php?download=<?= (int)$ep['id'] ?>" <?= $hasLink ? 'target="_blank" rel="noopener"' : '' ?>>
          <button class="btn-dl">
            <i class="fas <?= $hasLink ? 'fa-link' : 'fa-download' ?>"></i> Télécharger
          </button>
        </a>
      <?php else: ?>
        <button class="btn-dl" disabled style="background:#e2e8f0;color:var(--muted);cursor:not-allowed">
          <i class="fas fa-clock"></i> Bientôt
        </button>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <div style="text-align:right;margin-top:24px">
    <a href="admin_epreuves.php"
       style="font-size:.82rem;color:var(--muted);display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border:1px solid var(--border);border-radius:8px;background:white;transition:all .2s"
       onmouseover="this.style.borderColor='var(--primary)';this.style.color='var(--primary)'"
       onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--muted)'">
      <i class="fas fa-cog"></i> Gérer les fichiers (Admin)
    </a>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>
