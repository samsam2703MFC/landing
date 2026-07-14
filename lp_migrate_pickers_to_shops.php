<?php
/**
 * lp_migrate_pickers_to_shops.php — Fusionne lp_webshop_pickers dans lp_shops
 *   1) ajoute les colonnes picker à lp_shops
 *   2) copie les lignes de lp_webshop_pickers en tant que lignes lp_shops (show_in_picker=1, is_active=0)
 *   3) archive lp_webshop_pickers → lp_webshop_pickers_bak
 * SUPPRIMER ce fichier après exécution.
 */
define('LP_DB_HOST', 'localhost');
define('LP_DB_NAME', 'atelierby_db');
define('LP_DB_USER', 'sam');
define('LP_DB_PASS', 'NhoQyQbKRSPh4Ubg3sR7DMjs5');

$pdo = new PDO("mysql:host=".LP_DB_HOST.";dbname=".LP_DB_NAME.";charset=utf8mb4",
    LP_DB_USER, LP_DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

echo '<pre style="font-family:monospace;font-size:13px">';

/* 1) Colonnes picker sur lp_shops */
$cols = [
    "picker_key    VARCHAR(40)  NOT NULL DEFAULT ''",
    "zone          VARCHAR(120) NOT NULL DEFAULT ''",
    "lat           DECIMAL(9,6) NOT NULL DEFAULT 0",
    "lng           DECIMAL(9,6) NOT NULL DEFAULT 0",
    "show_in_picker TINYINT(1)  NOT NULL DEFAULT 0",
];
echo "=== 1) Colonnes picker sur lp_shops ===\n";
foreach ($cols as $c) {
    $name = strtok($c, ' ');
    try { $pdo->exec("ALTER TABLE lp_shops ADD COLUMN $c"); echo "  ✓ ajoutée : $name\n"; }
    catch (PDOException $e) { echo "  ~ déjà présente : $name\n"; }
}

/* 2) Copie des pickers */
echo "\n=== 2) Copie des pickers ===\n";
$already = (int)$pdo->query("SELECT COUNT(*) FROM lp_shops WHERE show_in_picker = 1")->fetchColumn();
if ($already) {
    echo "  ~ $already picker(s) déjà présent(s) dans lp_shops — copie ignorée.\n";
} else {
    try {
        $pickers = $pdo->query("SELECT * FROM lp_webshop_pickers ORDER BY sort_order ASC")->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $pickers = [];
        echo "  ✗ lp_webshop_pickers introuvable — rien à copier.\n";
    }
    if ($pickers) {
        // sort_order : à la suite des boutiques existantes
        $base = (int)$pdo->query("SELECT COALESCE(MAX(sort_order),0) FROM lp_shops")->fetchColumn();
        $ins = $pdo->prepare(
            "INSERT INTO lp_shops
                (sort_order, name, city, postal_code, kind, is_active,
                 webshop_url, picker_key, zone, lat, lng, show_in_picker)
             VALUES
                (:so, :name, '', '', 'shop', 0,
                 :url, :key, :zone, :lat, :lng, 1)"
        );
        $i = 0;
        foreach ($pickers as $p) {
            $ins->execute([
                ':so'   => $base + 1 + $i++,
                ':name' => $p['name'],
                ':url'  => $p['shop_url'],
                ':key'  => $p['picker_key'],
                ':zone' => $p['zone'],
                ':lat'  => $p['lat'],
                ':lng'  => $p['lng'],
            ]);
            echo "  ✓ copié : {$p['name']} ({$p['zone']})\n";
        }
    }
}

/* 3) Archivage de l'ancienne table */
echo "\n=== 3) Archivage ===\n";
try {
    $pdo->exec("RENAME TABLE lp_webshop_pickers TO lp_webshop_pickers_bak");
    echo "  ✓ lp_webshop_pickers → lp_webshop_pickers_bak (conservée en secours)\n";
} catch (PDOException $e) {
    echo "  ~ " . htmlspecialchars($e->getMessage()) . "\n";
}

/* Vérification */
echo "\n=== Vérification — pickers vus par l'API ===\n";
foreach ($pdo->query("SELECT picker_key, name, zone, lat, lng, webshop_url FROM lp_shops WHERE show_in_picker=1 ORDER BY sort_order")->fetchAll(PDO::FETCH_ASSOC) as $r) {
    echo "  {$r['picker_key']} · {$r['name']} · {$r['zone']} · {$r['lat']},{$r['lng']} · {$r['webshop_url']}\n";
}

echo "\n✓ Fusion terminée. Le picker lit maintenant lp_shops (show_in_picker=1). Supprimez ce fichier.\n";
echo '</pre>';
