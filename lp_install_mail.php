<?php
/**
 * lp_install_mail.php — Crée lp_mail_params + seed par défaut
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
        CREATE TABLE IF NOT EXISTS lp_mail_params (
            id                  TINYINT UNSIGNED  NOT NULL AUTO_INCREMENT,

            -- Expéditeur
            from_email          VARCHAR(255) NOT NULL DEFAULT 'noreply@latelierby.be',
            from_name           VARCHAR(100) NOT NULL DEFAULT 'L\\'Atelier By',

            -- Destinataire interne (reçoit les leads)
            notify_email        VARCHAR(255) NOT NULL DEFAULT 'franchise@latelierby.be',
            notify_name         VARCHAR(100) NOT NULL DEFAULT 'Équipe Franchise',
            notify_subject_fr   VARCHAR(255) NOT NULL DEFAULT 'Nouveau lead franchise : {prenom} {nom}',
            notify_subject_nl   VARCHAR(255) NOT NULL DEFAULT 'Nieuwe franchise lead: {prenom} {nom}',

            -- Confirmation candidat
            confirm_subject_fr  VARCHAR(255) NOT NULL DEFAULT 'Votre demande de dossier — L\\'Atelier By',
            confirm_subject_nl  VARCHAR(255) NOT NULL DEFAULT 'Uw franchisedossier aanvraag — L\\'Atelier By',
            confirm_intro_fr    TEXT,
            confirm_intro_nl    TEXT,

            -- SMTP (laisser vide = PHP mail() local)
            smtp_host           VARCHAR(255) DEFAULT NULL,
            smtp_port           SMALLINT UNSIGNED DEFAULT 587,
            smtp_user           VARCHAR(255) DEFAULT NULL,
            smtp_pass           VARCHAR(255) DEFAULT NULL,
            smtp_secure         ENUM('tls','ssl','none') DEFAULT 'tls',

            updated_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // Seed
    $exists = $pdo->query('SELECT COUNT(*) FROM lp_mail_params')->fetchColumn();
    if (!$exists) {
        $pdo->exec("
            INSERT INTO lp_mail_params
                (from_email, from_name, notify_email, notify_name,
                 confirm_intro_fr, confirm_intro_nl)
            VALUES (
                'noreply@latelierby.be',
                'L\\'Atelier By',
                'franchise@latelierby.be',
                'Équipe Franchise',
                'Bonjour {prenom},\n\nNous avons bien reçu votre demande de dossier franchise L\\'Atelier By.\nUn membre de notre équipe vous recontactera sous 48 h.\n\nÀ très bientôt,\nL\\'équipe L\\'Atelier By',
                'Beste {prenom},\n\nWe hebben uw aanvraag voor het L\\'Atelier By franchisedossier goed ontvangen.\nEen lid van ons team neemt binnen 48 u contact met u op.\n\nMet vriendelijke groeten,\nHet team van L\\'Atelier By'
            )
        ");
    }

    echo '<p style="font-family:monospace;color:green">✓ Table lp_mail_params créée et initialisée.<br>Supprimez ce fichier maintenant.</p>';
} catch (PDOException $e) {
    echo '<p style="font-family:monospace;color:red">✗ ' . htmlspecialchars($e->getMessage()) . '</p>';
}
