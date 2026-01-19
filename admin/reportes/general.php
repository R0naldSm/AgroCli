<?php
// admin/reportes/general.php
require_once '../../config/config.php';
require_once '../../config/database.php';

// Verificar que sea admin_general
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'admin_general') {
    header('Location: ../login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Obtener estadísticas generales
try {
    // Totales globales
    $query = "SELECT 
              (SELECT COUNT(*) FROM empresas WHERE deleted_at IS NULL) as total_empresas,
              (SELECT COUNT(*) FROM usuarios WHERE deleted_at IS NULL AND rol != 'admin_general') as total_usuarios,
              (SELECT COUNT(*) FROM clientes WHERE deleted_at IS NULL) as total_clientes,
              (SELECT COUNT(*) FROM lotes WHERE deleted_at IS NULL) as total_lotes,
              (SELECT COUNT(*) FROM etapas_cultivo WHERE deleted_at IS NULL) as total_etapas,
              (SELECT COUNT(*) FROM productos WHERE deleted_at IS NULL) as total_productos";
    
    $stmt = $db->query($query);
    $totales = $stmt->fetch();
    
    // Producción por año
    $query = "SELECT 
              YEAR(fecha_fin_real) as anio,
              COUNT(*) as total_etapas,
              SUM(produccion_quintales) as produccion_total
              FROM etapas_cultivo
              WHERE estado = 'finalizada' 
              AND deleted_at IS NULL
              GROUP BY YEAR(fecha_fin_real)
              ORDER BY anio DESC
              LIMIT 5";
    
    $stmt = $db->query($query);
    $produccion_anual = $stmt->fetchAll();
    
    // Producción por empresa
    $query = "SELECT 
              e.nombre as empresa,
              COUNT(ec.id) as total_etapas,
              COALESCE(SUM(ec.produccion_quintales), 0) as produccion_total,
              COUNT(DISTINCT c.id) as total_clientes,
              COUNT(DISTINCT l.id) as total_lotes
              FROM empresas e
              LEFT JOIN etapas_cultivo ec ON e.id = ec.id_empresa AND ec.estado = 'finalizada' AND ec.deleted_at IS NULL
              LEFT JOIN lotes l ON e.id = l.id_empresa AND l.deleted_at IS NULL
              LEFT JOIN clientes c ON e.id = c.id_empresa AND c.deleted_at IS NULL
              WHERE e.deleted_at IS NULL
              GROUP BY e.id, e.nombre
              ORDER BY produccion_total DESC";
    
    $stmt = $db->query($query);
    $produccion_empresa = $stmt->fetchAll();
    
    // Producción por temporada
    $query = "SELECT 
              l.temporada,
              COUNT(ec.id) as total_etapas,
              COALESCE(SUM(ec.produccion_quintales), 0) as produccion_total,
              ROUND(AVG(ec.produccion_quintales), 2) as promedio_produccion
              FROM etapas_cultivo ec
              INNER JOIN lotes l ON ec.id_lote = l.id
              WHERE ec.estado = 'finalizada' 
              AND ec.deleted_at IS NULL
              AND YEAR(ec.fecha_fin_real) = YEAR(NOW())
              GROUP BY l.temporada";
    
    $stmt = $db->query($query);
    $produccion_temporada = $stmt->fetchAll();
    
    // Tipo de cultivo más usado
    $query = "SELECT 
              tipo_cultivo,
              COUNT(*) as total,
              COALESCE(SUM(produccion_quintales), 0) as produccion_total
              FROM etapas_cultivo
              WHERE deleted_at IS NULL
              GROUP BY tipo_cultivo";
    
    $stmt = $db->query($query);
    $cultivo_tipo = $stmt->fetchAll();
    
    // Top 10 productos más usados
    $query = "SELECT 
              p.nombre as producto,
              p.tipo,
              COUNT(a.id) as total_aplicaciones,
              SUM(a.cantidad) as cantidad_total
              FROM productos p
              INNER JOIN aplicaciones a ON p.id = a.id_producto
              WHERE p.deleted_at IS NULL AND a.deleted_at IS NULL
              GROUP BY p.id, p.nombre, p.tipo
              ORDER BY total_aplicaciones DESC
              LIMIT 10";
    
    $stmt = $db->query($query);
    $productos_top = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
    $totales = null;
    $produccion_anual = [];
    $produccion_empresa = [];
    $produccion_temporada = [];
    $cultivo_tipo = [];
    $productos_top = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte General - AgriManage</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../../public/css/style.css" rel="stylesheet">
    
    <style>
        @media print {
            .no-print { display: none !important; }
            .card { break-inside: avoid; }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-danger sticky-top shadow-sm no-print">
        <div class="container-fluid">
            <a class="navbar-brand" href="../dashboard.php">
                <i class="bi bi-shield-lock-fill"></i>
                <strong>AgriManage - ADMIN</strong>
            </a>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-success" onclick="window.print()">
                    <i class="bi bi-printer"></i> Imprimir
                </button>
                <a href="../dashboard.php" class="btn btn-sm btn-light">
                    <i class="bi bi-arrow-left"></i> Dashboard
                </a>
            </div>
        </div>
    </nav>
    
    <div class="container-fluid mt-4">
        <div class="text-center mb-4">
            <h1><i class="bi bi-bar-chart-fill"></i> Reporte General del Sistema</h1>
            <p class="text-muted">Generado el <?php echo date('d/m/Y H:i'); ?></p>
        </div>
        
        <!-- Totales Generales -->
        <div class="card mb-4">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="bi bi-grid-3x3"></i> Resumen General</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-2">
                        <h3 class="text-danger"><?php echo $totales['total_empresas']; ?></h3>
                        <p>Empresas</p>
                    </div>
                    <div class="col-md-2">
                        <h3 class="text-primary"><?php echo $totales['total_usuarios']; ?></h3>
                        <p>Usuarios</p>
                    </div>
                    <div class="col-md-2">
                        <h3 class="text-success"><?php echo $totales['total_clientes']; ?></h3>
                        <p>Clientes</p>
                    </div>
                    <div class="col-md-2">
                        <h3 class="text-info"><?php echo $totales['total_lotes']; ?></h3>
                        <p>Lotes</p>
                    </div>
                    <div class="col-md-2">
                        <h3 class="text-warning"><?php echo $totales['total_etapas']; ?></h3>
                        <p>Etapas</p>
                    </div>
                    <div class="col-md-2">
                        <h3 class="text-secondary"><?php echo $totales['total_productos']; ?></h3>
                        <p>Productos</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Producción Anual -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-calendar-check"></i> Producción por Año</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Año</th>
                                    <th>Etapas</th>
                                    <th>Producción (qq)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($produccion_anual as $pa): ?>
                                    <tr>
                                        <td><strong><?php echo $pa['anio']; ?></strong></td>
                                        <td><?php echo $pa['total_etapas']; ?></td>
                                        <td class="text-success">
                                            <strong><?php echo formatearNumero($pa['produccion_total'], 2); ?></strong> qq
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-warning text-white">
                        <h5 class="mb-0"><i class="bi bi-sun"></i> Producción por Temporada (<?php echo date('Y'); ?>)</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Temporada</th>
                                    <th>Etapas</th>
                                    <th>Total (qq)</th>
                                    <th>Promedio (qq)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($produccion_temporada as $pt): ?>
                                    <tr>
                                        <td>
                                            <i class="bi bi-<?php echo $pt['temporada'] == 'invierno' ? 'cloud-rain' : 'sun'; ?>"></i>
                                            <strong><?php echo ucfirst($pt['temporada']); ?></strong>
                                        </td>
                                        <td><?php echo $pt['total_etapas']; ?></td>
                                        <td><?php echo formatearNumero($pt['produccion_total'], 2); ?></td>
                                        <td class="text-info"><?php echo formatearNumero($pt['promedio_produccion'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Producción por Empresa -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-building"></i> Producción por Empresa</h5>
            </div>
            <div class="card-body">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Empresa</th>
                            <th>Clientes</th>
                            <th>Lotes</th>
                            <th>Etapas Finalizadas</th>
                            <th>Producción Total (qq)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($produccion_empresa as $pe): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($pe['empresa']); ?></strong></td>
                                <td><?php echo $pe['total_clientes']; ?></td>
                                <td><?php echo $pe['total_lotes']; ?></td>
                                <td><?php echo $pe['total_etapas']; ?></td>
                                <td class="text-success">
                                    <strong><?php echo formatearNumero($pe['produccion_total'], 2); ?></strong> qq
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Tipo de Cultivo y Productos Top -->
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="bi bi-diagram-3"></i> Tipo de Cultivo</h5>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Tipo</th>
                                    <th>Total Etapas</th>
                                    <th>Producción (qq)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cultivo_tipo as $ct): ?>
                                    <tr>
                                        <td><strong><?php echo ucfirst($ct['tipo_cultivo']); ?></strong></td>
                                        <td><?php echo $ct['total']; ?></td>
                                        <td><?php echo formatearNumero($ct['produccion_total'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="bi bi-box-seam"></i> Top 10 Productos Más Usados</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Tipo</th>
                                    <th>Aplicaciones</th>
                                    <th>Cantidad</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($productos_top as $index => $pt): ?>
                                    <tr>
                                        <td>
                                            <span class="badge bg-primary"><?php echo $index + 1; ?></span>
                                            <?php echo htmlspecialchars($pt['producto']); ?>
                                        </td>
                                        <td><small><?php echo ucfirst($pt['tipo']); ?></small></td>
                                        <td><?php echo $pt['total_aplicaciones']; ?></td>
                                        <td><?php echo formatearNumero($pt['cantidad_total'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>