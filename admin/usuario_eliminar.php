<?php
// admin/usuario_eliminar.php
require_once '../config/config.php';
require_once '../config/database.php';

// Verificar que sea admin_general
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'admin_general') {
    header('Location: login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$id_usuario = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_usuario <= 0) {
    $_SESSION['error'] = "ID de usuario inválido";
    header('Location: usuarios.php');
    exit();
}

try {
    // Verificar que no sea admin_general
    $query = "SELECT rol, nombre_completo FROM usuarios WHERE id = :id AND deleted_at IS NULL";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id_usuario);
    $stmt->execute();
    $usuario = $stmt->fetch();
    
    if (!$usuario) {
        $_SESSION['error'] = "Usuario no encontrado";
        header('Location: usuarios.php');
        exit();
    }
    
    if ($usuario['rol'] == 'admin_general') {
        $_SESSION['error'] = "No se puede eliminar un administrador general";
        header('Location: usuarios.php');
        exit();
    }
    
    // Eliminar lógicamente
    $query = "UPDATE usuarios SET deleted_at = NOW() WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id_usuario);
    $stmt->execute();
    
    // Registrar en auditoría
    $query = "INSERT INTO auditoria (id_usuario, id_empresa, accion, tabla_afectada, id_registro, datos_antiguos)
              VALUES (:id_usuario_admin, 0, 'DELETE', 'usuarios', :id_usuario_eliminado, :nombre)";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id_usuario_admin', $_SESSION['usuario_id']);
    $stmt->bindParam(':id_usuario_eliminado', $id_usuario);
    $stmt->bindParam(':nombre', $usuario['nombre_completo']);
    $stmt->execute();
    
    $_SESSION['success'] = "Usuario eliminado exitosamente: " . $usuario['nombre_completo'];
    
} catch (PDOException $e) {
    $_SESSION['error'] = "Error al eliminar usuario: " . $e->getMessage();
}

header('Location: usuarios.php');
exit();
?>