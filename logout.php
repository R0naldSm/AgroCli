<?php
// logout.php - CERRAR SESIÓN (Raíz del proyecto)
session_start();

// Guardar mensaje de despedida
$nombre = isset($_SESSION['nombre_completo']) ? $_SESSION['nombre_completo'] : 'Usuario';

// Destruir todas las variables de sesión
$_SESSION = array();

// Destruir la cookie de sesión
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
}

// Destruir la sesión
session_destroy();

// Iniciar nueva sesión limpia para el mensaje
session_start();
$_SESSION['logout_message'] = "Hasta luego, {$nombre}. Sesión cerrada correctamente.";

// Redirigir al login
header('Location: login.php');
exit();
?>