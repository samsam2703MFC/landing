<?php
/**
 * lp_install_footer.php — Crée lp_footer_links + seed par défaut
 * SUPPRIMER ce fichier après exécution.
 *
 * col : 1 = Explorer, 2 = Services, 3 = La Maison
 *       (les titres de colonnes restent dans lp_i18n : ft.explore / ft.services / ft.house)
 * Une colonne sans lien actif disparaît automatiquement.
 */
define('LP_DB_HOST', 'localhost');
define('LP_DB_NAME', 'atelierby_db');
define('LP_DB_USER', 'sam');
define('LP_DB_PASS', 'NhoQyQbKRSPh4Ubg3sR7DMjs5');

try {
    $pdo = new PDO("mysql:host=".LP_DB_HOST.";dbname=".LP_DB_NAME.";charset=utf8mb4",
        LP_DB_USER, LP_DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS lp_footer_links (
            id        INT UNSIGNED  NOT NULL AUTO_INCREMENT,
            col       TINYINT       NOT NULL DEFAULT 1,
            position  TINYINT       NOT NULL DEFAULT 0,
            label_fr  VARCHAR(80)   NOT NULL DEFAULT '',
            label_nl  VARCHAR(80)   NOT NULL DEFAULT '',
            url       VARCHAR(255)  NOT NULL DEFAULT '',
            is_active TINYINT(1)    NOT NULL DEFAULT 1,
            updated_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_col (col, position)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $exists = $pdo->query('SELECT COUNT(*) FROM lp_footer_links')->fetchColumn();
    if (!$exists) {
        $stmt = $pdo->prepare(
            'INSERT INTO lp_footer_links (col, position, label_fr, label_nl, url, is_active)
             VALUES (:col, :pos, :fr, :nl, :url, 1)'
        );
        $rows = [
            // Colonne 1 — Explorer
            [1, 0, 'Nos boutiques', 'Onze winkels',  'index.html#boutiques'],
            [1, 1, 'Produits',      'Producten',      'index.html#produits'],
            [1, 2, 'Expériences',   'Ervaringen',     'index.html#experiences'],
            // Colonne 2 — Services
            [2, 0, 'Click & Collect',   'Click & Collect',      'index.html#experiences'],
            [2, 1, 'Livraison bureau',  'Levering op kantoor',  'index.html#experiences'],
            [2, 2, 'Magasin en ligne',  'Webshop',              'index.html#produits'],
            // Colonne 3 — La Maison
            [3, 0, 'Franchise',        'Franchise',           'franchise-lead.html'],
            [3, 1, 'Galette des rois', 'Driekoningentaart',   'galette-des-rois.html'],
        ];
        foreach ($rows as $r) {
            $stmt->execute([':col'=>$r[0],':pos'=>$r[1],':fr'=>$r[2],':nl'=>$r[3],':url'=>$r[4]]);
        }
    }

    echo '<p style="font-family:monospace;color:green">✓ Table lp_footer_links créée et initialisée (8 liens). Supprimez ce fichier.</p>';
    echo '<p style="font-family:monospace;color:#555">→ is_active=0 masque un lien. Une colonne sans lien actif disparaît. Modifiez url pour la redirection.</p>';
} catch (PDOException $e) {
    echo '<p style="font-family:monospace;color:red">✗ ' . htmlspecialchars($e->getMessage()) . '</p>';
}
