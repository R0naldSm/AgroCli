<?php
require_once '../../config/config.php';
checkAuth();
// Las variables $cliente, $lotes, $historial vienen del controlador
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Cliente - AgriManage</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../../public/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-dark bg-success sticky-top shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand" href="../dashboard.php">
                <i class="bi bi-trees"></i> <strong>AgriManage</strong>
            </a>
            <a href="../../controllers/ClienteController.php" class="btn btn-sm btn-light">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </nav>
    
    <div class="container-fluid mt-4">
        <div class="row">
            <!-- Información del Cliente -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-person-circle"></i> Información del Cliente
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <i class="bi bi-person-circle" style="font-size: 5rem; color: #198754;"></i>
                            <h4 class="mt-2"><?php echo htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido']); ?></h4>
                        </div>
                        
                        <table class="table table-sm">
                            <tbody>
                                <tr>
                                    <th width="40%"><i class="bi bi-card-text"></i> Cédula:</th>
                                    <td><?php echo htmlspecialchars($cliente['cedula']); ?></td>
                                </tr>
                                <tr>
                                    <th><i class="bi bi-telephone"></i> Teléfono:</th>
                                    <td><?php echo htmlspecialchars($cliente['telefono'] ?: '-'); ?></td>
                                </tr>
                                <tr>
                                    <th><i class="bi bi-envelope"></i> Email:</th>
                                    <td><?php echo htmlspecialchars($cliente['email'] ?: '-'); ?></td>
                                </tr>
                                <tr>
                                    <th><i class="bi bi-geo-alt"></i> Dirección:</th>
                                    <td><?php echo htmlspecialchars($cliente['direccion'] ?: '-'); ?></td>
                                </tr>
                                <tr>
                                    <th><i class="bi bi-calendar-plus"></i> Registrado:</th>
                                    <td><?php echo formatearFecha($cliente['created_at']); ?></td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <div class="d-grid gap-2 mt-3">
                            <a href="../../controllers/ClienteController.php?action=edit&id=<?php echo $cliente['id']; ?>" 
                               class="btn btn-warning">
                                <i class="bi bi-pencil"></i> Editar Cliente
                            </a>
                            <a href="../../controllers/NotificacionController.php?action=create&id_cliente=<?php echo $cliente['id']; ?>" 
                               class="btn btn-info">
                                <i class="bi bi-bell"></i> Enviar Notificación
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Estadísticas Rápidas -->
                <div class="card mt-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="bi bi-bar-chart"></i> Resumen</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span><i class="bi bi-geo-alt-fill text-info"></i> Lotes:</span>
                            <strong><?php echo count($lotes); ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span><i class="bi bi-calendar-check text-warning"></i> Cosechas:</span>
                            <strong><?php echo count($historial); ?></strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span><i class="bi bi-bar-chart-fill text-success"></i> Producción Total:</span>
                            <strong>
                                <?php 
                                $total_produccion = array_sum(array_column($historial, 'produccion_quintales'));
                                echo formatearNumero($total_produccion, 2); 
                                ?> qq
                            </strong>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Lotes y Historial -->
            <div class="col-md-8">
                <!-- Lotes del Cliente -->
                <div class="card mb-3">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-geo-alt-fill"></i> Lotes del Cliente
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($lotes)): ?>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> Este cliente no tiene lotes registrados aún.
                                <a href="../../controllers/LoteController.php?action=create&id_cliente=<?php echo $cliente['id']; ?>" 
                                   class="btn btn-sm btn-info ms-2">
                                    <i class="bi bi-plus-lg"></i> Crear Primer Lote
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Tamaño</th>
                                            <th>Temporada</th>
                                            <th>Etapas</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($lotes as $lote): ?>
                                            <tr>
                                                <td><strong><?php echo htmlspecialchars($lote['nombre']); ?></strong></td>
                                                <td>
                                                    <?php echo formatearNumero($lote['tamanio_hectareas'], 2); ?> ha
                                                    <br><small class="text-muted">
                                                        (<?php echo formatearNumero($lote['tamanio_paradas'], 0); ?> paradas)
                                                    </small>
                                                </td>
                                                <td>
                                                    <?php if ($lote['temporada'] == 'invierno'): ?>
                                                        <i class="bi bi-cloud-rain text-primary"></i> Invierno
                                                    <?php else: ?>
                                                        <i class="bi bi-sun text-warning"></i> Verano
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary">
                                                        <?php echo $lote['total_etapas']; ?> etapas
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="../../controllers/LoteController.php?action=view&id=<?php echo $lote['id']; ?>" 
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
                
                <!-- Historial de Producción -->
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-clock-history"></i> Historial de Producción
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($historial)): ?>
                            <p class="text-muted text-center">No hay historial de producción</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Lote</th>
                                            <th>Tipo</th>
                                            <th>Temporada</th>
                                            <th>Fecha Inicio</th>
                                            <th>Fecha Fin</th>
                                            <th>Producción</th>
                                            <th>Rendimiento</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($historial as $hist): ?>
                                            <tr>
                                                <td><strong><?php echo htmlspecialchars($hist['lote_nombre']); ?></strong></td>
                                                <td>
                                                    <span class="badge bg-info">
                                                        <?php echo ucfirst($hist['tipo_cultivo']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($hist['temporada'] == 'invierno'): ?>
                                                        <i class="bi bi-cloud-rain text-primary"></i>
                                                    <?php else: ?>
                                                        <i class="bi bi-sun text-warning"></i>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo formatearFecha($hist['fecha_inicio']); ?></td>
                                                <td><?php echo formatearFecha($hist['fecha_fin_real']); ?></td>
                                                <td>
                                                    <strong class="text-success">
                                                        <?php echo formatearNumero($hist['produccion_quintales'], 2); ?> qq
                                                    </strong>
                                                </td>
                                                <td>
                                                    <?php 
                                                    $rendimiento = $hist['produccion_quintales'] / $hist['tamanio_hectareas'];
                                                    echo formatearNumero($rendimiento, 2); ?> qq/ha
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
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>