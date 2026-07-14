<?php
/**
 * lp_install_i18n.php — Crée lp_i18n (UI chrome fr/nl) + seed
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
        CREATE TABLE IF NOT EXISTS lp_i18n (
            i18n_key   VARCHAR(60)  NOT NULL,
            value_fr   VARCHAR(500) NOT NULL DEFAULT '',
            value_nl   VARCHAR(500) NOT NULL DEFAULT '',
            updated_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (i18n_key)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $stmt = $pdo->prepare(
        'INSERT IGNORE INTO lp_i18n (i18n_key, value_fr, value_nl) VALUES (:k, :fr, :nl)'
    );

    $rows = [
        // Navigation
        ['nav.produits',    'Produits',       'Producten'],
        ['nav.boutiques',   'Boutiques',      'Winkels'],
        ['nav.experiences', 'Expériences',    'Ervaringen'],
        ['nav.franchise',   'Franchise',      'Franchise'],
        ['order',           'Commander',      'Bestellen'],

        // Filtres boutiques
        ['filter.all',   'Toutes',    'Alle'],
        ['filter.shop',  'Boutiques', 'Winkels'],
        ['filter.popup', 'Pop-up',    'Pop-up'],

        // Labels cartes et modal boutique
        ['collab.shop',     'Voir dans la boutique',  'Bekijk in de winkel'],
        ['card.more',       'Voir les détails →',     'Bekijk details →'],
        ['badge.popup',     'Pop-up',                 'Pop-up'],
        ['label.services',  'Services',               'Diensten'],
        ['label.contact',   'Contact',                'Contact'],
        ['label.hours',     'Horaires',               'Openingsuren'],
        ['closed',          'Fermé',                  'Gesloten'],

        // Labels services (modal boutique)
        ['svc.collect',  'Click & Collect',         'Click & Collect'],
        ['svc.delivery', 'Livraison',               'Levering'],
        ['svc.catering', 'Traiteur',                'Catering'],
        ['svc.b2b',      'B2B',                     'B2B'],
        ['svc.phone',    'Commande téléphonique',   'Telefonische bestelling'],
        ['svc.office',   'Livraison bureau',        'Levering op kantoor'],
        ['svc.loyalty',  'Programme fidélité',      'Getrouwheidsprogramma'],

        // Jours de la semaine
        ['day.mon', 'Lun', 'Ma'],
        ['day.tue', 'Mar', 'Di'],
        ['day.wed', 'Mer', 'Wo'],
        ['day.thu', 'Jeu', 'Do'],
        ['day.fri', 'Ven', 'Vr'],
        ['day.sat', 'Sam', 'Za'],
        ['day.sun', 'Dim', 'Zo'],

        // Footer
        ['ft.tag',        "Maison de pains et viennoiseries — Belgique, depuis 2019.", 'Huis van brood en viennoiserie — België, sinds 2019.'],
        ['ft.explore',    'Explorer',          'Ontdekken'],
        ['ft.shops',      'Nos boutiques',     'Onze winkels'],
        ['ft.products',   'Produits',          'Producten'],
        ['ft.experiences','Expériences',       'Ervaringen'],
        ['ft.services',   'Services',          'Diensten'],
        ['ft.collect',    'Click & Collect',   'Click & Collect'],
        ['ft.office',     'Livraison bureau',  'Levering op kantoor'],
        ['ft.online',     'Magasin en ligne',  'Webshop'],
        ['ft.house',      'La Maison',         'Het huis'],
        ['ft.franchise',  'Franchise',         'Franchise'],
        ['ft.galette',    'Galette des rois',  'Driekoningentaart'],
        ['ft.copyright',  "© 2026 L'Atelier By — Tous droits réservés.", "© 2026 L'Atelier By — Alle rechten voorbehouden."],
        ['ft.legal',      'Mentions légales · Confidentialité · Conditions', 'Wettelijke vermeldingen · Privacy · Voorwaarden'],
        ['ft.totop',      'Haut de page ↑',   'Naar boven ↑'],

        // Picker webshop
        ['pk.eyebrow',    'Magasin en ligne',       'Webshop'],
        ['pk.title',      'Choisissez votre boutique', 'Kies uw winkel'],
        ['pk.lede',       'Chaque boutique a son propre magasin en ligne. Sélectionnez la vôtre — nous la retiendrons pour vos prochaines commandes.', 'Elke winkel heeft zijn eigen webshop. Kies de uwe — we onthouden ze voor uw volgende bestellingen.'],
        ['pk.geo',        'Trouver la boutique la plus proche', 'Vind de dichtstbijzijnde winkel'],
        ['pk.go',         'Aller au magasin →',     'Naar de webshop →'],
        ['pk.flagMine',   'Ma boutique',            'Mijn winkel'],
        ['pk.flagNear',   'La plus proche',         'Dichtstbij'],
        ['pk.unavail',    "La géolocalisation n'est pas disponible sur cet appareil.", 'Geolocatie is niet beschikbaar op dit toestel.'],
        ['pk.locating',   'Localisation en cours…', 'Locatie bepalen…'],
        ['pk.denied',     'Localisation indisponible. Choisissez votre boutique dans la liste.', 'Locatie niet beschikbaar. Kies uw winkel in de lijst.'],
        ['pk.nearestPre', 'La plus proche : ',      'Dichtstbij: '],
        ['pk.km',         ' km.',                   ' km.'],

        // Modale QR
        ['qr.eyebrow', 'Application mobile',       'Mobiele app'],
        ['qr.title',   'Scannez pour télécharger', 'Scan om te downloaden'],
        ['qr.hint',    "Pointez l'appareil photo de votre téléphone sur le code. Disponible sur iOS et Android.", 'Richt de camera van uw telefoon op de code. Beschikbaar op iOS en Android.'],

        // Titre + description de page (SEO)
        ['doc.title', "L'Atelier By — Maison de pains & viennoiseries", "L'Atelier By — Huis van brood & viennoiserie"],
        ['doc.description',
         "L'Atelier By — Maison de pains et viennoiseries artisanales en Belgique. Fait main, chaque matin.",
         "L'Atelier By — Huis van ambachtelijk brood en viennoiserie in België. Met de hand gemaakt, elke ochtend."],
    ];

    foreach ($rows as $r) {
        $stmt->execute([':k' => $r[0], ':fr' => $r[1], ':nl' => $r[2]]);
    }

    // Ajouter URLs dans lp_params
    try {
        $pdo->exec("INSERT IGNORE INTO lp_params (param_key, param_value) VALUES
            ('galette_url',   'galette-des-rois.html'),
            ('franchise_url', 'franchise-lead.html')
        ");
    } catch (PDOException $e) {}

    $count = $pdo->query('SELECT COUNT(*) FROM lp_i18n')->fetchColumn();
    echo '<p style="font-family:monospace;color:green">✓ Table lp_i18n créée — ' . $count . ' clés insérées. URLs ajoutées dans lp_params. Supprimez ce fichier.</p>';
} catch (PDOException $e) {
    echo '<p style="font-family:monospace;color:red">✗ ' . htmlspecialchars($e->getMessage()) . '</p>';
}
