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

// Verificar que el usuario esté logueado
if (!isset($_SESSION['vendedor']) || empty($_SESSION['vendedor'])) {
    $datos = [
        "success" => false,
        "message" => "Error: Debe iniciar sesión para modificar el carrito.",
    ];
    echo json_encode($datos);
    exit;
}

// Verificar que exista la acción
if (empty($_POST['action'])) {
    $datos = [
        "success" => false,
        "message" => "Error: No se especificó la acción a realizar.",
    ];
    echo json_encode($datos);
    exit;
}

try {
    $action = $_POST['action'];

    // Inicializar carrito si no existe
    if (!isset($_SESSION['carrito'])) {
        $_SESSION['carrito'] = [];
    }

    switch ($action) {
        case 'cambiar_cantidad':
            if (!isset($_POST['index']) || !isset($_POST['cambio'])) {
                throw new Exception("Faltan parámetros para cambiar cantidad");
            }

            $index = intval($_POST['index']);
            $cambio = intval($_POST['cambio']);

            if (!isset($_SESSION['carrito'][$index])) {
                throw new Exception("Producto no encontrado en el carrito");
            }

            $nuevaCantidad = $_SESSION['carrito'][$index]['cantidad'] + $cambio;
            $stockDisponible = $_SESSION['carrito'][$index]['stock_disponible'] ?? 0;

            if ($nuevaCantidad <= 0) {
                // Si la cantidad es 0 o menos, eliminar el producto
                unset($_SESSION['carrito'][$index]);
                $_SESSION['carrito'] = array_values($_SESSION['carrito']); // Reindexar array
                $mensaje = "Producto eliminado del carrito";
            } else if ($nuevaCantidad > $stockDisponible) {
                // Si la nueva cantidad excede el stock disponible
                throw new Exception("No se puede agregar más cantidad. Stock disponible: $stockDisponible unidades");
            } else {
                // Actualizar cantidad y subtotal
                $_SESSION['carrito'][$index]['cantidad'] = $nuevaCantidad;
                $_SESSION['carrito'][$index]['subtotal'] = $nuevaCantidad * $_SESSION['carrito'][$index]['Importe'];
                $mensaje = "Cantidad actualizada correctamente";
            }

            $datos = [
                "success" => true,
                "message" => $mensaje,
                "nueva_cantidad" => $nuevaCantidad > 0 ? $nuevaCantidad : 0
            ];
            break;

        case 'eliminar_producto':
            if (!isset($_POST['index'])) {
                throw new Exception("Falta el índice del producto a eliminar");
            }

            $index = intval($_POST['index']);

            if (!isset($_SESSION['carrito'][$index])) {
                throw new Exception("Producto no encontrado en el carrito");
            }

            $productoEliminado = $_SESSION['carrito'][$index]['Descripcion'];
            unset($_SESSION['carrito'][$index]);
            $_SESSION['carrito'] = array_values($_SESSION['carrito']); // Reindexar array

            $datos = [
                "success" => true,
                "message" => "Producto '$productoEliminado' eliminado del carrito",
            ];
            break;

        case 'limpiar_carrito':
            $cantidadProductos = count($_SESSION['carrito']);
            $_SESSION['carrito'] = [];

            $datos = [
                "success" => true,
                "message" => "Carrito vaciado correctamente. Se eliminaron $cantidadProductos productos.",
            ];
            break;

        case 'obtener_carrito':
            // Calcular totales
            $totalItems = 0;
            $totalImporte = 0;
            foreach ($_SESSION['carrito'] as $item) {
                $totalItems += $item['cantidad'];
                $totalImporte += $item['subtotal'];
            }

            $datos = [
                "success" => true,
                "carrito" => $_SESSION['carrito'],
                "resumen" => [
                    'total_items' => $totalItems,
                    'total_importe' => $totalImporte,
                    'cantidad_productos' => count($_SESSION['carrito'])
                ]
            ];
            break;

        default:
            throw new Exception("Acción no válida: $action");
    }

    // Calcular totales actualizados para todas las acciones (excepto obtener_carrito)
    if ($action !== 'obtener_carrito') {
        $totalItems = 0;
        $totalImporte = 0;
        foreach ($_SESSION['carrito'] as $item) {
            $totalItems += $item['cantidad'];
            $totalImporte += $item['subtotal'];
        }

        $datos['carrito_actualizado'] = [
            'total_items' => $totalItems,
            'total_importe' => $totalImporte,
            'cantidad_productos' => count($_SESSION['carrito'])
        ];

        $datos['carritoCount'] = $totalItems;
    }

    echo json_encode($datos);
} catch (Exception $e) {
    $datos = [
        "success" => false,
        "message" => "Error: " . $e->getMessage(),
        "error_details" => $e->getTraceAsString()
    ];
    echo json_encode($datos);
}
