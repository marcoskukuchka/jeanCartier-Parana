<?php
// Incluir el loader para inicializar sesión
require_once './conn/loader.php';

// Verificar que haya una sesión activa
if (session_status() === PHP_SESSION_ACTIVE) {
    // Eliminar variables específicas de la sesión
    if (isset($_SESSION['vendedor'])) {
        unset($_SESSION['vendedor']);
    }

    if (isset($_SESSION['carrito'])) {
        unset($_SESSION['carrito']);
    }

    // Limpiar cualquier otra variable de sesión específica del proyecto
    // Mantener solo lo esencial del sistema
    $_SESSION = array();

    // Destruir la cookie de sesión si existe
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    // Destruir la sesión completamente
    session_destroy();
}

// Redirigir al index principal
header('Location: ../index.php');
exit;
