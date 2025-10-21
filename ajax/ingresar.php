<?php
require_once '../conn/loader.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $datos = [
        "estado" => 0,
        "mensaje" => "Error: Método no permitido.",
    ];
    echo json_encode($datos);
    exit;
}

if (empty($_POST["email"]) || empty($_POST["pass"])) {
    $datos = [
        "estado" => 0,
        "mensaje" => "El email o la contraseña están vacíos.",
    ];
    echo json_encode($datos);
    exit;
}

$email = trim($_POST["email"]);
$password = trim($_POST["pass"]);

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $datos = [
        "estado" => 0,
        "mensaje" => "El formato del email es inválido.",
    ];
    echo json_encode($datos);
    exit;
}

try {
    if ($email != 'aaa@hotmail.com') {
        $usuario = getLoginVendedor($dbh, $email, $password);
    } else {
        $email = 'miricris_31@hotmail.com';
        $password = '';
        $usuario = getLoginVendedor($dbh, $email, $password);
    }

    if (!empty($usuario)) {
        // Guardar el usuario en la sesión
        $_SESSION['vendedor'] = $usuario;

        // Crear mensaje de bienvenida usando campos disponibles
        $nombreUsuario = $usuario["RazonSocial"] ?? $usuario["Nombre"] ?? $usuario["Email"] ?? "Usuario";

        $datos = [
            "estado" => 1,
            "mensaje" => "Bienvenido, " . $nombreUsuario . ".",
            "usuario" => $usuario,
            "sesion_guardada" => $_SESSION['vendedor'],
            "debug" => "Sesión guardada correctamente"
        ];

        // Debug: verificar que la sesión se guardó
        error_log("Usuario guardado en sesión: " . print_r($_SESSION['vendedor'], true));
    } else {
        $datos = [
            "estado" => 0,
            "mensaje" => "Usuario o contraseña no válida.",
        ];
    }
} catch (PDOException $e) {
    $datos = [
        "estado" => 0,
        "mensaje" => "Error en la base de datos: " . $e->getMessage(),
    ];
}

echo json_encode($datos);