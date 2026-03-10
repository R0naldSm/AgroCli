<?php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Producto - AgriManage</title>
    
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
            <a href="../../controllers/ProductoController.php" class="btn btn-sm btn-light">
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
                            <i class="bi bi-pencil-square"></i> Editar Producto
                        </h5>
                    </div>
                    <div class="card-body">
                        <form action="../../controllers/ProductoController.php?action=update&id=<?php echo $producto['id']; ?>" 
                              method="POST">
                            <div class="mb-3">
                                <label class="form-label">Nombre del Producto <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="nombre" 
                                       value="<?php echo htmlspecialchars($producto['nombre']); ?>" required>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Tipo <span class="text-danger">*</span></label>
                                    <select class="form-select" name="tipo" required>
                                        <option value="">Seleccione tipo</option>
                                        <option value="herbicida" <?php echo ($producto['tipo'] == 'herbicida') ? 'selected' : ''; ?>>Herbicida</option>
                                        <option value="fungicida" <?php echo ($producto['tipo'] == 'fungicida') ? 'selected' : ''; ?>>Fungicida</option>
                                        <option value="insecticida" <?php echo ($producto['tipo'] == 'insecticida') ? 'selected' : ''; ?>>Insecticida</option>
                                        <option value="fertilizante" <?php echo ($producto['tipo'] == 'fertilizante') ? 'selected' : ''; ?>>Fertilizante</option>
                                        <option value="otro" <?php echo ($producto['tipo'] == 'otro') ? 'selected' : ''; ?>>Otro</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Unidad de Medida <span class="text-danger">*</span></label>
                                    <select class="form-select" name="unidad_medida" required>
                                        <option value="">Seleccione</option>
                                        <option value="litros" <?php echo ($producto['unidad_medida'] == 'litros') ? 'selected' : ''; ?>>Litros (L)</option>
                                        <option value="galones" <?php echo ($producto['unidad_medida'] == 'galones') ? 'selected' : ''; ?>>Galones (gal)</option>
                                        <option value="kilogramos" <?php echo ($producto['unidad_medida'] == 'kilogramos') ? 'selected' : ''; ?>>Kilogramos (kg)</option>
                                        <option value="libras" <?php echo ($producto['unidad_medida'] == 'libras') ? 'selected' : ''; ?>>Libras (lb)</option>
                                        <option value="sacos" <?php echo ($producto['unidad_medida'] == 'sacos') ? 'selected' : ''; ?>>Sacos</option>
                                        <option value="unidades" <?php echo ($producto['unidad_medida'] == 'unidades') ? 'selected' : ''; ?>>Unidades</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Descripción</label>
                                <textarea class="form-control" name="descripcion" rows="3"><?php echo htmlspecialchars($producto['descripcion']); ?></textarea>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Stock Actual</label>
                                    <input type="number" class="form-control" name="stock" 
                                           step="0.01" min="0" 
                                           value="<?php echo $producto['stock']; ?>" required>
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label">Precio Unitario</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control" name="precio_unitario" 
                                               step="0.01" min="0" 
                                               value="<?php echo $producto['precio_unitario']; ?>">
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label">Estado</label>
                                    <select class="form-select" name="activo">
                                        <option value="1" <?php echo $producto['activo'] ? 'selected' : ''; ?>>Activo</option>
                                        <option value="0" <?php echo !$producto['activo'] ? 'selected' : ''; ?>>Inactivo</option>
                                    </select>
                                </div>
                            </div>
                            
                            <?php if ($producto['total_aplicaciones'] > 0): ?>
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i>
                                    Este producto tiene <strong><?php echo $producto['total_aplicaciones']; ?> aplicaciones</strong> registradas.
                                </div>
                            <?php endif; ?>
                            
                            <div class="d-flex justify-content-end gap-2">
                                <a href="../../controllers/ProductoController.php" class="btn btn-secondary">Cancelar</a>
                                <button type="submit" class="btn btn-warning">
                                    <i class="bi bi-save"></i> Actualizar Producto
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
                        <p class="small mb-2"><strong>Creado:</strong> <?php echo formatearFecha($producto['created_at']); ?></p>
                        <p class="small mb-0"><strong>Última actualización:</strong> <?php echo formatearFecha($producto['updated_at']); ?></p>
                    </div>
                </div>
                
                <?php if ($producto['stock'] < 10): ?>
                    <div class="card mt-3 border-danger">
                        <div class="card-body">
                            <h6 class="text-danger"><i class="bi bi-exclamation-triangle"></i> Stock Bajo</h6>
                            <p class="small mb-0">El stock actual está por debajo del nivel mínimo recomendado.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>