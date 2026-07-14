<?php
/**
 * lp_lead.php — Enregistre un candidat franchise dans lp_candidates
 * Envoie ensuite 2 mails : notification interne + confirmation candidat
 * Logue chaque envoi dans lp_mail_log (créée automatiquement si absente)
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

// ── Création automatique de lp_mail_log si absente ───────────
$pdo->exec("
    CREATE TABLE IF NOT EXISTS lp_mail_log (
        id           INT UNSIGNED     NOT NULL AUTO_INCREMENT,
        candidate_id INT UNSIGNED     DEFAULT NULL,
        type         ENUM('notify','confirm') NOT NULL,
        to_email     VARCHAR(255)     NOT NULL,
        subject      VARCHAR(255)     NOT NULL,
        status       ENUM('sent','failed') NOT NULL DEFAULT 'sent',
        error_msg    VARCHAR(500)     DEFAULT NULL,
        sent_at      TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_candidate (candidate_id),
        KEY idx_sent_at   (sent_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

// ── Insert candidat ──────────────────────────────────────────
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
    $new_id = (int)$pdo->lastInsertId();
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'insert_failed']);
    exit;
}

// ── Email ────────────────────────────────────────────────────
try {
    $mp = $pdo->query('SELECT * FROM lp_mail_params LIMIT 1')->fetch();
} catch (PDOException $e) {
    $mp = null;
}

if ($mp) {
    $sfx  = '_' . $lang;
    $from = $mp['from_name'] . ' <' . $mp['from_email'] . '>';

    $replace = function (string $tpl) use ($first_name, $last_name): string {
        return str_replace(['{prenom}', '{nom}'], [$first_name, $last_name], $tpl);
    };

    // ── 1) Notification interne ──────────────────────────────
    $notify_subject = $replace($mp['notify_subject' . $sfx] ?: $mp['notify_subject_fr']);
    $notify_to      = $mp['notify_name'] . ' <' . $mp['notify_email'] . '>';

    $notify_body  = "Nouveau lead franchise reçu\n";
    $notify_body .= "================================\n";
    $notify_body .= "Prénom    : $first_name\n";
    $notify_body .= "Nom       : $last_name\n";
    $notify_body .= "Email     : $email\n";
    $notify_body .= "Téléphone : $phone\n";
    $notify_body .= "Zone      : $area\n";
    $notify_body .= "Langue    : $lang\n";
    $notify_body .= "Message   :\n$message\n";

    [$ok, $err] = lp_send_mail($mp, $notify_to, $notify_subject, $notify_body, $from);
    lp_log_mail($pdo, $new_id, 'notify', $mp['notify_email'], $notify_subject, $ok, $err);

    // ── 2) Confirmation candidat ─────────────────────────────
    $confirm_subject = $replace($mp['confirm_subject' . $sfx] ?: $mp['confirm_subject_fr']);
    $confirm_intro   = $replace($mp['confirm_intro'   . $sfx] ?: $mp['confirm_intro_fr']);
    $confirm_to      = "$first_name $last_name <$email>";

    [$ok, $err] = lp_send_mail($mp, $confirm_to, $confirm_subject, $confirm_intro, $from);
    lp_log_mail($pdo, $new_id, 'confirm', $email, $confirm_subject, $ok, $err);
}

echo json_encode(['ok' => true, 'id' => $new_id]);

// ── Log ───────────────────────────────────────────────────────
function lp_log_mail(PDO $pdo, int $candidate_id, string $type, string $to_email,
                     string $subject, bool $ok, string $error_msg): void
{
    try {
        $pdo->prepare(
            'INSERT INTO lp_mail_log (candidate_id, type, to_email, subject, status, error_msg)
             VALUES (:cid, :type, :to, :subj, :status, :err)'
        )->execute([
            ':cid'    => $candidate_id,
            ':type'   => $type,
            ':to'     => mb_substr($to_email, 0, 255),
            ':subj'   => mb_substr($subject,  0, 255),
            ':status' => $ok ? 'sent' : 'failed',
            ':err'    => $ok ? null : mb_substr($error_msg, 0, 500),
        ]);
    } catch (PDOException $e) {
        // log failure is non-blocking
    }
}

// ── Envoi (mail() ou SMTP via socket) ────────────────────────
function lp_send_mail(array $mp, string $to, string $subject, string $body, string $from): array
{
    $headers  = "From: $from\r\n";
    $headers .= "Reply-To: {$mp['from_email']}\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "X-Mailer: LP-Lead/1.0\r\n";

    if (!empty($mp['smtp_host'])) {
        return lp_smtp_send($mp, $to, $subject, $body, $headers);
    }

    $encoded_subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    $ok = @mail($to, $encoded_subject, $body, $headers);
    return [$ok, $ok ? '' : 'mail() returned false'];
}

function lp_smtp_send(array $mp, string $to, string $subject, string $body, string $headers): array
{
    $host   = $mp['smtp_host'];
    $port   = (int)($mp['smtp_port'] ?? 587);
    $user   = $mp['smtp_user'] ?? '';
    $pass   = $mp['smtp_pass'] ?? '';
    $secure = $mp['smtp_secure'] ?? 'tls';
    $from_e = $mp['from_email'];

    $prefix = ($secure === 'ssl') ? 'ssl://' : '';
    $socket = @fsockopen($prefix . $host, $port, $errno, $errstr, 10);
    if (!$socket) return [false, "fsockopen: $errstr ($errno)"];

    $read = function () use ($socket): string {
        $r = '';
        while ($line = fgets($socket, 512)) {
            $r .= $line;
            if ($line[3] === ' ') break;
        }
        return $r;
    };
    $cmd = function (string $c) use ($socket, $read): string {
        fwrite($socket, $c . "\r\n");
        return $read();
    };

    $read(); // banner
    $cmd('EHLO ' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));

    if ($secure === 'tls') {
        $cmd('STARTTLS');
        stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
        $cmd('EHLO ' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
    }

    if ($user) {
        $cmd('AUTH LOGIN');
        $cmd(base64_encode($user));
        $r = $cmd(base64_encode($pass));
        if (!str_starts_with(trim($r), '235')) {
            fclose($socket);
            return [false, 'SMTP AUTH failed: ' . trim($r)];
        }
    }

    $cmd("MAIL FROM:<$from_e>");
    preg_match('/<(.+?)>/', $to, $m);
    $to_addr = $m[1] ?? $to;
    $r = $cmd("RCPT TO:<$to_addr>");
    if (!str_starts_with(trim($r), '250')) {
        fclose($socket);
        return [false, 'RCPT rejected: ' . trim($r)];
    }

    $cmd('DATA');
    $encoded_subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    $msg  = "To: $to\r\n";
    $msg .= "Subject: $encoded_subject\r\n";
    $msg .= $headers;
    $msg .= "\r\n" . $body . "\r\n.";
    $r = $cmd($msg);

    $cmd('QUIT');
    fclose($socket);

    $ok = str_starts_with(trim($r), '250');
    return [$ok, $ok ? '' : 'DATA rejected: ' . trim($r)];
}
