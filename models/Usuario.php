<?php
class Usuario extends BaseModel {
    public function __construct($db) {
        parent::__construct($db);
        $this->table = 'usuarios';
    }
    
    public function porEmpresa($id_empresa) {
        $query = "SELECT * FROM usuarios 
                  WHERE id_empresa = :id_empresa 
                  AND deleted_at IS NULL
                  ORDER BY nombre_completo ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_empresa', $id_empresa);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function usernameExiste($username, $excluir_id = null) {
        $query = "SELECT COUNT(*) as total FROM usuarios 
                  WHERE username = :username 
                  AND deleted_at IS NULL";
        
        if ($excluir_id) {
            $query .= " AND id != :excluir_id";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        
        if ($excluir_id) {
            $stmt->bindParam(':excluir_id', $excluir_id);
        }
        
        $stmt->execute();
        $row = $stmt->fetch();
        return $row['total'] > 0;
    }
}
