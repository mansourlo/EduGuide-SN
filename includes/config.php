<?php
// =============================================
//  EduGuide SN — Configuration générale
// =============================================

// ── Base de données ──
define('DB_HOST',    'localhost');
define('DB_NAME',    'eduguide_sn');
define('DB_USER',    'root');   // ← votre utilisateur MySQL
define('DB_PASS',    '');       // ← votre mot de passe MySQL
define('DB_CHARSET', 'utf8mb4');

define('SITE_NAME', 'EduGuide SN');
define('SITE_URL',  'http://localhost/eduguide-sn');

// ── Clé API Google Gemini (GRATUIT — 1500 req/jour) ──
// Obtenez votre clé GRATUITE sur : https://aistudio.google.com/app/apikey
define('GEMINI_API_KEY', 'VOTRE_CLE_GEMINI_ICI');  // ← Remplacez ici

// ── Mot de passe admin ──
define('ADMIN_PASS', 'admin123');  // ← Changez en production

// ── Connexion PDO ──
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            die('<div style="font-family:sans-serif;padding:2rem;background:#fff1f2;border:1px solid #fecaca;border-radius:8px;color:#dc2626;max-width:600px;margin:2rem auto;">
                <strong>Erreur de connexion à la base de données</strong><br>
                Vérifiez vos paramètres dans <code>includes/config.php</code><br><br>
                <small>' . htmlspecialchars($e->getMessage()) . '</small>
            </div>');
        }
    }
    return $pdo;
}
