<?php
// Configuración específica para la sucursal de Córdoba
ini_set('session.save_path', __DIR__ . '/sessions');
ini_set('session.name', 'PHPSESSID_CBA'); // Sesión específica para Córdoba
ini_set('session.gc_maxlifetime', 7200); // 2 horas
ini_set('session.cookie_lifetime', 0); // Hasta cerrar navegador

session_start();

if (session_id()) {
    // Detectar si estamos en HTTPS
    $is_https = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ||
        (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

    setcookie(session_name(), session_id(), [
        'expires' => 0,
        'path' => '/cba/', // Ruta específica para Córdoba
        'secure' => $is_https, // Solo true en HTTPS
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}