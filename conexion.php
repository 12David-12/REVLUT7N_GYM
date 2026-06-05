<?php
/**
 * ARCHIVO DE CONEXIÓN A LA BASE DE DATOS
 * 
 * Este archivo conecta la aplicación con la base de datos MySQL
 * Incluye manejo de errores y configuración de caracteres
 */

// Datos de conexión
$host = 'localhost';      // Host del servidor
$usuario = 'root';        // Usuario de MySQL
$contrasena = 'Jdavid10.'; // Contraseña de MySQL
$base_datos = 'sisgym';   // Nombre de la base de datos
$puerto = 3306;           // Puerto de MySQL (por defecto)

// Crear conexión con MySQLi (procedural)
$conexion = new mysqli($host, $usuario, $contrasena, $base_datos, $puerto);

// Verificar conexión
if ($conexion->connect_error) {
    // Si hay error, mostrar mensaje y detener
    die("Error de conexión: " . $conexion->connect_error);
}

// Configurar el conjunto de caracteres a UTF-8
// Esto permite mostrar correctamente acentos, ñ, etc.
$conexion->set_charset("utf8");

/**
 * NOTA IMPORTANTE:
 * Esta conexión se usa en otros archivos PHP con:
 * include 'conexion.php';
 * 
 * Ejemplo:
 * include 'conexion.php';
 * $resultado = $conexion->query("SELECT * FROM empleados");
 */
?>
