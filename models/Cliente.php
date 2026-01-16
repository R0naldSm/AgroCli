<?php
// models/Cliente.php
require_once 'Database.php';

class Cliente extends BaseModel {
    public $id;
    public $id_empresa;
    public $cedula;
    public $nombre;
    public $apellido;
    public $telefono;
    public $email;
    public $direccion;
    
    public function __construct($db, $id_empresa) {
        parent::__construct($db, $id_empresa);
        $this->table = 'clientes';
    }
    
    // Crear cliente
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  (id_empresa, cedula, nombre, apellido, telefono, email, direccion) 
                  VALUES 
                  (:id_empresa, :cedula, :nombre, :apellido, :telefono, :email, :direccion)";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitizar datos
        $this->cedula = htmlspecialchars(strip_tags($this->cedula));
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->apellido = htmlspecialchars(strip_tags($this->apellido));
        
        // Bind
        $stmt->bindParam(':id_empresa', $this->id_empresa);
        $stmt->bindParam(':cedula', $this->cedula);
        $stmt->bindParam(':nombre', $this->nombre);
        $stmt->bindParam(':apellido', $this->apellido);
        $stmt->bindParam(':telefono', $this->telefono);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':direccion', $this->direccion);
        
        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    // Actualizar cliente
    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET cedula = :cedula,
                      nombre = :nombre,
                      apellido = :apellido,
                      telefono = :telefono,
                      email = :email,
                      direccion = :direccion,
                      updated_at = NOW()
                  WHERE id = :id AND id_empresa = :id_empresa";
        
        $stmt = $this->conn->prepare($query);
        
        $this->cedula = htmlspecialchars(strip_tags($this->cedula));
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->apellido = htmlspecialchars(strip_tags($this->apellido));
        
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':id_empresa', $this->id_empresa);
        $stmt->bindParam(':cedula', $this->cedula);
        $stmt->bindParam(':nombre', $this->nombre);
        $stmt->bindParam(':apellido', $this->apellido);
        $stmt->bindParam(':telefono', $this->telefono);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':direccion', $this->direccion);
        
        return $stmt->execute();
    }
    
    // Buscar por cédula (autocompletado)
    public function buscarPorCedula($cedula) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE cedula LIKE :cedula 
                  AND id_empresa = :id_empresa 
                  AND deleted_at IS NULL
                  LIMIT 10";
        
        $stmt = $this->conn->prepare($query);
        $search_cedula = $cedula . "%";
        $stmt->bindParam(':cedula', $search_cedula);
        $stmt->bindParam(':id_empresa', $this->id_empresa);
        
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    // Verificar si cédula existe
    public function cedulaExiste($cedula, $excluir_id = null) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " 
                  WHERE cedula = :cedula 
                  AND id_empresa = :id_empresa 
                  AND deleted_at IS NULL";
        
        if ($excluir_id) {
            $query .= " AND id != :excluir_id";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':cedula', $cedula);
        $stmt->bindParam(':id_empresa', $this->id_empresa);
        
        if ($excluir_id) {
            $stmt->bindParam(':excluir_id', $excluir_id);
        }
        
        $stmt->execute();
        $row = $stmt->fetch();
        
        return $row['total'] > 0;
    }
    
    // Obtener lotes del cliente
    public function getLotes($id_cliente) {
        $query = "SELECT l.*, 
                  (SELECT COUNT(*) FROM etapas_cultivo ec 
                   WHERE ec.id_lote = l.id AND ec.deleted_at IS NULL) as total_etapas
                  FROM lotes l
                  WHERE l.id_cliente = :id_cliente 
                  AND l.id_empresa = :id_empresa 
                  AND l.deleted_at IS NULL
                  ORDER BY l.id DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_cliente', $id_cliente);
        $stmt->bindParam(':id_empresa', $this->id_empresa);
        
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    // Obtener historial de producción del cliente
    public function getHistorialProduccion($id_cliente) {
        $query = "SELECT 
                    l.nombre as lote_nombre,
                    ec.tipo_cultivo,
                    ec.fecha_inicio,
                    ec.fecha_fin_real,
                    ec.produccion_quintales,
                    ec.temporada,
                    l.tamanio_hectareas
                  FROM etapas_cultivo ec
                  INNER JOIN lotes l ON ec.id_lote = l.id
                  WHERE l.id_cliente = :id_cliente
                  AND l.id_empresa = :id_empresa
                  AND ec.estado = 'finalizada'
                  AND ec.deleted_at IS NULL
                  ORDER BY ec.fecha_fin_real DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_cliente', $id_cliente);
        $stmt->bindParam(':id_empresa', $this->id_empresa);
        
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    // Búsqueda avanzada
    public function busquedaAvanzada($termino) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE (nombre LIKE :termino 
                  OR apellido LIKE :termino 
                  OR cedula LIKE :termino
                  OR CONCAT(nombre, ' ', apellido) LIKE :termino)
                  AND id_empresa = :id_empresa 
                  AND deleted_at IS NULL
                  LIMIT 20";
        
        $stmt = $this->conn->prepare($query);
        $search_term = "%{$termino}%";
        $stmt->bindParam(':termino', $search_term);
        $stmt->bindParam(':id_empresa', $this->id_empresa);
        
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
?>