<?php
require_once '../../config/config.php';
checkAuth();
// La variable $cliente viene del controlador
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Cliente - AgriManage</title>
    
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
            <a href="../../controllers/ClienteController.php" class="btn btn-sm btn-light">
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
                            <i class="bi bi-pencil-square"></i> Editar Cliente
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger">
                                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <form action="../../controllers/ClienteController.php?action=update&id=<?php echo $cliente['id']; ?>" 
                              method="POST" id="form-cliente">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Cédula <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="cedula" id="cedula" 
                                           maxlength="10" pattern="[0-9]{10}" 
                                           value="<?php echo htmlspecialchars($cliente['cedula']); ?>" required>
                                    <div class="invalid-feedback" id="cedula-error"></div>
                                    <div class="valid-feedback" id="cedula-ok"></div>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nombre <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="nombre" 
                                           value="<?php echo htmlspecialchars($cliente['nombre']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Apellido <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="apellido" 
                                           value="<?php echo htmlspecialchars($cliente['apellido']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Teléfono</label>
                                    <input type="tel" class="form-control" name="telefono" 
                                           pattern="[0-9]{9,10}"
                                           value="<?php echo htmlspecialchars($cliente['telefono']); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email"
                                           value="<?php echo htmlspecialchars($cliente['email']); ?>">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Dirección</label>
                                <textarea class="form-control" name="direccion" rows="3"><?php echo htmlspecialchars($cliente['direccion']); ?></textarea>
                            </div>
                            
                            <div class="d-flex justify-content-end gap-2">
                                <a href="../../controllers/ClienteController.php" class="btn btn-secondary">
                                    Cancelar
                                </a>
                                <button type="submit" class="btn btn-warning" id="btn-submit">
                                    <i class="bi bi-save"></i> Actualizar
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
                        <p class="small mb-2"><strong>Creado:</strong> <?php echo formatearFecha($cliente['created_at']); ?></p>
                        <p class="small mb-0"><strong>Última actualización:</strong> <?php echo formatearFecha($cliente['updated_at']); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../public/js/validacion.js"></script>
    <script>
        // Validación de cédula en tiempo real
        document.getElementById('cedula').addEventListener('input', function(e) {
            const cedula = e.target.value;
            const input = e.target;
            
            input.classList.remove('is-valid', 'is-invalid');
            
            if (cedula.length === 10) {
                if (validarCedulaEcuatoriana(cedula)) {
                    input.classList.add('is-valid');
                    document.getElementById('cedula-ok').textContent = 'Cédula válida';
                    document.getElementById('btn-submit').disabled = false;
                } else {
                    input.classList.add('is-invalid');
                    document.getElementById('cedula-error').textContent = 'Cédula inválida';
                    document.getElementById('btn-submit').disabled = true;
                }
            }
        });
        
        // Solo números en cédula
        document.getElementById('cedula').addEventListener('keypress', function(e) {
            if (!/[0-9]/.test(e.key)) e.preventDefault();
        });
        
        // Validar al cargar
        document.getElementById('cedula').dispatchEvent(new Event('input'));
    </script>
</body>
</html>