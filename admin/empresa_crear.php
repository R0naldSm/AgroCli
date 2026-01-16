<?php
// admin/empresa_crear.php - CREAR EMPRESA Y SU PRIMER USUARIO
require_once '../config/config.php';
require_once '../config/database.php';

// Verificar que sea admin_general
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'admin_general') {
    header('Location: login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();
$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $db->beginTransaction();
        
        // 1. Crear empresa usando procedimiento almacenado
        $query = "CALL sp_crear_empresa(:nombre, :ruc, :direccion, :telefono, :email)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':nombre', $_POST['nombre_empresa']);
        $stmt->bindParam(':ruc', $_POST['ruc']);
        $stmt->bindParam(':direccion', $_POST['direccion']);
        $stmt->bindParam(':telefono', $_POST['telefono']);
        $stmt->bindParam(':email', $_POST['email_empresa']);
        $stmt->execute();
        
        $resultado = $stmt->fetch();
        
        if ($resultado['success']) {
            $id_empresa = $resultado['id'];
            
            // 2. Crear usuario ingeniero para la empresa
            $username = $_POST['username'];
            $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
            
            // Limpiar resultset anterior
            $stmt->closeCursor();
            
            $query = "CALL sp_crear_usuario(:id_empresa, :username, :password, :nombre_completo, :email, :rol)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id_empresa', $id_empresa);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $password_hash);
            $stmt->bindParam(':nombre_completo', $_POST['nombre_usuario']);
            $stmt->bindParam(':email', $_POST['email_usuario']);
            $stmt->bindValue(':rol', 'ingeniero');
            $stmt->execute();
            
            $resultado_usuario = $stmt->fetch();
            
            if ($resultado_usuario['success']) {
                $db->commit();
                $_SESSION['success'] = "Empresa y usuario creados exitosamente";
                header('Location: dashboard.php');
                exit();
            } else {
                $db->rollBack();
                $error = $resultado_usuario['mensaje'];
            }
        } else {
            $db->rollBack();
            $error = $resultado['mensaje'];
        }
        
    } catch (PDOException $e) {
        $db->rollBack();
        $error = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Empresa - AgriManage</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../public/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-dark bg-danger sticky-top shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <i class="bi bi-shield-lock-fill"></i>
                <strong>AgriManage - ADMINISTRADOR</strong>
            </a>
            <a href="dashboard.php" class="btn btn-sm btn-light">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </nav>
    
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h4 class="mb-0">
                            <i class="bi bi-building"></i> Crear Nueva Empresa
                        </h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="" id="form-empresa">
                            <!-- DATOS DE LA EMPRESA -->
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">
                                        <i class="bi bi-1-circle-fill text-danger"></i> 
                                        Datos de la Empresa
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-8">
                                            <label for="nombre_empresa" class="form-label">
                                                Nombre de la Empresa <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="nombre_empresa" 
                                                   name="nombre_empresa" 
                                                   required>
                                            <div class="form-text">Ejemplo: Agroquímico San José</div>
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <label for="ruc" class="form-label">
                                                RUC <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="ruc" 
                                                   name="ruc" 
                                                   pattern="[0-9]{13}"
                                                   maxlength="13"
                                                   required>
                                            <div class="form-text">13 dígitos</div>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="telefono" class="form-label">Teléfono</label>
                                            <input type="tel" 
                                                   class="form-control" 
                                                   id="telefono" 
                                                   name="telefono">
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label for="email_empresa" class="form-label">Email</label>
                                            <input type="email" 
                                                   class="form-control" 
                                                   id="email_empresa" 
                                                   name="email_empresa">
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="direccion" class="form-label">Dirección</label>
                                        <textarea class="form-control" 
                                                  id="direccion" 
                                                  name="direccion" 
                                                  rows="2"></textarea>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- DATOS DEL PRIMER USUARIO (INGENIERO) -->
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">
                                        <i class="bi bi-2-circle-fill text-danger"></i> 
                                        Primer Usuario (Ingeniero Agrónomo)
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-info">
                                        <i class="bi bi-info-circle"></i>
                                        Se creará automáticamente un usuario ingeniero para esta empresa. 
                                        Puede agregar más usuarios después desde el panel de administración.
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-12">
                                            <label for="nombre_usuario" class="form-label">
                                                Nombre Completo del Ingeniero <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="nombre_usuario" 
                                                   name="nombre_usuario" 
                                                   required>
                                            <div class="form-text">Ejemplo: Ing. Carlos Mendoza</div>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="username" class="form-label">
                                                Usuario de Acceso <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="username" 
                                                   name="username" 
                                                   required>
                                            <div class="form-text">Usuario para iniciar sesión</div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label for="email_usuario" class="form-label">Email</label>
                                            <input type="email" 
                                                   class="form-control" 
                                                   id="email_usuario" 
                                                   name="email_usuario">
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="password" class="form-label">
                                                Contraseña <span class="text-danger">*</span>
                                            </label>
                                            <input type="password" 
                                                   class="form-control" 
                                                   id="password" 
                                                   name="password" 
                                                   minlength="8"
                                                   required>
                                            <div class="form-text">Mínimo 8 caracteres</div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label for="password_confirm" class="form-label">
                                                Confirmar Contraseña <span class="text-danger">*</span>
                                            </label>
                                            <input type="password" 
                                                   class="form-control" 
                                                   id="password_confirm" 
                                                   name="password_confirm" 
                                                   minlength="8"
                                                   required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- BOTONES -->
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="dashboard.php" class="btn btn-secondary">
                                    <i class="bi bi-x-lg"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-danger">
                                    <i class="bi bi-save"></i> Crear Empresa y Usuario
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
        // Solo permitir números en RUC
        document.getElementById('ruc').addEventListener('keypress', function(e) {
            if (!/[0-9]/.test(e.key)) {
                e.preventDefault();
            }
        });
        
        // Validar que las contraseñas coincidan
        document.getElementById('form-empresa').addEventListener('submit', function(e) {
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