
<?php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Notificaciones - AgriManage</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../../public/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-dark bg-success sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="../dashboard.php">
                <i class="bi bi-trees"></i> AgriManage
            </a>
            <a href="../dashboard.php" class="btn btn-sm btn-light">
                <i class="bi bi-house"></i> Dashboard
            </a>
        </div>
    </nav>
    
    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between mb-3">
            <h2><i class="bi bi-bell-fill"></i> Notificaciones</h2>
            <a href="../../controllers/NotificacionController.php?action=create" class="btn btn-success">
                <i class="bi bi-plus-lg"></i> Nueva Notificación
            </a>
        </div>
        
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Fecha</th>
                                <th>Tipo</th>
                                <th>Destinatario</th>
                                <th>Asunto</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($notificaciones)): ?>
                                <tr><td colspan="5" class="text-center">No hay notificaciones</td></tr>
                            <?php else: ?>
                                <?php foreach ($notificaciones as $notif): ?>
                                    <tr>
                                        <td><?php echo formatearFecha($notif['fecha_envio']); ?></td>
                                        <td>
                                            <?php if ($notif['tipo'] == 'general'): ?>
                                                <span class="badge bg-primary">General</span>
                                            <?php else: ?>
                                                <span class="badge bg-info">Individual</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($notif['tipo'] == 'general'): ?>
                                                <em>Todos los clientes</em>
                                            <?php else: ?>
                                                <?php echo $notif['cliente_nombre']; ?>
                                            <?php endif; ?>
                                        </td>
                                        <td><strong><?php echo $notif['asunto']; ?></strong></td>
                                        <td>
                                            <a href="../../controllers/NotificacionController.php?action=view&id=<?php echo $notif['id']; ?>" 
                                               class="btn btn-sm btn-info">
                                                <i class="bi bi-eye"></i>
                                            </a>
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
