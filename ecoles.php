<?php
require_once 'includes/functions.php';
$pageTitle = 'Écoles & Universités';
$activeNav = 'ecoles';

$search = trim($_GET['q'] ?? '');
$type   = trim($_GET['type'] ?? '');

$db = getDB();
$where = []; $params = [];
if ($search) { $where[] = "(nom LIKE ? OR ville LIKE ? OR filieres LIKE ?)"; $like = "%$search%"; $params = array_merge($params, [$like,$like,$like]); }
if ($type)   { $where[] = "type = ?"; $params[] = $type; }
$sql = "SELECT * FROM ecoles" . ($where ? " WHERE " . implode(" AND ", $where) : "") . " ORDER BY nom";
$stmt = $db->prepare($sql); $stmt->execute($params);
$ecoles = $stmt->fetchAll();

require_once 'includes/header.php';
?>

<section class="page-hero">
  <h1>Écoles & Universités</h1>
  <p>Découvrez les meilleures institutions éducatives du Sénégal, leurs filières et conditions d'admission.</p>
</section>

<!-- FILTRES -->
<div class="filter-bar">
  <form method="GET" action="ecoles.php">
    <div class="filter-bar-inner">
      <div class="search-wrap">
        <i class="fas fa-search"></i>
        <input type="text" name="q" value="<?= h($search) ?>" placeholder="Rechercher une école ou un domaine..."/>
      </div>
      <select name="type" class="select-filter">
        <option value="">Tous les types</option>
        <option value="public" <?= $type==='public'?'selected':'' ?>>Public</option>
        <option value="prive"  <?= $type==='prive' ?'selected':'' ?>>Privé</option>
      </select>
      <button type="submit" class="btn-filter-submit"><i class="fas fa-search"></i> Filtrer</button>
      <?php if ($search || $type): ?>
        <a href="ecoles.php" style="color:var(--muted);font-size:0.85rem;display:flex;align-items:center;gap:4px;"><i class="fas fa-times"></i> Réinitialiser</a>
      <?php endif; ?>
    </div>
  </form>
</div>

<!-- LISTE ÉCOLES -->
<div class="section-body">
  <div class="result-count"><?= count($ecoles) ?> école<?= count($ecoles) > 1 ? 's' : '' ?> trouvée<?= count($ecoles) > 1 ? 's' : '' ?></div>

  <?php if (empty($ecoles)): ?>
    <div class="empty-state">
      <i class="fas fa-university"></i>
      <p>Aucune école ne correspond à votre recherche.</p>
    </div>
  <?php else: ?>
  <div class="schools-grid">
    <?php foreach ($ecoles as $e):
      $filieres = jsonDecode($e['filieres']);
      $domaines = jsonDecode($e['domaines']);
    ?>
    <div class="school-card">
      <div class="school-card-header">
        <h3><?= h($e['nom']) ?></h3>
        <?= badgeType($e['type']) ?>
      </div>
      <div class="school-location">
        <i class="fas fa-map-marker-alt"></i> <?= h($e['ville']) ?>
      </div>
      <div class="school-tags">
        <?php foreach (array_slice($filieres, 0, 3) as $f): ?>
          <span class="tag"><?= h($f) ?></span>
        <?php endforeach; ?>
        <?php if (count($filieres) > 3): ?>
          <span class="tag" style="background:#f8f9fa;color:var(--muted);border-color:var(--border);">+<?= count($filieres)-3 ?></span>
        <?php endif; ?>
      </div>
      <p class="school-desc"><?= h(mb_strimwidth($e['presentation'] ?? '', 0, 140, '...')) ?></p>
      <a href="ecole.php?slug=<?= h($e['slug']) ?>">
        <button class="btn-details">Voir les détails <i class="fas fa-arrow-right" style="font-size:0.75rem;"></i></button>
      </a>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
