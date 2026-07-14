<?php
/**
 * lp_migrate_legal.php — Ajoute les clés ft.legal1/2/3 dans lp_i18n
 * SUPPRIMER ce fichier après exécution.
 */
define('LP_DB_HOST', 'localhost');
define('LP_DB_NAME', 'atelierby_db');
define('LP_DB_USER', 'sam');
define('LP_DB_PASS', 'NhoQyQbKRSPh4Ubg3sR7DMjs5');

try {
    $pdo = new PDO("mysql:host=".LP_DB_HOST.";dbname=".LP_DB_NAME.";charset=utf8mb4",
        LP_DB_USER, LP_DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    $stmt = $pdo->prepare(
        'INSERT IGNORE INTO lp_i18n (i18n_key, value_fr, value_nl) VALUES (:k, :fr, :nl)'
    );
    $rows = [
        ['ft.legal1', 'Mentions légales', 'Wettelijke vermeldingen'],
        ['ft.legal2', 'Confidentialité',  'Privacy'],
        ['ft.legal3', 'Conditions',       'Voorwaarden'],
    ];
    foreach ($rows as $r) {
        $stmt->execute([':k' => $r[0], ':fr' => $r[1], ':nl' => $r[2]]);
    }

    echo '<p style="font-family:monospace;color:green">✓ Clés ft.legal1/2/3 ajoutées dans lp_i18n. Supprimez ce fichier.</p>';
} catch (PDOException $e) {
    echo '<p style="font-family:monospace;color:red">✗ ' . htmlspecialchars($e->getMessage()) . '</p>';
}
