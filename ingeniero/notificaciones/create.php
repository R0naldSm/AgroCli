<?php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nueva Notificación - AgriManage</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-dark bg-success sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="../dashboard.php">
                <i class="bi bi-trees"></i> AgriManage
            </a>
            <a href="../../controllers/NotificacionController.php" class="btn btn-sm btn-light">
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
                            <i class="bi bi-bell-fill"></i> Nueva Notificación
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="../../controllers/NotificacionController.php?action=store">
                            <div class="mb-3">
                                <label class="form-label">Tipo de Notificación <span class="text-danger">*</span></label>
                                <select class="form-select" name="tipo" id="tipo" required>
                                    <option value="">Seleccione...</option>
                                    <option value="general">General (Todos los clientes)</option>
                                    <option value="individual">Individual</option>
                                </select>
                            </div>
                            
                            <div class="mb-3" id="div-cliente" style="display: none;">
                                <label class="form-label">Cliente</label>
                                <select class="form-select" name="id_cliente" id="id_cliente">
                                    <option value="">Seleccione un cliente</option>
                                    <?php foreach ($clientes as $cliente): ?>
                                        <option value="<?php echo $cliente['id']; ?>">
                                            <?php echo $cliente['nombre_completo']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Asunto <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="asunto" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Mensaje <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="mensaje" rows="5" required></textarea>
                            </div>
                            
                            <div class="d-flex justify-content-end gap-2">
                                <a href="../../controllers/NotificacionController.php" class="btn btn-secondary">Cancelar</a>
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-send"></i> Enviar Notificación
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-body">
                        <h6><i class="bi bi-lightbulb"></i> Plantillas</h6>
                        <button class="btn btn-sm btn-outline-primary w-100 mb-2" onclick="usarPlantilla('recordatorio')">
                            Recordatorio de Aplicación
                        </button>
                        <button class="btn btn-sm btn-outline-primary w-100 mb-2" onclick="usarPlantilla('feria')">
                            Invitación a Feria
                        </button>
                        <button class="btn btn-sm btn-outline-primary w-100" onclick="usarPlantilla('cosecha')">
                            Próxima Cosecha
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('tipo').addEventListener('change', function() {
            const divCliente = document.getElementById('div-cliente');
            const selectCliente = document.getElementById('id_cliente');
            
            if (this.value === 'individual') {
                divCliente.style.display = 'block';
                selectCliente.required = true;
            } else {
                divCliente.style.display = 'none';
                selectCliente.required = false;
            }
        });
        
        function usarPlantilla(tipo) {
            const plantillas = {
                'recordatorio': {
                    asunto: 'Recordatorio de Aplicación',
                    mensaje: 'Estimado cliente, le recordamos que tiene programada una aplicación de productos. Por favor, tenga listo el lote.'
                },
                'feria': {
                    asunto: 'Invitación a Feria Agroquímica',
                    mensaje: '¡Lo invitamos a nuestra feria agroquímica! Descuentos especiales en productos seleccionados.'
                },
                'cosecha': {
                    asunto: 'Próxima Cosecha',
                    mensaje: 'Su cultivo está próximo a cosecharse. Estaremos en contacto para coordinar.'
                }
            };
            
            if (plantillas[tipo]) {
                document.querySelector('[name="asunto"]').value = plantillas[tipo].asunto;
                document.querySelector('[name="mensaje"]').value = plantillas[tipo].mensaje;
            }
        }
    </script>
</body>
</html>