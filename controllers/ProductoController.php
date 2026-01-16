<?php
// controllers/ProductoController.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

checkAuth();

class ProductoController {
    private $db;
    private $id_empresa;
    private $id_usuario;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->id_empresa = $_SESSION['id_empresa'];
        $this->id_usuario = $_SESSION['usuario_id'];
    }
    
    // Listar productos
    public function index() {
        try {
            $query = "SELECT p.*,
                      (SELECT COUNT(*) FROM aplicaciones a 
                       WHERE a.id_producto = p.id AND a.deleted_at IS NULL) as total_aplicaciones
                      FROM productos p
                      WHERE p.id_empresa = :id_empresa 
                      AND p.deleted_at IS NULL
                      ORDER BY p.nombre ASC";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id_empresa', $this->id_empresa);
            $stmt->execute();
            $productos = $stmt->fetchAll();
            
            include __DIR__ . '/../ingeniero/productos/index.php';
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
            include __DIR__ . '/../ingeniero/productos/index.php';
        }
    }
    
    // Formulario crear producto
    public function create() {
        include __DIR__ . '/../ingeniero/productos/crear.php';
    }
    
    // Guardar producto
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                $query = "INSERT INTO productos 
                          (id_empresa, nombre, tipo, descripcion, unidad_medida, precio_unitario, stock)
                          VALUES 
                          (:id_empresa, :nombre, :tipo, :descripcion, :unidad_medida, :precio_unitario, :stock)";
                
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':id_empresa', $this->id_empresa);
                $stmt->bindParam(':nombre', sanitize($_POST['nombre']));
                $stmt->bindParam(':tipo', $_POST['tipo']);
                $stmt->bindParam(':descripcion', sanitize($_POST['descripcion']));
                $stmt->bindParam(':unidad_medida', $_POST['unidad_medida']);
                $stmt->bindParam(':precio_unitario', $_POST['precio_unitario']);
                $stmt->bindParam(':stock', $_POST['stock']);
                $stmt->execute();
                
                $id_producto = $this->db->lastInsertId();
                
                // Registrar auditoría
                $query = "INSERT INTO auditoria (id_usuario, id_empresa, accion, tabla_afectada, id_registro)
                          VALUES (:id_usuario, :id_empresa, 'INSERT', 'productos', :id_registro)";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':id_usuario', $this->id_usuario);
                $stmt->bindParam(':id_empresa', $this->id_empresa);
                $stmt->bindParam(':id_registro', $id_producto);
                $stmt->execute();
                
                $_SESSION['success'] = "Producto registrado exitosamente";
                header('Location: ProductoController.php');
            } catch (PDOException $e) {
                $_SESSION['error'] = "Error: " . $e->getMessage();
                header('Location: ProductoController.php?action=create');
            }
        }
    }
    
    // Formulario editar producto
    public function edit($id) {
        try {
            $query = "SELECT * FROM productos 
                      WHERE id = :id 
                      AND id_empresa = :id_empresa 
                      AND deleted_at IS NULL";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':id_empresa', $this->id_empresa);
            $stmt->execute();
            $producto = $stmt->fetch();
            
            if (!$producto) {
                $_SESSION['error'] = "Producto no encontrado";
                header('Location: ProductoController.php');
                exit();
            }
            
            include __DIR__ . '/../ingeniero/productos/editar.php';
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
            header('Location: ProductoController.php');
        }
    }
    
    // Actualizar producto
    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                $query = "UPDATE productos SET
                          nombre = :nombre,
                          tipo = :tipo,
                          descripcion = :descripcion,
                          unidad_medida = :unidad_medida,
                          precio_unitario = :precio_unitario,
                          stock = :stock,
                          activo = :activo,
                          updated_at = NOW()
                          WHERE id = :id AND id_empresa = :id_empresa";
                
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':id', $id);
                $stmt->bindParam(':id_empresa', $this->id_empresa);
                $stmt->bindParam(':nombre', sanitize($_POST['nombre']));
                $stmt->bindParam(':tipo', $_POST['tipo']);
                $stmt->bindParam(':descripcion', sanitize($_POST['descripcion']));
                $stmt->bindParam(':unidad_medida', $_POST['unidad_medida']);
                $stmt->bindParam(':precio_unitario', $_POST['precio_unitario']);
                $stmt->bindParam(':stock', $_POST['stock']);
                $stmt->bindParam(':activo', $_POST['activo']);
                $stmt->execute();
                
                // Registrar auditoría
                $query = "INSERT INTO auditoria (id_usuario, id_empresa, accion, tabla_afectada, id_registro)
                          VALUES (:id_usuario, :id_empresa, 'UPDATE', 'productos', :id_registro)";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':id_usuario', $this->id_usuario);
                $stmt->bindParam(':id_empresa', $this->id_empresa);
                $stmt->bindParam(':id_registro', $id);
                $stmt->execute();
                
                $_SESSION['success'] = "Producto actualizado exitosamente";
                header('Location: ProductoController.php');
            } catch (PDOException $e) {
                $_SESSION['error'] = "Error: " . $e->getMessage();
                header("Location: ProductoController.php?action=edit&id={$id}");
            }
        }
    }
    
    // Actualizar stock
    public function actualizarStock($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                $operacion = $_POST['operacion']; // 'sumar' o 'restar'
                $cantidad = floatval($_POST['cantidad']);
                
                if ($operacion == 'sumar') {
                    $query = "UPDATE productos SET stock = stock + :cantidad WHERE id = :id AND id_empresa = :id_empresa";
                } else {
                    $query = "UPDATE productos SET stock = stock - :cantidad WHERE id = :id AND id_empresa = :id_empresa";
                }
                
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':id', $id);
                $stmt->bindParam(':id_empresa', $this->id_empresa);
                $stmt->bindParam(':cantidad', $cantidad);
                $stmt->execute();
                
                $_SESSION['success'] = "Stock actualizado exitosamente";
                header('Location: ProductoController.php');
            } catch (PDOException $e) {
                $_SESSION['error'] = "Error: " . $e->getMessage();
                header('Location: ProductoController.php');
            }
        }
    }
    
    // Eliminar producto (lógico)
    public function delete($id) {
        try {
            // Verificar que no tenga aplicaciones
            $query = "SELECT COUNT(*) as total FROM aplicaciones 
                      WHERE id_producto = :id 
                      AND id_empresa = :id_empresa 
                      AND deleted_at IS NULL";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':id_empresa', $this->id_empresa);
            $stmt->execute();
            $result = $stmt->fetch();
            
            if ($result['total'] > 0) {
                $_SESSION['error'] = "No se puede eliminar. El producto tiene aplicaciones registradas. Puede desactivarlo en su lugar.";
            } else {
                $query = "UPDATE productos SET deleted_at = NOW() 
                          WHERE id = :id AND id_empresa = :id_empresa";
                
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':id', $id);
                $stmt->bindParam(':id_empresa', $this->id_empresa);
                $stmt->execute();
                
                $_SESSION['success'] = "Producto eliminado exitosamente";
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
        }
        
        header('Location: ProductoController.php');
    }
    
    // Buscar productos (AJAX)
    public function buscar() {
        if (isset($_GET['q'])) {
            try {
                $termino = "%" . $_GET['q'] . "%";
                
                $query = "SELECT id, nombre, tipo, stock, unidad_medida, precio_unitario
                          FROM productos 
                          WHERE id_empresa = :id_empresa 
                          AND deleted_at IS NULL
                          AND activo = 1
                          AND (nombre LIKE :termino OR tipo LIKE :termino)
                          ORDER BY nombre ASC
                          LIMIT 20";
                
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':id_empresa', $this->id_empresa);
                $stmt->bindParam(':termino', $termino);
                $stmt->execute();
                $productos = $stmt->fetchAll();
                
                header('Content-Type: application/json');
                echo json_encode($productos);
            } catch (PDOException $e) {
                echo json_encode(['error' => $e->getMessage()]);
            }
        }
    }
    
    // Reporte de stock bajo
    public function stockBajo() {
        try {
            $query = "SELECT * FROM productos 
                      WHERE id_empresa = :id_empresa 
                      AND deleted_at IS NULL
                      AND activo = 1
                      AND stock < 10
                      ORDER BY stock ASC";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id_empresa', $this->id_empresa);
            $stmt->execute();
            $productos_bajo_stock = $stmt->fetchAll();
            
            include __DIR__ . '/../ingeniero/productos/stock_bajo.php';
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
            header('Location: ProductoController.php');
        }
    }
}

// Enrutamiento
$controller = new ProductoController();
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
    case 'actualizar_stock':
        $controller->actualizarStock($id);
        break;
    case 'delete':
        $controller->delete($id);
        break;
    case 'buscar':
        $controller->buscar();
        break;
    case 'stock_bajo':
        $controller->stockBajo();
        break;
    default:
        $controller->index();
}
?>