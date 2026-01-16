<?php
// ingeniero/clientes/index.php
require_once '../../config/config.php';
checkAuth();
// La variable $clientes viene del controlador
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes - AgriManage</title>
    
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
            <a href="../dashboard.php" class="btn btn-sm btn-light">
                <i class="bi bi-house"></i> Dashboard
            </a>
        </div>
    </nav>
    
    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2><i class="bi bi-people-fill"></i> Clientes</h2>
            <a href="../../controllers/ClienteController.php?action=create" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Nuevo Cliente
            </a>
        </div>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <div class="row mb-3">
            <div class="col-md-6">
                <input type="text" class="form-control" id="busqueda" placeholder="Buscar por nombre, apellido o cédula...">
            </div>
            <div class="col-md-6 text-end">
                <span class="badge bg-secondary">Total: <?php echo count($clientes ?? []); ?> clientes</span>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="tabla-clientes">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Cédula</th>
                                <th>Nombre Completo</th>
                                <th>Teléfono</th>
                                <th>Email</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($clientes)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">No hay clientes registrados</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($clientes as $cliente): ?>
                                    <tr>
                                        <td><?php echo $cliente['id']; ?></td>
                                        <td><?php echo $cliente['cedula']; ?></td>
                                        <td><strong><?php echo $cliente['nombre'] . ' ' . $cliente['apellido']; ?></strong></td>
                                        <td><?php echo $cliente['telefono'] ?: '-'; ?></td>
                                        <td><?php echo $cliente['email'] ?: '-'; ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="../../controllers/ClienteController.php?action=view&id=<?php echo $cliente['id']; ?>" 
                                                   class="btn btn-info" title="Ver">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="../../controllers/ClienteController.php?action=edit&id=<?php echo $cliente['id']; ?>" 
                                                   class="btn btn-warning" title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <button class="btn btn-danger btn-eliminar" 
                                                        data-id="<?php echo $cliente['id']; ?>"
                                                        data-nombre="<?php echo $cliente['nombre'] . ' ' . $cliente['apellido']; ?>"
                                                        title="Eliminar">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal Eliminar -->
    <div class="modal fade" id="modalEliminar" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Confirmar Eliminación</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>¿Está seguro de eliminar a <strong id="nombre-cliente"></strong>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <a href="#" id="btn-confirmar" class="btn btn-danger">Eliminar</a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Búsqueda
        document.getElementById('busqueda').addEventListener('input', function(e) {
            const termino = e.target.value.toLowerCase();
            const filas = document.querySelectorAll('#tabla-clientes tbody tr');
            
            filas.forEach(fila => {
                const texto = fila.textContent.toLowerCase();
                fila.style.display = texto.includes(termino) ? '' : 'none';
            });
        });
        
        // Modal eliminar
        const modal = new bootstrap.Modal(document.getElementById('modalEliminar'));
        document.querySelectorAll('.btn-eliminar').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('nombre-cliente').textContent = this.dataset.nombre;
                document.getElementById('btn-confirmar').href = 
                    '../../controllers/ClienteController.php?action=delete&id=' + this.dataset.id;
                modal.show();
            });
        });
    </script>
</body>
</html>