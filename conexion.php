<?php
/**
 * conexion.php
 * Devuelve un objeto mysqli conectado con SSL a Azure MySQL.
 */

function obtenerConexion(): mysqli
{
    $host     = "accespersoneldb.mysql.database.azure.com";
    $user     = "adminUser";
    $password = "admin123+";
    $dbname   = "gestionEmpleados";
    $port     = 3306;

    // Conexión sin SSL (sin opciones ni certificados)
    $conn = new mysqli($host, $user, $password, $dbname, $port);

    if ($conn->connect_error) {
        die("❌ Conexión fallida: " . $conn->connect_error);
    }

    $conn->set_charset("utf8mb4");

    return $conn;
}

?>
