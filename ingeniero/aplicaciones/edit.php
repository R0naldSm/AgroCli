<?php
// ingeniero/aplicaciones/editar.php
require_once '../../config/config.php';
checkAuth();
// La variable $aplicacion y $productos vienen del controlador
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Aplicación - AgriManage</title>
    
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
            <a href="../../controllers/AplicacionController.php" class="btn btn-sm btn-light">
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
                            <i class="bi bi-pencil-square"></i> Editar Aplicación
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <h6><i class="bi bi-info-circle"></i> Información de la Aplicación</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Cliente:</strong> <?php echo $aplicacion['cliente_nombre']; ?></p>
                                    <p class="mb-0"><strong>Lote:</strong> <?php echo $aplicacion['lote_nombre']; ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Etapa:</strong> <?php echo ucfirst($aplicacion['tipo_cultivo']); ?></p>
                                    <p class="mb-0"><strong>Producto Original:</strong> <?php echo $aplicacion['producto_nombre']; ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <form action="../../controllers/AplicacionController.php?action=update&id=<?php echo $aplicacion['id']; ?>" 
                              method="POST">
                            
                            <div class="mb-3">
                                <label class="form-label">Producto <span class="text-danger">*</span></label>
                                <select class="form-select" name="id_producto" id="producto_select" required>
                                    <option value="">Seleccione un producto</option>
                                    <?php foreach ($productos as $producto): ?>
                                        <option value="<?php echo $producto['id']; ?>" 
                                                data-stock="<?php echo $producto['stock']; ?>"
                                                data-unidad="<?php echo $producto['unidad_medida']; ?>"
                                                <?php echo ($aplicacion['id_producto'] == $producto['id']) ? 'selected' : ''; ?>>
                                            <?php echo $producto['nombre']; ?> (<?php echo ucfirst($producto['tipo']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">Stock disponible: <span id="stock-display">-</span></small>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Cantidad <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="cantidad" id="cantidad"
                                           value="<?php echo $aplicacion['cantidad']; ?>"
                                           step="0.01" min="0.01" required>
                                    <small class="text-muted">Cantidad aplicada del producto</small>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Fecha de Aplicación <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="fecha_aplicacion"
                                           value="<?php echo $aplicacion['fecha_aplicacion']; ?>"
                                           max="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Dosis</label>
                                <input type="text" class="form-control" name="dosis"
                                       value="<?php echo htmlspecialchars($aplicacion['dosis']); ?>"
                                       placeholder="Ej: 500ml/ha, 2kg/parada">
                                <small class="text-muted">Dosis recomendada o utilizada</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Método de Aplicación</label>
                                <input type="text" class="form-control" name="metodo_aplicacion"
                                       value="<?php echo htmlspecialchars($aplicacion['metodo_aplicacion']); ?>"
                                       placeholder="Ej: Aspersión, Bomba manual">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Observaciones</label>
                                <textarea class="form-control" name="observaciones" rows="3"><?php echo htmlspecialchars($aplicacion['observaciones']); ?></textarea>
                            </div>
                            
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle"></i>
                                <strong>Importante:</strong> Al modificar la cantidad, el stock se recalculará automáticamente.
                            </div>
                            
                            <div class="d-flex justify-content-end gap-2">
                                <a href="../../controllers/AplicacionController.php" class="btn btn-secondary">Cancelar</a>
                                <button type="submit" class="btn btn-warning">
                                    <i class="bi bi-save"></i> Actualizar Aplicación
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
        const productoSelect = document.getElementById('producto_select');
        const cantidadInput = document.getElementById('cantidad');
        const stockDisplay = document.getElementById('stock-display');
        
        function actualizarStock() {
            const option = productoSelect.options[productoSelect.selectedIndex];
            const stock = parseFloat(option.dataset.stock);
            const unidad = option.dataset.unidad;
            
            if (stock !== undefined) {
                const clase = stock < 10 ? 'text-danger' : 'text-success';
                stockDisplay.innerHTML = `<strong class="${clase}">${stock} ${unidad}</strong>`;
            }
        }
        
        function validarCantidad() {
            const option = productoSelect.options[productoSelect.selectedIndex];
            const stock = parseFloat(option.dataset.stock);
            const cantidad = parseFloat(cantidadInput.value);
            const cantidadOriginal = <?php echo $aplicacion['cantidad']; ?>;
            
            // Sumar la cantidad original al stock actual
            const stockReal = stock + cantidadOriginal;
            
            if (cantidad > stockReal) {
                cantidadInput.classList.add('is-invalid');
                alert(`La cantidad supera el stock disponible (${stockReal})`);
            } else {
                cantidadInput.classList.remove('is-invalid');
            }
        }
        
        productoSelect.addEventListener('change', actualizarStock);
        cantidadInput.addEventListener('input', validarCantidad);
        
        // Inicializar
        actualizarStock();
    </script>
</body>
</html>