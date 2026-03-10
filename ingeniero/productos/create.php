<?php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Producto - AgriManage</title>
    
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
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-box-seam"></i> Nuevo Producto
                        </h5>
                    </div>
                    <div class="card-body">
                        <form action="../../controllers/ProductoController.php?action=store" method="POST">
                            <div class="mb-3">
                                <label class="form-label">Nombre del Producto <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="nombre" 
                                       placeholder="Ej: Glifosato 480 SL" required>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Tipo <span class="text-danger">*</span></label>
                                    <select class="form-select" name="tipo" required>
                                        <option value="">Seleccione tipo</option>
                                        <option value="herbicida">Herbicida</option>
                                        <option value="fungicida">Fungicida</option>
                                        <option value="insecticida">Insecticida</option>
                                        <option value="fertilizante">Fertilizante</option>
                                        <option value="otro">Otro</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Unidad de Medida <span class="text-danger">*</span></label>
                                    <select class="form-select" name="unidad_medida" required>
                                        <option value="">Seleccione</option>
                                        <option value="litros">Litros (L)</option>
                                        <option value="galones">Galones (gal)</option>
                                        <option value="kilogramos">Kilogramos (kg)</option>
                                        <option value="libras">Libras (lb)</option>
                                        <option value="sacos">Sacos</option>
                                        <option value="unidades">Unidades</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Descripción</label>
                                <textarea class="form-control" name="descripcion" rows="3" 
                                          placeholder="Características, composición, recomendaciones de uso..."></textarea>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Stock Inicial <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="stock" 
                                           step="0.01" min="0" value="0" required>
                                    <small class="text-muted">Cantidad disponible en inventario</small>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Precio Unitario</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control" name="precio_unitario" 
                                               step="0.01" min="0" value="0">
                                    </div>
                                    <small class="text-muted">Precio por unidad de medida</small>
                                </div>
                            </div>
                            
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i>
                                <strong>Nota:</strong> El stock se actualizará automáticamente con cada aplicación registrada.
                            </div>
                            
                            <div class="d-flex justify-content-end gap-2">
                                <a href="../../controllers/ProductoController.php" class="btn btn-secondary">Cancelar</a>
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-save"></i> Guardar Producto
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-body">
                        <h6><i class="bi bi-lightbulb"></i> Tipos de Productos</h6>
                        <ul class="small mb-0">
                            <li><strong>Herbicida:</strong> Control de malezas</li>
                            <li><strong>Fungicida:</strong> Control de hongos</li>
                            <li><strong>Insecticida:</strong> Control de plagas</li>
                            <li><strong>Fertilizante:</strong> Nutrición del cultivo</li>
                            <li><strong>Otro:</strong> Coadyuvantes, etc.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>