<?php
/**
 * lp_install_app.php — Crée lp_params + lp_app_section + seed par défaut
 * SUPPRIMER ce fichier après exécution.
 */
define('LP_DB_HOST', 'localhost');
define('LP_DB_NAME', 'atelierby_db');
define('LP_DB_USER', 'sam');
define('LP_DB_PASS', 'NhoQyQbKRSPh4Ubg3sR7DMjs5');

try {
    $pdo = new PDO("mysql:host=".LP_DB_HOST.";dbname=".LP_DB_NAME.";charset=utf8mb4",
        LP_DB_USER, LP_DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // ── lp_params : table clé/valeur générale ───────────────
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS lp_params (
            param_key   VARCHAR(60)  NOT NULL,
            param_value TEXT,
            updated_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (param_key)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $pdo->exec("
        INSERT IGNORE INTO lp_params (param_key, param_value) VALUES
            ('app_url',   'https://latelierby.be/app'),
            ('site_name', \"L'Atelier By\")
    ");
    echo '<p style="font-family:monospace;color:green">✓ Table lp_params créée.</p>';

    // ── lp_app_section : contenu section app ────────────────
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS lp_app_section (
            id          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
            title_fr    VARCHAR(255)  NOT NULL DEFAULT '',
            title_nl    VARCHAR(255)  NOT NULL DEFAULT '',
            lede_fr     TEXT,
            lede_nl     TEXT,
            point1_fr   VARCHAR(255)  NOT NULL DEFAULT '',
            point1_nl   VARCHAR(255)  NOT NULL DEFAULT '',
            point2_fr   VARCHAR(255)  NOT NULL DEFAULT '',
            point2_nl   VARCHAR(255)  NOT NULL DEFAULT '',
            point3_fr   VARCHAR(255)  NOT NULL DEFAULT '',
            point3_nl   VARCHAR(255)  NOT NULL DEFAULT '',
            cta_text_fr VARCHAR(100)  NOT NULL DEFAULT '',
            cta_text_nl VARCHAR(100)  NOT NULL DEFAULT '',
            hint_fr     VARCHAR(150)  NOT NULL DEFAULT '',
            hint_nl     VARCHAR(150)  NOT NULL DEFAULT '',
            updated_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $exists = $pdo->query('SELECT COUNT(*) FROM lp_app_section')->fetchColumn();
    if (!$exists) {
        $pdo->exec("
            INSERT INTO lp_app_section
                (title_fr, title_nl, lede_fr, lede_nl,
                 point1_fr, point1_nl, point2_fr, point2_nl, point3_fr, point3_nl,
                 cta_text_fr, cta_text_nl, hint_fr, hint_nl)
            VALUES (
                'Application mobile', 'Mobiele applicatie',
                'Votre accès garanti à tous les bundles et promotions, au programme de fidélité et à vos abonnements — réunis au même endroit.',
                'Uw gegarandeerde toegang tot alle bundles en promoties, het getrouwheidsprogramma en uw abonnementen — op één plek.',
                'Tous les bundles & promotions', 'Alle bundles & promoties',
                'Programme de fidélité',         'Getrouwheidsprogramma',
                'Abonnements',                   'Abonnementen',
                \"Télécharger l'application\",   'Download de app',
                'Scannez un QR code · iOS & Android', 'Scan een QR-code · iOS & Android'
            )
        ");
    }

    // Ajouter 'app' dans lp_sections si absente
    try {
        $secExists = $pdo->query("SELECT COUNT(*) FROM lp_sections WHERE section_key='app'")->fetchColumn();
        if (!$secExists) {
            $pdo->exec("
                INSERT INTO lp_sections (section_key, eyebrow_fr, eyebrow_nl, title_fr, title_nl)
                VALUES ('app', 'Mobile', 'Mobiel', 'Application mobile', 'Mobiele applicatie')
            ");
        }
    } catch (PDOException $e) {}

    echo '<p style="font-family:monospace;color:green">✓ Table lp_app_section créée et initialisée. Supprimez ce fichier.</p>';
    echo '<p style="font-family:monospace;color:#555">→ Modifiez <strong>app_url</strong> dans lp_params pour changer le lien du QR code.</p>';

} catch (PDOException $e) {
    echo '<p style="font-family:monospace;color:red">✗ ' . htmlspecialchars($e->getMessage()) . '</p>';
}
