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
    'families' => 'lp_get_families',
    'franchise_page' => 'lp_get_franchise_page',
    'legal'    => 'lp_get_legal',
    'footer'   => 'lp_get_footer',
    'nav'      => 'lp_get_nav',
    'i18n'     => 'lp_get_i18n',
    'params'   => 'lp_get_params',
    'app'      => 'lp_get_app',
    'services' => 'lp_get_services',
    'sections' => 'lp_get_sections',
    'od'       => 'lp_get_od',
    'od_zones' => 'lp_get_od_zones',
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
        'SELECT id, sort_order, name, city,
                zip          AS cp,
                kind,
                address_line AS addr,
                phone        AS tel,
                email        AS mail,
                concept_fr   AS concept,
                concept_nl   AS conceptNl,
                image_path   AS illus,
                webshop_url
         FROM shops
         WHERE active = 1
         ORDER BY sort_order ASC'
    )->fetchAll();

    if (!$shops) return [];

    $ids = array_column($shops, 'id');
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    // horaires
    $stmt = $pdo->prepare(
        "SELECT shop_id, day, hours FROM shop_hours WHERE shop_id IN ($placeholders)"
    );
    $stmt->execute($ids);
    $hours_raw = $stmt->fetchAll();

    // services
    $stmt = $pdo->prepare(
        "SELECT shop_id, service_key FROM shop_services WHERE shop_id IN ($placeholders)"
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
    // Picker = boutiques réelles dont le webshop est actif ; étiquette = ville
    $rows = $pdo->query(
        'SELECT CAST(id AS CHAR) AS `key`, city, name, zone, lat, lng, webshop_url AS shop
         FROM shops
         WHERE webshop_enabled = 1
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

function lp_get_families(PDO $pdo): array {
    return $pdo->query(
        'SELECT position, name_fr, name_nl, count_fr, count_nl, image_path, href
         FROM lp_product_families
         WHERE is_active = 1
         ORDER BY position ASC'
    )->fetchAll();
}

function lp_get_franchise_page(PDO $pdo): array {
    // Textes
    $rows = $pdo->query(
        'SELECT i18n_key, value_fr, value_nl FROM lp_franchise_i18n'
    )->fetchAll();
    $fr = $nl = [];
    foreach ($rows as $r) {
        $fr[$r['i18n_key']] = $r['value_fr'];
        $nl[$r['i18n_key']] = $r['value_nl'];
    }

    // Zones : { fr: [ [groupLabel, [[value,label],...]], ... ], nl: [...] }
    $zrows = $pdo->query(
        'SELECT lang, group_label, value, label
         FROM lp_franchise_zones
         WHERE is_active = 1
         ORDER BY lang ASC, group_pos ASC, pos ASC'
    )->fetchAll();

    $zbuf = ['fr' => [], 'nl' => []];   // lang => [groupLabel => [[value,label],...]]
    foreach ($zrows as $z) {
        $lg = $z['lang'];
        $zbuf[$lg][$z['group_label']][] = [$z['value'], $z['label']];
    }
    $zones = ['fr' => [], 'nl' => []];
    foreach ($zbuf as $lg => $groups) {
        foreach ($groups as $label => $opts) {
            $zones[$lg][] = [$label, $opts];
        }
    }

    return ['i18n' => ['fr' => $fr, 'nl' => $nl], 'zones' => $zones];
}

function lp_get_legal(PDO $pdo): array {
    $row = $pdo->query('SELECT * FROM lp_legal ORDER BY id ASC LIMIT 1')->fetch();
    if (!$row) return [];
    unset($row['id'], $row['updated_at']);
    return $row;
}

function lp_get_footer(PDO $pdo): array {
    $rows = $pdo->query(
        'SELECT col, position, label_fr, label_nl, url
         FROM lp_footer_links
         WHERE is_active = 1
         ORDER BY col ASC, position ASC'
    )->fetchAll();
    $out = [];
    foreach ($rows as $r) {
        $out[(string)$r['col']][] = $r;
    }
    return $out;
}

function lp_get_nav(PDO $pdo): array {
    return $pdo->query(
        'SELECT position, label_fr, label_nl, url, icon, hex_color
         FROM lp_nav_items
         WHERE is_active = 1
         ORDER BY position ASC'
    )->fetchAll();
}

function lp_get_i18n(PDO $pdo): array {
    $rows = $pdo->query(
        'SELECT i18n_key, value_fr, value_nl FROM lp_i18n'
    )->fetchAll();
    $fr = $nl = [];
    foreach ($rows as $r) {
        $fr[$r['i18n_key']] = $r['value_fr'];
        $nl[$r['i18n_key']] = $r['value_nl'];
    }
    return ['fr' => $fr, 'nl' => $nl];
}

function lp_get_params(PDO $pdo): array {
    $rows = $pdo->query('SELECT param_key, param_value FROM lp_params')->fetchAll();
    $out = [];
    foreach ($rows as $r) $out[$r['param_key']] = $r['param_value'];
    return $out;
}

function lp_get_app(PDO $pdo): array {
    $section = $pdo->query(
        'SELECT title_fr, title_nl, lede_fr, lede_nl,
                point1_fr, point1_nl, point2_fr, point2_nl, point3_fr, point3_nl,
                cta_text_fr, cta_text_nl, hint_fr, hint_nl
         FROM lp_app_section LIMIT 1'
    )->fetch();
    if (!$section) return [];

    $app_url = $pdo->query(
        "SELECT param_value FROM lp_params WHERE param_key = 'app_url'"
    )->fetchColumn();

    $section['app_url'] = $app_url ?: 'https://latelierby.be/app';
    return $section;
}

function lp_get_services(PDO $pdo): array {
    return $pdo->query(
        'SELECT position, name_fr, name_nl, desc_fr, desc_nl, icon_svg, image_path, url, theme
         FROM lp_services
         WHERE is_active = 1
         ORDER BY position ASC'
    )->fetchAll();
}

function lp_get_sections(PDO $pdo): array {
    $rows = $pdo->query(
        'SELECT section_key, eyebrow_fr, eyebrow_nl, title_fr, title_nl, lede_fr, lede_nl
         FROM lp_sections'
    )->fetchAll();
    $out = [];
    foreach ($rows as $r) {
        $out[$r['section_key']] = $r;
    }
    return $out;
}

/**
 * Office Delivery (Livraison Bureau) — pilote livraison-bureau.html.
 * Renvoie tout le contenu des tables lp_od_* + le footer partagé
 * (lp_footer_links + lp_i18n ft.*) pour harmonisation avec le site.
 * Chaque sous-requête est isolée : une table lp_od_* manquante n'empêche
 * pas le reste (la page garde de toute façon son fallback codé en dur).
 */
function lp_get_od(PDO $pdo): array {
    $q = function (string $sql) use ($pdo): array {
        try { return $pdo->query($sql)->fetchAll(); }
        catch (PDOException $e) { return []; }
    };

    // i18n scalaires → { fr:{key:val}, nl:{key:val} }
    $i18n = ['fr' => [], 'nl' => []];
    foreach ($q('SELECT i18n_key, value_fr, value_nl FROM lp_od_i18n') as $r) {
        $i18n['fr'][$r['i18n_key']] = $r['value_fr'];
        $i18n['nl'][$r['i18n_key']] = $r['value_nl'];
    }

    // listes simples → { list_key: [ {fr,nl}, ... ] }
    $lists = [];
    foreach ($q('SELECT list_key, value_fr, value_nl FROM lp_od_list WHERE is_active = 1 ORDER BY list_key ASC, position ASC') as $r) {
        $lists[$r['list_key']][] = ['fr' => $r['value_fr'], 'nl' => $r['value_nl']];
    }

    // shops (sid=0 = système)
    $shops = [];
    foreach ($q('SELECT sid, name, city, email, is_system FROM lp_od_shops WHERE is_active = 1 ORDER BY sid ASC') as $r) {
        $shops[] = [
            'id' => (int) $r['sid'], 'name' => $r['name'], 'city' => $r['city'],
            'email' => $r['email'], 'is_system' => ((int) $r['is_system'] === 1),
        ];
    }

    // zones (days/slots éclatés en tableaux)
    $zones = [];
    foreach ($q('SELECT id, shop_id, region, zone_name, city, cutoff_time, days, slots, min_qty, note_fr, note_nl, priority FROM lp_od_zones WHERE is_active = 1 ORDER BY region ASC, priority ASC') as $r) {
        $zones[] = [
            'id' => (int) $r['id'], 'shop_id' => (int) $r['shop_id'], 'region' => $r['region'],
            'zone_name' => $r['zone_name'], 'city' => $r['city'], 'cutoff_time' => $r['cutoff_time'],
            'days'  => ($r['days']  !== '' ? explode(',', $r['days'])  : []),
            'slots' => ($r['slots'] !== '' ? explode('|', $r['slots']) : []),
            'min' => (int) $r['min_qty'],
            'note_fr' => $r['note_fr'], 'note_nl' => $r['note_nl'],
            'priority' => (int) $r['priority'],
        ];
    }

    $usecases = $q('SELECT name_fr, name_nl, desc_fr, desc_nl, icon_path FROM lp_od_usecases WHERE is_active = 1 ORDER BY position ASC');
    $steps    = $q('SELECT title_fr, title_nl, desc_fr, desc_nl FROM lp_od_steps WHERE is_active = 1 ORDER BY position ASC');
    $rollout  = $q('SELECT title_fr, title_nl, desc_fr, desc_nl FROM lp_od_rollout WHERE is_active = 1 ORDER BY position ASC');

    // offers (includes éclatés)
    $offers = [];
    foreach ($q('SELECT name_fr, name_nl, desc_fr, desc_nl, icon_path, includes_fr, includes_nl, audience_fr, audience_nl, setup_fr, setup_nl, is_recurring FROM lp_od_offers WHERE is_active = 1 ORDER BY position ASC') as $r) {
        $r['includes_fr'] = ($r['includes_fr'] !== '' ? explode('|', $r['includes_fr']) : []);
        $r['includes_nl'] = ($r['includes_nl'] !== '' ? explode('|', $r['includes_nl']) : []);
        $r['is_recurring'] = ((int) $r['is_recurring'] === 1);
        $offers[] = $r;
    }

    $products     = $q('SELECT title_fr, title_nl, note_fr, note_nl, image_path FROM lp_od_products WHERE is_active = 1 ORDER BY position ASC');
    $specs        = $q('SELECT value, label_fr, label_nl FROM lp_od_specs WHERE is_active = 1 ORDER BY position ASC');
    $testimonials = $q('SELECT quote_fr, quote_nl, author, company_fr, company_nl, initial FROM lp_od_testimonials WHERE is_active = 1 ORDER BY position ASC');

    $faqs = [];
    foreach ($q('SELECT question_fr, question_nl, answer_fr, answer_nl, zone_link FROM lp_od_faqs WHERE is_active = 1 ORDER BY position ASC') as $r) {
        $r['zone_link'] = ((int) $r['zone_link'] === 1);
        $faqs[] = $r;
    }

    $fields = [];
    foreach ($q('SELECT field_key, field_type, col_span, required, depends FROM lp_od_form_fields WHERE is_active = 1 ORDER BY position ASC') as $r) {
        $fields[] = [
            'key' => $r['field_key'], 'type' => $r['field_type'],
            'span' => (int) $r['col_span'], 'required' => ((int) $r['required'] === 1),
            'depends' => $r['depends'],
        ];
    }

    // Footer partagé avec le reste du site (harmonisation)
    $footer = [];
    try { $footer = lp_get_footer($pdo); } catch (PDOException $e) { $footer = []; }
    $footer_i18n = ['fr' => [], 'nl' => []];
    try { $footer_i18n = lp_get_i18n($pdo); } catch (PDOException $e) {}

    return [
        'i18n' => $i18n, 'lists' => $lists, 'shops' => $shops, 'zones' => $zones,
        'usecases' => $usecases, 'steps' => $steps, 'rollout' => $rollout,
        'offers' => $offers, 'products' => $products, 'specs' => $specs,
        'testimonials' => $testimonials, 'faqs' => $faqs, 'form_fields' => $fields,
        'footer' => $footer, 'footer_i18n' => $footer_i18n,
    ];
}

/**
 * Zones de livraison RÉELLES (livraison-bureau) — pilotées par l'ERP.
 *
 * Hiérarchie :
 *   ws_franchisor_catchment (zone de chalandise)  =  le GROUPE du menu déroulant
 *     └── ws_tours (tournées)                       =  les OPTIONS sélectionnables
 *           └── ws_tour_availability                =  jours / heure limite / créneau
 *
 * Le lien tournée ↔ chalandise se fait par shop_id (chaque boutique a sa
 * chalandise et ses tournées). Une boutique doit être active + landing_enabled.
 * Renvoie { shops:[...], zones:[...] } dans la forme attendue par le JSX ;
 * en cas d'absence de table / d'erreur, renvoie des tableaux vides et la page
 * garde son contenu de secours.
 */
function lp_get_od_zones(PDO $pdo): array {
    $q = function (string $sql) use ($pdo): array {
        try { return $pdo->query($sql)->fetchAll(); }
        catch (PDOException $e) { return []; }
    };

    // Tournées rattachées à une boutique affichée sur la landing.
    // Le groupe = la chalandise (catchment) de la même boutique.
    $tours = $q(
        "SELECT t.id AS tour_id, t.shop_id, TRIM(t.name) AS tour_name, t.zone_secondary,
                s.name AS shop_name, s.city AS shop_city, s.email AS shop_email,
                (SELECT c.name FROM ws_franchisor_catchment c
                   WHERE c.shop_id = t.shop_id AND c.active = 1
                   ORDER BY c.id ASC LIMIT 1) AS catchment_name
         FROM ws_tours t
         JOIN shops s ON s.id = t.shop_id AND s.active = 1 AND s.landing_enabled = 1
         WHERE t.active = 1
         ORDER BY s.sort_order ASC, t.id ASC"
    );
    if (!$tours) return ['shops' => [], 'zones' => []];

    // Disponibilités (jours / heure limite / créneau) indexées par tournée.
    $avail = [];
    foreach ($q("SELECT tour_id, delivery_day, delivery_start, delivery_end, cutoff_time
                 FROM ws_tour_availability WHERE active = 1
                 ORDER BY tour_id ASC, delivery_day ASC") as $r) {
        $avail[(int) $r['tour_id']][] = $r;
    }

    $dayCode = [1 => 'lun', 2 => 'mar', 3 => 'mer', 4 => 'jeu', 5 => 'ven', 6 => 'sam', 7 => 'dim'];
    $hm = function ($t) { return ($t !== null && $t !== '') ? substr((string) $t, 0, 5) : ''; };

    $shopsMap = [];
    $zones = [];
    foreach ($tours as $t) {
        $tid  = (int) $t['tour_id'];
        $sid  = (int) $t['shop_id'];
        $rows = $avail[$tid] ?? [];

        $days = $slots = [];
        $cutoff = '';
        foreach ($rows as $a) {
            $d = (int) $a['delivery_day'];
            if (isset($dayCode[$d]) && !in_array($dayCode[$d], $days, true)) $days[] = $dayCode[$d];
            $slot = $hm($a['delivery_start']) . ' – ' . $hm($a['delivery_end']);
            if (trim($slot, ' –') !== '' && !in_array($slot, $slots, true)) $slots[] = $slot;
            if ($cutoff === '' && $hm($a['cutoff_time']) !== '') $cutoff = $hm($a['cutoff_time']);
        }

        $group = $t['catchment_name'] ?: ($t['shop_city'] ?: 'Autres zones');
        $note  = ($t['zone_secondary'] !== null && trim((string) $t['zone_secondary']) !== '')
               ? ('Communes desservies : ' . $t['zone_secondary']) : '';

        $zones[] = [
            'id'          => $tid,
            'shop_id'     => $sid,
            'group'       => $group,                                   // libellé de chalandise
            'region'      => '',
            'zone_name'   => $t['tour_name'] !== '' ? $t['tour_name'] : ('Tournée ' . $tid),
            'city'        => $t['shop_city'],
            'cutoff_time' => $cutoff,
            'days'        => $days,
            'slots'       => $slots,
            'min'         => null,                                     // pas de source fiable en base
            'note_fr'     => $note,
            'note_nl'     => $note,
            'priority'    => $tid,
            'is_active'   => true,
        ];

        if (!isset($shopsMap[$sid])) {
            $shopsMap[$sid] = [
                'id' => $sid, 'name' => $t['shop_name'], 'city' => $t['shop_city'],
                'email' => $t['shop_email'], 'is_system' => false,
            ];
        }
    }

    return ['shops' => array_values($shopsMap), 'zones' => $zones];
}

function lp_get_all(PDO $pdo): array {
    // Chaque handler est isolé : une table manquante ne casse pas tout l'API.
    $keys = [
        'hero'     => 'lp_get_hero',
        'seasonal' => 'lp_get_seasonal',
        'collabs'  => 'lp_get_collabs',
        'franchise'=> 'lp_get_franchise',
        'shops'    => 'lp_get_shops',
        'pickers'  => 'lp_get_pickers',
        'families' => 'lp_get_families',
        'footer'   => 'lp_get_footer',
        'nav'      => 'lp_get_nav',
        'i18n'     => 'lp_get_i18n',
        'params'   => 'lp_get_params',
        'app'      => 'lp_get_app',
        'services' => 'lp_get_services',
        'sections' => 'lp_get_sections',
    ];
    $out = [];
    foreach ($keys as $key => $fn) {
        try { $out[$key] = $fn($pdo); }
        catch (PDOException $e) { $out[$key] = ($key === 'franchise' || $key === 'app') ? [] : []; }
    }
    return $out;
}
