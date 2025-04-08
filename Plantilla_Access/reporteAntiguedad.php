<?php
session_start();
require 'conexion.php';
include 'template.php';
// Consulta para calcular la antigüedad y obtener los beneficios
$query = "
    SELECT 
        u.id_usuario,
        CONCAT(u.nombre, ' ', u.apellido) AS nombre_completo,
        u.fecha_ingreso,
        TIMESTAMPDIFF(YEAR, u.fecha_ingreso, CURDATE()) AS antiguedad_anios,
        TIMESTAMPDIFF(MONTH, u.fecha_ingreso, CURDATE()) % 12 AS antiguedad_meses,
        b.razon AS beneficio,
        b.monto AS monto_beneficio,
        b.fechacreacion AS fecha_beneficio
    FROM Usuario u
    LEFT JOIN Beneficios b ON u.id_usuario = b.id_usuario AND b.razon = 'Bono por Antigüedad'
    WHERE u.fecha_ingreso IS NOT NULL
    ORDER BY antiguedad_anios DESC, antiguedad_meses DESC
";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Antigüedad Laboral</title>

  
</head>
<style>
    body {
        font-family: 'Ruda', sans-serif;
        background-color: #f7f7f7;
        /* Blanco cremoso */
        margin: 0;
        padding: 0;
    }

    .card-body {
        padding: 27px;
        margin-bottom: 0;
        background-color: #f7f7f7;
        /* Blanco cremoso */

        /* Eliminar margen inferior */
        padding-bottom: 0;
        /* Eliminar padding inferior */
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.6);
    }

    .card-footer {
        margin-top: 0;
        /* Si tienes una sección card-footer, asegúrate de que no tenga márgenes */
    }

    .selec {
        width: 100%;
        padding: 5px;
        font-size: 16px;
        border: 2px solid rgb(15, 15, 15);
        border-radius: 5px;
        background: #f9f9f9;
        cursor: pointer;
        transition: all 0.3s ease;
        text-align: center;
        margin-left: 10%;

    }

    .select-container {
        width: 100%;
        padding: 5px;
        font-size: 16px;
        color: black;
        border: 2px solid rgb(15, 15, 15);
        border-radius: 5px;
        background: #f9f9f9;
        cursor: pointer;
        transition: all 0.3s ease;
        text-align: center;
        margin-left: 5%;
    }

    select:hover {
        border-color: #a88c4a;
    }

    select:focus {
        outline: none;
        border-color: #805d24;
        box-shadow: 0 0 5px rgba(200, 150, 60, 0.6);
    }

    .container {
        display: flex;
        flex-direction: column;
        background-color: #f7f7f7;
        /* Blanco cremoso */
        margin-top: 10%;
        justify-content: flex-start;
        /* Alinea hacia la parte superior */
        align-items: center;
        /* Centra los elementos horizontalmente */
        padding: 10px;
        max-width: 100%;
        box-shadow: 0 0 5px rgba(0, 0, 0, 0.6);
    }


    .row {
        display: flex;
        justify-content: center;
        align-items: center;

    }

    h2 {
        text-align: center;
        color: #333;
        margin-bottom: 50px;
      
        font-weight: bold;
    }

    label {
        text-align: center;
        color: black;
        margin-bottom: 50px;
        font-weight: bold;
    }

    h5 {
        color: black;
        font-weight: bold;
    }

    .btn {
        display: inline-block;
        background-color:rgb(182, 155, 94);
        color: white;
        padding: 12px 20px;
        font-size: 16px;
        font-weight: bold;
        text-decoration: none;
        border-radius: 5px;
        margin-bottom: 20px;
        transition: background-color 0.3s;
        cursor: pointer;
        border: none;
        margin-top: 2%;
    }



    .btn:hover {
        background-color: #c9aa5f;
    }

    .btn:active {
        background-color: #c9aa5f;
    }


    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        border-radius: 8px;
        overflow: hidden;
    }

    th,
    td {
        padding: 12px;
        text-align: left;
        font-size: 16px;
        color: #555;
        border-bottom: 1px solid #ddd;
    }

    th {
        background-color: #f7f7f7;
        /* Blanco cremoso */
        color: black;
    }

    tr:hover {
        background-color: #f1f1f1;
    }

    td {
        background-color: #f9f9f9;
    }

    .no-records {
        text-align: center;
        font-style: italic;
        color: #888;
    }

    /* Estilos del fondo del modal */
    .modal {
        display: none;
        position: fixed;
        z-index: 1;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        justify-content: center;
        align-items: center;
    }

    /* Contenido del modal */
    .modal-content {
        background-color: white;
        padding: 20px;
        border-radius: 10px;
        width: 300px;
        text-align: center;
        margin-bottom: 5%;

    }

    /* Botón de cerrar */
    .close {
        position: absolute;
        top: 10px;
        right: 20px;
        font-size: 25px;
        cursor: pointer;
    }

    /* Botones dentro del modal */
    .modal-content a {
        display: block;
        margin: 10px 0;
        padding: 10px;
        text-decoration: none;
        color: white;
        background-color: gray;
        border-radius: 5px;
        background-color: #c9aa5f;
    }

    .modal-content a:hover {
        background-color: darkgray;
    }

    /* Estilos para los botones alineados */
    .button-container {
        display: flex;
        justify-content: space-between;
        /* Distribuye el espacio entre los botones */
        width: 100%;
    }

    .close-button {
        border: none;
        display: inline-block;
        padding: 8px 16px;
        vertical-align: middle;
        overflow: hidden;
        text-decoration: none;
        color: inherit;
        background-color: inherit;
        text-align: center;
        cursor: pointer;
        white-space: nowrap
    }

    .topright {
        position: absolute;
        right: 0;
        top: 0
    }
</style>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Reporte de Antigüedad Laboral</h2>
        <form method="POST" action="exportarAntiguedad.php">
    <button type="submit" class="btn btn-success">Exportar Reporte</button>
</form>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre Completo</th>
                    <th>Fecha de Ingreso</th>
                    <th>Antigüedad (Años)</th>
                    <th>Antigüedad (Meses)</th>
                    <th>Beneficio</th>
                    <th>Monto del Beneficio</th>
                    <th>Fecha del Beneficio</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id_usuario'] ?></td>
                        <td><?= htmlspecialchars($row['nombre_completo']) ?></td>
                        <td><?= $row['fecha_ingreso'] ?></td>
                        <td><?= $row['antiguedad_anios'] ?></td>
                        <td><?= $row['antiguedad_meses'] ?></td>
                        <td><?= htmlspecialchars($row['beneficio'] ?? 'N/A') ?></td>
                        <td><?= $row['monto_beneficio'] ?? 'N/A' ?></td>
                        <td><?= $row['fecha_beneficio'] ?? 'N/A' ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>