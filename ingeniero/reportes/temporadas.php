<?php
// ingeniero/reportes/temporadas.php
require_once '../../config/config.php';
require_once '../../config/database.php';

checkAuth();

$database = new Database();
$db = $database->getConnection();
$id_empresa = $_SESSION['id_empresa'];

// Filtros
$filtro_anio = isset($_GET['anio']) ? intval($_GET['anio']) : date('Y');

try {
    // Comparación Invierno vs Verano
    $query = "SELECT 
              l.temporada,
              COUNT(DISTINCT l.id) as total_lotes,
              COUNT(ec.id) as total_etapas,
              COALESCE(SUM(ec.produccion_quintales), 0) as produccion_total,
              ROUND(AVG(ec.produccion_quintales), 2) as promedio_produccion,
              COALESCE(SUM(l.tamanio_hectareas), 0) as hectareas_total,
              ROUND(AVG(ec.dias_duracion), 0) as promedio_dias
              FROM lotes l
              LEFT JOIN etapas_cultivo ec ON l.id = ec.id_lote 
                  AND ec.estado = 'finalizada'
                  AND YEAR(ec.fecha_fin_real) = :anio
                  AND ec.deleted_at IS NULL
              WHERE l.id_empresa = :id_empresa
              AND l.deleted_at IS NULL
              GROUP BY l.temporada";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id_empresa', $id_empresa);
    $stmt->bindParam(':anio', $filtro_anio);
    $stmt->execute();
    $comparacion = $stmt->fetchAll();
    
    // Producción por temporada y tipo
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
              AND ec.deleted_at IS NULL
              GROUP BY l.temporada, ec.tipo_cultivo
              ORDER BY l.temporada, ec.tipo_cultivo";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id_empresa', $id_empresa);
    $stmt->bindParam(':anio', $filtro_anio);
    $stmt->execute();
    $detalle_temporada = $stmt->fetchAll();
    
    // Rendimiento por hectárea
    $query = "SELECT 
              l.temporada,
              l.nombre as lote_nombre,
              CONCAT(c.nombre, ' ', c.apellido) as cliente_nombre,
              l.tamanio_hectareas,
              COALESCE(SUM(ec.produccion_quintales), 0) as produccion_total,
              ROUND(COALESCE(SUM(ec.produccion_quintales), 0) / l.tamanio_hectareas, 2) as rendimiento
              FROM lotes l
              INNER JOIN clientes c ON l.id_cliente = c.id
              LEFT JOIN etapas_cultivo ec ON l.id = ec.id_lote 
                  AND ec.estado = 'finalizada'
                  AND YEAR(ec.fecha_fin_real) = :anio
                  AND ec.deleted_at IS NULL
              WHERE l.id_empresa = :id_empresa
              AND l.deleted_at IS NULL
              GROUP BY l.id, l.temporada, l.nombre, c.nombre, c.apellido, l.tamanio_hectareas
              HAVING produccion_total > 0
              ORDER BY rendimiento DESC
              LIMIT 10";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id_empresa', $id_empresa);
    $stmt->bindParam(':anio', $filtro_anio);
    $stmt->execute();
    $top_rendimiento = $stmt->fetchAll();
    
    // Evolución mensual por temporada
    $query = "SELECT 
              l.temporada,
              MONTH(ec.fecha_fin_real) as mes,
              COUNT(*) as total_cosechas,
              COALESCE(SUM(ec.produccion_quintales), 0) as produccion_total
              FROM etapas_cultivo ec
              INNER JOIN lotes l ON ec.id_lote = l.id
              WHERE ec.id_empresa = :id_empresa
              AND ec.estado = 'finalizada'
              AND YEAR(ec.fecha_fin_real) = :anio
              AND ec.deleted_at IS NULL
              GROUP BY l.temporada, MONTH(ec.fecha_fin_real)
              ORDER BY mes, l.temporada";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id_empresa', $id_empresa);
    $stmt->bindParam(':anio', $filtro_anio);
    $stmt->execute();
    $evolucion_mensual = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = "Error: " . $e->getMessage();
    $comparacion = [];
    $detalle_temporada = [];
    $top_rendimiento = [];
    $evolucion_mensual = [];
}

$meses = ['', 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte por Temporadas - AgriManage</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../../public/css/style.css" rel="stylesheet">
    
    <style>
        @media print { .no-print { display: none !important; } }
        .comparacion-card { min-height: 200px; }
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
            <h2><i class="bi bi-calendar3"></i> Reporte por Temporadas</h2>
            <p class="text-muted"><?php echo $_SESSION['empresa_nombre']; ?></p>
            <p class="text-muted">Año: <?php echo $filtro_anio; ?></p>
        </div>
        
        <!-- Filtro de Año -->
        <div class="card mb-4 no-print">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Año</label>
                        <select class="form-select" name="anio" onchange="this.form.submit()">
                            <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                                <option value="<?php echo $y; ?>" <?php echo ($filtro_anio == $y) ? 'selected' : ''; ?>>
                                    <?php echo $y; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Comparación Invierno vs Verano -->
        <div class="row mb-4">
            <div class="col-md-12">
                <h4 class="mb-3"><i class="bi bi-bar-chart"></i> Comparación Invierno vs Verano</h4>
            </div>
            
            <?php 
            $invierno = null;
            $verano = null;
            foreach ($comparacion as $temp) {
                if ($temp['temporada'] == 'invierno') $invierno = $temp;
                if ($temp['temporada'] == 'verano') $verano = $temp;
            }
            ?>
            
            <div class="col-md-6">
                <div class="card border-primary comparacion-card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-cloud-rain"></i> Temporada Invierno
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if ($invierno && $invierno['total_etapas'] > 0): ?>
                            <div class="row text-center">
                                <div class="col-4">
                                    <h3 class="text-primary"><?php echo $invierno['total_lotes']; ?></h3>
                                    <small>Lotes</small>
                                </div>
                                <div class="col-4">
                                    <h3 class="text-primary"><?php echo $invierno['total_etapas']; ?></h3>
                                    <small>Cosechas</small>
                                </div>
                                <div class="col-4">
                                    <h3 class="text-primary"><?php echo formatearNumero($invierno['hectareas_total'], 2); ?></h3>
                                    <small>Hectáreas</small>
                                </div>
                            </div>
                            <hr>
                            <div class="row text-center">
                                <div class="col-6">
                                    <h4 class="text-success"><?php echo formatearNumero($invierno['produccion_total'], 2); ?> qq</h4>
                                    <small>Producción Total</small>
                                </div>
                                <div class="col-6">
                                    <h4 class="text-info"><?php echo formatearNumero($invierno['produccion_total'] / $invierno['hectareas_total'], 2); ?></h4>
                                    <small>qq/ha Promedio</small>
                                </div>
                            </div>
                            <hr>
                            <div class="text-center">
                                <small class="text-muted">Duración promedio: <?php echo $invierno['promedio_dias']; ?> días</small>
                            </div>
                        <?php else: ?>
                            <p class="text-center text-muted">No hay datos de invierno para este año</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card border-warning comparacion-card">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">
                            <i class="bi bi-sun"></i> Temporada Verano
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if ($verano && $verano['total_etapas'] > 0): ?>
                            <div class="row text-center">
                                <div class="col-4">
                                    <h3 class="text-warning"><?php echo $verano['total_lotes']; ?></h3>
                                    <small>Lotes</small>
                                </div>
                                <div class="col-4">
                                    <h3 class="text-warning"><?php echo $verano['total_etapas']; ?></h3>
                                    <small>Cosechas</small>
                                </div>
                                <div class="col-4">
                                    <h3 class="text-warning"><?php echo formatearNumero($verano['hectareas_total'], 2); ?></h3>
                                    <small>Hectáreas</small>
                                </div>
                            </div>
                            <hr>
                            <div class="row text-center">
                                <div class="col-6">
                                    <h4 class="text-success"><?php echo formatearNumero($verano['produccion_total'], 2); ?> qq</h4>
                                    <small>Producción Total</small>
                                </div>
                                <div class="col-6">
                                    <h4 class="text-info"><?php echo formatearNumero($verano['produccion_total'] / $verano['hectareas_total'], 2); ?></h4>
                                    <small>qq/ha Promedio</small>
                                </div>
                            </div>
                            <hr>
                            <div class="text-center">
                                <small class="text-muted">Duración promedio: <?php echo $verano['promedio_dias']; ?> días</small>
                            </div>
                        <?php else: ?>
                            <p class="text-center text-muted">No hay datos de verano para este año</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Detalle por Temporada y Tipo -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-grid-3x3"></i> Detalle por Temporada y Tipo de Cultivo</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>Temporada</th>
                                <th>Tipo</th>
                                <th>Total Etapas</th>
                                <th>Hectáreas</th>
                                <th>Producción Total</th>
                                <th>Promedio/Etapa</th>
                                <th>Rendimiento</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($detalle_temporada)): ?>
                                <tr><td colspan="7" class="text-center">No hay datos</td></tr>
                            <?php else: ?>
                                <?php foreach ($detalle_temporada as $det): ?>
                                    <tr>
                                        <td>
                                            <?php if ($det['temporada'] == 'invierno'): ?>
                                                <i class="bi bi-cloud-rain text-primary"></i> Invierno
                                            <?php else: ?>
                                                <i class="bi bi-sun text-warning"></i> Verano
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?php echo ucfirst($det['tipo_cultivo']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $det['total_etapas']; ?></td>
                                        <td><?php echo formatearNumero($det['hectareas_total'], 2); ?> ha</td>
                                        <td>
                                            <strong class="text-success">
                                                <?php echo formatearNumero($det['produccion_total'], 2); ?> qq
                                            </strong>
                                        </td>
                                        <td><?php echo formatearNumero($det['promedio_produccion'], 2); ?> qq</td>
                                        <td>
                                            <strong>
                                                <?php 
                                                $rend = $det['produccion_total'] / $det['hectareas_total'];
                                                echo formatearNumero($rend, 2); 
                                                ?> qq/ha
                                            </strong>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Top 10 Rendimiento -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-trophy"></i> Top 10 Lotes por Rendimiento</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Cliente</th>
                                <th>Lote</th>
                                <th>Temporada</th>
                                <th>Hectáreas</th>
                                <th>Producción</th>
                                <th>Rendimiento</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($top_rendimiento)): ?>
                                <tr><td colspan="7" class="text-center">No hay datos</td></tr>
                            <?php else: ?>
                                <?php foreach ($top_rendimiento as $index => $top): ?>
                                    <tr>
                                        <td>
                                            <span class="badge bg-primary"><?php echo $index + 1; ?></span>
                                        </td>
                                        <td><strong><?php echo htmlspecialchars($top['cliente_nombre']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($top['lote_nombre']); ?></td>
                                        <td>
                                            <?php if ($top['temporada'] == 'invierno'): ?>
                                                <i class="bi bi-cloud-rain text-primary"></i>
                                            <?php else: ?>
                                                <i class="bi bi-sun text-warning"></i>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo formatearNumero($top['tamanio_hectareas'], 2); ?> ha</td>
                                        <td class="text-success">
                                            <strong><?php echo formatearNumero($top['produccion_total'], 2); ?> qq</strong>
                                        </td>
                                        <td>
                                            <strong class="text-primary" style="font-size: 1.1rem;">
                                                <?php echo formatearNumero($top['rendimiento'], 2); ?> qq/ha
                                            </strong>
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
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>