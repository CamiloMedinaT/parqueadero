<?php
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$auth = new Auth();
$auth->checkAdmin();

$db = new Database();
$error = '';
$success = '';

// Obtener lista de usuarios
$users = $db->query("SELECT * FROM usuarios ORDER BY fecha_creacion DESC")->fetch_all(MYSQLI_ASSOC);

// Procesar formulario de nuevo usuario
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['crear_usuario'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $nombre = trim($_POST['nombre']);
    $rol = $_POST['rol'];
    
    // Validar
    if (empty($username) || empty($password) || empty($nombre)) {
        $error = "Todos los campos son obligatorios";
    } else {
        // Verificar si el usuario ya existe
        $result = $db->query("SELECT id FROM usuarios WHERE username = ?", [$username]);
        if ($result->num_rows > 0) {
            $error = "El nombre de usuario ya existe";
        } else {
            // Crear usuario
            $hashed_password = hash('sha256', $password);
            $db->query("INSERT INTO usuarios (username, password, nombre, rol) VALUES (?, ?, ?, ?)", 
                      [$username, $hashed_password, $nombre, $rol]);
            $success = "Usuario creado correctamente";
            header("Refresh:2");
        }
    }
}

// Procesar cambio de estado
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cambiar_estado'])) {
    $user_id = $_POST['user_id'];
    $activo = $_POST['activo'] ? 0 : 1;
    
    $db->query("UPDATE usuarios SET activo = ? WHERE id = ?", [$activo, $user_id]);
    $success = "Estado del usuario actualizado";
    header("Refresh:2");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios - Sistema de Parqueadero</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container mt-4">
        <h2>Administración de Usuarios</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5>Nuevo Usuario</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="username" class="form-label">Usuario</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="password" class="form-label">Contraseña</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre Completo</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="rol" class="form-label">Rol</label>
                                <select class="form-select" id="rol" name="rol" required>
                                    <option value="admin">Administrador</option>
                                    <option value="empleado">Empleado</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <button type="submit" name="crear_usuario" class="btn btn-primary">Crear Usuario</button>
                </form>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5>Lista de Usuarios</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Usuario</th>
                                <th>Nombre</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th>Fecha Creación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo $user['username']; ?></td>
                                    <td><?php echo $user['nombre']; ?></td>
                                    <td><?php echo ucfirst($user['rol']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $user['activo'] ? 'success' : 'danger'; ?>">
                                            <?php echo $user['activo'] ? 'Activo' : 'Inactivo'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($user['fecha_creacion'])); ?></td>
                                    <td>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <input type="hidden" name="activo" value="<?php echo $user['activo']; ?>">
                                            <button type="submit" name="cambiar_estado" class="btn btn-sm btn-<?php echo $user['activo'] ? 'warning' : 'success'; ?>">
                                                <?php echo $user['activo'] ? 'Desactivar' : 'Activar'; ?>
                                            </button>
                                        </form>
                                    </td>
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