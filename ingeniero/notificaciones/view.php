
<?php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Notificación - AgriManage</title>
    
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
            <a href="../../controllers/NotificacionController.php" class="btn btn-sm btn-light">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </nav>
    
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-envelope-open"></i> Detalle de la Notificación
                            </h5>
                            <?php if ($notificacion['tipo'] == 'general'): ?>
                                <span class="badge bg-primary">General</span>
                            <?php else: ?>
                                <span class="badge bg-info">Individual</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Información del Remitente -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <small class="text-muted">De:</small>
                                <br>
                                <strong><?php echo htmlspecialchars($notificacion['emisor_nombre']); ?></strong>
                                <br>
                                <small class="text-muted"><?php echo $_SESSION['empresa_nombre']; ?></small>
                            </div>
                            <div class="col-md-6 text-end">
                                <small class="text-muted">Fecha de Envío:</small>
                                <br>
                                <strong><?php echo formatearFecha($notificacion['fecha_envio']); ?></strong>
                                <br>
                                <small class="text-muted">
                                    <?php echo date('H:i', strtotime($notificacion['fecha_envio'])); ?>
                                </small>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <!-- Destinatario -->
                        <div class="mb-3">
                            <small class="text-muted">Para:</small>
                            <br>
                            <?php if ($notificacion['tipo'] == 'general'): ?>
                                <span class="badge bg-primary">
                                    <i class="bi bi-people"></i> Todos los Clientes
                                </span>
                            <?php else: ?>
                                <strong><?php echo htmlspecialchars($notificacion['cliente_nombre']); ?></strong>
                                <br>
                                <?php if ($notificacion['cliente_email']): ?>
                                    <small>
                                        <i class="bi bi-envelope"></i> 
                                        <?php echo htmlspecialchars($notificacion['cliente_email']); ?>
                                    </small>
                                    <br>
                                <?php endif; ?>
                                <?php if ($notificacion['cliente_telefono']): ?>
                                    <small>
                                        <i class="bi bi-telephone"></i> 
                                        <?php echo htmlspecialchars($notificacion['cliente_telefono']); ?>
                                    </small>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        
                        <hr>
                        
                        <!-- Asunto -->
                        <div class="mb-3">
                            <small class="text-muted">Asunto:</small>
                            <h4><?php echo htmlspecialchars($notificacion['asunto']); ?></h4>
                        </div>
                        
                        <!-- Mensaje -->
                        <div class="mb-3">
                            <small class="text-muted">Mensaje:</small>
                            <div class="card bg-light mt-2">
                                <div class="card-body">
                                    <p style="white-space: pre-line;">
                                        <?php echo nl2br(htmlspecialchars($notificacion['mensaje'])); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Estado de Lectura -->
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            <strong>Estado:</strong>
                            <?php if ($notificacion['leida']): ?>
                                <span class="badge bg-success">Leída</span>
                            <?php else: ?>
                                <span class="badge bg-warning">No Leída</span>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Acciones -->
                        <div class="d-flex justify-content-between mt-4">
                            <div>
                                <a href="../../controllers/NotificacionController.php" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> Volver
                                </a>
                            </div>
                            <div>
                                <a href="../../controllers/NotificacionController.php?action=edit&id=<?php echo $notificacion['id']; ?>" 
                                   class="btn btn-warning">
                                    <i class="bi bi-pencil"></i> Editar
                                </a>
                                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalEliminar">
                                    <i class="bi bi-trash"></i> Eliminar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Información Adicional -->
                <div class="card mt-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="bi bi-info-circle"></i> Información Adicional</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <small class="text-muted">ID de Notificación:</small>
                                <br>
                                <strong><?php echo $notificacion['id']; ?></strong>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted">Tipo de Notificación:</small>
                                <br>
                                <strong><?php echo ucfirst($notificacion['tipo']); ?></strong>
                            </div>
                        </div>
                    </div>
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
                    <p>¿Está seguro de eliminar esta notificación?</p>
                    <p class="text-muted">Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <a href="../../controllers/NotificacionController.php?action=delete&id=<?php echo $notificacion['id']; ?>" 
                       class="btn btn-danger">
                        <i class="bi bi-trash"></i> Eliminar
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>