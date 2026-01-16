<?php
// controllers/AplicacionController.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

checkAuth();

class AplicacionController {
    private $db;
    private $id_empresa;
    private $id_usuario;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->id_empresa = $_SESSION['id_empresa'];
        $this->id_usuario = $_SESSION['usuario_id'];
    }
    
    // Listar aplicaciones
    public function index() {
        try {
            $query = "SELECT a.*, 
                      p.nombre as producto_nombre,
                      p.tipo as producto_tipo,
                      ec.tipo_cultivo,
                      l.nombre as lote_nombre,
                      CONCAT(c.nombre, ' ', c.apellido) as cliente_nombre
                      FROM aplicaciones a
                      INNER JOIN productos p ON a.id_producto = p.id
                      INNER JOIN etapas_cultivo ec ON a.id_etapa_cultivo = ec.id
                      INNER JOIN lotes l ON ec.id_lote = l.id
                      INNER JOIN clientes c ON l.id_cliente = c.id
                      WHERE a.id_empresa = :id_empresa 
                      AND a.deleted_at IS NULL
                      ORDER BY a.fecha_aplicacion DESC
                      LIMIT 100";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id_empresa', $this->id_empresa);
            $stmt->execute();
            $aplicaciones = $stmt->fetchAll();
            
            include __DIR__ . '/../ingeniero/aplicaciones/index.php';
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
            include __DIR__ . '/../ingeniero/aplicaciones/index.php';
        }
    }
    
    // Formulario crear aplicación
    public function create() {
        try {
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
            
            // Obtener productos disponibles
            $query = "SELECT id, nombre, tipo, unidad_medida, stock
                      FROM productos 
                      WHERE id_empresa = :id_empresa 
                      AND activo = 1
                      AND deleted_at IS NULL
                      ORDER BY nombre ASC";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id_empresa', $this->id_empresa);
            $stmt->execute();
            $productos = $stmt->fetchAll();
            
            include __DIR__ . '/../ingeniero/aplicaciones/crear.php';
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
            header('Location: AplicacionController.php');
        }
    }
    
    // Guardar aplicación usando procedimiento almacenado
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                $this->db->beginTransaction();
                
                // Procesar múltiples productos si vienen en array
                if (isset($_POST['productos']) && is_array($_POST['productos'])) {
                    foreach ($_POST['productos'] as $producto) {
                        $query = "CALL sp_registrar_aplicacion(
                            :id_empresa,
                            :id_etapa_cultivo,
                            :id_producto,
                            :fecha_aplicacion,
                            :cantidad,
                            :dosis,
                            :metodo,
                            :observaciones,
                            :id_usuario
                        )";
                        
                        $stmt = $this->db->prepare($query);
                        $stmt->bindParam(':id_empresa', $this->id_empresa);
                        $stmt->bindParam(':id_etapa_cultivo', $_POST['id_etapa_cultivo']);
                        $stmt->bindParam(':id_producto', $producto['id_producto']);
                        $stmt->bindParam(':fecha_aplicacion', $producto['fecha_aplicacion']);
                        $stmt->bindParam(':cantidad', $producto['cantidad']);
                        $stmt->bindParam(':dosis', $producto['dosis']);
                        $stmt->bindParam(':metodo', $_POST['metodo_aplicacion']);
                        $stmt->bindParam(':observaciones', $_POST['observaciones']);
                        $stmt->bindParam(':id_usuario', $this->id_usuario);
                        $stmt->execute();
                        
                        $resultado = $stmt->fetch();
                        $stmt->closeCursor();
                        
                        if (!$resultado['success']) {
                            throw new Exception($resultado['mensaje']);
                        }
                    }
                } else {
                    // Una sola aplicación
                    $query = "CALL sp_registrar_aplicacion(
                        :id_empresa,
                        :id_etapa_cultivo,
                        :id_producto,
                        :fecha_aplicacion,
                        :cantidad,
                        :dosis,
                        :metodo,
                        :observaciones,
                        :id_usuario
                    )";
                    
                    $stmt = $this->db->prepare($query);
                    $stmt->bindParam(':id_empresa', $this->id_empresa);
                    $stmt->bindParam(':id_etapa_cultivo', $_POST['id_etapa_cultivo']);
                    $stmt->bindParam(':id_producto', $_POST['id_producto']);
                    $stmt->bindParam(':fecha_aplicacion', $_POST['fecha_aplicacion']);
                    $stmt->bindParam(':cantidad', $_POST['cantidad']);
                    $stmt->bindParam(':dosis', $_POST['dosis']);
                    $stmt->bindParam(':metodo', $_POST['metodo_aplicacion']);
                    $stmt->bindParam(':observaciones', $_POST['observaciones']);
                    $stmt->bindParam(':id_usuario', $this->id_usuario);
                    $stmt->execute();
                    
                    $resultado = $stmt->fetch();
                    
                    if (!$resultado['success']) {
                        throw new Exception($resultado['mensaje']);
                    }
                }
                
                $this->db->commit();
                $_SESSION['success'] = "Aplicación(es) registrada(s) exitosamente";
                header('Location: AplicacionController.php');
            } catch (Exception $e) {
                $this->db->rollBack();
                $_SESSION['error'] = "Error: " . $e->getMessage();
                header('Location: AplicacionController.php?action=create');
            }
        }
    }
    
    // Ver historial de aplicaciones por etapa
    public function porEtapa($id_etapa) {
        try {
            $stmt = $this->db->prepare("CALL sp_historial_aplicaciones(:id_etapa, :id_empresa)");
            $stmt->bindParam(':id_etapa', $id_etapa);
            $stmt->bindParam(':id_empresa', $this->id_empresa);
            $stmt->execute();
            $aplicaciones = $stmt->fetchAll();
            
            include __DIR__ . '/../ingeniero/aplicaciones/historial.php';
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
            header('Location: AplicacionController.php');
        }
    }
    
    // Reutilizar aplicaciones de etapa anterior
    public function reutilizar() {
        if (isset($_GET['id_etapa_origen'])) {
            try {
                $query = "SELECT a.*, p.nombre as producto_nombre, p.stock
                          FROM aplicaciones a
                          INNER JOIN productos p ON a.id_producto = p.id
                          WHERE a.id_etapa_cultivo = :id_etapa 
                          AND a.id_empresa = :id_empresa 
                          AND a.deleted_at IS NULL
                          AND p.activo = 1
                          ORDER BY a.fecha_aplicacion ASC";
                
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':id_etapa', $_GET['id_etapa_origen']);
                $stmt->bindParam(':id_empresa', $this->id_empresa);
                $stmt->execute();
                $aplicaciones_anteriores = $stmt->fetchAll();
                
                header('Content-Type: application/json');
                echo json_encode($aplicaciones_anteriores);
            } catch (PDOException $e) {
                echo json_encode(['error' => $e->getMessage()]);
            }
        }
    }
    
    // Eliminar aplicación (lógico)
    public function delete($id) {
        try {
            // Primero obtener datos para revertir stock
            $query = "SELECT id_producto, cantidad FROM aplicaciones 
                      WHERE id = :id AND id_empresa = :id_empresa";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':id_empresa', $this->id_empresa);
            $stmt->execute();
            $aplicacion = $stmt->fetch();
            
            if ($aplicacion) {
                // Revertir stock
                $query = "UPDATE productos SET stock = stock + :cantidad 
                          WHERE id = :id_producto";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':cantidad', $aplicacion['cantidad']);
                $stmt->bindParam(':id_producto', $aplicacion['id_producto']);
                $stmt->execute();
                
                // Eliminar aplicación (lógico)
                $query = "UPDATE aplicaciones SET deleted_at = NOW() 
                          WHERE id = :id AND id_empresa = :id_empresa";
                
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':id', $id);
                $stmt->bindParam(':id_empresa', $this->id_empresa);
                $stmt->execute();
                
                $_SESSION['success'] = "Aplicación eliminada y stock revertido";
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
        }
        
        header('Location: AplicacionController.php');
    }
    
    // Búsqueda de aplicaciones
    public function buscar() {
        if (isset($_GET['q'])) {
            try {
                $termino = "%" . $_GET['q'] . "%";
                
                $query = "SELECT a.*, 
                          p.nombre as producto_nombre,
                          CONCAT(c.nombre, ' ', c.apellido) as cliente_nombre
                          FROM aplicaciones a
                          INNER JOIN productos p ON a.id_producto = p.id
                          INNER JOIN etapas_cultivo ec ON a.id_etapa_cultivo = ec.id
                          INNER JOIN lotes l ON ec.id_lote = l.id
                          INNER JOIN clientes c ON l.id_cliente = c.id
                          WHERE a.id_empresa = :id_empresa 
                          AND a.deleted_at IS NULL
                          AND (p.nombre LIKE :termino OR c.nombre LIKE :termino OR c.apellido LIKE :termino)
                          ORDER BY a.fecha_aplicacion DESC
                          LIMIT 50";
                
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':id_empresa', $this->id_empresa);
                $stmt->bindParam(':termino', $termino);
                $stmt->execute();
                $aplicaciones = $stmt->fetchAll();
                
                header('Content-Type: application/json');
                echo json_encode($aplicaciones);
            } catch (PDOException $e) {
                echo json_encode(['error' => $e->getMessage()]);
            }
        }
    }
}

// Enrutamiento
$controller = new AplicacionController();
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
    case 'por_etapa':
        $id_etapa = $_GET['id_etapa'] ?? null;
        $controller->porEtapa($id_etapa);
        break;
    case 'reutilizar':
        $controller->reutilizar();
        break;
    case 'delete':
        $controller->delete($id);
        break;
    case 'buscar':
        $controller->buscar();
        break;
    default:
        $controller->index();
}
?>