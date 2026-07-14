<?php
/**
 * lp_fix_urls.php — Diagnostique + corrige les URLs dans lp_params
 * SUPPRIMER ce fichier après exécution.
 */
define('LP_DB_HOST', 'localhost');
define('LP_DB_NAME', 'atelierby_db');
define('LP_DB_USER', 'sam');
define('LP_DB_PASS', 'NhoQyQbKRSPh4Ubg3sR7DMjs5');

$pdo = new PDO("mysql:host=".LP_DB_HOST.";dbname=".LP_DB_NAME.";charset=utf8mb4",
    LP_DB_USER, LP_DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

echo '<pre style="font-family:monospace;font-size:13px">';

echo "=== AVANT ===\n";
foreach ($pdo->query("SELECT param_key, param_value FROM lp_params ORDER BY param_key")->fetchAll(PDO::FETCH_ASSOC) as $r) {
    echo "  {$r['param_key']} = {$r['param_value']}\n";
}

// Corriger franchise_url → franchise-lead.html (relatif, site sous /landing/)
$pdo->prepare("UPDATE lp_params SET param_value = 'franchise-lead.html' WHERE param_key = 'franchise_url'")->execute();

// S'assurer que la clé existe si absente
$pdo->prepare("INSERT IGNORE INTO lp_params (param_key, param_value) VALUES ('franchise_url', 'franchise-lead.html')")->execute();

echo "\n=== APRÈS ===\n";
foreach ($pdo->query("SELECT param_key, param_value FROM lp_params ORDER BY param_key")->fetchAll(PDO::FETCH_ASSOC) as $r) {
    echo "  {$r['param_key']} = {$r['param_value']}\n";
}

echo "\n✓ franchise_url corrigé vers 'franchise-lead.html'.\n";
echo "  (galette_url reste tel quel — page galette-des-rois.html non créée, à traiter séparément.)\n";
echo "Supprimez ce fichier.\n";
echo '</pre>';
