<?php
class Lote extends BaseModel {
    public function __construct($db, $id_empresa) {
        parent::__construct($db, $id_empresa);
        $this->table = 'lotes';
    }
    
    public function porCliente($id_cliente) {
        $query = "SELECT * FROM lotes 
                  WHERE id_cliente = :id_cliente 
                  AND id_empresa = :id_empresa 
                  AND deleted_at IS NULL
                  ORDER BY nombre ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_cliente', $id_cliente);
        $stmt->bindParam(':id_empresa', $this->id_empresa);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function conEtapas() {
        $query = "SELECT l.*, 
                  (SELECT COUNT(*) FROM etapas_cultivo ec 
                   WHERE ec.id_lote = l.id AND ec.deleted_at IS NULL) as total_etapas
                  FROM lotes l
                  WHERE l.id_empresa = :id_empresa 
                  AND l.deleted_at IS NULL
                  ORDER BY l.id DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_empresa', $this->id_empresa);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}