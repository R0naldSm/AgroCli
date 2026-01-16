<?php
// controllers/LoteController.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

checkAuth();

class LoteController {
    private $db;
    private $id_empresa;
    private $id_usuario;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->id_empresa = $_SESSION['id_empresa'];
        $this->id_usuario = $_SESSION['usuario_id'];
    }
    
    // Listar lotes
    public function index() {
        try {
            $query = "SELECT l.*, 
                      CONCAT(c.nombre, ' ', c.apellido) as cliente_nombre,
                      c.cedula as cliente_cedula,
                      (SELECT COUNT(*) FROM etapas_cultivo ec 
                       WHERE ec.id_lote = l.id AND ec.deleted_at IS NULL) as total_etapas
                      FROM lotes l
                      INNER JOIN clientes c ON l.id_cliente = c.id
                      WHERE l.id_empresa = :id_empresa 
                      AND l.deleted_at IS NULL
                      ORDER BY l.created_at DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id_empresa', $this->id_empresa);
            $stmt->execute();
            $lotes = $stmt->fetchAll();
            
            include __DIR__ . '/../ingeniero/lotes/index.php';
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error al cargar lotes: " . $e->getMessage();
            include __DIR__ . '/../ingeniero/lotes/index.php';
        }
    }
    
    // Mostrar formulario crear
    public function create() {
        try {
            // Obtener clientes para el select
            $query = "SELECT id, CONCAT(nombre, ' ', apellido) as nombre_completo, cedula
                      FROM clientes 
                      WHERE id_empresa = :id_empresa 
                      AND deleted_at IS NULL
                      ORDER BY nombre ASC";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id_empresa', $this->id_empresa);
            $stmt->execute();
            $clientes = $stmt->fetchAll();
            
            include __DIR__ . '/../ingeniero/lotes/crear.php';
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
            header('Location: LoteController.php');
        }
    }
    
    // Guardar lote usando procedimiento almacenado
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                $query = "CALL sp_registrar_lote(
                    :id_empresa,
                    :id_cliente,
                    :nombre,
                    :ubicacion,
                    :tamanio_paradas,
                    :temporada,
                    :id_usuario
                )";
                
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':id_empresa', $this->id_empresa);
                $stmt->bindParam(':id_cliente', $_POST['id_cliente']);
                $stmt->bindParam(':nombre', sanitize($_POST['nombre']));
                $stmt->bindParam(':ubicacion', sanitize($_POST['ubicacion']));
                $stmt->bindParam(':tamanio_paradas', $_POST['tamanio_paradas']);
                $stmt->bindParam(':temporada', $_POST['temporada']);
                $stmt->bindParam(':id_usuario', $this->id_usuario);
                $stmt->execute();
                
                $resultado = $stmt->fetch();
                
                if ($resultado['success']) {
                    $_SESSION['success'] = "Lote registrado exitosamente";
                    header('Location: LoteController.php');
                } else {
                    $_SESSION['error'] = $resultado['mensaje'];
                    header('Location: LoteController.php?action=create');
                }
            } catch (PDOException $e) {
                $_SESSION['error'] = "Error: " . $e->getMessage();
                header('Location: LoteController.php?action=create');
            }
        }
    }
    
    // Ver detalles del lote
    public function view($id) {
        try {
            // Obtener datos del lote
            $query = "SELECT l.*, 
                      CONCAT(c.nombre, ' ', c.apellido) as cliente_nombre,
                      c.cedula, c.telefono, c.email
                      FROM lotes l
                      INNER JOIN clientes c ON l.id_cliente = c.id
                      WHERE l.id = :id 
                      AND l.id_empresa = :id_empresa 
                      AND l.deleted_at IS NULL";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':id_empresa', $this->id_empresa);
            $stmt->execute();
            $lote = $stmt->fetch();
            
            if (!$lote) {
                $_SESSION['error'] = "Lote no encontrado";
                header('Location: LoteController.php');
                exit();
            }
            
            // Obtener etapas del lote
            $query = "SELECT * FROM etapas_cultivo 
                      WHERE id_lote = :id_lote 
                      AND id_empresa = :id_empresa
                      AND deleted_at IS NULL
                      ORDER BY fecha_inicio DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id_lote', $id);
            $stmt->bindParam(':id_empresa', $this->id_empresa);
            $stmt->execute();
            $etapas = $stmt->fetchAll();
            
            include __DIR__ . '/../ingeniero/lotes/ver.php';
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
            header('Location: LoteController.php');
        }
    }
    
    // Editar lote
    public function edit($id) {
        try {
            // Obtener lote
            $query = "SELECT * FROM lotes 
                      WHERE id = :id 
                      AND id_empresa = :id_empresa 
                      AND deleted_at IS NULL";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':id_empresa', $this->id_empresa);
            $stmt->execute();
            $lote = $stmt->fetch();
            
            if (!$lote) {
                $_SESSION['error'] = "Lote no encontrado";
                header('Location: LoteController.php');
                exit();
            }
            
            // Obtener clientes
            $query = "SELECT id, CONCAT(nombre, ' ', apellido) as nombre_completo
                      FROM clientes 
                      WHERE id_empresa = :id_empresa 
                      AND deleted_at IS NULL
                      ORDER BY nombre ASC";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id_empresa', $this->id_empresa);
            $stmt->execute();
            $clientes = $stmt->fetchAll();
            
            include __DIR__ . '/../ingeniero/lotes/editar.php';
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
            header('Location: LoteController.php');
        }
    }
    
    // Actualizar lote
    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                // Calcular conversiones
                $tamanio_paradas = floatval($_POST['tamanio_paradas']);
                $tamanio_cuadras = $tamanio_paradas / 16;
                $tamanio_hectareas = $tamanio_paradas / 21;
                
                $query = "UPDATE lotes SET
                          id_cliente = :id_cliente,
                          nombre = :nombre,
                          ubicacion = :ubicacion,
                          tamanio_paradas = :tamanio_paradas,
                          tamanio_cuadras = :tamanio_cuadras,
                          tamanio_hectareas = :tamanio_hectareas,
                          temporada = :temporada,
                          updated_at = NOW()
                          WHERE id = :id 
                          AND id_empresa = :id_empresa";
                
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':id', $id);
                $stmt->bindParam(':id_empresa', $this->id_empresa);
                $stmt->bindParam(':id_cliente', $_POST['id_cliente']);
                $stmt->bindParam(':nombre', sanitize($_POST['nombre']));
                $stmt->bindParam(':ubicacion', sanitize($_POST['ubicacion']));
                $stmt->bindParam(':tamanio_paradas', $tamanio_paradas);
                $stmt->bindParam(':tamanio_cuadras', $tamanio_cuadras);
                $stmt->bindParam(':tamanio_hectareas', $tamanio_hectareas);
                $stmt->bindParam(':temporada', $_POST['temporada']);
                $stmt->execute();
                
                // Registrar auditoría
                $query = "INSERT INTO auditoria (id_usuario, id_empresa, accion, tabla_afectada, id_registro)
                          VALUES (:id_usuario, :id_empresa, 'UPDATE', 'lotes', :id_registro)";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':id_usuario', $this->id_usuario);
                $stmt->bindParam(':id_empresa', $this->id_empresa);
                $stmt->bindParam(':id_registro', $id);
                $stmt->execute();
                
                $_SESSION['success'] = "Lote actualizado exitosamente";
                header('Location: LoteController.php');
            } catch (PDOException $e) {
                $_SESSION['error'] = "Error: " . $e->getMessage();
                header("Location: LoteController.php?action=edit&id={$id}");
            }
        }
    }
    
    // Eliminar lote (lógico)
    public function delete($id) {
        try {
            $query = "UPDATE lotes SET deleted_at = NOW() 
                      WHERE id = :id AND id_empresa = :id_empresa";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':id_empresa', $this->id_empresa);
            $stmt->execute();
            
            $_SESSION['success'] = "Lote eliminado exitosamente";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error al eliminar: " . $e->getMessage();
        }
        
        header('Location: LoteController.php');
    }
    
    // Obtener lotes por cliente (AJAX)
    public function porCliente() {
        if (isset($_GET['id_cliente'])) {
            try {
                $query = "SELECT id, nombre, tamanio_hectareas, temporada
                          FROM lotes 
                          WHERE id_cliente = :id_cliente 
                          AND id_empresa = :id_empresa 
                          AND deleted_at IS NULL
                          ORDER BY nombre ASC";
                
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':id_cliente', $_GET['id_cliente']);
                $stmt->bindParam(':id_empresa', $this->id_empresa);
                $stmt->execute();
                $lotes = $stmt->fetchAll();
                
                header('Content-Type: application/json');
                echo json_encode($lotes);
            } catch (PDOException $e) {
                echo json_encode(['error' => $e->getMessage()]);
            }
        }
    }
}

// Enrutamiento
$controller = new LoteController();
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
    case 'view':
        $controller->view($id);
        break;
    case 'edit':
        $controller->edit($id);
        break;
    case 'update':
        $controller->update($id);
        break;
    case 'delete':
        $controller->delete($id);
        break;
    case 'por_cliente':
        $controller->porCliente();
        break;
    default:
        $controller->index();
}
?>