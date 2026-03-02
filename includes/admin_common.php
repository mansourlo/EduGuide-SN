<?php
/**
 * includes/admin_common.php
 * Session, auth, CSS et fonctions partagées entre toutes les pages admin
 */
require_once __DIR__ . '/functions.php';

$adminPass = defined('ADMIN_PASS') ? ADMIN_PASS : 'admin123';
if (session_status() === PHP_SESSION_NONE) session_start();

$error = '';
if (($_POST['action'] ?? '') === 'login') {
    if (($_POST['password'] ?? '') === $adminPass) {
        $_SESSION['admin'] = true;
    } else {
        $error = 'Mot de passe incorrect.';
    }
}
if (($_GET['action'] ?? '') === 'logout') {
    session_destroy();
    header('Location: ' . ($_SERVER['PHP_SELF'] ?? 'index.php'));
    exit;
}

function adminHeader(string $title, string $activePage = ''): void {
    $pages = [
        'ecoles'   => ['admin_ecoles.php',   'fas fa-university', 'Écoles'],
        'concours' => ['admin_concours.php',  'fas fa-file-alt',   'Concours'],
        'epreuves' => ['admin_epreuves.php',  'fas fa-download',   'Épreuves'],
    ];
    ?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title><?= htmlspecialchars($title) ?> — Admin EduGuide SN</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <style>
  *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
  :root{--primary:#1a6ef5;--primary-dark:#1055cc;--secondary:#22c55e;--accent:#f97316;
        --dark:#1a2233;--text:#1e2d45;--muted:#6b7a99;--bg:#f0f4ff;--border:#e2e8f0;
        --sidebar-w:220px;}
  body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);display:flex;min-height:100vh;}
  h1,h2,h3,h4{font-family:'Poppins',sans-serif;}
  a{text-decoration:none;color:inherit;}

  /* SIDEBAR */
  .sidebar{width:var(--sidebar-w);background:#13192b;min-height:100vh;position:fixed;top:0;left:0;z-index:200;display:flex;flex-direction:column;}
  .sidebar-logo{padding:20px 18px;display:flex;align-items:center;gap:10px;border-bottom:1px solid rgba(255,255,255,0.07);}
  .sidebar-logo .logo-icon{width:34px;height:34px;background:var(--primary);border-radius:8px;display:flex;align-items:center;justify-content:center;color:white;font-size:1rem;flex-shrink:0;}
  .sidebar-logo span{font-family:'Poppins',sans-serif;font-weight:700;color:white;font-size:1rem;}
  .sidebar-logo .sn{color:var(--primary);}
  .sidebar-label{padding:18px 18px 8px;font-size:0.68rem;font-weight:700;color:#4a5568;text-transform:uppercase;letter-spacing:.08em;}
  .sidebar-nav{flex:1;padding:0 10px;}
  .sidebar-nav a{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:8px;color:#a0aec0;font-size:0.875rem;font-weight:500;transition:all 0.2s;margin-bottom:2px;}
  .sidebar-nav a:hover{background:rgba(255,255,255,0.06);color:white;}
  .sidebar-nav a.active{background:var(--primary);color:white;}
  .sidebar-nav a i{width:16px;text-align:center;font-size:0.85rem;}
  .sidebar-footer{padding:16px 18px;border-top:1px solid rgba(255,255,255,0.07);}
  .sidebar-footer a{display:flex;align-items:center;gap:8px;font-size:0.82rem;color:#718096;transition:color 0.2s;}
  .sidebar-footer a:hover{color:white;}

  /* MAIN */
  .admin-main{margin-left:var(--sidebar-w);flex:1;display:flex;flex-direction:column;min-height:100vh;}
  .admin-topbar{background:white;border-bottom:1px solid var(--border);padding:0 28px;height:56px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100;box-shadow:0 1px 4px rgba(26,110,245,0.06);}
  .admin-topbar h1{font-size:1.1rem;font-weight:700;color:var(--dark);}
  .topbar-right{display:flex;align-items:center;gap:10px;}
  .admin-body{padding:28px;flex:1;}

  /* BUTTONS */
  .btn{display:inline-flex;align-items:center;gap:6px;padding:9px 16px;border-radius:8px;font-size:0.85rem;font-weight:600;font-family:'Inter',sans-serif;cursor:pointer;border:none;transition:all 0.2s;}
  .btn-sm{padding:7px 12px;font-size:0.8rem;}
  .btn-xs{padding:5px 9px;font-size:0.75rem;}
  .btn-primary{background:var(--primary);color:white;}.btn-primary:hover{background:var(--primary-dark);}
  .btn-success{background:#dcfce7;color:#15803d;border:1px solid #bbf7d0;}.btn-success:hover{background:#bbf7d0;}
  .btn-danger{background:#fee2e2;color:#b91c1c;border:1px solid #fecaca;}.btn-danger:hover{background:#fecaca;}
  .btn-warning{background:#fef9c3;color:#854d0e;border:1px solid #fde68a;}.btn-warning:hover{background:#fde68a;}
  .btn-outline{background:white;color:var(--muted);border:1px solid var(--border);}.btn-outline:hover{border-color:var(--primary);color:var(--primary);}

  /* ALERTS */
  .alert{padding:12px 16px;border-radius:10px;font-size:0.87rem;margin-bottom:20px;display:flex;align-items:center;gap:10px;}
  .alert-success{background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;}
  .alert-error{background:#fff1f2;border:1px solid #fecaca;color:#b91c1c;}
  .alert-info{background:#eff6ff;border:1px solid #bfdbfe;color:#1d4ed8;}
  .alert-warning{background:#fffbeb;border:1px solid #fde68a;color:#92400e;}

  /* CARDS */
  .card{background:white;border:1px solid var(--border);border-radius:14px;box-shadow:0 2px 8px rgba(26,110,245,0.04);}
  .card-header{padding:18px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;}
  .card-header h3{font-size:1rem;font-weight:700;color:var(--dark);}
  .card-body{padding:22px;}
  .count-badge{background:#eff6ff;color:var(--primary);padding:3px 10px;border-radius:20px;font-size:0.73rem;font-weight:600;}

  /* FORM */
  .form-grid{display:grid;gap:16px;}
  .form-grid-2{grid-template-columns:1fr 1fr;}
  .form-grid-3{grid-template-columns:1fr 1fr 1fr;}
  .form-group{display:flex;flex-direction:column;gap:5px;}
  .form-group label{font-size:0.8rem;font-weight:600;color:var(--dark);}
  .form-group .hint{font-size:0.72rem;color:var(--muted);margin-top:3px;}
  .form-control{padding:10px 13px;border:1px solid var(--border);border-radius:9px;font-size:0.875rem;font-family:'Inter',sans-serif;outline:none;transition:border 0.2s;width:100%;}
  .form-control:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(26,110,245,0.1);}
  .form-control::placeholder{color:#b0b9cc;}
  select.form-control{appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236b7a99' d='M6 8L1 3h10z'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 12px center;padding-right:36px;}
  .form-divider{border:none;border-top:1px solid var(--border);margin:4px 0;}

  /* TABLE */
  .table-wrap{overflow-x:auto;}
  table{width:100%;border-collapse:collapse;}
  thead th{background:#f8faff;padding:11px 14px;text-align:left;font-size:0.76rem;font-weight:600;color:var(--muted);border-bottom:1px solid var(--border);white-space:nowrap;}
  tbody tr{border-bottom:1px solid #f1f5ff;transition:background 0.15s;}
  tbody tr:hover{background:#fafbff;}
  tbody tr:last-child{border-bottom:none;}
  td{padding:11px 14px;font-size:0.85rem;vertical-align:middle;}
  .td-main{font-weight:600;color:var(--dark);line-height:1.3;}
  .td-sub{font-size:0.76rem;color:var(--muted);margin-top:3px;}
  .actions{display:flex;gap:5px;flex-wrap:wrap;}

  /* BADGES */
  .badge{display:inline-flex;align-items:center;gap:3px;padding:3px 9px;border-radius:20px;font-size:0.72rem;font-weight:600;}
  .badge-public{background:#dbeafe;color:#1d4ed8;}
  .badge-prive{background:#fce7f3;color:#be185d;}
  .badge-ouvert{background:#dcfce7;color:#15803d;}
  .badge-ferme{background:#fee2e2;color:#b91c1c;}
  .badge-avenir{background:#fef9c3;color:#854d0e;}

  /* MODAL */
  .modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:999;align-items:center;justify-content:center;}
  .modal-overlay.open{display:flex;}
  .modal{background:white;border-radius:16px;width:100%;max-width:640px;max-height:90vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,0.2);}
  .modal-header{padding:20px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;}
  .modal-header h3{font-size:1.05rem;font-weight:700;}
  .modal-close{background:none;border:none;cursor:pointer;color:var(--muted);font-size:1.2rem;line-height:1;padding:4px;}
  .modal-close:hover{color:var(--dark);}
  .modal-body{padding:24px;}
  .modal-footer{padding:16px 24px;border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:10px;}

  /* STATS CARDS */
  .stats-row{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px;}
  .stat-card{background:white;border:1px solid var(--border);border-radius:12px;padding:18px;display:flex;align-items:center;gap:14px;box-shadow:0 2px 8px rgba(26,110,245,0.04);}
  .stat-icon{width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;color:white;flex-shrink:0;}
  .stat-icon.blue{background:var(--primary);}
  .stat-icon.green{background:var(--secondary);}
  .stat-icon.orange{background:var(--accent);}
  .stat-info .num{font-family:'Poppins',sans-serif;font-size:1.5rem;font-weight:700;color:var(--dark);}
  .stat-info .lbl{font-size:0.76rem;color:var(--muted);}

  /* LOGIN PAGE */
  .login-wrap{display:flex;align-items:center;justify-content:center;min-height:100vh;width:100%;background:var(--bg);}
  .login-card{background:white;border:1px solid var(--border);border-radius:16px;padding:40px;width:100%;max-width:380px;box-shadow:0 4px 24px rgba(26,110,245,0.08);}

  @media(max-width:900px){
    .sidebar{transform:translateX(-100%);}
    .admin-main{margin-left:0;}
    .form-grid-2,.form-grid-3{grid-template-columns:1fr;}
    .stats-row{grid-template-columns:1fr;}
  }
  </style>
    <?php
}

function adminSidebar(string $activePage): void {
    $pages = [
        'ecoles'   => ['admin_ecoles.php',   'fas fa-university', 'Écoles'],
        'concours' => ['admin_concours.php',  'fas fa-file-alt',   'Concours'],
        'epreuves' => ['admin_epreuves.php',  'fas fa-download',   'Épreuves'],
    ];
    ?>
  <aside class="sidebar">
    <div class="sidebar-logo">
      <div class="logo-icon"><i class="fas fa-graduation-cap"></i></div>
      <span>EduGuide <span class="sn">SN</span></span>
    </div>
    <div class="sidebar-label">Administration</div>
    <nav class="sidebar-nav">
      <?php foreach ($pages as $key => [$href, $icon, $label]): ?>
      <a href="<?= $href ?>" class="<?= $activePage === $key ? 'active' : '' ?>">
        <i class="<?= $icon ?>"></i> <?= $label ?>
      </a>
      <?php endforeach; ?>
    </nav>
    <div class="sidebar-footer">
      <a href="index.php" style="margin-bottom:10px;display:flex;align-items:center;gap:8px;">
        <i class="fas fa-eye"></i> Voir le site
      </a>
      <a href="?action=logout" style="display:flex;align-items:center;gap:8px;">
        <i class="fas fa-sign-out-alt"></i> Déconnexion
      </a>
    </div>
  </aside>
    <?php
}

function adminLoginPage(string $error, string $self): void { ?>
<div class="login-wrap">
  <div class="login-card">
    <div style="text-align:center;margin-bottom:24px;">
      <div style="width:54px;height:54px;background:var(--primary);border-radius:14px;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;font-size:1.5rem;color:white;">
        <i class="fas fa-lock"></i>
      </div>
      <h2 style="font-size:1.4rem;margin-bottom:6px;">Administration</h2>
      <p style="color:var(--muted);font-size:0.87rem;">Accès réservé à l'équipe EduGuide SN</p>
    </div>
    <?php if ($error): ?>
      <div class="alert alert-error"><i class="fas fa-times-circle"></i> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST">
      <input type="hidden" name="action" value="login"/>
      <div class="form-group" style="margin-bottom:16px;">
        <label>Mot de passe administrateur</label>
        <input type="password" name="password" class="form-control" placeholder="••••••••" autofocus required/>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
        <i class="fas fa-sign-in-alt"></i> Se connecter
      </button>
    </form>
    <div style="text-align:center;margin-top:16px;">
      <a href="index.php" style="font-size:0.82rem;color:var(--muted);">← Retour au site</a>
    </div>
  </div>
</div>
</body></html>
<?php
    exit;
}
