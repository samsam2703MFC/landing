<?php
/**
 * lp_fix_paths.php — Corrige les image_path et supprime les doublons
 * SUPPRIMER ce fichier après exécution.
 */
define('LP_DB_HOST', 'localhost');
define('LP_DB_NAME', 'atelierby_db');
define('LP_DB_USER', 'sam');
define('LP_DB_PASS', 'NhoQyQbKRSPh4Ubg3sR7DMjs5');

$pdo = new PDO("mysql:host=".LP_DB_HOST.";dbname=".LP_DB_NAME.";charset=utf8mb4",
    LP_DB_USER, LP_DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

echo '<pre style="font-family:monospace;font-size:13px">';

// 1. Corriger /assets/img/ → img/ dans toutes les tables
$tables = ['lp_hero_slides', 'lp_seasonal_items', 'lp_collaborations', 'lp_product_families'];
foreach ($tables as $t) {
    try {
        $n = $pdo->exec("UPDATE $t SET image_path = REPLACE(image_path, '/assets/img/', 'img/') WHERE image_path LIKE '/assets/img/%'");
        echo "✓ $t : $n ligne(s) corrigée(s)\n";
    } catch (PDOException $e) {
        echo "✗ $t : " . $e->getMessage() . "\n";
    }
}

// 2. Supprimer les doublons dans lp_hero_slides (garder le plus petit id par position)
echo "\n--- Doublons lp_hero_slides ---\n";
try {
    $deleted = $pdo->exec("
        DELETE h1 FROM lp_hero_slides h1
        INNER JOIN lp_hero_slides h2
          ON h1.position = h2.position AND h1.id > h2.id
    ");
    echo "✓ $deleted doublon(s) supprimé(s)\n";
    $count = $pdo->query('SELECT COUNT(*) FROM lp_hero_slides')->fetchColumn();
    echo "  → $count ligne(s) restante(s)\n";
} catch (PDOException $e) {
    echo "✗ " . $e->getMessage() . "\n";
}

// 3. Doublons lp_seasonal_items (garder le plus petit id par name_fr)
echo "\n--- Doublons lp_seasonal_items ---\n";
try {
    $deleted = $pdo->exec("
        DELETE s1 FROM lp_seasonal_items s1
        INNER JOIN lp_seasonal_items s2
          ON s1.name_fr = s2.name_fr AND s1.id > s2.id
    ");
    echo "✓ $deleted doublon(s) supprimé(s)\n";
} catch (PDOException $e) {
    echo "✗ " . $e->getMessage() . "\n";
}

// 4. Tout activer dans seasonal et collaborations
echo "\n--- Activation ---\n";
$n = $pdo->exec("UPDATE lp_seasonal_items SET is_active = 1");
echo "✓ lp_seasonal_items : $n ligne(s) activée(s)\n";

$n = $pdo->exec("UPDATE lp_collaborations SET is_active = 1");
echo "✓ lp_collaborations : $n ligne(s) activée(s)\n";

// 5. Vérification finale
echo "\n--- Vérification finale ---\n";
$checks = [
    'lp_hero_slides'      => 'SELECT id, position, is_active, image_path FROM lp_hero_slides ORDER BY position',
    'lp_seasonal_items'   => 'SELECT id, is_active, image_path FROM lp_seasonal_items ORDER BY position',
    'lp_collaborations'   => 'SELECT id, is_active, image_path FROM lp_collaborations ORDER BY position',
];
$base = __DIR__;
foreach ($checks as $name => $sql) {
    echo "\n$name :\n";
    foreach ($pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $ok = file_exists($base.'/'.$r['image_path']) ? '✓' : '✗';
        $active = $r['is_active'] ? 'actif' : 'INACTIF';
        echo "  id={$r['id']} [{$active}]  $ok {$r['image_path']}\n";
    }
}

echo "\nTerminé. Supprimez ce fichier.\n";
echo '</pre>';
