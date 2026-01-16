<?php
// admin/empresa_editar.php
require_once '../config/config.php';
require_once '../config/database.php';

// Verificar que sea admin_general
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'admin_general') {
    header('Location: login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$id_empresa = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Obtener datos de la empresa
try {
    $query = "SELECT * FROM empresas WHERE id = :id AND deleted_at IS NULL";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id_empresa);
    $stmt->execute();
    $empresa = $stmt->fetch();
    
    if (!$empresa) {
        $_SESSION['error'] = "Empresa no encontrada";
        header('Location: dashboard.php');
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
    header('Location: dashboard.php');
    exit();
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $query = "UPDATE empresas SET
                  nombre = :nombre,
                  ruc = :ruc,
                  direccion = :direccion,
                  telefono = :telefono,
                  email = :email,
                  activo = :activo,
                  updated_at = NOW()
                  WHERE id = :id";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id_empresa);
        $stmt->bindParam(':nombre', $_POST['nombre']);
        $stmt->bindParam(':ruc', $_POST['ruc']);
        $stmt->bindParam(':direccion', $_POST['direccion']);
        $stmt->bindParam(':telefono', $_POST['telefono']);
        $stmt->bindParam(':email', $_POST['email']);
        $stmt->bindParam(':activo', $_POST['activo']);
        $stmt->execute();
        
        $_SESSION['success'] = "Empresa actualizada exitosamente";
        header('Location: dashboard.php');
        exit();
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
    <title>Editar Empresa - AgriManage</title>
    
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
            <a href="dashboard.php" class="btn btn-sm btn-light">
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
                            <i class="bi bi-building"></i> Editar Empresa
                        </h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger">
                                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="row mb-3">
                                <div class="col-md-8">
                                    <label class="form-label">Nombre de la Empresa <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="nombre" 
                                           value="<?php echo htmlspecialchars($empresa['nombre']); ?>" required>
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label">RUC <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="ruc" 
                                           value="<?php echo htmlspecialchars($empresa['ruc']); ?>"
                                           pattern="[0-9]{13}" maxlength="13" required>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Teléfono</label>
                                    <input type="tel" class="form-control" name="telefono" 
                                           value="<?php echo htmlspecialchars($empresa['telefono']); ?>">
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email" 
                                           value="<?php echo htmlspecialchars($empresa['email']); ?>">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Dirección</label>
                                <textarea class="form-control" name="direccion" rows="2"><?php echo htmlspecialchars($empresa['direccion']); ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Estado</label>
                                <select class="form-select" name="activo">
                                    <option value="1" <?php echo $empresa['activo'] ? 'selected' : ''; ?>>Activa</option>
                                    <option value="0" <?php echo !$empresa['activo'] ? 'selected' : ''; ?>>Inactiva</option>
                                </select>
                            </div>
                            
                            <div class="d-flex justify-content-end gap-2">
                                <a href="dashboard.php" class="btn btn-secondary">
                                    <i class="bi bi-x-lg"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-danger">
                                    <i class="bi bi-save"></i> Actualizar Empresa
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>