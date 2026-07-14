<?php
/**
 * lp_install_pickers.php — Crée lp_webshop_pickers + seed par défaut
 * SUPPRIMER ce fichier après exécution.
 */
define('LP_DB_HOST', 'localhost');
define('LP_DB_NAME', 'atelierby_db');
define('LP_DB_USER', 'sam');
define('LP_DB_PASS', 'NhoQyQbKRSPh4Ubg3sR7DMjs5');

try {
    $pdo = new PDO("mysql:host=".LP_DB_HOST.";dbname=".LP_DB_NAME.";charset=utf8mb4",
        LP_DB_USER, LP_DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS lp_webshop_pickers (
            id          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
            sort_order  TINYINT       NOT NULL DEFAULT 0,
            picker_key  VARCHAR(40)   NOT NULL,
            name        VARCHAR(80)   NOT NULL DEFAULT '',
            zone        VARCHAR(120)  NOT NULL DEFAULT '',
            lat         DECIMAL(9,6)  NOT NULL DEFAULT 0,
            lng         DECIMAL(9,6)  NOT NULL DEFAULT 0,
            shop_url    VARCHAR(255)  NOT NULL DEFAULT '',
            is_active   TINYINT(1)    NOT NULL DEFAULT 1,
            updated_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uq_key (picker_key)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $exists = $pdo->query('SELECT COUNT(*) FROM lp_webshop_pickers')->fetchColumn();
    if (!$exists) {
        $stmt = $pdo->prepare(
            'INSERT INTO lp_webshop_pickers (sort_order, picker_key, name, zone, lat, lng, shop_url)
             VALUES (:so, :key, :name, :zone, :lat, :lng, :url)'
        );

        $pickers = [
            [0, 'halle',     'Halle',     'Hal · Brabant flamand',  50.7331, 4.2364, 'https://halle.latelierby.be'],
            [1, 'wavre',     'Wavre',     'Brabant wallon',          50.7171, 4.6012, 'https://wavre.latelierby.be'],
            [2, 'corbais',   'Corbais',   'Mont-Saint-Guibert',      50.6389, 4.6167, 'https://corbais.latelierby.be'],
            [3, 'gembloux',  'Gembloux',  'Namur',                   50.5611, 4.6917, 'https://gembloux.latelierby.be'],
            [4, 'sombreffe', 'Sombreffe', 'Namur',                   50.5333, 4.6000, 'https://sombreffe.latelierby.be'],
            [5, 'gosselies', 'Gosselies', 'Charleroi',               50.4667, 4.4333, 'https://gosselies.latelierby.be'],
        ];

        foreach ($pickers as $p) {
            $stmt->execute([
                ':so' => $p[0], ':key'  => $p[1], ':name' => $p[2],
                ':zone' => $p[3], ':lat' => $p[4], ':lng'  => $p[5], ':url' => $p[6],
            ]);
        }
    }

    echo '<p style="font-family:monospace;color:green">✓ Table lp_webshop_pickers créée et initialisée (6 pickers). Supprimez ce fichier.</p>';
} catch (PDOException $e) {
    echo '<p style="font-family:monospace;color:red">✗ ' . htmlspecialchars($e->getMessage()) . '</p>';
}
