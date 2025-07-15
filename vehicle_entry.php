<?php
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$auth = new Auth();
$auth->checkAuth();

$parking = new ParkingFunctions();
$error = '';
$success = '';
$codigo_barras = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $placa = strtoupper(trim($_POST['placa']));
    $nombre_usuario = trim($_POST['nombre_usuario']);
    $tipo_vehiculo = $_POST['tipo_vehiculo'];
    $usuario_id = $_SESSION['user_id'];
    
    $codigo_barras = $parking->registrarEntrada($placa, $nombre_usuario, $tipo_vehiculo, $usuario_id);
    
    if ($codigo_barras) {
        $success = "Entrada registrada correctamente. Código de barras: $codigo_barras";
        
        // Aquí iría el código para imprimir el ticket
        // require_once 'classes/TicketGenerator.php';
        // $ticket = new TicketGenerator();
        // $ticket->printEntryTicket($codigo_barras, $placa, $nombre_usuario, $tipo_vehiculo, date('Y-m-d H:i:s'));
    } else {
        $error = "No hay espacios disponibles para este tipo de vehículo o ocurrió un error.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Entrada - Sistema de Parqueadero</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container mt-4">
        <h2>Registrar Entrada de Vehículo</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
            <div class="text-center mt-3">
                <img src="classes/BarcodeGenerator.php?text=<?php echo $codigo_barras; ?>" alt="Código de Barras" class="img-fluid">
                <p class="mt-2"><?php echo $codigo_barras; ?></p>
                <button class="btn btn-primary" onclick="window.print()">
                    <i class="bi bi-printer"></i> Imprimir Ticket
                </button>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="mt-4">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="placa" class="form-label">Placa del Vehículo</label>
                        <input type="text" class="form-control" id="placa" name="placa" required>
                    </div>
                    <div class="mb-3">
                        <label for="nombre_usuario" class="form-label">Nombre del Usuario</label>
                        <input type="text" class="form-control" id="nombre_usuario" name="nombre_usuario" required>
                    </div>
                    <div class="mb-3">
                        <label for="tipo_vehiculo" class="form-label">Tipo de Vehículo</label>
                        <select class="form-select" id="tipo_vehiculo" name="tipo_vehiculo" required>
                            <option value="carro">Carro</option>
                            <option value="moto">Moto</option>
                            <option value="cicla">Cicla</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Registrar Entrada</button>
                </div>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>