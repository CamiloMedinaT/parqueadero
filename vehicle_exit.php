<?php
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$auth = new Auth();
$auth->checkAuth();

$parking = new ParkingFunctions();
$error = '';
$success = '';
$registro = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['buscar'])) {
        $codigo_barras = $_POST['codigo_barras'];
        
        $sql = "SELECT * FROM registros WHERE codigo_barras = ? AND hora_salida IS NULL";
        $result = $parking->query($sql, [$codigo_barras]);
        
        if ($result->num_rows > 0) {
            $registro = $result->fetch_assoc();
        } else {
            $error = "No se encontró un vehículo activo con ese código de barras";
        }
    } elseif (isset($_POST['pagar'])) {
        $codigo_barras = $_POST['codigo_barras'];
        $metodo_pago = $_POST['metodo_pago'];
        $usuario_id = $_SESSION['user_id'];
        
        $registro = $parking->registrarSalida($codigo_barras, $metodo_pago, $usuario_id);
        
        if ($registro) {
            $success = "Salida registrada correctamente. Valor a pagar: $" . number_format($registro['valor_pagar'], 2);
            
            // Aquí iría el código para imprimir el ticket de salida
            // require_once 'classes/TicketGenerator.php';
            // $ticket = new TicketGenerator();
            // $ticket->printExitTicket($registro);
        } else {
            $error = "Ocurrió un error al registrar la salida";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Salida - Sistema de Parqueadero</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container mt-4">
        <h2>Registrar Salida de Vehículo</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
            <div class="text-center mt-3">
                <button class="btn btn-primary" onclick="window.print()">
                    <i class="bi bi-printer"></i> Imprimir Ticket
                </button>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="mt-4">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="codigo_barras" class="form-label">Código de Barras</label>
                        <input type="text" class="form-control" id="codigo_barras" name="codigo_barras" required autofocus>
                    </div>
                    <button type="submit" name="buscar" class="btn btn-primary">Buscar Vehículo</button>
                </div>
            </div>
        </form>
        
        <?php if ($registro): ?>
            <div class="card mt-4">
                <div class="card-header">
                    Información del Vehículo
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Placa:</strong> <?php echo $registro['placa']; ?></p>
                            <p><strong>Nombre:</strong> <?php echo $registro['nombre_usuario']; ?></p>
                            <p><strong>Tipo:</strong> <?php echo ucfirst($registro['tipo_vehiculo']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Hora de Entrada:</strong> <?php echo date('d/m/Y H:i', strtotime($registro['hora_entrada'])); ?></p>
                            <p><strong>Tiempo Estadia:</strong> 
                                <?php 
                                $entrada = new DateTime($registro['hora_entrada']);
                                $salida = new DateTime();
                                $interval = $entrada->diff($salida);
                                echo $interval->format('%h horas %i minutos');
                                ?>
                            </p>
                        </div>
                    </div>
                    
                    <form method="POST" class="mt-3">
                        <input type="hidden" name="codigo_barras" value="<?php echo $registro['codigo_barras']; ?>">
                        <div class="mb-3">
                            <label for="metodo_pago" class="form-label">Método de Pago</label>
                            <select class="form-select" id="metodo_pago" name="metodo_pago" required>
                                <option value="efectivo">Efectivo</option>
                                <option value="nequi">Nequi</option>
                                <option value="daviplata">Daviplata</option>
                            </select>
                        </div>
                        <button type="submit" name="pagar" class="btn btn-success">Registrar Salida y Pagar</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Autofocus en el campo de código de barras
        document.getElementById('codigo_barras').focus();
    </script>
</body>
</html>