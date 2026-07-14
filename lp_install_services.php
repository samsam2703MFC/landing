<?php
/**
 * lp_install_services.php — Crée lp_services + seed par défaut
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
        CREATE TABLE IF NOT EXISTS lp_services (
            id          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
            position    TINYINT       NOT NULL DEFAULT 0,
            name_fr     VARCHAR(150)  NOT NULL DEFAULT '',
            name_nl     VARCHAR(150)  NOT NULL DEFAULT '',
            desc_fr     TEXT,
            desc_nl     TEXT,
            icon_svg    VARCHAR(600)  NOT NULL DEFAULT '',
            is_active   TINYINT(1)    NOT NULL DEFAULT 1,
            updated_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $exists = $pdo->query('SELECT COUNT(*) FROM lp_services')->fetchColumn();
    if (!$exists) {
        $stmt = $pdo->prepare(
            'INSERT INTO lp_services (position, name_fr, name_nl, desc_fr, desc_nl, icon_svg)
             VALUES (:pos, :nfr, :nnl, :dfr, :dnl, :svg)'
        );

        $rows = [
            [
                0,
                'Click & Collect', 'Click & Collect',
                "Commandez en ligne, récupérez chaud. Le créneau ouvre 48 h à l'avance.",
                'Bestel online, haal warm af. Het tijdslot opent 48 u vooraf.',
                '<circle cx="12" cy="12" r="8"/><path d="M8 12l3 3 5-6"/>',
            ],
            [
                1,
                'Livraison', 'Levering',
                "Zone urbaine, livreurs à vélo. Créneau de 30 minutes garanti à l'heure choisie.",
                'Stedelijk gebied, fietsers. Gegarandeerd 30 minuten tijdslot op het gekozen uur.',
                '<path d="M3 16V7h11v9M14 10h4l3 3v3h-7"/><circle cx="7" cy="18" r="1.8"/><circle cx="17" cy="18" r="1.8"/>',
            ],
            [
                2,
                'Commande téléphonique', 'Telefonische bestelling',
                "Plateaux, allergènes, cadeaux d'entreprise. Une voix, une oreille.",
                'Schotels, allergenen, bedrijfscadeaus. Een stem, een oor.',
                '<path d="M5 4h3l2 5-2 1a11 11 0 006 6l1-2 5 2v3a2 2 0 01-2 2A16 16 0 013 6a2 2 0 012-2z"/>',
            ],
            [
                3,
                'Comptes B2B', 'B2B-accounts',
                'Hôtels, restaurants, collectivités. Livraisons avant 7 h, facturation mensuelle.',
                'Hotels, restaurants, gemeenschappen. Levering vóór 7 u, maandelijkse facturatie.',
                '<path d="M3 20h18M5 20V9l7-4 7 4v11"/><path d="M10 20v-5h4v5"/>',
            ],
            [
                4,
                'Boutique en ligne', 'Webshop',
                'Toute la Maison au bout des doigts. Choisissez, réglez, récupérez depuis chez vous.',
                'Het hele huis aan uw vingertoppen. Kies, betaal, haal af vanuit huis.',
                '<path d="M6 8h12l-1 12H7L6 8z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/>',
            ],
            [
                5,
                'Programme fidélité', 'Getrouwheidsprogramma',
                'Un QR code scanné en caisse, des récompenses qui grandissent à chaque visite.',
                'Een QR-code gescand aan de kassa, beloningen die groeien bij elk bezoek.',
                '<path d="M12 21s-7-4.5-7-10a4 4 0 017-2.7A4 4 0 0119 11c0 5.5-7 10-7 10z"/>',
            ],
        ];

        foreach ($rows as $r) {
            $stmt->execute([
                ':pos' => $r[0], ':nfr' => $r[1], ':nnl' => $r[2],
                ':dfr' => $r[3], ':dnl' => $r[4], ':svg' => $r[5],
            ]);
        }
    }

    // Ajouter la section "experiences" dans lp_sections si absente
    try {
        $secExists = $pdo->query("SELECT COUNT(*) FROM lp_sections WHERE section_key='experiences'")->fetchColumn();
        if (!$secExists) {
            $pdo->prepare(
                "INSERT INTO lp_sections (section_key, eyebrow_fr, eyebrow_nl, title_fr, title_nl)
                 VALUES ('experiences', 'Expériences', 'Ervaringen', 'Nos services', 'Onze diensten')"
            )->execute();
        }
    } catch (PDOException $e) { /* lp_sections peut ne pas exister */ }

    echo '<p style="font-family:monospace;color:green">✓ Table lp_services créée et initialisée. Supprimez ce fichier.</p>';
} catch (PDOException $e) {
    echo '<p style="font-family:monospace;color:red">✗ ' . htmlspecialchars($e->getMessage()) . '</p>';
}
