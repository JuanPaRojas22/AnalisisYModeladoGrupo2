<?php
session_start();
require 'conexion.php';
include 'template.php';

$message = '';
$status = '';

// Consulta para calcular la antigüedad y registrar beneficios
$query = "
    INSERT INTO Beneficios (id_usuario, razon, monto, fechacreacion, usuariocreacion)
    SELECT 
        u.id_usuario,
        'Bono por Antigüedad' AS razon,
        CASE 
            WHEN TIMESTAMPDIFF(YEAR, u.fecha_ingreso, CURDATE()) >= 10 THEN 1000
            WHEN TIMESTAMPDIFF(YEAR, u.fecha_ingreso, CURDATE()) >= 5 THEN 500
            ELSE 0
        END AS monto,
        CURDATE() AS fechacreacion,
        'admin' AS usuariocreacion
    FROM Usuario u
    WHERE TIMESTAMPDIFF(YEAR, u.fecha_ingreso, CURDATE()) >= 1
    AND NOT EXISTS (
        SELECT 1 FROM Beneficios b 
        WHERE b.id_usuario = u.id_usuario AND b.razon = 'Bono por Antigüedad'
    )
";

if ($conn->query($query) === TRUE) {
    $status = 'success';
    $message = 'Beneficios registrados correctamente.';
} else {
    $status = 'error';
    $message = 'Error al registrar beneficios: ' . $conn->error;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Beneficios por Antigüedad</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container " >
        <!-- Mostrar mensaje de éxito o error -->
        <?php if ($status === 'success'): ?>
            <div class="alert alert-success text-center">
                <h4><?= htmlspecialchars($message) ?></h4>
            </div>
        <?php elseif ($status === 'error'): ?>
            <div class="alert alert-danger text-center">
                <h4><?= htmlspecialchars($message) ?></h4>
            </div>
        <?php endif; ?>

        <!-- Contenido adicional si es necesario -->
        <div class="text-center mt-3">
        <a href="registrarBeneficiosAntiguedad.php" class="btn btn-primary" style="background-color: #147964; color: white; padding: 12px 20px; font-size: 16px; font-weight: bold; text-decoration: none; border-radius: 5px; transition: background-color 0.3s;">
    Registrar Nuevos Beneficios
</a>
        </div>
    </div>
</body>
</html>

<style>
    body {
        font-family: 'Ruda', sans-serif;
        background-color: #f7f7f7;  /* Blanco cremoso */
        margin: 0;
        padding: 0;
    }

    .card-body {
        padding: 27px;
        margin-bottom: 0;
        background-color: #f7f7f7;  /* Blanco cremoso */

        /* Eliminar margen inferior */
        padding-bottom: 0;
        /* Eliminar padding inferior */
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.6);
    }

    .card-footer {
        margin-top: 0;
        /* Si tienes una sección card-footer, asegúrate de que no tenga márgenes */
    }

    select {
        width: 70%;
        padding: 10px;
        font-size: 16px;
        border: 2px solid rgb(15, 15, 15);
        border-radius: 5px;
        background: #f9f9f9;
        cursor: pointer;
        transition: all 0.3s ease;
        text-align: center;
    }

    select:hover {
        border-color: #a88c4a;
    }

    select:focus {
        outline: none;
        border-color: #106469;
        box-shadow: #106469;
    }

    .container {
        display: flex;
        flex-direction: column;
        background-color: #f7f7f7;  /* Blanco cremoso */
        margin-top: 10%;
        justify-content: flex-start;
        /* Alinea hacia la parte superior */
        align-items: center;
        /* Centra los elementos horizontalmente */
        padding: 10px;
        max-width: 50%;
        box-shadow: 0 0 5px rgba(0, 0, 0, 0.6);
        border-radius: 10px;
    }


    .row {
        display: flex;
        justify-content: center;
        align-items: center;
       
    }

    h1 {
        text-align: center;
        color: #333;
        margin-bottom: 50px;
        margin-right: 10%;
        font-weight: bold;
    }

    h3 {
        text-align: center;
        color: black;
        margin-bottom: 50px;
        margin-right: 10%;
        font-weight: bold;
    }
    h5{
        color: black;
        font-weight: bold;
    }

    .btn {
        display: inline-block;
                        background-color: #106469;
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
    }



    .btn:hover {
        background-color: #106469;
    }

    .btn:active {
        background-color: #106469;
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
        background-color: #f7f7f7;  /* Blanco cremoso */
        color: #fff;
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
        background-color: #106469;
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