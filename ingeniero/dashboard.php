<?php
// ingeniero/dashboard.php
require_once '../config/config.php';
require_once '../config/database.php';

checkAuth();

$database = new Database();
$db = $database->getConnection();
$id_empresa = $_SESSION['id_empresa'];

// Obtener estadísticas usando el procedimiento almacenado
try {
    $stmt = $db->prepare("CALL sp_dashboard_ingeniero(:id_empresa)");
    $stmt->bindParam(':id_empresa', $id_empresa);
    $stmt->execute();
    
    // Primera consulta: totales
    $totales = $stmt->fetch();
    $total_clientes = $totales['total_clientes'] ?? 0;
    
    // Siguiente resultado
    $stmt->nextRowset();
    $totales_lotes = $stmt->fetch();
    $total_lotes = $totales_lotes['total_lotes'] ?? 0;
    
    // Siguiente resultado
    $stmt->nextRowset();
    $totales_etapas = $stmt->fetch();
    $etapas_proceso = $totales_etapas['etapas_proceso'] ?? 0;
    
    // Siguiente resultado
    $stmt->nextRowset();
    $totales_produccion = $stmt->fetch();
    $produccion_anual = $totales_produccion['produccion_anual'] ?? 0;
    
    // Siguiente resultado - Próximas cosechas
    $stmt->nextRowset();
    $proximas_cosechas = $stmt->fetchAll();
    
    $stmt->closeCursor();
    
    // Últimas cosechas finalizadas
    $query = "SELECT ec.*, l.nombre as lote_nombre, 
              CONCAT(c.nombre, ' ', c.apellido) as cliente_nombre
              FROM etapas_cultivo ec
              INNER JOIN lotes l ON ec.id_lote = l.id
              INNER JOIN clientes c ON l.id_cliente = c.id
              WHERE ec.id_empresa = :id_empresa 
              AND ec.estado = 'finalizada'
              AND ec.deleted_at IS NULL
              ORDER BY ec.fecha_fin_real DESC
              LIMIT 5";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id_empresa', $id_empresa);
    $stmt->execute();
    $ultimas_cosechas = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = "Error al cargar estadísticas: " . $e->getMessage();
    $total_clientes = 0;
    $total_lotes = 0;
    $etapas_proceso = 0;
    $produccion_anual = 0;
    $proximas_cosechas = [];
    $ultimas_cosechas = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - AgriManage</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../public/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-dark bg-success sticky-top shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <i class="bi bi-trees"></i>
                <strong>AgriManage</strong>
            </a>
            
            <div class="d-flex align-items-center text-white">
                <span class="me-3">
                    <i class="bi bi-building"></i>
                    <?php echo $_SESSION['empresa_nombre']; ?>
                </span>
                
                <div class="dropdown">
                    <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i>
                        <?php echo $_SESSION['nombre_completo']; ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <span class="dropdown-item-text">
                                <small class="text-muted">
                                    <?php 
                                    $roles = [
                                        'admin_empresa' => 'Admin. Empresa',
                                        'ingeniero' => 'Ingeniero Agrónomo',
                                        'asistente' => 'Asistente'
                                    ];
                                    echo $roles[$_SESSION['rol']] ?? $_SESSION['rol'];
                                    ?>
                                </small>
                            </span>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="logout.php">
                                <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>
    
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="dashboard.php">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <h6 class="sidebar-heading px-3 mt-4 mb-1 text-muted">
                                <span>Gestión</span>
                            </h6>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="../controllers/ClienteController.php">
                                <i class="bi bi-people-fill"></i> Clientes
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="../controllers/LoteController.php">
                                <i class="bi bi-geo-alt-fill"></i> Lotes
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="../controllers/EtapaController.php">
                                <i class="bi bi-calendar-check"></i> Etapas
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="../controllers/AplicacionController.php">
                                <i class="bi bi-droplet-fill"></i> Aplicaciones
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="../controllers/ProductoController.php">
                                <i class="bi bi-box-seam"></i> Productos
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="../controllers/NotificacionController.php">
                                <i class="bi bi-bell-fill"></i> Notificaciones
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
            
            <!-- Contenido Principal -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </h1>
                    <div class="btn-toolbar">
                        <span class="badge bg-secondary">
                            <?php echo date('d/m/Y H:i'); ?>
                        </span>
                    </div>
                </div>
                
                <!-- Bienvenida -->
                <div class="alert alert-success fade-in" role="alert">
                    <h5 class="alert-heading">
                        <i class="bi bi-emoji-smile"></i> 
                        ¡Bienvenido, <?php echo $_SESSION['nombre_completo']; ?>!
                    </h5>
                    <p class="mb-0">
                        Empresa: <strong><?php echo $_SESSION['empresa_nombre']; ?></strong>
                    </p>
                </div>
                
                <!-- Estadísticas -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stat-label">Clientes</div>
                                    <div class="stat-number"><?php echo $total_clientes; ?></div>
                                </div>
                                <div class="stat-icon">
                                    <i class="bi bi-people-fill"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="stat-card" style="background: linear-gradient(135deg, #0dcaf0 0%, #0891b2 100%);">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stat-label">Lotes</div>
                                    <div class="stat-number"><?php echo $total_lotes; ?></div>
                                </div>
                                <div class="stat-icon">
                                    <i class="bi bi-geo-alt-fill"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="stat-card" style="background: linear-gradient(135deg, #ffc107 0%, #ff8800 100%);">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stat-label">En Proceso</div>
                                    <div class="stat-number"><?php echo $etapas_proceso; ?></div>
                                </div>
                                <div class="stat-icon">
                                    <i class="bi bi-hourglass-split"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="stat-card" style="background: linear-gradient(135deg, #20c997 0%, #0d9488 100%);">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stat-label">Producción <?php echo date('Y'); ?></div>
                                    <div class="stat-number"><?php echo formatearNumero($produccion_anual, 0); ?></div>
                                    <small>quintales</small>
                                </div>
                                <div class="stat-icon">
                                    <i class="bi bi-bar-chart-fill"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Alertas de cosechas próximas -->
                <?php if (!empty($proximas_cosechas)): ?>
                <div class="alert alert-warning" role="alert">
                    <h5 class="alert-heading">
                        <i class="bi bi-exclamation-triangle-fill"></i> 
                        Cosechas Próximas (15 días)
                    </h5>
                    <ul class="mb-0">
                        <?php foreach ($proximas_cosechas as $cosecha): ?>
                            <li>
                                <strong><?php echo $cosecha['cliente_nombre']; ?></strong> - 
                                Lote: <?php echo $cosecha['lote_nombre']; ?> 
                                (<?php echo ucfirst($cosecha['tipo_cultivo']); ?>) - 
                                <span class="badge bg-warning text-dark">
                                    <?php echo $cosecha['dias_restantes']; ?> días restantes
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <!-- Últimas cosechas -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">
                                    <i class="bi bi-clock-history"></i> 
                                    Últimas Cosechas Finalizadas
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($ultimas_cosechas)): ?>
                                    <p class="text-muted text-center">No hay cosechas finalizadas aún</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Cliente</th>
                                                    <th>Lote</th>
                                                    <th>Tipo</th>
                                                    <th>Fecha</th>
                                                    <th>Producción</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($ultimas_cosechas as $cosecha): ?>
                                                    <tr>
                                                        <td><strong><?php echo $cosecha['cliente_nombre']; ?></strong></td>
                                                        <td><?php echo $cosecha['lote_nombre']; ?></td>
                                                        <td>
                                                            <span class="badge bg-info">
                                                                <?php echo ucfirst($cosecha['tipo_cultivo']); ?>
                                                            </span>
                                                        </td>
                                                        <td><?php echo formatearFecha($cosecha['fecha_fin_real']); ?></td>
                                                        <td>
                                                            <strong class="text-success">
                                                                <?php echo formatearNumero($cosecha['produccion_quintales'], 2); ?> qq
                                                            </strong>
                                                        </td>
                                                        <td>
                                                            <a href="../controllers/EtapaController.php?action=view&id=<?php echo $cosecha['id']; ?>" 
                                                               class="btn btn-sm btn-info">
                                                                <i class="bi bi-eye"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Accesos rápidos -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <h4 class="mb-3">
                            <i class="bi bi-lightning-fill"></i> Accesos Rápidos
                        </h4>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="bi bi-person-plus-fill text-success" style="font-size: 3rem;"></i>
                                <h6 class="mt-2">Nuevo Cliente</h6>
                                <a href="../controllers/ClienteController.php?action=create" class="btn btn-sm btn-success">
                                    Crear
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="bi bi-geo-fill text-info" style="font-size: 3rem;"></i>
                                <h6 class="mt-2">Nuevo Lote</h6>
                                <a href="../controllers/LoteController.php?action=create" class="btn btn-sm btn-info">
                                    Crear
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="bi bi-calendar-plus text-warning" style="font-size: 3rem;"></i>
                                <h6 class="mt-2">Nueva Etapa</h6>
                                <a href="../controllers/EtapaController.php?action=create" class="btn btn-sm btn-warning">
                                    Crear
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="bi bi-droplet-fill text-primary" style="font-size: 3rem;"></i>
                                <h6 class="mt-2">Nueva Aplicación</h6>
                                <a href="../controllers/AplicacionController.php?action=create" class="btn btn-sm btn-primary">
                                    Crear
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../public/js/app.js"></script>
</body>
</html>