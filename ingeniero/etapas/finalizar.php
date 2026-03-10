<?php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalizar Etapa - AgriManage</title>
    
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
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-check-circle"></i> Finalizar Etapa de Cultivo
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Información de la Etapa -->
                        <div class="alert alert-info">
                            <h6><i class="bi bi-info-circle"></i> Información de la Etapa</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Cliente:</strong> <?php echo $etapa['cliente_nombre']; ?></p>
                                    <p class="mb-1"><strong>Lote:</strong> <?php echo $etapa['lote_nombre']; ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Tipo:</strong> <?php echo ucfirst($etapa['tipo_cultivo']); ?></p>
                                    <p class="mb-1"><strong>Fecha Inicio:</strong> <?php echo formatearFecha($etapa['fecha_inicio']); ?></p>
                                    <p class="mb-0"><strong>Fecha Estimada:</strong> <?php echo formatearFecha($etapa['fecha_fin_estimada']); ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <form action="../../controllers/EtapaController.php?action=procesar_finalizacion&id=<?php echo $etapa['id']; ?>" 
                              method="POST">
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Fecha Real de Cosecha <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="fecha_fin_real" 
                                           value="<?php echo date('Y-m-d'); ?>" 
                                           max="<?php echo date('Y-m-d'); ?>" required>
                                    <small class="text-muted">Fecha en que se realizó la cosecha</small>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Producción Obtenida (quintales) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="produccion_quintales" 
                                           step="0.01" min="0" required>
                                    <small class="text-muted">Cantidad total cosechada</small>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Observaciones</label>
                                <textarea class="form-control" name="observaciones" rows="4" 
                                          placeholder="Detalles sobre la cosecha, condiciones del cultivo, problemas encontrados, etc."></textarea>
                            </div>
                            
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle"></i>
                                <strong>Importante:</strong> Una vez finalizada la etapa, no podrá modificarse. 
                                Asegúrese de que todos los datos sean correctos.
                            </div>
                            
                            <div class="d-flex justify-content-end gap-2">
                                <a href="../../controllers/EtapaController.php" class="btn btn-secondary">
                                    Cancelar
                                </a>
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check-circle"></i> Finalizar Etapa
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