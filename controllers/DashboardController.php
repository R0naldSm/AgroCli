<?php
// admin/controllers/DashboardController.php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';

class DashboardController {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    // Obtener estadísticas generales del sistema
    public function getEstadisticas() {
        try {
            $estadisticas = [];
            
            // Total de empresas
            $query = "SELECT COUNT(*) as total FROM empresas WHERE deleted_at IS NULL";
            $stmt = $this->db->query($query);
            $estadisticas['total_empresas'] = $stmt->fetch()['total'];
            
            // Total de usuarios (excepto admin_general)
            $query = "SELECT COUNT(*) as total FROM usuarios 
                      WHERE deleted_at IS NULL AND rol != 'admin_general'";
            $stmt = $this->db->query($query);
            $estadisticas['total_usuarios'] = $stmt->fetch()['total'];
            
            // Total de clientes en todo el sistema
            $query = "SELECT COUNT(*) as total FROM clientes WHERE deleted_at IS NULL";
            $stmt = $this->db->query($query);
            $estadisticas['total_clientes'] = $stmt->fetch()['total'];
            
            // Producción total del año actual
            $query = "SELECT COALESCE(SUM(produccion_quintales), 0) as total 
                      FROM etapas_cultivo 
                      WHERE YEAR(fecha_fin_real) = YEAR(NOW())
                      AND estado = 'finalizada'
                      AND deleted_at IS NULL";
            $stmt = $this->db->query($query);
            $estadisticas['produccion_total'] = $stmt->fetch()['total'];
            
            return $estadisticas;
            
        } catch (PDOException $e) {
            return [
                'total_empresas' => 0,
                'total_usuarios' => 0,
                'total_clientes' => 0,
                'produccion_total' => 0
            ];
        }
    }
    
    // Listar empresas con sus estadísticas
    public function listarEmpresas() {
        try {
            $query = "SELECT e.*,
                      (SELECT COUNT(*) FROM usuarios u 
                       WHERE u.id_empresa = e.id AND u.deleted_at IS NULL) as total_usuarios,
                      (SELECT COUNT(*) FROM clientes c 
                       WHERE c.id_empresa = e.id AND c.deleted_at IS NULL) as total_clientes
                      FROM empresas e
                      WHERE e.deleted_at IS NULL
                      ORDER BY e.id DESC";
            
            $stmt = $this->db->query($query);
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            return [];
        }
    }
    
    // Últimos usuarios creados
    public function ultimosUsuarios($limite = 10) {
        try {
            $query = "SELECT u.*, e.nombre as empresa_nombre
                      FROM usuarios u
                      LEFT JOIN empresas e ON u.id_empresa = e.id
                      WHERE u.deleted_at IS NULL
                      AND u.rol != 'admin_general'
                      ORDER BY u.created_at DESC
                      LIMIT :limite";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            return [];
        }
    }
    
    // Actividad reciente (auditoría)
    public function actividadReciente($limite = 20) {
        try {
            $query = "SELECT a.*, u.nombre_completo, e.nombre as empresa_nombre
                      FROM auditoria a
                      INNER JOIN usuarios u ON a.id_usuario = u.id
                      LEFT JOIN empresas e ON a.id_empresa = e.id
                      ORDER BY a.created_at DESC
                      LIMIT :limite";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            return [];
        }
    }
    
    // Producción por empresa
    public function produccionPorEmpresa() {
        try {
            $query = "SELECT e.nombre as empresa,
                      COALESCE(SUM(ec.produccion_quintales), 0) as produccion
                      FROM empresas e
                      LEFT JOIN etapas_cultivo ec ON e.id = ec.id_empresa 
                          AND ec.estado = 'finalizada'
                          AND YEAR(ec.fecha_fin_real) = YEAR(NOW())
                          AND ec.deleted_at IS NULL
                      WHERE e.deleted_at IS NULL
                      GROUP BY e.id, e.nombre
                      ORDER BY produccion DESC";
            
            $stmt = $this->db->query($query);
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            return [];
        }
    }
}
?>