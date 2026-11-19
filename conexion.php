<?php
// Configuración de la base de datos
$host = "localhost";
$usuario = "root";
$password = "";
$base_datos = "gestor_recetas";

// Conexión directa a MySQL usando mysqli
$conexion = new mysqli($host, $usuario, $password, $base_datos);

// Verificar conexión
if ($conexion->connect_error) {
    die("Error al conectar con la base de datos: " . $conexion->connect_error);
}

// Establecer conjunto de caracteres utf8mb4 para tildes y caracteres especiales
$conexion->set_charset("utf8mb4");
?>
