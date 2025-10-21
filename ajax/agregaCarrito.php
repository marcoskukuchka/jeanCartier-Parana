<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../conn/loader.php';

// Verificar que el método sea POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $datos = [
        "success" => false,
        "message" => "Error: Método no permitido.",
    ];
    echo json_encode($datos);
    exit;
}

// Verificar que se hayan enviado todos los datos requeridos
$camposRequeridos = ['CodArticulo', 'CodBarra', 'ColDescripcion', 'Descripcion', 'Foto', 'Importe', 'ModDescripcion', 'Stock', 'cantidad'];
$camposFaltantes = [];

foreach ($camposRequeridos as $campo) {
    if (empty($_POST[$campo])) {
        $camposFaltantes[] = $campo;
    }
}

if (!empty($camposFaltantes)) {
    $datos = [
        "success" => false,
        "message" => "Faltan campos requeridos: " . implode(', ', $camposFaltantes),
        "campos_faltantes" => $camposFaltantes
    ];
    echo json_encode($datos);
    exit;
}

// Verificar que el usuario esté logueado
if (!isset($_SESSION['vendedor']) || empty($_SESSION['vendedor'])) {
    $datos = [
        "success" => false,
        "message" => "Error: Debe iniciar sesión para agregar productos al carrito.",
    ];
    echo json_encode($datos);
    exit;
}

try {
    // Obtener datos del POST
    $codArticulo = trim($_POST['CodArticulo']);
    $codBarra = trim($_POST['CodBarra']);
    $colDescripcion = trim($_POST['ColDescripcion']);
    $descripcion = trim($_POST['Descripcion']);
    $foto = trim($_POST['Foto']);
    $importe = floatval($_POST['Importe']);
    $modDescripcion = trim($_POST['ModDescripcion']);
    $stockDisponible = intval($_POST['Stock']);
    $cantidad = intval($_POST['cantidad']);

    // Validar cantidad
    if ($cantidad <= 0) {
        $datos = [
            "success" => false,
            "message" => "La cantidad debe ser mayor a 0.",
        ];
        echo json_encode($datos);
        exit;
    }

    // Validar importe
    if ($importe <= 0) {
        $datos = [
            "success" => false,
            "message" => "El precio del producto no es válido.",
        ];
        echo json_encode($datos);
        exit;
    }

    // Validar stock disponible
    if ($stockDisponible <= 0) {
        $datos = [
            "success" => false,
            "message" => "El producto no tiene stock disponible.",
        ];
        echo json_encode($datos);
        exit;
    }

    // Validar que la cantidad no exceda el stock disponible
    if ($cantidad > $stockDisponible) {
        $datos = [
            "success" => false,
            "message" => "La cantidad solicitada ($cantidad) excede el stock disponible ($stockDisponible).",
        ];
        echo json_encode($datos);
        exit;
    }

    // Inicializar carrito si no existe
    if (!isset($_SESSION['carrito'])) {
        $_SESSION['carrito'] = [];
    }

    // Crear clave única para el producto (por código de barra y modelo)
    $claveProducto = $codBarra . '_' . $modDescripcion;

    // Verificar si el producto ya existe en el carrito
    $productoExistente = false;
    foreach ($_SESSION['carrito'] as $index => $item) {
        if ($item['CodBarra'] === $codBarra && $item['ModDescripcion'] === $modDescripcion) {
            // Producto existe, verificar que la cantidad total no exceda el stock
            $cantidadTotal = $item['cantidad'] + $cantidad;
            if ($cantidadTotal > $stockDisponible) {
                $datos = [
                    "success" => false,
                    "message" => "No puedes agregar $cantidad unidades. Ya tienes {$item['cantidad']} en el carrito y el stock disponible es $stockDisponible.",
                ];
                echo json_encode($datos);
                exit;
            }

            // Actualizar cantidad y stock
            $_SESSION['carrito'][$index]['cantidad'] = $cantidadTotal;
            $_SESSION['carrito'][$index]['subtotal'] = $cantidadTotal * $importe;
            $_SESSION['carrito'][$index]['stock_disponible'] = $stockDisponible;
            $productoExistente = true;
            break;
        }
    }

    // Si el producto no existe, agregarlo
    if (!$productoExistente) {
        $nuevoProducto = [
            'CodArticulo' => $codArticulo,
            'CodBarra' => $codBarra,
            'ColDescripcion' => $colDescripcion,
            'Descripcion' => $descripcion,
            'Foto' => $foto,
            'Importe' => $importe,
            'ModDescripcion' => $modDescripcion,
            'cantidad' => $cantidad,
            'stock_disponible' => $stockDisponible,
            'subtotal' => $cantidad * $importe,
            'fecha_agregado' => date('Y-m-d H:i:s')
        ];

        $_SESSION['carrito'][] = $nuevoProducto;
    }

    // Calcular totales del carrito
    $totalItems = 0;
    $totalImporte = 0;
    foreach ($_SESSION['carrito'] as $item) {
        $totalItems += $item['cantidad'];
        $totalImporte += $item['subtotal'];
    }

    // Respuesta exitosa
    $datos = [
        "success" => true,
        "message" => "Producto agregado al carrito correctamente.",
        "producto" => [
            'CodArticulo' => $codArticulo,
            'Descripcion' => $descripcion,
            'ModDescripcion' => $modDescripcion,
            'ColDescripcion' => $colDescripcion,
            'cantidad' => $cantidad,
            'stock_disponible' => $stockDisponible,
            'stock_restante' => $stockDisponible - $cantidad,
            'importe' => $importe
        ],
        "carrito" => [
            'total_items' => $totalItems,
            'total_importe' => $totalImporte,
            'cantidad_productos' => count($_SESSION['carrito'])
        ],
        "carritoCount" => $totalItems,
        "debug" => [
            "carrito_completo" => $_SESSION['carrito'],
            "session_id" => session_id()
        ]
    ];

    echo json_encode($datos);
} catch (Exception $e) {
    $datos = [
        "success" => false,
        "message" => "Error interno del servidor: " . $e->getMessage(),
        "error_details" => $e->getTraceAsString()
    ];
    echo json_encode($datos);
}
