<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$auth = new Auth();
$auth->checkAdmin();

$parking = new ParkingFunctions();

$fecha = $_GET['fecha'] ?? date('Y-m-d');
$registros = $parking->obtenerRegistros($fecha, $fecha);
$totales = $parking->obtenerTotales($fecha, $fecha);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte Diario - Sistema de Parqueadero</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <div class="container mt-4">
        <h2>Reporte Diario</h2>
        
        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-4">
                <label for="fecha" class="form-label">Fecha</label>
                <input type="date" class="form-control" id="fecha" name="fecha" value="<?php echo $fecha; ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100">Filtrar</button>
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <button type="button" class="btn btn-success w-100" onclick="window.print()">
                    <i class="bi bi-printer"></i> Imprimir
                </button>
            </div>
        </form>
        
        <div class="card mb-4">
            <div class="card-header">
                Resumen del Día
            </div>
            <div class="card-body">
                <div class="row">
                    <?php 
                    $total_recaudado = 0;
                    foreach ($totales as $total) {
                        $total_recaudado += $total['total_recaudado'];
                    }
                    ?>
                    <div class="col-md-4">
                        <p><strong>Total Recaudado:</strong> $<?php echo number_format($total_recaudado, 2); ?></p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>Vehículos en Parqueadero:</strong> 
                            <?php 
                            $en_parqueadero = array_reduce($registros, function($carry, $item) {
                                return $carry + ($item['hora_salida'] === null ? 1 : 0);
                            }, 0);
                            echo $en_parqueadero;
                            ?>
                        </p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>Total Vehículos:</strong> <?php echo count($registros); ?></p>
                    </div>
                </div>
                
                <h5 class="mt-3">Por Método de Pago</h5>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Método</th>
                                <th>Cantidad</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($totales as $total): ?>
                                <tr>
                                    <td><?php echo ucfirst($total['metodo_pago']); ?></td>
                                    <td><?php echo $total['cantidad']; ?></td>
                                    <td>$<?php echo number_format($total['total_recaudado'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                Detalle de Movimientos
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Placa</th>
                                <th>Tipo</th>
                                <th>Entrada</th>
                                <th>Salida</th>
                                <th>Tiempo</th>
                                <th>Valor</th>
                                <th>Método Pago</th>
                                <th>Atendió</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($registros as $registro): ?>
                                <tr>
                                    <td><?php echo $registro['placa']; ?></td>
                                    <td><?php echo ucfirst($registro['tipo_vehiculo']); ?></td>
                                    <td><?php echo date('H:i', strtotime($registro['hora_entrada'])); ?></td>
                                    <td><?php echo $registro['hora_salida'] ? date('H:i', strtotime($registro['hora_salida'])) : '--'; ?></td>
                                    <td><?php echo $registro['tiempo_estadia'] ?? '--'; ?></td>
                                    <td><?php echo $registro['valor_pagar'] ? '$' . number_format($registro['valor_pagar'], 2) : '--'; ?></td>
                                    <td><?php echo $registro['metodo_pago'] ? ucfirst($registro['metodo_pago']) : '--'; ?></td>
                                    <td><?php echo $registro['nombre_entrada']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>