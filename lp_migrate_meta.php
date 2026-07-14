<?php
/**
 * lp_migrate_meta.php — Ajoute la clé doc.description dans lp_i18n (SEO)
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
    $stmt->execute([
        ':k'  => 'doc.description',
        ':fr' => "L'Atelier By — Maison de pains et viennoiseries artisanales en Belgique. Fait main, chaque matin.",
        ':nl' => "L'Atelier By — Huis van ambachtelijk brood en viennoiserie in België. Met de hand gemaakt, elke ochtend.",
    ]);

    echo '<p style="font-family:monospace;color:green">✓ Clé doc.description ajoutée dans lp_i18n. Supprimez ce fichier.</p>';
} catch (PDOException $e) {
    echo '<p style="font-family:monospace;color:red">✗ ' . htmlspecialchars($e->getMessage()) . '</p>';
}
