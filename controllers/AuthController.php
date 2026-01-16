<?php
// admin/controllers/AuthController.php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';

class AuthController {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    // Procesar login
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = trim($_POST['username']);
            $password = $_POST['password'];
            
            if (empty($username) || empty($password)) {
                $_SESSION['error'] = 'Por favor, ingrese usuario y contraseña';
                header('Location: ../login.php');
                exit();
            }
            
            try {
                // Solo permitir admin_general
                $query = "SELECT * FROM usuarios 
                          WHERE username = :username 
                          AND rol = 'admin_general'
                          AND activo = 1 
                          AND deleted_at IS NULL";
                
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':username', $username);
                $stmt->execute();
                
                $usuario = $stmt->fetch();
                
                if ($usuario && password_verify($password, $usuario['password'])) {
                    // Login exitoso
                    $_SESSION['usuario_id'] = $usuario['id'];
                    $_SESSION['username'] = $usuario['username'];
                    $_SESSION['nombre_completo'] = $usuario['nombre_completo'];
                    $_SESSION['email'] = $usuario['email'];
                    $_SESSION['rol'] = $usuario['rol'];
                    $_SESSION['portal'] = 'admin';
                    
                    // Actualizar último acceso
                    $update = "UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = :id";
                    $stmt_update = $this->db->prepare($update);
                    $stmt_update->bindParam(':id', $usuario['id']);
                    $stmt_update->execute();
                    
                    header('Location: ../dashboard.php');
                    exit();
                } else {
                    $_SESSION['error'] = 'Acceso denegado. Solo administradores.';
                    header('Location: ../login.php');
                    exit();
                }
            } catch (PDOException $e) {
                $_SESSION['error'] = 'Error en el sistema.';
                header('Location: ../login.php');
                exit();
            }
        }
    }
    
    // Cerrar sesión
    public function logout() {
        session_start();
        $_SESSION = array();
        
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 42000, '/');
        }
        
        session_destroy();
        header('Location: ../login.php');
        exit();
    }
    
    // Verificar autenticación
    public function verificarAuth() {
        if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'admin_general') {
            header('Location: ../login.php');
            exit();
        }
    }
}

// Enrutamiento
if (isset($_GET['action'])) {
    $controller = new AuthController();
    $action = $_GET['action'];
    
    switch ($action) {
        case 'login':
            $controller->login();
            break;
        case 'logout':
            $controller->logout();
            break;
        default:
            header('Location: ../login.php');
    }
}
?>