<?php
require_once '../../config/config.php';
checkAuth();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Aplicaciones - AgriManage</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../../public/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-dark bg-success sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="../dashboard.php"><i class="bi bi-trees"></i> AgriManage</a>
            <a href="../dashboard.php" class="btn btn-sm btn-light"><i class="bi bi-house"></i> Dashboard</a>
        </div>
    </nav>
    
    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between mb-3">
            <h2><i class="bi bi-droplet-fill"></i> Aplicaciones de Productos</h2>
            <a href="../../controllers/AplicacionController.php?action=create" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Nueva Aplicación
            </a>
        </div>
        
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead class="table-dark">
                            <tr>
                                <th>Fecha</th>
                                <th>Cliente</th>
                                <th>Lote</th>
                                <th>Producto</th>
                                <th>Tipo</th>
                                <th>Cantidad</th>
                                <th>Dosis</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($aplicaciones)): ?>
                                <tr><td colspan="7" class="text-center">No hay aplicaciones registradas</td></tr>
                            <?php else: ?>
                                <?php foreach ($aplicaciones as $app): ?>
                                    <tr>
                                        <td><?php echo formatearFecha($app['fecha_aplicacion']); ?></td>
                                        <td><?php echo $app['cliente_nombre']; ?></td>
                                        <td><?php echo $app['lote_nombre']; ?></td>
                                        <td><strong><?php echo $app['producto_nombre']; ?></strong></td>
                                        <td><span class="badge bg-info"><?php echo ucfirst($app['producto_tipo']); ?></span></td>
                                        <td><?php echo formatearNumero($app['cantidad'], 2); ?></td>
                                        <td><?php echo $app['dosis']; ?></td>
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