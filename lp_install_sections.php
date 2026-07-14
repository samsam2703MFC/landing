<?php
/**
 * lp_install_sections.php — Crée lp_sections + seed par défaut
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
        CREATE TABLE IF NOT EXISTS lp_sections (
            section_key VARCHAR(40)  NOT NULL,
            eyebrow_fr  VARCHAR(150) NOT NULL DEFAULT '',
            eyebrow_nl  VARCHAR(150) NOT NULL DEFAULT '',
            title_fr    VARCHAR(255) NOT NULL DEFAULT '',
            title_nl    VARCHAR(255) NOT NULL DEFAULT '',
            lede_fr     TEXT,
            lede_nl     TEXT,
            updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (section_key)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $exists = $pdo->query('SELECT COUNT(*) FROM lp_sections')->fetchColumn();
    if (!$exists) {
        $stmt = $pdo->prepare(
            'INSERT INTO lp_sections (section_key, eyebrow_fr, eyebrow_nl, title_fr, title_nl, lede_fr, lede_nl)
             VALUES (:key, :efr, :enl, :tfr, :tnl, :lfr, :lnl)'
        );
        $rows = [
            ['produits',
             'Les produits',    'De producten',
             'Cinq familles de produits', 'Vijf productfamilies',
             'Façonné, cuit et servi sur place chaque jour.',
             'Elke dag ter plaatse gevormd, gebakken en geserveerd.'],
            ['collaborations',
             'Éditions',        'Edities',
             'Collaborations',  'Samenwerkingen',
             'Des pièces co-signées avec des artisans, chocolatiers et chefs — introuvables ailleurs.',
             'Stukken samen ontworpen met ambachtslieden, chocolatiers en chefs — nergens anders te vinden.'],
            ['saison',
             'Édition limitée', 'Beperkte editie',
             'Éditions limitées de saison', 'Beperkte seizoensedities',
             'Disponibles tant que la matière première dure : quelques jours, parfois quelques semaines.',
             'Verkrijgbaar zolang de grondstof strekt: enkele dagen, soms enkele weken.'],
            ['boutiques',
             'Nos adresses',    'Onze adressen',
             '',                '',
             'De Bruges à Liège — boutiques et pop-ups. Choisissez la plus proche.',
             'Van Brugge tot Luik — winkels en pop-ups. Kies de dichtstbijzijnde.'],
        ];
        foreach ($rows as $r) {
            $stmt->execute([':key'=>$r[0],':efr'=>$r[1],':enl'=>$r[2],':tfr'=>$r[3],':tnl'=>$r[4],':lfr'=>$r[5],':lnl'=>$r[6]]);
        }
    }

    echo '<p style="font-family:monospace;color:green">✓ Table lp_sections créée et initialisée. Supprimez ce fichier.</p>';
} catch (PDOException $e) {
    echo '<p style="font-family:monospace;color:red">✗ ' . htmlspecialchars($e->getMessage()) . '</p>';
}
