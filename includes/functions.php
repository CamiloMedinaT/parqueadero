<?php
require_once 'database.php';

class ParkingFunctions {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function registrarEntrada($placa, $nombre_usuario, $tipo_vehiculo, $usuario_id) {
        // Generar código de barras único
        $codigo_barras = $this->generarCodigoBarras();
        
        // Verificar disponibilidad
        if (!$this->verificarDisponibilidad($tipo_vehiculo)) {
            return false;
        }
        
        // Registrar entrada
        $sql = "INSERT INTO registros (codigo_barras, placa, nombre_usuario, tipo_vehiculo, hora_entrada, usuario_entrada) 
                VALUES (?, ?, ?, ?, NOW(), ?)";
        $result = $this->db->query($sql, [$codigo_barras, $placa, $nombre_usuario, $tipo_vehiculo, $usuario_id]);
        
        if ($result) {
            // Actualizar espacios ocupados
            $this->actualizarEspacios($tipo_vehiculo, 'entrada');
            return $codigo_barras;
        }
        
        return false;
    }
    
    public function registrarSalida($codigo_barras, $metodo_pago, $usuario_id) {
        // Obtener registro
        $sql = "SELECT * FROM registros WHERE codigo_barras = ? AND hora_salida IS NULL";
        $result = $this->db->query($sql, [$codigo_barras]);
        
        if ($result->num_rows == 0) {
            return false;
        }
        
        $registro = $result->fetch_assoc();
        $tipo_vehiculo = $registro['tipo_vehiculo'];
        
        // Calcular tiempo y valor
        $hora_entrada = new DateTime($registro['hora_entrada']);
        $hora_salida = new DateTime();
        $interval = $hora_entrada->diff($hora_salida);
        
        $horas = $interval->h + ($interval->days * 24);
        $minutos = $interval->i;
        
        // Obtener tarifa
        $tarifa = $this->obtenerTarifa($tipo_vehiculo);
        $valor_pagar = $this->calcularValor($horas, $minutos, $tarifa);
        
        // Actualizar registro
        $sql = "UPDATE registros SET 
                hora_salida = NOW(), 
                tiempo_estadia = CONCAT(?, ' horas y ', ?, ' minutos'), 
                valor_pagar = ?, 
                metodo_pago = ?, 
                pagado = TRUE, 
                usuario_salida = ?, 
                facturado_por = ?
                WHERE codigo_barras = ?";
        
        $result = $this->db->query($sql, [
            $horas, $minutos, $valor_pagar, $metodo_pago, $usuario_id, $usuario_id, $codigo_barras
        ]);
        
        if ($result) {
            // Actualizar espacios ocupados
            $this->actualizarEspacios($tipo_vehiculo, 'salida');
            return $registro;
        }
        
        return false;
    }
    
    private function generarCodigoBarras() {
        return 'PARK' . time() . rand(100, 999);
    }
    
    private function verificarDisponibilidad($tipo_vehiculo) {
        $sql = "SELECT disponibles FROM espacios WHERE tipo_vehiculo = ?";
        $result = $this->db->query($sql, [$tipo_vehiculo]);
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['disponibles'] > 0;
        }
        
        return false;
    }
    
    private function actualizarEspacios($tipo_vehiculo, $accion) {
        $operator = $accion == 'entrada' ? '+' : '-';
        $sql = "UPDATE espacios SET ocupados = ocupados $operator 1 WHERE tipo_vehiculo = ?";
        $this->db->query($sql, [$tipo_vehiculo]);
    }
    
    private function obtenerTarifa($tipo_vehiculo) {
        $sql = "SELECT * FROM tarifas WHERE tipo_vehiculo = ?";
        $result = $this->db->query($sql, [$tipo_vehiculo]);
        return $result->fetch_assoc();
    }
    
    private function calcularValor($horas, $minutos, $tarifa) {
        // Si son más de 8 horas, cobrar como día completo
        if ($horas >= 8) {
            return $tarifa['valor_dia'];
        }
        
        // Cobrar por horas completas (redondeando hacia arriba los minutos)
        $horas_completas = $minutos > 0 ? $horas + 1 : $horas;
        return $horas_completas * $tarifa['valor_hora'];
    }
    
    public function obtenerEspacios() {
        $sql = "SELECT * FROM espacios";
        $result = $this->db->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    public function obtenerRegistros($fecha_inicio = null, $fecha_fin = null) {
        $sql = "SELECT r.*, ue.nombre as nombre_entrada, us.nombre as nombre_salida 
                FROM registros r
                LEFT JOIN usuarios ue ON r.usuario_entrada = ue.id
                LEFT JOIN usuarios us ON r.usuario_salida = us.id
                WHERE 1=1";
        
        $params = [];
        
        if ($fecha_inicio && $fecha_fin) {
            $sql .= " AND DATE(r.hora_entrada) BETWEEN ? AND ?";
            $params[] = $fecha_inicio;
            $params[] = $fecha_fin;
        }
        
        $sql .= " ORDER BY r.hora_entrada DESC";
        
        $result = $this->db->query($sql, $params);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    public function obtenerTotales($fecha_inicio = null, $fecha_fin = null) {
        $sql = "SELECT 
                COUNT(*) as total_vehiculos,
                SUM(CASE WHEN hora_salida IS NULL THEN 1 ELSE 0 END) as vehiculos_parqueados,
                SUM(valor_pagar) as total_recaudado,
                metodo_pago,
                COUNT(*) as cantidad
                FROM registros
                WHERE pagado = TRUE";
        
        $params = [];
        
        if ($fecha_inicio && $fecha_fin) {
            $sql .= " AND DATE(hora_entrada) BETWEEN ? AND ?";
            $params[] = $fecha_inicio;
            $params[] = $fecha_fin;
        }
        
        $sql .= " GROUP BY metodo_pago";
        
        $result = $this->db->query($sql, $params);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    public function actualizarTarifas($carro, $moto, $cicla) {
        $this->db->query("UPDATE tarifas SET valor_hora = ? WHERE tipo_vehiculo = 'carro'", [$carro]);
        $this->db->query("UPDATE tarifas SET valor_hora = ? WHERE tipo_vehiculo = 'moto'", [$moto]);
        $this->db->query("UPDATE tarifas SET valor_hora = ? WHERE tipo_vehiculo = 'cicla'", [$cicla]);
        return true;
    }
}
?>