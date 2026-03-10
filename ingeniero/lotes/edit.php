<?php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Lote - AgriManage</title>
    
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
            <a href="../../controllers/LoteController.php" class="btn btn-sm btn-light">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </nav>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">
                            <i class="bi bi-pencil-square"></i> Editar Lote
                        </h5>
                    </div>
                    <div class="card-body">
                        <form action="../../controllers/LoteController.php?action=update&id=<?php echo $lote['id']; ?>" method="POST">
                            <div class="mb-3">
                                <label class="form-label">Cliente <span class="text-danger">*</span></label>
                                <select class="form-select" name="id_cliente" required>
                                    <option value="">Seleccione un cliente</option>
                                    <?php foreach ($clientes as $cliente): ?>
                                        <option value="<?php echo $cliente['id']; ?>" 
                                                <?php echo ($lote['id_cliente'] == $cliente['id']) ? 'selected' : ''; ?>>
                                            <?php echo $cliente['nombre_completo'] . ' - ' . $cliente['cedula']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Nombre del Lote <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="nombre" 
                                       value="<?php echo htmlspecialchars($lote['nombre']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Ubicación</label>
                                <textarea class="form-control" name="ubicacion" rows="2"><?php echo htmlspecialchars($lote['ubicacion']); ?></textarea>
                            </div>
                            
                            <div class="card bg-light mb-3">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="bi bi-rulers"></i> Tamaño del Lote
                                    </h6>
                                    
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label class="form-label">Paradas <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" name="tamanio_paradas" 
                                                   id="tamanio_paradas" step="0.01" 
                                                   value="<?php echo $lote['tamanio_paradas']; ?>" required>
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <label class="form-label">Cuadras</label>
                                            <input type="number" class="form-control" id="tamanio_cuadras" 
                                                   step="0.01" readonly 
                                                   value="<?php echo $lote['tamanio_cuadras']; ?>">
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <label class="form-label">Hectáreas</label>
                                            <input type="number" class="form-control" id="tamanio_hectareas" 
                                                   step="0.01" readonly 
                                                   value="<?php echo $lote['tamanio_hectareas']; ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Temporada <span class="text-danger">*</span></label>
                                <select class="form-select" name="temporada" required>
                                    <option value="">Seleccione temporada</option>
                                    <option value="invierno" <?php echo ($lote['temporada'] == 'invierno') ? 'selected' : ''; ?>>
                                        Invierno
                                    </option>
                                    <option value="verano" <?php echo ($lote['temporada'] == 'verano') ? 'selected' : ''; ?>>
                                        Verano
                                    </option>
                                </select>
                            </div>
                            
                            <div class="d-flex justify-content-end gap-2">
                                <a href="../../controllers/LoteController.php" class="btn btn-secondary">Cancelar</a>
                                <button type="submit" class="btn btn-warning">
                                    <i class="bi bi-save"></i> Actualizar Lote
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-body">
                        <h6><i class="bi bi-info-circle"></i> Información</h6>
                        <p class="small mb-2"><strong>Creado:</strong> <?php echo formatearFecha($lote['created_at']); ?></p>
                        <p class="small mb-0"><strong>Última actualización:</strong> <?php echo formatearFecha($lote['updated_at']); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Conversiones automáticas
        const inputParadas = document.getElementById('tamanio_paradas');
        const inputCuadras = document.getElementById('tamanio_cuadras');
        const inputHectareas = document.getElementById('tamanio_hectareas');
        
        inputParadas.addEventListener('input', function() {
            const paradas = parseFloat(this.value) || 0;
            inputCuadras.value = (paradas / 16).toFixed(2);
            inputHectareas.value = (paradas / 21).toFixed(2);
        });
    </script>
</body>
</html>
