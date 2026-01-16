<?php
// config/config.php
define('BASE_URL', 'http://localhost/agrimanage/');
define('APP_NAME', 'AgriManage');
define('APP_VERSION', '1.0.0');

// Configuración de zona horaria
date_default_timezone_set('America/Guayaquil');

// Configuración de sesión
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Cambiar a 1 en producción con HTTPS

session_start();

// Función para verificar sesión activa
function checkAuth() {
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: ' . BASE_URL . 'login.php');
        exit();
    }
}

// Función para verificar rol
function checkRole($roles_permitidos) {
    if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], $roles_permitidos)) {
        header('Location: ' . BASE_URL . 'dashboard.php');
        exit();
    }
}

// Función para sanitizar inputs
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Conversiones de medidas
define('PARADAS_POR_CUADRA', 16);
define('PARADAS_POR_HECTAREA', 21);
define('METROS_POR_PARADA', 21);

function paradasACuadras($paradas) {
    return $paradas / PARADAS_POR_CUADRA;
}

function paradasAHectareas($paradas) {
    return $paradas / PARADAS_POR_HECTAREA;
}

function cuadrasAParadas($cuadras) {
    return $cuadras * PARADAS_POR_CUADRA;
}

function hectareasAParadas($hectareas) {
    return $hectareas * PARADAS_POR_HECTAREA;
}

// Validación de cédula ecuatoriana
function validarCedulaEcuatoriana($cedula) {
    // Remover espacios y guiones
    $cedula = preg_replace('/[^0-9]/', '', $cedula);
    
    // Debe tener 10 dígitos
    if (strlen($cedula) != 10) {
        return false;
    }
    
    // Los dos primeros dígitos deben estar entre 01 y 24
    $provincia = substr($cedula, 0, 2);
    if ($provincia < 1 || $provincia > 24) {
        return false;
    }
    
    // El tercer dígito debe ser menor a 6
    if ($cedula[2] > 5) {
        return false;
    }
    
    // Algoritmo de validación
    $coeficientes = [2, 1, 2, 1, 2, 1, 2, 1, 2];
    $suma = 0;
    
    for ($i = 0; $i < 9; $i++) {
        $valor = $cedula[$i] * $coeficientes[$i];
        if ($valor > 9) {
            $valor -= 9;
        }
        $suma += $valor;
    }
    
    $digito_verificador = (10 - ($suma % 10)) % 10;
    
    return $digito_verificador == $cedula[9];
}

// Calcular días estimados según tipo de cultivo
function calcularDiasEstimados($tipo_cultivo) {
    switch ($tipo_cultivo) {
        case 'siembra':
            return 110; // Aproximadamente 4 meses
        case 'soca':
            return 135; // Aproximadamente 4 meses + 2 semanas
        default:
            return 110;
    }
}

// Formatear fecha
function formatearFecha($fecha) {
    if (!$fecha) return '-';
    $timestamp = strtotime($fecha);
    return date('d/m/Y', $timestamp);
}

// Formatear número
function formatearNumero($numero, $decimales = 2) {
    return number_format($numero, $decimales, '.', ',');
}
?>