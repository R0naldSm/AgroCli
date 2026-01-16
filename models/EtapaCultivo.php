<?php
class EtapaCultivo extends BaseModel {
    public function __construct($db, $id_empresa) {
        parent::__construct($db, $id_empresa);
        $this->table = 'etapas_cultivo';
    }
    
    public function porLote($id_lote) {
        $query = "SELECT * FROM etapas_cultivo 
                  WHERE id_lote = :id_lote 
                  AND id_empresa = :id_empresa 
                  AND deleted_at IS NULL
                  ORDER BY fecha_inicio DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_lote', $id_lote);
        $stmt->bindParam(':id_empresa', $this->id_empresa);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function enProceso() {
        $query = "SELECT * FROM etapas_cultivo 
                  WHERE estado = 'en_proceso'
                  AND id_empresa = :id_empresa 
                  AND deleted_at IS NULL
                  ORDER BY fecha_inicio DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_empresa', $this->id_empresa);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function proximasACosechar($dias = 15) {
        $query = "SELECT ec.*, l.nombre as lote_nombre,
                  DATEDIFF(ec.fecha_fin_estimada, NOW()) as dias_restantes
                  FROM etapas_cultivo ec
                  INNER JOIN lotes l ON ec.id_lote = l.id
                  WHERE ec.estado = 'en_proceso'
                  AND ec.id_empresa = :id_empresa
                  AND ec.fecha_fin_estimada BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL :dias DAY)
                  AND ec.deleted_at IS NULL
                  ORDER BY ec.fecha_fin_estimada ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_empresa', $this->id_empresa);
        $stmt->bindParam(':dias', $dias);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}