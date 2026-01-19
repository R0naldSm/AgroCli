<?php
// admin/reportes/auditoria.php
require_once '../../config/config.php';
require_once '../../config/database.php';

// Verificar que sea admin_general
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'admin_general') {
    header('Location: ../login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Filtros
$filtro_usuario = isset($_GET['usuario']) ? intval($_GET['usuario']) : 0;
$filtro_accion = isset($_GET['accion']) ? $_GET['accion'] : '';
$filtro_tabla = isset($_GET['tabla']) ? $_GET['tabla'] : '';
$filtro_fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : '';
$filtro_fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : '';

// Obtener logs de auditoría
try {
    $query = "SELECT a.*, 
              u.nombre_completo as usuario_nombre,
              u.username,
              e.nombre as empresa_nombre
              FROM auditoria a
              INNER JOIN usuarios u ON a.id_usuario = u.id
              LEFT JOIN empresas e ON a.id_empresa = e.id
              WHERE 1=1";
    
    $params = [];
    
    if ($filtro_usuario > 0) {
        $query .= " AND a.id_usuario = :id_usuario";
        $params[':id_usuario'] = $filtro_usuario;
    }
    
    if (!empty($filtro_accion)) {
        $query .= " AND a.accion = :accion";
        $params[':accion'] = $filtro_accion;
    }
    
    if (!empty($filtro_tabla)) {
        $query .= " AND a.tabla_afectada = :tabla";
        $params[':tabla'] = $filtro_tabla;
    }
    
    if (!empty($filtro_fecha_inicio)) {
        $query .= " AND DATE(a.created_at) >= :fecha_inicio";
        $params[':fecha_inicio'] = $filtro_fecha_inicio;
    }
    
    if (!empty($filtro_fecha_fin)) {
        $query .= " AND DATE(a.created_at) <= :fecha_fin";
        $params[':fecha_fin'] = $filtro_fecha_fin;
    }
    
    $query .= " ORDER BY a.created_at DESC LIMIT 500";
    
    $stmt = $db->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $logs = $stmt->fetchAll();
    
    // Obtener lista de usuarios para filtro
    $query_usuarios = "SELECT id, nombre_completo FROM usuarios WHERE deleted_at IS NULL ORDER BY nombre_completo";
    $stmt_usuarios = $db->query($query_usuarios);
    $usuarios = $stmt_usuarios->fetchAll();
    
} catch (PDOException $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
    $logs = [];
    $usuarios = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auditoría - AgriManage</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../../public/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-dark bg-danger sticky-top shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand" href="../dashboard.php">
                <i class="bi bi-shield-lock-fill"></i>
                <strong>AgriManage - ADMIN</strong>
            </a>
            <a href="../dashboard.php" class="btn btn-sm btn-light">
                <i class="bi bi-arrow-left"></i> Dashboard
            </a>
        </div>
    </nav>
    
    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2><i class="bi bi-shield-check"></i> Auditoría del Sistema</h2>
            <button class="btn btn-success" onclick="window.print()">
                <i class="bi bi-printer"></i> Imprimir
            </button>
        </div>
        
        <!-- Filtros -->
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-funnel"></i> Filtros</h5>
            </div>
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Usuario</label>
                        <select class="form-select" name="usuario">
                            <option value="0">Todos los usuarios</option>
                            <?php foreach ($usuarios as $usuario): ?>
                                <option value="<?php echo $usuario['id']; ?>" 
                                        <?php echo ($filtro_usuario == $usuario['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($usuario['nombre_completo']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">Acción</label>
                        <select class="form-select" name="accion">
                            <option value="">Todas</option>
                            <option value="INSERT" <?php echo ($filtro_accion == 'INSERT') ? 'selected' : ''; ?>>INSERT</option>
                            <option value="UPDATE" <?php echo ($filtro_accion == 'UPDATE') ? 'selected' : ''; ?>>UPDATE</option>
                            <option value="DELETE" <?php echo ($filtro_accion == 'DELETE') ? 'selected' : ''; ?>>DELETE</option>
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">Tabla</label>
                        <select class="form-select" name="tabla">
                            <option value="">Todas</option>
                            <option value="clientes" <?php echo ($filtro_tabla == 'clientes') ? 'selected' : ''; ?>>Clientes</option>
                            <option value="lotes" <?php echo ($filtro_tabla == 'lotes') ? 'selected' : ''; ?>>Lotes</option>
                            <option value="etapas_cultivo" <?php echo ($filtro_tabla == 'etapas_cultivo') ? 'selected' : ''; ?>>Etapas</option>
                            <option value="aplicaciones" <?php echo ($filtro_tabla == 'aplicaciones') ? 'selected' : ''; ?>>Aplicaciones</option>
                            <option value="productos" <?php echo ($filtro_tabla == 'productos') ? 'selected' : ''; ?>>Productos</option>
                            <option value="usuarios" <?php echo ($filtro_tabla == 'usuarios') ? 'selected' : ''; ?>>Usuarios</option>
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">Fecha Inicio</label>
                        <input type="date" class="form-control" name="fecha_inicio" 
                               value="<?php echo $filtro_fecha_inicio; ?>">
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">Fecha Fin</label>
                        <input type="date" class="form-control" name="fecha_fin" 
                               value="<?php echo $filtro_fecha_fin; ?>">
                    </div>
                    
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Estadísticas rápidas -->
        <div class="row mb-3">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h6>Total Registros</h6>
                        <h3><?php echo count($logs); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h6>Inserciones</h6>
                        <h3><?php echo count(array_filter($logs, fn($l) => $l['accion'] == 'INSERT')); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h6>Actualizaciones</h6>
                        <h3><?php echo count(array_filter($logs, fn($l) => $l['accion'] == 'UPDATE')); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <h6>Eliminaciones</h6>
                        <h3><?php echo count(array_filter($logs, fn($l) => $l['accion'] == 'DELETE')); ?></h3>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tabla de logs -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Fecha/Hora</th>
                                <th>Usuario</th>
                                <th>Empresa</th>
                                <th>Acción</th>
                                <th>Tabla</th>
                                <th>Registro ID</th>
                                <th>IP</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($logs)): ?>
                                <tr>
                                    <td colspan="8" class="text-center">No hay registros de auditoría</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($logs as $log): ?>
                                    <tr>
                                        <td><?php echo $log['id']; ?></td>
                                        <td><?php echo date('d/m/Y H:i:s', strtotime($log['created_at'])); ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($log['usuario_nombre']); ?></strong>
                                            <br><small class="text-muted"><?php echo $log['username']; ?></small>
                                        </td>
                                        <td>
                                            <?php if ($log['empresa_nombre']): ?>
                                                <span class="badge bg-info">
                                                    <?php echo htmlspecialchars($log['empresa_nombre']); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $badges = [
                                                'INSERT' => 'bg-success',
                                                'UPDATE' => 'bg-warning',
                                                'DELETE' => 'bg-danger'
                                            ];
                                            $badge = $badges[$log['accion']] ?? 'bg-secondary';
                                            ?>
                                            <span class="badge <?php echo $badge; ?>">
                                                <?php echo $log['accion']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo $log['tabla_afectada']; ?></td>
                                        <td><?php echo $log['id_registro'] ?? '-'; ?></td>
                                        <td><?php echo $log['ip_address'] ?? '-'; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>