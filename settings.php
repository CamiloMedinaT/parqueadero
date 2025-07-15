<?php
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$auth = new Auth();
$auth->checkAdmin();

$parking = new ParkingFunctions();
$error = '';
$success = '';

// Obtener tarifas actuales
$tarifas_result = $parking->query("SELECT * FROM tarifas");
$tarifas = [];
while ($row = $tarifas_result->fetch_assoc()) {
    $tarifas[$row['tipo_vehiculo']] = $row;
}

// Obtener espacios
$espacios_result = $parking->query("SELECT * FROM espacios");
$espacios_data = [];
while ($row = $espacios_result->fetch_assoc()) {
    $espacios_data[$row['tipo_vehiculo']] = $row;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['actualizar_tarifas'])) {
        $carro = $_POST['carro'];
        $moto = $_POST['moto'];
        $cicla = $_POST['cicla'];
        
        if (is_numeric($carro) && is_numeric($moto) && is_numeric($cicla)) {
            $parking->actualizarTarifas($carro, $moto, $cicla);
            $success = "Tarifas actualizadas correctamente";
            
            // Actualizar tarifas en memoria
            $tarifas['carro']['valor_hora'] = $carro;
            $tarifas['moto']['valor_hora'] = $moto;
            $tarifas['cicla']['valor_hora'] = $cicla;
        } else {
            $error = "Todos los valores deben ser numéricos";
        }
    } elseif (isset($_POST['actualizar_espacios'])) {
        $carro_total = $_POST['carro_total'];
        $moto_total = $_POST['moto_total'];
        $cicla_total = $_POST['cicla_total'];
        
        if (is_numeric($carro_total) && is_numeric($moto_total) && is_numeric($cicla_total)) {
            $parking->query("UPDATE espacios SET total = ? WHERE tipo_vehiculo = 'carro'", [$carro_total]);
            $parking->query("UPDATE espacios SET total = ? WHERE tipo_vehiculo = 'moto'", [$moto_total]);
            $parking->query("UPDATE espacios SET total = ? WHERE tipo_vehiculo = 'cicla'", [$cicla_total]);
            $success = "Espacios actualizados correctamente";
            
            // Actualizar espacios en memoria
            $espacios_data['carro']['total'] = $carro_total;
            $espacios_data['moto']['total'] = $moto_total;
            $espacios_data['cicla']['total'] = $cicla_total;
        } else {
            $error = "Todos los valores deben ser numéricos";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - Sistema de Parqueadero</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container mt-4">
        <h2>Configuración del Sistema</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Tarifas por Hora</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label for="carro" class="form-label">Carro</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="carro" name="carro" 
                                           value="<?php echo $tarifas['carro']['valor_hora']; ?>" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="moto" class="form-label">Moto</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="moto" name="moto" 
                                           value="<?php echo $tarifas['moto']['valor_hora']; ?>" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="cicla" class="form-label">Cicla</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="cicla" name="cicla" 
                                           value="<?php echo $tarifas['cicla']['valor_hora']; ?>" required>
                                </div>
                            </div>
                            <button type="submit" name="actualizar_tarifas" class="btn btn-primary">Actualizar Tarifas</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Espacios Disponibles</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label for="carro_total" class="form-label">Total Carros</label>
                                <input type="number" class="form-control" id="carro_total" name="carro_total" 
                                       value="<?php echo $espacios_data['carro']['total']; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="moto_total" class="form-label">Total Motos</label>
                                <input type="number" class="form-control" id="moto_total" name="moto_total" 
                                       value="<?php echo $espacios_data['moto']['total']; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="cicla_total" class="form-label">Total Ciclas</label>
                                <input type="number" class="form-control" id="cicla_total" name="cicla_total" 
                                       value="<?php echo $espacios_data['cicla']['total']; ?>" required>
                            </div>
                            <button type="submit" name="actualizar_espacios" class="btn btn-primary">Actualizar Espacios</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>