<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$auth = new Auth();
$auth->checkAdmin();

$parking = new ParkingFunctions();

$mes = $_GET['mes'] ?? date('m');
$anio = $_GET['anio'] ?? date('Y');
$fecha_inicio = "$anio-$mes-01";
$fecha_fin = "$anio-$mes-" . date('t', strtotime($fecha_inicio));

$registros = $parking->obtenerRegistros($fecha_inicio, $fecha_fin);
$totales = $parking->obtenerTotales($fecha_inicio, $fecha_fin);

// Agrupar por día para el gráfico
$datos_grafico = [];
for ($i = 1; $i <= date('t', strtotime($fecha_inicio)); $i++) {
    $dia = sprintf("%02d", $i);
    $fecha_dia = "$anio-$mes-$dia";
    $totales_dia = $parking->obtenerTotales($fecha_dia, $fecha_dia);
    
    $total_dia = 0;
    foreach ($totales_dia as $total) {
        $total_dia += $total['total_recaudado'];
    }
    
    $datos_grafico[] = [
        'dia' => $dia,
        'total' => $total_dia
    ];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte Mensual - Sistema de Parqueadero</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <div class="container mt-4">
        <h2>Reporte Mensual</h2>
        
        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-3">
                <label for="mes" class="form-label">Mes</label>
                <select class="form-select" id="mes" name="mes">
                    <?php for ($i = 1; $i <= 12; $i++): ?>
                        <option value="<?php echo sprintf("%02d", $i); ?>" <?php echo $mes == sprintf("%02d", $i) ? 'selected' : ''; ?>>
                            <?php echo date('F', mktime(0, 0, 0, $i, 1)); ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="anio" class="form-label">Año</label>
                <select class="form-select" id="anio" name="anio">
                    <?php for ($i = date('Y') - 2; $i <= date('Y'); $i++): ?>
                        <option value="<?php echo $i; ?>" <?php echo $anio == $i ? 'selected' : ''; ?>><?php echo $i; ?></option>
                    <?php endfor; ?>
                </select>
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
                Resumen del Mes
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
                        <p><strong>Vehículos Atendidos:</strong> <?php echo count($registros); ?></p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>Promedio Diario:</strong> $<?php echo number_format($total_recaudado / date('t', strtotime($fecha_inicio)), 2); ?></p>
                    </div>
                </div>
                
                <canvas id="graficoMensual" height="100"></canvas>
                
                <h5 class="mt-4">Por Método de Pago</h5>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Método</th>
                                <th>Cantidad</th>
                                <th>Total</th>
                                <th>Porcentaje</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($totales as $total): ?>
                                <tr>
                                    <td><?php echo ucfirst($total['metodo_pago']); ?></td>
                                    <td><?php echo $total['cantidad']; ?></td>
                                    <td>$<?php echo number_format($total['total_recaudado'], 2); ?></td>
                                    <td><?php echo number_format(($total['total_recaudado'] / $total_recaudado) * 100, 2); ?>%</td>
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
                                <th>Fecha</th>
                                <th>Placa</th>
                                <th>Tipo</th>
                                <th>Entrada</th>
                                <th>Salida</th>
                                <th>Valor</th>
                                <th>Método Pago</th>
                                <th>Atendió</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($registros as $registro): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y', strtotime($registro['hora_entrada'])); ?></td>
                                    <td><?php echo $registro['placa']; ?></td>
                                    <td><?php echo ucfirst($registro['tipo_vehiculo']); ?></td>
                                    <td><?php echo date('H:i', strtotime($registro['hora_entrada'])); ?></td>
                                    <td><?php echo $registro['hora_salida'] ? date('H:i', strtotime($registro['hora_salida'])) : '--'; ?></td>
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

    <script>
        // Gráfico de ingresos diarios
        const ctx = document.getElementById('graficoMensual').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($datos_grafico, 'dia')); ?>,
                datasets: [{
                    label: 'Ingresos por Día',
                    data: <?php echo json_encode(array_column($datos_grafico, 'total')); ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Valor ($)'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Día del Mes'
                        }
                    }
                }
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>