<?php
/**
 * Diagnóstico de conexión a la base de datos.
 * Si la conexión falla, envía un email con el reporte.
 *
 * Ejecutar desde el navegador. Proteger o borrar cuando no se use.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('America/Argentina/Buenos_Aires');

header('Content-Type: text/html; charset=utf-8');

require_once __DIR__ . '/libs/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/libs/PHPMailer/src/SMTP.php';
require_once __DIR__ . '/libs/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/* ---------- Destinatario del alerta ---------- */
$emailDestino = 'marcos.kukuchka@gmail.com';

/* ---------- Credenciales / DSN (igual que conn/conn.php) ---------- */
$dbuser     = 'AGSWEB';
$dbpass     = 'AgsWeb2025!';
$serverName = '26.229.63.28, 10028';
$database   = 'siv';
$dsn        = "sqlsrv:Server={$serverName};Database={$database}";

$fecha      = date('Y-m-d H:i:s');
$reporte    = [];
$fallos     = [];

function linea($msg, &$reporte)
{
    $reporte[] = $msg;
}

/* -------------------------------------------------------------
 * 1) Entorno PHP / drivers
 * ------------------------------------------------------------- */
linea("Fecha: {$fecha}", $reporte);
linea("Servidor PHP: " . php_uname(), $reporte);
linea("PHP version: " . PHP_VERSION, $reporte);
linea("SERVER_ADDR: " . ($_SERVER['SERVER_ADDR'] ?? '(desconocida)'), $reporte);
linea("HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? '(desconocido)'), $reporte);
linea("REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? '(desconocido)'), $reporte);

$drivers = PDO::getAvailableDrivers();
linea("Drivers PDO: " . (empty($drivers) ? '(ninguno)' : implode(', ', $drivers)), $reporte);

if (!in_array('sqlsrv', $drivers, true)) {
    $fallos[] = "Driver PDO 'sqlsrv' no disponible.";
    linea("[FALLA] Driver PDO 'sqlsrv' no disponible.", $reporte);
} else {
    linea("[OK] Driver PDO 'sqlsrv' disponible.", $reporte);
}

if (function_exists('sqlsrv_connect')) {
    linea("[OK] Extensión sqlsrv (no-PDO) disponible.", $reporte);
} else {
    linea("[INFO] Extensión sqlsrv (no-PDO) no disponible.", $reporte);
}

/* -------------------------------------------------------------
 * 2) IP pública de salida
 * ------------------------------------------------------------- */
$ipPublica = false;
$servicios = [
    'https://api.ipify.org',
    'https://ifconfig.me/ip',
    'https://icanhazip.com',
];

foreach ($servicios as $url) {
    $ctx  = stream_context_create(['http' => ['timeout' => 5]]);
    $resp = @file_get_contents($url, false, $ctx);
    if ($resp !== false && trim($resp) !== '') {
        $ipPublica = trim($resp);
        break;
    }
}

if (!$ipPublica && function_exists('curl_init')) {
    $ch = curl_init('https://api.ipify.org');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $resp = curl_exec($ch);
    curl_close($ch);
    if ($resp !== false && trim($resp) !== '') {
        $ipPublica = trim($resp);
    }
}

linea("IP pública de salida: " . ($ipPublica ?: '(no se pudo obtener)'), $reporte);

/* -------------------------------------------------------------
 * 3) Prueba de conexión PDO
 * ------------------------------------------------------------- */
$conexionOk = false;
$errorPdo   = null;
$inicio     = microtime(true);

try {
    $dbh = new PDO($dsn, $dbuser, $dbpass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    $conexionOk = true;
    $ms = round((microtime(true) - $inicio) * 1000);
    linea("[OK] Conexión PDO OK en {$ms} ms. DSN: {$dsn}", $reporte);
} catch (PDOException $e) {
    $errorPdo = $e->getMessage();
    $ms = round((microtime(true) - $inicio) * 1000);
    $fallos[] = "Conexión PDO falló: {$errorPdo}";
    linea("[FALLA] Conexión PDO falló en {$ms} ms.", $reporte);
    linea("DSN: {$dsn}", $reporte);
    linea("Usuario: {$dbuser}", $reporte);
    linea("Error: {$errorPdo}", $reporte);
}

/* -------------------------------------------------------------
 * 4) Prueba sqlsrv_connect (opcional)
 * ------------------------------------------------------------- */
if (function_exists('sqlsrv_connect')) {
    $connectionInfo = [
        'Database'          => $database,
        'UID'               => $dbuser,
        'PWD'               => $dbpass,
        'ConnectionPooling' => '1',
        'LoginTimeout'      => 10,
    ];
    $conmsql = @sqlsrv_connect($serverName, $connectionInfo);
    if ($conmsql) {
        linea("[OK] sqlsrv_connect OK.", $reporte);
        sqlsrv_close($conmsql);
    } else {
        $errs = sqlsrv_errors();
        $detalle = $errs ? json_encode($errs, JSON_UNESCAPED_UNICODE) : '(sin detalle)';
        $fallos[] = "sqlsrv_connect falló: {$detalle}";
        linea("[FALLA] sqlsrv_connect falló.", $reporte);
        linea("Detalle: {$detalle}", $reporte);
    }
}

/* -------------------------------------------------------------
 * 5) Si hay fallos, enviar email
 * ------------------------------------------------------------- */
$emailEnviado = null;
$emailError   = null;

if (!empty($fallos)) {
    $cuerpoTexto = "ALERTA: Fallo de conexión a la base de datos\n"
        . "========================================\n\n"
        . implode("\n", $reporte)
        . "\n\nResumen de fallos:\n- "
        . implode("\n- ", $fallos)
        . "\n";

    $cuerpoHtml = '<html><body style="font-family:monospace;font-size:14px">'
        . '<h2 style="color:#c00">Alerta: fallo de conexión a la DB</h2>'
        . '<p><strong>Fecha:</strong> ' . htmlspecialchars($fecha) . '</p>'
        . '<p><strong>Host:</strong> ' . htmlspecialchars($_SERVER['HTTP_HOST'] ?? '-') . '</p>'
        . '<p><strong>IP pública:</strong> ' . htmlspecialchars($ipPublica ?: 'desconocida') . '</p>'
        . '<h3>Fallos</h3><ul>';
    foreach ($fallos as $f) {
        $cuerpoHtml .= '<li>' . htmlspecialchars($f) . '</li>';
    }
    $cuerpoHtml .= '</ul><h3>Reporte completo</h3><pre style="background:#f5f5f5;padding:12px;border:1px solid #ddd">'
        . htmlspecialchars(implode("\n", $reporte))
        . '</pre></body></html>';

    try {
        $mail = new PHPMailer(true);
        $mail->SMTPDebug = 0;
        $mail->isSMTP();
        $mail->Host       = 'mail.tiendajeancartierhogar.com.ar';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'presupuesto@tiendajeancartierhogar.com.ar';
        $mail->Password   = 'FaruSae0ujoh';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;
        $mail->CharSet    = 'UTF-8';

        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true,
            ],
        ];

        $mail->setFrom('presupuesto@tiendajeancartierhogar.com.ar', 'Diagnóstico DB - Jean Cartier');
        $mail->addAddress($emailDestino);

        $mail->isHTML(true);
        $mail->Subject = '[ALERTA DB] Fallo de conexión - ' . ($ipPublica ?: 'sin-ip') . ' - ' . $fecha;
        $mail->Body    = $cuerpoHtml;
        $mail->AltBody = $cuerpoTexto;

        $emailEnviado = $mail->send();
        linea("[OK] Email de alerta enviado a {$emailDestino}.", $reporte);
    } catch (Exception $e) {
        $emailEnviado = false;
        $emailError   = $e->getMessage();
        linea("[FALLA] No se pudo enviar el email: {$emailError}", $reporte);
    }
} else {
    linea("[OK] Sin fallos. No se envía email.", $reporte);
}

/* -------------------------------------------------------------
 * Salida en pantalla
 * ------------------------------------------------------------- */
$okColor  = '#0a7a0a';
$badColor = '#c00';
$estado   = empty($fallos) ? 'CONEXIÓN OK' : 'FALLÓ LA CONEXIÓN';
$color    = empty($fallos) ? $okColor : $badColor;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Diagnóstico DB</title>
    <style>
        body { font-family: Consolas, monospace; background: #1e1e1e; color: #ddd; padding: 24px; }
        h1 { color: <?= $color ?>; margin-bottom: 8px; }
        .meta { color: #888; margin-bottom: 20px; }
        pre { background: #2d2d2d; padding: 16px; border-radius: 6px; white-space: pre-wrap; line-height: 1.5; }
        .ok { color: #6c6; }
        .fail { color: #f66; }
        .info { color: #9cf; }
    </style>
</head>
<body>
    <h1><?= htmlspecialchars($estado) ?></h1>
    <p class="meta"><?= htmlspecialchars($fecha) ?> — DSN: <?= htmlspecialchars($dsn) ?></p>
    <pre><?php
    foreach ($reporte as $linea) {
        $clase = 'info';
        if (strpos($linea, '[OK]') !== false) {
            $clase = 'ok';
        } elseif (strpos($linea, '[FALLA]') !== false) {
            $clase = 'fail';
        }
        echo '<span class="' . $clase . '">' . htmlspecialchars($linea) . "</span>\n";
    }
    ?></pre>
    <?php if (!empty($fallos)): ?>
        <p class="<?= $emailEnviado ? 'ok' : 'fail' ?>">
            Email alerta:
            <?php
            if ($emailEnviado) {
                echo 'enviado a ' . htmlspecialchars($emailDestino);
            } else {
                echo 'NO enviado' . ($emailError ? ' — ' . htmlspecialchars($emailError) : '');
            }
            ?>
        </p>
    <?php endif; ?>
</body>
</html>
