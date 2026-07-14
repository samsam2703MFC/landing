<?php
/**
 * lp_install_hero.php — Crée lp_hero_slides + seed par défaut
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
        CREATE TABLE IF NOT EXISTS lp_hero_slides (
            id              INT UNSIGNED  NOT NULL AUTO_INCREMENT,
            position        TINYINT       NOT NULL DEFAULT 0,
            eyebrow_fr      VARCHAR(150)  NOT NULL DEFAULT '',
            eyebrow_nl      VARCHAR(150)  NOT NULL DEFAULT '',
            title_fr        VARCHAR(255)  NOT NULL DEFAULT '',
            title_nl        VARCHAR(255)  NOT NULL DEFAULT '',
            lede_fr         TEXT,
            lede_nl         TEXT,
            cta1_text_fr    VARCHAR(100)  NOT NULL DEFAULT '',
            cta1_text_nl    VARCHAR(100)  NOT NULL DEFAULT '',
            cta1_url        VARCHAR(255)  NOT NULL DEFAULT '',
            cta2_text_fr    VARCHAR(100)  NOT NULL DEFAULT '',
            cta2_text_nl    VARCHAR(100)  NOT NULL DEFAULT '',
            cta2_url        VARCHAR(255)  NOT NULL DEFAULT '',
            image_path      VARCHAR(255)  NOT NULL DEFAULT '',
            ws_product_slug VARCHAR(150)  NOT NULL DEFAULT '',
            is_active       TINYINT(1)    NOT NULL DEFAULT 1,
            updated_at      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $exists = $pdo->query('SELECT COUNT(*) FROM lp_hero_slides')->fetchColumn();
    if (!$exists) {
        $stmt = $pdo->prepare(
            'INSERT INTO lp_hero_slides
                (position, eyebrow_fr, eyebrow_nl, title_fr, title_nl,
                 lede_fr, lede_nl,
                 cta1_text_fr, cta1_text_nl, cta1_url,
                 cta2_text_fr, cta2_text_nl, cta2_url,
                 image_path, is_active)
             VALUES
                (:pos, :efr, :enl, :tfr, :tnl,
                 :lfr, :lnl,
                 :c1fr, :c1nl, :c1url,
                 :c2fr, :c2nl, :c2url,
                 :img, 1)'
        );

        $slides = [
            [
                0,
                'Maison de pains & viennoiseries — Belgique',
                'Maison van brood & viennoiseries — België',
                'Fait main, <span class="script">chaque matin.</span>',
                'Handgemaakt, <span class="script">elke ochtend.</span>',
                'Tout est fabriqué dans nos ateliers, à la main, chaque jour. Du pain de tradition aux tartes à partager.',
                'Alles wordt dagelijks met de hand gemaakt in onze ateliers. Van traditioneel brood tot deelbare taarten.',
                'Découvrir les produits', 'Ontdek de producten', 'index.html#produits',
                'Trouver une boutique →', 'Vind een winkel →', 'index.html#boutiques',
                'img/products/bread-1.png',
            ],
            [
                1,
                "L'offre", 'Het aanbod',
                '4 parts achetées, la 5ᵉ <span class="script">offerte.</span>',
                '4 delen gekocht, het 5ᵉ <span class="script">gratis.</span>',
                'Tartes, quiches et gâteaux pensés pour être partagés — à table, au bureau, en famille.',
                'Taarten, quiches en gebak om te delen — aan tafel, op kantoor, met het gezin.',
                'Voir les produits', 'Bekijk producten', 'index.html#produits',
                'Commander →', 'Bestellen →', 'index.html#boutiques',
                'img/products/cake.png',
            ],
            [
                2,
                'Édition limitée', 'Beperkte editie',
                'Produits de <span class="script">saison.</span>',
                'Seizoens<span class="script">producten.</span>',
                'Disponibles tant que la matière première dure : quelques jours, parfois quelques semaines.',
                'Beschikbaar zolang de grondstof strekt: enkele dagen, soms enkele weken.',
                'Voir les éditions', 'Bekijk edities', 'index.html#saison',
                'Galette des Rois →', 'Galette des Rois →', 'index.html#saison',
                'img/availability/epiphany.png',
            ],
            [
                3,
                'Édition saisonnière · Été', 'Seizoenditie · Zomer',
                'Brioche Croustillante <span class="script">Myrtille.</span>',
                'Knapperige Brioche <span class="script">Bosbes.</span>',
                "Brioche filée, cœur de myrtille et crumble citronné. Jusqu'à épuisement des matières premières de saison.",
                'Gedraaide brioche, bosbessenhart en citroenkruimel. Zolang de seizoensingrediënten strekken.',
                'Commander', 'Bestellen', 'index.html#boutiques',
                'Voir les éditions →', 'Bekijk edities →', 'index.html#saison',
                'img/products/brioche-croustillante.png',
            ],
        ];

        foreach ($slides as $s) {
            $stmt->execute([
                ':pos'  => $s[0],  ':efr'  => $s[1],  ':enl'  => $s[2],
                ':tfr'  => $s[3],  ':tnl'  => $s[4],
                ':lfr'  => $s[5],  ':lnl'  => $s[6],
                ':c1fr' => $s[7],  ':c1nl' => $s[8],  ':c1url'=> $s[9],
                ':c2fr' => $s[10], ':c2nl' => $s[11], ':c2url'=> $s[12],
                ':img'  => $s[13],
            ]);
        }
    }

    echo '<p style="font-family:monospace;color:green">✓ Table lp_hero_slides créée et initialisée. Supprimez ce fichier.</p>';
} catch (PDOException $e) {
    echo '<p style="font-family:monospace;color:red">✗ ' . htmlspecialchars($e->getMessage()) . '</p>';
}
