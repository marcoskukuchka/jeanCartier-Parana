<?php
require_once './conn/loader.php';

// Verificar que el usuario esté logueado
if (!isset($_SESSION['vendedor']) || empty($_SESSION['vendedor'])) {
    header('Location: login.php');
    exit;
}
// Continuar con la lógica de productos
$stmt = $dbh->prepare("SELECT * FROM vueWebArticulosDisponibilidad WHERE Deposito > 0 AND Stock > 0 ORDER BY Foto ASC, Importe ASC");
$stmt->execute();
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
$productos = array_group_by($productos, 'CodArticulo');
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <?php require_once './includes/head.php' ?>
</head>

<body>
    <?php require_once './includes/header.php' ?>
    <main>
        <div class="container">
            <div class="mt-4 mb-3">
                <div class="form-group col-xs-9">
                    <input class="form-control" type="text" placeholder="Buscar" id="filtro" />
                </div>
            </div>
            <div class="container my-4">

                <div class="row fw-bold border-bottom pb-2 d-none d-md-flex">
                    <div class="col-md-1">Foto</div>
                    <div class="col-md-2">Cod. Artículo</div>
                    <div class="col-md-4">Descripción</div>
                    <div class="col-md-4">Precio Unitario</div>
                </div>
                <div id="productos-table">
                    <?php
                    $indice = 0;
                    foreach ($productos as $key => $value) : ?>
                        <?php
                        //obtengo los modelos y colores de los articulos
                        $variantes = [];
                        $fotosUnicas = []; // Array para almacenar fotos únicas
                        
                        foreach ($value as $item) {
                            $variantes[] = [
                                'modelo' => $item['ModDescripcion'],
                                'color' => $item['ColDescripcion'],
                                'stock' => $item['Stock'],
                                'codBarra' => $item['CodBarra'],
                                'importe' => $item['Importe']
                            ];
                            
                            // Agregar foto única si no existe ya
                            if (!empty($item['Foto']) && !in_array($item['Foto'], $fotosUnicas)) {
                                $fotosUnicas[] = $item['Foto'];
                            }
                        }

                        // Organizar por modelo y color con stock
                        $resultado = [];
                        foreach ($variantes as $item) {
                            $modelo = $item['modelo'];
                            $color = $item['color'];

                            if (!isset($resultado[$modelo])) {
                                $resultado[$modelo] = [];
                            }

                            // Buscar si ya existe este color en el modelo
                            $colorExiste = false;
                            foreach ($resultado[$modelo] as &$colorData) {
                                if ($colorData['color'] === $color) {
                                    $colorData['stock'] += $item['stock'];
                                    $colorExiste = true;
                                    break;
                                }
                            }

                            if (!$colorExiste) {
                                $resultado[$modelo][] = [
                                    'color' => $color,
                                    'stock' => $item['stock'],
                                    'codBarra' => $item['codBarra'],
                                    'importe' => $item['importe']
                                ];
                            }
                        }

                        // Crear JSON para JavaScript
                        $productoData = json_encode($resultado);
                        ?>
                        <div class="row border-bottom py-3 align-items-center item row-striped"
                            data-nombre="<?= implode(' ', $fotosUnicas) . " " . $value[0]['ModDescripcion'] . " " . $value[0]['CodArticulo'] . " " . $value[0]['Descripcion'] . " " . $value[0]['DescripCodBarracion'] ?> ">
                            <div class="col-md-1">
                                <p class="fw-bold d-md-none mb-0">Foto</p>
                                <p class="mb-0">
                                    <?php 
                                    // Mostrar todas las fotos únicas separadas por coma
                                    echo implode(' ', $fotosUnicas);
                                    ?>
                                </p>
                            </div>
                            <div class="col-md-2">
                                <p class="fw-bold d-md-none mb-0">Cod. Artículo</p>
                                <p class="mb-0"><?= $value[0]['CodArticulo'] ?></p>
                            </div>

                            <div class="col-md-4">
                                <p class="fw-bold d-md-none mb-0">Descripción</p>
                                <p class="mb-0"><?= $value[0]['Descripcion'] ?></p>
                            </div>
                            <div class="col-md-2 d-flex flex-wrap align-items-center gap-2">
                                <div>
                                    <p class="fw-bold d-md-none mb-0">Precio Unitario</p>
                                    <p class="mb-0">$ <?= number_format($value[0]['Importe'], 2, ',', '.') ?></p>
                                </div>
                            </div>
                            <div
                                class="col-md-3 d-flex flex-wrap align-items-center  justify-content-end justify-content-md-center gap-2">

                                <a class="btn btn-secondary toggle-producto" href="#items-<?= $indice ?>"
                                    role="button" aria-expanded="false" aria-controls="items-<?= $indice ?>">
                                    Ver más
                                </a>
                            </div>
                            <div class="collapse mt-2" id="items-<?= $indice ?>">
                                <div class="card card-body">
                                    <form action="ajax/agregaCarrito.php" method="post" id="form<?= $indice ?>">
                                        <div class="col-12 d-flex flex-column flex-lg-row">
                                            <div
                                                class="col-12 col-lg-3 d-flex justify-content-center align-items-center justify-content-lg-start">
                                                <img src="../productos/<?= $value[0]['CodBarra'] ?>.jpg"
                                                    alt="<?= $value[0]['CodBarra'] ?>" class="img-fluid rounded-3"
                                                    id="imagen-producto-<?= $indice ?>">
                                            </div>
                                            <div class="col-12 col-lg-9 mt-2 mt-lg-0">
                                                <div class="ms-1 ms-lg-3">
                                                    <div class="radio-group">
                                                        <div class="d-flex flex-column">
                                                            <p class="mb-1 fs-14 fw-bold">Modelo:</p>
                                                            <div class="d-flex flex-wrap gap-1" id="modelos-<?= $indice ?>">
                                                                <?php foreach ($resultado as $modelo => $colores) : ?>
                                                                    <div class="form-check">
                                                                        <input class="form-check-input" type="radio"
                                                                            name="ModDescripcion"
                                                                            id="modelo-<?= $modelo . '-' . $indice ?>"
                                                                            value="<?= $modelo ?>"
                                                                            onchange="cambiarModelo(<?= $indice ?>, '<?= $modelo ?>')">
                                                                        <label class="form-check-label"
                                                                            for="modelo-<?= $modelo . '-' . $indice ?>">
                                                                            <?= $modelo ?>
                                                                        </label>
                                                                    </div>
                                                                <?php endforeach ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="radio-group mt-3">
                                                        <div class="d-flex flex-column">
                                                            <p class="mb-1 fs-14 fw-bold">Colores:</p>
                                                            <div class="d-flex flex-wrap gap-1" id="colores-<?= $indice ?>">
                                                                <p class="text-muted">Selecciona un modelo para ver los
                                                                    colores disponibles</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="mt-3" id="stock-info-<?= $indice ?>" style="display: none;">
                                                        <div class="alert alert-info">
                                                            <strong>Stock disponible:</strong> <span
                                                                id="stock-cantidad-<?= $indice ?>">0</span> unidades
                                                        </div>
                                                    </div>
                                                    <div class="mt-3" id="agregar-carrito-<?= $indice ?>"
                                                        style="display: none;">
                                                        <div class="row">
                                                            <div class="col-md-4">
                                                                <label for="cantidad-<?= $indice ?>"
                                                                    class="form-label">Cantidad:</label>
                                                                <input type="number" class="form-control"
                                                                    id="cantidad-<?= $indice ?>" name="cantidad" min="1"
                                                                    value="1">
                                                            </div>
                                                            <div
                                                                class="col-12 col-lg-4 d-flex align-items-end mt-2 mt-lg-0">
                                                                <button type="submit" class="btn btn-primary w-100 fs-14">
                                                                    <i class="fas fa-cart-plus me-1"></i>
                                                                    Agregar al Carrito
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Campos ocultos para el formulario -->
                                        <input type="hidden" name="CodArticulo" value="<?= $value[0]['CodArticulo'] ?>">
                                        <input type="hidden" name="Descripcion" value="<?= $value[0]['Descripcion'] ?>">
                                        <input type="hidden" name="Foto" value="<?= implode(', ', $fotosUnicas) ?>">
                                        <input type="hidden" name="ColDescripcion" id="color-seleccionado-<?= $indice ?>">
                                        <input type="hidden" name="CodBarra" id="codbarra-seleccionado-<?= $indice ?>">
                                        <input type="hidden" name="Importe" id="importe-seleccionado-<?= $indice ?>">
                                        <input type="hidden" name="ModDescripcion" id="modelo-seleccionado-<?= $indice ?>">
                                        <input type="hidden" name="Stock" id="stock-seleccionado-<?= $indice ?>">

                                        <!-- Datos del producto para JavaScript -->
                                        <script>
                                            window.productoData<?= $indice ?> = <?= $productoData ?>;
                                        </script>
                                    </form>
                                </div>
                            </div>
                        </div>

                    <?php
                        $indice++;
                    endforeach ?>

                    <!-- <div class="d-flex justify-content-center align-items-center flex-column" style="display: none;">
                        <div id="spinner" class="spinner-border text-secondary mt-5" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div> -->
                </div>
            </div>
        </div>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script>
        // Función para cambiar modelo y mostrar colores disponibles
        function cambiarModelo(indice, modelo) {
            const productoData = window[`productoData${indice}`];
            const coloresContainer = document.getElementById(`colores-${indice}`);
            const stockInfo = document.getElementById(`stock-info-${indice}`);
            const agregarCarrito = document.getElementById(`agregar-carrito-${indice}`);

            // Limpiar selección previa de colores
            document.getElementById(`color-seleccionado-${indice}`).value = '';
            document.getElementById(`codbarra-seleccionado-${indice}`).value = '';
            document.getElementById(`importe-seleccionado-${indice}`).value = '';
            document.getElementById(`modelo-seleccionado-${indice}`).value = modelo;
            document.getElementById(`stock-seleccionado-${indice}`).value = '';

            // Resetear imagen al modelo por defecto (primer color disponible)
            const imagenProducto = document.getElementById(`imagen-producto-${indice}`);
            if (productoData && productoData[modelo] && productoData[modelo].length > 0) {
                const primerColor = productoData[modelo][0];
                const imagenDefault = `../productos/${primerColor.codBarra}.jpg`;
                imagenProducto.src = imagenDefault;
                imagenProducto.alt = primerColor.codBarra;
            }

            if (productoData && productoData[modelo]) {
                // Mostrar colores disponibles para el modelo seleccionado
                let coloresHTML = '';
                productoData[modelo].forEach((colorData, index) => {
                    const stockClass = colorData.stock > 0 ? '' : 'text-danger';
                    const disabled = colorData.stock <= 0 ? 'disabled' : '';

                    coloresHTML += `
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="ColDescripcion" 
                                   id="color-${indice}-${index}" 
                                   value="${colorData.color}" 
                                   ${disabled}
                                   onchange="seleccionarColor(${indice}, '${colorData.color}', ${colorData.stock}, '${colorData.codBarra}', ${colorData.importe})">
                            <label class="form-check-label ${stockClass}" for="color-${indice}-${index}">
                                ${colorData.color} 
                                <small class="ms-1">(${parseInt(colorData.stock)} unidades)</small>
                            </label>
                        </div>
                    `;
                });

                coloresContainer.innerHTML = coloresHTML;
                stockInfo.style.display = 'none';
                agregarCarrito.style.display = 'none';

            } else {
                coloresContainer.innerHTML = '<p class="text-muted">No hay colores disponibles para este modelo</p>';
                stockInfo.style.display = 'none';
                agregarCarrito.style.display = 'none';
            }
        }

        // Función para seleccionar color y mostrar stock
        function seleccionarColor(indice, color, stock, codBarra, importe) {
            const stockCantidad = document.getElementById(`stock-cantidad-${indice}`);
            const stockInfo = document.getElementById(`stock-info-${indice}`);
            const agregarCarrito = document.getElementById(`agregar-carrito-${indice}`);
            const cantidadInput = document.getElementById(`cantidad-${indice}`);
            const imagenProducto = document.getElementById(`imagen-producto-${indice}`);

            // Actualizar campos ocultos
            document.getElementById(`color-seleccionado-${indice}`).value = color;
            document.getElementById(`codbarra-seleccionado-${indice}`).value = codBarra;
            document.getElementById(`importe-seleccionado-${indice}`).value = importe;
            document.getElementById(`stock-seleccionado-${indice}`).value = stock;

            // Cambiar la imagen del producto
            const nuevaImagen = `../productos/${codBarra}.jpg`;

            // Agregar efecto de transición suave
            imagenProducto.style.opacity = '0.7';
            imagenProducto.classList.add('img-loading');

            // Crear una nueva imagen para verificar si existe
            const tempImg = new Image();
            tempImg.onload = function() {
                // La imagen existe, actualizar la imagen del producto
                imagenProducto.src = nuevaImagen;
                imagenProducto.alt = codBarra;
                imagenProducto.classList.remove('img-loading');
                imagenProducto.classList.add('img-loaded');

                setTimeout(() => {
                    imagenProducto.style.opacity = '1';
                }, 150);
            };

            tempImg.onerror = function() {
                // La imagen no existe, mantener la imagen por defecto
                imagenProducto.classList.remove('img-loading');
                imagenProducto.classList.add('img-loaded');
                imagenProducto.style.opacity = '1';

                // Opcional: mostrar un mensaje o usar una imagen por defecto
                console.log(`Imagen no encontrada: ${nuevaImagen}`);
            };

            tempImg.src = nuevaImagen;

            // Mostrar información de stock
            stockCantidad.textContent = stock;
            stockInfo.style.display = 'block';

            // Configurar cantidad máxima
            cantidadInput.max = stock;
            cantidadInput.setAttribute('max', stock);

            // Resetear el valor si excede el stock disponible
            if (parseInt(cantidadInput.value) > stock) {
                cantidadInput.value = stock;
            }

            // La validación de cantidad máxima se maneja automáticamente por HTML5
            // usando el atributo 'max' que se actualiza arriba

            // Mostrar botón de agregar al carrito
            agregarCarrito.style.display = 'block';
        }

        // Función de búsqueda
        $(document).ready(function() {
            // Manejar toggle manual con slideToggle de jQuery
            $('a.toggle-producto').on('click', function(e) {
                e.preventDefault();
                var target = $(this).attr('href');
                var collapseElement = $(target);
                var button = $(this);

                console.log('Click en Ver más - Target:', target);
                console.log('Elemento collapse:', collapseElement);
                console.log('Visible antes:', collapseElement.is(':visible'));

                // Usar slideToggle para animación suave
                collapseElement.slideToggle(300, function() {
                    // Actualizar atributos después de la animación
                    if (collapseElement.is(':visible')) {
                        button.attr('aria-expanded', 'true');
                        collapseElement.addClass('show');
                        button.text('Ver menos');
                        console.log('Abierto - aria-expanded: true');
                    } else {
                        button.attr('aria-expanded', 'false');
                        collapseElement.removeClass('show');
                        button.text('Ver más');
                        console.log('Cerrado - aria-expanded: false');
                    }
                });
            });

            $('#filtro').on('keyup', function() {
                const searchTerm = $(this).val().toLowerCase();
                $('.item').each(function() {
                    const nombre = $(this).data('nombre').toLowerCase();
                    if (nombre.includes(searchTerm)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });

            // Envío de formularios por AJAX
            $('form[id^="form"]').on('submit', function(e) {
                e.preventDefault();

                const form = $(this);
                const formId = form.attr('id');
                const indice = formId.replace('form', '');
                const submitBtn = form.find('button[type="submit"]');
                const originalText = submitBtn.html();

                // Validar que se haya seleccionado modelo y color
                const modelo = form.find('input[name="ModDescripcion"]:checked').val();
                const color = form.find('input[name="ColDescripcion"]:checked').val();
                const cantidad = form.find('input[name="cantidad"]').val();

                if (!modelo) {
                    mostrarAlerta('Por favor selecciona un modelo', 'warning');
                    return false;
                }

                if (!color) {
                    mostrarAlerta('Por favor selecciona un color', 'warning');
                    return false;
                }

                if (!cantidad || cantidad <= 0) {
                    mostrarAlerta('Por favor ingresa una cantidad válida', 'warning');
                    return false;
                }

                // Verificar stock disponible
                const stockDisponible = parseInt(form.find('input[name="Stock"]').val());
                if (cantidad > stockDisponible) {
                    mostrarAlerta(`La cantidad solicitada (${cantidad}) excede el stock disponible (${stockDisponible})`, 'warning');
                    return false;
                }

                // Cambiar texto del botón y deshabilitar
                submitBtn.html('<i class="fas fa-spinner fa-spin me-1"></i>Agregando...');
                submitBtn.prop('disabled', true);

                // Obtener todos los datos del formulario
                const formData = new FormData(this);

                // Enviar por AJAX
                $.ajax({
                    url: 'ajax/agregaCarrito.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            mostrarAlerta(response.message, 'success');

                            // Limpiar formulario
                            //form[0].reset();

                            // Ocultar sección de agregar al carrito
                            $(`#agregar-carrito-${indice}`).hide();
                            $(`#stock-info-${indice}`).hide();

                            // Desmarcar selecciones de color
                            form.find('input[name="ColDescripcion"]').prop('checked', false);

                            // Actualizar contador del carrito si existe
                            if (response.carritoCount !== undefined) {
                                actualizarContadorCarrito(response.carritoCount);
                            }

                        } else {
                            mostrarAlerta(response.message || 'Error al agregar al carrito',
                                'danger');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error AJAX:', error);
                        mostrarAlerta('Error de conexión. Intenta nuevamente.', 'danger');
                    },
                    complete: function() {
                        // Restaurar botón
                        submitBtn.html(originalText);
                        submitBtn.prop('disabled', false);
                    }
                });
            });
        });

        // Función para mostrar alertas
        function mostrarAlerta(mensaje, tipo) {
            // Remover alertas previas
            $('.alert-floating').remove();

            // Crear alerta
            const alerta = $(`
                <div class="alert alert-${tipo} alert-floating alert-dismissible fade show" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                    <i class="fas fa-${tipo === 'success' ? 'check-circle' : tipo === 'warning' ? 'exclamation-triangle' : 'times-circle'} me-2"></i>
                    ${mensaje}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `);

            // Agregar al body
            $('body').append(alerta);

            // Auto-ocultar después de 5 segundos
            setTimeout(function() {
                alerta.alert('close');
            }, 5000);
        }

        // Función para actualizar contador del carrito
        function actualizarContadorCarrito(count) {
            const contador = $('.carrito-contador');
            if (contador.length > 0) {
                contador.text(count);
                contador.addClass('animate__animated animate__pulse');
                setTimeout(() => {
                    contador.removeClass('animate__animated animate__pulse');
                }, 1000);
            }
        }
    </script>

</body>

</html>