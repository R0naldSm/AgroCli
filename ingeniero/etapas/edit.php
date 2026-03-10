<?php
// ingeniero/etapas/editar.php
require_once '../../config/config.php';
checkAuth();
// La variable $etapa viene del controlador
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Etapa - AgriManage</title>
    
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
    
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">
                            <i class="bi bi-pencil-square"></i> Editar Etapa de Cultivo
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if ($etapa['estado'] == 'finalizada'): ?>
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle"></i>
                                <strong>Advertencia:</strong> Esta etapa ya está finalizada. Solo puede modificar observaciones.
                            </div>
                        <?php endif; ?>
                        
                        <form action="../../controllers/EtapaController.php?action=update&id=<?php echo $etapa['id']; ?>" method="POST">
                            <div class="alert alert-info">
                                <h6><i class="bi bi-info-circle"></i> Información Actual</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Cliente:</strong> <?php echo $etapa['cliente_nombre']; ?></p>
                                        <p class="mb-0"><strong>Lote:</strong> <?php echo $etapa['lote_nombre']; ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Estado:</strong> 
                                            <?php if ($etapa['estado'] == 'en_proceso'): ?>
                                                <span class="badge bg-warning">En Proceso</span>
                                            <?php else: ?>
                                                <span class="badge bg-success">Finalizada</span>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if ($etapa['estado'] == 'en_proceso'): ?>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Tipo de Cultivo <span class="text-danger">*</span></label>
                                        <select class="form-select" name="tipo_cultivo" id="tipo_cultivo" required>
                                            <option value="siembra" <?php echo ($etapa['tipo_cultivo'] == 'siembra') ? 'selected' : ''; ?>>
                                                Siembra (110 días)
                                            </option>
                                            <option value="soca" <?php echo ($etapa['tipo_cultivo'] == 'soca') ? 'selected' : ''; ?>>
                                                Soca (135 días)
                                            </option>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label class="form-label">Fecha de Inicio <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" name="fecha_inicio" 
                                               id="fecha_inicio"
                                               value="<?php echo $etapa['fecha_inicio']; ?>" required>
                                    </div>
                                </div>
                                
                                <div class="alert alert-info" id="info-estimacion">
                                    <i class="bi bi-info-circle"></i>
                                    <strong>Fecha estimada de cosecha:</strong> <span id="fecha_estimada"></span>
                                    <br>
                                    <small id="info-dias"></small>
                                </div>
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <label class="form-label">Observaciones</label>
                                <textarea class="form-control" name="observaciones" rows="4"><?php echo htmlspecialchars($etapa['observaciones']); ?></textarea>
                            </div>
                            
                            <div class="d-flex justify-content-end gap-2">
                                <a href="../../controllers/EtapaController.php" class="btn btn-secondary">Cancelar</a>
                                <button type="submit" class="btn btn-warning">
                                    <i class="bi bi-save"></i> Actualizar Etapa
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function calcularFechaEstimada() {
            const tipo = document.getElementById('tipo_cultivo').value;
            const fechaInicio = document.getElementById('fecha_inicio').value;
            
            if (tipo && fechaInicio) {
                const dias = tipo === 'siembra' ? 110 : 135;
                const fecha = new Date(fechaInicio);
                fecha.setDate(fecha.getDate() + dias);
                
                const fechaEstimada = fecha.toLocaleDateString('es-EC', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
                
                document.getElementById('fecha_estimada').textContent = fechaEstimada;
                document.getElementById('info-dias').textContent = `Duración: ${dias} días`;
            }
        }
        
        <?php if ($etapa['estado'] == 'en_proceso'): ?>
        document.getElementById('tipo_cultivo').addEventListener('change', calcularFechaEstimada);
        document.getElementById('fecha_inicio').addEventListener('change', calcularFechaEstimada);
        
        // Calcular al cargar
        calcularFechaEstimada();
        <?php endif; ?>
    </script>
</body>
</html>