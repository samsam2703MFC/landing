<?php
/**
 * lp_office_lead.php — Prospect « Livraison au bureau » (hors zone / sans tournée).
 *
 * Appelé en POST par livraison-bureau.jsx quand le prospect encode son code
 * postal (aucune tournée disponible dans sa région). Écrit dans la base
 * partagée atelierby_db un enregistrement `client` :
 *
 *   is_b2b          = 1
 *   office_delivery = 1   → déclenche le trigger qui crée le WS_OFFICE de provenance
 *   status          = 1   (à valider ; 0 = validé)
 *   id_main_shop    = boutique déduite du code postal (zone de chalandise
 *                     ws_franchisor_catchment) ; 0 si aucun franchisé ne couvre
 *                     la zone → le client apparaît en « Prospect » côté franchisor.
 *
 * Miroir de lp_lead.php (CORS, JSON, PDO). Aucune écriture de contenu éditorial.
 */

define('LP_DB_HOST', 'localhost');
define('LP_DB_NAME', 'atelierby_db');
define('LP_DB_USER', 'sam');
define('LP_DB_PASS', 'NhoQyQbKRSPh4Ubg3sR7DMjs5');
define('LP_DB_PORT', 3306);

$allowed_origins = [
    'https://latelierby.be',
    'https://www.latelierby.be',
    'http://185.180.206.46',
    'http://localhost',
];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowed_origins, true)) {
    header('Access-Control-Allow-Origin: ' . $origin);
}
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST')    { http_response_code(405); echo json_encode(['error' => 'method_not_allowed']); exit; }

$body = json_decode(file_get_contents('php://input'), true);
if (!$body) { http_response_code(400); echo json_encode(['error' => 'invalid_json']); exit; }

function lp_clean($v, int $max = 190): string {
    return mb_substr(trim(strip_tags((string) $v)), 0, $max);
}

$first   = lp_clean($body['first_name'] ?? '', 80);
$last    = lp_clean($body['last_name']  ?? '', 80);
$company = lp_clean($body['company']    ?? '', 160);
$email   = filter_var(trim($body['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$phone   = lp_clean($body['phone'] ?? '', 40);
$zip     = preg_replace('/\D+/', '', lp_clean($body['postal_code'] ?? '', 12)); // 4 chiffres (BE)
$lang    = in_array($body['lang'] ?? 'fr', ['fr', 'nl'], true) ? $body['lang'] : 'fr';

if (!$email)                          { http_response_code(422); echo json_encode(['error' => 'missing_email']);   exit; }
if (!preg_match('/^\d{4}$/', $zip))   { http_response_code(422); echo json_encode(['error' => 'invalid_postal']); exit; }

try {
    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', LP_DB_HOST, LP_DB_PORT, LP_DB_NAME);
    $pdo = new PDO($dsn, LP_DB_USER, LP_DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    http_response_code(503); echo json_encode(['error' => 'db_unavailable']); exit;
}

$colExists = function (string $table, string $col) use ($pdo): bool {
    $st = $pdo->prepare("SELECT 1 FROM information_schema.columns
                          WHERE table_schema=DATABASE() AND table_name=? AND column_name=? LIMIT 1");
    $st->execute([$table, $col]);
    return (bool) $st->fetchColumn();
};

// ── 1) Shop déduit du code postal (le franchisé dont la chalandise couvre le CP) ──
$shopId = 0; $shopName = null;
try {
    $st = $pdo->prepare("SELECT shop_id FROM ws_franchisor_catchment
                          WHERE active=1 AND shop_id IS NOT NULL
                            AND postcodes REGEXP CONCAT('(^|[^0-9])', ?, '($|[^0-9])')
                          ORDER BY shop_id LIMIT 1");
    $st->execute([$zip]);
    $sid = $st->fetchColumn();
    if ($sid !== false && $sid !== null) $shopId = (int) $sid;
} catch (PDOException $e) { /* pas de table chalandise → prospect non rattaché */ }
if ($shopId) {
    try { $s = $pdo->prepare("SELECT name FROM shops WHERE id=? LIMIT 1"); $s->execute([$shopId]); $shopName = $s->fetchColumn() ?: null; }
    catch (PDOException $e) { /* nom facultatif */ }
}

// ── 2) Anti-doublon : un client avec cet e-mail existe déjà → on ne recrée pas ──
try {
    $st = $pdo->prepare("SELECT id, id_main_shop FROM client WHERE email IS NOT NULL AND LOWER(TRIM(email))=? LIMIT 1");
    $st->execute([strtolower($email)]);
    if ($ex = $st->fetch()) {
        echo json_encode(['ok' => true, 'duplicate' => true,
            'attached' => ((int) $ex['id_main_shop'] !== 0), 'shop' => $shopName]);
        exit;
    }
} catch (PDOException $e) { /* si la lecture échoue, on tente quand même l'insert */ }

// ── 3) INSERT client (miroir de l'inscription webshop + drapeaux B2B) ──
$cols = ['id_main_shop', 'email', 'phone', 'phone_prefix', 'phone_e164',
         'name', 'surname', 'zip', 'password_hash', 'active',
         'source_channel', 'webshop_user', 'preferred_auth_method'];
$vals = [$shopId, $email, ($phone ?: null), null, null,
         ($company ?: trim($first . ' ' . $last)), $last, $zip, null, 1,
         'webshop', 0, null];

if ($colExists('client', 'is_b2b'))          { $cols[] = 'is_b2b';          $vals[] = 1; }
if ($colExists('client', 'office_delivery')) { $cols[] = 'office_delivery'; $vals[] = 1; }
if ($colExists('client', 'status'))          { $cols[] = 'status';          $vals[] = 1; } // 1 = à valider
if ($colExists('client', 'locality'))        { $cols[] = 'locality';        $vals[] = ($body['locality'] ?? null) ?: null; }

try {
    $ph  = implode(',', array_fill(0, count($cols), '?'));
    $sql = 'INSERT INTO client (' . implode(',', $cols) . ') VALUES (' . $ph . ')';
    $st  = $pdo->prepare($sql);
    $st->execute($vals);
    $cid = (int) $pdo->lastInsertId();
} catch (PDOException $e) {
    http_response_code(500); echo json_encode(['error' => 'insert_failed']); exit;
}

echo json_encode([
    'ok'        => true,
    'client_id' => $cid,
    'attached'  => ($shopId !== 0),
    'shop'      => $shopName,
    'message'   => $shopId !== 0
        ? 'Votre demande est rattachée à votre boutique de quartier.'
        : 'Votre demande est enregistrée — nous cherchons un franchisé pour votre zone.',
]);
