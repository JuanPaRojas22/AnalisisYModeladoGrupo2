<?php
session_start();
include 'template.php';
require 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_usuario = $_POST['id_usuario'];
    $fecha = $_POST['fecha'];
    $motivo = $_POST['motivo'];
    $registrado_por = $_SESSION['id_usuario']; // ID del administrador que registra la ausencia

    $query = "INSERT INTO Ausencias (id_usuario, fecha, motivo, registrado_por) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("issi", $id_usuario, $fecha, $motivo, $registrado_por);

    if ($stmt->execute()) {
        echo "<script>alert('Ausencia registrada correctamente.');</script>";
    } else {
        echo "<script>alert('Error al registrar la ausencia.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Ausencia</title>
</head>

<body>

    <div class="container">
        <h1>Registrar Ausencia</h1>
        <form method="POST" action="">
        <div class="select-container">
            <label for="id_usuario">Empleado:</label>
            <select name="id_usuario" required>
                <?php
                $result = $conn->query("SELECT id_usuario, nombre FROM Usuario");
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='{$row['id_usuario']}'>{$row['nombre']}</option>";
                }
                ?>
            </select>
            
                <label for="fecha">Fecha:</label>
                <input type="date" name="fecha" required>

                <label for="motivo">Motivo:</label>
                <input type="text" name="motivo" required>

                <button class="btn" type="submit">Registrar Ausencia</button>
        </form>
    </div>
    </div>
</body>

</html>

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

    h1 {
        text-align: center;
        color: #333;
        margin-bottom: 50px;
        margin-right: 10%;
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