<?php
/**
 * lp_install_seasonal.php — Crée lp_seasonal_items + seed par défaut
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
        CREATE TABLE IF NOT EXISTS lp_seasonal_items (
            id               INT UNSIGNED  NOT NULL AUTO_INCREMENT,
            position         TINYINT       NOT NULL DEFAULT 0,
            tag_fr           VARCHAR(80)   NOT NULL DEFAULT '',
            tag_nl           VARCHAR(80)   NOT NULL DEFAULT '',
            name_fr          VARCHAR(150)  NOT NULL DEFAULT '',
            name_nl          VARCHAR(150)  NOT NULL DEFAULT '',
            desc_fr          TEXT,
            desc_nl          TEXT,
            image_path       VARCHAR(255)  NOT NULL DEFAULT '',
            item_url         VARCHAR(255)  NOT NULL DEFAULT '',
            ws_product_slug  VARCHAR(150)  NOT NULL DEFAULT '',
            available_from   DATE          DEFAULT NULL,
            available_until  DATE          DEFAULT NULL,
            is_active        TINYINT(1)    NOT NULL DEFAULT 1,
            updated_at       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $exists = $pdo->query('SELECT COUNT(*) FROM lp_seasonal_items')->fetchColumn();
    if (!$exists) {
        $stmt = $pdo->prepare(
            'INSERT INTO lp_seasonal_items
                (position, tag_fr, tag_nl, name_fr, name_nl, desc_fr, desc_nl, image_path, item_url, is_active)
             VALUES
                (:pos, :tfr, :tnl, :nfr, :nnl, :dfr, :dnl, :img, :url, 1)'
        );

        $items = [
            [
                0,
                'Été', 'Zomer',
                'Brioche Croustillante Myrtille', 'Knapperige Bosbesbrioche',
                "Brioche filée, cœur de myrtille et crumble citronné. Jusqu'à épuisement des matières de saison.",
                'Gedraaide brioche, bosbessenhart en citroenkruimel. Zolang de seizoensingrediënten strekken.',
                'img/products/brioche-croustillante.png', 'index.html#produits',
            ],
            [
                1,
                'Saison', 'Seizoen',
                'Tarte de printemps', 'Lentetaart',
                'On commence quand la matière est prête, on termine quand elle s\'épuise.',
                'We beginnen als de ingrediënten klaar zijn, we stoppen als ze op zijn.',
                'img/availability/spring.png', 'index.html#produits',
            ],
            [
                2,
                'Fête', 'Feest',
                'Galette des Rois', 'Driekoningentaart',
                'Frangipane à l\'ancienne, fève signée. De janvier à l\'Épiphanie.',
                'Traditionele frangipane, gesigneerde boon. Van januari tot Driekoningen.',
                'img/availability/epiphany.png', 'index.html#produits',
            ],
            [
                3,
                'Édition', 'Editie',
                'Spéculoos de saint Nicolas', 'Sinterklaasspeculaas',
                'Si vous l\'aimez, ne l\'attendez pas.',
                'Als u ervan houdt, wacht dan niet.',
                'img/availability/saint-nicholas.png', 'index.html#produits',
            ],
        ];

        foreach ($items as $s) {
            $stmt->execute([
                ':pos' => $s[0], ':tfr' => $s[1], ':tnl' => $s[2],
                ':nfr' => $s[3], ':nnl' => $s[4],
                ':dfr' => $s[5], ':dnl' => $s[6],
                ':img' => $s[7], ':url' => $s[8],
            ]);
        }
    }

    echo '<p style="font-family:monospace;color:green">✓ Table lp_seasonal_items créée et initialisée. Supprimez ce fichier.</p>';
} catch (PDOException $e) {
    echo '<p style="font-family:monospace;color:red">✗ ' . htmlspecialchars($e->getMessage()) . '</p>';
}
