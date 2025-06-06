<?php
$host = "localhost";      // Servidor
$user = "root";           // Usuario de la base de datos
$pass = "";               // Contraseña del usuario
$db   = "rcdb";           // Nombre de la base de datos

$conn = new mysqli($host, $user, $pass, $db);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>
