<?php
// ══ TOUTES LES ACTIONS D'ABORD (avant tout output HTML) ══
require_once 'includes/admin_common.php';

// Auth
if (session_status() === PHP_SESSION_NONE) session_start();
$adminPass = defined('ADMIN_PASS') ? ADMIN_PASS : 'admin123';
$error = '';
if (($_POST['action']??'') === 'login') {
    if (($_POST['password']??'') === $adminPass) { $_SESSION['admin'] = true; }
    else { $error = 'Mot de passe incorrect.'; }
}
if (($_GET['action']??'') === 'logout') { session_destroy(); header('Location: admin_ecoles.php'); exit; }

// Actions CRUD (seulement si connecté)
if (isset($_SESSION['admin'])) {
    $db = getDB();

    if (($_GET['action']??'') === 'delete' && isset($_GET['id'])) {
        $db->prepare("DELETE FROM ecoles WHERE id=?")->execute([(int)$_GET['id']]);
        header('Location: admin_ecoles.php?msg=deleted'); exit;
    }

    if (($_POST['action']??'') === 'add') {
        $p = parseEcolePost();
        $db->prepare("INSERT INTO ecoles (nom,slug,type,ville,icone,presentation,filieres,domaines,conditions,pieces,type_admission,email,telephone) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?)")->execute($p);
        header('Location: admin_ecoles.php?msg=added'); exit;
    }

    if (($_POST['action']??'') === 'edit') {
        $p = parseEcolePost();
        $p[] = (int)$_POST['id'];
        $db->prepare("UPDATE ecoles SET nom=?,slug=?,type=?,ville=?,icone=?,presentation=?,filieres=?,domaines=?,conditions=?,pieces=?,type_admission=?,email=?,telephone=? WHERE id=?")->execute($p);
        header('Location: admin_ecoles.php?msg=edited'); exit;
    }
}

function parseEcolePost(): array {
    $filieres  = array_values(array_filter(array_map('trim', explode(',', $_POST['filieres']??''))));
    $labels    = array_map('trim', explode(',', $_POST['domaines_labels']??''));
    $classes   = array_map('trim', explode(',', $_POST['domaines_classes']??''));
    $domaines  = [];
    foreach ($labels as $i=>$lbl) if ($lbl) $domaines[] = ['label'=>$lbl,'classe'=>$classes[$i]??'dt-blue'];
    $conditions= array_values(array_filter(array_map('trim', explode("\n", $_POST['conditions']??''))));
    $pieces    = array_values(array_filter(array_map('trim', explode("\n", $_POST['pieces']??''))));
    return [
        trim($_POST['nom']), trim($_POST['slug']), $_POST['type']??'public',
        trim($_POST['ville']), trim($_POST['icone']?:'fas fa-university'),
        trim($_POST['presentation']),
        json_encode($filieres, JSON_UNESCAPED_UNICODE),
        json_encode($domaines, JSON_UNESCAPED_UNICODE),
        json_encode($conditions,JSON_UNESCAPED_UNICODE),
        json_encode($pieces,   JSON_UNESCAPED_UNICODE),
        trim($_POST['type_admission']?:'Par concours'),
        trim($_POST['email']??''), trim($_POST['telephone']??''),
    ];
}

// ══ MAINTENANT ON AFFICHE ══
adminHeader('Admin — Écoles');

if (!isset($_SESSION['admin'])) {
    adminLoginPage($error, $_SERVER['PHP_SELF']);
    // adminLoginPage() fait exit, donc on n'arrive jamais ici
}

$db     = getDB();
$ecoles = $db->query("SELECT * FROM ecoles ORDER BY nom")->fetchAll();

$editEcole = null;
if (isset($_GET['edit'])) {
    $s = $db->prepare("SELECT * FROM ecoles WHERE id=?");
    $s->execute([(int)$_GET['edit']]);
    $editEcole = $s->fetch() ?: null;
}

function ecoleFormFields(array $e = []): void {
    $f   = fn($k,$d='') => htmlspecialchars($e[$k] ?? $d, ENT_QUOTES, 'UTF-8');
    $fil = implode(', ', jsonDecode($e['filieres']??'[]'));
    $dom = jsonDecode($e['domaines']??'[]');
    $domL= implode(', ', array_column($dom,'label'));
    $domC= implode(', ', array_column($dom,'classe'));
    $cond= implode("\n", jsonDecode($e['conditions']??'[]'));
    $piec= implode("\n", jsonDecode($e['pieces']??'[]'));
    ?>
    <div class="form-grid form-grid-2" style="margin-bottom:14px">
      <div class="form-group"><label>Nom *</label><input name="nom" class="form-control" value="<?=$f('nom')?>" required placeholder="École Supérieure Polytechnique"/></div>
      <div class="form-group"><label>Slug * <span class="hint">URL unique</span></label><input name="slug" class="form-control" value="<?=$f('slug')?>" required placeholder="esp"/></div>
      <div class="form-group"><label>Type *</label>
        <select name="type" class="form-control">
          <option value="public" <?=($e['type']??'')==='public'?'selected':''?>>Public</option>
          <option value="prive"  <?=($e['type']??'')==='prive' ?'selected':''?>>Privé</option>
        </select>
      </div>
      <div class="form-group"><label>Ville *</label><input name="ville" class="form-control" value="<?=$f('ville')?>" required placeholder="Dakar"/></div>
      <div class="form-group"><label>Icône FontAwesome</label><input name="icone" class="form-control" value="<?=$f('icone','fas fa-university')?>" placeholder="fas fa-university"/></div>
      <div class="form-group"><label>Type d'admission</label><input name="type_admission" class="form-control" value="<?=$f('type_admission','Par concours')?>"/></div>
      <div class="form-group"><label>Email</label><input name="email" type="email" class="form-control" value="<?=$f('email')?>" placeholder="contact@ecole.sn"/></div>
      <div class="form-group"><label>Téléphone</label><input name="telephone" class="form-control" value="<?=$f('telephone')?>" placeholder="+221 33 XXX XX XX"/></div>
    </div>
    <div class="form-group" style="margin-bottom:14px"><label>Présentation</label>
      <textarea name="presentation" class="form-control" rows="3"><?=$f('presentation')?></textarea>
    </div>
    <div class="form-grid form-grid-2" style="margin-bottom:14px">
      <div class="form-group"><label>Filières <span class="hint">(virgule)</span></label><input name="filieres" class="form-control" value="<?=htmlspecialchars($fil,ENT_QUOTES,'UTF-8')?>"/></div>
      <div class="form-group"><label>Domaines — Libellés <span class="hint">(virgule)</span></label><input name="domaines_labels" class="form-control" value="<?=htmlspecialchars($domL,ENT_QUOTES,'UTF-8')?>"/></div>
      <div class="form-group"><label>Domaines — Classes <span class="hint">dt-blue / dt-green / dt-purple / dt-orange</span></label><input name="domaines_classes" class="form-control" value="<?=htmlspecialchars($domC,ENT_QUOTES,'UTF-8')?>"/></div>
    </div>
    <div class="form-grid form-grid-2">
      <div class="form-group"><label>Conditions d'accès <span class="hint">(une par ligne)</span></label><textarea name="conditions" class="form-control" rows="4"><?=htmlspecialchars($cond,ENT_QUOTES,'UTF-8')?></textarea></div>
      <div class="form-group"><label>Pièces à fournir <span class="hint">(une par ligne)</span></label><textarea name="pieces" class="form-control" rows="4"><?=htmlspecialchars($piec,ENT_QUOTES,'UTF-8')?></textarea></div>
    </div>
    <?php
}
?>
</head><body>
<?php adminSidebar('ecoles'); ?>
<div class="admin-main">
  <div class="admin-topbar">
    <h1><i class="fas fa-university" style="color:var(--primary);margin-right:8px"></i>Gestion des Écoles</h1>
    <div class="topbar-right">
      <button class="btn btn-primary btn-sm" onclick="openModal('modal-add')"><i class="fas fa-plus"></i> Nouvelle école</button>
    </div>
  </div>
  <div class="admin-body">

    <?php $msgs=['added'=>['success','École ajoutée !'],'edited'=>['success','École modifiée !'],'deleted'=>['error','École supprimée.']];
    if(isset($_GET['msg'],$msgs[$_GET['msg']])):[$t,$txt]=$msgs[$_GET['msg']]; ?>
    <div class="alert alert-<?=$t?>"><i class="fas fa-check-circle"></i> <?=$txt?></div>
    <?php endif; ?>

    <div class="stats-row">
      <div class="stat-card"><div class="stat-icon blue"><i class="fas fa-university"></i></div><div class="stat-info"><div class="num"><?=count($ecoles)?></div><div class="lbl">Écoles enregistrées</div></div></div>
      <div class="stat-card"><div class="stat-icon green"><i class="fas fa-landmark"></i></div><div class="stat-info"><div class="num"><?=count(array_filter($ecoles,fn($e)=>$e['type']==='public'))?></div><div class="lbl">Publiques</div></div></div>
      <div class="stat-card"><div class="stat-icon orange"><i class="fas fa-store"></i></div><div class="stat-info"><div class="num"><?=count(array_filter($ecoles,fn($e)=>$e['type']==='prive'))?></div><div class="lbl">Privées</div></div></div>
    </div>

    <div class="card">
      <div class="card-header"><h3>Toutes les écoles</h3><span class="count-badge"><?=count($ecoles)?> école<?=count($ecoles)>1?'s':''?></span></div>
      <div class="table-wrap"><table>
        <thead><tr><th>#</th><th>Nom & Localisation</th><th>Type</th><th>Filières</th><th>Admission</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach($ecoles as $e): $fil=jsonDecode($e['filieres']); ?>
        <tr>
          <td style="color:var(--muted);font-size:.76rem">#<?=$e['id']?></td>
          <td>
            <div class="td-main"><i class="<?=h($e['icone'])?>" style="color:var(--primary);margin-right:6px"></i><?=h($e['nom'])?></div>
            <div class="td-sub"><i class="fas fa-map-marker-alt"></i> <?=h($e['ville'])?> &bull; <code><?=h($e['slug'])?></code></div>
          </td>
          <td><span class="badge badge-<?=$e['type']?>"><?=$e['type']==='public'?'Public':'Privé'?></span></td>
          <td style="max-width:180px"><?php foreach(array_slice($fil,0,3) as $f): ?><span style="background:#eff6ff;color:var(--primary);border-radius:5px;padding:2px 7px;font-size:.7rem;display:inline-block;margin:1px"><?=h($f)?></span><?php endforeach; ?><?php if(count($fil)>3): ?><span style="font-size:.7rem;color:var(--muted)">+<?=count($fil)-3?></span><?php endif; ?></td>
          <td style="font-size:.78rem"><?=h($e['type_admission'])?></td>
          <td><div class="actions">
            <a href="admin_ecoles.php?edit=<?=$e['id']?>"><button class="btn btn-warning btn-xs"><i class="fas fa-edit"></i> Modifier</button></a>
            <a href="?action=delete&id=<?=$e['id']?>" onclick="return confirm('Supprimer cette école ?')"><button class="btn btn-danger btn-xs"><i class="fas fa-trash"></i></button></a>
            <a href="ecole.php?slug=<?=h($e['slug'])?>" target="_blank"><button class="btn btn-outline btn-xs"><i class="fas fa-eye"></i></button></a>
          </div></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table></div>
    </div>
  </div>
</div>

<!-- MODAL AJOUTER -->
<div class="modal-overlay" id="modal-add">
  <div class="modal" style="max-width:700px">
    <div class="modal-header"><h3><i class="fas fa-plus-circle" style="color:var(--primary);margin-right:8px"></i>Nouvelle école</h3><button class="modal-close" onclick="closeModal('modal-add')">✕</button></div>
    <form method="POST"><input type="hidden" name="action" value="add"/>
      <div class="modal-body"><?php ecoleFormFields(); ?></div>
      <div class="modal-footer"><button type="button" class="btn btn-outline" onclick="closeModal('modal-add')">Annuler</button><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Enregistrer</button></div>
    </form>
  </div>
</div>

<!-- MODAL MODIFIER (prérempli PHP) -->
<?php if ($editEcole): ?>
<div class="modal-overlay open" id="modal-edit">
  <div class="modal" style="max-width:700px">
    <div class="modal-header"><h3><i class="fas fa-edit" style="color:var(--accent);margin-right:8px"></i>Modifier — <?=h($editEcole['nom'])?></h3><button class="modal-close" onclick="location.href='admin_ecoles.php'">✕</button></div>
    <form method="POST"><input type="hidden" name="action" value="edit"/><input type="hidden" name="id" value="<?=(int)$editEcole['id']?>"/>
      <div class="modal-body"><?php ecoleFormFields($editEcole); ?></div>
      <div class="modal-footer"><a href="admin_ecoles.php"><button type="button" class="btn btn-outline">Annuler</button></a><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Enregistrer</button></div>
    </form>
  </div>
</div>
<?php endif; ?>

<script>
function openModal(id){document.getElementById(id).classList.add('open');document.body.style.overflow='hidden';}
function closeModal(id){document.getElementById(id).classList.remove('open');document.body.style.overflow='';}
document.querySelectorAll('.modal-overlay').forEach(m=>m.addEventListener('click',e=>{if(e.target===m&&m.id==='modal-add')closeModal(m.id);}));
</script>
</body></html>
