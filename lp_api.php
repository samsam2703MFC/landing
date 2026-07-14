<?php
/**
 * lp_api.php — REST API léger pour les tables lp_
 * Déposez ce fichier à la racine de votre projet (même serveur que l'ERP).
 *
 * Endpoints :
 *   GET /lp_api.php?r=hero       → slides du carousel hero
 *   GET /lp_api.php?r=seasonal   → éditions saisonnières
 *   GET /lp_api.php?r=collabs    → collaborations
 *   GET /lp_api.php?r=franchise  → textes section franchise
 *   GET /lp_api.php?r=shops      → boutiques + horaires + services
 *   GET /lp_api.php?r=pickers    → webshop picker
 *   GET /lp_api.php?r=all        → tout en une seule requête (recommandé)
 *
 * Sécurité : CORS restreint à votre domaine. Pas d'écriture possible ici.
 */

// ── Config DB ───────────────────────────────────────────────
define('LP_DB_HOST', 'localhost');
define('LP_DB_NAME', 'atelierby_db');
define('LP_DB_USER', 'sam');
define('LP_DB_PASS', 'NhoQyQbKRSPh4Ubg3sR7DMjs5');
define('LP_DB_PORT', 3306);

// ── CORS ─────────────────────────────────────────────────────
$allowed_origins = [
    'https://latelierby.be',
    'https://www.latelierby.be',
    'http://185.180.206.46',
    'http://localhost',
];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowed_origins, true)) {
    header('Access-Control-Allow-Origin: ' . $origin);
}
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=300');   // 5 min cache navigateur

// ── Connexion PDO ────────────────────────────────────────────
try {
    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
        LP_DB_HOST, LP_DB_PORT, LP_DB_NAME);
    $pdo = new PDO($dsn, LP_DB_USER, LP_DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    http_response_code(503);
    echo json_encode(['error' => 'db_unavailable']);
    exit;
}

// ── Routeur ──────────────────────────────────────────────────
$route = $_GET['r'] ?? 'all';

$handlers = [
    'hero'     => 'lp_get_hero',
    'seasonal' => 'lp_get_seasonal',
    'collabs'  => 'lp_get_collabs',
    'franchise'=> 'lp_get_franchise',
    'shops'    => 'lp_get_shops',
    'pickers'  => 'lp_get_pickers',
    'all'      => 'lp_get_all',
];

if (!isset($handlers[$route])) {
    http_response_code(404);
    echo json_encode(['error' => 'unknown_route']);
    exit;
}

echo json_encode($handlers[$route]($pdo), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

// ── Handlers ─────────────────────────────────────────────────

function lp_get_hero(PDO $pdo): array {
    return $pdo->query(
        'SELECT id, position, eyebrow_fr, eyebrow_nl, title_fr, title_nl,
                lede_fr, lede_nl, cta1_text_fr, cta1_text_nl, cta1_url,
                cta2_text_fr, cta2_text_nl, cta2_url, image_path, ws_product_slug
         FROM lp_hero_slides
         WHERE is_active = 1
         ORDER BY position ASC'
    )->fetchAll();
}

function lp_get_seasonal(PDO $pdo): array {
    return $pdo->query(
        'SELECT id, position, tag_fr, tag_nl, name_fr, name_nl,
                desc_fr, desc_nl, image_path, item_url,
                available_from, available_until, ws_product_slug
         FROM lp_seasonal_items
         WHERE is_active = 1
         ORDER BY position ASC'
    )->fetchAll();
}

function lp_get_collabs(PDO $pdo): array {
    return $pdo->query(
        'SELECT id, position, tag_fr, tag_nl, name_fr, name_nl,
                desc_fr, desc_nl, image_path, shop_url
         FROM lp_collaborations
         WHERE is_active = 1
         ORDER BY position ASC'
    )->fetchAll();
}

function lp_get_franchise(PDO $pdo): array {
    $row = $pdo->query(
        'SELECT title_fr, title_nl, lede_fr, lede_nl,
                point1_fr, point1_nl, point2_fr, point2_nl, point3_fr, point3_nl,
                cta_text_fr, cta_text_nl, cta_url
         FROM lp_franchise_section
         LIMIT 1'
    )->fetch();
    return $row ?: [];
}

function lp_get_shops(PDO $pdo): array {
    // boutiques
    $shops = $pdo->query(
        'SELECT id, sort_order, name, city, postal_code, kind,
                address, phone, email, concept_fr, concept_nl,
                image_path, webshop_url
         FROM lp_shops
         WHERE is_active = 1
         ORDER BY sort_order ASC'
    )->fetchAll();

    if (!$shops) return [];

    $ids = array_column($shops, 'id');
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    // horaires
    $stmt = $pdo->prepare(
        "SELECT shop_id, day, hours FROM lp_shop_hours WHERE shop_id IN ($placeholders)"
    );
    $stmt->execute($ids);
    $hours_raw = $stmt->fetchAll();

    // services
    $stmt = $pdo->prepare(
        "SELECT shop_id, service_key FROM lp_shop_services WHERE shop_id IN ($placeholders)"
    );
    $stmt->execute($ids);
    $svc_raw = $stmt->fetchAll();

    // index par shop_id
    $hours_map = $svc_map = [];
    foreach ($hours_raw as $r) $hours_map[$r['shop_id']][$r['day']] = $r['hours'];
    foreach ($svc_raw  as $r) $svc_map[$r['shop_id']][] = $r['service_key'];

    foreach ($shops as &$s) {
        $sid = $s['id'];
        $s['hours'] = $hours_map[$sid] ?? [];
        $s['svc']   = $svc_map[$sid]  ?? [];
        unset($s['id'], $s['sort_order']); // pas besoin côté JS
    }
    unset($s);

    return $shops;
}

function lp_get_pickers(PDO $pdo): array {
    $rows = $pdo->query(
        'SELECT picker_key AS `key`, name, zone, lat, lng, shop_url AS shop
         FROM lp_webshop_pickers
         WHERE is_active = 1
         ORDER BY sort_order ASC'
    )->fetchAll();

    // lat/lng doivent être des floats, pas des strings
    foreach ($rows as &$r) {
        $r['lat'] = (float) $r['lat'];
        $r['lng'] = (float) $r['lng'];
    }
    unset($r);
    return $rows;
}

function lp_get_all(PDO $pdo): array {
    return [
        'hero'     => lp_get_hero($pdo),
        'seasonal' => lp_get_seasonal($pdo),
        'collabs'  => lp_get_collabs($pdo),
        'franchise'=> lp_get_franchise($pdo),
        'shops'    => lp_get_shops($pdo),
        'pickers'  => lp_get_pickers($pdo),
    ];
}
