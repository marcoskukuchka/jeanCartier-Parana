<?php
/**
 * =============================================================
 *  TEST DE DIAGNÓSTICO - Login / Conexión a la base de datos
 * =============================================================
 *  Ejecutá este archivo desde el navegador (test.php) para ver,
 *  paso a paso, dónde se rompe el flujo del login.
 *
 *  IMPORTANTE: borrá o protegé este archivo cuando termines de
 *  diagnosticar. Muestra información sensible (credenciales/errores).
 * =============================================================
 */

// Mostrar TODOS los errores durante el diagnóstico
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: text/html; charset=utf-8');

echo "<pre style='font-family:monospace;font-size:14px;line-height:1.5'>";

function ok($msg)   { echo "  [ OK  ] $msg\n"; }
function bad($msg)  { echo "  [FALLA] $msg\n"; }
function info($msg) { echo "  [INFO ] $msg\n"; }
function titulo($t) { echo "\n================ $t ================\n"; }

/* -------------------------------------------------------------
 * PASO 1: ¿Está instalado el driver de SQL Server para PDO?
 * ------------------------------------------------------------- */
titulo("PASO 1 - Drivers PDO disponibles");

$drivers = PDO::getAvailableDrivers();
info("Drivers PDO detectados: " . implode(', ', $drivers));

if (in_array('sqlsrv', $drivers)) {
    ok("El driver 'sqlsrv' (PDO) está disponible.");
} else {
    bad("NO existe el driver 'sqlsrv' de PDO. El login NO puede funcionar sin él.");
    info("Verificá que php_pdo_sqlsrv esté habilitado en php.ini (extension=php_pdo_sqlsrv).");
}

if (function_exists('sqlsrv_connect')) {
    ok("La extensión 'sqlsrv' (no-PDO) también está disponible.");
} else {
    info("La extensión 'sqlsrv' (no-PDO) no está. Solo importa si usás sqlsrv_connect().");
}

/* -------------------------------------------------------------
 * PASO 1.5: IP pública del servidor donde corre PHP
 * ------------------------------------------------------------- */
titulo("PASO 1.5 - IP pública del servidor");

/*
 * La base solo acepta conexiones desde IPs habilitadas (VPN/firewall).
 * Acá averiguamos con qué IP pública SALE este servidor, para verificar
 * que esa IP esté permitida en el SQL Server / VPN.
 */
info("IP interna del servidor (SERVER_ADDR): " . ($_SERVER['SERVER_ADDR'] ?? '(desconocida)'));

$ipPublica = false;
$servicios = [
    'https://api.ipify.org',
    'https://ifconfig.me/ip',
    'https://icanhazip.com',
];

foreach ($servicios as $url) {
    $ctx = stream_context_create(['http' => ['timeout' => 5]]);
    $resp = @file_get_contents($url, false, $ctx);
    if ($resp !== false && trim($resp) !== '') {
        $ipPublica = trim($resp);
        break;
    }
}

// Respaldo con cURL por si allow_url_fopen está deshabilitado
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

if ($ipPublica) {
    ok("IP PÚBLICA de salida de este servidor: $ipPublica");
    info("Verificá que esta IP esté habilitada en el firewall/VPN del SQL Server (181.13.218.11).");
} else {
    bad("No se pudo obtener la IP pública (¿sin salida a internet o allow_url_fopen/cURL deshabilitados?).");
}

/* -------------------------------------------------------------
 * PASO 2: Conexión directa a la base (sin usar conn.php)
 * ------------------------------------------------------------- */
titulo("PASO 2 - Conexión directa con PDO");

$dbuser = 'AGSWEB';
$dbpass = 'AgsWeb2025!';
$dsn    = "sqlsrv:Server=181.13.218.11, 10028;Database=siv";

$dbhTest = null;
try {
    $dbhTest = new PDO($dsn, $dbuser, $dbpass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    ok("Conexión PDO establecida correctamente contra: $dsn");
} catch (PDOException $e) {
    bad("No se pudo conectar a la base de datos.");
    info("Mensaje: " . $e->getMessage());
    info("Causas típicas: VPN caída, IP/puerto inaccesible, credenciales o nombre de BD incorrectos.");
}

/* -------------------------------------------------------------
 * PASO 3: Cargar el loader real del proyecto (conn/loader.php)
 * ------------------------------------------------------------- */
titulo("PASO 3 - Cargar conn/loader.php (conexión real del proyecto)");

/*
 * OJO: loader.php incluye session.php, que hace session_start() y
 * fija session.save_path en conn/sessions. Si esa carpeta no existe
 * o no tiene permisos, la sesión (y por ende el login) falla.
 */
$sessionsDir = __DIR__ . '/conn/sessions';
if (is_dir($sessionsDir)) {
    ok("La carpeta de sesiones existe: $sessionsDir");
    if (is_writable($sessionsDir)) {
        ok("La carpeta de sesiones tiene permisos de escritura.");
    } else {
        bad("La carpeta de sesiones NO tiene permisos de escritura. El login no podrá guardar la sesión.");
    }
} else {
    bad("La carpeta de sesiones NO existe: $sessionsDir");
    info("Creála (mkdir conn/sessions) o el session_start() de session.php fallará.");
}

require_once __DIR__ . '/conn/loader.php';

if (isset($dbh) && $dbh instanceof PDO) {
    ok("El objeto \$dbh del proyecto se creó correctamente.");
} else {
    bad("El objeto \$dbh NO se creó. Revisá conn/conn.php (probablemente la conexión falló).");
}

/*
 * NOTA IMPORTANTE sobre conn/conn.php:
 * En ese archivo se arma el array $options con PDO::ATTR_ERRMODE,
 * PERO al crear la conexión se hace `new PDO($dsn, $dbuser, $dbpass)`
 * SIN pasar $options. Por eso ERRMODE_EXCEPTION no se aplica y los
 * errores de consulta pueden quedar silenciados (devuelven false).
 */
if (isset($dbh) && $dbh instanceof PDO) {
    $modo = $dbh->getAttribute(PDO::ATTR_ERRMODE);
    if ($modo === PDO::ERRMODE_EXCEPTION) {
        ok("El \$dbh del proyecto tiene ERRMODE_EXCEPTION activo.");
    } else {
        bad("El \$dbh del proyecto NO tiene ERRMODE_EXCEPTION (los errores SQL quedan ocultos).");
        info("En conn/conn.php pasá \$options al constructor: new PDO(\$dsn, \$dbuser, \$dbpass, \$options).");
    }
}

/* -------------------------------------------------------------
 * PASO 4: Verificar la tabla webClientes
 * ------------------------------------------------------------- */
titulo("PASO 4 - Consulta a la tabla webClientes");

$conexion = (isset($dbh) && $dbh instanceof PDO) ? $dbh : $dbhTest;

if (!$conexion) {
    bad("No hay conexión válida para consultar. Se omite el paso 4.");
} else {
    try {
        $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $conexion->query("SELECT TOP 5 * FROM webClientes");
        $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ok("La tabla webClientes respondió. Filas de muestra: " . count($filas));

        if (count($filas) > 0) {
            info("Columnas disponibles: " . implode(', ', array_keys($filas[0])));
            info("Confirmá que existan las columnas usadas en el login: Email, Clave, Suspendido.");
        } else {
            bad("La tabla webClientes está VACÍA. Sin registros no hay login posible.");
        }
    } catch (PDOException $e) {
        bad("Error al consultar webClientes: " . $e->getMessage());
    }
}

/* -------------------------------------------------------------
 * PASO 5: Probar la función real de login (getLoginVendedor)
 * ------------------------------------------------------------- */
titulo("PASO 5 - Probar getLoginVendedor()");

/*
 * >>> COMPLETÁ ACÁ con un email y clave que SEPAS que son válidos <<<
 * Así comprobamos si la consulta del login devuelve el usuario.
 */
$emailPrueba = 'CAMBIAR@ejemplo.com';
$clavePrueba = 'CAMBIAR_CLAVE';

if (!function_exists('getLoginVendedor')) {
    bad("La función getLoginVendedor() no está cargada. Revisá conn/sql.php.");
} elseif (!$conexion) {
    bad("No hay conexión para probar el login.");
} elseif ($emailPrueba === 'CAMBIAR@ejemplo.com') {
    info("Editá \$emailPrueba y \$clavePrueba en este archivo con credenciales reales para probar el login.");
} else {
    try {
        $usuario = getLoginVendedor($conexion, $emailPrueba, $clavePrueba);
        if (!empty($usuario)) {
            ok("¡Login CORRECTO! getLoginVendedor devolvió un usuario.");
            info("Datos devueltos:");
            print_r($usuario);
        } else {
            bad("getLoginVendedor NO devolvió usuario (email/clave no coinciden o Suspendido != 0).");
            info("Verificá en la BD que el registro exista con esa clave y Suspendido = 0.");
        }
    } catch (PDOException $e) {
        bad("Error en la consulta de login: " . $e->getMessage());
    }
}

/* -------------------------------------------------------------
 * PASO 6: Diagnóstico de sesión
 * ------------------------------------------------------------- */
titulo("PASO 6 - Estado de la sesión");

info("session_status(): " . session_status() . " (2 = activa)");
info("session_id(): " . (session_id() ?: '(vacío)'));
info("session.save_path: " . session_save_path());

if (session_status() === PHP_SESSION_ACTIVE) {
    $_SESSION['test_diagnostico'] = 'valor_de_prueba';
    if (($_SESSION['test_diagnostico'] ?? null) === 'valor_de_prueba') {
        ok("Se puede leer/escribir en \$_SESSION correctamente.");
    } else {
        bad("No se pudo escribir en \$_SESSION.");
    }
} else {
    bad("La sesión NO está activa. El login no podrá persistir el usuario.");
}

echo "\n================ FIN DEL DIAGNÓSTICO ================\n";
echo "</pre>";
