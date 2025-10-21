<?php
require_once './conn/loader.php';

// Verificar que el usuario esté logueado
if (!isset($_SESSION['vendedor']) || empty($_SESSION['vendedor'])) {
    header('Location: login.php');
    exit;
}

// Obtener presupuestos del cliente
$presupuestos = getPresupuestoCliente($dbh, $_SESSION['vendedor']['IdCliente']);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <?php require_once './includes/head.php' ?>
    <title>Presupuestos - Jean Cartier</title>
</head>

<body>
    <?php require_once './includes/header.php' ?>
    <main>
        <div class="container">
            <div class="mt-4 mb-3">
                <!-- Header responsive -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="mb-0 fs-4 fs-md-2"><i class="fas fa-file-invoice-dollar me-2"></i>Mis Presupuestos</h2>
                    <!-- Botones desktop -->
                    <div class="d-none d-lg-block">
                        <a href="./productos.php" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left me-1"></i>Volver a Productos
                        </a>
                    </div>
                </div>

                <!-- Botones móvil -->
                <div class="d-lg-none mb-3">
                    <div class="d-grid">
                        <a href="./productos.php" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left me-1"></i>Volver a Productos
                        </a>
                    </div>
                </div>
            </div>

            <?php if (empty($presupuestos)): ?>
                <!-- Sin presupuestos -->
                <div class="container my-5">
                    <div class="text-center py-5">
                        <i class="fas fa-file-invoice fa-5x text-muted mb-4"></i>
                        <h3 class="text-muted">No hay presupuestos disponibles</h3>
                        <p class="text-muted mb-4">Aún no tienes presupuestos registrados en el sistema.</p>
                        <a href="./productos.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-shopping-bag me-2"></i>Ver Productos
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Tabla de presupuestos -->
                <div class="container my-5">
                    <div class="row fw-bold border-bottom pb-2 d-none d-md-flex">
                        <div class="col-md-3">Nº Presupuesto</div>
                        <div class="col-md-3">Estado</div>
                        <div class="col-md-3">Sub Total</div>
                        <div class="col-md-3">Total Pendiente</div>
                    </div>
                    <div id="productos-table">
                        <?php 
                        $totalFinal = 0;
                        foreach ($presupuestos as $key => $value):
                            $totalFinal += $value['ImpTotalPendiente'];
                        ?>
                            <div class="row border-bottom py-3 align-items-center item row-striped">
                                <div class="col-md-3">
                                    <p class="fw-bold d-md-none mb-0">Nº Presupuesto</p>
                                    <p class="mb-0"><?= $value['Numero'] ?></p>
                                </div>
                                <div class="col-md-3">
                                    <p class="fw-bold d-md-none mb-0">Estado</p>
                                    <p class="mb-0">
                                        <?= $value['Estado'] == 'P' ? '<span class="badge bg-danger">Pendiente</span>' : $value['Estado'] ?>
                                    </p>
                                </div>
                                <div class="col-md-3">
                                    <p class="fw-bold d-md-none mb-0">Total</p>
                                    <p class="mb-0">$ <?= number_format($value['ImpTotal'], 2, ",", ".") ?></p>
                                </div>
                                <div class="col-md-3 d-flex flex-wrap align-items-center gap-2">
                                    <div>
                                        <p class="fw-bold d-md-none mb-0">Total Pendiente</p>
                                        <p class="mb-0">$ <?= number_format($value['ImpTotalPendiente'], 2, ",", ".") ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach ?>
                    </div>

                    <!-- Resumen total -->
                    <div class="row mt-4">
                        <div class="col-md-8"></div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0"><i class="fas fa-calculator me-2"></i>Resumen Total</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Total de Presupuestos:</span>
                                        <strong><?= count($presupuestos) ?></strong>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between mb-3">
                                        <h5>Total Pendiente:</h5>
                                        <h5 class="text-danger">$ <?= number_format($totalFinal, 2, ',', '.') ?></h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</body>

</html>