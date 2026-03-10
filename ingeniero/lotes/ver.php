<?php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Lote - AgriManage</title>
    
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
            <a href="../../controllers/LoteController.php" class="btn btn-sm btn-light">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </nav>
    
    <div class="container-fluid mt-4">
        <div class="row">
            <!-- Información del Lote -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-geo-alt-fill"></i> Información del Lote
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <i class="bi bi-geo-alt-fill" style="font-size: 5rem; color: #0dcaf0;"></i>
                            <h4 class="mt-2"><?php echo htmlspecialchars($lote['nombre']); ?></h4>
                        </div>
                        
                        <table class="table table-sm">
                            <tbody>
                                <tr>
                                    <th width="40%"><i class="bi bi-person"></i> Cliente:</th>
                                    <td>
                                        <a href="../../controllers/ClienteController.php?action=view&id=<?php echo $lote['id_cliente']; ?>">
                                            <?php echo htmlspecialchars($lote['cliente_nombre']); ?>
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <th><i class="bi bi-card-text"></i> Cédula:</th>
                                    <td><?php echo htmlspecialchars($lote['cedula']); ?></td>
                                </tr>
                                <tr>
                                    <th><i class="bi bi-telephone"></i> Teléfono:</th>
                                    <td><?php echo htmlspecialchars($lote['telefono'] ?: '-'); ?></td>
                                </tr>
                                <tr>
                                    <th><i class="bi bi-rulers"></i> Tamaño:</th>
                                    <td>
                                        <strong><?php echo formatearNumero($lote['tamanio_hectareas'], 2); ?> ha</strong>
                                        <br><small class="text-muted">
                                            <?php echo formatearNumero($lote['tamanio_paradas'], 0); ?> paradas
                                            <br><?php echo formatearNumero($lote['tamanio_cuadras'], 2); ?> cuadras
                                        </small>
                                    </td>
                                </tr>
                                <tr>
                                    <th><i class="bi bi-sun"></i> Temporada:</th>
                                    <td>
                                        <?php if ($lote['temporada'] == 'invierno'): ?>
                                            <span class="badge bg-primary">
                                                <i class="bi bi-cloud-rain"></i> Invierno
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">
                                                <i class="bi bi-sun"></i> Verano
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th><i class="bi bi-geo"></i> Ubicación:</th>
                                    <td><?php echo htmlspecialchars($lote['ubicacion'] ?: '-'); ?></td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <div class="d-grid gap-2 mt-3">
                            <a href="../../controllers/LoteController.php?action=edit&id=<?php echo $lote['id']; ?>" 
                               class="btn btn-warning">
                                <i class="bi bi-pencil"></i> Editar Lote
                            </a>
                            <a href="../../controllers/EtapaController.php?action=create&id_lote=<?php echo $lote['id']; ?>" 
                               class="btn btn-success">
                                <i class="bi bi-plus-lg"></i> Nueva Etapa
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Etapas del Lote -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">
                            <i class="bi bi-calendar-check"></i> Etapas de Cultivo
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($etapas)): ?>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> Este lote no tiene etapas de cultivo registradas.
                                <a href="../../controllers/EtapaController.php?action=create&id_lote=<?php echo $lote['id']; ?>" 
                                   class="btn btn-sm btn-warning ms-2">
                                    <i class="bi bi-plus-lg"></i> Crear Primera Etapa
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Tipo</th>
                                            <th>Fecha Inicio</th>
                                            <th>Fecha Estimada</th>
                                            <th>Estado</th>
                                            <th>Producción</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($etapas as $etapa): ?>
                                            <tr>
                                                <td>
                                                    <span class="badge bg-info">
                                                        <?php echo ucfirst($etapa['tipo_cultivo']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo formatearFecha($etapa['fecha_inicio']); ?></td>
                                                <td>
                                                    <?php 
                                                    if ($etapa['estado'] == 'finalizada') {
                                                        echo formatearFecha($etapa['fecha_fin_real']);
                                                    } else {
                                                        echo formatearFecha($etapa['fecha_fin_estimada']);
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php if ($etapa['estado'] == 'en_proceso'): ?>
                                                        <span class="badge bg-warning">En Proceso</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-success">Finalizada</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($etapa['estado'] == 'finalizada'): ?>
                                                        <strong class="text-success">
                                                            <?php echo formatearNumero($etapa['produccion_quintales'], 2); ?> qq
                                                        </strong>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="../../controllers/EtapaController.php?action=view&id=<?php echo $etapa['id']; ?>" 
                                                           class="btn btn-info">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                        <?php if ($etapa['estado'] == 'en_proceso'): ?>
                                                            <a href="../../controllers/EtapaController.php?action=finalizar&id=<?php echo $etapa['id']; ?>" 
                                                               class="btn btn-success">
                                                                <i class="bi bi-check-circle"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
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