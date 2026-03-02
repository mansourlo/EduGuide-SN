<?php
require_once 'includes/functions.php';
$pageTitle = 'Concours';
$activeNav = 'concours';

$search  = trim($_GET['q']      ?? '');
$annee   = trim($_GET['annee']  ?? '');
$statut  = trim($_GET['statut'] ?? '');
$ecoleId = trim($_GET['ecole']  ?? '');

$db = getDB();
$where = []; $params = [];
if ($search)  { $where[] = "(c.nom LIKE ? OR e.nom LIKE ? OR c.matieres LIKE ?)"; $like="%$search%"; $params=array_merge($params,[$like,$like,$like]); }
if ($annee)   { $where[] = "c.annee = ?";   $params[] = $annee; }
if ($statut)  { $where[] = "c.statut = ?";  $params[] = $statut; }
if ($ecoleId) { $where[] = "c.ecole_id = ?"; $params[] = $ecoleId; }

$sql = "SELECT c.*, e.nom AS ecole_nom, e.slug AS ecole_slug FROM concours c JOIN ecoles e ON c.ecole_id = e.id"
     . ($where ? " WHERE ".implode(" AND ",$where) : "")
     . " ORDER BY c.annee DESC, c.date_limite ASC";
$stmt = $db->prepare($sql); $stmt->execute($params);
$concoursList = $stmt->fetchAll();

// Pour les filtres
$annees = $db->query("SELECT DISTINCT annee FROM concours ORDER BY annee DESC")->fetchAll(PDO::FETCH_COLUMN);
$ecoles = $db->query("SELECT id, nom FROM ecoles ORDER BY nom")->fetchAll();

require_once 'includes/header.php';
?>

<section class="page-hero">
  <h1>Concours</h1>
  <p>Consultez tous les concours disponibles, leurs conditions d'éligibilité et les matières évaluées.</p>
</section>

<!-- FILTRES -->
<div class="filter-bar">
  <form method="GET" action="concours.php">
    <div class="filter-bar-inner">
      <div class="search-wrap">
        <i class="fas fa-search"></i>
        <input type="text" name="q" value="<?= h($search) ?>" placeholder="Rechercher un concours, une école ou une matière..."/>
      </div>
      <select name="annee" class="select-filter">
        <option value="">Toutes les années</option>
        <?php foreach ($annees as $a): ?>
          <option value="<?= h($a) ?>" <?= $annee==$a?'selected':'' ?>><?= h($a) ?></option>
        <?php endforeach; ?>
      </select>
      <select name="statut" class="select-filter">
        <option value="">Tous les statuts</option>
        <option value="ouvert"  <?= $statut==='ouvert' ?'selected':'' ?>>Ouvert</option>
        <option value="ferme"   <?= $statut==='ferme'  ?'selected':'' ?>>Fermé</option>
        <option value="a_venir" <?= $statut==='a_venir'?'selected':'' ?>>À venir</option>
      </select>
      <select name="ecole" class="select-filter">
        <option value="">Toutes les écoles</option>
        <?php foreach ($ecoles as $e): ?>
          <option value="<?= $e['id'] ?>" <?= $ecoleId==$e['id']?'selected':'' ?>><?= h($e['nom']) ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn-filter-submit"><i class="fas fa-search"></i> Filtrer</button>
      <?php if ($search||$annee||$statut||$ecoleId): ?>
        <a href="concours.php" style="color:var(--muted);font-size:0.85rem;display:flex;align-items:center;gap:4px;"><i class="fas fa-times"></i> Réinitialiser</a>
      <?php endif; ?>
    </div>
  </form>
</div>

<!-- LISTE CONCOURS -->
<div class="section-body">
  <div class="result-count"><?= count($concoursList) ?> concours trouvé<?= count($concoursList)>1?'s':'' ?></div>

  <?php if (empty($concoursList)): ?>
    <div class="empty-state"><i class="fas fa-file-alt"></i><p>Aucun concours ne correspond à votre recherche.</p></div>
  <?php else: ?>
  <div class="concours-grid">
    <?php foreach ($concoursList as $c):
      $matieres = jsonDecode($c['matieres']);
    ?>
    <div class="concours-card">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px;">
        <h3><?= h($c['nom']) ?></h3>
        <?= statutBadge($c['statut']) ?>
      </div>
      <div class="concours-school">
        <i class="fas fa-graduation-cap"></i>
        <a href="ecole.php?slug=<?= h($c['ecole_slug']) ?>" style="color:var(--muted);"><?= h($c['ecole_nom']) ?></a>
      </div>
      <?php if ($c['date_limite']): ?>
      <div class="concours-date">
        <i class="fas fa-calendar"></i>
        Date limite : <?= date('d/m/Y', strtotime($c['date_limite'])) ?>
      </div>
      <?php endif; ?>
      <?php if ($c['niveau_requis']): ?>
      <div>
        <span class="concours-label">Niveau requis :</span>
        <span class="niveau-badge"><?= h($c['niveau_requis']) ?></span>
      </div>
      <?php endif; ?>
      <?php if (!empty($matieres)): ?>
      <div>
        <span class="concours-label">Matières :</span>
        <div class="matiere-tags">
          <?php foreach ($matieres as $m): ?>
            <span class="mtag"><?= h($m) ?></span>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
      <a href="epreuves.php?concours_id=<?= $c['id'] ?>">
        <button class="btn-details" style="margin-top:4px;">Voir les épreuves <i class="fas fa-arrow-right" style="font-size:0.75rem;"></i></button>
      </a>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
