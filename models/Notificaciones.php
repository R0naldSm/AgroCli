<?php
class Notificacion extends BaseModel {
    public function __construct($db, $id_empresa) {
        parent::__construct($db, $id_empresa);
        $this->table = 'notificaciones';
    }
    
    public function porCliente($id_cliente) {
        $query = "SELECT * FROM notificaciones 
                  WHERE id_cliente = :id_cliente
                  AND id_empresa = :id_empresa 
                  AND deleted_at IS NULL
                  ORDER BY fecha_envio DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_cliente', $id_cliente);
        $stmt->bindParam(':id_empresa', $this->id_empresa);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function generales() {
        $query = "SELECT * FROM notificaciones 
                  WHERE tipo = 'general'
                  AND id_empresa = :id_empresa 
                  AND deleted_at IS NULL
                  ORDER BY fecha_envio DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_empresa', $this->id_empresa);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}