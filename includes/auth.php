<?php
require_once 'database.php';

session_start();

class Auth {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function login($username, $password) {
        $conn = $this->db->getConnection();
        $hashed_password = hash('sha256', $password);
        
        $stmt = $conn->prepare("SELECT * FROM usuarios WHERE username = ? AND password = ? AND activo = 1");
        $stmt->bind_param("ss", $username, $hashed_password);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nombre'] = $user['nombre'];
            $_SESSION['rol'] = $user['rol'];
            return true;
        }
        
        return false;
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    public function isAdmin() {
        return $this->isLoggedIn() && $_SESSION['rol'] == 'admin';
    }
    
    public function logout() {
        session_destroy();
        header("Location: login.php");
        exit();
    }
    
    public function checkAuth() {
        if (!$this->isLoggedIn()) {
            header("Location: login.php");
            exit();
        }
    }
    
    public function checkAdmin() {
        $this->checkAuth();
        if (!$this->isAdmin()) {
            header("Location: dashboard.php");
            exit();
        }
    }
}
?>