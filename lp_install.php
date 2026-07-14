<?php
/**
 * lp_install.php — Crée la table lp_candidates
 * SUPPRIMER ce fichier après exécution.
 */
define('LP_DB_HOST', 'localhost');
define('LP_DB_NAME', 'atelierby_db');
define('LP_DB_USER', 'sam');
define('LP_DB_PASS', 'NhoQyQbKRSPh4Ubg3sR7DMjs5');

try {
    $pdo = new PDO(
        "mysql:host=".LP_DB_HOST.";dbname=".LP_DB_NAME.";charset=utf8mb4",
        LP_DB_USER, LP_DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS lp_candidates (
            id          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
            first_name  VARCHAR(100)    NOT NULL,
            last_name   VARCHAR(100)    NOT NULL,
            email       VARCHAR(255)    NOT NULL,
            phone       VARCHAR(40)     DEFAULT NULL,
            area        VARCHAR(120)    DEFAULT NULL,
            message     TEXT            DEFAULT NULL,
            lang        ENUM('fr','nl') NOT NULL DEFAULT 'fr',
            status      ENUM('new','contacted','qualified','rejected') NOT NULL DEFAULT 'new',
            notes       TEXT            DEFAULT NULL,
            ip          VARCHAR(45)     DEFAULT NULL,
            user_agent  VARCHAR(255)    DEFAULT NULL,
            created_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_email  (email),
            KEY idx_status (status),
            KEY idx_date   (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo '<p style="font-family:monospace;color:green">✓ Table lp_candidates créée avec succès.<br>Supprimez ce fichier maintenant.</p>';
} catch (PDOException $e) {
    echo '<p style="font-family:monospace;color:red">✗ Erreur : ' . htmlspecialchars($e->getMessage()) . '</p>';
}
