<?php
/**
 * lp_diag_db.php — Affiche les image_path stockés dans chaque table lp_
 * SUPPRIMER ce fichier après diagnostic.
 */
define('LP_DB_HOST', 'localhost');
define('LP_DB_NAME', 'atelierby_db');
define('LP_DB_USER', 'sam');
define('LP_DB_PASS', 'NhoQyQbKRSPh4Ubg3sR7DMjs5');

$pdo = new PDO("mysql:host=".LP_DB_HOST.";dbname=".LP_DB_NAME.";charset=utf8mb4",
    LP_DB_USER, LP_DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$base = dirname(__FILE__);

function check_img(string $path, string $base): string {
    if (!$path) return '(vide)';
    return file_exists($base.'/'.$path) ? "✓ $path" : "✗ ABSENT: $path";
}

echo '<pre style="font-family:monospace;font-size:13px">';

$tables = [
    ['lp_hero_slides',      'SELECT id, is_active, image_path FROM lp_hero_slides ORDER BY position'],
    ['lp_product_families', 'SELECT id, is_active, image_path FROM lp_product_families ORDER BY position'],
    ['lp_seasonal_items',   'SELECT id, is_active, image_path FROM lp_seasonal_items ORDER BY position'],
    ['lp_collaborations',   'SELECT id, is_active, image_path FROM lp_collaborations ORDER BY position'],
];

foreach ($tables as [$name, $sql]) {
    echo "\n=== $name ===\n";
    try {
        $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        if (!$rows) { echo "  (vide)\n"; continue; }
        foreach ($rows as $r) {
            $active = $r['is_active'] ? 'actif' : 'INACTIF';
            $img    = check_img($r['image_path'], $base);
            echo "  id={$r['id']} [{$active}]  $img\n";
        }
    } catch (PDOException $e) {
        echo "  TABLE INEXISTANTE\n";
    }
}

echo '</pre>';
