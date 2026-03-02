<?php
require_once '../../config/config.php';
checkAuth();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lotes - AgriManage</title>
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
            <h2><i class="bi bi-geo-alt-fill"></i> Lotes/Parcelas</h2>
            <a href="../../controllers/LoteController.php?action=create" class="btn btn-info">
                <i class="bi bi-plus-lg"></i> Nuevo Lote
            </a>
        </div>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Nombre Lote</th>
                                <th>Tamaño</th>
                                <th>Temporada</th>
                                <th>Etapas</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($lotes)): ?>
                                <tr><td colspan="7" class="text-center">No hay lotes registrados</td></tr>
                            <?php else: ?>
                                <?php foreach ($lotes as $lote): ?>
                                    <tr>
                                        <td><?php echo $lote['id']; ?></td>
                                        <td><strong><?php echo $lote['cliente_nombre']; ?></strong></td>
                                        <td><?php echo $lote['nombre']; ?></td>
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
                                            <span class="badge bg-secondary"><?php echo $lote['total_etapas']; ?></span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="../../controllers/LoteController.php?action=view&id=<?php echo $lote['id']; ?>" 
                                                   class="btn btn-info"><i class="bi bi-eye"></i></a>
                                                <a href="../../controllers/LoteController.php?action=edit&id=<?php echo $lote['id']; ?>" 
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