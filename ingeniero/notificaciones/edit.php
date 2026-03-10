
<?php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Notificación - AgriManage</title>
    
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
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">
                            <i class="bi bi-pencil-square"></i> Editar Notificación
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            <strong>Tipo:</strong> 
                            <?php if ($notificacion['tipo'] == 'general'): ?>
                                <span class="badge bg-primary">Notificación General</span>
                            <?php else: ?>
                                <span class="badge bg-info">Notificación Individual</span>
                                <br><strong>Destinatario:</strong> <?php echo $notificacion['cliente_nombre']; ?>
                            <?php endif; ?>
                        </div>
                        
                        <form action="../../controllers/NotificacionController.php?action=update&id=<?php echo $notificacion['id']; ?>" 
                              method="POST">
                            
                            <div class="mb-3">
                                <label class="form-label">Asunto <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="asunto" 
                                       value="<?php echo htmlspecialchars($notificacion['asunto']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Mensaje <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="mensaje" rows="6" required><?php echo htmlspecialchars($notificacion['mensaje']); ?></textarea>
                            </div>
                            
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle"></i>
                                <strong>Nota:</strong> No se puede cambiar el tipo ni el destinatario de una notificación existente.
                            </div>
                            
                            <div class="d-flex justify-content-end gap-2">
                                <a href="../../controllers/NotificacionController.php" class="btn btn-secondary">Cancelar</a>
                                <button type="submit" class="btn btn-warning">
                                    <i class="bi bi-save"></i> Actualizar Notificación
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
