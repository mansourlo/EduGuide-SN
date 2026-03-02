<?php
require_once 'includes/functions.php';
$pageTitle = 'Accueil';
$activeNav = 'accueil';

$nbEcoles   = countTable('ecoles');
$nbConcours = countTable('concours');
$nbEpreuves = countTable('epreuves');

require_once 'includes/header.php';
?>

<!-- HERO -->
<section class="hero">
  <div class="hero-container">
    <div class="hero-left">
      <div class="hero-badge"><div class="dot"></div> Plateforme éducative #1 au Sénégal</div>
      <h1>EduGuide SN<br>Le guide scolaire<br>et concours du<br>Sénégal</h1>
      <p>Découvrez les meilleures écoles, préparez vos concours avec les anciennes épreuves, et trouvez votre orientation idéale grâce à notre outil personnalisé.</p>
      <div class="hero-btns">
        <a href="ecoles.php"><button class="btn-outline-white">Explorer les opportunités &nbsp;→</button></a>
        <a href="orientation.php"><button class="btn-white">Faire le test d'orientation</button></a>
      </div>
      <div class="hero-stats">
        <div class="stat-item">
          <div class="num"><?= $nbEcoles ?>+</div>
          <div class="label">Écoles référencées</div>
        </div>
        <div class="stat-item">
          <div class="num"><?= $nbConcours ?>+</div>
          <div class="label">Concours disponibles</div>
        </div>
        <div class="stat-item">
          <div class="num"><?= $nbEpreuves ?>+</div>
          <div class="label">Épreuves téléchargeables</div>
        </div>
      </div>
    </div>
    <div class="hero-right">
      <div class="hero-circle"><div class="hero-circle-inner"><i class="fas fa-graduation-cap"></i></div></div>
      <div class="floating-icon fi-1"><i class="fas fa-book-open"></i></div>
      <div class="floating-icon fi-2"><i class="fas fa-star"></i></div>
      <div class="floating-icon fi-3"><i class="fas fa-compass"></i></div>
    </div>
  </div>
</section>

<!-- FEATURES -->
<section class="features">
  <h2 class="section-title">Tout ce dont vous avez besoin pour<br><span class="highlight">réussir</span></h2>
  <p class="section-subtitle">EduGuide SN centralise toutes les ressources essentielles pour guider votre parcours éducatif au Sénégal.</p>
  <div class="cards-grid">
    <a href="ecoles.php" style="text-decoration:none;color:inherit;">
      <div class="card">
        <div class="card-icon icon-blue"><i class="fas fa-university"></i></div>
        <h3>Écoles & Universités</h3>
        <p>Explorez le catalogue complet des établissements du Sénégal avec leurs filières et conditions d'admission.</p>
        <span class="card-link">En savoir plus →</span>
      </div>
    </a>
    <a href="concours.php" style="text-decoration:none;color:inherit;">
      <div class="card">
        <div class="card-icon icon-green"><i class="fas fa-file-alt"></i></div>
        <h3>Concours</h3>
        <p>Consultez les informations sur tous les concours : conditions, matières évaluées et procédures d'inscription.</p>
        <span class="card-link">En savoir plus →</span>
      </div>
    </a>
    <a href="epreuves.php" style="text-decoration:none;color:inherit;">
      <div class="card">
        <div class="card-icon icon-orange"><i class="fas fa-download"></i></div>
        <h3>Anciennes Épreuves</h3>
        <p>Téléchargez gratuitement les sujets des années précédentes pour mieux vous préparer aux concours.</p>
        <span class="card-link">En savoir plus →</span>
      </div>
    </a>
    <a href="orientation.php" style="text-decoration:none;color:inherit;">
      <div class="card">
        <div class="card-icon icon-indigo"><i class="fas fa-compass"></i></div>
        <h3>Orientation Personnalisée</h3>
        <p>Répondez à quelques questions et recevez des recommandations adaptées à votre profil et vos ambitions.</p>
        <span class="card-link">En savoir plus →</span>
      </div>
    </a>
  </div>
</section>

<!-- CTA -->
<section class="cta-section">
  <div class="cta-box">
    <div class="cta-badge"><i class="fas fa-compass"></i> Outil d'orientation intelligent</div>
    <h2>Trouvez votre voie idéale en quelques clics</h2>
    <p>Notre outil d'orientation analyse votre profil, vos intérêts et vos compétences pour vous recommander les meilleures écoles.</p>
    <a href="orientation.php"><button class="btn-cta">Commencer le test d'orientation &nbsp;→</button></a>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>
