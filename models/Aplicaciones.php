<?php
class Aplicacion extends BaseModel {
    public function __construct($db, $id_empresa) {
        parent::__construct($db, $id_empresa);
        $this->table = 'aplicaciones';
    }
    
    public function porEtapa($id_etapa) {
        $query = "SELECT a.*, p.nombre as producto_nombre, p.tipo as producto_tipo
                  FROM aplicaciones a
                  INNER JOIN productos p ON a.id_producto = p.id
                  WHERE a.id_etapa_cultivo = :id_etapa
                  AND a.id_empresa = :id_empresa 
                  AND a.deleted_at IS NULL
                  ORDER BY a.fecha_aplicacion DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_etapa', $id_etapa);
        $stmt->bindParam(':id_empresa', $this->id_empresa);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function ultimasAplicaciones($limite = 50) {
        $query = "SELECT a.*, 
                  p.nombre as producto_nombre,
                  ec.tipo_cultivo,
                  l.nombre as lote_nombre
                  FROM aplicaciones a
                  INNER JOIN productos p ON a.id_producto = p.id
                  INNER JOIN etapas_cultivo ec ON a.id_etapa_cultivo = ec.id
                  INNER JOIN lotes l ON ec.id_lote = l.id
                  WHERE a.id_empresa = :id_empresa 
                  AND a.deleted_at IS NULL
                  ORDER BY a.fecha_aplicacion DESC
                  LIMIT :limite";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_empresa', $this->id_empresa);
        $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}
