<?php
/**
 * lp_install_legal.php — Crée lp_legal (données légales de l'entreprise) + ligne vide
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
        CREATE TABLE IF NOT EXISTS lp_legal (
            id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
            company_name   VARCHAR(150) NOT NULL DEFAULT '',
            legal_form     VARCHAR(100) NOT NULL DEFAULT '',
            address        VARCHAR(255) NOT NULL DEFAULT '',
            bce_number     VARCHAR(40)  NOT NULL DEFAULT '',
            vat_number     VARCHAR(40)  NOT NULL DEFAULT '',
            editor_name    VARCHAR(120) NOT NULL DEFAULT '',
            contact_email  VARCHAR(120) NOT NULL DEFAULT '',
            contact_phone  VARCHAR(40)  NOT NULL DEFAULT '',
            host_name      VARCHAR(150) NOT NULL DEFAULT '',
            host_address   VARCHAR(255) NOT NULL DEFAULT '',
            host_contact   VARCHAR(150) NOT NULL DEFAULT '',
            dpo_email      VARCHAR(120) NOT NULL DEFAULT '',
            data_retention VARCHAR(80)  NOT NULL DEFAULT '',
            payment_methods VARCHAR(255) NOT NULL DEFAULT '',
            jurisdiction   VARCHAR(120) NOT NULL DEFAULT '',
            audience_tool  VARCHAR(120) NOT NULL DEFAULT '',
            updated_date   VARCHAR(40)  NOT NULL DEFAULT '',
            updated_at     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $exists = $pdo->query('SELECT COUNT(*) FROM lp_legal')->fetchColumn();
    if (!$exists) {
        // Ligne unique pré-remplie avec des exemples (à remplacer par vos vraies données)
        $pdo->exec("
            INSERT INTO lp_legal
                (company_name, legal_form, address, bce_number, vat_number,
                 editor_name, contact_email, contact_phone,
                 host_name, host_address, host_contact,
                 dpo_email, data_retention, payment_methods, jurisdiction, audience_tool, updated_date)
            VALUES
                ('L''Atelier By', 'SRL', 'Adresse à compléter', '0XXX.XXX.XXX', 'BE 0XXX.XXX.XXX',
                 'Nom Prénom', 'contact@atelierby.be', '+32 ...',
                 'Hébergeur à compléter', 'Adresse hébergeur', 'contact hébergeur',
                 'privacy@atelierby.be', '3 ans', 'Carte bancaire, Bancontact', 'Bruxelles', '', '2026')
        ");
    }

    echo '<p style="font-family:monospace;color:green">✓ Table lp_legal créée (1 ligne d''exemple). Modifiez-la dans PHPMyAdmin avec vos vraies données. Supprimez ce fichier.</p>';
} catch (PDOException $e) {
    echo '<p style="font-family:monospace;color:red">✗ ' . htmlspecialchars($e->getMessage()) . '</p>';
}
