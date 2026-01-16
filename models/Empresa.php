<?php
class Empresa extends BaseModel {
    public function __construct($db) {
        parent::__construct($db);
        $this->table = 'empresas';
    }
    
    public function conEstadisticas() {
        $query = "SELECT e.*,
                  (SELECT COUNT(*) FROM usuarios u 
                   WHERE u.id_empresa = e.id AND u.deleted_at IS NULL) as total_usuarios,
                  (SELECT COUNT(*) FROM clientes c 
                   WHERE c.id_empresa = e.id AND c.deleted_at IS NULL) as total_clientes
                  FROM empresas e
                  WHERE e.deleted_at IS NULL
                  ORDER BY e.id DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}
?>