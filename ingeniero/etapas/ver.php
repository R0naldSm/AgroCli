<?php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Etapa - AgriManage</title>
    
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
            <a href="../../controllers/EtapaController.php" class="btn btn-sm btn-light">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </nav>
    
    <div class="container-fluid mt-4">
        <div class="row">
            <!-- Información de la Etapa -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">
                            <i class="bi bi-calendar-check"></i> Información de la Etapa
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <?php if ($etapa['estado'] == 'en_proceso'): ?>
                                <i class="bi bi-hourglass-split" style="font-size: 5rem; color: #ffc107;"></i>
                                <h5 class="mt-2">
                                    <span class="badge bg-warning">En Proceso</span>
                                </h5>
                            <?php else: ?>
                                <i class="bi bi-check-circle" style="font-size: 5rem; color: #198754;"></i>
                                <h5 class="mt-2">
                                    <span class="badge bg-success">Finalizada</span>
                                </h5>
                            <?php endif; ?>
                        </div>
                        
                        <table class="table table-sm">
                            <tbody>
                                <tr>
                                    <th width="45%"><i class="bi bi-person"></i> Cliente:</th>
                                    <td>
                                        <a href="../../controllers/ClienteController.php?action=view&id=<?php echo $etapa['id_cliente']; ?>">
                                            <?php echo htmlspecialchars($etapa['cliente_nombre']); ?>
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <th><i class="bi bi-geo-alt"></i> Lote:</th>
                                    <td>
                                        <a href="../../controllers/LoteController.php?action=view&id=<?php echo $etapa['id_lote']; ?>">
                                            <?php echo htmlspecialchars($etapa['lote_nombre']); ?>
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <th><i class="bi bi-rulers"></i> Tamaño:</th>
                                    <td><?php echo formatearNumero($etapa['tamanio_hectareas'], 2); ?> ha</td>
                                </tr>
                                <tr>
                                    <th><i class="bi bi-tag"></i> Tipo:</th>
                                    <td>
                                        <span class="badge bg-info">
                                            <?php echo ucfirst($etapa['tipo_cultivo']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th><i class="bi bi-sun"></i> Temporada:</th>
                                    <td>
                                        <?php if ($etapa['temporada'] == 'invierno'): ?>
                                            <i class="bi bi-cloud-rain text-primary"></i> Invierno
                                        <?php else: ?>
                                            <i class="bi bi-sun text-warning"></i> Verano
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th><i class="bi bi-calendar-plus"></i> Inicio:</th>
                                    <td><?php echo formatearFecha($etapa['fecha_inicio']); ?></td>
                                </tr>
                                <tr>
                                    <th><i class="bi bi-calendar-event"></i> Estimada:</th>
                                    <td><?php echo formatearFecha($etapa['fecha_fin_estimada']); ?></td>
                                </tr>
                                <?php if ($etapa['estado'] == 'finalizada'): ?>
                                    <tr>
                                        <th><i class="bi bi-calendar-check"></i> Cosecha:</th>
                                        <td><strong><?php echo formatearFecha($etapa['fecha_fin_real']); ?></strong></td>
                                    </tr>
                                    <tr>
                                        <th><i class="bi bi-clock"></i> Duración:</th>
                                        <td><?php echo $etapa['dias_duracion']; ?> días</td>
                                    </tr>
                                    <tr>
                                        <th><i class="bi bi-bar-chart"></i> Producción:</th>
                                        <td>
                                            <strong class="text-success" style="font-size: 1.2rem;">
                                                <?php echo formatearNumero($etapa['produccion_quintales'], 2); ?> qq
                                            </strong>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><i class="bi bi-speedometer"></i> Rendimiento:</th>
                                        <td>
                                            <?php 
                                            $rendimiento = $etapa['produccion_quintales'] / $etapa['tamanio_hectareas'];
                                            echo formatearNumero($rendimiento, 2); 
                                            ?> qq/ha
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        
                        <?php if ($etapa['observaciones']): ?>
                            <div class="alert alert-info">
                                <small><strong>Observaciones:</strong></small>
                                <p class="small mb-0"><?php echo nl2br(htmlspecialchars($etapa['observaciones'])); ?></p>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($etapa['estado'] == 'en_proceso'): ?>
                            <div class="d-grid gap-2 mt-3">
                                <a href="../../controllers/EtapaController.php?action=finalizar&id=<?php echo $etapa['id']; ?>" 
                                   class="btn btn-success">
                                    <i class="bi bi-check-circle"></i> Finalizar Etapa
                                </a>
                                <a href="../../controllers/AplicacionController.php?action=create&id_etapa=<?php echo $etapa['id']; ?>" 
                                   class="btn btn-primary">
                                    <i class="bi bi-droplet-fill"></i> Registrar Aplicación
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Aplicaciones de Productos -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-droplet-fill"></i> Aplicaciones de Productos
                        </h5>
                        <?php if ($etapa['estado'] == 'en_proceso'): ?>
                            <a href="../../controllers/AplicacionController.php?action=create&id_etapa=<?php echo $etapa['id']; ?>" 
                               class="btn btn-sm btn-light">
                                <i class="bi bi-plus-lg"></i> Nueva Aplicación
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <?php if (empty($aplicaciones)): ?>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> No hay aplicaciones registradas para esta etapa.
                                <?php if ($etapa['estado'] == 'en_proceso'): ?>
                                    <a href="../../controllers/AplicacionController.php?action=create&id_etapa=<?php echo $etapa['id']; ?>" 
                                       class="btn btn-sm btn-primary ms-2">
                                        <i class="bi bi-plus-lg"></i> Registrar Primera Aplicación
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Producto</th>
                                            <th>Tipo</th>
                                            <th>Cantidad</th>
                                            <th>Dosis</th>
                                            <th>Método</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($aplicaciones as $app): ?>
                                            <tr>
                                                <td><?php echo formatearFecha($app['fecha_aplicacion']); ?></td>
                                                <td><strong><?php echo htmlspecialchars($app['producto_nombre']); ?></strong></td>
                                                <td>
                                                    <span class="badge bg-secondary">
                                                        <?php echo ucfirst($app['producto_tipo']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo formatearNumero($app['cantidad'], 2); ?></td>
                                                <td><?php echo htmlspecialchars($app['dosis']); ?></td>
                                                <td><?php echo htmlspecialchars($app['metodo_aplicacion'] ?: '-'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Resumen de Aplicaciones -->
                            <div class="card bg-light mt-3">
                                <div class="card-body">
                                    <h6><i class="bi bi-pie-chart"></i> Resumen por Tipo de Producto</h6>
                                    <div class="row">
                                        <?php
                                        $tipos = [];
                                        foreach ($aplicaciones as $app) {
                                            $tipo = ucfirst($app['producto_tipo']);
                                            if (!isset($tipos[$tipo])) {
                                                $tipos[$tipo] = 0;
                                            }
                                            $tipos[$tipo]++;
                                        }
                                        
                                        foreach ($tipos as $tipo => $cantidad):
                                        ?>
                                            <div class="col-md-3">
                                                <div class="text-center">
                                                    <h4 class="text-primary"><?php echo $cantidad; ?></h4>
                                                    <small><?php echo $tipo; ?></small>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
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