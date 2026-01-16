<?php
// ingeniero/login.php - PORTAL DEL INGENIERO
session_start();

// Si ya está autenticado como ingeniero, redirigir
if (isset($_SESSION['usuario_id']) && in_array($_SESSION['rol'], ['ingeniero', 'admin_empresa', 'asistente'])) {
    header('Location: dashboard.php');
    exit();
}

require_once '../config/database.php';

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
            
            // Usar procedimiento almacenado para autenticar
            $query = "CALL sp_autenticar_usuario(:username, :password)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $password);
            $stmt->execute();
            
            $usuario = $stmt->fetch();
            
            // Verificar que no sea admin_general
            if ($usuario && $usuario['id'] && $usuario['rol'] != 'admin_general') {
                if (password_verify($password, $usuario['password'])) {
                    // Login exitoso
                    $_SESSION['usuario_id'] = $usuario['id'];
                    $_SESSION['username'] = $usuario['username'];
                    $_SESSION['nombre_completo'] = $usuario['nombre_completo'];
                    $_SESSION['email'] = $usuario['email'];
                    $_SESSION['rol'] = $usuario['rol'];
                    $_SESSION['id_empresa'] = $usuario['id_empresa'];
                    $_SESSION['empresa_nombre'] = $usuario['empresa_nombre'];
                    $_SESSION['portal'] = 'ingeniero'; // Identificador del portal
                    
                    header('Location: dashboard.php');
                    exit();
                } else {
                    $error = 'Usuario o contraseña incorrectos';
                }
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
    <title>AgriManage - Portal Ingeniero</title>
    
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
        }
        
        .login-header {
            background: linear-gradient(135deg, #198754 0%, #0d6938 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .login-header i {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        
        .login-header h3 {
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .badge-ingeniero {
            background-color: rgba(255,255,255,0.2);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
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
            transition: transform 0.2s;
        }
        
        .btn-login:hover {
            background: linear-gradient(135deg, #0d6938 0%, #198754 100%);
            transform: translateY(-2px);
        }
        
        .alert {
            border-radius: 8px;
            border: none;
        }
        
        .portal-switch {
            text-align: center;
            margin-top: 1rem;
            padding: 1rem;
            background-color: #f8f9fa;
            border-radius: 8px;
        }
        
        .portal-switch a {
            color: #dc3545;
            font-weight: 600;
            text-decoration: none;
        }
        
        .portal-switch a:hover {
            text-decoration: underline;
        }
        
        .info-box {
            background-color: #d1e7dd;
            border-left: 4px solid #198754;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 6px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="card login-card">
            <div class="login-header">
                <i class="bi bi-clipboard-data"></i>
                <h3>AgriManage</h3>
                <div class="badge-ingeniero">
                    <i class="bi bi-person-badge"></i> PORTAL INGENIERO
                </div>
            </div>
            
            <div class="login-body">
                <div class="info-box">
                    <small>
                        <i class="bi bi-info-circle-fill"></i>
                        <strong>Ingenieros Agrónomos:</strong> Este portal es para registrar productos aplicados a sus clientes.
                    </small>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
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
                        <div class="form-text">Usuario asignado por el administrador</div>
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
                        ¿No tiene usuario? Contacte al administrador de su empresa
                    </small>
                </div>
                
                <div class="portal-switch">
                    <i class="bi bi-arrow-left-right"></i>
                    ¿Eres administrador del sistema? 
                    <a href="../admin/login.php">
                        Ir al Portal Administrativo
                    </a>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <small class="text-white">
                AgriManage v1.0.0 &copy; 2024 - Portal Ingeniero
            </small>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>