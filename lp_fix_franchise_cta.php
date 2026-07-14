<?php
/**
 * lp_fix_franchise_cta.php — Diagnostique + corrige lp_franchise_section.cta_url
 * Le bouton "Recevoir le dossier" lit son lien depuis cette colonne.
 * SUPPRIMER ce fichier après exécution.
 */
define('LP_DB_HOST', 'localhost');
define('LP_DB_NAME', 'atelierby_db');
define('LP_DB_USER', 'sam');
define('LP_DB_PASS', 'NhoQyQbKRSPh4Ubg3sR7DMjs5');

$pdo = new PDO("mysql:host=".LP_DB_HOST.";dbname=".LP_DB_NAME.";charset=utf8mb4",
    LP_DB_USER, LP_DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

echo '<pre style="font-family:monospace;font-size:13px">';

echo "=== AVANT — lp_franchise_section ===\n";
$row = $pdo->query("SELECT cta_text_fr, cta_text_nl, cta_url FROM lp_franchise_section LIMIT 1")->fetch(PDO::FETCH_ASSOC);
if ($row) {
    foreach ($row as $k => $v) echo "  $k = $v\n";
} else {
    echo "  (table vide !)\n";
}

// Corriger cta_url → franchise-lead.html
$pdo->exec("UPDATE lp_franchise_section SET cta_url = 'franchise-lead.html'");

echo "\n=== APRÈS ===\n";
$row = $pdo->query("SELECT cta_text_fr, cta_text_nl, cta_url FROM lp_franchise_section LIMIT 1")->fetch(PDO::FETCH_ASSOC);
foreach ($row as $k => $v) echo "  $k = $v\n";

echo "\n✓ cta_url corrigé vers 'franchise-lead.html'.\n";
echo "Le bouton 'Recevoir le dossier' pointe maintenant vers /landing/franchise-lead.html.\n";
echo "⚠ Rechargez l'accueil avec Ctrl+Shift+R (cache API de 5 min).\n";
echo "Supprimez ce fichier.\n";
echo '</pre>';
