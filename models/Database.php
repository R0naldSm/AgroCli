<?php
// ============================================
// models/Database.php - Clase Base
// ============================================
class BaseModel {
    protected $conn;
    protected $table;
    protected $id_empresa;
    
    public function __construct($db, $id_empresa = null) {
        $this->conn = $db;
        $this->id_empresa = $id_empresa;
    }
    
    // Obtener todos los registros
    public function getAll() {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE deleted_at IS NULL";
        
        if ($this->id_empresa) {
            $query .= " AND id_empresa = :id_empresa";
        }
        
        $query .= " ORDER BY id DESC";
        
        $stmt = $this->conn->prepare($query);
        
        if ($this->id_empresa) {
            $stmt->bindParam(':id_empresa', $this->id_empresa);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    // Obtener por ID
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE id = :id AND deleted_at IS NULL";
        
        if ($this->id_empresa) {
            $query .= " AND id_empresa = :id_empresa";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        if ($this->id_empresa) {
            $stmt->bindParam(':id_empresa', $this->id_empresa);
        }
        
        $stmt->execute();
        return $stmt->fetch();
    }
    
    // Eliminar lógicamente
    public function delete($id) {
        $query = "UPDATE " . $this->table . " 
                  SET deleted_at = NOW() 
                  WHERE id = :id";
        
        if ($this->id_empresa) {
            $query .= " AND id_empresa = :id_empresa";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        if ($this->id_empresa) {
            $stmt->bindParam(':id_empresa', $this->id_empresa);
        }
        
        return $stmt->execute();
    }
    
    // Contar registros
    public function count() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " 
                  WHERE deleted_at IS NULL";
        
        if ($this->id_empresa) {
            $query .= " AND id_empresa = :id_empresa";
        }
        
        $stmt = $this->conn->prepare($query);
        
        if ($this->id_empresa) {
            $stmt->bindParam(':id_empresa', $this->id_empresa);
        }
        
        $stmt->execute();
        $row = $stmt->fetch();
        return $row['total'];
    }
}