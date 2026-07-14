<?php
/**
 * lp_install_shops.php — Crée lp_shops + lp_shop_hours + lp_shop_services + seed
 * SUPPRIMER ce fichier après exécution.
 */
define('LP_DB_HOST', 'localhost');
define('LP_DB_NAME', 'atelierby_db');
define('LP_DB_USER', 'sam');
define('LP_DB_PASS', 'NhoQyQbKRSPh4Ubg3sR7DMjs5');

try {
    $pdo = new PDO("mysql:host=".LP_DB_HOST.";dbname=".LP_DB_NAME.";charset=utf8mb4",
        LP_DB_USER, LP_DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // ── lp_shops ─────────────────────────────────────────────
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS lp_shops (
            id           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
            sort_order   TINYINT       NOT NULL DEFAULT 0,
            name         VARCHAR(100)  NOT NULL DEFAULT '',
            city         VARCHAR(80)   NOT NULL DEFAULT '',
            postal_code  VARCHAR(10)   NOT NULL DEFAULT '',
            kind         ENUM('shop','popup') NOT NULL DEFAULT 'shop',
            address      VARCHAR(255)  NOT NULL DEFAULT '',
            phone        VARCHAR(30)   NOT NULL DEFAULT '',
            email        VARCHAR(100)  NOT NULL DEFAULT '',
            concept_fr   TEXT,
            concept_nl   TEXT,
            image_path   VARCHAR(255)  NOT NULL DEFAULT '',
            webshop_url  VARCHAR(255)  NOT NULL DEFAULT '',
            is_active    TINYINT(1)    NOT NULL DEFAULT 1,
            updated_at   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // ── lp_shop_hours ─────────────────────────────────────────
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS lp_shop_hours (
            id       INT UNSIGNED NOT NULL AUTO_INCREMENT,
            shop_id  INT UNSIGNED NOT NULL,
            day      ENUM('mon','tue','wed','thu','fri','sat','sun') NOT NULL,
            hours    VARCHAR(30)  NOT NULL DEFAULT '',
            PRIMARY KEY (id),
            KEY idx_shop (shop_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // ── lp_shop_services ──────────────────────────────────────
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS lp_shop_services (
            id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
            shop_id     INT UNSIGNED NOT NULL,
            service_key VARCHAR(30)  NOT NULL,
            PRIMARY KEY (id),
            KEY idx_shop (shop_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $exists = $pdo->query('SELECT COUNT(*) FROM lp_shops')->fetchColumn();
    if (!$exists) {
        $shops = [
            [
                'sort_order' => 0, 'name' => 'Maison Châtelain', 'city' => 'Bruxelles', 'postal_code' => '1050',
                'kind' => 'shop', 'address' => 'Rue du Bailli 42, 1050 Ixelles',
                'phone' => '+32 2 648 12 30', 'email' => 'chatelain@atelierby.be',
                'concept_fr' => 'Boulangerie-pâtisserie de quartier. Pains de tradition et viennoiseries au beurre AOP.',
                'concept_nl' => 'Buurtbakkerij-patisserie. Traditioneel brood en viennoiserie met AOP-boter.',
                'image_path' => 'img/products/bread-1.png', 'webshop_url' => '',
                'hours' => ['mon'=>'7:00 – 19:00','tue'=>'7:00 – 19:00','wed'=>'7:00 – 19:00','thu'=>'7:00 – 19:00','fri'=>'7:00 – 20:00','sat'=>'8:00 – 19:00','sun'=>'8:00 – 14:00'],
                'services' => ['collect','delivery','b2b','office','loyalty'],
            ],
            [
                'sort_order' => 1, 'name' => 'Atelier Sablon', 'city' => 'Bruxelles', 'postal_code' => '1000',
                'kind' => 'shop', 'address' => 'Place du Grand Sablon 18, 1000 Bruxelles',
                'phone' => '+32 2 511 08 74', 'email' => 'sablon@atelierby.be',
                'concept_fr' => 'Concept store. Brunch, pâtisserie fine et café de spécialité.',
                'concept_nl' => 'Conceptstore. Brunch, fijne patisserie en specialiteitskoffie.',
                'image_path' => 'img/products/cake.png', 'webshop_url' => '',
                'hours' => ['mon'=>'8:00 – 18:00','tue'=>'8:00 – 18:00','wed'=>'8:00 – 18:00','thu'=>'8:00 – 18:00','fri'=>'8:00 – 19:00','sat'=>'8:00 – 19:00','sun'=>'9:00 – 15:00'],
                'services' => ['collect','delivery','catering','loyalty'],
            ],
            [
                'sort_order' => 2, 'name' => 'Le Carré', 'city' => 'Liège', 'postal_code' => '4000',
                'kind' => 'shop', 'address' => "Rue Pont d'Avroy 11, 4000 Liège",
                'phone' => '+32 4 223 45 90', 'email' => 'carre@atelierby.be',
                'concept_fr' => 'Maison familiale au cœur du Carré liégeois. Spécialité des gaufrettes.',
                'concept_nl' => 'Familiehuis in het hart van de Luikse Carré. Specialiteit: gaufrettes.',
                'image_path' => 'img/products/croissant.png', 'webshop_url' => '',
                'hours' => ['mon'=>'7:00 – 19:00','tue'=>'7:00 – 19:00','wed'=>'7:00 – 19:00','thu'=>'7:00 – 19:00','fri'=>'7:00 – 20:00','sat'=>'7:00 – 20:00','sun'=>'8:00 – 15:00'],
                'services' => ['collect','delivery','phone','b2b','loyalty'],
            ],
            [
                'sort_order' => 3, 'name' => 'Zuid Bakery', 'city' => 'Antwerpen', 'postal_code' => '2000',
                'kind' => 'shop', 'address' => 'Volkstraat 37, 2000 Antwerpen',
                'phone' => '+32 3 238 91 45', 'email' => 'zuid@atelierby.be',
                'concept_fr' => 'Pains au levain longue fermentation. Viennoiseries du dimanche.',
                'concept_nl' => 'Zuurdesembrood met lange rijs. Zondagse viennoiserie.',
                'image_path' => 'img/products/roll.png', 'webshop_url' => '',
                'hours' => ['mon'=>'7:00 – 18:30','tue'=>'7:00 – 18:30','wed'=>'7:00 – 18:30','thu'=>'7:00 – 18:30','fri'=>'7:00 – 19:00','sat'=>'8:00 – 19:00','sun'=>'closed'],
                'services' => ['collect','office','b2b','loyalty'],
            ],
            [
                'sort_order' => 4, 'name' => 'Patershol', 'city' => 'Gent', 'postal_code' => '9000',
                'kind' => 'popup', 'address' => 'Kraanlei 15, 9000 Gent',
                'phone' => '+32 9 225 67 10', 'email' => 'patershol@atelierby.be',
                'concept_fr' => 'Résidence éphémère — avril à juin. Croissants du matin, limonades artisanales.',
                'concept_nl' => 'Tijdelijke residentie — april tot juni. Ochtendcroissants, ambachtelijke limonades.',
                'image_path' => 'img/products/cookies.png', 'webshop_url' => '',
                'hours' => ['mon'=>'closed','tue'=>'8:00 – 16:00','wed'=>'8:00 – 16:00','thu'=>'8:00 – 16:00','fri'=>'8:00 – 17:00','sat'=>'8:00 – 17:00','sun'=>'9:00 – 14:00'],
                'services' => ['collect','catering'],
            ],
            [
                'sort_order' => 5, 'name' => 'Le Grognon', 'city' => 'Namur', 'postal_code' => '5000',
                'kind' => 'shop', 'address' => 'Rue des Brasseurs 108, 5000 Namur',
                'phone' => '+32 81 22 14 58', 'email' => 'grognon@atelierby.be',
                'concept_fr' => 'Au pied de la citadelle. Pains spéciaux et tartes salées à la découpe.',
                'concept_nl' => 'Aan de voet van de citadel. Speciale broden en hartige taarten per stuk.',
                'image_path' => 'img/products/savoury-tart.png', 'webshop_url' => '',
                'hours' => ['mon'=>'7:00 – 18:00','tue'=>'7:00 – 18:00','wed'=>'7:00 – 18:00','thu'=>'7:00 – 18:00','fri'=>'7:00 – 19:00','sat'=>'7:30 – 18:00','sun'=>'8:00 – 13:00'],
                'services' => ['collect','delivery','phone','loyalty'],
            ],
            [
                'sort_order' => 6, 'name' => 'Brugge Studio', 'city' => 'Brugge', 'postal_code' => '8000',
                'kind' => 'shop', 'address' => 'Steenstraat 74, 8000 Brugge',
                'phone' => '+32 50 33 29 41', 'email' => 'brugge@atelierby.be',
                'concept_fr' => 'Atelier-boutique. Cours de boulangerie le samedi, vente à emporter.',
                'concept_nl' => 'Atelier-winkel. Bakcursussen op zaterdag, afhaal.',
                'image_path' => 'img/products/sweet-tart-small.png', 'webshop_url' => '',
                'hours' => ['mon'=>'closed','tue'=>'8:00 – 18:00','wed'=>'8:00 – 18:00','thu'=>'8:00 – 18:00','fri'=>'8:00 – 18:00','sat'=>'8:00 – 18:00','sun'=>'9:00 – 15:00'],
                'services' => ['collect','catering','loyalty'],
            ],
            [
                'sort_order' => 7, 'name' => 'Leuven', 'city' => 'Leuven', 'postal_code' => '3000',
                'kind' => 'popup', 'address' => 'Oude Markt 23, 3000 Leuven',
                'phone' => '+32 16 22 45 61', 'email' => 'leuven@atelierby.be',
                'concept_fr' => 'Résidence universitaire — septembre à juin. Sandwiches du midi.',
                'concept_nl' => 'Studentenresidentie — september tot juni. Broodjes \'s middags.',
                'image_path' => 'img/products/sandwiches.png', 'webshop_url' => '',
                'hours' => ['mon'=>'closed','tue'=>'11:00 – 20:00','wed'=>'11:00 – 20:00','thu'=>'11:00 – 20:00','fri'=>'11:00 – 22:00','sat'=>'11:00 – 22:00','sun'=>'11:00 – 17:00'],
                'services' => ['collect','office'],
            ],
        ];

        $stmtShop = $pdo->prepare(
            'INSERT INTO lp_shops (sort_order, name, city, postal_code, kind, address, phone, email, concept_fr, concept_nl, image_path, webshop_url)
             VALUES (:so, :name, :city, :cp, :kind, :addr, :tel, :mail, :cfr, :cnl, :img, :ws)'
        );
        $stmtHours = $pdo->prepare(
            'INSERT INTO lp_shop_hours (shop_id, day, hours) VALUES (:sid, :day, :h)'
        );
        $stmtSvc = $pdo->prepare(
            'INSERT INTO lp_shop_services (shop_id, service_key) VALUES (:sid, :key)'
        );

        foreach ($shops as $s) {
            $stmtShop->execute([
                ':so'   => $s['sort_order'], ':name' => $s['name'],
                ':city' => $s['city'],       ':cp'   => $s['postal_code'],
                ':kind' => $s['kind'],       ':addr' => $s['address'],
                ':tel'  => $s['phone'],      ':mail' => $s['email'],
                ':cfr'  => $s['concept_fr'], ':cnl'  => $s['concept_nl'],
                ':img'  => $s['image_path'], ':ws'   => $s['webshop_url'],
            ]);
            $id = (int)$pdo->lastInsertId();
            foreach ($s['hours'] as $day => $h) {
                $stmtHours->execute([':sid' => $id, ':day' => $day, ':h' => $h]);
            }
            foreach ($s['services'] as $key) {
                $stmtSvc->execute([':sid' => $id, ':key' => $key]);
            }
        }
    }

    echo '<p style="font-family:monospace;color:green">✓ Tables lp_shops + lp_shop_hours + lp_shop_services créées et initialisées (8 boutiques). Supprimez ce fichier.</p>';
} catch (PDOException $e) {
    echo '<p style="font-family:monospace;color:red">✗ ' . htmlspecialchars($e->getMessage()) . '</p>';
}
