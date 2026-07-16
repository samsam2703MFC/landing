<?php
/**
 * lp_od_install.php — Crée et initialise les tables `lp_od_*`
 * (Office Delivery / Livraison Bureau) qui pilotent la page
 * livraison-bureau.html, entièrement depuis la base de données.
 *
 * Visitez une fois : http://185.180.206.46/landing/lp_od_install.php
 * puis SUPPRIMEZ ce fichier (il est public).
 *
 * Convention identique au reste du site : colonnes _fr/_nl bilingues,
 * is_active masque une ligne, position/priority ordonne.
 * Le footer reste piloté par lp_footer_links + lp_i18n (ft.*), partagé
 * avec le reste du site — rien n'est dupliqué ici.
 */
define('LP_DB_HOST', 'localhost');
define('LP_DB_NAME', 'atelierby_db');
define('LP_DB_USER', 'sam');
define('LP_DB_PASS', 'NhoQyQbKRSPh4Ubg3sR7DMjs5');

header('Content-Type: text/html; charset=utf-8');

function ok($m){ echo '<p style="font-family:monospace;color:green;margin:2px 0">✓ '.htmlspecialchars($m).'</p>'; }
function info($m){ echo '<p style="font-family:monospace;color:#555;margin:2px 0">→ '.htmlspecialchars($m).'</p>'; }

try {
    $pdo = new PDO(
        'mysql:host='.LP_DB_HOST.';dbname='.LP_DB_NAME.';charset=utf8mb4',
        LP_DB_USER, LP_DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // ── Schéma ──────────────────────────────────────────────────
    $pdo->exec("CREATE TABLE IF NOT EXISTS lp_od_i18n (
        i18n_key  VARCHAR(64) NOT NULL,
        value_fr  TEXT NOT NULL,
        value_nl  TEXT NOT NULL,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (i18n_key)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS lp_od_list (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        list_key VARCHAR(32) NOT NULL,
        position TINYINT NOT NULL DEFAULT 0,
        value_fr VARCHAR(255) NOT NULL DEFAULT '',
        value_nl VARCHAR(255) NOT NULL DEFAULT '',
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        PRIMARY KEY (id), KEY idx_list (list_key, position)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS lp_od_shops (
        sid INT NOT NULL,
        name VARCHAR(80) NOT NULL DEFAULT '',
        city VARCHAR(80) NOT NULL DEFAULT '',
        email VARCHAR(160) NOT NULL DEFAULT '',
        is_system TINYINT(1) NOT NULL DEFAULT 0,
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        PRIMARY KEY (sid)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS lp_od_zones (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        shop_id INT NOT NULL,
        region VARCHAR(8) NOT NULL DEFAULT '',
        zone_name VARCHAR(120) NOT NULL DEFAULT '',
        city VARCHAR(80) NOT NULL DEFAULT '',
        cutoff_time VARCHAR(8) NOT NULL DEFAULT '',
        days VARCHAR(64) NOT NULL DEFAULT '',      /* ex: lun,mar,mer */
        slots VARCHAR(255) NOT NULL DEFAULT '',    /* ex: 07:30 – 09:00|09:00 – 10:00 */
        min_qty INT NOT NULL DEFAULT 0,
        note_fr VARCHAR(255) NOT NULL DEFAULT '',
        note_nl VARCHAR(255) NOT NULL DEFAULT '',
        priority TINYINT NOT NULL DEFAULT 1,
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        PRIMARY KEY (id), KEY idx_region (region, priority)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS lp_od_usecases (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        position TINYINT NOT NULL DEFAULT 0,
        name_fr VARCHAR(120) NOT NULL DEFAULT '',
        name_nl VARCHAR(120) NOT NULL DEFAULT '',
        desc_fr VARCHAR(400) NOT NULL DEFAULT '',
        desc_nl VARCHAR(400) NOT NULL DEFAULT '',
        icon_path VARCHAR(160) NOT NULL DEFAULT '',
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        PRIMARY KEY (id), KEY idx_pos (position)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS lp_od_steps (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        position TINYINT NOT NULL DEFAULT 0,
        title_fr VARCHAR(160) NOT NULL DEFAULT '',
        title_nl VARCHAR(160) NOT NULL DEFAULT '',
        desc_fr VARCHAR(400) NOT NULL DEFAULT '',
        desc_nl VARCHAR(400) NOT NULL DEFAULT '',
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        PRIMARY KEY (id), KEY idx_pos (position)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS lp_od_offers (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        position TINYINT NOT NULL DEFAULT 0,
        name_fr VARCHAR(120) NOT NULL DEFAULT '',
        name_nl VARCHAR(120) NOT NULL DEFAULT '',
        desc_fr VARCHAR(400) NOT NULL DEFAULT '',
        desc_nl VARCHAR(400) NOT NULL DEFAULT '',
        icon_path VARCHAR(160) NOT NULL DEFAULT '',
        includes_fr VARCHAR(500) NOT NULL DEFAULT '',  /* items séparés par | */
        includes_nl VARCHAR(500) NOT NULL DEFAULT '',
        audience_fr VARCHAR(160) NOT NULL DEFAULT '',
        audience_nl VARCHAR(160) NOT NULL DEFAULT '',
        setup_fr VARCHAR(160) NOT NULL DEFAULT '',
        setup_nl VARCHAR(160) NOT NULL DEFAULT '',
        is_recurring TINYINT(1) NOT NULL DEFAULT 0,
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        PRIMARY KEY (id), KEY idx_pos (position)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS lp_od_products (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        position TINYINT NOT NULL DEFAULT 0,
        title_fr VARCHAR(120) NOT NULL DEFAULT '',
        title_nl VARCHAR(120) NOT NULL DEFAULT '',
        note_fr VARCHAR(400) NOT NULL DEFAULT '',
        note_nl VARCHAR(400) NOT NULL DEFAULT '',
        image_path VARCHAR(160) NOT NULL DEFAULT '',
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        PRIMARY KEY (id), KEY idx_pos (position)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS lp_od_specs (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        position TINYINT NOT NULL DEFAULT 0,
        value VARCHAR(32) NOT NULL DEFAULT '',
        label_fr VARCHAR(160) NOT NULL DEFAULT '',
        label_nl VARCHAR(160) NOT NULL DEFAULT '',
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        PRIMARY KEY (id), KEY idx_pos (position)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS lp_od_testimonials (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        position TINYINT NOT NULL DEFAULT 0,
        quote_fr VARCHAR(600) NOT NULL DEFAULT '',
        quote_nl VARCHAR(600) NOT NULL DEFAULT '',
        author VARCHAR(80) NOT NULL DEFAULT '',
        company_fr VARCHAR(160) NOT NULL DEFAULT '',
        company_nl VARCHAR(160) NOT NULL DEFAULT '',
        initial VARCHAR(4) NOT NULL DEFAULT '',
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        PRIMARY KEY (id), KEY idx_pos (position)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS lp_od_faqs (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        position TINYINT NOT NULL DEFAULT 0,
        question_fr VARCHAR(300) NOT NULL DEFAULT '',
        question_nl VARCHAR(300) NOT NULL DEFAULT '',
        answer_fr VARCHAR(800) NOT NULL DEFAULT '',
        answer_nl VARCHAR(800) NOT NULL DEFAULT '',
        zone_link TINYINT(1) NOT NULL DEFAULT 0,
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        PRIMARY KEY (id), KEY idx_pos (position)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS lp_od_form_fields (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        position TINYINT NOT NULL DEFAULT 0,
        field_key VARCHAR(32) NOT NULL DEFAULT '',
        field_type VARCHAR(16) NOT NULL DEFAULT 'text',  /* text|email|tel|number|zone|freq|textarea|checkbox */
        col_span TINYINT NOT NULL DEFAULT 1,             /* 1 ou 2 */
        required TINYINT(1) NOT NULL DEFAULT 0,
        depends VARCHAR(16) NOT NULL DEFAULT '',         /* '' ou 'zone0' */
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        PRIMARY KEY (id), KEY idx_pos (position)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS lp_od_rollout (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        position TINYINT NOT NULL DEFAULT 0,
        title_fr VARCHAR(160) NOT NULL DEFAULT '',
        title_nl VARCHAR(160) NOT NULL DEFAULT '',
        desc_fr VARCHAR(400) NOT NULL DEFAULT '',
        desc_nl VARCHAR(400) NOT NULL DEFAULT '',
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        PRIMARY KEY (id), KEY idx_pos (position)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    ok('Tables lp_od_* créées (13).');

    // ── Helper d'insertion idempotente ──────────────────────────
    $seedTable = function(string $table, array $cols, array $rows) use ($pdo) {
        $count = (int)$pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
        if ($count > 0) { info("$table déjà peuplée ($count lignes) — ignorée."); return; }
        $ph = implode(',', array_map(fn($c) => ':'.$c, $cols));
        $stmt = $pdo->prepare("INSERT INTO $table (".implode(',', $cols).") VALUES ($ph)");
        foreach ($rows as $r) {
            $params = [];
            foreach ($cols as $i => $c) $params[':'.$c] = $r[$i];
            $stmt->execute($params);
        }
        ok("$table initialisée (".count($rows)." lignes).");
    };

    // ── i18n (scalaires) ────────────────────────────────────────
    $i18n = [
        // nav
        ['navZone','Votre zone','Uw zone'],
        ['navProcess','Comment ça marche','Hoe het werkt'],
        ['navFormules','Les formules','De formules'],
        ['navProduits','Nos produits','Onze producten'],
        ['navFaq','FAQ','FAQ'],
        ['ctaHeader','Être contacté','Contact opnemen'],
        // hero
        ['heroEyebrow','Livraison au bureau','Levering op kantoor'],
        ['heroTitle','La boulangerie qui vient','De bakkerij die naar'],
        ['heroScript','au bureau.','kantoor komt.'],
        ['heroLede',"Petits-déjeuners, pauses et lunchs faits maison, déposés à votre équipe le matin. En ponctuel ou chaque semaine — c'est votre boutique de quartier qui livre.","Ontbijt, pauzes en lunches, huisgemaakt en 's ochtends bij uw team geleverd. Eenmalig of elke week — uw buurtwinkel levert."],
        ['heroCta1','Organiser les livraisons de mon bureau','De leveringen voor mijn kantoor regelen'],
        ['heroCta2','Voir comment ça marche','Bekijk hoe het werkt'],
        ['heroNote',"Ceci n'est pas qu'une boulangerie — c'est votre pause du matin, livrée.",'Dit is niet zomaar een bakkerij — het is uw ochtendpauze, geleverd.'],
        // zone
        ['zoneEyebrow','Votre zone pilote tout','Uw zone bepaalt alles'],
        ['zoneTitle','Où livrons-nous ?','Waar leveren we?'],
        ['zoneLede','Nos jours, horaires et créneaux dépendent de la boutique qui vous livre. Choisissez votre zone : vous verrez aussitôt vos modalités exactes.','Onze dagen, uren en tijdvakken hangen af van de winkel die u belevert. Kies uw zone: u ziet meteen uw exacte voorwaarden.'],
        ['zoneSelectLabel','Sélectionnez votre zone de livraison','Selecteer uw leveringszone'],
        ['zoneChoose','Choisir une zone…','Kies een zone…'],
        ['zoneOutOption',"Ma zone n'est pas dans la liste",'Mijn zone staat niet in de lijst'],
        ['zoneGeneric',"Chaque boutique du réseau organise ses propres tournées. Indiquez votre zone : les jours livrés, l'heure limite de commande, les créneaux et le minimum s'afficheront ici — aucun chiffre générique qui serait faux ailleurs.",'Elke winkel van het netwerk organiseert zijn eigen rondes. Geef uw zone op: de leverdagen, het uiterste besteltijdstip, de tijdvakken en het minimum verschijnen hier — geen algemeen cijfer dat elders onjuist zou zijn.'],
        ['zoneLivePar','Livré par','Geleverd door'],
        ['lblDays','Jours livrés','Leverdagen'],
        ['lblCutoff','Heure limite de commande','Uiterste besteltijd'],
        ['lblSlots','Créneaux de livraison','Leveringstijdvakken'],
        ['lblMin','Minimum de commande','Minimum bestelling'],
        ['zoneCta','Organiser mes livraisons dans cette zone','Mijn leveringen in deze zone regelen'],
        ['cutoffSuffix',', la veille',', de dag ervoor'],
        ['minSuffix','personnes min.','personen min.'],
        ['zoneOutTitle',"Votre zone n'est pas encore couverte ?",'Uw zone is nog niet gedekt?'],
        ['zoneOutBody','Laissez-nous votre commune : chaque demande hors zone nous aide à cartographier où étendre nos tournées.','Laat ons uw gemeente weten: elke aanvraag buiten zone helpt ons in kaart te brengen waar we onze rondes uitbreiden.'],
        ['zoneOutLabel','Code postal / commune','Postcode / gemeente'],
        ['zoneOutPh','ex. 1300 Wavre','bv. 1300 Wavre'],
        ['zoneOutCta','Enregistrer ma demande','Mijn aanvraag registreren'],
        ['shopHintSys','Demande dirigée vers la direction opérationnelle.','Aanvraag doorgestuurd naar de operationele directie.'],
        ['shopHintPrefix','Livré par la boutique de','Geleverd door de winkel van'],
        // use cases
        ['ucEyebrow','Pour qui, pour quoi','Voor wie, waarvoor'],
        ['ucTitle',"Chaque moment d'équipe a sa formule.",'Elk teammoment heeft zijn formule.'],
        ['ucLede',"Que vous soyez cinq ou quatre-vingts, on s'occupe des pauses qui font du bien au bureau.",'Of u nu met vijf of tachtig bent, wij zorgen voor de pauzes die deugd doen op kantoor.'],
        // process
        ['procEyebrow','Comment ça marche','Hoe het werkt'],
        ['procTitle','Quatre étapes, zéro gestion.','Vier stappen, nul beheer.'],
        ['stepZoneHint','Choisir ma zone pour voir mes modalités →','Kies mijn zone om mijn voorwaarden te zien →'],
        ['stepBefore','avant','vóór'],
        // offers
        ['offEyebrow','Les formules','De formules'],
        ['offTitle','Des compositions pensées pour le bureau.','Samenstellingen bedacht voor kantoor.'],
        ['offLede','Votre boutique adapte les quantités et la mise en place. On vous propose une formule, jamais un catalogue à trier.','Uw winkel past de hoeveelheden en de presentatie aan. Wij stellen een formule voor, nooit een catalogus om uit te zoeken.'],
        ['offerBadge','Dispo en hebdo','Wekelijks beschikbaar'],
        ['lblFor','Pour :','Voor:'],
        ['lblSetup','Mise en place :','Presentatie:'],
        // recurrence
        ['recEyebrow','Récurrence','Herhaling'],
        ['recTitle','Envie de régularité ? Passez en hebdo.','Zin in regelmaat? Ga wekelijks.'],
        ['recBody',"Un abonnement hebdomadaire pour vos formules éligibles. Vous gardez la main : modifiez, mettez en pause ou annulez d'une semaine à l'autre, sans engagement.",'Een wekelijks abonnement voor uw in aanmerking komende formules. U houdt de controle: wijzig, pauzeer of annuleer van week tot week, zonder verbintenis.'],
        // products
        ['prodEyebrow','Nos produits','Onze producten'],
        ['prodTitle','Frais du jour, sans exception.','Elke dag vers, zonder uitzondering.'],
        ['prodLede',"Dans tout le réseau L'Atelier By, tout est préparé et livré le jour même. Nos salades sont composées et assaisonnées à la main, chaque matin en boutique.","In het hele netwerk van L'Atelier By wordt alles dezelfde dag bereid en geleverd. Onze salades worden elke ochtend met de hand samengesteld en op smaak gebracht in de winkel."],
        ['prodBadge','Frais du jour','Vers van de dag'],
        // proof
        ['clientsLabel','Ils font livrer leurs équipes','Zij laten hun teams beleveren'],
        // faq
        ['faqEyebrow','Questions fréquentes','Veelgestelde vragen'],
        ['faqTitle',"Tout ce qu'il faut savoir.",'Alles wat u moet weten.'],
        ['faqLinkText',' Choisir ma zone →',' Kies mijn zone →'],
        // mise en œuvre / déploiement
        ['mepEyebrow','Mise en place','Ingebruikname'],
        ['mepTitle',"De votre premier contact au déploiement dans vos équipes.",'Van uw eerste contact tot de uitrol in uw teams.'],
        ['mepLede',"On s'occupe de tout le cadrage. Une fois lancé, chacun commande et paie de son côté — vous n'avez plus rien à gérer.",'Wij regelen de volledige opstart. Eenmaal gelanceerd bestelt en betaalt iedereen zelf — u hoeft niets meer te beheren.'],
        ['mepBenefit',"Chaque commande est payée via le compte de chaque collaborateur : aucune avance, aucune centralisation. Vous gagnez un temps précieux.",'Elke bestelling wordt betaald via het account van elke medewerker: geen voorschot, geen centralisatie. U wint kostbare tijd.'],
        ['mepBenefitLabel','Le gain pour vous','Uw voordeel'],
        // contact
        ['cEyebrow','Organiser mes livraisons','Mijn leveringen regelen'],
        ['cTitle',"Dites-nous en un peu plus, on s'occupe du reste.",'Vertel ons wat meer, wij doen de rest.'],
        ['cLede','Pas de commande ni de devis à cette étape : votre boutique de zone vous rappelle pour construire vos livraisons ensemble.','Geen bestelling of offerte in deze stap: uw zonewinkel belt u terug om samen uw leveringen op te bouwen.'],
        // form
        ['freqChoose','Choisir…','Kiezen…'],
        ['consentText',"J'accepte que mes données soient transmises à la boutique L'Atelier By de ma zone pour le traitement de ma demande, conformément à la politique de confidentialité.","Ik ga ermee akkoord dat mijn gegevens worden bezorgd aan de winkel van L'Atelier By in mijn zone voor de behandeling van mijn aanvraag, conform het privacybeleid."],
        ['submitLabel','Envoyer ma demande','Mijn aanvraag versturen'],
        ['formNote',"Aucune commande n'est passée ici. Votre demande est simplement transmise à la boutique de votre zone.",'Hier wordt geen bestelling geplaatst. Uw aanvraag wordt gewoon aan de winkel van uw zone bezorgd.'],
        // errors
        ['err.required','Champ requis.','Verplicht veld.'],
        ['err.email','E-mail invalide.','Ongeldig e-mailadres.'],
        ['err.zone','Sélectionnez votre zone.','Selecteer uw zone.'],
        ['err.postal','Indiquez votre code postal / commune.','Geef uw postcode / gemeente op.'],
        ['err.consent','Merci de confirmer votre consentement.','Bevestig uw toestemming.'],
        // routing / modal
        ['shopSystemName','Direction Opérationnelle','Operationele directie'],
        ['routingSys',"Votre commune n'est pas encore couverte : votre demande part vers notre direction opérationnelle, qui la garde pour étendre nos tournées.",'Uw gemeente is nog niet gedekt: uw aanvraag gaat naar onze operationele directie, die ze bewaart om onze rondes uit te breiden.'],
        ['routingNormalPre','Votre demande est transmise à la boutique de ','Uw aanvraag wordt bezorgd aan de winkel van '],
        ['routingNormalPost',', qui vous recontacte pour organiser vos livraisons.',', die contact met u opneemt om uw leveringen te regelen.'],
        ['mEyebrow','Demande reçue','Aanvraag ontvangen'],
        ['mTitle',"Merci — c'est bien enregistré.",'Bedankt — goed geregistreerd.'],
        ['mShopLabel','Destinataire','Bestemmeling'],
        ['mNotifLabel','Notification','Melding'],
        ['mRefLabel','Référence (shop_id)','Referentie (shop_id)'],
        ['mClose','Fermer','Sluiten'],
        // regions
        ['region.bw','Brabant wallon','Waals-Brabant'],
        ['region.bf','Brabant flamand','Vlaams-Brabant'],
        ['region.nm','Namur','Namen'],
        ['region.eb','Est de Bruxelles','Ten oosten van Brussel'],
        // days
        ['day.lun','Lun','Ma'],['day.mar','Mar','Di'],['day.mer','Mer','Wo'],
        ['day.jeu','Jeu','Do'],['day.ven','Ven','Vr'],['day.sam','Sam','Za'],['day.dim','Dim','Zo'],
        // field labels / placeholders
        ['field.first_name','Prénom','Voornaam'],
        ['field.last_name','Nom','Naam'],
        ['field.company','Société','Bedrijf'],
        ['field.email','E-mail professionnel','Professioneel e-mailadres'],
        ['field.email.ph','vous@societe.be','u@bedrijf.be'],
        ['field.phone','Téléphone','Telefoon'],
        ['field.phone.ph','+32 …','+32 …'],
        ['field.zone_id','Votre zone de livraison','Uw leveringszone'],
        ['field.postal_code','Code postal / commune','Postcode / gemeente'],
        ['field.postal_code.ph','ex. 1300 Wavre','bv. 1300 Wavre'],
        ['field.team_size',"Taille de l'équipe",'Grootte van het team'],
        ['field.team_size.ph','ex. 25','bv. 25'],
        ['field.frequency','Fréquence souhaitée','Gewenste frequentie'],
        ['field.message','Votre message','Uw bericht'],
        ['field.message.ph','Dites-nous en plus sur vos habitudes, vos contraintes…','Vertel ons meer over uw gewoontes, uw beperkingen…'],
    ];
    $seedTable('lp_od_i18n', ['i18n_key','value_fr','value_nl'], $i18n);

    // ── Listes simples ──────────────────────────────────────────
    $lists = [
        // recPoints
        ['recPoints',0,'Livraison hebdomadaire automatique','Automatische wekelijkse levering'],
        ['recPoints',1,'Pause ou annulation en un clic','Pauzeren of annuleren met één klik'],
        ['recPoints',2,'Sans engagement de durée','Zonder looptijdverbintenis'],
        // prodAssure
        ['prodAssure',0,'Tout est frais du jour','Alles vers van de dag'],
        ['prodAssure',1,'Salades faites maison','Salades huisgemaakt'],
        ['prodAssure',2,'Livré le matin même','Dezelfde ochtend geleverd'],
        // contactPoints
        ['contactPoints',0,'Réponse de votre boutique sous 48 h ouvrées','Antwoord van uw winkel binnen 48 werkuren'],
        ['contactPoints',1,"Aucun engagement — on construit vos livraisons ensemble",'Geen verbintenis — we bouwen uw leveringen samen op'],
        ['contactPoints',2,"Vos données ne sont transmises qu'à la boutique de votre zone",'Uw gegevens worden enkel aan de winkel van uw zone bezorgd'],
        // clients (identiques fr/nl)
        ['clients',0,'Novéa','Novéa'],['clients',1,'Wallonis','Wallonis'],['clients',2,'Studio Klein','Studio Klein'],
        ['clients',3,'Groupe Haval','Groupe Haval'],['clients',4,'Praxis','Praxis'],
        // freqOptions
        ['freq',0,'Ponctuel','Eenmalig'],['freq',1,'Hebdomadaire','Wekelijks'],['freq',2,'Mensuel','Maandelijks'],
    ];
    $seedTable('lp_od_list', ['list_key','position','value_fr','value_nl'], $lists);

    // ── Shops (référentiel réseau ; sid=0 = système/direction) ──
    $shops = [
        [0,'','Réseau','direction@latelierby.be',1,1],
        [1,'Halle','Halle','halle@latelierby.be',0,1],
        [2,'Corbais','Corbais','corbais@latelierby.be',0,1],
        [3,'Gosselies','Gosselies','gosselies@latelierby.be',0,1],
        [4,'Sombreffe','Sombreffe','sombreffe@latelierby.be',0,1],
        [5,'Gembloux','Gembloux','gembloux@latelierby.be',0,1],
        [6,'Wavre','Wavre','wavre@latelierby.be',0,1],
    ];
    $seedTable('lp_od_shops', ['sid','name','city','email','is_system','is_active'], $shops);

    // ── Zones ───────────────────────────────────────────────────
    $zones = [
        [6,'bw','Wavre & Rixensart','Wavre','16:00','lun,mar,mer,jeu,ven','07:30 – 09:00|09:00 – 10:00|11:30 – 13:00',6,'Créneau lunch disponible toute la semaine.','Lunchtijdvak de hele week beschikbaar.',1,1],
        [2,'bw','Louvain-la-Neuve & Ottignies','Louvain-la-Neuve','17:00','lun,mar,mer,jeu,ven','07:30 – 09:00|09:00 – 10:00|11:30 – 13:00',6,'Accès campus : badge visiteur requis.','Campustoegang: bezoekersbadge vereist.',2,1],
        [2,'bw',"Nivelles & Braine-l'Alleud",'Nivelles','16:00','mar,jeu,ven','08:00 – 09:30',8,'','',3,1],
        [1,'bf','Halle & environs','Halle','16:00','mar,mer,jeu,ven','07:30 – 09:00|09:00 – 10:30',8,'Samedi sur demande pour les événements.','Zaterdag op aanvraag voor evenementen.',1,1],
        [1,'bf','Beersel – Sint-Pieters-Leeuw','Beersel','15:00','mar,jeu','08:00 – 09:30',10,'','',2,1],
        [5,'nm','Gembloux & campus agro','Gembloux','17:00','mar,mer,jeu,ven','07:30 – 09:00|09:00 – 10:30',6,'','',1,1],
        [4,'nm','Sombreffe – Ligny','Sombreffe','16:30','lun,mer,ven','07:45 – 09:15',8,'Livraison groupée le mercredi.','Gegroepeerde levering op woensdag.',2,1],
        [6,'eb','Overijse – Tervuren','Overijse','15:30','mar,mer,ven','08:00 – 09:30',8,'','',1,1],
        [2,'eb','Auderghem – Woluwe','Auderghem','15:00','mar,jeu','08:00 – 09:30',10,'Accès parking limité : livraison au rez.','Beperkte parking: levering op gelijkvloers.',2,1],
    ];
    $seedTable('lp_od_zones', ['shop_id','region','zone_name','city','cutoff_time','days','slots','min_qty','note_fr','note_nl','priority','is_active'], $zones);

    // ── Use cases ───────────────────────────────────────────────
    $uc = [
        [0,'Réunions & comités','Vergaderingen & comités','De quoi tenir une matinée de travail : viennoiseries, café, eau, sans interrompre le rythme.','Genoeg voor een werkochtend: viennoiserie, koffie, water, zonder het ritme te breken.','img/sandwiches.png',1],
        [1,"Petits-déjeuners d'accueil",'Onthaalontbijten','Recevez clients et partenaires avec un petit-déjeuner qui donne le ton.','Ontvang klanten en partners met een ontbijt dat de toon zet.','img/croissant.png',1],
        [2,'Onboarding & arrivées','Onboarding & nieuwkomers','Marquez le premier jour des nouveaux : une attention simple, chaleureuse, sur le bureau.',"Vier de eerste dag van nieuwe collega's: een eenvoudige, warme attentie op het bureau.",'img/rolls.png',1],
        [3,'Anniversaires & pots','Verjaardagen & recepties','Un moment à fêter ? Cakes, tartes et pièces sucrées, livrés prêts à partager.','Iets te vieren? Cakes, taarten en zoetigheden, klaar om te delen.','img/cupcake.png',1],
        [4,"Vendredis d'équipe",'Teamvrijdagen','Terminez la semaine en douceur avec une pause gourmande récurrente.','Sluit de week zacht af met een terugkerende lekkere pauze.','img/cookies.png',1],
    ];
    $seedTable('lp_od_usecases', ['position','name_fr','name_nl','desc_fr','desc_nl','icon_path','is_active'], $uc);

    // ── Steps ───────────────────────────────────────────────────
    $steps = [
        [0,"Vous nous dites ce qu'il vous faut",'U vertelt ons wat u nodig hebt','Nombre de personnes, type de pause, dates envisagées. En deux minutes.','Aantal personen, type pauze, gewenste data. In twee minuten.',1],
        [1,'Votre boutique vous propose une formule','Uw winkel stelt u een formule voor','Adaptée à votre zone et à ses modalités de livraison.','Afgestemd op uw zone en de leveringsvoorwaarden ervan.',1],
        [2,'Vous validez votre planning','U bevestigt uw planning',"Ponctuel ou récurrent, ajustable d'une semaine à l'autre. Vous gardez la main.",'Eenmalig of terugkerend, week na week aanpasbaar. U houdt de controle.',1],
        [3,'On livre, on facture en fin de mois','Wij leveren, we factureren op het maandeinde',"Livraison au bureau à l'heure convenue. Une seule facture mensuelle, rien à gérer.",'Levering op kantoor op het afgesproken uur. Eén maandelijkse factuur, niets te beheren.',1],
    ];
    $seedTable('lp_od_steps', ['position','title_fr','title_nl','desc_fr','desc_nl','is_active'], $steps);

    // ── Offers ──────────────────────────────────────────────────
    $offers = [
        [0,"Le Petit-Déjeuner d'équipe",'Het Teamontbijt','La corbeille du matin : viennoiseries pur beurre, pains et jus pressé.','De ochtendmand: roomboterviennoiserie, brood en versgeperst sap.','img/croissant.png','Croissants & pains au chocolat|Petits pains & confitures maison|Jus de fruits frais','Croissants & chocoladebroodjes|Pistolets & huisgemaakte confituur|Vers fruitsap','Matinées de travail, réunions','Werkochtenden, vergaderingen','Corbeilles + serviettes incluses','Manden + servetten inbegrepen',1,1],
        [1,'La Pause Gourmande','De Zoete Pauze',"Le petit plaisir de l'après-midi, sucré et réconfortant.",'Het namiddagplezier, zoet en troostend.','img/cookies.png','Cookies & financiers du jour|Cakes tranchés|Café et thé en option','Cookies & financiers van de dag|Gesneden cake|Koffie en thee optioneel','Pauses, fins de journée','Pauzes, einde van de dag','Prêt à partager','Klaar om te delen',1,1],
        [2,'Le Lunch au Bureau','De Lunch op Kantoor','Un déjeuner complet et fait maison, sans sortir du bureau.','Een volledige, huisgemaakte lunch zonder het kantoor te verlaten.','img/sandwiches.png','Sandwiches garnis & wraps|Salades de saison|Tartes salées','Belegde broodjes & wraps|Seizoenssalades|Hartige taarten',"Déjeuners d'équipe, ateliers",'Teamlunches, workshops','Vaisselle & mise en place incluses','Servies & presentatie inbegrepen',0,1],
        [3,'Le Plateau Célébration','Het Feestplateau','Pour marquer le coup : pièces festives et desserts à partager.','Om het moment te vieren: feestelijke stukken en desserts om te delen.','img/cake-slice.png','Tartes sucrées & entremets|Cupcakes décorés|Pièces à partager','Zoete taarten & entremets|Versierde cupcakes|Stukken om te delen','Anniversaires, pots, événements','Verjaardagen, recepties, evenementen','Présentoir & découpe inclus','Presentatie & versnijding inbegrepen',1,1],
    ];
    $seedTable('lp_od_offers', ['position','name_fr','name_nl','desc_fr','desc_nl','icon_path','includes_fr','includes_nl','audience_fr','audience_nl','setup_fr','setup_nl','is_recurring','is_active'], $offers);

    // ── Products ────────────────────────────────────────────────
    $prods = [
        [0,'Plateau de sandwichs','Sandwichschotel','Pains du jour, garnitures fraîches, tranchés et dressés le matin même.','Brood van de dag, verse garnituur, dezelfde ochtend gesneden en gedresseerd.','img/p-sandwich-club.jpg',1],
        [1,'Plateau de fruits','Fruitschotel','Fruits de saison découpés le jour de la livraison, jamais la veille.','Seizoensfruit gesneden op de dag van levering, nooit de dag ervoor.','img/p-chou-farci.jpg',1],
        [2,'Buffet dessert','Dessertbuffet','Tartes, entremets et douceurs préparés en boutique par nos pâtissiers.','Taarten, entremets en zoetigheden bereid in de winkel door onze patissiers.','img/sweet-tart-big.png',1],
        [3,'Salades maison','Huisgemaakte salades','Composées et assaisonnées à la main, chaque matin — recettes maison.','Elke ochtend met de hand samengesteld en op smaak gebracht — eigen recepten.','img/p-salade-chef-saumon.jpg',1],
        [4,'Plateau petit-déjeuner','Ontbijtschotel','Viennoiserie pur beurre et pains, encore tièdes à la livraison.','Roomboterviennoiserie en brood, nog lauw bij levering.','img/p-sand-oeufs-bacon.jpg',1],
    ];
    $seedTable('lp_od_products', ['position','title_fr','title_nl','note_fr','note_nl','image_path','is_active'], $prods);

    // ── Specs ───────────────────────────────────────────────────
    $specs = [
        [0,'80','personnes servies en une seule livraison','personen bediend in één levering',1],
        [1,'6','boutiques artisanales partout en Belgique','ambachtelijke winkels in heel België',1],
        [2,'07:30','au bureau, viennoiseries encore tièdes','op kantoor, viennoiserie nog lauw',1],
        [3,'1','facture mensuelle, zéro gestion','maandelijkse factuur, nul beheer',1],
    ];
    $seedTable('lp_od_specs', ['position','value','label_fr','label_nl','is_active'], $specs);

    // ── Testimonials ────────────────────────────────────────────
    $tst = [
        [0,"Le mardi, toute l'équipe descend à 9h pour les viennoiseries. C'est devenu notre rituel — et je n'ai plus rien à gérer.",'Op dinsdag komt het hele team om 9u naar beneden voor de viennoiserie. Het is ons ritueel geworden — en ik moet niets meer beheren.','Camille D.',"Office Manager — cabinet d'avocats, Wavre",'Office Manager — advocatenkantoor, Wavre','C',1],
        [1,"On a réglé nos petits-déjeuners d'onboarding en cinq minutes. Une facture par mois, livré à l'heure, à chaque fois.",'We regelden onze onboarding-ontbijten in vijf minuten. Eén factuur per maand, telkens op tijd geleverd.','Naïm B.','HR — scale-up tech, Louvain-la-Neuve','HR — tech scale-up, Louvain-la-Neuve','N',1],
    ];
    $seedTable('lp_od_testimonials', ['position','quote_fr','quote_nl','author','company_fr','company_nl','initial','is_active'], $tst);

    // ── FAQ ─────────────────────────────────────────────────────
    $faqs = [
        [0,'Quel est le délai pour commander ?','Wat is de besteltermijn?',"Le délai et l'heure limite dépendent de la boutique qui vous livre.",'De termijn en het uiterste tijdstip hangen af van de winkel die u belevert.',1,1],
        [1,'Puis-je modifier ou annuler une livraison ?','Kan ik een levering wijzigen of annuleren?',"Oui. En récurrent comme en ponctuel, vous ajustez ou annulez d'une semaine à l'autre, dans le respect de l'heure limite de votre zone.",'Ja. Zowel terugkerend als eenmalig past u aan of annuleert u van week tot week, met respect voor het uiterste tijdstip van uw zone.',0,1],
        [2,'Comment gérez-vous les allergènes ?','Hoe gaan jullie om met allergenen?',"La liste complète des allergènes accompagne chaque formule. Signalez vos contraintes à votre boutique : elle adapte la composition quand c'est possible.",'De volledige allergenenlijst hoort bij elke formule. Meld uw beperkingen aan uw winkel: die past de samenstelling aan waar mogelijk.',0,1],
        [3,'Comment se passe la facturation ?','Hoe verloopt de facturatie?','Une seule facture mensuelle regroupe toutes vos livraisons du mois. Pas de paiement à la commande, rien à avancer.','Eén maandelijkse factuur bundelt al uw leveringen van de maand. Geen betaling bij bestelling, niets voor te schieten.',0,1],
        [4,'La TVA est-elle incluse ?','Is de btw inbegrepen?','Tous les montants communiqués par votre boutique incluent la TVA applicable. Votre facture en détaille le taux.','Alle bedragen die uw winkel meedeelt zijn inclusief de toepasselijke btw. Uw factuur vermeldt het tarief.',0,1],
        [5,'Quels moyens de paiement acceptez-vous ?','Welke betaalmethodes aanvaarden jullie?','Le règlement se fait par virement à réception de la facture mensuelle. Les modalités précises vous sont confirmées par votre boutique.','Betaling gebeurt via overschrijving na ontvangst van de maandfactuur. De precieze modaliteiten worden door uw winkel bevestigd.',0,1],
    ];
    $seedTable('lp_od_faqs', ['position','question_fr','question_nl','answer_fr','answer_nl','zone_link','is_active'], $faqs);

    // ── Mise en œuvre / déploiement (5 étapes) ──────────────────
    $rollout = [
        [0,'Vous nous contactez','U neemt contact op','Un formulaire, deux minutes. On identifie aussitôt la boutique qui couvre votre zone.','Eén formulier, twee minuten. We bepalen meteen de winkel die uw zone dekt.',1],
        [1,'Le responsable de boutique prend rendez-vous','De winkelverantwoordelijke maakt een afspraak','Votre interlocuteur local vous rappelle et fixe un moment pour parler de vos besoins.','Uw lokale contactpersoon belt u terug en plant een moment om uw behoeften te bespreken.',1],
        [2,'On discute des modalités','We bespreken de voorwaarden','Jours, créneaux, formules, budget et fréquence : tout est cadré ensemble, sans engagement.','Dagen, tijdvakken, formules, budget en frequentie: alles wordt samen afgesproken, zonder verbintenis.',1],
        [3,'Préparation de votre voucher de bienvenue','Voorbereiding van uw welkomstvoucher','On configure vos accès et un voucher de bienvenue, prêt à partager avec vos équipes.','We configureren uw toegang en een welkomstvoucher, klaar om met uw teams te delen.',1],
        [4,'Vous déployez dans vos équipes','U rolt het uit naar uw teams','Chaque collaborateur commande et paie via son propre compte. Vous ne gérez rien — vous gagnez du temps.','Elke medewerker bestelt en betaalt via zijn eigen account. U beheert niets — u wint tijd.',1],
    ];
    $seedTable('lp_od_rollout', ['position','title_fr','title_nl','desc_fr','desc_nl','is_active'], $rollout);

    // ── Form fields ─────────────────────────────────────────────
    $fields = [
        [0,'first_name','text',1,1,''],
        [1,'last_name','text',1,1,''],
        [2,'company','text',2,1,''],
        [3,'email','email',1,1,''],
        [4,'phone','tel',1,0,''],
        [5,'zone_id','zone',2,1,''],
        [6,'postal_code','text',2,1,'zone0'],
        [7,'team_size','number',1,0,''],
        [8,'frequency','freq',1,0,''],
        [9,'message','textarea',2,0,''],
        [10,'consent','checkbox',2,1,''],
    ];
    $seedTable('lp_od_form_fields', ['position','field_key','field_type','col_span','required','depends'], $fields);

    echo '<hr><p style="font-family:monospace;color:green;font-weight:bold">✓ Terminé. La page livraison-bureau.html est maintenant pilotée par la base.</p>';
    info('Le footer reste piloté par lp_footer_links + lp_i18n (ft.*), partagé avec le site.');
    info('SUPPRIMEZ ce fichier maintenant (il expose les identifiants DB).');

} catch (PDOException $e) {
    echo '<p style="font-family:monospace;color:red">✗ '.htmlspecialchars($e->getMessage()).'</p>';
}
