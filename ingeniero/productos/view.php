<?php
// ingeniero/productos/ver.php
require_once '../../config/config.php';
checkAuth();
// Las variables $producto y $aplicaciones vienen del controlador
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Producto - AgriManage</title>
    
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
            <a href="../../controllers/ProductoController.php" class="btn btn-sm btn-light">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </nav>
    
    <div class="container-fluid mt-4">
        <div class="row">
            <!-- Información del Producto -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-box-seam"></i> Información del Producto
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <i class="bi bi-box-seam" style="font-size: 5rem; color: #198754;"></i>
                            <h4 class="mt-2"><?php echo htmlspecialchars($producto['nombre']); ?></h4>
                            <span class="badge bg-primary">
                                <?php echo ucfirst($producto['tipo']); ?>
                            </span>
                        </div>
                        
                        <table class="table table-sm">
                            <tbody>
                                <tr>
                                    <th width="45%"><i class="bi bi-tag"></i> Tipo:</th>
                                    <td>
                                        <span class="badge bg-primary">
                                            <?php echo ucfirst($producto['tipo']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th><i class="bi bi-rulers"></i> Unidad:</th>
                                    <td><?php echo htmlspecialchars($producto['unidad_medida']); ?></td>
                                </tr>
                                <tr>
                                    <th><i class="bi bi-graph-up"></i> Stock:</th>
                                    <td>
                                        <?php if ($producto['stock'] < 10): ?>
                                            <strong class="text-danger" style="font-size: 1.2rem;">
                                                <?php echo formatearNumero($producto['stock'], 2); ?>
                                            </strong>
                                            <span class="badge bg-danger">Stock Bajo</span>
                                        <?php else: ?>
                                            <strong class="text-success" style="font-size: 1.2rem;">
                                                <?php echo formatearNumero($producto['stock'], 2); ?>
                                            </strong>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th><i class="bi bi-cash"></i> Precio:</th>
                                    <td>
                                        <strong>$<?php echo formatearNumero($producto['precio_unitario'], 2); ?></strong>
                                        <br><small class="text-muted">por <?php echo $producto['unidad_medida']; ?></small>
                                    </td>
                                </tr>
                                <tr>
                                    <th><i class="bi bi-circle-fill"></i> Estado:</th>
                                    <td>
                                        <?php if ($producto['activo']): ?>
                                            <span class="badge bg-success">Activo</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th><i class="bi bi-calendar-plus"></i> Creado:</th>
                                    <td><?php echo formatearFecha($producto['created_at']); ?></td>
                                </tr>
                                <tr>
                                    <th><i class="bi bi-calendar-check"></i> Actualizado:</th>
                                    <td><?php echo formatearFecha($producto['updated_at']); ?></td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <?php if ($producto['descripcion']): ?>
                            <div class="alert alert-info">
                                <small><strong>Descripción:</strong></small>
                                <p class="small mb-0"><?php echo nl2br(htmlspecialchars($producto['descripcion'])); ?></p>
                            </div>
                        <?php endif; ?>
                        
                        <div class="d-grid gap-2 mt-3">
                            <a href="../../controllers/ProductoController.php?action=edit&id=<?php echo $producto['id']; ?>" 
                               class="btn btn-warning">
                                <i class="bi bi-pencil"></i> Editar Producto
                            </a>
                            
                            <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#modalStock">
                                <i class="bi bi-arrow-repeat"></i> Ajustar Stock
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Estadísticas -->
                <div class="card mt-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="bi bi-bar-chart"></i> Estadísticas</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span><i class="bi bi-droplet-fill text-primary"></i> Total Aplicaciones:</span>
                            <strong><?php echo $producto['total_aplicaciones']; ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span><i class="bi bi-box text-success"></i> Stock Inicial:</span>
                            <strong><?php echo formatearNumero($producto['stock_inicial'] ?? 0, 2); ?></strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span><i class="bi bi-graph-down text-danger"></i> Consumido:</span>
                            <strong>
                                <?php 
                                $total_consumido = array_sum(array_column($aplicaciones, 'cantidad'));
                                echo formatearNumero($total_consumido, 2); 
                                ?>
                            </strong>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Historial de Aplicaciones -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-clock-history"></i> Historial de Aplicaciones
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($aplicaciones)): ?>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> Este producto no ha sido aplicado aún.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Cliente</th>
                                            <th>Lote</th>
                                            <th>Etapa</th>
                                            <th>Cantidad</th>
                                            <th>Dosis</th>
                                            <th>Método</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($aplicaciones as $app): ?>
                                            <tr>
                                                <td><?php echo formatearFecha($app['fecha_aplicacion']); ?></td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($app['cliente_nombre']); ?></strong>
                                                </td>
                                                <td><?php echo htmlspecialchars($app['lote_nombre']); ?></td>
                                                <td>
                                                    <span class="badge bg-info">
                                                        <?php echo ucfirst($app['tipo_cultivo']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <strong><?php echo formatearNumero($app['cantidad'], 2); ?></strong>
                                                    <?php echo $producto['unidad_medida']; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($app['dosis'] ?: '-'); ?></td>
                                                <td><?php echo htmlspecialchars($app['metodo_aplicacion'] ?: '-'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Resumen por mes -->
                            <div class="card bg-light mt-3">
                                <div class="card-body">
                                    <h6><i class="bi bi-calendar3"></i> Consumo por Mes (Último Año)</h6>
                                    <?php
                                    $consumo_mensual = [];
                                    foreach ($aplicaciones as $app) {
                                        $mes = date('Y-m', strtotime($app['fecha_aplicacion']));
                                        if (!isset($consumo_mensual[$mes])) {
                                            $consumo_mensual[$mes] = 0;
                                        }
                                        $consumo_mensual[$mes] += $app['cantidad'];
                                    }
                                    
                                    if (!empty($consumo_mensual)):
                                        arsort($consumo_mensual);
                                        $top_meses = array_slice($consumo_mensual, 0, 5, true);
                                    ?>
                                        <div class="row">
                                            <?php foreach ($top_meses as $mes => $cantidad): ?>
                                                <div class="col-md-4 mb-2">
                                                    <small class="text-muted"><?php echo date('M Y', strtotime($mes . '-01')); ?></small>
                                                    <br>
                                                    <strong class="text-primary">
                                                        <?php echo formatearNumero($cantidad, 2); ?> 
                                                        <?php echo $producto['unidad_medida']; ?>
                                                    </strong>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal Ajustar Stock -->
    <div class="modal fade" id="modalStock" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">Ajustar Stock</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="../../controllers/ProductoController.php?action=actualizar_stock&id=<?php echo $producto['id']; ?>" 
                      method="POST">
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <strong>Stock actual:</strong> <?php echo formatearNumero($producto['stock'], 2); ?> 
                            <?php echo $producto['unidad_medida']; ?>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Operación <span class="text-danger">*</span></label>
                            <select class="form-select" name="operacion" required>
                                <option value="">Seleccione...</option>
                                <option value="sumar">Agregar Stock (Compra/Ingreso)</option>
                                <option value="restar">Reducir Stock (Ajuste/Pérdida)</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Cantidad <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="cantidad" 
                                   step="0.01" min="0.01" required>
                            <small class="text-muted">Cantidad a agregar o reducir</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Motivo</label>
                            <textarea class="form-control" name="motivo" rows="2" 
                                      placeholder="Ej: Compra del 10/03/2026, Ajuste por inventario"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-info">
                            <i class="bi bi-arrow-repeat"></i> Actualizar Stock
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>