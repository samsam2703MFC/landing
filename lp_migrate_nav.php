<?php
/**
 * lp_migrate_nav.php — Ajoute hex_color, élargit icon à TEXT dans lp_nav_items
 * SUPPRIMER ce fichier après exécution.
 */
define('LP_DB_HOST', 'localhost');
define('LP_DB_NAME', 'atelierby_db');
define('LP_DB_USER', 'sam');
define('LP_DB_PASS', 'NhoQyQbKRSPh4Ubg3sR7DMjs5');

try {
    $pdo = new PDO("mysql:host=".LP_DB_HOST.";dbname=".LP_DB_NAME.";charset=utf8mb4",
        LP_DB_USER, LP_DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // Ajouter hex_color si absent
    try {
        $pdo->exec("ALTER TABLE lp_nav_items ADD COLUMN hex_color VARCHAR(20) NOT NULL DEFAULT '#8A6200' AFTER style");
        echo '<p style="font-family:monospace;color:green">✓ Colonne hex_color ajoutée.</p>';
    } catch (PDOException $e) {
        echo '<p style="font-family:monospace;color:#888">~ hex_color existe déjà.</p>';
    }

    // Élargir icon à VARCHAR(2000) pour accueillir du SVG
    try {
        $pdo->exec("ALTER TABLE lp_nav_items MODIFY COLUMN icon VARCHAR(2000) NOT NULL DEFAULT ''");
        echo '<p style="font-family:monospace;color:green">✓ Colonne icon élargie (SVG supporté).</p>';
    } catch (PDOException $e) {
        echo '<p style="font-family:monospace;color:red">✗ ' . htmlspecialchars($e->getMessage()) . '</p>';
    }

    // Mettre à jour les couleurs des lignes existantes
    $pdo->exec("UPDATE lp_nav_items SET hex_color='#8A6200' WHERE style='gold'  AND hex_color=''");
    $pdo->exec("UPDATE lp_nav_items SET hex_color='#9B1C1C' WHERE style='ruby'  AND hex_color=''");
    $pdo->exec("UPDATE lp_nav_items SET hex_color='#166534' WHERE style='green' AND hex_color=''");

    echo '<p style="font-family:monospace;color:green">✓ Migration terminée. Supprimez ce fichier.</p>';
    echo '<p style="font-family:monospace;color:#555">→ Modifiez hex_color directement dans PHPMyAdmin (ex: #C0392B).</p>';
    echo '<p style="font-family:monospace;color:#555">→ Pour une icône SVG, collez le code &lt;svg&gt;...&lt;/svg&gt; dans la colonne icon.</p>';

} catch (PDOException $e) {
    echo '<p style="font-family:monospace;color:red">✗ ' . htmlspecialchars($e->getMessage()) . '</p>';
}
