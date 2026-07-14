<?php
/**
 * lp_diag_assets.php — Vérifie que les images existent sur le serveur
 * SUPPRIMER ce fichier après diagnostic.
 */
$base = __DIR__;
$images = [
    'img/brand/logo.png',
    'img/products/bread-1.png',
    'img/products/brioche-croustillante.png',
    'img/products/cake-slice.png',
    'img/products/cake.png',
    'img/products/cookies.png',
    'img/products/croissant.png',
    'img/products/roll.png',
    'img/products/sandwiches.png',
    'img/products/savoury-tart.png',
    'img/products/sweet-tart-small.png',
    'img/availability/epiphany.png',
    'img/availability/saint-nicholas.png',
    'img/availability/spring.png',
    'img/availability/summer.png',
];

echo '<pre style="font-family:monospace;font-size:13px">';
echo "Répertoire racine : $base\n\n";
foreach ($images as $img) {
    $path = $base . '/' . $img;
    $ok   = file_exists($path);
    $size = $ok ? round(filesize($path) / 1024, 1) . ' KB' : '—';
    echo ($ok ? '✓' : '✗') . "  $img  ($size)\n";
}
echo '</pre>';
