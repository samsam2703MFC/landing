<?php
/**
 * lp_migrate_services.php — Section services : 2 tuiles (office apricot + b2b copper)
 *   1) ajoute url + theme à lp_services
 *   2) désactive tous les services existants
 *   3) (ré)insère les 2 tuiles voulues, actives, avec lien + thème
 * SUPPRIMER après exécution.
 */
define('LP_DB_HOST', 'localhost');
define('LP_DB_NAME', 'atelierby_db');
define('LP_DB_USER', 'sam');
define('LP_DB_PASS', 'NhoQyQbKRSPh4Ubg3sR7DMjs5');

header('Content-Type: text/plain; charset=utf-8');
$pdo = new PDO("mysql:host=".LP_DB_HOST.";dbname=".LP_DB_NAME.";charset=utf8mb4",
    LP_DB_USER, LP_DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

// 1) Colonnes
foreach (["image_path VARCHAR(255) NOT NULL DEFAULT ''", "url VARCHAR(255) NOT NULL DEFAULT ''", "theme VARCHAR(30) NOT NULL DEFAULT ''"] as $c) {
    $name = strtok($c, ' ');
    try { $pdo->exec("ALTER TABLE lp_services ADD COLUMN $c"); echo "✓ colonne $name ajoutée\n"; }
    catch (PDOException $e) { echo "~ colonne $name déjà présente\n"; }
}

// 2) Tout désactiver + retirer d'anciennes tuiles thématisées (idempotent)
$pdo->exec("UPDATE lp_services SET is_active = 0");
$pdo->exec("DELETE FROM lp_services WHERE theme IN ('apricot','copper')");
echo "✓ services existants désactivés\n";

// 3) Insérer les 2 tuiles
$stmt = $pdo->prepare(
    'INSERT INTO lp_services (position, name_fr, name_nl, desc_fr, desc_nl, icon_svg, image_path, url, theme, is_active)
     VALUES (:pos, :nfr, :nnl, :dfr, :dnl, :svg, :img, :url, :theme, 1)'
);
$rows = [
    [0,
     'Livraison au bureau', 'Levering op kantoor',
     'Pauses gourmandes et petits-déjeuners livrés directement à votre entreprise.',
     'Lekkere pauzes en ontbijten rechtstreeks op kantoor geleverd.',
     '<path d="M4 21V8l8-5 8 5v13M9 21v-6h6v6"/>',
     'img/services/delivery.png', 'livraison-bureau.html', 'apricot'],
    [1,
     'Comptes B2B', 'B2B-accounts',
     'Hôtels, restaurants, collectivités. Livraisons avant 7 h, facturation mensuelle.',
     'Hotels, restaurants, gemeenschappen. Levering vóór 7 u, maandelijkse facturatie.',
     '<path d="M3 20h18M5 20V9l7-4 7 4v11"/><path d="M10 20v-5h4v5"/>',
     'img/services/b2b.png', 'evenements-b2b.html', 'copper'],
];
foreach ($rows as $r) {
    $stmt->execute([':pos'=>$r[0],':nfr'=>$r[1],':nnl'=>$r[2],':dfr'=>$r[3],':dnl'=>$r[4],':svg'=>$r[5],':img'=>$r[6],':url'=>$r[7],':theme'=>$r[8]]);
    echo "✓ tuile insérée : {$r[1]} ({$r[8]}) → img {$r[6]}\n";
}

echo "\n=== Services actifs (dans la section) ===\n";
foreach ($pdo->query("SELECT position, name_fr, theme, image_path FROM lp_services WHERE is_active=1 ORDER BY position") as $s) {
    echo "  {$s['position']} · {$s['name_fr']} · thème={$s['theme']} · {$s['image_path']}\n";
}
echo "\n✓ Terminé. Modifiez url/theme/textes dans lp_services. Supprimez ce fichier.\n";
