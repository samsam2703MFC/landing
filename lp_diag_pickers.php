<?php
/**
 * lp_diag_pickers.php — Vérifie l'état des pickers après fusion
 * SUPPRIMER après diagnostic.
 */
define('LP_DB_HOST', 'localhost');
define('LP_DB_NAME', 'atelierby_db');
define('LP_DB_USER', 'sam');
define('LP_DB_PASS', 'NhoQyQbKRSPh4Ubg3sR7DMjs5');

header('Content-Type: text/plain; charset=utf-8');
$pdo = new PDO("mysql:host=".LP_DB_HOST.";dbname=".LP_DB_NAME.";charset=utf8mb4",
    LP_DB_USER, LP_DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

echo "=== Colonnes de lp_shops ===\n";
$cols = $pdo->query("SHOW COLUMNS FROM lp_shops")->fetchAll(PDO::FETCH_COLUMN);
echo implode(', ', $cols) . "\n";
$hasPicker = in_array('show_in_picker', $cols, true);
echo "\nshow_in_picker présente ? " . ($hasPicker ? "OUI" : "NON — la migration n'a pas tourné !") . "\n";

if ($hasPicker) {
    echo "\n=== Lignes show_in_picker = 1 ===\n";
    $rows = $pdo->query("SELECT id, name, zone, lat, lng, webshop_url, is_active, show_in_picker FROM lp_shops WHERE show_in_picker = 1 ORDER BY sort_order")->fetchAll(PDO::FETCH_ASSOC);
    if (!$rows) {
        echo "  AUCUNE ligne — les pickers n'ont pas été copiés.\n";
    } else {
        foreach ($rows as $r) {
            echo "  id={$r['id']} · {$r['name']} · {$r['zone']} · {$r['lat']},{$r['lng']} · {$r['webshop_url']}\n";
        }
    }
}

echo "\n=== Ce que l'API renvoie (r=pickers) ===\n";
try {
    $rows = $pdo->query(
        'SELECT picker_key AS `key`, name, zone, lat, lng, webshop_url AS shop
         FROM lp_shops WHERE show_in_picker = 1 ORDER BY sort_order ASC'
    )->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as &$r) { $r['lat'] = (float)$r['lat']; $r['lng'] = (float)$r['lng']; }
    unset($r);
    echo json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . "\n";
} catch (PDOException $e) {
    echo "  ✗ ERREUR SQL : " . $e->getMessage() . "\n";
}

echo "\n=== Test r=all (est-ce que tout l'API fonctionne ?) ===\n";
try {
    $pdo->query('SELECT picker_key, zone, lat, lng, show_in_picker FROM lp_shops LIMIT 1')->fetch();
    echo "  ✓ Les colonnes picker existent, r=all ne devrait pas planter.\n";
} catch (PDOException $e) {
    echo "  ✗ r=all PLANTE car : " . $e->getMessage() . "\n";
    echo "  → Toute la page retombe sur les valeurs codées en dur.\n";
}
