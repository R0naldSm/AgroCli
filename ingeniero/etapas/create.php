<?php
// ingeniero/etapas/crear.php
require_once '../../config/config.php';
checkAuth();
// La variable $clientes viene del controlador
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Etapa - AgriManage</title>
    
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
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">
                            <i class="bi bi-calendar-plus"></i> Nueva Etapa de Cultivo
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger">
                                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <form action="../../controllers/EtapaController.php?action=store" method="POST">
                            <div class="mb-3">
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
                            
                            <div class="mb-3">
                                <label class="form-label">Lote <span class="text-danger">*</span></label>
                                <select class="form-select" name="id_lote" id="id_lote" required disabled>
                                    <option value="">Primero seleccione un cliente</option>
                                </select>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Tipo de Cultivo <span class="text-danger">*</span></label>
                                    <select class="form-select" name="tipo_cultivo" id="tipo_cultivo" required>
                                        <option value="">Seleccione...</option>
                                        <option value="siembra">Siembra (110 días ≈ 4 meses)</option>
                                        <option value="soca">Soca (135 días ≈ 4.5 meses)</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Fecha de Inicio <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="fecha_inicio" id="fecha_inicio" required>
                                </div>
                            </div>
                            
                            <div class="alert alert-info" id="info-estimacion" style="display: none;">
                                <i class="bi bi-info-circle"></i>
                                <strong>Fecha estimada de cosecha:</strong> <span id="fecha_estimada"></span>
                                <br>
                                <small id="info-dias"></small>
                            </div>
                            
                            <div class="d-flex justify-content-end gap-2">
                                <a href="../../controllers/EtapaController.php" class="btn btn-secondary">Cancelar</a>
                                <button type="submit" class="btn btn-warning">
                                    <i class="bi bi-save"></i> Registrar Etapa
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
                        <p class="small mb-2"><strong>Siembra:</strong> Primera plantación del cultivo (≈110 días)</p>
                        <p class="small mb-0"><strong>Soca:</strong> Rebrote después de la primera cosecha (≈135 días)</p>
                        <hr>
                        <p class="small mb-0">La fecha de cosecha se calcula automáticamente según el tipo de cultivo.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Cargar lotes por cliente
        document.getElementById('id_cliente_select').addEventListener('change', function() {
            const clienteId = this.value;
            const selectLote = document.getElementById('id_lote');
            
            if (!clienteId) {
                selectLote.innerHTML = '<option value="">Primero seleccione un cliente</option>';
                selectLote.disabled = true;
                return;
            }
            
            fetch(`../../controllers/LoteController.php?action=por_cliente&id_cliente=${clienteId}`)
                .then(response => response.json())
                .then(data => {
                    let html = '<option value="">Seleccione un lote</option>';
                    data.forEach(lote => {
                        html += `<option value="${lote.id}">${lote.nombre} (${lote.tamanio_hectareas} ha - ${lote.temporada})</option>`;
                    });
                    selectLote.innerHTML = html;
                    selectLote.disabled = false;
                });
        });
        
        // Calcular fecha estimada
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
                document.getElementById('info-estimacion').style.display = 'block';
            } else {
                document.getElementById('info-estimacion').style.display = 'none';
            }
        }
        
        document.getElementById('tipo_cultivo').addEventListener('change', calcularFechaEstimada);
        document.getElementById('fecha_inicio').addEventListener('change', calcularFechaEstimada);
    </script>
</body>
</html>