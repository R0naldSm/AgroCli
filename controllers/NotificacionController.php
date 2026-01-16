<?php
// controllers/NotificacionController.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

checkAuth();

class NotificacionController {
    private $db;
    private $id_empresa;
    private $id_usuario;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->id_empresa = $_SESSION['id_empresa'];
        $this->id_usuario = $_SESSION['usuario_id'];
    }
    
    // Listar notificaciones enviadas
    public function index() {
        try {
            $query = "SELECT n.*,
                      CONCAT(c.nombre, ' ', c.apellido) as cliente_nombre,
                      u.nombre_completo as emisor_nombre
                      FROM notificaciones n
                      LEFT JOIN clientes c ON n.id_cliente = c.id
                      INNER JOIN usuarios u ON n.id_usuario_emisor = u.id
                      WHERE n.id_empresa = :id_empresa 
                      AND n.deleted_at IS NULL
                      ORDER BY n.fecha_envio DESC
                      LIMIT 100";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id_empresa', $this->id_empresa);
            $stmt->execute();
            $notificaciones = $stmt->fetchAll();
            
            include __DIR__ . '/../ingeniero/notificaciones/index.php';
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
            include __DIR__ . '/../ingeniero/notificaciones/index.php';
        }
    }
    
    // Formulario crear notificación
    public function create() {
        try {
            // Obtener clientes para notificaciones individuales
            $query = "SELECT id, CONCAT(nombre, ' ', apellido) as nombre_completo, email, telefono
                      FROM clientes 
                      WHERE id_empresa = :id_empresa 
                      AND deleted_at IS NULL
                      ORDER BY nombre ASC";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id_empresa', $this->id_empresa);
            $stmt->execute();
            $clientes = $stmt->fetchAll();
            
            include __DIR__ . '/../ingeniero/notificaciones/crear.php';
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
            header('Location: NotificacionController.php');
        }
    }
    
    // Guardar notificación
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                $tipo = $_POST['tipo']; // 'general' o 'individual'
                $asunto = sanitize($_POST['asunto']);
                $mensaje = sanitize($_POST['mensaje']);
                
                if ($tipo == 'general') {
                    // Enviar a todos los clientes
                    $query = "INSERT INTO notificaciones 
                              (id_empresa, id_usuario_emisor, id_cliente, tipo, asunto, mensaje)
                              SELECT :id_empresa, :id_usuario_emisor, id, 'general', :asunto, :mensaje
                              FROM clientes 
                              WHERE id_empresa = :id_empresa2 
                              AND deleted_at IS NULL";
                    
                    $stmt = $this->db->prepare($query);
                    $stmt->bindParam(':id_empresa', $this->id_empresa);
                    $stmt->bindParam(':id_empresa2', $this->id_empresa);
                    $stmt->bindParam(':id_usuario_emisor', $this->id_usuario);
                    $stmt->bindParam(':asunto', $asunto);
                    $stmt->bindParam(':mensaje', $mensaje);
                    $stmt->execute();
                    
                    $total_enviadas = $stmt->rowCount();
                    $_SESSION['success'] = "Notificación general enviada a {$total_enviadas} clientes";
                    
                } else {
                    // Enviar a cliente específico
                    $id_cliente = intval($_POST['id_cliente']);
                    
                    $query = "INSERT INTO notificaciones 
                              (id_empresa, id_usuario_emisor, id_cliente, tipo, asunto, mensaje)
                              VALUES 
                              (:id_empresa, :id_usuario_emisor, :id_cliente, 'individual', :asunto, :mensaje)";
                    
                    $stmt = $this->db->prepare($query);
                    $stmt->bindParam(':id_empresa', $this->id_empresa);
                    $stmt->bindParam(':id_usuario_emisor', $this->id_usuario);
                    $stmt->bindParam(':id_cliente', $id_cliente);
                    $stmt->bindParam(':asunto', $asunto);
                    $stmt->bindParam(':mensaje', $mensaje);
                    $stmt->execute();
                    
                    $_SESSION['success'] = "Notificación enviada exitosamente";
                }
                
                header('Location: NotificacionController.php');
            } catch (PDOException $e) {
                $_SESSION['error'] = "Error: " . $e->getMessage();
                header('Location: NotificacionController.php?action=create');
            }
        }
    }
    
    // Ver detalle de notificación
    public function view($id) {
        try {
            $query = "SELECT n.*,
                      CONCAT(c.nombre, ' ', c.apellido) as cliente_nombre,
                      c.email as cliente_email,
                      c.telefono as cliente_telefono,
                      u.nombre_completo as emisor_nombre
                      FROM notificaciones n
                      LEFT JOIN clientes c ON n.id_cliente = c.id
                      INNER JOIN usuarios u ON n.id_usuario_emisor = u.id
                      WHERE n.id = :id 
                      AND n.id_empresa = :id_empresa";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':id_empresa', $this->id_empresa);
            $stmt->execute();
            $notificacion = $stmt->fetch();
            
            if (!$notificacion) {
                $_SESSION['error'] = "Notificación no encontrada";
                header('Location: NotificacionController.php');
                exit();
            }
            
            include __DIR__ . '/../ingeniero/notificaciones/ver.php';
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
            header('Location: NotificacionController.php');
        }
    }
    
    // Eliminar notificación (lógico)
    public function delete($id) {
        try {
            $query = "UPDATE notificaciones SET deleted_at = NOW() 
                      WHERE id = :id AND id_empresa = :id_empresa";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':id_empresa', $this->id_empresa);
            $stmt->execute();
            
            $_SESSION['success'] = "Notificación eliminada exitosamente";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
        }
        
        header('Location: NotificacionController.php');
    }
    
    // Plantillas predefinidas de notificaciones (AJAX)
    public function plantillas() {
        $plantillas = [
            'recordatorio_aplicacion' => [
                'asunto' => 'Recordatorio de Aplicación',
                'mensaje' => 'Estimado cliente, le recordamos que tiene programada una aplicación de productos para el día [FECHA]. Por favor, tenga listo el lote para el trabajo.'
            ],
            'feria' => [
                'asunto' => 'Invitación a Feria Agroquímica',
                'mensaje' => '¡Lo invitamos a nuestra feria agroquímica! Descuentos especiales en productos seleccionados. Fecha: [FECHA]. ¡No falte!'
            ],
            'nueva_cosecha' => [
                'asunto' => 'Próxima Cosecha',
                'mensaje' => 'Su cultivo está próximo a cosecharse. Estimamos que estará listo aproximadamente el [FECHA]. Estaremos en contacto.'
            ],
            'producto_disponible' => [
                'asunto' => 'Nuevo Producto Disponible',
                'mensaje' => 'Tenemos disponible un nuevo producto que puede ser de su interés: [PRODUCTO]. Contáctenos para más información.'
            ],
            'seguimiento' => [
                'asunto' => 'Seguimiento de Cultivo',
                'mensaje' => 'Queremos hacer un seguimiento del estado de su cultivo. ¿Cuándo podemos visitarle para una inspección?'
            ]
        ];
        
        header('Content-Type: application/json');
        echo json_encode($plantillas);
    }
    
    // Notificaciones por cliente (historial)
    public function porCliente($id_cliente) {
        try {
            $query = "SELECT n.*, u.nombre_completo as emisor_nombre
                      FROM notificaciones n
                      INNER JOIN usuarios u ON n.id_usuario_emisor = u.id
                      WHERE n.id_cliente = :id_cliente 
                      AND n.id_empresa = :id_empresa
                      AND n.deleted_at IS NULL
                      ORDER BY n.fecha_envio DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id_cliente', $id_cliente);
            $stmt->bindParam(':id_empresa', $this->id_empresa);
            $stmt->execute();
            $notificaciones = $stmt->fetchAll();
            
            header('Content-Type: application/json');
            echo json_encode($notificaciones);
        } catch (PDOException $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
    // Estadísticas de notificaciones
    public function estadisticas() {
        try {
            // Total enviadas
            $query = "SELECT COUNT(*) as total FROM notificaciones 
                      WHERE id_empresa = :id_empresa AND deleted_at IS NULL";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id_empresa', $this->id_empresa);
            $stmt->execute();
            $total = $stmt->fetch()['total'];
            
            // Por tipo
            $query = "SELECT tipo, COUNT(*) as cantidad FROM notificaciones 
                      WHERE id_empresa = :id_empresa AND deleted_at IS NULL
                      GROUP BY tipo";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id_empresa', $this->id_empresa);
            $stmt->execute();
            $por_tipo = $stmt->fetchAll();
            
            // Últimas 7 días
            $query = "SELECT DATE(fecha_envio) as fecha, COUNT(*) as cantidad 
                      FROM notificaciones 
                      WHERE id_empresa = :id_empresa 
                      AND deleted_at IS NULL
                      AND fecha_envio >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                      GROUP BY DATE(fecha_envio)
                      ORDER BY fecha DESC";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id_empresa', $this->id_empresa);
            $stmt->execute();
            $ultimos_dias = $stmt->fetchAll();
            
            $estadisticas = [
                'total' => $total,
                'por_tipo' => $por_tipo,
                'ultimos_dias' => $ultimos_dias
            ];
            
            header('Content-Type: application/json');
            echo json_encode($estadisticas);
        } catch (PDOException $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}

// Enrutamiento
$controller = new NotificacionController();
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
    case 'delete':
        $controller->delete($id);
        break;
    case 'plantillas':
        $controller->plantillas();
        break;
    case 'por_cliente':
        $id_cliente = $_GET['id_cliente'] ?? null;
        $controller->porCliente($id_cliente);
        break;
    case 'estadisticas':
        $controller->estadisticas();
        break;
    default:
        $controller->index();
}
?>