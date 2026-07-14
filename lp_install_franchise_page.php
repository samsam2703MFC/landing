<?php
/**
 * lp_install_franchise_page.php — Crée lp_franchise_i18n + lp_franchise_zones + seed
 * SUPPRIMER ce fichier après exécution.
 */
define('LP_DB_HOST', 'localhost');
define('LP_DB_NAME', 'atelierby_db');
define('LP_DB_USER', 'sam');
define('LP_DB_PASS', 'NhoQyQbKRSPh4Ubg3sR7DMjs5');

try {
    $pdo = new PDO("mysql:host=".LP_DB_HOST.";dbname=".LP_DB_NAME.";charset=utf8mb4",
        LP_DB_USER, LP_DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    /* ── 1) Textes (i18n) ─────────────────────────────────── */
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS lp_franchise_i18n (
            i18n_key   VARCHAR(60)  NOT NULL,
            value_fr   VARCHAR(600) NOT NULL DEFAULT '',
            value_nl   VARCHAR(600) NOT NULL DEFAULT '',
            updated_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (i18n_key)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $stmt = $pdo->prepare(
        'INSERT IGNORE INTO lp_franchise_i18n (i18n_key, value_fr, value_nl) VALUES (:k, :fr, :nl)'
    );

    $rows = [
        ['nav.model',   'Le modèle', 'Het model'],
        ['nav.profil',  'Profil',    'Profiel'],
        ['nav.prereq',  'Prérequis', 'Vereisten'],
        ['nav.contact', 'Contact',   'Contact'],

        ['hero.eyebrow', 'Franchise', 'Franchise'],
        ['hero.title',   'Ouvrez votre <span class="script">boutique.</span>', 'Open uw eigen <span class="script">zaak.</span>'],
        ['hero.lede',    'Un modèle clé en main, rentable et entièrement accompagné — même sans expérience en boulangerie.', 'Een kant-en-klaar model, rendabel en volledig begeleid — zelfs zonder ervaring in de bakkerij.'],
        ['hero.b1',      'Production centralisée : vous vendez, nous produisons.', 'Gecentraliseerde productie: u verkoopt, wij produceren.'],
        ['hero.b2',      "Accompagnement de l'emplacement à l'ouverture.", 'Begeleiding van locatie tot opening.'],
        ['hero.b3',      'Rentabilité estimée dès la deuxième année.', 'Rendabiliteit verwacht vanaf het tweede jaar.'],

        ['profil.eyebrow', 'Profil recherché', 'Gezocht profiel'],
        ['profil.h2',      'Ce que nous recherchons', 'Wat wij zoeken'],
        ['profil.intro',   "Chez L'Atelier By, nous recherchons des entrepreneurs :", "Bij L'Atelier By zoeken we ondernemers die:"],
        ['profil.c1',      'motivés et rayonnants', 'gemotiveerd en stralend zijn'],
        ['profil.c2',      'autonomes', 'zelfstandig zijn'],
        ['profil.c3',      'investis dans un projet stimulant', 'zich inzetten voor een boeiend project'],
        ['profil.c4',      'courageux', 'moedig zijn'],
        ['profil.c5',      "sensibles au détail et aux normes d'hygiène", 'oog hebben voor detail en hygiëne'],
        ['profil.c6',      'capables de gérer une petite équipe', 'een klein team kunnen leiden'],

        ['prereq.eyebrow', 'Prérequis', 'Vereisten'],
        ['prereq.h2',      "Ce dont vous avez besoin — et ce dont vous n'avez pas besoin", 'Wat u nodig hebt — en wat niet'],
        ['prereq.yes',     'Nécessaire', 'Vereist'],
        ['prereq.y1',      'Un certificat de gestion', 'Een getuigschrift bedrijfsbeheer'],
        ['prereq.y2',      "L'envie d'entreprendre et de gérer une équipe", 'Zin om te ondernemen en een team te leiden'],
        ['prereq.y3',      'Une expérience commerciale<small>Un atout, pas une obligation</small>', 'Commerciële ervaring<small>Een troef, geen verplichting</small>'],
        ['prereq.no',      'Pas nécessaire', 'Niet nodig'],
        ['prereq.n1',      'Aucune formation en boulangerie', 'Geen bakkersopleiding'],
        ['prereq.n2',      'Aucune expérience du métier<small>Nous vous formons sur le terrain</small>', 'Geen ervaring in het vak<small>Wij leiden u op in de praktijk</small>'],
        ['prereq.note',    '<b>Ne pas être boulanger est même un avantage.</b> Vous vous concentrez sur le commerce et vos clients ; nous produisons.', '<b>Geen bakker zijn is zelfs een voordeel.</b> U richt zich op de winkel en uw klanten; wij produceren.'],

        ['contact.eyebrow', 'Contact', 'Contact'],
        ['contact.h2',      'Recevez le dossier complet', 'Ontvang het volledige dossier'],
        ['contact.lede',    "Laissez-nous vos coordonnées : nous vous envoyons le dossier de franchise et un membre de l'équipe vous recontacte.", 'Laat uw gegevens achter: we sturen u het franchisedossier en een teamlid neemt contact met u op.'],

        ['form.firstname',       'Prénom', 'Voornaam'],
        ['form.lastname',        'Nom', 'Naam'],
        ['form.email',           'E-mail', 'E-mail'],
        ['form.emailPh',         'vous@exemple.be', 'u@voorbeeld.be'],
        ['form.phone',           'Téléphone', 'Telefoon'],
        ['form.zone',            'Zone géographique souhaitée', 'Gewenste regio'],
        ['form.zonePlaceholder', 'Choisir une zone…', 'Kies een regio…'],
        ['form.submit',          'Demande de contact', 'Contactaanvraag'],
        ['form.note',            'Sans engagement. Vos données restent confidentielles et ne sont jamais partagées.', 'Vrijblijvend. Uw gegevens blijven vertrouwelijk en worden nooit gedeeld.'],

        ['modal.eyebrow', 'Merci', 'Bedankt'],
        ['modal.title',   'Votre demande est bien reçue.', 'Uw aanvraag is goed ontvangen.'],
        ['modal.body',    "Nous vous envoyons le dossier de franchise par e-mail sous 48 h, et un membre de l'équipe vous recontacte pour en parler.", 'We sturen u het franchisedossier binnen 48 u per e-mail, en een teamlid neemt contact op om het te bespreken.'],
        ['modal.back',    'Retour à l\'accueil <span class="ar">→</span>', 'Terug naar home <span class="ar">→</span>'],

        ['footer.tag',        'Maison de pains et viennoiseries — Belgique, depuis 2019.', 'Huis van brood en viennoiserie — België, sinds 2019.'],
        ['footer.explore',    'Explorer', 'Ontdekken'],
        ['footer.shops',      'Nos boutiques', 'Onze winkels'],
        ['footer.products',   'Produits', 'Producten'],
        ['footer.experiences','Expériences', 'Ervaringen'],
        ['footer.services',   'Services', 'Diensten'],
        ['footer.collect',    'Click & Collect', 'Click & Collect'],
        ['footer.office',     'Livraison bureau', 'Levering op kantoor'],
        ['footer.online',     'Magasin en ligne', 'Webshop'],
        ['footer.house',      'La Maison', 'Het huis'],
        ['footer.franchise',  'Franchise', 'Franchise'],
        ['footer.galette',    'Galette des rois', 'Driekoningentaart'],
        ['footer.copyright',  '© 2026 L\'Atelier By — Tous droits réservés.', '© 2026 L\'Atelier By — Alle rechten voorbehouden.'],
        ['footer.legal',      'Mentions légales · Confidentialité · Conditions', 'Wettelijke vermeldingen · Privacy · Voorwaarden'],
        ['footer.totop',      'Haut de page ↑', 'Naar boven ↑'],

        ['doc.title', "L'Atelier By — Ouvrez votre franchise", "L'Atelier By — Open uw franchise"],
    ];
    foreach ($rows as $r) {
        $stmt->execute([':k' => $r[0], ':fr' => $r[1], ':nl' => $r[2]]);
    }

    /* ── 2) Zones géographiques ───────────────────────────── */
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS lp_franchise_zones (
            id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
            lang        ENUM('fr','nl') NOT NULL,
            group_label VARCHAR(120) NOT NULL DEFAULT '',
            group_pos   TINYINT      NOT NULL DEFAULT 0,
            value       VARCHAR(80)  NOT NULL DEFAULT '',
            label       VARCHAR(120) NOT NULL DEFAULT '',
            pos         TINYINT      NOT NULL DEFAULT 0,
            is_active   TINYINT(1)   NOT NULL DEFAULT 1,
            updated_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_lang (lang, group_pos, pos)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $zexists = $pdo->query('SELECT COUNT(*) FROM lp_franchise_zones')->fetchColumn();
    if (!$zexists) {
        $zstmt = $pdo->prepare(
            'INSERT INTO lp_franchise_zones (lang, group_label, group_pos, value, label, pos)
             VALUES (:lang, :gl, :gp, :val, :lbl, :p)'
        );
        // [lang, groupLabel, groupPos, [[value,label], ...]]
        $zones = [
            ['fr', 'Région de Bruxelles-Capitale', 0, [['bruxelles-capitale','Bruxelles-Capitale']]],
            ['fr', 'Brabant wallon', 1, [['nivelles','Nivelles']]],
            ['fr', 'Hainaut', 2, [['ath','Ath'],['charleroi','Charleroi'],['la-louviere','La Louvière'],['mons','Mons'],['mouscron','Mouscron'],['soignies','Soignies'],['thuin','Thuin'],['tournai','Tournai']]],
            ['fr', 'Liège', 3, [['huy','Huy'],['liege','Liège'],['verviers','Verviers'],['waremme','Waremme']]],
            ['fr', 'Luxembourg', 4, [['arlon','Arlon'],['bastogne','Bastogne'],['marche-en-famenne','Marche-en-Famenne'],['neufchateau','Neufchâteau'],['virton','Virton']]],
            ['fr', 'Namur', 5, [['dinant','Dinant'],['namur','Namur'],['philippeville','Philippeville']]],

            ['nl', 'Brussels Hoofdstedelijk Gewest', 0, [['brussel','Brussel']]],
            ['nl', 'Vlaams-Brabant', 1, [['halle-vilvoorde','Halle-Vilvoorde'],['leuven','Leuven']]],
            ['nl', 'Antwerpen', 2, [['antwerpen','Antwerpen'],['mechelen','Mechelen'],['turnhout','Turnhout']]],
            ['nl', 'Limburg', 3, [['hasselt','Hasselt'],['maaseik','Maaseik'],['tongeren','Tongeren']]],
            ['nl', 'West-Vlaanderen', 4, [['brugge','Brugge'],['kortrijk','Kortrijk'],['diksmuide','Diksmuide'],['veurne','Veurne'],['oostende','Oostende'],['roeselare','Roeselare'],['tielt','Tielt'],['ieper','Ieper']]],
            ['nl', 'Oost-Vlaanderen', 5, [['aalst','Aalst'],['oudenaarde','Oudenaarde'],['eeklo','Eeklo'],['gent','Gent'],['sint-niklaas','Sint-Niklaas'],['dendermonde','Dendermonde']]],
        ];
        foreach ($zones as $grp) {
            $p = 0;
            foreach ($grp[3] as $opt) {
                $zstmt->execute([
                    ':lang' => $grp[0], ':gl' => $grp[1], ':gp' => $grp[2],
                    ':val'  => $opt[0], ':lbl' => $opt[1], ':p' => $p++,
                ]);
            }
        }
    }

    echo '<p style="font-family:monospace;color:green">✓ Tables lp_franchise_i18n + lp_franchise_zones créées et initialisées. Supprimez ce fichier.</p>';
} catch (PDOException $e) {
    echo '<p style="font-family:monospace;color:red">✗ ' . htmlspecialchars($e->getMessage()) . '</p>';
}
