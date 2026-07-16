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
            url         VARCHAR(255)  NOT NULL DEFAULT '',
            theme       VARCHAR(30)   NOT NULL DEFAULT '',
            is_active   TINYINT(1)    NOT NULL DEFAULT 1,
            updated_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $exists = $pdo->query('SELECT COUNT(*) FROM lp_services')->fetchColumn();
    if (!$exists) {
        $stmt = $pdo->prepare(
            'INSERT INTO lp_services (position, name_fr, name_nl, desc_fr, desc_nl, icon_svg, url, theme)
             VALUES (:pos, :nfr, :nnl, :dfr, :dnl, :svg, :url, :theme)'
        );

        $rows = [
            [
                0,
                'Livraison au bureau', 'Levering op kantoor',
                'Pauses gourmandes et petits-déjeuners livrés directement à votre entreprise.',
                'Lekkere pauzes en ontbijten rechtstreeks op kantoor geleverd.',
                '<path d="M4 21V8l8-5 8 5v13M9 21v-6h6v6"/>',
                'livraison-bureau.html', 'apricot',
            ],
            [
                1,
                'Comptes B2B', 'B2B-accounts',
                'Hôtels, restaurants, collectivités. Livraisons avant 7 h, facturation mensuelle.',
                'Hotels, restaurants, gemeenschappen. Levering vóór 7 u, maandelijkse facturatie.',
                '<path d="M3 20h18M5 20V9l7-4 7 4v11"/><path d="M10 20v-5h4v5"/>',
                'b2b.html', 'copper',
            ],
        ];

        foreach ($rows as $r) {
            $stmt->execute([
                ':pos' => $r[0], ':nfr' => $r[1], ':nnl' => $r[2],
                ':dfr' => $r[3], ':dnl' => $r[4], ':svg' => $r[5],
                ':url' => $r[6], ':theme' => $r[7],
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
