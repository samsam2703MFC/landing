<?php
/**
 * lp_lead.php — Enregistre un candidat franchise dans lp_candidates
 * Appelé en POST par franchise-lead.html
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
if ($_SERVER['REQUEST_METHOD'] !== 'POST')    { http_response_code(405); echo json_encode(['error'=>'method_not_allowed']); exit; }

// ── Lecture du body JSON ─────────────────────────────────────
$body = json_decode(file_get_contents('php://input'), true);
if (!$body) { http_response_code(400); echo json_encode(['error'=>'invalid_json']); exit; }

function clean(string $v, int $max = 255): string {
    return mb_substr(trim(strip_tags($v)), 0, $max);
}

$first_name = clean($body['first_name'] ?? '');
$last_name  = clean($body['last_name']  ?? '');
$email      = filter_var(trim($body['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$phone      = clean($body['phone'] ?? '', 40);
$area       = clean($body['area']  ?? '', 120);
$message    = clean($body['message'] ?? '', 1000);
$lang       = in_array($body['lang'] ?? 'fr', ['fr','nl'], true) ? $body['lang'] : 'fr';

if (!$first_name || !$last_name || !$email) {
    http_response_code(422);
    echo json_encode(['error'=>'missing_required_fields']);
    exit;
}

// ── Connexion DB ─────────────────────────────────────────────
try {
    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
        LP_DB_HOST, LP_DB_PORT, LP_DB_NAME);
    $pdo = new PDO($dsn, LP_DB_USER, LP_DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    http_response_code(503);
    echo json_encode(['error'=>'db_unavailable']);
    exit;
}

// ── Insert ───────────────────────────────────────────────────
try {
    $stmt = $pdo->prepare(
        'INSERT INTO lp_candidates
            (first_name, last_name, email, phone, area, message, lang, ip, user_agent)
         VALUES
            (:first_name, :last_name, :email, :phone, :area, :message, :lang, :ip, :ua)'
    );
    $stmt->execute([
        ':first_name' => $first_name,
        ':last_name'  => $last_name,
        ':email'      => $email,
        ':phone'      => $phone,
        ':area'       => $area,
        ':message'    => $message,
        ':lang'       => $lang,
        ':ip'         => $_SERVER['REMOTE_ADDR'] ?? '',
        ':ua'         => mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
    ]);
    echo json_encode(['ok' => true, 'id' => $pdo->lastInsertId()]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'insert_failed']);
}
