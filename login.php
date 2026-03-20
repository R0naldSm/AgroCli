<?php
// login.php (Raíz del proyecto)
session_start();

// Si ya está autenticado, redirigir según rol
if (isset($_SESSION['usuario_id'])) {
    if ($_SESSION['rol'] == 'admin_general') {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: ingeniero/dashboard.php');
    }
    exit();
}

require_once 'config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Por favor, ingrese usuario y contraseña';
    } else {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            // Buscar usuario (cualquier rol)
            $query = "SELECT u.*, e.nombre as empresa_nombre 
                      FROM usuarios u
                      LEFT JOIN empresas e ON u.id_empresa = e.id
                      WHERE u.username = :username 
                      AND u.activo = 1 
                      AND u.deleted_at IS NULL";
            
            $stmt = $db->prepare($query);
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
                $_SESSION['id_empresa'] = $usuario['id_empresa'];
                $_SESSION['empresa_nombre'] = $usuario['empresa_nombre'];
                
                // Actualizar último acceso
                $update = "UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = :id";
                $stmt_update = $db->prepare($update);
                $stmt_update->bindParam(':id', $usuario['id']);
                $stmt_update->execute();
                
                // Redirigir según rol
                if ($usuario['rol'] == 'admin_general') {
                    header('Location: admin/dashboard.php');
                } else {
                    // ingeniero, admin_empresa, asistente
                    header('Location: ingeniero/dashboard.php');
                }
                exit();
            } else {
                $error = 'Usuario o contraseña incorrectos';
            }
        } catch (PDOException $e) {
            $error = 'Error en el sistema. Intente nuevamente.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgriManage - Iniciar Sesión</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #198754 0%, #0d6938 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            max-width: 450px;
            width: 100%;
            padding: 15px;
        }
        
        .login-card {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            animation: fadeIn 0.5s ease-in;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .login-header {
            background: linear-gradient(135deg, #198754 0%, #0d6938 100%);
            color: white;
            padding: 2.5rem 2rem;
            text-align: center;
        }
        
        .login-header i {
            font-size: 4rem;
            margin-bottom: 1rem;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .login-header h3 {
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .login-body {
            padding: 2rem;
            background-color: white;
        }
        
        .form-control {
            border-radius: 8px;
            padding: 0.75rem 1rem;
            border: 2px solid #e9ecef;
            font-size: 1rem;
        }
        
        .form-control:focus {
            border-color: #198754;
            box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.25);
        }
        
        .input-group-text {
            background-color: #198754;
            color: white;
            border: none;
            border-radius: 8px 0 0 8px;
        }
        
        .btn-login {
            background: linear-gradient(135deg, #198754 0%, #0d6938 100%);
            border: none;
            border-radius: 8px;
            padding: 0.75rem;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s;
        }
        
        .btn-login:hover {
            background: linear-gradient(135deg, #0d6938 0%, #198754 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(25, 135, 84, 0.3);
        }
        
        .alert {
            border-radius: 8px;
            border: none;
        }
        
        .roles-info {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        
        .role-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            margin: 0.25rem;
        }
        
        .role-admin {
            background-color: #dc3545;
            color: white;
        }
        
        .role-ingeniero {
            background-color: #198754;
            color: white;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="card login-card">
            <div class="login-header">
                <i class="bi bi-trees"></i>
                <h3>AgriManage</h3>
                <p class="mb-0">Sistema de Gestión Agrícola</p>
            </div>
            
            <div class="login-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <div class="roles-info">
                    <small class="d-block mb-2 text-muted">
                        <i class="bi bi-info-circle"></i> <strong>Roles del sistema:</strong>
                    </small>
                    <span class="role-badge role-admin">
                        <i class="bi bi-shield-lock"></i> Administrador
                    </span>
                    <span class="role-badge role-ingeniero">
                        <i class="bi bi-person-badge"></i> Ingeniero Agrónomo
                    </span>
                </div>
                
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="username" class="form-label">Usuario</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-person-fill"></i>
                            </span>
                            <input type="text" 
                                   class="form-control" 
                                   id="username" 
                                   name="username" 
                                   placeholder="Ingrese su usuario"
                                   required 
                                   autofocus>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="form-label">Contraseña</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-lock-fill"></i>
                            </span>
                            <input type="password" 
                                   class="form-control" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Ingrese su contraseña"
                                   required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="bi bi-eye" id="eyeIcon"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-success btn-login">
                            <i class="bi bi-box-arrow-in-right"></i>
                            Ingresar al Sistema
                        </button>
                    </div>
                </form>
                
                <div class="text-center mt-3">
                    <small class="text-muted">
                        ¿Olvidó su contraseña? Contacte al administrador
                    </small>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <small class="text-white">
                AgriManage v1.0.0 &copy; <?php echo date('Y'); ?>
            </small>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mostrar/ocultar contraseña
        document.getElementById('togglePassword').addEventListener('click', function() {
            const password = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            
            if (password.type === 'password') {
                password.type = 'text';
                eyeIcon.classList.remove('bi-eye');
                eyeIcon.classList.add('bi-eye-slash');
            } else {
                password.type = 'password';
                eyeIcon.classList.remove('bi-eye-slash');
                eyeIcon.classList.add('bi-eye');
            }
        });
        
        // Animación al cargar
        document.querySelector('.login-card').style.opacity = '0';
        window.addEventListener('load', function() {
            document.querySelector('.login-card').style.opacity = '1';
        });
    </script>
</body>
</html>