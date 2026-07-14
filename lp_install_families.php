<?php
/**
 * lp_install_families.php — Crée lp_product_families + seed
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
        CREATE TABLE IF NOT EXISTS lp_product_families (
            id          TINYINT UNSIGNED  NOT NULL AUTO_INCREMENT,
            position    TINYINT UNSIGNED  NOT NULL DEFAULT 0,
            name_fr     VARCHAR(100)      NOT NULL,
            name_nl     VARCHAR(100)      NOT NULL,
            count_fr    VARCHAR(60)       NOT NULL DEFAULT '',
            count_nl    VARCHAR(60)       NOT NULL DEFAULT '',
            image_path  VARCHAR(255)      NOT NULL DEFAULT '',
            href        VARCHAR(255)      NOT NULL DEFAULT 'index.html#produits',
            is_active   TINYINT(1)        NOT NULL DEFAULT 1,
            updated_at  TIMESTAMP         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_position (position)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $exists = $pdo->query('SELECT COUNT(*) FROM lp_product_families')->fetchColumn();
    if (!$exists) {
        $stmt = $pdo->prepare(
            'INSERT INTO lp_product_families (position, name_fr, name_nl, count_fr, count_nl, image_path, href)
             VALUES (:pos, :nfr, :nnl, :cfr, :cnl, :img, :href)'
        );
        $rows = [
            [0, 'Pains',         'Brood',         '24 variétés',     '24 variëteiten',  'img/products/bread-1.png',     'index.html#produits'],
            [1, 'Viennoiseries', 'Viennoiserie',   '16 pièces',       '16 stuks',        'img/products/croissant.png',   'index.html#produits'],
            [2, 'Pâtisseries',   'Gebak',          '12 créations',    '12 creaties',     'img/products/cake.png',        'index.html#produits'],
            [3, 'Salé',          'Hartig',         '9 tartes',        '9 taarten',       'img/products/savoury-tart.png','index.html#produits'],
            [4, 'Sandwiches',    'Sandwiches',     '14 combinaisons', '14 combinaties',  'img/products/sandwiches.png',  'index.html#produits'],
        ];
        foreach ($rows as $r) {
            $stmt->execute([':pos'=>$r[0],':nfr'=>$r[1],':nnl'=>$r[2],':cfr'=>$r[3],':cnl'=>$r[4],':img'=>$r[5],':href'=>$r[6]]);
        }
    }

    echo '<p style="font-family:monospace;color:green">✓ Table lp_product_families créée et initialisée. Supprimez ce fichier.</p>';
} catch (PDOException $e) {
    echo '<p style="font-family:monospace;color:red">✗ ' . htmlspecialchars($e->getMessage()) . '</p>';
}
