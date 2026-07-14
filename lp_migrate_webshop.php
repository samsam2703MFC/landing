<?php
/**
 * lp_migrate_webshop.php — Picker basé sur les vraies boutiques
 *   1) renomme show_in_picker → webshop_active
 *   2) supprime les 6 pickers fictifs (halle, wavre, …)
 *   3) active webshop_active sur les vraies boutiques (is_active = 1)
 * SUPPRIMER après exécution.
 */
define('LP_DB_HOST', 'localhost');
define('LP_DB_NAME', 'atelierby_db');
define('LP_DB_USER', 'sam');
define('LP_DB_PASS', 'NhoQyQbKRSPh4Ubg3sR7DMjs5');

header('Content-Type: text/plain; charset=utf-8');
$pdo = new PDO("mysql:host=".LP_DB_HOST.";dbname=".LP_DB_NAME.";charset=utf8mb4",
    LP_DB_USER, LP_DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

// 1) Renommer la colonne
$cols = $pdo->query("SHOW COLUMNS FROM lp_shops")->fetchAll(PDO::FETCH_COLUMN);
if (in_array('show_in_picker', $cols, true) && !in_array('webshop_active', $cols, true)) {
    $pdo->exec("ALTER TABLE lp_shops CHANGE show_in_picker webshop_active TINYINT(1) NOT NULL DEFAULT 0");
    echo "✓ Colonne show_in_picker → webshop_active\n";
} elseif (in_array('webshop_active', $cols, true)) {
    echo "~ webshop_active existe déjà\n";
} else {
    $pdo->exec("ALTER TABLE lp_shops ADD COLUMN webshop_active TINYINT(1) NOT NULL DEFAULT 0");
    echo "✓ Colonne webshop_active ajoutée\n";
}

// 2) Supprimer les pickers fictifs
$n = $pdo->exec("DELETE FROM lp_shops WHERE picker_key IN ('halle','wavre','corbais','gembloux','sombreffe','gosselies')");
echo "✓ $n picker(s) fictif(s) supprimé(s)\n";

// 3) Activer le webshop sur les vraies boutiques
$n = $pdo->exec("UPDATE lp_shops SET webshop_active = 1 WHERE is_active = 1");
echo "✓ webshop_active = 1 sur $n boutique(s) réelle(s)\n";

// Vérification
echo "\n=== Boutiques dans le picker (webshop_active = 1) ===\n";
foreach ($pdo->query("SELECT id, city, name, lat, lng, webshop_url FROM lp_shops WHERE webshop_active = 1 ORDER BY sort_order") as $r) {
    echo "  id={$r['id']} · {$r['city']} · {$r['name']} · coords {$r['lat']},{$r['lng']} · " . ($r['webshop_url'] ?: '(webshop_url vide → à remplir)') . "\n";
}

echo "\n✓ Terminé.\n";
echo "→ Étiquette du picker = ville (city).\n";
echo "→ Pour chaque boutique : remplissez webshop_url (lien) + lat/lng (géoloc) dans PHPMyAdmin.\n";
echo "→ webshop_active = 0 retire une boutique du picker.\n";
echo "Supprimez ce fichier.\n";
