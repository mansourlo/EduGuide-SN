<?php
$activeNav = $activeNav ?? 'accueil';
$pageTitle = isset($pageTitle) ? h($pageTitle) . ' — EduGuide SN' : 'EduGuide SN';
$root      = $rootPath ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= $pageTitle ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <link rel="stylesheet" href="<?= $root ?>assets/css/style.css"/>

  <!-- Favicon -->
  <link rel="icon" type="image/png" href="./assets/images/favicon.png">
</head>
<body>

<nav id="main-nav">
  <!-- Logo -->
  <a href="<?= $root ?>index.php" class="nav-logo">
    <div class="logo-icon"><i class="fas fa-graduation-cap"></i></div>
    <span>EduGuide <span class="sn">SN</span></span>
  </a>

  <!-- Liens desktop -->
  <ul class="nav-links" id="nav-links">
    <li><a href="<?= $root ?>index.php"       class="<?= $activeNav==='accueil'     ? 'active' : '' ?>">Accueil</a></li>
    <li><a href="<?= $root ?>ecoles.php"      class="<?= $activeNav==='ecoles'      ? 'active' : '' ?>">Écoles</a></li>
    <li><a href="<?= $root ?>concours.php"    class="<?= $activeNav==='concours'    ? 'active' : '' ?>">Concours</a></li>
    <li><a href="<?= $root ?>epreuves.php"    class="<?= $activeNav==='epreuves'    ? 'active' : '' ?>">Épreuves</a></li>
    <li><a href="<?= $root ?>orientation.php" class="<?= $activeNav==='orientation' ? 'active' : '' ?>">Orientation</a></li>
  </ul>

  <!-- Bouton CTA desktop -->
  <a href="<?= $root ?>orientation.php" class="nav-cta-wrap">
    <button class="btn-nav">Commencer l'orientation</button>
  </a>

  <!-- Bouton hamburger -->
  <button class="hamburger" id="hamburger" onclick="toggleMenu()" aria-label="Menu">
    <span></span>
    <span></span>
    <span></span>
  </button>
</nav>

<!-- Menu mobile (overlay) -->
<div class="mobile-menu" id="mobile-menu">
  <div class="mobile-menu-header">
    <a href="<?= $root ?>index.php" class="nav-logo" onclick="closeMenu()">
      <div class="logo-icon"><i class="fas fa-graduation-cap"></i></div>
      <span>EduGuide <span class="sn">SN</span></span>
    </a>
    <button class="mobile-close" onclick="closeMenu()"><i class="fas fa-times"></i></button>
  </div>
  <ul class="mobile-links">
    <li><a href="<?= $root ?>index.php"       class="<?= $activeNav==='accueil'     ? 'active' : '' ?>" onclick="closeMenu()"><i class="fas fa-home"></i> Accueil</a></li>
    <li><a href="<?= $root ?>ecoles.php"      class="<?= $activeNav==='ecoles'      ? 'active' : '' ?>" onclick="closeMenu()"><i class="fas fa-university"></i> Écoles</a></li>
    <li><a href="<?= $root ?>concours.php"    class="<?= $activeNav==='concours'    ? 'active' : '' ?>" onclick="closeMenu()"><i class="fas fa-file-alt"></i> Concours</a></li>
    <li><a href="<?= $root ?>epreuves.php"    class="<?= $activeNav==='epreuves'    ? 'active' : '' ?>" onclick="closeMenu()"><i class="fas fa-download"></i> Épreuves</a></li>
    <li><a href="<?= $root ?>orientation.php" class="<?= $activeNav==='orientation' ? 'active' : '' ?>" onclick="closeMenu()"><i class="fas fa-compass"></i> Orientation</a></li>
  </ul>
  <div class="mobile-cta">
    <a href="<?= $root ?>orientation.php" onclick="closeMenu()">
      <button class="btn-nav" style="width:100%;padding:13px;font-size:.95rem">
        <i class="fas fa-compass" style="margin-right:6px"></i> Commencer l'orientation
      </button>
    </a>
  </div>
</div>

<!-- Overlay sombre derrière le menu -->
<div class="menu-overlay" id="menu-overlay" onclick="closeMenu()"></div>

<script>
function toggleMenu() {
  const open = document.getElementById('mobile-menu').classList.toggle('open');
  document.getElementById('menu-overlay').classList.toggle('open', open);
  document.getElementById('hamburger').classList.toggle('open', open);
  document.body.style.overflow = open ? 'hidden' : '';
}
function closeMenu() {
  document.getElementById('mobile-menu').classList.remove('open');
  document.getElementById('menu-overlay').classList.remove('open');
  document.getElementById('hamburger').classList.remove('open');
  document.body.style.overflow = '';
}
// Fermer avec Escape
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeMenu(); });
</script>
