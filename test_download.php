<?php
// ─────────────────────────────────────────
//  test_download.php — Diagnostic téléchargement
//  Supprimez ce fichier après les tests !
// ─────────────────────────────────────────
require_once 'includes/functions.php';

$db       = getDB();
$epreuves = $db->query("
    SELECT ep.id, ep.matiere, ep.annee, ep.fichier, ep.lien_externe,
           c.nom AS concours_nom
    FROM epreuves ep
    JOIN concours c ON ep.concours_id = c.id
    ORDER BY ep.id
")->fetchAll();

$uploadDir = __DIR__ . '/uploads/epreuves/';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Diagnostic Téléchargement</title>
<style>
body{font-family:sans-serif;padding:2rem;background:#f5f8ff;max-width:900px;margin:0 auto}
h2{color:#1a2233}
table{width:100%;border-collapse:collapse;background:white;border-radius:10px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.08)}
th{background:#1a6ef5;color:white;padding:11px 14px;text-align:left;font-size:.82rem}
td{padding:10px 14px;border-bottom:1px solid #e2e8f0;font-size:.83rem;vertical-align:middle}
tr:last-child td{border-bottom:none}
tr:hover td{background:#f0f5ff}
.ok{color:#15803d;font-weight:600} .fail{color:#b91c1c;font-weight:600} .warn{color:#854d0e;font-weight:600}
.path{font-family:monospace;background:#f1f5f9;padding:2px 6px;border-radius:4px;font-size:.78rem}
.info{background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:14px 18px;margin-bottom:20px;font-size:.87rem}
.info code{background:#dbeafe;padding:2px 6px;border-radius:4px}
</style>
</head>
<body>
<h2>🔍 Diagnostic Téléchargement — EduGuide SN</h2>

<div class="info">
  📁 Dossier de dépôt des PDF : <code><?= htmlspecialchars($uploadDir) ?></code><br>
  <?php if (is_dir($uploadDir)): ?>
    ✅ Le dossier <strong>existe</strong> —
    <?php
    $files = glob($uploadDir . '*');
    $pdfFiles = array_filter($files, fn($f) => strtolower(pathinfo($f, PATHINFO_EXTENSION)) === 'pdf');
    echo count($pdfFiles) . ' fichier(s) PDF trouvé(s) : ';
    if ($pdfFiles) {
        echo '<strong>' . implode(', ', array_map('basename', $pdfFiles)) . '</strong>';
    } else {
        echo '<span style="color:#b91c1c">Aucun PDF dans ce dossier !</span>';
    }
    ?>
  <?php else: ?>
    <span style="color:#b91c1c">❌ Le dossier <strong>n'existe pas</strong> — créez-le manuellement.</span>
  <?php endif; ?>
</div>

<table>
  <thead>
    <tr>
      <th>#</th>
      <th>Matière / Concours</th>
      <th>Fichier renseigné</th>
      <th>Statut</th>
      <th>Action</th>
    </tr>
  </thead>
  <tbody>
  <?php foreach ($epreuves as $ep):
    $fichier      = trim($ep['fichier'] ?? '');
    $lienExterne  = trim($ep['lien_externe'] ?? '');
    $fullPath     = $uploadDir . basename($fichier);

    if (!empty($lienExterne)) {
        $statut = '<span class="ok">✅ Lien externe configuré</span>';
        $detail = '<a href="' . htmlspecialchars($lienExterne) . '" target="_blank">Ouvrir le lien</a>';
    } elseif (empty($fichier)) {
        $statut = '<span class="warn">⚠️ Aucun fichier renseigné</span>';
        $detail = 'Allez dans l\'admin pour ajouter le nom du fichier';
    } elseif (file_exists($fullPath)) {
        $size   = round(filesize($fullPath) / 1024);
        $statut = '<span class="ok">✅ Fichier trouvé (' . $size . ' Ko)</span>';
        $detail = '<a href="epreuves.php?download=' . $ep['id'] . '">Tester le téléchargement</a>';
    } else {
        $statut = '<span class="fail">❌ Fichier introuvable</span>';
        $detail = 'Fichier attendu : <span class="path">' . htmlspecialchars($fullPath) . '</span>';
    }
  ?>
  <tr>
    <td style="color:#6b7a99;font-size:.76rem">#<?= $ep['id'] ?></td>
    <td>
      <strong><?= htmlspecialchars($ep['matiere']) ?></strong><br>
      <span style="color:#6b7a99;font-size:.76rem"><?= htmlspecialchars($ep['concours_nom']) ?> — <?= $ep['annee'] ?></span>
    </td>
    <td>
      <?php if ($fichier): ?>
        <span class="path"><?= htmlspecialchars($fichier) ?></span>
      <?php else: ?>
        <span style="color:#b0b9cc">—</span>
      <?php endif; ?>
    </td>
    <td><?= $statut ?></td>
    <td style="font-size:.8rem"><?= $detail ?></td>
  </tr>
  <?php endforeach; ?>
  </tbody>
</table>

<div style="margin-top:24px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:16px 20px;font-size:.85rem">
  <strong>📋 Comment ajouter un fichier :</strong><br>
  1. Copiez votre PDF dans : <code><?= htmlspecialchars($uploadDir) ?></code><br>
  2. Dans <a href="admin_epreuves.php">admin_epreuves.php</a>, champ <strong>"Chemin local"</strong>, écrivez <strong>uniquement le nom du fichier</strong> : ex: <code>math-esp-2024.pdf</code><br>
  3. Cliquez <strong>Enregistrer</strong> — le bouton Télécharger s'activera automatiquement.
</div>

<p style="margin-top:20px;font-size:.78rem;color:#6b7a99">⚠️ Supprimez ce fichier (<code>test_download.php</code>) après les tests.</p>
</body>
</html>
