<?php
require_once 'includes/functions.php';

// ── Sécurité basique : mot de passe admin ──
// Changer ce mot de passe dans config.php : define('ADMIN_PASS', 'votre_mot_de_passe');
$adminPass = defined('ADMIN_PASS') ? ADMIN_PASS : 'admin123';

session_start();
$error = '';

if ($_POST['action'] ?? '' === 'login') {
    if ($_POST['password'] === $adminPass) {
        $_SESSION['admin'] = true;
    } else {
        $error = 'Mot de passe incorrect.';
    }
}
if ($_GET['action'] ?? '' === 'logout') {
    session_destroy();
    header('Location: admin_epreuves.php');
    exit;
}

// ── Enregistrer le chemin / lien d'une épreuve ──
if (isset($_SESSION['admin']) && ($_POST['action'] ?? '') === 'save_lien') {
    $id            = (int)$_POST['id'];
    $fichier       = trim($_POST['fichier'] ?? '');
    $lien_externe  = trim($_POST['lien_externe'] ?? '');

    $db = getDB();
    $stmt = $db->prepare("UPDATE epreuves SET fichier = ?, lien_externe = ? WHERE id = ?");
    $stmt->execute([$fichier ?: null, $lien_externe ?: null, $id]);
    header('Location: admin_epreuves.php?msg=saved#ep-' . $id);
    exit;
}

// ── Ajouter une nouvelle épreuve ──
if (isset($_SESSION['admin']) && ($_POST['action'] ?? '') === 'add_epreuve') {
    $db = getDB();
    $stmt = $db->prepare("
        INSERT INTO epreuves (concours_id, matiere, annee, fichier, lien_externe, tag_classe)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        (int)$_POST['concours_id'],
        trim($_POST['matiere']),
        (int)$_POST['annee'],
        trim($_POST['fichier']) ?: null,
        trim($_POST['lien_externe']) ?: null,
        trim($_POST['tag_classe']) ?: 'tag-math',
    ]);
    header('Location: admin_epreuves.php?msg=added');
    exit;
}

// ── Supprimer une épreuve ──
if (isset($_SESSION['admin']) && ($_GET['action'] ?? '') === 'delete' && isset($_GET['id'])) {
    $db = getDB();
    $db->prepare("DELETE FROM epreuves WHERE id = ?")->execute([(int)$_GET['id']]);
    header('Location: admin_epreuves.php?msg=deleted');
    exit;
}

// ── Récupérer les données ──
$db = getDB();
$epreuves = $db->query("
    SELECT ep.*, c.nom AS concours_nom, e.nom AS ecole_nom
    FROM epreuves ep
    JOIN concours c ON ep.concours_id = c.id
    JOIN ecoles   e ON c.ecole_id     = e.id
    ORDER BY ep.annee DESC, ep.matiere ASC
")->fetchAll();

$concoursList = $db->query("
    SELECT c.id, CONCAT(c.nom, ' (', e.nom, ')') AS label
    FROM concours c JOIN ecoles e ON c.ecole_id = e.id
    ORDER BY c.nom
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Admin Épreuves — EduGuide SN</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    :root{--primary:#1a6ef5;--primary-dark:#1055cc;--secondary:#22c55e;--accent:#f97316;--dark:#1a2233;--text:#1e2d45;--muted:#6b7a99;--bg:#f5f8ff;--border:#e2e8f0}
    body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text)}
    h1,h2,h3{font-family:'Poppins',sans-serif}
    a{text-decoration:none;color:inherit}

    /* TOPBAR */
    .topbar{background:#fff;border-bottom:1px solid var(--border);padding:0 2rem;height:60px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100;box-shadow:0 1px 8px rgba(26,110,245,0.06)}
    .topbar-logo{display:flex;align-items:center;gap:8px;font-family:'Poppins',sans-serif;font-weight:700;font-size:1.1rem}
    .topbar-logo .logo-icon{width:32px;height:32px;background:var(--primary);border-radius:8px;display:flex;align-items:center;justify-content:center;color:white;font-size:0.9rem}
    .topbar-logo .sn{color:var(--primary)}
    .topbar-actions{display:flex;align-items:center;gap:12px}
    .btn-sm{padding:7px 14px;border-radius:7px;font-size:0.82rem;font-weight:600;font-family:'Inter',sans-serif;cursor:pointer;border:none;transition:all 0.2s}
    .btn-primary{background:var(--primary);color:white}.btn-primary:hover{background:var(--primary-dark)}
    .btn-danger{background:#fee2e2;color:#b91c1c;border:1px solid #fecaca}.btn-danger:hover{background:#fecaca}
    .btn-success{background:#dcfce7;color:#15803d;border:1px solid #bbf7d0}.btn-success:hover{background:#bbf7d0}
    .btn-outline{background:white;color:var(--muted);border:1px solid var(--border)}.btn-outline:hover{border-color:var(--primary);color:var(--primary)}

    /* LOGIN */
    .login-wrap{min-height:100vh;display:flex;align-items:center;justify-content:center}
    .login-card{background:white;border:1px solid var(--border);border-radius:16px;padding:40px;width:100%;max-width:380px;box-shadow:0 4px 24px rgba(26,110,245,0.08)}
    .login-card h2{margin-bottom:6px;font-size:1.4rem}
    .login-card p{color:var(--muted);font-size:0.87rem;margin-bottom:24px}
    .form-group{margin-bottom:16px}
    .form-group label{display:block;font-size:0.82rem;font-weight:600;color:var(--dark);margin-bottom:6px}
    .form-control{width:100%;padding:11px 14px;border:1px solid var(--border);border-radius:9px;font-size:0.875rem;font-family:'Inter',sans-serif;outline:none;transition:border 0.2s}
    .form-control:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(26,110,245,0.1)}
    .form-control::placeholder{color:#b0b9cc}
    .btn-block{width:100%;padding:12px;font-size:0.9rem}

    /* MAIN */
    .main{max-width:1100px;margin:0 auto;padding:32px 2rem 60px}
    .page-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;flex-wrap:wrap;gap:12px}
    .page-header h1{font-size:1.5rem}
    .page-header p{color:var(--muted);font-size:0.87rem;margin-top:4px}

    /* ALERTS */
    .alert{padding:12px 16px;border-radius:10px;font-size:0.87rem;margin-bottom:20px;display:flex;align-items:center;gap:10px}
    .alert-success{background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d}
    .alert-error{background:#fff1f2;border:1px solid #fecaca;color:#b91c1c}
    .alert-info{background:#eff6ff;border:1px solid #bfdbfe;color:#1d4ed8}

    /* ADD FORM */
    .add-card{background:white;border:1px solid var(--border);border-radius:14px;padding:24px;margin-bottom:28px;box-shadow:0 2px 8px rgba(26,110,245,0.04)}
    .add-card h3{font-size:1rem;font-weight:700;color:var(--dark);margin-bottom:18px;display:flex;align-items:center;gap:8px}
    .add-card h3 i{color:var(--primary)}
    .form-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px}
    .form-grid-2{display:grid;grid-template-columns:1fr 1fr;gap:14px}
    .form-grid-full{grid-column:1/-1}
    select.form-control{appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236b7a99' d='M6 8L1 3h10z'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 12px center;padding-right:36px}

    /* TABLE */
    .table-card{background:white;border:1px solid var(--border);border-radius:14px;overflow:hidden;box-shadow:0 2px 8px rgba(26,110,245,0.04)}
    .table-header{padding:18px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
    .table-header h3{font-size:1rem;font-weight:700;color:var(--dark)}
    .table-header .count{background:#eff6ff;color:var(--primary);padding:3px 10px;border-radius:20px;font-size:0.75rem;font-weight:600}
    table{width:100%;border-collapse:collapse}
    thead th{background:#f8faff;padding:12px 16px;text-align:left;font-size:0.78rem;font-weight:600;color:var(--muted);border-bottom:1px solid var(--border);white-space:nowrap}
    tbody tr{border-bottom:1px solid #f1f5ff;transition:background 0.15s}
    tbody tr:hover{background:#fafbff}
    tbody tr:last-child{border-bottom:none}
    td{padding:12px 16px;font-size:0.85rem;vertical-align:middle}
    .td-title{font-weight:600;color:var(--dark)}
    .td-sub{font-size:0.78rem;color:var(--muted);margin-top:2px}

    /* LIEN FORM inline */
    .lien-form{display:flex;flex-direction:column;gap:6px}
    .lien-input-row{display:flex;gap:6px;align-items:center}
    .lien-input{flex:1;padding:7px 10px;border:1px solid var(--border);border-radius:7px;font-size:0.8rem;font-family:'Inter',sans-serif;outline:none;transition:border 0.2s;min-width:0}
    .lien-input:focus{border-color:var(--primary)}
    .lien-input.has-value{border-color:var(--secondary);background:#f0fdf4}
    .lien-label{font-size:0.72rem;font-weight:600;color:var(--muted);width:60px;flex-shrink:0}

    /* BADGES */
    .badge-dl{display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:20px;font-size:0.72rem;font-weight:600}
    .badge-ok{background:#dcfce7;color:#15803d}
    .badge-no{background:#f1f5ff;color:var(--muted)}
    .badge-mat{padding:3px 9px;border-radius:6px;font-size:0.72rem;font-weight:600}
    .tag-math{background:#eff6ff;color:#2563eb}.tag-phys{background:#fdf2ff;color:#9333ea}
    .tag-bio{background:#f0fdf4;color:#16a34a}.tag-ang{background:#fff7ed;color:#ea580c}
    .tag-chim{background:#fff1f2;color:#e11d48}

    /* TAG COLOR PICKER */
    .tag-picker{display:flex;gap:6px;flex-wrap:wrap;margin-top:4px}
    .tag-choice{padding:4px 10px;border-radius:6px;font-size:0.72rem;font-weight:600;cursor:pointer;border:2px solid transparent;transition:all 0.15s}
    .tag-choice.active,.tag-choice:hover{border-color:var(--dark);transform:scale(1.05)}

    .dl-count{display:inline-flex;align-items:center;gap:3px;font-size:0.75rem;color:var(--muted)}
    .actions-td{display:flex;gap:6px;align-items:center}

    @media(max-width:900px){.form-grid{grid-template-columns:1fr 1fr} table{font-size:0.8rem}}
    @media(max-width:600px){.form-grid{grid-template-columns:1fr} .lien-input-row{flex-direction:column} .lien-label{width:auto}}
  </style>
</head>
<body>

<?php if (!isset($_SESSION['admin'])): ?>
<!-- ══ PAGE LOGIN ══ -->
<div class="login-wrap">
  <div class="login-card">
    <div style="text-align:center;margin-bottom:20px;">
      <div style="width:52px;height:52px;background:var(--primary);border-radius:12px;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;font-size:1.4rem;color:white;">
        <i class="fas fa-lock"></i>
      </div>
      <h2>Administration</h2>
      <p>Accès réservé à l'équipe EduGuide SN</p>
    </div>
    <?php if ($error): ?>
      <div class="alert alert-error"><i class="fas fa-times-circle"></i> <?= h($error) ?></div>
    <?php endif; ?>
    <form method="POST">
      <input type="hidden" name="action" value="login"/>
      <div class="form-group">
        <label>Mot de passe administrateur</label>
        <input type="password" name="password" class="form-control" placeholder="••••••••" autofocus required/>
      </div>
      <button type="submit" class="btn-sm btn-primary btn-block">
        <i class="fas fa-sign-in-alt"></i> Se connecter
      </button>
    </form>
    <div style="text-align:center;margin-top:16px;">
      <a href="epreuves.php" style="font-size:0.82rem;color:var(--muted);">← Retour au site</a>
    </div>
  </div>
</div>

<?php else: ?>
<!-- ══ PAGE ADMIN ══ -->

<div class="topbar">
  <div class="topbar-logo">
    <div class="logo-icon"><i class="fas fa-graduation-cap"></i></div>
    <span>EduGuide <span class="sn">SN</span></span>
    <span style="margin-left:8px;font-size:0.78rem;font-weight:400;color:var(--muted);">/ Admin Épreuves</span>
  </div>
  <div class="topbar-actions">
    <a href="epreuves.php" class="btn-sm btn-outline"><i class="fas fa-eye"></i> Voir le site</a>
    <a href="admin_epreuves.php?action=logout" class="btn-sm btn-danger"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
  </div>
</div>

<div class="main">

  <div class="page-header">
    <div>
      <h1>Gestion des Épreuves</h1>
      <p>Renseignez le chemin local ou le lien externe (Google Drive, Dropbox…) pour chaque épreuve.</p>
    </div>
  </div>

  <!-- ALERTS -->
  <?php if (isset($_GET['msg'])): ?>
    <?php if ($_GET['msg'] === 'saved'): ?>
      <div class="alert alert-success"><i class="fas fa-check-circle"></i> Lien enregistré avec succès !</div>
    <?php elseif ($_GET['msg'] === 'added'): ?>
      <div class="alert alert-success"><i class="fas fa-check-circle"></i> Épreuve ajoutée avec succès !</div>
    <?php elseif ($_GET['msg'] === 'deleted'): ?>
      <div class="alert alert-error"><i class="fas fa-trash"></i> Épreuve supprimée.</div>
    <?php endif; ?>
  <?php endif; ?>

  <!-- INFO BOX -->
  <div class="alert alert-info" style="margin-bottom:24px;">
    <i class="fas fa-info-circle" style="font-size:1.1rem;"></i>
    <div>
      <strong>Comment renseigner un fichier :</strong><br>
      <span style="font-size:0.82rem;">
        • <strong>Chemin local</strong> : nom du fichier PDF dans <code>uploads/epreuves/</code> — ex: <code>math-esp-2024.pdf</code><br>
        • <strong>Lien externe</strong> : URL complète Google Drive, Dropbox, OneDrive… — ex: <code>https://drive.google.com/file/d/xxx/view</code><br>
        • Si les deux sont renseignés, le <strong>lien externe est prioritaire</strong>.
      </span>
    </div>
  </div>

  <!-- FORMULAIRE AJOUT -->
  <div class="add-card">
    <h3><i class="fas fa-plus-circle"></i> Ajouter une nouvelle épreuve</h3>
    <form method="POST">
      <input type="hidden" name="action" value="add_epreuve"/>
      <div class="form-grid">
        <div class="form-group">
          <label>Concours *</label>
          <select name="concours_id" class="form-control" required>
            <option value="">— Choisir —</option>
            <?php foreach ($concoursList as $c): ?>
              <option value="<?= $c['id'] ?>"><?= h($c['label']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Matière *</label>
          <input type="text" name="matiere" class="form-control" placeholder="ex: Mathématiques" required/>
        </div>
        <div class="form-group">
          <label>Année *</label>
          <input type="number" name="annee" class="form-control" placeholder="2024" min="2000" max="2099" required/>
        </div>
        <div class="form-group">
          <label><i class="fas fa-folder-open" style="color:var(--accent);"></i> Chemin local (fichier PDF)</label>
          <input type="text" name="fichier" class="form-control" placeholder="math-esp-2024.pdf"/>
          <div style="font-size:0.72rem;color:var(--muted);margin-top:4px;">Déposez le PDF dans <code>uploads/epreuves/</code></div>
        </div>
        <div class="form-group">
          <label><i class="fas fa-link" style="color:var(--primary);"></i> Lien externe (URL)</label>
          <input type="url" name="lien_externe" class="form-control" placeholder="https://drive.google.com/..."/>
        </div>
        <div class="form-group">
          <label>Couleur du badge</label>
          <select name="tag_classe" class="form-control">
            <option value="tag-math">🔵 Mathématiques</option>
            <option value="tag-phys">🟣 Physique-Chimie</option>
            <option value="tag-bio">🟢 Biologie</option>
            <option value="tag-ang">🟠 Anglais</option>
            <option value="tag-chim">🔴 Chimie</option>
          </select>
        </div>
      </div>
      <button type="submit" class="btn-sm btn-primary"><i class="fas fa-plus"></i> Ajouter l'épreuve</button>
    </form>
  </div>

  <!-- TABLEAU ÉPREUVES -->
  <div class="table-card">
    <div class="table-header">
      <h3>Toutes les épreuves</h3>
      <span class="count"><?= count($epreuves) ?> épreuve<?= count($epreuves) > 1 ? 's' : '' ?></span>
    </div>
    <div style="overflow-x:auto;">
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Matière / Concours</th>
            <th>Année</th>
            <th style="min-width:320px;">Chemin local & Lien externe</th>
            <th>Statut</th>
            <th><i class="fas fa-download"></i></th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($epreuves as $ep): ?>
          <tr id="ep-<?= $ep['id'] ?>">
            <td style="color:var(--muted);font-size:0.78rem;">#<?= $ep['id'] ?></td>
            <td>
              <div class="td-title">
                <span class="badge-mat <?= h($ep['tag_classe']) ?>"><?= h($ep['matiere']) ?></span>
              </div>
              <div class="td-sub"><?= h($ep['concours_nom']) ?></div>
              <div class="td-sub" style="font-size:0.73rem;color:#9ca3af;"><?= h($ep['ecole_nom']) ?></div>
            </td>
            <td><strong><?= h($ep['annee']) ?></strong></td>
            <td>
              <!-- Formulaire inline de mise à jour -->
              <form method="POST" class="lien-form">
                <input type="hidden" name="action" value="save_lien"/>
                <input type="hidden" name="id" value="<?= $ep['id'] ?>"/>

                <div class="lien-input-row">
                  <span class="lien-label" title="Nom du fichier dans uploads/epreuves/">
                    <i class="fas fa-folder" style="color:var(--accent);"></i> Local
                  </span>
                  <input type="text"
                    name="fichier"
                    class="lien-input <?= $ep['fichier'] ? 'has-value' : '' ?>"
                    value="<?= h($ep['fichier'] ?? '') ?>"
                    placeholder="math-esp-2024.pdf"/>
                </div>

                <div class="lien-input-row">
                  <span class="lien-label" title="URL complète : Google Drive, Dropbox...">
                    <i class="fas fa-link" style="color:var(--primary);"></i> URL
                  </span>
                  <input type="url"
                    name="lien_externe"
                    class="lien-input <?= $ep['lien_externe'] ? 'has-value' : '' ?>"
                    value="<?= h($ep['lien_externe'] ?? '') ?>"
                    placeholder="https://drive.google.com/..."/>
                </div>

                <button type="submit" class="btn-sm btn-success" style="align-self:flex-start;margin-top:2px;">
                  <i class="fas fa-save"></i> Enregistrer
                </button>
              </form>
            </td>
            <td>
              <?php if (!empty($ep['lien_externe'])): ?>
                <span class="badge-dl badge-ok"><i class="fas fa-link"></i> URL</span>
              <?php elseif (!empty($ep['fichier'])): ?>
                <span class="badge-dl badge-ok"><i class="fas fa-file-pdf"></i> Local</span>
              <?php else: ?>
                <span class="badge-dl badge-no"><i class="fas fa-clock"></i> Aucun</span>
              <?php endif; ?>
            </td>
            <td>
              <span class="dl-count"><i class="fas fa-download"></i> <?= $ep['nb_telechargements'] ?></span>
            </td>
            <td>
              <div class="actions-td">
                <?php if (!empty($ep['lien_externe']) || !empty($ep['fichier'])): ?>
                  <a href="epreuves.php?download=<?= $ep['id'] ?>" target="_blank" title="Tester le téléchargement">
                    <button class="btn-sm btn-primary" style="padding:6px 10px;"><i class="fas fa-download"></i></button>
                  </a>
                <?php endif; ?>
                <a href="admin_epreuves.php?action=delete&id=<?= $ep['id'] ?>"
                   onclick="return confirm('Supprimer cette épreuve ?')">
                  <button class="btn-sm btn-danger" style="padding:6px 10px;"><i class="fas fa-trash"></i></button>
                </a>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

</div><!-- /main -->
<?php endif; ?>

<script>
// Mise en évidence des inputs modifiés
document.querySelectorAll('.lien-input').forEach(input => {
    input.addEventListener('input', function() {
        this.classList.toggle('has-value', this.value.trim().length > 0);
    });
});
</script>
</body>
</html>
