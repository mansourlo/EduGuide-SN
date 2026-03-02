<?php // includes/footer.php ?>
<footer>
  <div class="footer-top">
    <div class="footer-brand">
      <div class="footer-logo">
        <div class="logo-icon"><i class="fas fa-graduation-cap"></i></div>
        EduGuide <span class="sn">SN</span>
      </div>
      <p>Le guide scolaire et universitaire du Sénégal. Trouvez votre voie vers la réussite académique.</p>
      <div class="social-links">
        <a href="#"><i class="fab fa-facebook-f"></i></a>
        <a href="#"><i class="fab fa-twitter"></i></a>
        <a href="#"><i class="fab fa-linkedin-in"></i></a>
        <a href="#"><i class="fab fa-instagram"></i></a>
      </div>
    </div>
    <div class="footer-col">
      <h4>Navigation</h4>
      <ul>
        <li><a href="<?= $rootPath ?? '' ?>index.php">Accueil</a></li>
        <li><a href="<?= $rootPath ?? '' ?>ecoles.php">Écoles & Universités</a></li>
        <li><a href="<?= $rootPath ?? '' ?>concours.php">Concours</a></li>
        <li><a href="<?= $rootPath ?? '' ?>epreuves.php">Anciennes Épreuves</a></li>
        <li><a href="<?= $rootPath ?? '' ?>orientation.php">Orientation</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h4>Ressources</h4>
      <ul>
        <li><a href="#">Guide d'inscription</a></li>
        <li><a href="#">Conseils de préparation</a></li>
        <li><a href="#">FAQ</a></li>
        <li><a href="#">Blog</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h4>Contact</h4>
      <ul class="footer-contact">
        <li><i class="fas fa-map-marker-alt"></i> Dakar, Sénégal</li>
        <li><i class="fas fa-phone"></i> +221 XX XXX XX XX</li>
        <li><i class="fas fa-envelope"></i> contact@eduguidesn.com</li>
      </ul>
    </div>
  </div>
  <div class="footer-bottom">
    <span>© <?= date('Y') ?> EduGuide SN. Tous droits réservés.</span>
    <div style="display:flex;gap:20px;">
      <a href="#">Mentions légales</a>
      <a href="#">Politique de confidentialité</a>
    </div>
  </div>
</footer>
</body>
</html>
