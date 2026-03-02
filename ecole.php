<?php
require_once 'includes/functions.php';

$slug = trim($_GET['slug'] ?? '');
if (!$slug) { header('Location: ecoles.php'); exit; }

$ecole = getEcoleBySlug($slug);
if (!$ecole) { header('Location: ecoles.php'); exit; }

$concours = getConcoursByEcole($ecole['id']);
$filieres = jsonDecode($ecole['filieres']);
$domaines = jsonDecode($ecole['domaines']);
$conditions= jsonDecode($ecole['conditions']);
$pieces   = jsonDecode($ecole['pieces']);

$pageTitle = $ecole['nom'];
$activeNav = 'ecoles';
require_once 'includes/header.php';
?>

<!-- HERO DÉTAIL -->
<div class="detail-hero">
  <div class="detail-hero-inner">
    <div class="detail-hero-icon"><i class="<?= h($ecole['icone']) ?>"></i></div>
    <div class="detail-hero-info">
      <h1><?= h($ecole['nom']) ?> <?= badgeType($ecole['type']) ?></h1>
      <div class="detail-location"><i class="fas fa-map-marker-alt"></i> <?= h($ecole['ville']) ?></div>
    </div>
  </div>
</div>

<div class="detail-body">
  <!-- COLONNE GAUCHE -->
  <div class="left-col">
    <div class="info-card">
      <div class="info-card-title"><i class="fas fa-graduation-cap"></i> Présentation</div>
      <p><?= h($ecole['presentation']) ?></p>
    </div>

    <div class="info-card">
      <div class="info-card-title" style="margin-bottom:14px;">Filières proposées</div>
      <div class="filiere-tags">
        <?php foreach ($filieres as $f): ?>
          <span class="filiere-tag"><?= h($f) ?></span>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="info-card">
      <div class="info-card-title" style="margin-bottom:14px;">Domaines</div>
      <div style="display:flex;flex-wrap:wrap;gap:8px;">
        <?php foreach ($domaines as $d): ?>
          <span class="domaine-tag <?= h($d['classe'] ?? '') ?>"><?= h($d['label']) ?></span>
        <?php endforeach; ?>
      </div>
    </div>

    <?php if ($ecole['email'] || $ecole['telephone']): ?>
    <div class="info-card">
      <div class="info-card-title"><i class="fas fa-address-book"></i> Contact</div>
      <?php if ($ecole['email']): ?>
        <p><i class="fas fa-envelope" style="color:var(--primary);margin-right:8px;"></i><?= h($ecole['email']) ?></p>
      <?php endif; ?>
      <?php if ($ecole['telephone']): ?>
        <p style="margin-top:8px;"><i class="fas fa-phone" style="color:var(--primary);margin-right:8px;"></i><?= h($ecole['telephone']) ?></p>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </div>

  <!-- SIDEBAR DROITE -->
  <div class="right-col">
    <div class="sidebar-card">
      <div class="sidebar-card-title"><i class="fas fa-file-alt"></i> Conditions d'accès</div>
      <ul class="conditions-list">
        <?php foreach ($conditions as $i => $c): ?>
          <li><div class="cond-num"><?= $i+1 ?></div><span><?= h($c) ?></span></li>
        <?php endforeach; ?>
      </ul>
    </div>

    <div class="sidebar-card">
      <div class="sidebar-card-title"><i class="fas fa-paperclip"></i> Pièces à fournir</div>
      <ul class="pieces-list">
        <?php foreach ($pieces as $p): ?>
          <li><?= h($p) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>

    <div class="sidebar-card">
      <div class="sidebar-card-title">Type d'admission</div>
      <span class="badge-admission"><?= h($ecole['type_admission']) ?></span>
    </div>

    <?php if (!empty($concours)): ?>
    <div class="sidebar-card">
      <div class="sidebar-card-title"><i class="fas fa-link"></i> Concours liés</div>
      <?php foreach ($concours as $c): ?>
        <a href="concours.php?ecole=<?= $ecole['id'] ?>" class="concours-link-item">
          <?= h($c['nom']) ?> <i class="fas fa-arrow-right" style="font-size:0.75rem;color:var(--muted);"></i>
        </a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>
