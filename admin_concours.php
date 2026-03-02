<?php
// ══ TOUTES LES ACTIONS D'ABORD (avant tout output HTML) ══
require_once 'includes/admin_common.php';

if (session_status() === PHP_SESSION_NONE) session_start();
$adminPass = defined('ADMIN_PASS') ? ADMIN_PASS : 'admin123';
$error = '';
if (($_POST['action']??'') === 'login') {
    if (($_POST['password']??'') === $adminPass) { $_SESSION['admin'] = true; }
    else { $error = 'Mot de passe incorrect.'; }
}
if (($_GET['action']??'') === 'logout') { session_destroy(); header('Location: admin_concours.php'); exit; }

if (isset($_SESSION['admin'])) {
    $db = getDB();

    if (($_GET['action']??'') === 'delete' && isset($_GET['id'])) {
        $db->prepare("DELETE FROM concours WHERE id=?")->execute([(int)$_GET['id']]);
        header('Location: admin_concours.php?msg=deleted'); exit;
    }

    if (($_POST['action']??'') === 'add') {
        $p = parseConcoursPost();
        $db->prepare("INSERT INTO concours (ecole_id,nom,annee,date_limite,niveau_requis,matieres,description,frais,statut) VALUES(?,?,?,?,?,?,?,?,?)")->execute($p);
        header('Location: admin_concours.php?msg=added'); exit;
    }

    if (($_POST['action']??'') === 'edit') {
        $p = parseConcoursPost();
        $p[] = (int)$_POST['id'];
        $db->prepare("UPDATE concours SET ecole_id=?,nom=?,annee=?,date_limite=?,niveau_requis=?,matieres=?,description=?,frais=?,statut=? WHERE id=?")->execute($p);
        header('Location: admin_concours.php?msg=edited'); exit;
    }
}

function parseConcoursPost(): array {
    $matieres = array_values(array_filter(array_map('trim', explode(',', $_POST['matieres']??''))));
    return [
        (int)($_POST['ecole_id']??0),
        trim($_POST['nom']??''),
        (int)($_POST['annee']??date('Y')),
        trim($_POST['date_limite']??'')?:null,
        trim($_POST['niveau_requis']??''),
        json_encode($matieres, JSON_UNESCAPED_UNICODE),
        trim($_POST['description']??''),
        (float)($_POST['frais']??0),
        $_POST['statut']??'a_venir',
    ];
}

// ══ AFFICHAGE ══
adminHeader('Admin — Concours');

if (!isset($_SESSION['admin'])) {
    adminLoginPage($error, $_SERVER['PHP_SELF']);
}

$db       = getDB();
$concours = $db->query("SELECT c.*, e.nom AS ecole_nom FROM concours c JOIN ecoles e ON c.ecole_id=e.id ORDER BY c.annee DESC, c.nom")->fetchAll();
$ecoles   = $db->query("SELECT id, nom FROM ecoles ORDER BY nom")->fetchAll();

$editConcours = null;
if (isset($_GET['edit'])) {
    $s = $db->prepare("SELECT * FROM concours WHERE id=?");
    $s->execute([(int)$_GET['edit']]);
    $editConcours = $s->fetch() ?: null;
}

function concoursFormFields(array $c = [], array $ecoles = []): void {
    $f       = fn($k,$d='') => htmlspecialchars($c[$k] ?? $d, ENT_QUOTES, 'UTF-8');
    $matieres= implode(', ', jsonDecode($c['matieres']??'[]'));
    $dateVal = isset($c['date_limite']) && $c['date_limite'] ? substr($c['date_limite'],0,10) : '';
    ?>
    <div class="form-grid form-grid-2" style="margin-bottom:14px">
      <div class="form-group" style="grid-column:1/-1"><label>École *</label>
        <select name="ecole_id" class="form-control" required>
          <option value="">— Choisir une école —</option>
          <?php foreach($ecoles as $e): ?>
            <option value="<?=$e['id']?>" <?=($c['ecole_id']??0)==$e['id']?'selected':''?>><?=htmlspecialchars($e['nom'],ENT_QUOTES,'UTF-8')?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group" style="grid-column:1/-1"><label>Nom du concours *</label><input name="nom" class="form-control" value="<?=$f('nom')?>" required placeholder="ex: Concours d'entrée ESP 2025"/></div>
      <div class="form-group"><label>Année *</label><input name="annee" type="number" class="form-control" value="<?=$f('annee',date('Y'))?>" min="2000" max="2099" required/></div>
      <div class="form-group"><label>Date limite d'inscription</label><input name="date_limite" type="date" class="form-control" value="<?=htmlspecialchars($dateVal,ENT_QUOTES,'UTF-8')?>"/></div>
      <div class="form-group"><label>Niveau requis</label><input name="niveau_requis" class="form-control" value="<?=$f('niveau_requis')?>" placeholder="Baccalauréat S1, S2, S3"/></div>
      <div class="form-group"><label>Frais de dossier (FCFA)</label><input name="frais" type="number" class="form-control" value="<?=$f('frais','0')?>" min="0"/></div>
      <div class="form-group"><label>Statut</label>
        <select name="statut" class="form-control">
          <option value="ouvert"  <?=($c['statut']??'')==='ouvert' ?'selected':''?>>Ouvert</option>
          <option value="ferme"   <?=($c['statut']??'')==='ferme'  ?'selected':''?>>Fermé</option>
          <option value="a_venir" <?=($c['statut']??'a_venir')==='a_venir'?'selected':''?>>À venir</option>
        </select>
      </div>
      <div class="form-group"><label>Matières <span class="hint">(séparées par virgule)</span></label><input name="matieres" class="form-control" value="<?=htmlspecialchars($matieres,ENT_QUOTES,'UTF-8')?>" placeholder="Mathématiques, Physique, Français"/></div>
    </div>
    <div class="form-group"><label>Description</label><textarea name="description" class="form-control" rows="3" placeholder="Informations complémentaires..."><?=$f('description')?></textarea></div>
    <?php
}
?>
</head><body>
<?php adminSidebar('concours'); ?>
<div class="admin-main">
  <div class="admin-topbar">
    <h1><i class="fas fa-file-alt" style="color:var(--primary);margin-right:8px"></i>Gestion des Concours</h1>
    <div class="topbar-right">
      <button class="btn btn-primary btn-sm" onclick="openModal('modal-add')"><i class="fas fa-plus"></i> Nouveau concours</button>
    </div>
  </div>
  <div class="admin-body">

    <?php $msgs=['added'=>['success','Concours ajouté !'],'edited'=>['success','Concours modifié !'],'deleted'=>['error','Concours supprimé.']];
    if(isset($_GET['msg'],$msgs[$_GET['msg']])):[$t,$txt]=$msgs[$_GET['msg']]; ?>
    <div class="alert alert-<?=$t?>"><i class="fas fa-check-circle"></i> <?=$txt?></div>
    <?php endif; ?>

    <div class="stats-row">
      <div class="stat-card"><div class="stat-icon blue"><i class="fas fa-file-alt"></i></div><div class="stat-info"><div class="num"><?=count($concours)?></div><div class="lbl">Total concours</div></div></div>
      <div class="stat-card"><div class="stat-icon green"><i class="fas fa-door-open"></i></div><div class="stat-info"><div class="num"><?=count(array_filter($concours,fn($c)=>$c['statut']==='ouvert'))?></div><div class="lbl">Ouverts</div></div></div>
      <div class="stat-card"><div class="stat-icon orange"><i class="fas fa-clock"></i></div><div class="stat-info"><div class="num"><?=count(array_filter($concours,fn($c)=>$c['statut']==='a_venir'))?></div><div class="lbl">À venir</div></div></div>
    </div>

    <div class="card">
      <div class="card-header"><h3>Tous les concours</h3><span class="count-badge"><?=count($concours)?> concours</span></div>
      <div class="table-wrap"><table>
        <thead><tr><th>#</th><th>Nom du concours</th><th>École</th><th>Année</th><th>Date limite</th><th>Statut</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach($concours as $c): ?>
        <tr>
          <td style="color:var(--muted);font-size:.76rem">#<?=$c['id']?></td>
          <td><div class="td-main"><?=h($c['nom'])?></div><div class="td-sub"><?=h($c['niveau_requis']??'')?></div></td>
          <td style="font-size:.82rem"><?=h($c['ecole_nom'])?></td>
          <td><strong><?=$c['annee']?></strong></td>
          <td style="font-size:.82rem"><?=$c['date_limite']?date('d/m/Y',strtotime($c['date_limite'])):'-'?></td>
          <td><?php $cls=['ouvert'=>'badge-ouvert','ferme'=>'badge-ferme','a_venir'=>'badge-avenir']; ?>
            <span class="badge <?=$cls[$c['statut']]??''?>"><?=$c['statut']==='a_venir'?'À venir':ucfirst($c['statut'])?></span>
          </td>
          <td><div class="actions">
            <a href="admin_concours.php?edit=<?=$c['id']?>"><button class="btn btn-warning btn-xs"><i class="fas fa-edit"></i> Modifier</button></a>
            <a href="?action=delete&id=<?=$c['id']?>" onclick="return confirm('Supprimer ce concours ?')"><button class="btn btn-danger btn-xs"><i class="fas fa-trash"></i></button></a>
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
  <div class="modal" style="max-width:680px">
    <div class="modal-header"><h3><i class="fas fa-plus-circle" style="color:var(--primary);margin-right:8px"></i>Nouveau concours</h3><button class="modal-close" onclick="closeModal('modal-add')">✕</button></div>
    <form method="POST"><input type="hidden" name="action" value="add"/>
      <div class="modal-body"><?php concoursFormFields([], $ecoles); ?></div>
      <div class="modal-footer"><button type="button" class="btn btn-outline" onclick="closeModal('modal-add')">Annuler</button><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Enregistrer</button></div>
    </form>
  </div>
</div>

<!-- MODAL MODIFIER (prérempli PHP) -->
<?php if ($editConcours): ?>
<div class="modal-overlay open" id="modal-edit">
  <div class="modal" style="max-width:680px">
    <div class="modal-header"><h3><i class="fas fa-edit" style="color:var(--accent);margin-right:8px"></i>Modifier — <?=h($editConcours['nom'])?></h3><button class="modal-close" onclick="location.href='admin_concours.php'">✕</button></div>
    <form method="POST"><input type="hidden" name="action" value="edit"/><input type="hidden" name="id" value="<?=(int)$editConcours['id']?>"/>
      <div class="modal-body"><?php concoursFormFields($editConcours, $ecoles); ?></div>
      <div class="modal-footer"><a href="admin_concours.php"><button type="button" class="btn btn-outline">Annuler</button></a><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Enregistrer</button></div>
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
