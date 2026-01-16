<?php
// controllers/EtapaController.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

checkAuth();

class EtapaController {
    private $db;
    private $id_empresa;
    private $id_usuario;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->id_empresa = $_SESSION['id_empresa'];
        $this->id_usuario = $_SESSION['usuario_id'];
    }
    
    // Listar etapas
    public function index() {
        try {
            $query = "SELECT ec.*, 
                      l.nombre as lote_nombre,
                      CONCAT(c.nombre, ' ', c.apellido) as cliente_nombre,
                      (SELECT COUNT(*) FROM aplicaciones a 
                       WHERE a.id_etapa_cultivo = ec.id AND a.deleted_at IS NULL) as total_aplicaciones
                      FROM etapas_cultivo ec
                      INNER JOIN lotes l ON ec.id_lote = l.id
                      INNER JOIN clientes c ON l.id_cliente = c.id
                      WHERE ec.id_empresa = :id_empresa 
                      AND ec.deleted_at IS NULL
                      ORDER BY ec.fecha_inicio DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id_empresa', $this->id_empresa);
            $stmt->execute();
            $etapas = $stmt->fetchAll();
            
            include __DIR__ . '/../ingeniero/etapas/index.php';
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
            include __DIR__ . '/../ingeniero/etapas/index.php';
        }
    }
    
    // Formulario crear etapa
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
            
            include __DIR__ . '/../ingeniero/etapas/crear.php';
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
            header('Location: EtapaController.php');
        }
    }
    
    // Guardar etapa usando procedimiento almacenado
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                $query = "CALL sp_registrar_etapa(
                    :id_empresa,
                    :id_lote,
                    :tipo_cultivo,
                    :fecha_inicio,
                    :id_usuario
                )";
                
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':id_empresa', $this->id_empresa);
                $stmt->bindParam(':id_lote', $_POST['id_lote']);
                $stmt->bindParam(':tipo_cultivo', $_POST['tipo_cultivo']);
                $stmt->bindParam(':fecha_inicio', $_POST['fecha_inicio']);
                $stmt->bindParam(':id_usuario', $this->id_usuario);
                $stmt->execute();
                
                $resultado = $stmt->fetch();
                
                if ($resultado['success']) {
                    $_SESSION['success'] = "Etapa de cultivo registrada exitosamente. Fecha estimada de cosecha: " . formatearFecha($resultado['fecha_estimada']);
                    header('Location: EtapaController.php');
                } else {
                    $_SESSION['error'] = $resultado['mensaje'];
                    header('Location: EtapaController.php?action=create');
                }
            } catch (PDOException $e) {
                $_SESSION['error'] = "Error: " . $e->getMessage();
                header('Location: EtapaController.php?action=create');
            }
        }
    }
    
    // Ver detalles de la etapa
    public function view($id) {
        try {
            // Obtener datos de la etapa
            $query = "SELECT ec.*, 
                      l.nombre as lote_nombre,
                      l.tamanio_hectareas,
                      l.temporada,
                      CONCAT(c.nombre, ' ', c.apellido) as cliente_nombre,
                      c.cedula, c.telefono
                      FROM etapas_cultivo ec
                      INNER JOIN lotes l ON ec.id_lote = l.id
                      INNER JOIN clientes c ON l.id_cliente = c.id
                      WHERE ec.id = :id 
                      AND ec.id_empresa = :id_empresa 
                      AND ec.deleted_at IS NULL";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':id_empresa', $this->id_empresa);
            $stmt->execute();
            $etapa = $stmt->fetch();
            
            if (!$etapa) {
                $_SESSION['error'] = "Etapa no encontrada";
                header('Location: EtapaController.php');
                exit();
            }
            
            // Obtener aplicaciones usando procedimiento almacenado
            $stmt = $this->db->prepare("CALL sp_historial_aplicaciones(:id_etapa, :id_empresa)");
            $stmt->bindParam(':id_etapa', $id);
            $stmt->bindParam(':id_empresa', $this->id_empresa);
            $stmt->execute();
            $aplicaciones = $stmt->fetchAll();
            
            include __DIR__ . '/../ingeniero/etapas/ver.php';
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
            header('Location: EtapaController.php');
        }
    }
    
    // Formulario finalizar etapa
    public function finalizar($id) {
        try {
            $query = "SELECT ec.*, 
                      l.nombre as lote_nombre,
                      CONCAT(c.nombre, ' ', c.apellido) as cliente_nombre
                      FROM etapas_cultivo ec
                      INNER JOIN lotes l ON ec.id_lote = l.id
                      INNER JOIN clientes c ON l.id_cliente = c.id
                      WHERE ec.id = :id 
                      AND ec.id_empresa = :id_empresa 
                      AND ec.estado = 'en_proceso'
                      AND ec.deleted_at IS NULL";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':id_empresa', $this->id_empresa);
            $stmt->execute();
            $etapa = $stmt->fetch();
            
            if (!$etapa) {
                $_SESSION['error'] = "Etapa no encontrada o ya finalizada";
                header('Location: EtapaController.php');
                exit();
            }
            
            include __DIR__ . '/../ingeniero/etapas/finalizar.php';
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
            header('Location: EtapaController.php');
        }
    }
    
    // Procesar finalización de etapa
    public function procesarFinalizacion($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                $query = "CALL sp_finalizar_etapa(
                    :id_etapa,
                    :produccion_quintales,
                    :fecha_fin,
                    :observaciones,
                    :id_usuario,
                    :id_empresa
                )";
                
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':id_etapa', $id);
                $stmt->bindParam(':produccion_quintales', $_POST['produccion_quintales']);
                $stmt->bindParam(':fecha_fin', $_POST['fecha_fin_real']);
                $stmt->bindParam(':observaciones', $_POST['observaciones']);
                $stmt->bindParam(':id_usuario', $this->id_usuario);
                $stmt->bindParam(':id_empresa', $this->id_empresa);
                $stmt->execute();
                
                $resultado = $stmt->fetch();
                
                if ($resultado['success']) {
                    $_SESSION['success'] = "Etapa finalizada exitosamente. Producción: " . $_POST['produccion_quintales'] . " quintales";
                    header('Location: EtapaController.php');
                } else {
                    $_SESSION['error'] = $resultado['mensaje'];
                    header("Location: EtapaController.php?action=finalizar&id={$id}");
                }
            } catch (PDOException $e) {
                $_SESSION['error'] = "Error: " . $e->getMessage();
                header("Location: EtapaController.php?action=finalizar&id={$id}");
            }
        }
    }
    
    // Eliminar etapa (lógico)
    public function delete($id) {
        try {
            // Verificar que no tenga aplicaciones
            $query = "SELECT COUNT(*) as total FROM aplicaciones 
                      WHERE id_etapa_cultivo = :id 
                      AND id_empresa = :id_empresa 
                      AND deleted_at IS NULL";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':id_empresa', $this->id_empresa);
            $stmt->execute();
            $result = $stmt->fetch();
            
            if ($result['total'] > 0) {
                $_SESSION['error'] = "No se puede eliminar. La etapa tiene aplicaciones registradas.";
            } else {
                $query = "UPDATE etapas_cultivo SET deleted_at = NOW() 
                          WHERE id = :id AND id_empresa = :id_empresa";
                
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':id', $id);
                $stmt->bindParam(':id_empresa', $this->id_empresa);
                $stmt->execute();
                
                $_SESSION['success'] = "Etapa eliminada exitosamente";
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
        }
        
        header('Location: EtapaController.php');
    }
    
    // Obtener etapas por lote (AJAX)
    public function porLote() {
        if (isset($_GET['id_lote'])) {
            try {
                $query = "SELECT id, tipo_cultivo, fecha_inicio, fecha_fin_estimada, estado
                          FROM etapas_cultivo 
                          WHERE id_lote = :id_lote 
                          AND id_empresa = :id_empresa 
                          AND deleted_at IS NULL
                          ORDER BY fecha_inicio DESC";
                
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':id_lote', $_GET['id_lote']);
                $stmt->bindParam(':id_empresa', $this->id_empresa);
                $stmt->execute();
                $etapas = $stmt->fetchAll();
                
                header('Content-Type: application/json');
                echo json_encode($etapas);
            } catch (PDOException $e) {
                echo json_encode(['error' => $e->getMessage()]);
            }
        }
    }
}

// Enrutamiento
$controller = new EtapaController();
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
    case 'finalizar':
        $controller->finalizar($id);
        break;
    case 'procesar_finalizacion':
        $controller->procesarFinalizacion($id);
        break;
    case 'delete':
        $controller->delete($id);
        break;
    case 'por_lote':
        $controller->porLote();
        break;
    default:
        $controller->index();
}
?>