-- =====================================================
-- Base de datos AgroCli
-- Versión corregida y con datos de prueba
-- =====================================================

CREATE DATABASE IF NOT EXISTS agrimanage CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE agrimanage;

-- =====================================================
-- TABLAS
-- =====================================================

CREATE TABLE empresas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    ruc VARCHAR(20) UNIQUE,
    direccion TEXT,
    telefono VARCHAR(20),
    email VARCHAR(100),
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
);

CREATE TABLE usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_empresa INT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nombre_completo VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    rol ENUM('admin_general', 'admin_empresa', 'ingeniero', 'asistente') NOT NULL,
    activo TINYINT(1) DEFAULT 1,
    ultimo_acceso TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (id_empresa) REFERENCES empresas(id)
);

CREATE TABLE clientes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_empresa INT NOT NULL,
    cedula VARCHAR(13) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    telefono VARCHAR(20),
    email VARCHAR(100),
    direccion TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (id_empresa) REFERENCES empresas(id),
    INDEX idx_cedula (cedula),
    INDEX idx_empresa (id_empresa)
);

CREATE TABLE lotes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_empresa INT NOT NULL,
    id_cliente INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    ubicacion TEXT,
    tamanio_paradas DECIMAL(10,2),
    tamanio_cuadras DECIMAL(10,2),
    tamanio_hectareas DECIMAL(10,2),
    temporada ENUM('invierno', 'verano'),
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (id_empresa) REFERENCES empresas(id),
    FOREIGN KEY (id_cliente) REFERENCES clientes(id),
    INDEX idx_empresa_cliente (id_empresa, id_cliente)
);

CREATE TABLE etapas_cultivo (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_empresa INT NOT NULL,
    id_lote INT NOT NULL,
    -- CORRECTO: los valores son 'siembra' y 'soca' (no 'trasplante')
    tipo_cultivo ENUM('siembra', 'soca') NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin_estimada DATE,
    fecha_fin_real DATE,
    dias_duracion INT,
    estado ENUM('en_proceso', 'finalizada') DEFAULT 'en_proceso',
    produccion_quintales DECIMAL(10,2),
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (id_empresa) REFERENCES empresas(id),
    FOREIGN KEY (id_lote) REFERENCES lotes(id),
    INDEX idx_empresa_lote (id_empresa, id_lote)
);

CREATE TABLE productos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_empresa INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    tipo ENUM('herbicida', 'fungicida', 'insecticida', 'fertilizante', 'otro') NOT NULL,
    descripcion TEXT,
    unidad_medida VARCHAR(20),
    precio_unitario DECIMAL(10,2),
    stock DECIMAL(10,2) DEFAULT 0,
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (id_empresa) REFERENCES empresas(id),
    INDEX idx_empresa (id_empresa)
);

CREATE TABLE aplicaciones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_empresa INT NOT NULL,
    id_etapa_cultivo INT NOT NULL,
    id_producto INT NOT NULL,
    fecha_aplicacion DATE NOT NULL,
    cantidad DECIMAL(10,2) NOT NULL,
    dosis VARCHAR(50),
    metodo_aplicacion VARCHAR(100),
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (id_empresa) REFERENCES empresas(id),
    FOREIGN KEY (id_etapa_cultivo) REFERENCES etapas_cultivo(id),
    FOREIGN KEY (id_producto) REFERENCES productos(id),
    INDEX idx_empresa_etapa (id_empresa, id_etapa_cultivo)
);

CREATE TABLE notificaciones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_empresa INT NOT NULL,
    id_usuario_emisor INT NOT NULL,
    id_cliente INT NULL,
    tipo ENUM('general', 'individual') NOT NULL,
    asunto VARCHAR(200) NOT NULL,
    mensaje TEXT NOT NULL,
    fecha_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    leida TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (id_empresa) REFERENCES empresas(id),
    FOREIGN KEY (id_usuario_emisor) REFERENCES usuarios(id),
    FOREIGN KEY (id_cliente) REFERENCES clientes(id),
    INDEX idx_empresa_cliente (id_empresa, id_cliente)
);

CREATE TABLE auditoria (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    id_empresa INT NOT NULL,
    accion VARCHAR(50) NOT NULL,
    tabla_afectada VARCHAR(50),
    id_registro INT,
    datos_antiguos TEXT,
    datos_nuevos TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id),
    FOREIGN KEY (id_empresa) REFERENCES empresas(id),
    INDEX idx_usuario (id_usuario),
    INDEX idx_fecha (created_at)
);

-- =====================================================
-- PROCEDIMIENTOS ALMACENADOS
-- =====================================================

DELIMITER $$

-- 1. Crear empresa
CREATE PROCEDURE sp_crear_empresa(
    IN p_nombre VARCHAR(100),
    IN p_ruc VARCHAR(20),
    IN p_direccion TEXT,
    IN p_telefono VARCHAR(20),
    IN p_email VARCHAR(100)
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SELECT 'Error al crear la empresa' AS mensaje, 0 AS success;
    END;

    START TRANSACTION;

    IF EXISTS (SELECT 1 FROM empresas WHERE ruc = p_ruc AND deleted_at IS NULL) THEN
        SELECT 'El RUC ya está registrado' AS mensaje, 0 AS success;
        ROLLBACK;
    ELSE
        INSERT INTO empresas (nombre, ruc, direccion, telefono, email)
        VALUES (p_nombre, p_ruc, p_direccion, p_telefono, p_email);
        SELECT LAST_INSERT_ID() AS id, 'Empresa creada exitosamente' AS mensaje, 1 AS success;
        COMMIT;
    END IF;
END$$

-- 2. Crear usuario
CREATE PROCEDURE sp_crear_usuario(
    IN p_id_empresa INT,
    IN p_username VARCHAR(50),
    IN p_password VARCHAR(255),
    IN p_nombre_completo VARCHAR(100),
    IN p_email VARCHAR(100),
    IN p_rol VARCHAR(20)
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SELECT 'Error al crear el usuario' AS mensaje, 0 AS success;
    END;

    START TRANSACTION;

    IF EXISTS (SELECT 1 FROM usuarios WHERE username = p_username AND deleted_at IS NULL) THEN
        SELECT 'El nombre de usuario ya existe' AS mensaje, 0 AS success;
        ROLLBACK;
    ELSE
        INSERT INTO usuarios (id_empresa, username, password, nombre_completo, email, rol)
        VALUES (p_id_empresa, p_username, p_password, p_nombre_completo, p_email, p_rol);
        SELECT LAST_INSERT_ID() AS id, 'Usuario creado exitosamente' AS mensaje, 1 AS success;
        COMMIT;
    END IF;
END$$

-- 3. Registrar cliente
CREATE PROCEDURE sp_registrar_cliente(
    IN p_id_empresa INT,
    IN p_cedula VARCHAR(13),
    IN p_nombre VARCHAR(100),
    IN p_apellido VARCHAR(100),
    IN p_telefono VARCHAR(20),
    IN p_email VARCHAR(100),
    IN p_direccion TEXT,
    IN p_id_usuario INT
)
BEGIN
    DECLARE v_cliente_id INT;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SELECT 'Error al registrar cliente' AS mensaje, 0 AS success;
    END;

    START TRANSACTION;

    IF EXISTS (SELECT 1 FROM clientes WHERE cedula = p_cedula AND id_empresa = p_id_empresa AND deleted_at IS NULL) THEN
        SELECT 'La cédula ya está registrada en esta empresa' AS mensaje, 0 AS success;
        ROLLBACK;
    ELSE
        INSERT INTO clientes (id_empresa, cedula, nombre, apellido, telefono, email, direccion)
        VALUES (p_id_empresa, p_cedula, p_nombre, p_apellido, p_telefono, p_email, p_direccion);

        SET v_cliente_id = LAST_INSERT_ID();

        INSERT INTO auditoria (id_usuario, id_empresa, accion, tabla_afectada, id_registro)
        VALUES (p_id_usuario, p_id_empresa, 'INSERT', 'clientes', v_cliente_id);

        SELECT v_cliente_id AS id, 'Cliente registrado exitosamente' AS mensaje, 1 AS success;
        COMMIT;
    END IF;
END$$

-- 4. Registrar lote
CREATE PROCEDURE sp_registrar_lote(
    IN p_id_empresa INT,
    IN p_id_cliente INT,
    IN p_nombre VARCHAR(100),
    IN p_ubicacion TEXT,
    IN p_tamanio_paradas DECIMAL(10,2),
    IN p_temporada VARCHAR(20),
    IN p_id_usuario INT
)
BEGIN
    DECLARE v_lote_id INT;
    DECLARE v_cuadras DECIMAL(10,2);
    DECLARE v_hectareas DECIMAL(10,2);
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SELECT 'Error al registrar lote' AS mensaje, 0 AS success;
    END;

    START TRANSACTION;

    -- Calcular conversiones (16 paradas = 1 cuadra, 21 paradas = 1 hectárea)
    SET v_cuadras   = p_tamanio_paradas / 16;
    SET v_hectareas = p_tamanio_paradas / 21;

    INSERT INTO lotes (id_empresa, id_cliente, nombre, ubicacion, tamanio_paradas, tamanio_cuadras, tamanio_hectareas, temporada)
    VALUES (p_id_empresa, p_id_cliente, p_nombre, p_ubicacion, p_tamanio_paradas, v_cuadras, v_hectareas, p_temporada);

    SET v_lote_id = LAST_INSERT_ID();

    INSERT INTO auditoria (id_usuario, id_empresa, accion, tabla_afectada, id_registro)
    VALUES (p_id_usuario, p_id_empresa, 'INSERT', 'lotes', v_lote_id);

    SELECT v_lote_id AS id, 'Lote registrado exitosamente' AS mensaje, 1 AS success;
    COMMIT;
END$$

-- 5. Registrar etapa de cultivo
-- CORREGIDO: verifica que el lote no tenga ya una etapa activa
CREATE PROCEDURE sp_registrar_etapa(
    IN p_id_empresa INT,
    IN p_id_lote INT,
    IN p_tipo_cultivo VARCHAR(20),
    IN p_fecha_inicio DATE,
    IN p_id_usuario INT
)
BEGIN
    DECLARE v_etapa_id INT;
    DECLARE v_dias INT;
    DECLARE v_fecha_estimada DATE;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SELECT 'Error al registrar etapa' AS mensaje, 0 AS success;
    END;

    START TRANSACTION;

    -- Verificar que el lote no tenga una etapa en proceso
    IF EXISTS (
        SELECT 1 FROM etapas_cultivo
        WHERE id_lote = p_id_lote
        AND estado = 'en_proceso'
        AND deleted_at IS NULL
    ) THEN
        SELECT 'Este lote ya tiene una etapa de cultivo en proceso' AS mensaje, 0 AS success;
        ROLLBACK;
    ELSE
        -- siembra = 110 días, soca = 135 días
        IF p_tipo_cultivo = 'siembra' THEN
            SET v_dias = 110;
        ELSE
            SET v_dias = 135;
        END IF;

        SET v_fecha_estimada = DATE_ADD(p_fecha_inicio, INTERVAL v_dias DAY);

        INSERT INTO etapas_cultivo (id_empresa, id_lote, tipo_cultivo, fecha_inicio, fecha_fin_estimada, dias_duracion)
        VALUES (p_id_empresa, p_id_lote, p_tipo_cultivo, p_fecha_inicio, v_fecha_estimada, v_dias);

        SET v_etapa_id = LAST_INSERT_ID();

        INSERT INTO auditoria (id_usuario, id_empresa, accion, tabla_afectada, id_registro)
        VALUES (p_id_usuario, p_id_empresa, 'INSERT', 'etapas_cultivo', v_etapa_id);

        SELECT v_etapa_id AS id, v_fecha_estimada AS fecha_estimada, 'Etapa registrada exitosamente' AS mensaje, 1 AS success;
        COMMIT;
    END IF;
END$$

-- 6. Registrar aplicación de producto
-- CORREGIDO: incluye unidad_medida en el SELECT de retorno
CREATE PROCEDURE sp_registrar_aplicacion(
    IN p_id_empresa INT,
    IN p_id_etapa_cultivo INT,
    IN p_id_producto INT,
    IN p_fecha_aplicacion DATE,
    IN p_cantidad DECIMAL(10,2),
    IN p_dosis VARCHAR(50),
    IN p_metodo VARCHAR(100),
    IN p_observaciones TEXT,
    IN p_id_usuario INT
)
BEGIN
    DECLARE v_aplicacion_id INT;
    DECLARE v_stock_actual DECIMAL(10,2);
    DECLARE v_nombre_producto VARCHAR(100);
    DECLARE v_unidad VARCHAR(20);
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SELECT 'Error al registrar aplicación' AS mensaje, 0 AS success;
    END;

    START TRANSACTION;

    -- Verificar que la etapa esté en proceso
    IF NOT EXISTS (
        SELECT 1 FROM etapas_cultivo
        WHERE id = p_id_etapa_cultivo
        AND estado = 'en_proceso'
        AND deleted_at IS NULL
    ) THEN
        SELECT 'La etapa de cultivo no está activa' AS mensaje, 0 AS success;
        ROLLBACK;
    ELSE
        -- Verificar stock disponible
        SELECT stock, nombre, unidad_medida
        INTO v_stock_actual, v_nombre_producto, v_unidad
        FROM productos
        WHERE id = p_id_producto AND deleted_at IS NULL;

        IF v_stock_actual IS NULL THEN
            SELECT 'Producto no encontrado' AS mensaje, 0 AS success;
            ROLLBACK;
        ELSEIF v_stock_actual < p_cantidad THEN
            SELECT CONCAT('Stock insuficiente para ', v_nombre_producto, '. Disponible: ', v_stock_actual, ' ', v_unidad) AS mensaje, 0 AS success;
            ROLLBACK;
        ELSE
            INSERT INTO aplicaciones (id_empresa, id_etapa_cultivo, id_producto, fecha_aplicacion, cantidad, dosis, metodo_aplicacion, observaciones)
            VALUES (p_id_empresa, p_id_etapa_cultivo, p_id_producto, p_fecha_aplicacion, p_cantidad, p_dosis, p_metodo, p_observaciones);

            SET v_aplicacion_id = LAST_INSERT_ID();

            -- Descontar stock
            UPDATE productos SET stock = stock - p_cantidad WHERE id = p_id_producto;

            INSERT INTO auditoria (id_usuario, id_empresa, accion, tabla_afectada, id_registro)
            VALUES (p_id_usuario, p_id_empresa, 'INSERT', 'aplicaciones', v_aplicacion_id);

            SELECT v_aplicacion_id AS id, 'Aplicación registrada exitosamente' AS mensaje, 1 AS success;
            COMMIT;
        END IF;
    END IF;
END$$

-- 7. Finalizar etapa y registrar producción
CREATE PROCEDURE sp_finalizar_etapa(
    IN p_id_etapa INT,
    IN p_produccion_quintales DECIMAL(10,2),
    IN p_fecha_fin DATE,
    IN p_observaciones TEXT,
    IN p_id_usuario INT,
    IN p_id_empresa INT
)
BEGIN
    DECLARE v_dias_reales INT;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SELECT 'Error al finalizar etapa' AS mensaje, 0 AS success;
    END;

    START TRANSACTION;

    -- Verificar que la etapa exista y esté en proceso
    IF NOT EXISTS (
        SELECT 1 FROM etapas_cultivo
        WHERE id = p_id_etapa
        AND id_empresa = p_id_empresa
        AND estado = 'en_proceso'
        AND deleted_at IS NULL
    ) THEN
        SELECT 'Etapa no encontrada o ya finalizada' AS mensaje, 0 AS success;
        ROLLBACK;
    ELSE
        SELECT DATEDIFF(p_fecha_fin, fecha_inicio) INTO v_dias_reales
        FROM etapas_cultivo
        WHERE id = p_id_etapa;

        UPDATE etapas_cultivo
        SET estado               = 'finalizada',
            fecha_fin_real       = p_fecha_fin,
            produccion_quintales = p_produccion_quintales,
            dias_duracion        = v_dias_reales,
            observaciones        = p_observaciones
        WHERE id = p_id_etapa AND id_empresa = p_id_empresa;

        INSERT INTO auditoria (id_usuario, id_empresa, accion, tabla_afectada, id_registro)
        VALUES (p_id_usuario, p_id_empresa, 'UPDATE', 'etapas_cultivo', p_id_etapa);

        SELECT p_id_etapa AS id, 'Etapa finalizada exitosamente' AS mensaje, 1 AS success;
        COMMIT;
    END IF;
END$$

-- 8. Dashboard del ingeniero
CREATE PROCEDURE sp_dashboard_ingeniero(
    IN p_id_empresa INT
)
BEGIN
    -- Total clientes
    SELECT COUNT(*) AS total_clientes
    FROM clientes
    WHERE id_empresa = p_id_empresa AND deleted_at IS NULL;

    -- Total lotes
    SELECT COUNT(*) AS total_lotes
    FROM lotes
    WHERE id_empresa = p_id_empresa AND deleted_at IS NULL;

    -- Etapas en proceso
    SELECT COUNT(*) AS etapas_proceso
    FROM etapas_cultivo
    WHERE id_empresa = p_id_empresa AND estado = 'en_proceso' AND deleted_at IS NULL;

    -- Producción año actual
    SELECT COALESCE(SUM(produccion_quintales), 0) AS produccion_anual
    FROM etapas_cultivo
    WHERE id_empresa = p_id_empresa
      AND YEAR(fecha_fin_real) = YEAR(NOW())
      AND estado = 'finalizada'
      AND deleted_at IS NULL;

    -- Próximas cosechas (próximos 15 días)
    SELECT ec.*,
           l.nombre AS lote_nombre,
           CONCAT(c.nombre, ' ', c.apellido) AS cliente_nombre,
           DATEDIFF(ec.fecha_fin_estimada, NOW()) AS dias_restantes
    FROM etapas_cultivo ec
    INNER JOIN lotes l ON ec.id_lote = l.id
    INNER JOIN clientes c ON l.id_cliente = c.id
    WHERE ec.id_empresa = p_id_empresa
      AND ec.estado = 'en_proceso'
      AND ec.fecha_fin_estimada BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 15 DAY)
      AND ec.deleted_at IS NULL
    ORDER BY ec.fecha_fin_estimada ASC;
END$$

-- 9. Autenticar usuario
CREATE PROCEDURE sp_autenticar_usuario(
    IN p_username VARCHAR(50),
    IN p_password VARCHAR(255)
)
BEGIN
    DECLARE v_user_id INT;
    DECLARE v_stored_password VARCHAR(255);

    SELECT id, password INTO v_user_id, v_stored_password
    FROM usuarios
    WHERE username = p_username
      AND activo = 1
      AND deleted_at IS NULL;

    IF v_user_id IS NOT NULL THEN
        UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = v_user_id;

        SELECT u.*, e.nombre AS empresa_nombre
        FROM usuarios u
        LEFT JOIN empresas e ON u.id_empresa = e.id
        WHERE u.id = v_user_id;
    ELSE
        SELECT NULL AS id, 'Usuario no encontrado o inactivo' AS mensaje;
    END IF;
END$$

-- 10. Historial de aplicaciones por etapa
-- CORREGIDO: ahora incluye unidad_medida que usan el JS y las vistas
CREATE PROCEDURE sp_historial_aplicaciones(
    IN p_id_etapa INT,
    IN p_id_empresa INT
)
BEGIN
    SELECT
        a.*,
        p.nombre        AS producto_nombre,
        p.tipo          AS producto_tipo,
        p.unidad_medida AS unidad_medida
    FROM aplicaciones a
    INNER JOIN productos p ON a.id_producto = p.id
    WHERE a.id_etapa_cultivo = p_id_etapa
      AND a.id_empresa       = p_id_empresa
      AND a.deleted_at       IS NULL
    ORDER BY a.fecha_aplicacion DESC;
END$$

DELIMITER ;

-- =====================================================
-- DATOS INICIALES — USUARIOS Y EMPRESAS
-- =====================================================

-- Empresas
INSERT INTO empresas (nombre, ruc, direccion, telefono, email) VALUES
('Agroquímico Salazar', '0912345678001', 'Av. Principal 123, Guayaquil', '042-123456', 'salazar@agro.com'),
('Agroquímico Basurto', '0987654321001', 'Calle Comercio 456, Daule',   '042-654321', 'basurto@agro.com');

-- Administrador general
-- Contraseña: admin123  (hash bcrypt generado con password_hash('admin123', PASSWORD_BCRYPT))
INSERT INTO usuarios (id_empresa, username, password, nombre_completo, email, rol) VALUES
(NULL, 'admin',
 '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIXFlpBkpOf6ZlG',
 'Administrador General', 'admin@agrimanage.com', 'admin_general');

-- Ingenieros de cada empresa
-- Contraseña: 12345678  (hash bcrypt generado con password_hash('12345678', PASSWORD_BCRYPT))
CALL sp_crear_usuario(1, 'ing_salazar',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 'Ing. María García', 'maria@salazar.com', 'ingeniero');

CALL sp_crear_usuario(2, 'ing_basurto',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 'Ing. Juan Pérez', 'juan@basurto.com', 'ingeniero');

-- =====================================================
-- DATOS DE PRUEBA — EMPRESA 1 (Salazar)
-- =====================================================

-- Clientes empresa 1
INSERT INTO clientes (id_empresa, cedula, nombre, apellido, telefono, email, direccion) VALUES
(1, '0901234567', 'Pedro',   'Delgado',  '0991234567', 'pedro@gmail.com',  'Km 12 Vía Daule'),
(1, '0912345678', 'Rosa',    'Moreira',  '0982345678', 'rosa@gmail.com',   'Km 5 Vía Balzar'),
(1, '0923456789', 'Carlos',  'Intriago', '0973456789', 'carlos@gmail.com', 'Hacienda El Palmar'),
(1, '0934567890', 'Beatriz', 'Pacheco',  '0964567890', 'beatriz@gmail.com','Km 20 Vía Milagro');

-- Lotes empresa 1
-- sp_registrar_lote calcula cuadras y hectáreas automáticamente
INSERT INTO lotes (id_empresa, id_cliente, nombre, ubicacion, tamanio_paradas, tamanio_cuadras, tamanio_hectareas, temporada) VALUES
(1, 1, 'Lote Norte',    'Sector norte de la finca',  32.00, 2.00, 1.52, 'invierno'),
(1, 1, 'Lote Sur',      'Sector sur de la finca',    48.00, 3.00, 2.29, 'verano'),
(1, 2, 'Parcela A',     'Frente a la carretera',     21.00, 1.31, 1.00, 'invierno'),
(1, 3, 'Potrero Grande','Zona interior de la finca', 63.00, 3.94, 3.00, 'verano'),
(1, 4, 'Finca Central', 'Centro de la hacienda',     42.00, 2.63, 2.00, 'invierno');

-- Productos empresa 1
INSERT INTO productos (id_empresa, nombre, tipo, descripcion, unidad_medida, precio_unitario, stock) VALUES
(1, 'Glifosato 480 SL',    'herbicida',    'Herbicida sistémico de amplio espectro',       'litros',    8.50,  120.00),
(1, 'Mancozeb 80 WP',      'fungicida',    'Fungicida de contacto preventivo',             'kg',        6.20,   80.00),
(1, 'Clorpirifos 48 EC',   'insecticida',  'Insecticida organofosforado',                  'litros',   12.00,   60.00),
(1, 'Urea 46%',            'fertilizante', 'Fertilizante nitrogenado granulado',           'kg',        0.85, 1500.00),
(1, '10-30-10 Arranca',    'fertilizante', 'Fertilizante de arranque NPK',                 'kg',        1.20,  800.00),
(1, 'Propiconazol 25 EC',  'fungicida',    'Fungicida sistémico para manchas foliares',    'litros',   18.00,   35.00),
(1, 'Imidacloprid 35 SC',  'insecticida',  'Insecticida sistémico para plagas chupadores', 'litros',   22.00,   25.00),
(1, '18-46-0 DAP',         'fertilizante', 'Fosfato diamónico, fertilizante de siembra',  'kg',        1.45,  600.00);

-- Etapas de cultivo empresa 1
-- Una etapa finalizada para reportes históricos
INSERT INTO etapas_cultivo (id_empresa, id_lote, tipo_cultivo, fecha_inicio, fecha_fin_estimada, fecha_fin_real, dias_duracion, estado, produccion_quintales, observaciones) VALUES
(1, 1, 'siembra', DATE_SUB(CURDATE(), INTERVAL 130 DAY), DATE_SUB(CURDATE(), INTERVAL 20 DAY), DATE_SUB(CURDATE(), INTERVAL 18 DAY), 112, 'finalizada', 185.50, 'Cosecha exitosa, buen rendimiento'),
(1, 2, 'soca',    DATE_SUB(CURDATE(), INTERVAL 160 DAY), DATE_SUB(CURDATE(), INTERVAL 25 DAY), DATE_SUB(CURDATE(), INTERVAL 22 DAY), 138, 'finalizada', 220.00, 'Temporada de verano, rendimiento superior'),
(1, 3, 'siembra', DATE_SUB(CURDATE(), INTERVAL 80 DAY),  DATE_ADD(CURDATE(), INTERVAL 30 DAY),  NULL, NULL, 'en_proceso', NULL, NULL),
(1, 4, 'soca',    DATE_SUB(CURDATE(), INTERVAL 50 DAY),  DATE_ADD(CURDATE(), INTERVAL 85 DAY),  NULL, NULL, 'en_proceso', NULL, NULL),
(1, 5, 'siembra', DATE_SUB(CURDATE(), INTERVAL 10 DAY),  DATE_ADD(CURDATE(), INTERVAL 100 DAY), NULL, NULL, 'en_proceso', NULL, NULL);

-- Aplicaciones sobre etapas en proceso (empresa 1)
INSERT INTO aplicaciones (id_empresa, id_etapa_cultivo, id_producto, fecha_aplicacion, cantidad, dosis, metodo_aplicacion, observaciones) VALUES
-- Etapa 3 (lote 3, Parcela A — en proceso)
(1, 3, 1, DATE_SUB(CURDATE(), INTERVAL 75 DAY), 3.00, '3L/ha', 'Aspersión bomba', 'Control de malezas inicial'),
(1, 3, 5, DATE_SUB(CURDATE(), INTERVAL 70 DAY), 50.00, '50kg/ha', 'Aplicación manual', 'Fertilización de arranque'),
(1, 3, 2, DATE_SUB(CURDATE(), INTERVAL 40 DAY), 2.00, '2kg/ha', 'Aspersión bomba', 'Prevención de enfermedades foliares'),
-- Etapa 4 (lote 4, Potrero Grande — en proceso)
(1, 4, 4, DATE_SUB(CURDATE(), INTERVAL 45 DAY), 90.00, '30kg/ha', 'Aplicación mecánica', 'Primera fertilización nitrogenada'),
(1, 4, 3, DATE_SUB(CURDATE(), INTERVAL 30 DAY), 5.00, '1.5L/ha', 'Aspersión', 'Control de changa y cogollero'),
(1, 4, 6, DATE_SUB(CURDATE(), INTERVAL 15 DAY), 2.00, '0.5L/ha', 'Aspersión bomba', 'Control preventivo de roya');

-- =====================================================
-- DATOS DE PRUEBA — EMPRESA 2 (Basurto)
-- =====================================================

-- Clientes empresa 2
INSERT INTO clientes (id_empresa, cedula, nombre, apellido, telefono, email, direccion) VALUES
(2, '0945678901', 'Luis',   'Bravo',    '0955678901', 'luis@gmail.com',   'Hacienda San Luis, Naranjal'),
(2, '0956789012', 'Ana',    'Cañarte',  '0946789012', 'ana@gmail.com',    'Km 8 Vía Bucay'),
(2, '0967890123', 'Jorge',  'Muñoz',    '0937890123', 'jorge@gmail.com',  'Finca La Esperanza, Milagro');

-- Lotes empresa 2
INSERT INTO lotes (id_empresa, id_cliente, nombre, ubicacion, tamanio_paradas, tamanio_cuadras, tamanio_hectareas, temporada) VALUES
(2, 5, 'Lote San Luis',   'Zona baja de la hacienda', 42.00, 2.63, 2.00, 'invierno'),
(2, 5, 'Lote Alto',       'Zona alta de la hacienda', 32.00, 2.00, 1.52, 'verano'),
(2, 6, 'Parcela Bucay',   'Frente al río',            21.00, 1.31, 1.00, 'invierno'),
(2, 7, 'Finca Milagro 1', 'Sector norte',             63.00, 3.94, 3.00, 'verano');

-- Productos empresa 2
INSERT INTO productos (id_empresa, nombre, tipo, descripcion, unidad_medida, precio_unitario, stock) VALUES
(2, 'Paraquat 20 SL',      'herbicida',    'Herbicida de contacto de acción rápida',       'litros',   14.00,   90.00),
(2, 'Azoxistrobina 25 SC', 'fungicida',    'Fungicida sistémico de amplio espectro',       'litros',   28.00,   40.00),
(2, 'Cipermetrina 25 EC',  'insecticida',  'Insecticida piretroide de amplio espectro',    'litros',    9.50,   75.00),
(2, 'Muriato de Potasio',  'fertilizante', 'Fertilizante potásico granulado KCl 60%',     'kg',        0.95,  900.00),
(2, 'Sulfato de Amonio',   'fertilizante', 'Fertilizante nitrogenado 21% N',              'kg',        0.65, 1200.00);

-- Etapas empresa 2
INSERT INTO etapas_cultivo (id_empresa, id_lote, tipo_cultivo, fecha_inicio, fecha_fin_estimada, fecha_fin_real, dias_duracion, estado, produccion_quintales, observaciones) VALUES
(2, 6, 'siembra', DATE_SUB(CURDATE(), INTERVAL 120 DAY), DATE_SUB(CURDATE(), INTERVAL 10 DAY), DATE_SUB(CURDATE(), INTERVAL 8 DAY), 112, 'finalizada', 168.00, 'Buena cosecha'),
(2, 7, 'siembra', DATE_SUB(CURDATE(), INTERVAL 60 DAY),  DATE_ADD(CURDATE(), INTERVAL 50 DAY), NULL, NULL, 'en_proceso', NULL, NULL),
(2, 8, 'soca',    DATE_SUB(CURDATE(), INTERVAL 30 DAY),  DATE_ADD(CURDATE(), INTERVAL 105 DAY),NULL, NULL, 'en_proceso', NULL, NULL);

-- =====================================================
-- AUDITORIA INICIAL (registro de creación de datos de prueba)
-- =====================================================

INSERT INTO auditoria (id_usuario, id_empresa, accion, tabla_afectada, id_registro, datos_nuevos) VALUES
(2, 1, 'INSERT', 'clientes',         1, 'Datos de prueba iniciales'),
(2, 1, 'INSERT', 'lotes',            1, 'Datos de prueba iniciales'),
(2, 1, 'INSERT', 'productos',        1, 'Datos de prueba iniciales'),
(3, 2, 'INSERT', 'clientes',         5, 'Datos de prueba iniciales'),
(3, 2, 'INSERT', 'productos',        9, 'Datos de prueba iniciales');