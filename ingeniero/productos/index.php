<?php
require_once '../../config/config.php';
checkAuth();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Productos - AgriManage</title>
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
            <h2><i class="bi bi-box-seam"></i> Productos</h2>
            <a href="../../controllers/ProductoController.php?action=create" class="btn btn-success">
                <i class="bi bi-plus-lg"></i> Nuevo Producto
            </a>
        </div>
        
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Nombre</th>
                                <th>Tipo</th>
                                <th>Stock</th>
                                <th>Unidad</th>
                                <th>Precio</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($productos)): ?>
                                <tr><td colspan="7" class="text-center">No hay productos</td></tr>
                            <?php else: ?>
                                <?php foreach ($productos as $prod): ?>
                                    <tr>
                                        <td><strong><?php echo $prod['nombre']; ?></strong></td>
                                        <td><span class="badge bg-primary"><?php echo ucfirst($prod['tipo']); ?></span></td>
                                        <td>
                                            <?php if ($prod['stock'] < 10): ?>
                                                <span class="text-danger"><strong><?php echo formatearNumero($prod['stock'], 2); ?></strong></span>
                                            <?php else: ?>
                                                <?php echo formatearNumero($prod['stock'], 2); ?>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $prod['unidad_medida']; ?></td>
                                        <td>$<?php echo formatearNumero($prod['precio_unitario'], 2); ?></td>
                                        <td>
                                            <?php if ($prod['activo']): ?>
                                                <span class="badge bg-success">Activo</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Inactivo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="../../controllers/ProductoController.php?action=edit&id=<?php echo $prod['id']; ?>" 
                                                   class="btn btn-warning"><i class="bi bi-pencil"></i></a>
                                            </div>
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