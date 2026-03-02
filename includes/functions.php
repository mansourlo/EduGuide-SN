<?php
// =============================================
//  Fonctions utilitaires
// =============================================
require_once __DIR__ . '/config.php';

// ── Migration douce : ajouter lien_externe si absent ──
function runMigrations(PDO $db): void {
    try {
        $db->query("SELECT lien_externe FROM epreuves LIMIT 1");
    } catch (\PDOException $e) {
        // La colonne n'existe pas → on la crée
        try {
            $db->exec("ALTER TABLE epreuves ADD COLUMN lien_externe VARCHAR(500) COMMENT 'URL externe' AFTER fichier");
        } catch (\PDOException $e2) { /* ignore */ }
    }
}

function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function jsonDecode(string|null $json): array {
    if (!$json) return [];
    $decoded = json_decode($json, true);
    return is_array($decoded) ? $decoded : [];
}

function badgeType(string $type): string {
    return $type === 'public'
        ? '<span class="badge-public">Public</span>'
        : '<span class="badge-prive">Privé</span>';
}

function statutBadge(string $statut): string {
    return match($statut) {
        'ouvert'  => '<span class="statut-ouvert">Ouvert</span>',
        'ferme'   => '<span class="statut-ferme">Fermé</span>',
        'a_venir' => '<span class="statut-avenir">À venir</span>',
        default   => ''
    };
}

// ── Récupérer toutes les écoles ──
function getAllEcoles(string $search = ''): array {
    $db = getDB();
    if ($search) {
        $stmt = $db->prepare("SELECT * FROM ecoles WHERE nom LIKE ? OR ville LIKE ? OR filieres LIKE ? ORDER BY nom");
        $like = "%$search%";
        $stmt->execute([$like, $like, $like]);
    } else {
        $stmt = $db->query("SELECT * FROM ecoles ORDER BY nom");
    }
    return $stmt->fetchAll();
}

// ── Récupérer une école par slug ──
function getEcoleBySlug(string $slug): array|false {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM ecoles WHERE slug = ?");
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

// ── Récupérer les concours d'une école ──
function getConcoursByEcole(int $ecoleId): array {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM concours WHERE ecole_id = ? ORDER BY annee DESC");
    $stmt->execute([$ecoleId]);
    return $stmt->fetchAll();
}

// ── Récupérer tous les concours ──
function getAllConcours(string $search = ''): array {
    $db = getDB();
    if ($search) {
        $stmt = $db->prepare("
            SELECT c.*, e.nom AS ecole_nom, e.slug AS ecole_slug
            FROM concours c
            JOIN ecoles e ON c.ecole_id = e.id
            WHERE c.nom LIKE ? OR e.nom LIKE ? OR c.matieres LIKE ?
            ORDER BY c.annee DESC, c.date_limite ASC
        ");
        $like = "%$search%";
        $stmt->execute([$like, $like, $like]);
    } else {
        $stmt = $db->query("
            SELECT c.*, e.nom AS ecole_nom, e.slug AS ecole_slug
            FROM concours c
            JOIN ecoles e ON c.ecole_id = e.id
            ORDER BY c.annee DESC, c.date_limite ASC
        ");
    }
    return $stmt->fetchAll();
}

// ── Récupérer toutes les épreuves ──
function getAllEpreuves(string $search = '', string $annee = '', string $matiere = '', string $concours_id = ''): array {
    $db = getDB();

    // Vérifier si la colonne lien_externe existe (migration douce)
    try {
        $db->query("SELECT lien_externe FROM epreuves LIMIT 1");
        $hasLienExterne = true;
    } catch (\PDOException $e) {
        $hasLienExterne = false;
    }

    $liensCol = $hasLienExterne
        ? "ep.fichier, ep.lien_externe,"
        : "ep.fichier, '' AS lien_externe,";

    $where = [];
    $params = [];

    if ($search) { $where[] = "(ep.matiere LIKE ? OR c.nom LIKE ? OR e.nom LIKE ?)"; $like = "%$search%"; $params = array_merge($params, [$like, $like, $like]); }
    if ($annee)  { $where[] = "ep.annee = ?";      $params[] = $annee; }
    if ($matiere){ $where[] = "ep.matiere LIKE ?"; $params[] = "%$matiere%"; }
    if ($concours_id) { $where[] = "ep.concours_id = ?"; $params[] = $concours_id; }

    $sql = "
        SELECT ep.id, ep.matiere, ep.annee, " . $liensCol . "
               ep.tag_classe, ep.nb_telechargements,
               c.nom AS concours_nom, e.nom AS ecole_nom, e.slug AS ecole_slug
        FROM epreuves ep
        JOIN concours c ON ep.concours_id = c.id
        JOIN ecoles e ON c.ecole_id = e.id
    ";
    if ($where) $sql .= " WHERE " . implode(" AND ", $where);
    $sql .= " ORDER BY ep.annee DESC, ep.matiere ASC";

    try {
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (\PDOException $e) {
        return [];
    }
}

// ── Compter ──
function countTable(string $table): int {
    $db = getDB();
    return (int)$db->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
}
