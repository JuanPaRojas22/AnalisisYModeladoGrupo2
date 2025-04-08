<?php
ob_start();  // Inicia el búfer de salida
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
require 'conexion.php';
include "template.php";


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $metodo_pago = $_POST["metodo_pago"];
    $fecha_pago = date("Y-m-d");

    // Calcular el aguinaldo para todos los usuarios
    $query_calculo = "SELECT id_usuario, 
                             SUM(salario_base) AS total_salario, 
                             SUM(total_bonos) AS total_bonos, 
                             SUM(pago_horas_extras) AS total_horas_extras 
                      FROM pago_planilla 
                      WHERE fecha_pago >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) 
                      GROUP BY id_usuario";

    // Verifica si la conexión sigue abierta antes de ejecutar la consulta
    if ($conn->ping()) {
        $stmt = $conn->prepare($query_calculo);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $id_usuario = $row["id_usuario"];
            $total_sum = $row["total_salario"] + $row["total_bonos"] + $row["total_horas_extras"];

            // Calcular el aguinaldo (dividir entre 12)
            $aguinaldo = $total_sum / 12;

            // Verificar si ya existe un aguinaldo registrado en el mismo año para este usuario
            $year = date("Y");
            $query_check = "SELECT COUNT(*) FROM historial_aguinaldo WHERE id_usuario = ? AND YEAR(fecha_pago) = ?";
            $stmt_check = $conn->prepare($query_check);
            $stmt_check->bind_param("is", $id_usuario, $year);
            $stmt_check->execute();
            $stmt_check->bind_result($count);
            $stmt_check->fetch();
            $stmt_check->close();

            // Si ya existe un aguinaldo para este año, no hacer nada
            if ($count > 0) {
                header("Location: Calcular_aguinaldo.php?aguinaldo_registrado=error");
            } else {
                // Insertar el pago del aguinaldo en la tabla historial_aguinaldo
                $query_insert = "INSERT INTO historial_aguinaldo (id_usuario, total_aguinaldo, fecha_pago, metodo_pago) 
                                 VALUES (?, ?, ?, ?)";
                $stmt_insert = $conn->prepare($query_insert);
                $stmt_insert->bind_param("idss", $id_usuario, $aguinaldo, $fecha_pago, $metodo_pago);

                if ($stmt_insert->execute()) {
                    // Mostrar el total sumado y el aguinaldo calculado para depuración
                    echo "<br>ID Usuario: " . $id_usuario . "<br>";
                    echo "Total sumado: " . $total_sum . "<br>";
                    echo "Aguinaldo calculado: " . $aguinaldo . "<br>";
                    echo "Método de pago: " . $metodo_pago . "<br>";
                    echo "Fecha de pago: " . $fecha_pago . "<br>";
                    echo "Aguinaldo registrado correctamente para el usuario con ID: " . $id_usuario . "<br>";
                } else {
                    echo "Error al registrar el aguinaldo para el usuario con ID: " . $id_usuario . "<br>";
                }

                $stmt_insert->close();
            }
        }

        // Cerrar el statement principal
        $stmt->close();
    } else {
        echo "La conexión a la base de datos ha fallado. Intente nuevamente.";
    }

    // Cerrar la conexión después de que todo haya terminado

}
ob_end_flush();  // Envía todo el contenido del búfer al navegador

?>



<head>


    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<section id="main-content">
    <section class="wrapper site-min-height">
        <div class="container">

            <h1>Listado de Aguinaldos</h1>

            <!-- Mostrar tabla con los datos de historial de aguinaldo -->

            <form action="calcular_aguinaldo.php" method="post">
                <label class="h1" style="color: black;" for="metodo_pago">Método de Pago:</label>
                <select name="metodo_pago" required>
                    <option value="Transferencia">Transferencia</option>
                    <option value="Efectivo">Efectivo</option>
                    <option value="Cheque">Cheque</option>
                </select>

                <button class='btn' type="submit"><i class="bi bi-calculator"></i></button>
            </form>
            <div>
               
                    <?php

                    $year = date("Y");  // Asigna el año actual
                    
                    // Mostrar el mensaje si el aguinaldo ya ha sido registrado
                    if (isset($_GET['aguinaldo_registrado']) && $_GET['aguinaldo_registrado'] == 'error'): ?>
                        <div class="alert alert-success mt-3 text-center mx-auto text-dark ">
                            ¡Aguinaldos ya registrados para el año
                            <?php echo $year; ?>!
                        </div>
                    <?php endif; ?>
            </div>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Correo</th>
                    <th>Aguinaldo Total</th>
                    <th>Fecha de Pago</th>
                    <th>Método de Pago</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Consulta para obtener el historial de aguinaldos
                $query_historial_aguinaldo = "SELECT u.nombre, u.apellido, u.correo_electronico, 
                                      h.total_aguinaldo, h.fecha_pago, h.metodo_pago
                                      FROM historial_aguinaldo h
                                      JOIN usuario u ON h.id_usuario = u.id_usuario
                                      ORDER BY h.fecha_pago DESC";

                // Ejecutar la consulta
                $result_historial = $conn->query($query_historial_aguinaldo);

                // Mostrar los resultados de la consulta
                if ($result_historial->num_rows > 0) {
                    while ($row = $result_historial->fetch_assoc()) {
                        echo "<tr>
                        <td>" . $row['nombre'] . "</td>
                        <td>" . $row['apellido'] . "</td>
                        <td>" . $row['correo_electronico'] . "</td>
                        <td>" . $row['total_aguinaldo'] . "</td>
                        <td>" . $row['fecha_pago'] . "</td>
                        <td>" . $row['metodo_pago'] . "</td>
                      </tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' class='no-records'>No se encontraron registros de aguinaldo.</td></tr>";
                }
                ?>
            </tbody>
        </table>










        <style>
            body {
                font-family: 'Ruda', sans-serif;
                background-color: #f7f7f7;
                /* Blanco cremoso */
                margin: 0;
                padding: 0;

            }

            select {
                width: 25%;
                padding: 10px;
                font-size: 16px;
                border: 2px solid rgb(15, 15, 15);
                border-radius: 5px;
                background: #f9f9f9;
                color: black;
                cursor: pointer;
                transition: all 0.3s ease;
                text-align: center;
            }

            .container {
                display: flex;
                flex-direction: column;
                background-color: #f7f7f7;
                /* Blanco cremoso */

                justify-content: flex-start;
                /* Alinea hacia la parte superior */
                align-items: center;
                /* Centra los elementos horizontalmente */
                padding: 10px;
                max-width: 100%;
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.6);
            }


            .row {
                display: flex;
                justify-content: center;
                align-items: center;

            }

            h1 {
                text-align: center;
                color: black;
                margin-bottom: 50px;
                margin-right: 2%;
                font-weight: bold;
            }

            h3 {
                text-align: center;
                color: black;
                margin-bottom: 50px;
                margin-right: 10%;
                font-weight: bold;
            }

            .btn {
                display: inline-block;
                background-color: #c9aa5f;
                color: #f7f7f7;
                padding: 10px 20px;
                font-size: 25px;
                font-weight: bold;
                text-align: center;
                text-decoration: none;
                border-radius: 5px;
                margin-bottom: 20px;
                margin-top: 20px;

                transition: background-color 0.3s;
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.6);

            }



            .btn:hover {
                background-color: #c9aa5f;
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.6);

            }

            .btn:active {
                background-color: #c9aa5f;
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.6);

            }

            table {
                width: 70%;
                border-collapse: collapse;
                margin-top: 20px;
                border-radius: 8px;
                overflow: hidden;
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.6);
                margin-left: 15%;

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
                background-color: #f7f7f7;
                /* Blanco cremoso */
            }

            td {
                background-color: #f7f7f7;
                /* Blanco cremoso */
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
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.6);

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