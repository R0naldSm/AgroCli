<?php
// ingeniero/reportes/produccion.php
require_once '../../config/config.php';
require_once '../../config/database.php';

checkAuth();

$database = new Database();
$db = $database->getConnection();
$id_empresa = $_SESSION['id_empresa'];

// Filtros
$filtro_anio = isset($_GET['anio']) ? intval($_GET['anio']) : date('Y');
$filtro_temporada = isset($_GET['temporada']) ? $_GET['temporada'] : '';
$filtro_tipo = isset($_GET['tipo']) ? $_GET['tipo'] : '';

try {
    // Producción por temporada
    $query = "SELECT 
              l.temporada,
              ec.tipo_cultivo,
              COUNT(*) as total_etapas,
              COALESCE(SUM(ec.produccion_quintales), 0) as produccion_total,
              ROUND(AVG(ec.produccion_quintales), 2) as promedio_produccion,
              COALESCE(SUM(l.tamanio_hectareas), 0) as hectareas_total
              FROM etapas_cultivo ec
              INNER JOIN lotes l ON ec.id_lote = l.id
              WHERE ec.id_empresa = :id_empresa 
              AND ec.estado = 'finalizada'
              AND YEAR(ec.fecha_fin_real) = :anio
              AND ec.deleted_at IS NULL";
    
    if ($filtro_temporada) {
        $query .= " AND l.temporada = :temporada";
    }
    
    if ($filtro_tipo) {
        $query .= " AND ec.tipo_cultivo = :tipo";
    }
    
    $query .= " GROUP BY l.temporada, ec.tipo_cultivo";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id_empresa', $id_empresa);
    $stmt->bindParam(':anio', $filtro_anio);
    
    if ($filtro_temporada) {
        $stmt->bindParam(':temporada', $filtro_temporada);
    }
    
    if ($filtro_tipo) {
        $stmt->bindParam(':tipo', $filtro_tipo);
    }
    
    $stmt->execute();
    $produccion_temporada = $stmt->fetchAll();
    
    // Top 10 productores
    $query = "SELECT 
              CONCAT(c.nombre, ' ', c.apellido) as cliente_nombre,
              COUNT(DISTINCT l.id) as total_lotes,
              COUNT(ec.id) as total_cosechas,
              COALESCE(SUM(ec.produccion_quintales), 0) as produccion_total,
              ROUND(AVG(ec.produccion_quintales), 2) as promedio_produccion
              FROM clientes c
              INNER JOIN lotes l ON c.id = l.id_cliente
              INNER JOIN etapas_cultivo ec ON l.id = ec.id_lote
              WHERE c.id_empresa = :id_empresa
              AND ec.estado = 'finalizada'
              AND YEAR(ec.fecha_fin_real) = :anio
              AND ec.deleted_at IS NULL
              GROUP BY c.id, c.nombre, c.apellido
              ORDER BY produccion_total DESC
              LIMIT 10";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id_empresa', $id_empresa);
    $stmt->bindParam(':anio', $filtro_anio);
    $stmt->execute();
    $top_productores = $stmt->fetchAll();
    
    // Producción mensual
    $query = "SELECT 
              MONTH(ec.fecha_fin_real) as mes,
              COUNT(*) as total_cosechas,
              COALESCE(SUM(ec.produccion_quintales), 0) as produccion_total
              FROM etapas_cultivo ec
              WHERE ec.id_empresa = :id_empresa
              AND ec.estado = 'finalizada'
              AND YEAR(ec.fecha_fin_real) = :anio
              AND ec.deleted_at IS NULL
              GROUP BY MONTH(ec.fecha_fin_real)
              ORDER BY mes";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id_empresa', $id_empresa);
    $stmt->bindParam(':anio', $filtro_anio);
    $stmt->execute();
    $produccion_mensual = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = "Error: " . $e->getMessage();
    $produccion_temporada = [];
    $top_productores = [];
    $produccion_mensual = [];
}

$meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
          'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Producción - AgriManage</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../../public/css/style.css" rel="stylesheet">
    
    <style>
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-success sticky-top shadow-sm no-print">
        <div class="container-fluid">
            <a class="navbar-brand" href="../dashboard.php">
                <i class="bi bi-trees"></i> <strong>AgriManage</strong>
            </a>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-light" onclick="window.print()">
                    <i class="bi bi-printer"></i> Imprimir
                </button>
                <a href="../dashboard.php" class="btn btn-sm btn-light">
                    <i class="bi bi-house"></i> Dashboard
                </a>
            </div>
        </div>
    </nav>
    
    <div class="container-fluid mt-4">
        <div class="text-center mb-4">
            <h2><i class="bi bi-bar-chart-fill"></i> Reporte de Producción</h2>
            <p class="text-muted"><?php echo $_SESSION['empresa_nombre']; ?></p>
            <p class="text-muted">Generado el <?php echo date('d/m/Y H:i'); ?></p>
        </div>
        
        <!-- Filtros -->
        <div class="card mb-4 no-print">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-funnel"></i> Filtros</h5>
            </div>
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Año</label>
                        <select class="form-select" name="anio">
                            <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                                <option value="<?php echo $y; ?>" <?php echo ($filtro_anio == $y) ? 'selected' : ''; ?>>
                                    <?php echo $y; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">Temporada</label>
                        <select class="form-select" name="temporada">
                            <option value="">Todas</option>
                            <option value="invierno" <?php echo ($filtro_temporada == 'invierno') ? 'selected' : ''; ?>>Invierno</option>
                            <option value="verano" <?php echo ($filtro_temporada == 'verano') ? 'selected' : ''; ?>>Verano</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">Tipo de Cultivo</label>
                        <select class="form-select" name="tipo">
                            <option value="">Todos</option>
                            <option value="siembra" <?php echo ($filtro_tipo == 'siembra') ? 'selected' : ''; ?>>Siembra</option>
                            <option value="soca" <?php echo ($filtro_tipo == 'soca') ? 'selected' : ''; ?>>Soca</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Filtrar
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Producción por Temporada y Tipo -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-grid-3x3"></i> Producción por Temporada y Tipo</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Temporada</th>
                                <th>Tipo</th>
                                <th>Total Etapas</th>
                                <th>Hectáreas</th>
                                <th>Producción Total</th>
                                <th>Promedio</th>
                                <th>Rendimiento</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($produccion_temporada)): ?>
                                <tr><td colspan="7" class="text-center">No hay datos</td></tr>
                            <?php else: ?>
                                <?php foreach ($produccion_temporada as $prod): ?>
                                    <tr>
                                        <td>
                                            <?php if ($prod['temporada'] == 'invierno'): ?>
                                                <i class="bi bi-cloud-rain text-primary"></i> Invierno
                                            <?php else: ?>
                                                <i class="bi bi-sun text-warning"></i> Verano
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?php echo ucfirst($prod['tipo_cultivo']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $prod['total_etapas']; ?></td>
                                        <td><?php echo formatearNumero($prod['hectareas_total'], 2); ?> ha</td>
                                        <td>
                                            <strong class="text-success">
                                                <?php echo formatearNumero($prod['produccion_total'], 2); ?> qq
                                            </strong>
                                        </td>
                                        <td><?php echo formatearNumero($prod['promedio_produccion'], 2); ?> qq</td>
                                        <td>
                                            <?php 
                                            $rendimiento = $prod['produccion_total'] / $prod['hectareas_total'];
                                            echo formatearNumero($rendimiento, 2); 
                                            ?> qq/ha
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="row">
            <!-- Top Productores -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-trophy"></i> Top 10 Productores</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Cliente</th>
                                        <th>Lotes</th>
                                        <th>Cosechas</th>
                                        <th>Total (qq)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($top_productores)): ?>
                                        <tr><td colspan="5" class="text-center">No hay datos</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($top_productores as $index => $prod): ?>
                                            <tr>
                                                <td>
                                                    <span class="badge bg-primary"><?php echo $index + 1; ?></span>
                                                </td>
                                                <td><strong><?php echo htmlspecialchars($prod['cliente_nombre']); ?></strong></td>
                                                <td><?php echo $prod['total_lotes']; ?></td>
                                                <td><?php echo $prod['total_cosechas']; ?></td>
                                                <td class="text-success">
                                                    <strong><?php echo formatearNumero($prod['produccion_total'], 2); ?></strong>
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
            
            <!-- Producción Mensual -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="bi bi-calendar3"></i> Producción Mensual</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Mes</th>
                                        <th>Cosechas</th>
                                        <th>Producción (qq)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($produccion_mensual)): ?>
                                        <tr><td colspan="3" class="text-center">No hay datos</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($produccion_mensual as $pm): ?>
                                            <tr>
                                                <td><strong><?php echo $meses[$pm['mes']]; ?></strong></td>
                                                <td><?php echo $pm['total_cosechas']; ?></td>
                                                <td class="text-success">
                                                    <strong><?php echo formatearNumero($pm['produccion_total'], 2); ?></strong>
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
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>