<?php
// admin/usuarios.php
require_once '../config/config.php';
require_once '../config/database.php';

// Verificar que sea admin_general
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'admin_general') {
    header('Location: login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Filtro por empresa
$filtro_empresa = isset($_GET['empresa']) ? intval($_GET['empresa']) : 0;

// Obtener lista de empresas para el filtro
try {
    $query = "SELECT id, nombre FROM empresas WHERE deleted_at IS NULL ORDER BY nombre";
    $stmt = $db->query($query);
    $empresas = $stmt->fetchAll();
} catch (PDOException $e) {
    $empresas = [];
}

// Obtener usuarios
try {
    $query = "SELECT u.*, e.nombre as empresa_nombre
              FROM usuarios u
              LEFT JOIN empresas e ON u.id_empresa = e.id
              WHERE u.deleted_at IS NULL
              AND u.rol != 'admin_general'";
    
    if ($filtro_empresa > 0) {
        $query .= " AND u.id_empresa = :id_empresa";
    }
    
    $query .= " ORDER BY u.created_at DESC";
    
    $stmt = $db->prepare($query);
    
    if ($filtro_empresa > 0) {
        $stmt->bindParam(':id_empresa', $filtro_empresa);
    }
    
    $stmt->execute();
    $usuarios = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
    $usuarios = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios - AgriManage</title>
    
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
            <div class="d-flex gap-2">
                <a href="dashboard.php" class="btn btn-sm btn-light">
                    <i class="bi bi-house"></i> Dashboard
                </a>
            </div>
        </div>
    </nav>
    
    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2><i class="bi bi-people-fill"></i> Usuarios del Sistema</h2>
            <a href="usuario_crear.php" class="btn btn-danger">
                <i class="bi bi-plus-lg"></i> Nuevo Usuario
            </a>
        </div>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- Filtros -->
        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Filtrar por Empresa</label>
                        <select class="form-select" name="empresa" onchange="this.form.submit()">
                            <option value="0">Todas las empresas</option>
                            <?php foreach ($empresas as $empresa): ?>
                                <option value="<?php echo $empresa['id']; ?>" 
                                        <?php echo ($filtro_empresa == $empresa['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($empresa['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-8 d-flex align-items-end">
                        <span class="badge bg-secondary">
                            Total: <?php echo count($usuarios); ?> usuarios
                        </span>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Tabla de usuarios -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Usuario</th>
                                <th>Nombre Completo</th>
                                <th>Email</th>
                                <th>Empresa</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th>Último Acceso</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($usuarios)): ?>
                                <tr>
                                    <td colspan="9" class="text-center">No hay usuarios registrados</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($usuarios as $usuario): ?>
                                    <tr>
                                        <td><?php echo $usuario['id']; ?></td>
                                        <td><strong><?php echo htmlspecialchars($usuario['username']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($usuario['nombre_completo']); ?></td>
                                        <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?php echo htmlspecialchars($usuario['empresa_nombre']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                            $roles = [
                                                'admin_empresa' => '<span class="badge bg-warning">Admin Empresa</span>',
                                                'ingeniero' => '<span class="badge bg-success">Ingeniero</span>',
                                                'asistente' => '<span class="badge bg-secondary">Asistente</span>'
                                            ];
                                            echo $roles[$usuario['rol']] ?? $usuario['rol'];
                                            ?>
                                        </td>
                                        <td>
                                            <?php if ($usuario['activo']): ?>
                                                <span class="badge bg-success">Activo</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Inactivo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php 
                                            echo $usuario['ultimo_acceso'] 
                                                ? formatearFecha($usuario['ultimo_acceso']) 
                                                : 'Nunca';
                                            ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-danger btn-eliminar" 
                                                        data-id="<?php echo $usuario['id']; ?>"
                                                        data-nombre="<?php echo htmlspecialchars($usuario['nombre_completo']); ?>"
                                                        title="Eliminar">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal Eliminar -->
    <div class="modal fade" id="modalEliminar" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Confirmar Eliminación</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>¿Está seguro de eliminar al usuario <strong id="nombre-usuario"></strong>?</p>
                    <p class="text-muted">Esta acción es reversible (eliminación lógica).</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <a href="#" id="btn-confirmar" class="btn btn-danger">Eliminar</a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Modal eliminar
        const modal = new bootstrap.Modal(document.getElementById('modalEliminar'));
        document.querySelectorAll('.btn-eliminar').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('nombre-usuario').textContent = this.dataset.nombre;
                document.getElementById('btn-confirmar').href = 
                    'usuario_eliminar.php?id=' + this.dataset.id;
                modal.show();
            });
        });
    </script>
</body>
</html>