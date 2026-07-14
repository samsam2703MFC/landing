<?php
/**
 * lp_install_nav.php — Crée lp_nav_items + seed par défaut
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
        CREATE TABLE IF NOT EXISTS lp_nav_items (
            id        INT UNSIGNED  NOT NULL AUTO_INCREMENT,
            position  TINYINT       NOT NULL DEFAULT 0,
            label_fr  VARCHAR(80)   NOT NULL DEFAULT '',
            label_nl  VARCHAR(80)   NOT NULL DEFAULT '',
            url       VARCHAR(255)  NOT NULL DEFAULT '',
            icon      VARCHAR(10)   NOT NULL DEFAULT '',
            style     ENUM('normal','gold','ruby','green') NOT NULL DEFAULT 'normal',
            is_active TINYINT(1)    NOT NULL DEFAULT 0,
            updated_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $exists = $pdo->query('SELECT COUNT(*) FROM lp_nav_items')->fetchColumn();
    if (!$exists) {
        $stmt = $pdo->prepare(
            'INSERT INTO lp_nav_items (position, label_fr, label_nl, url, icon, style, is_active)
             VALUES (:pos, :fr, :nl, :url, :icon, :style, :active)'
        );
        $rows = [
            [0, 'Galette des Rois', 'Driekoningentaart', 'galette-des-rois.html', '👑', 'gold',  0],
            [1, 'Fraise de saison', 'Seizoensaardbei',   'index.html#saison',     '🍓', 'ruby',  0],
            [2, 'Nouveauté',        'Nieuwigheid',       'index.html#saison',     '✨', 'green', 0],
        ];
        foreach ($rows as $r) {
            $stmt->execute([':pos'=>$r[0],':fr'=>$r[1],':nl'=>$r[2],':url'=>$r[3],':icon'=>$r[4],':style'=>$r[5],':active'=>$r[6]]);
        }
    }

    echo '<p style="font-family:monospace;color:green">✓ Table lp_nav_items créée (3 items, tous inactifs). Mettez is_active=1 pour afficher un item. Supprimez ce fichier.</p>';
} catch (PDOException $e) {
    echo '<p style="font-family:monospace;color:red">✗ ' . htmlspecialchars($e->getMessage()) . '</p>';
}
