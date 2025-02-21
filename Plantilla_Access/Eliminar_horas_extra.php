<?php
session_start();
include "template.php";
require 'conexion.php';

// Si se elimina una hora extra
if (isset($_POST['eliminar_hora_extra'], $_POST['id_hora_extra'], $_POST['id_usuario'])) {
    $id_hora_extra = $_POST['id_hora_extra']; // ID de la hora extra a eliminar
    $id_usuario = $_POST['id_usuario']; // ID del usuario

    // Verificar si la hora extra existe
    $query_hora_extra = "SELECT horas, monto_pago FROM horas_extra WHERE id_horas_extra = ? AND id_usuario = ?";
    if ($stmt_hora_extra = $conn->prepare($query_hora_extra)) {
        $stmt_hora_extra->bind_param("ii", $id_hora_extra, $id_usuario);
        $stmt_hora_extra->execute();
        $stmt_hora_extra->bind_result($horas_extra, $monto_pago);
        $stmt_hora_extra->fetch();
        $stmt_hora_extra->close();

        if ($horas_extra && $monto_pago) {
            // Eliminar la hora extra
            $query_eliminar = "DELETE FROM horas_extra WHERE id_horas_extra = ?";
            if ($stmt_eliminar = $conn->prepare($query_eliminar)) {
                $stmt_eliminar->bind_param("i", $id_hora_extra);
                $stmt_eliminar->execute();
                $stmt_eliminar->close();

                // Restablecer el salario neto en la tabla de planilla (eliminando el monto de las horas extras)
                $query_actualizar_salario = "UPDATE planilla SET salario_neto = salario_neto - ? WHERE id_usuario = ?";
                if ($stmt_actualizar_salario = $conn->prepare($query_actualizar_salario)) {
                    $stmt_actualizar_salario->bind_param("di", $monto_pago, $id_usuario);
                    $stmt_actualizar_salario->execute();
                    $stmt_actualizar_salario->close();
                }
                $_SESSION['mensaje_exito'] = "La hora extra ha sido eliminada correctamente.";


            }
        }
    } else {
        // Mensaje de error si no se encuentra la hora extra
        $_SESSION['mensaje_error'] = "Hubo un problema al eliminar la hora extra. Intenta de nuevo.";
    }



}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <title>Registar Horas Extra</title>


    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Bootstrap core CSS -->
    <link href="assets/css/bootstrap.css" rel="stylesheet">
    <!--external css-->
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="assets/css/zabuto_calendar.css">
    <link rel="stylesheet" type="text/css" href="assets/js/gritter/css/jquery.gritter.css" />
    <link rel="stylesheet" type="text/css" href="assets/lineicons/style.css">

    <!-- Custom styles for this template -->
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/style-responsive.css" rel="stylesheet">

</head>

<body>
    <section id="main-content">
        <section class="wrapper site-min-height">
            <div class="container">
                <a href="VerPlanilla.php" class="button"><i class="bi bi-arrow-return-left"></i>
                </a>
                <?php
                // Mostrar el mensaje de éxito o error
                if (isset($_SESSION['mensaje_exito'])) {
                    echo "<div class='alert alert-success'>" . $_SESSION['mensaje_exito'] . "</div>";
                    unset($_SESSION['mensaje_exito']);
                }

                if (isset($_SESSION['mensaje_error'])) {
                    echo "<div class='alert alert-danger'>" . $_SESSION['mensaje_error'] . "</div>";
                    unset($_SESSION['mensaje_error']);
                }
                ?>

                <h1>Listado de Horas Extras</h1>

                <table>
                    <thead>
                        <tr>

                            <th>Empleado</th>
                            <th>Usuario</th>
                            <th>Horas Extra</th>
                            <th>Monto Pago</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Mostrar los resultados de las horas extras
                        $sql_horas_extra = "SELECT u.username, u.nombre, u.apellido, 
                        SECOND(h.horas) AS horas, 
                        h.monto_pago, h.id_horas_extra, h.id_usuario 
                 FROM horas_extra h 
                 INNER JOIN usuario u ON h.id_usuario = u.id_usuario";
                        $result_horas_extra = $conn->query($sql_horas_extra);

                        if ($result_horas_extra->num_rows > 0) {
                            while ($row = $result_horas_extra->fetch_assoc()) {
                                echo "<tr>
                            <td>" . $row['nombre'], " ", $row['apellido'] . "</td>
                            <td>" . $row['username'] . "</td>
                            <td>" . $row['horas'] . "</td>
                            <td>¢" . number_format($row['monto_pago'], 2) . "</td>
                            <td>
                                <form method='POST' action=''>
                                    <input type='hidden' name='id_hora_extra' value='" . $row['id_horas_extra'] . "'>
                                    <input type='hidden' name='id_usuario' value='" . $row['id_usuario'] . "'>
                                    <button type='submit' name='eliminar_hora_extra' class='btn btn-danger'>
                                    <i class='bi bi-trash'></i></button>
                                </form>
                            </td>
                        </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4' class='no-records'>No se encontraron registros de horas extras.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </section>
    </section>
</body>
<style>
    body {
        font-family: 'Ruda', sans-serif;
        background-color: #f7f7f7;
        margin: 0;
        padding: 0;
    }

    .container {
        width: 80%;
        margin: 100px auto;
        padding: 20px;
        background-color: #ffffff;
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.6);


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

    .button {
        display: inline-block;
        background-color: #c9aa5f;
        color: white;
        padding: 10px 20px;
        font-size: 16px;
        font-weight: bold;
        text-align: center;
        text-decoration: none;
        border-radius: 5px;
        margin-bottom: 20px;
        transition: background-color 0.3s;
    }

    .btn {
        display: inline-block;
        color: white;
        padding: 10px 20px;
        font-size: 25px;
        font-weight: bold;
        text-align: center;
        text-decoration: none;
        border-radius: 5px;
        margin-bottom: 20px;
        transition: background-color 0.3s;
    }



    .btn:hover {}

    .btn:active {}

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
        text-align: center;
        font-size: 16px;
        color: #555;
        border-bottom: 1px solid #ddd;
    }

    th {
        background-color: #c9aa5f;
        color: #fff;
        text-align: center;
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
</style>

</html>