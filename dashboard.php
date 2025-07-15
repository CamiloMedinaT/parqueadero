<?php
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$auth = new Auth();
$auth->checkAuth();

$parking = new ParkingFunctions();
$espacios = $parking->obtenerEspacios();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema de Parqueadero</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container mt-4">
        <h2>Estado del Parqueadero</h2>
        
        <div class="row mt-4">
            <?php foreach ($espacios as $espacio): ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title text-uppercase"><?php echo $espacio['tipo_vehiculo']; ?></h5>
                            <div class="d-flex justify-content-between">
                                <span>Disponibles:</span>
                                <span class="fw-bold"><?php echo $espacio['disponibles']; ?></span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Ocupados:</span>
                                <span class="fw-bold"><?php echo $espacio['ocupados']; ?></span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Total:</span>
                                <span class="fw-bold"><?php echo $espacio['total']; ?></span>
                            </div>
                            <div class="progress mt-2">
                                <?php 
                                $porcentaje = ($espacio['ocupados'] / $espacio['total']) * 100;
                                $color = $porcentaje > 80 ? 'bg-danger' : ($porcentaje > 50 ? 'bg-warning' : 'bg-success');
                                ?>
                                <div class="progress-bar <?php echo $color; ?>" role="progressbar" 
                                     style="width: <?php echo $porcentaje; ?>%" 
                                     aria-valuenow="<?php echo $porcentaje; ?>" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="row mt-4">
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Registrar Entrada</h5>
                        <a href="vehicle_entry.php" class="btn btn-primary w-100">
                            <i class="bi bi-plus-circle"></i> Nuevo Registro
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Registrar Salida</h5>
                        <a href="vehicle_exit.php" class="btn btn-danger w-100">
                            <i class="bi bi-dash-circle"></i> Registrar Salida
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if ($auth->isAdmin()): ?>
        <div class="row mt-4">
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Reportes</h5>
                        <div class="d-grid gap-2">
                            <a href="reports/daily_report.php" class="btn btn-outline-secondary">
                                <i class="bi bi-file-earmark-text"></i> Reporte Diario
                            </a>
                            <a href="reports/monthly_report.php" class="btn btn-outline-secondary">
                                <i class="bi bi-file-earmark-spreadsheet"></i> Reporte Mensual
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Administración</h5>
                        <div class="d-grid gap-2">
                            <a href="settings.php" class="btn btn-outline-secondary">
                                <i class="bi bi-gear"></i> Configuración
                            </a>
                            <a href="users.php" class="btn btn-outline-secondary">
                                <i class="bi bi-people"></i> Usuarios
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>