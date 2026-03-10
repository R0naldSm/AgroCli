<?php
// ingeniero/aplicaciones/crear.php
require_once '../../config/config.php';
checkAuth();
// Las variables $clientes y $productos vienen del controlador
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Aplicación - AgriManage</title>
    
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
        <div class="row">
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-droplet-fill"></i> Nueva Aplicación de Productos
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger">
                                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <form action="../../controllers/AplicacionController.php?action=store" method="POST" id="form-aplicacion">
                            <!-- Selección de Cliente y Lote -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Cliente <span class="text-danger">*</span></label>
                                    <select class="form-select" id="id_cliente_select" required>
                                        <option value="">Seleccione un cliente</option>
                                        <?php foreach ($clientes as $cliente): ?>
                                            <option value="<?php echo $cliente['id']; ?>">
                                                <?php echo $cliente['nombre_completo']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Lote <span class="text-danger">*</span></label>
                                    <select class="form-select" id="id_lote" disabled required>
                                        <option value="">Primero seleccione un cliente</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Etapa de Cultivo <span class="text-danger">*</span></label>
                                <select class="form-select" name="id_etapa_cultivo" id="id_etapa_cultivo" disabled required>
                                    <option value="">Primero seleccione un lote</option>
                                </select>
                            </div>
                            
                            <hr>
                            
                            <!-- Productos a Aplicar -->
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6><i class="bi bi-box-seam"></i> Productos a Aplicar</h6>
                                <button type="button" class="btn btn-sm btn-success" id="btn-agregar-producto">
                                    <i class="bi bi-plus-lg"></i> Agregar Producto
                                </button>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-bordered" id="tabla-productos">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="30%">Producto</th>
                                            <th width="15%">Cantidad</th>
                                            <th width="15%">Dosis</th>
                                            <th width="20%">Fecha Aplicación</th>
                                            <th width="15%">Stock</th>
                                            <th width="5%"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="productos-tbody">
                                        <!-- Se llenarán con JavaScript -->
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label class="form-label">Método de Aplicación</label>
                                    <input type="text" class="form-control" name="metodo_aplicacion" 
                                           placeholder="Ej: Aspersión, Bomba manual, etc.">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Observaciones</label>
                                <textarea class="form-control" name="observaciones" rows="3" 
                                          placeholder="Condiciones del clima, recomendaciones, etc."></textarea>
                            </div>
                            
                            <div class="d-flex justify-content-end gap-2">
                                <a href="../../controllers/AplicacionController.php" class="btn btn-secondary">Cancelar</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Registrar Aplicación
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card bg-light">
                    <div class="card-body">
                        <h6><i class="bi bi-info-circle"></i> Información</h6>
                        <p class="small">Registre todos los productos aplicados en una misma sesión.</p>
                        <p class="small mb-0">El stock de los productos se actualizará automáticamente.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Productos disponibles desde PHP
        const productos = <?php echo json_encode($productos); ?>;
        let contadorProductos = 0;
        
        // Cargar lotes por cliente
        document.getElementById('id_cliente_select').addEventListener('change', function() {
            const clienteId = this.value;
            const selectLote = document.getElementById('id_lote');
            const selectEtapa = document.getElementById('id_etapa_cultivo');
            
            if (!clienteId) {
                selectLote.innerHTML = '<option value="">Primero seleccione un cliente</option>';
                selectLote.disabled = true;
                selectEtapa.innerHTML = '<option value="">Primero seleccione un lote</option>';
                selectEtapa.disabled = true;
                return;
            }
            
            fetch(`../../controllers/LoteController.php?action=por_cliente&id_cliente=${clienteId}`)
                .then(response => response.json())
                .then(data => {
                    let html = '<option value="">Seleccione un lote</option>';
                    data.forEach(lote => {
                        html += `<option value="${lote.id}">${lote.nombre} (${lote.tamanio_hectareas} ha)</option>`;
                    });
                    selectLote.innerHTML = html;
                    selectLote.disabled = false;
                });
        });
        
        // Cargar etapas por lote
        document.getElementById('id_lote').addEventListener('change', function() {
            const loteId = this.value;
            const selectEtapa = document.getElementById('id_etapa_cultivo');
            
            if (!loteId) {
                selectEtapa.innerHTML = '<option value="">Primero seleccione un lote</option>';
                selectEtapa.disabled = true;
                return;
            }
            
            fetch(`../../controllers/EtapaController.php?action=por_lote&id_lote=${loteId}`)
                .then(response => response.json())
                .then(data => {
                    let html = '<option value="">Seleccione una etapa</option>';
                    data.forEach(etapa => {
                        if (etapa.estado === 'en_proceso') {
                            html += `<option value="${etapa.id}">${etapa.tipo_cultivo} - ${etapa.fecha_inicio}</option>`;
                        }
                    });
                    selectEtapa.innerHTML = html;
                    selectEtapa.disabled = false;
                });
        });
        
        // Agregar producto
        document.getElementById('btn-agregar-producto').addEventListener('click', function() {
            agregarFilaProducto();
        });
        
        function agregarFilaProducto(datos = null) {
            const tbody = document.getElementById('productos-tbody');
            const tr = document.createElement('tr');
            const index = contadorProductos++;
            
            let optionsHtml = '<option value="">Seleccione producto</option>';
            productos.forEach(prod => {
                const selected = datos && datos.id_producto == prod.id ? 'selected' : '';
                optionsHtml += `<option value="${prod.id}" data-stock="${prod.stock}" data-unidad="${prod.unidad_medida}" ${selected}>
                    ${prod.nombre} (${prod.tipo})
                </option>`;
            });
            
            tr.innerHTML = `
                <td>
                    <select name="productos[${index}][id_producto]" class="form-select form-select-sm producto-select" required>
                        ${optionsHtml}
                    </select>
                </td>
                <td>
                    <input type="number" name="productos[${index}][cantidad]" 
                           class="form-control form-control-sm cantidad-input" 
                           value="${datos ? datos.cantidad : ''}" 
                           step="0.01" min="0.01" required>
                </td>
                <td>
                    <input type="text" name="productos[${index}][dosis]" 
                           class="form-control form-control-sm" 
                           value="${datos ? datos.dosis : ''}"
                           placeholder="Ej: 500ml/ha">
                </td>
                <td>
                    <input type="date" name="productos[${index}][fecha_aplicacion]" 
                           class="form-control form-control-sm" 
                           value="${datos ? datos.fecha_aplicacion : new Date().toISOString().split('T')[0]}" 
                           required>
                </td>
                <td class="stock-display text-center">
                    <span class="badge bg-secondary">-</span>
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger btn-eliminar-producto">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            `;
            
            tbody.appendChild(tr);
            
            // Event listeners
            const select = tr.querySelector('.producto-select');
            const cantidadInput = tr.querySelector('.cantidad-input');
            const stockDisplay = tr.querySelector('.stock-display');
            
            select.addEventListener('change', function() {
                const option = this.options[this.selectedIndex];
                const stock = option.dataset.stock;
                const unidad = option.dataset.unidad;
                
                if (stock) {
                    stockDisplay.innerHTML = `<span class="badge ${stock < 10 ? 'bg-danger' : 'bg-success'}">${stock} ${unidad}</span>`;
                }
            });
            
            cantidadInput.addEventListener('input', function() {
                const select = tr.querySelector('.producto-select');
                const option = select.options[select.selectedIndex];
                const stock = parseFloat(option.dataset.stock);
                const cantidad = parseFloat(this.value);
                
                if (cantidad > stock) {
                    this.classList.add('is-invalid');
                    alert('La cantidad supera el stock disponible');
                } else {
                    this.classList.remove('is-invalid');
                }
            });
            
            tr.querySelector('.btn-eliminar-producto').addEventListener('click', function() {
                tr.remove();
            });
            
            // Trigger change para mostrar stock inicial
            if (datos) {
                select.dispatchEvent(new Event('change'));
            }
        }
        
        // Agregar primera fila al cargar
        agregarFilaProducto();
        
        // Validar formulario
        document.getElementById('form-aplicacion').addEventListener('submit', function(e) {
            const filas = document.querySelectorAll('#productos-tbody tr');
            if (filas.length === 0) {
                e.preventDefault();
                alert('Debe agregar al menos un producto');
                return false;
            }
        });
    </script>
</body>
</html>