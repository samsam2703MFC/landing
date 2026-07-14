<?php
/**
 * lp_install_collabs.php — Crée lp_collaborations + seed par défaut
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
        CREATE TABLE IF NOT EXISTS lp_collaborations (
            id          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
            position    TINYINT       NOT NULL DEFAULT 0,
            tag_fr      VARCHAR(100)  NOT NULL DEFAULT '',
            tag_nl      VARCHAR(100)  NOT NULL DEFAULT '',
            name_fr     VARCHAR(150)  NOT NULL DEFAULT '',
            name_nl     VARCHAR(150)  NOT NULL DEFAULT '',
            desc_fr     TEXT,
            desc_nl     TEXT,
            image_path  VARCHAR(255)  NOT NULL DEFAULT '',
            shop_url    VARCHAR(255)  NOT NULL DEFAULT '',
            is_active   TINYINT(1)    NOT NULL DEFAULT 1,
            updated_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $exists = $pdo->query('SELECT COUNT(*) FROM lp_collaborations')->fetchColumn();
    if (!$exists) {
        $stmt = $pdo->prepare(
            'INSERT INTO lp_collaborations
                (position, tag_fr, tag_nl, name_fr, name_nl, desc_fr, desc_nl, image_path, shop_url, is_active)
             VALUES
                (:pos, :tfr, :tnl, :nfr, :nnl, :dfr, :dnl, :img, :url, 1)'
        );

        $items = [
            [
                0,
                'Viennoiserie × Darcis', 'Viennoiserie × Darcis',
                'Pain au chocolat', 'Chocoladebrood',
                'Couverture 70 % du maître chocolatier liégeois. Feuilletage au beurre AOP.',
                '70% couverture van de Luikse meesterchocolatier. Bladerdeeg met AOP-boter.',
                'img/products/roll.png', 'index.html#produits',
            ],
            [
                1,
                'Pâtisserie × Marcolini', 'Gebak × Marcolini',
                'Tarte chocolat', 'Chocoladetaart',
                'Ganache au grand cru vénézuélien, pâte sablée au sarrasin. Bruxelles uniquement.',
                'Ganache van Venezolaanse grand cru, boekweitkorstdeeg. Alleen in Brussel.',
                'img/products/sweet-tart-small.png', 'index.html#produits',
            ],
            [
                2,
                'Cookies × Benoît Nihant', 'Koekjes × Benoît Nihant',
                'Trois fèves', 'Drie bonen',
                'Sarrasin-sésame, cacao-fleur de sel, vanille de Tahiti — torréfacteur d\'Aywaille.',
                'Boekweit-sesam, cacao-zeezout, Tahiti-vanille — brander uit Aywaille.',
                'img/products/cookies.png', 'index.html#produits',
            ],
            [
                3,
                'Gâteau × La Maison', 'Taart × La Maison',
                'Entremets signature', 'Signatuurtaart',
                'Trois textures, un seul parfum de saison. Pensé pour être partagé.',
                'Drie texturen, één seizoenssmaak. Bedoeld om te delen.',
                'img/products/cake-slice.png', 'index.html#produits',
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

    echo '<p style="font-family:monospace;color:green">✓ Table lp_collaborations créée et initialisée. Supprimez ce fichier.</p>';
} catch (PDOException $e) {
    echo '<p style="font-family:monospace;color:red">✗ ' . htmlspecialchars($e->getMessage()) . '</p>';
}
