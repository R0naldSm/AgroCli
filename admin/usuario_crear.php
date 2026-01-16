<?php
// admin/usuario_crear.php
require_once '../config/config.php';
require_once '../config/database.php';

// Verificar que sea admin_general
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'admin_general') {
    header('Location: login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Obtener lista de empresas
try {
    $query = "SELECT id, nombre FROM empresas WHERE deleted_at IS NULL AND activo = 1 ORDER BY nombre";
    $stmt = $db->query($query);
    $empresas = $stmt->fetchAll();
} catch (PDOException $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
    header('Location: dashboard.php');
    exit();
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Verificar que el username no exista
        $query = "SELECT COUNT(*) as total FROM usuarios WHERE username = :username AND deleted_at IS NULL";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':username', $_POST['username']);
        $stmt->execute();
        
        if ($stmt->fetch()['total'] > 0) {
            $_SESSION['error'] = "El nombre de usuario ya existe";
        } else {
            // Crear usuario usando procedimiento almacenado
            $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
            
            $query = "CALL sp_crear_usuario(
                :id_empresa,
                :username,
                :password,
                :nombre_completo,
                :email,
                :rol
            )";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id_empresa', $_POST['id_empresa']);
            $stmt->bindParam(':username', $_POST['username']);
            $stmt->bindParam(':password', $password_hash);
            $stmt->bindParam(':nombre_completo', $_POST['nombre_completo']);
            $stmt->bindParam(':email', $_POST['email']);
            $stmt->bindParam(':rol', $_POST['rol']);
            $stmt->execute();
            
            $resultado = $stmt->fetch();
            
            if ($resultado['success']) {
                $_SESSION['success'] = "Usuario creado exitosamente";
                header('Location: usuarios.php');
                exit();
            } else {
                $_SESSION['error'] = $resultado['mensaje'];
            }
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Usuario - AgriManage</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../public/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-dark bg-danger sticky-top shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <i class="bi bi-shield-lock-fill"></i>
                <strong>AgriManage - ADMIN</strong>
            </a>
            <a href="usuarios.php" class="btn btn-sm btn-light">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </nav>
    
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h4 class="mb-0">
                            <i class="bi bi-person-plus"></i> Crear Nuevo Usuario
                        </h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger">
                                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="" id="form-usuario">
                            <div class="mb-3">
                                <label class="form-label">Empresa <span class="text-danger">*</span></label>
                                <select class="form-select" name="id_empresa" required>
                                    <option value="">Seleccione una empresa</option>
                                    <?php foreach ($empresas as $empresa): ?>
                                        <option value="<?php echo $empresa['id']; ?>">
                                            <?php echo htmlspecialchars($empresa['nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Nombre Completo <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="nombre_completo" 
                                       placeholder="Ej: Ing. Carlos Mendoza" required>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Usuario <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="username" 
                                           placeholder="Usuario para login" required>
                                    <small class="text-muted">Sin espacios ni caracteres especiales</small>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email">
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Contraseña <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" name="password" 
                                           id="password" minlength="8" required>
                                    <small class="text-muted">Mínimo 8 caracteres</small>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Confirmar Contraseña <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="password_confirm" 
                                           minlength="8" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Rol <span class="text-danger">*</span></label>
                                <select class="form-select" name="rol" required>
                                    <option value="">Seleccione un rol</option>
                                    <option value="admin_empresa">Administrador de Empresa</option>
                                    <option value="ingeniero">Ingeniero Agrónomo</option>
                                    <option value="asistente">Asistente</option>
                                </select>
                            </div>
                            
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i>
                                <strong>Roles:</strong>
                                <ul class="mb-0 mt-2">
                                    <li><strong>Admin Empresa:</strong> Gestiona usuarios de su empresa</li>
                                    <li><strong>Ingeniero:</strong> Registra clientes, lotes, aplicaciones</li>
                                    <li><strong>Asistente:</strong> Acceso limitado de lectura/escritura</li>
                                </ul>
                            </div>
                            
                            <div class="d-flex justify-content-end gap-2">
                                <a href="usuarios.php" class="btn btn-secondary">
                                    <i class="bi bi-x-lg"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-danger">
                                    <i class="bi bi-save"></i> Crear Usuario
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validar que las contraseñas coincidan
        document.getElementById('form-usuario').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirm = document.getElementById('password_confirm').value;
            
            if (password !== confirm) {
                e.preventDefault();
                alert('Las contraseñas no coinciden');
                return false;
            }
            
            if (password.length < 8) {
                e.preventDefault();
                alert('La contraseña debe tener al menos 8 caracteres');
                return false;
            }
        });
    </script>
</body>
</html>