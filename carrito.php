<?php
require_once './conn/loader.php';

// Verificar que el usuario esté logueado
if (!isset($_SESSION['vendedor']) || empty($_SESSION['vendedor'])) {
    header('Location: ./login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <?php require_once './includes/head.php' ?>
    <title>Carrito - Jean Cartier Córdoba</title>
</head>

<body>
    <?php require_once './includes/header.php' ?>
    <main>
        <div class="container">
            <div class="mt-4 mb-3">
                <!-- Header responsive -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="mb-0 fs-4 fs-md-2"><i class="fas fa-shopping-cart me-2"></i>Mi Carrito</h2>
                    <!-- Botones desktop -->
                    <div class="d-none d-lg-block">
                        <a href="./productos.php" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left me-1"></i>Seguir Comprando
                        </a>
                        <?php if (isset($_SESSION['carrito']) && !empty($_SESSION['carrito'])): ?>
                            <button class="btn btn-outline-danger ms-2" onclick="limpiarCarrito()">
                                <i class="fas fa-trash me-1"></i>Vaciar Carrito
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Botones móvil -->
                <div class="d-lg-none mb-3">
                    <div class="d-grid gap-2 d-sm-flex">
                        <a href="./productos.php" class="btn btn-outline-primary flex-fill">
                            <i class="fas fa-arrow-left me-1"></i>Seguir Comprando
                        </a>
                        <?php if (isset($_SESSION['carrito']) && !empty($_SESSION['carrito'])): ?>
                            <button class="btn btn-outline-danger flex-fill" onclick="limpiarCarrito()">
                                <i class="fas fa-trash me-1"></i>Vaciar
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if (!isset($_SESSION['carrito']) || empty($_SESSION['carrito'])): ?>
                <!-- Carrito vacío -->
                <div class="container my-4">
                    <div class="text-center py-5">
                        <i class="fas fa-shopping-cart fa-5x text-muted mb-4"></i>
                        <h3 class="text-muted">Tu carrito está vacío</h3>
                        <p class="text-muted mb-4">¡Explora nuestros productos y encuentra algo que te guste!</p>
                        <a href="./productos.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-shopping-bag me-2"></i>Ver Productos
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Carrito con productos -->
                <div class="container my-4">
                    <!-- Encabezado del carrito - solo desktop -->
                    <div class="row fw-bold border-bottom pb-2 d-none d-lg-flex">
                        <div class="col-lg-1">Foto</div>
                        <div class="col-lg-3">Producto</div>
                        <div class="col-lg-2">Modelo/Color</div>
                        <div class="col-lg-2">Precio Unit.</div>
                        <div class="col-lg-2">Cantidad</div>
                        <div class="col-lg-1">Subtotal</div>
                        <div class="col-lg-1">Acciones</div>
                    </div>

                    <!-- Productos en el carrito -->
                    <div id="carrito-items">
                        <?php
                        $totalGeneral = 0;
                        $totalItems = 0;
                        foreach ($_SESSION['carrito'] as $index => $producto):
                            $totalGeneral += $producto['subtotal'];
                            $totalItems += $producto['cantidad'];
                        ?>
                            <!-- Datos para JavaScript -->
                            <script>
                                if (!window.stockData) window.stockData = {};
                                if (!window.cantidadData) window.cantidadData = {};
                                window.stockData[<?= $index ?>] = <?= $producto['stock_disponible'] ?? 0 ?>;
                                window.cantidadData[<?= $index ?>] = <?= $producto['cantidad'] ?>;
                            </script>
                            <!-- Vista Desktop -->
                            <div class="row border-bottom py-3 align-items-center item-carrito d-none d-lg-flex">
                                <!-- Foto del producto -->
                                <div class="col-lg-1">
                                    <div class="d-flex justify-content-center">
                                        <img src="../productos/<?= $producto['CodBarra'] ?>.jpg"
                                            alt="<?= htmlspecialchars($producto['Descripcion']) ?>" class="img-fluid rounded-3"
                                            style="width: 60px; height: 60px; object-fit: cover;"
                                            onerror="this.src='../assets/img/no-image.jpg'">
                                    </div>
                                </div>

                                <!-- Información del producto -->
                                <div class="col-lg-3">
                                    <div>
                                        <h6 class="mb-1"><?= htmlspecialchars($producto['Descripcion']) ?></h6>
                                        <small class="text-muted">Código: <?= $producto['CodArticulo'] ?></small>
                                    </div>
                                </div>

                                <!-- Modelo y Color -->
                                <div class="col-lg-2">
                                    <div>
                                        <span
                                            class="badge bg-primary mb-1"><?= htmlspecialchars($producto['ModDescripcion']) ?></span><br>
                                        <span
                                            class="badge bg-secondary mb-1"><?= htmlspecialchars($producto['ColDescripcion']) ?></span><br>
                                        <?php if (isset($producto['stock_disponible'])): ?>
                                            <!-- <span class="badge bg-success">Stock: <?= $producto['stock_disponible'] ?></span> -->
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Precio unitario -->
                                <div class="col-lg-2">
                                    <p class="mb-0 fw-bold">$<?= number_format($producto['Importe'], 2, ',', '.') ?></p>
                                </div>

                                <!-- Cantidad -->
                                <div class="col-lg-2">
                                    <div class="d-flex align-items-center justify-content-center">
                                        <button class="btn btn-outline-secondary btn-sm" type="button"
                                            onclick="cambiarCantidad(<?= $index ?>, -1)">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <div class="mx-3 text-center">
                                            <span class="fw-bold d-block"><?= $producto['cantidad'] ?></span>
                                            <?php if ($producto['cantidad'] >= ($producto['stock_disponible'] ?? 0)): ?>
                                                <small class="text-warning">Stock máximo</small>
                                            <?php endif; ?>
                                        </div>
                                        <button
                                            class="btn btn-outline-secondary btn-sm <?= $producto['cantidad'] >= ($producto['stock_disponible'] ?? 0) ? 'disabled' : '' ?>"
                                            type="button" onclick="cambiarCantidad(<?= $index ?>, 1)"
                                            <?= $producto['cantidad'] >= ($producto['stock_disponible'] ?? 0) ? 'disabled title="Stock máximo alcanzado"' : '' ?>>
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Subtotal -->
                                <div class="col-lg-1">
                                    <p class="mb-0 fw-bold text-success">
                                        $<?= number_format($producto['subtotal'], 2, ',', '.') ?></p>
                                </div>

                                <!-- Acciones -->
                                <div class="col-lg-1">
                                    <div class="d-flex justify-content-center">
                                        <button class="btn btn-outline-danger btn-sm" onclick="eliminarProducto(<?= $index ?>)"
                                            title="Eliminar producto">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Vista Móvil -->
                            <div class="card mb-3 d-lg-none item-carrito-mobile">
                                <div class="card-body p-3">
                                    <div class="row g-2">
                                        <!-- Imagen y título -->
                                        <div class="col-4">
                                            <img src="../productos/<?= $producto['CodBarra'] ?>.jpg"
                                                alt="<?= htmlspecialchars($producto['Descripcion']) ?>"
                                                class="img-fluid rounded" style="width: 100%; height: 80px; object-fit: cover;"
                                                onerror="this.src='../assets/img/no-image.jpg'">
                                        </div>
                                        <div class="col-8">
                                            <h6 class="card-title mb-1 fs-14"><?= htmlspecialchars($producto['Descripcion']) ?>
                                            </h6>
                                            <p class="text-muted mb-1 fs-12">Código: <?= $producto['CodArticulo'] ?></p>
                                            <div class="mb-2">
                                                <span
                                                    class="badge bg-primary me-1 fs-10"><?= htmlspecialchars($producto['ModDescripcion']) ?></span>
                                                <span
                                                    class="badge bg-secondary fs-10"><?= htmlspecialchars($producto['ColDescripcion']) ?></span>
                                                <?php if (isset($producto['stock_disponible'])): ?>
                                                    <span class="badge bg-success fs-10">Stock:
                                                        <?= $producto['stock_disponible'] ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Precio y cantidad -->
                                    <div class="row g-2 mt-2">
                                        <div class="col-6">
                                            <div class="text-center">
                                                <small class="text-muted d-block">Precio unitario</small>
                                                <strong
                                                    class="fs-16">$<?= number_format($producto['Importe'], 2, ',', '.') ?></strong>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="text-center">
                                                <small class="text-muted d-block">Cantidad</small>
                                                <div class="d-flex align-items-center justify-content-center mt-1">
                                                    <button class="btn btn-outline-secondary btn-sm py-1 px-2" type="button"
                                                        onclick="cambiarCantidad(<?= $index ?>, -1)">
                                                        <i class="fas fa-minus fa-xs"></i>
                                                    </button>
                                                    <div class="mx-2 text-center">
                                                        <span class="fw-bold fs-16 d-block"><?= $producto['cantidad'] ?></span>
                                                        <?php if ($producto['cantidad'] >= ($producto['stock_disponible'] ?? 0)): ?>
                                                            <small class="text-warning fs-10">Stock máximo</small>
                                                        <?php endif; ?>
                                                    </div>
                                                    <button
                                                        class="btn btn-outline-secondary btn-sm py-1 px-2 <?= $producto['cantidad'] >= ($producto['stock_disponible'] ?? 0) ? 'disabled' : '' ?>"
                                                        type="button" onclick="cambiarCantidad(<?= $index ?>, 1)"
                                                        <?= $producto['cantidad'] >= ($producto['stock_disponible'] ?? 0) ? 'disabled title="Stock máximo alcanzado"' : '' ?>>
                                                        <i class="fas fa-plus fa-xs"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Subtotal y acciones -->
                                    <div class="row g-2 mt-2 pt-2 border-top">
                                        <div class="col-8">
                                            <div class="d-flex align-items-center">
                                                <span class="text-muted me-2">Subtotal:</span>
                                                <strong
                                                    class="text-success fs-18">$<?= number_format($producto['subtotal'], 2, ',', '.') ?></strong>
                                            </div>
                                        </div>
                                        <div class="col-4 text-end">
                                            <button class="btn btn-outline-danger btn-sm"
                                                onclick="eliminarProducto(<?= $index ?>)" title="Eliminar producto">
                                                <i class="fas fa-trash me-1"></i>Eliminar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Resumen del carrito -->
                    <!-- Vista Desktop -->
                    <div class="row mt-4 d-none d-lg-flex">
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Información del Pedido</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <p><strong>Cliente:</strong>
                                                <?= $_SESSION['vendedor']['Email'] ?? 'No disponible' ?></p>
                                            <p><strong>Sucursal:</strong> Córdoba</p>
                                        </div>
                                        <div class="col-sm-6">
                                            <p><strong>Fecha:</strong> <?= date('d/m/Y H:i') ?></p>
                                            <p><strong>Productos únicos:</strong> <?= count($_SESSION['carrito']) ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0"><i class="fas fa-calculator me-2"></i>Total del Pedido</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Total de items:</span>
                                        <strong><?= $totalItems ?></strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-3">
                                        <span>Productos únicos:</span>
                                        <strong><?= count($_SESSION['carrito']) ?></strong>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between mb-3">
                                        <h5>Total:</h5>
                                        <h5 class="text-success">$<?= number_format($totalGeneral, 2, ',', '.') ?></h5>
                                    </div>
                                    <button class="btn btn-success w-100 mb-2" onclick="finalizarPedido(event)">
                                        <i class="fas fa-check me-2"></i>Finalizar Pedido
                                    </button>
                                    <button class="btn btn-outline-secondary w-100" onclick="imprimirPedido()">
                                        <i class="fas fa-print me-2"></i>Imprimir
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Vista Móvil -->
                    <div class="d-lg-none mt-4">
                        <!-- Resumen compacto del total -->
                        <div class="card mb-3">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0 text-center"><i class="fas fa-calculator me-2"></i>Resumen del Pedido</h5>
                            </div>
                            <div class="card-body">
                                <div class="row text-center g-3">
                                    <div class="col-4">
                                        <div class="border-end">
                                            <h6 class="text-muted mb-1">Items</h6>
                                            <h4 class="mb-0 text-primary"><?= $totalItems ?></h4>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="border-end">
                                            <h6 class="text-muted mb-1">Productos</h6>
                                            <h4 class="mb-0 text-primary"><?= count($_SESSION['carrito']) ?></h4>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <h6 class="text-muted mb-1">Total</h6>
                                        <h4 class="mb-0 text-success">$<?= number_format($totalGeneral, 2, ',', '.') ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botones de acción móvil -->
                        <div class="d-grid gap-2">
                            <button class="btn btn-success btn-lg" onclick="finalizarPedido(event)">
                                <i class="fas fa-check me-2"></i>Finalizar Pedido
                            </button>
                            <div class="row g-2">
                                <div class="col-6">
                                    <button class="btn btn-outline-secondary w-100" onclick="imprimirPedido()">
                                        <i class="fas fa-print me-2"></i>Imprimir
                                    </button>
                                </div>
                                <div class="col-6">
                                    <a href="./productos.php" class="btn btn-outline-primary w-100">
                                        <i class="fas fa-plus me-2"></i>Más productos
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Información del vendedor móvil -->
                        <div class="card mt-3">
                            <div class="card-body p-3">
                                <h6 class="card-title mb-2"><i class="fas fa-user me-2"></i>Información del Pedido</h6>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <small class="text-muted">Vendedor:</small>
                                        <p class="mb-1 fs-14"><?= $_SESSION['vendedor']['Email'] ?? 'No disponible' ?></p>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Sucursal:</small>
                                        <p class="mb-1 fs-14">Córdoba</p>
                                    </div>
                                    <div class="col-12">
                                        <small class="text-muted">Fecha:</small>
                                        <p class="mb-0 fs-14"><?= date('d/m/Y H:i') ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Barra flotante móvil con total -->
        <?php if (isset($_SESSION['carrito']) && !empty($_SESSION['carrito'])): ?>
            <div class="fixed-bottom d-lg-none bg-white border-top shadow-lg p-3" id="mobile-cart-summary">
                <div class="container">
                    <div class="row align-items-center">
                        <div class="col-8">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-shopping-cart text-primary me-2"></i>
                                <div>
                                    <strong class="d-block"><?= $totalItems ?> items -
                                        $<?= number_format($totalGeneral, 2, ',', '.') ?></strong>
                                    <small class="text-muted"><?= count($_SESSION['carrito']) ?> producto(s)
                                        único(s)</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <button class="btn btn-success w-100 btn-sm" onclick="finalizarPedido(event)">
                                <i class="fas fa-check me-1"></i>Finalizar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script>
        // Función para cambiar cantidad de productos
        function cambiarCantidad(index, cambio) {
            // Obtener información del producto desde el DOM
            const stockDisponible = parseInt(window.stockData[index]) || 0;
            const cantidadActual = parseInt(window.cantidadData[index]) || 0;
            const nuevaCantidad = cantidadActual + cambio;

            // Validación previa en el cliente
            if (cambio > 0 && nuevaCantidad > stockDisponible) {
                mostrarAlerta(`No se puede agregar más cantidad. Stock disponible: ${stockDisponible} unidades`, 'warning');
                return;
            }

            // Si la validación pasa, enviar al servidor
            $.ajax({
                url: './ajax/actualizar_carrito.php',
                type: 'POST',
                data: {
                    action: 'cambiar_cantidad',
                    index: index,
                    cambio: cambio
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        mostrarAlerta(response.message, 'danger');
                    }
                },
                error: function() {
                    mostrarAlerta('Error de conexión. Intenta nuevamente.', 'danger');
                }
            });
        }

        // Función para eliminar producto
        function eliminarProducto(index) {
            if (confirm('¿Estás seguro de eliminar este producto del carrito?')) {
                $.ajax({
                    url: './ajax/actualizar_carrito.php',
                    type: 'POST',
                    data: {
                        action: 'eliminar_producto',
                        index: index
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            mostrarAlerta(response.message, 'danger');
                        }
                    },
                    error: function() {
                        mostrarAlerta('Error de conexión. Intenta nuevamente.', 'danger');
                    }
                });
            }
        }

        // Función para limpiar carrito
        function limpiarCarrito() {
            if (confirm('¿Estás seguro de vaciar todo el carrito?')) {
                $.ajax({
                    url: './ajax/actualizar_carrito.php',
                    type: 'POST',
                    data: {
                        action: 'limpiar_carrito'
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            mostrarAlerta(response.message, 'danger');
                        }
                    },
                    error: function() {
                        mostrarAlerta('Error de conexión. Intenta nuevamente.', 'danger');
                    }
                });
            }
        }

        // Función para finalizar pedido
        function finalizarPedido(event) {
            // Prevenir comportamiento por defecto
            if (event) {
                event.preventDefault();
            }

            // Confirmar antes de finalizar
            if (!confirm('¿Confirmar la finalización del pedido?')) {
                return;
            }

            // Deshabilitar botones mientras se procesa
            $('button[onclick*="finalizarPedido"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Procesando...');

            $.ajax({
                url: './ajax/finalizarCompra.php',
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    // Verificar el estado de la respuesta
                    if (response.estado === 1) {
                        // Éxito
                        mostrarAlerta(response.mensaje || 'Pedido finalizado correctamente', 'success');
                        
                        // Redirigir a la página de agradecimiento después de 1.5 segundos
                        setTimeout(function() {
                            location.href = './gracias.php';
                        }, 1500);
                    } else {
                        // Error en el procesamiento
                        mostrarAlerta(response.mensaje || 'No se pudo finalizar el pedido', 'danger');
                        
                        // Rehabilitar botones
                        $('button[onclick*="finalizarPedido"]').prop('disabled', false).html('<i class="fas fa-check me-2"></i>Finalizar Pedido');
                    }
                },
                error: function(xhr, status, error) {
                    // Error de conexión o servidor
                    mostrarAlerta('Error de conexión. No se pudo finalizar el pedido. Intenta nuevamente.', 'danger');
                    console.error('Error al finalizar pedido:', error);
                    
                    // Rehabilitar botones
                    $('button[onclick*="finalizarPedido"]').prop('disabled', false).html('<i class="fas fa-check me-2"></i>Finalizar Pedido');
                }
            });
        }

        // Función para imprimir pedido
        function imprimirPedido() {
            window.print();
        }

        // Función para mostrar alertas
        function mostrarAlerta(mensaje, tipo) {
            // Remover alertas previas
            $('.alert-floating').remove();

            // Crear alerta
            const iconos = {
                'success': 'check-circle',
                'danger': 'exclamation-triangle',
                'warning': 'exclamation-triangle',
                'info': 'info-circle'
            };

            const alerta = $(`
                <div class="alert alert-${tipo} alert-floating alert-dismissible fade show" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                    <i class="fas fa-${iconos[tipo]} me-2"></i>
                    ${mensaje}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `);

            // Agregar al body
            $('body').append(alerta);

            // Auto-ocultar después de 5 segundos
            setTimeout(function() {
                alerta.alert('close');
            }, 5000);
        }

        // Función para manejar el scroll en móvil
        $(document).ready(function() {
            // Agregar clase al body si hay barra flotante
            if ($('#mobile-cart-summary').length) {
                $('body').addClass('has-mobile-cart');
            }

            // Animación de scroll deshabilitada - la barra flotante permanece siempre visible
            // (Código comentado para evitar animaciones molestas al hacer scroll)
            /*
            let lastScrollTop = 0;
            $(window).scroll(function() {
                if ($(window).width() < 992) { // Solo en móvil
                    let scrollTop = $(this).scrollTop();

                    if (scrollTop > lastScrollTop && scrollTop > 100) {
                        // Scrolling down - ocultar barra
                        $('#mobile-cart-summary').fadeOut(200);
                    } else {
                        // Scrolling up - mostrar barra
                        $('#mobile-cart-summary').fadeIn(200);
                    }
                    lastScrollTop = scrollTop;
                }
            });

            // Mostrar la barra cuando se llega al final del scroll
            $(window).scroll(function() {
                if ($(window).width() < 992) {
                    if ($(window).scrollTop() + $(window).height() >= $(document).height() - 100) {
                        $('#mobile-cart-summary').fadeIn(200);
                    }
                }
            });
            */

            // Optimizar touch events en móvil
            if ('ontouchstart' in window) {
                $('.btn').on('touchstart', function() {
                    $(this).addClass('active');
                }).on('touchend', function() {
                    setTimeout(() => {
                        $(this).removeClass('active');
                    }, 150);
                });
            }
        });
    </script>
</body>

</html>