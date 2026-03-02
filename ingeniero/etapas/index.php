<?php
require_once '../../config/config.php';
checkAuth();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Etapas de Cultivo - AgriManage</title>
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
            <h2><i class="bi bi-calendar-check"></i> Etapas de Cultivo</h2>
            <a href="../../controllers/EtapaController.php?action=create" class="btn btn-warning">
                <i class="bi bi-plus-lg"></i> Nueva Etapa
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
                                <th>Lote</th>
                                <th>Tipo</th>
                                <th>Fecha Inicio</th>
                                <th>Fecha Estimada</th>
                                <th>Estado</th>
                                <th>Aplicaciones</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($etapas)): ?>
                                <tr><td colspan="9" class="text-center">No hay etapas registradas</td></tr>
                            <?php else: ?>
                                <?php foreach ($etapas as $etapa): ?>
                                    <tr>
                                        <td><?php echo $etapa['id']; ?></td>
                                        <td><strong><?php echo $etapa['cliente_nombre']; ?></strong></td>
                                        <td><?php echo $etapa['lote_nombre']; ?></td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?php echo ucfirst($etapa['tipo_cultivo']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo formatearFecha($etapa['fecha_inicio']); ?></td>
                                        <td><?php echo formatearFecha($etapa['fecha_fin_estimada']); ?></td>
                                        <td>
                                            <?php if ($etapa['estado'] == 'en_proceso'): ?>
                                                <span class="badge bg-warning">En Proceso</span>
                                            <?php else: ?>
                                                <span class="badge bg-success">Finalizada</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                <?php echo $etapa['total_aplicaciones']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="../../controllers/EtapaController.php?action=view&id=<?php echo $etapa['id']; ?>" 
                                                   class="btn btn-info"><i class="bi bi-eye"></i></a>
                                                <?php if ($etapa['estado'] == 'en_proceso'): ?>
                                                    <a href="../../controllers/EtapaController.php?action=finalizar&id=<?php echo $etapa['id']; ?>" 
                                                       class="btn btn-success"><i class="bi bi-check-circle"></i></a>
                                                <?php endif; ?>
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