<?php
// controllers/ClienteController.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

checkAuth();

class ClienteController {
    private $db;
    private $id_empresa;
    private $id_usuario;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->id_empresa = $_SESSION['id_empresa'];
        $this->id_usuario = $_SESSION['usuario_id'];
    }
    
    // Listar todos los clientes
    public function index() {
        try {
            $query = "SELECT * FROM clientes 
                      WHERE id_empresa = :id_empresa 
                      AND deleted_at IS NULL
                      ORDER BY nombre ASC";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id_empresa', $this->id_empresa);
            $stmt->execute();
            $clientes = $stmt->fetchAll();
            
            include __DIR__ . '/../ingeniero/clientes/index.php';
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error al cargar clientes: " . $e->getMessage();
            include __DIR__ . '/../ingeniero/clientes/index.php';
        }
    }
    
    // Mostrar formulario de creación
    public function create() {
        include __DIR__ . '/../ingeniero/clientes/crear.php';
    }
    
    // Guardar nuevo cliente usando procedimiento almacenado
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                // Validar cédula
                $cedula = sanitize($_POST['cedula']);
                
                if (!validarCedulaEcuatoriana($cedula)) {
                    $_SESSION['error'] = "Cédula ecuatoriana inválida";
                    header('Location: ClienteController.php?action=create');
                    exit();
                }
                
                $query = "CALL sp_registrar_cliente(
                    :id_empresa,
                    :cedula,
                    :nombre,
                    :apellido,
                    :telefono,
                    :email,
                    :direccion,
                    :id_usuario
                )";
                
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':id_empresa', $this->id_empresa);
                $stmt->bindParam(':cedula', $cedula);
                $stmt->bindParam(':nombre', sanitize($_POST['nombre']));
                $stmt->bindParam(':apellido', sanitize($_POST['apellido']));
                $stmt->bindParam(':telefono', sanitize($_POST['telefono']));
                $stmt->bindParam(':email', sanitize($_POST['email']));
                $stmt->bindParam(':direccion', sanitize($_POST['direccion']));
                $stmt->bindParam(':id_usuario', $this->id_usuario);
                $stmt->execute();
                
                $resultado = $stmt->fetch();
                
                if ($resultado['success']) {
                    $_SESSION['success'] = "Cliente registrado exitosamente";
                    header('Location: ClienteController.php');
                } else {
                    $_SESSION['error'] = $resultado['mensaje'];
                    header('Location: ClienteController.php?action=create');
                }
            } catch (PDOException $e) {
                $_SESSION['error'] = "Error: " . $e->getMessage();
                header('Location: ClienteController.php?action=create');
            }
        }
    }
    
    // Mostrar formulario de edición
    public function edit($id) {
        try {
            $query = "SELECT * FROM clientes 
                      WHERE id = :id 
                      AND id_empresa = :id_empresa 
                      AND deleted_at IS NULL";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':id_empresa', $this->id_empresa);
            $stmt->execute();
            $cliente = $stmt->fetch();
            
            if (!$cliente) {
                $_SESSION['error'] = "Cliente no encontrado";
                header('Location: ClienteController.php');
                exit();
            }
            
            include __DIR__ . '/../ingeniero/clientes/editar.php';
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
            header('Location: ClienteController.php');
        }
    }
    
    // Actualizar cliente
    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                $cedula = sanitize($_POST['cedula']);
                
                if (!validarCedulaEcuatoriana($cedula)) {
                    $_SESSION['error'] = "Cédula ecuatoriana inválida";
                    header("Location: ClienteController.php?action=edit&id={$id}");
                    exit();
                }
                
                $query = "UPDATE clientes SET
                          cedula = :cedula,
                          nombre = :nombre,
                          apellido = :apellido,
                          telefono = :telefono,
                          email = :email,
                          direccion = :direccion,
                          updated_at = NOW()
                          WHERE id = :id AND id_empresa = :id_empresa";
                
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':id', $id);
                $stmt->bindParam(':id_empresa', $this->id_empresa);
                $stmt->bindParam(':cedula', $cedula);
                $stmt->bindParam(':nombre', sanitize($_POST['nombre']));
                $stmt->bindParam(':apellido', sanitize($_POST['apellido']));
                $stmt->bindParam(':telefono', sanitize($_POST['telefono']));
                $stmt->bindParam(':email', sanitize($_POST['email']));
                $stmt->bindParam(':direccion', sanitize($_POST['direccion']));
                $stmt->execute();
                
                // Registrar auditoría
                $query = "INSERT INTO auditoria (id_usuario, id_empresa, accion, tabla_afectada, id_registro)
                          VALUES (:id_usuario, :id_empresa, 'UPDATE', 'clientes', :id_registro)";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':id_usuario', $this->id_usuario);
                $stmt->bindParam(':id_empresa', $this->id_empresa);
                $stmt->bindParam(':id_registro', $id);
                $stmt->execute();
                
                $_SESSION['success'] = "Cliente actualizado exitosamente";
                header('Location: ClienteController.php');
            } catch (PDOException $e) {
                $_SESSION['error'] = "Error: " . $e->getMessage();
                header("Location: ClienteController.php?action=edit&id={$id}");
            }
        }
    }
    
    // Ver detalles del cliente
    public function view($id) {
        try {
            // Obtener datos del cliente
            $query = "SELECT * FROM clientes 
                      WHERE id = :id 
                      AND id_empresa = :id_empresa 
                      AND deleted_at IS NULL";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':id_empresa', $this->id_empresa);
            $stmt->execute();
            $cliente = $stmt->fetch();
            
            if (!$cliente) {
                $_SESSION['error'] = "Cliente no encontrado";
                header('Location: ClienteController.php');
                exit();
            }
            
            // Obtener lotes del cliente
            $query = "SELECT l.*, 
                      (SELECT COUNT(*) FROM etapas_cultivo ec 
                       WHERE ec.id_lote = l.id AND ec.deleted_at IS NULL) as total_etapas
                      FROM lotes l
                      WHERE l.id_cliente = :id_cliente 
                      AND l.id_empresa = :id_empresa 
                      AND l.deleted_at IS NULL
                      ORDER BY l.id DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id_cliente', $id);
            $stmt->bindParam(':id_empresa', $this->id_empresa);
            $stmt->execute();
            $lotes = $stmt->fetchAll();
            
            // Obtener historial de producción
            $query = "SELECT 
                        l.nombre as lote_nombre,
                        ec.tipo_cultivo,
                        ec.fecha_inicio,
                        ec.fecha_fin_real,
                        ec.produccion_quintales,
                        l.temporada,
                        l.tamanio_hectareas
                      FROM etapas_cultivo ec
                      INNER JOIN lotes l ON ec.id_lote = l.id
                      WHERE l.id_cliente = :id_cliente
                      AND l.id_empresa = :id_empresa
                      AND ec.estado = 'finalizada'
                      AND ec.deleted_at IS NULL
                      ORDER BY ec.fecha_fin_real DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id_cliente', $id);
            $stmt->bindParam(':id_empresa', $this->id_empresa);
            $stmt->execute();
            $historial = $stmt->fetchAll();
            
            include __DIR__ . '/../ingeniero/clientes/ver.php';
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
            header('Location: ClienteController.php');
        }
    }
    
    // Eliminar cliente (lógico)
    public function delete($id) {
        try {
            $query = "UPDATE clientes SET deleted_at = NOW() 
                      WHERE id = :id AND id_empresa = :id_empresa";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':id_empresa', $this->id_empresa);
            $stmt->execute();
            
            $_SESSION['success'] = "Cliente eliminado exitosamente";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
        }
        
        header('Location: ClienteController.php');
    }
    
    // Buscar clientes por cédula (AJAX)
    public function buscarPorCedula() {
        if (isset($_GET['cedula'])) {
            try {
                $cedula = sanitize($_GET['cedula']);
                
                $query = "SELECT * FROM clientes 
                          WHERE cedula LIKE :cedula 
                          AND id_empresa = :id_empresa 
                          AND deleted_at IS NULL
                          LIMIT 10";
                
                $stmt = $this->db->prepare($query);
                $search_cedula = $cedula . "%";
                $stmt->bindParam(':cedula', $search_cedula);
                $stmt->bindParam(':id_empresa', $this->id_empresa);
                $stmt->execute();
                $resultados = $stmt->fetchAll();
                
                header('Content-Type: application/json');
                echo json_encode($resultados);
            } catch (PDOException $e) {
                echo json_encode(['error' => $e->getMessage()]);
            }
        }
    }
    
    // Búsqueda avanzada (AJAX)
    public function buscar() {
        if (isset($_GET['q'])) {
            try {
                $termino = sanitize($_GET['q']);
                $search = "%{$termino}%";
                
                $query = "SELECT * FROM clientes 
                          WHERE (nombre LIKE :termino 
                          OR apellido LIKE :termino 
                          OR cedula LIKE :termino
                          OR CONCAT(nombre, ' ', apellido) LIKE :termino)
                          AND id_empresa = :id_empresa 
                          AND deleted_at IS NULL
                          LIMIT 20";
                
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':termino', $search);
                $stmt->bindParam(':id_empresa', $this->id_empresa);
                $stmt->execute();
                $resultados = $stmt->fetchAll();
                
                header('Content-Type: application/json');
                echo json_encode($resultados);
            } catch (PDOException $e) {
                echo json_encode(['error' => $e->getMessage()]);
            }
        }
    }
}

// Enrutamiento
$controller = new ClienteController();
$action = $_GET['action'] ?? 'index';
$id = isset($_GET['id']) ? intval($_GET['id']) : null;

switch ($action) {
    case 'index':
        $controller->index();
        break;
    case 'create':
        $controller->create();
        break;
    case 'store':
        $controller->store();
        break;
    case 'edit':
        $controller->edit($id);
        break;
    case 'update':
        $controller->update($id);
        break;
    case 'view':
        $controller->view($id);
        break;
    case 'delete':
        $controller->delete($id);
        break;
    case 'buscar_cedula':
        $controller->buscarPorCedula();
        break;
    case 'buscar':
        $controller->buscar();
        break;
    default:
        $controller->index();
}
?>