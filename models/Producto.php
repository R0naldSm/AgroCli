<?php
class Producto extends BaseModel {
    public function __construct($db, $id_empresa) {
        parent::__construct($db, $id_empresa);
        $this->table = 'productos';
    }
    
    public function activos() {
        $query = "SELECT * FROM productos 
                  WHERE activo = 1
                  AND id_empresa = :id_empresa 
                  AND deleted_at IS NULL
                  ORDER BY nombre ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_empresa', $this->id_empresa);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function stockBajo($limite = 10) {
        $query = "SELECT * FROM productos 
                  WHERE stock < :limite
                  AND activo = 1
                  AND id_empresa = :id_empresa 
                  AND deleted_at IS NULL
                  ORDER BY stock ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limite', $limite);
        $stmt->bindParam(':id_empresa', $this->id_empresa);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function actualizarStock($id, $cantidad, $operacion = 'restar') {
        if ($operacion == 'restar') {
            $query = "UPDATE productos SET stock = stock - :cantidad WHERE id = :id AND id_empresa = :id_empresa";
        } else {
            $query = "UPDATE productos SET stock = stock + :cantidad WHERE id = :id AND id_empresa = :id_empresa";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':cantidad', $cantidad);
        $stmt->bindParam(':id_empresa', $this->id_empresa);
        
        return $stmt->execute();
    }
}