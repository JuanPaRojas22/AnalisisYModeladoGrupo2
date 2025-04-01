<?php
ob_start(); // Inicia el búfer de salida para evitar que se envíen cabeceras prematuramente

require 'conexion.php';
require 'template.php';


if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verifica si el usuario está logueado
if (!isset($_SESSION['id_usuario'])) {
    echo "Debes iniciar sesión para acceder a esta página.";
    exit;
}
$id_usuario = $_SESSION['id_usuario'];

if (isset($_POST['generar_pdf'])) {
    // Redirige a otro archivo PHP que generará el PDF
    header("Location: generar_salarios_pdf.php"); // Redirige a generar_pdf.php
    exit; // Termina la ejecución del script
}

?>



<!DOCTYPE html>

<body>

    <div class="container">
        <h1>Generar Monto Total de los Salarios</h1>

        <!-- Formulario que envía una solicitud para calcular la suma -->
        <form class="form-container" action="reporte_hacienda.php" method="post">
            <button class="btn" type="submit" name="calcular_salario">Calcular Total de Salarios</button>
            <button class="btn" type="submit" name="generar_pdf"><i class="bi bi-file-earmark-arrow-down-fill"></i>
            </button>

        </form>

        <?php

        if (isset($_POST['calcular_salario'])) {
            // Consulta para obtener la suma total de salarios activos
            $query = "SELECT SUM(salario_neto) AS monto_total_salarios FROM planilla";
            $result = mysqli_query($conn, $query);

            // Verificar si la consulta fue exitosa
            if ($result) {
                $row = mysqli_fetch_assoc($result);
                $monto_total = $row['monto_total_salarios'];

                // Mostrar el resultado en pantalla
                echo "<p class='resultado'><strong>El monto total de los salarios de la empresa es: </strong> " . number_format($monto_total, 2, ',', '.') . "</p>";
            } else {
                // Si ocurre un error con la consulta
                echo "<p class='resultado'>Error al obtener el total de salarios: " . mysqli_error($conn) . "</p>";
            }

            mysqli_close($conn);
        }
        ?>
    </div>
</body>

<style>
    body {
        font-family: 'Ruda', sans-serif;
        background-color: #f7f7f7;
        margin: 0;
        padding: 0;

    }

    .resultado {
        text-align: center;
        /* Asegura que el texto se centre */
        font-size: 18px;
        color: #333;
        margin-top: 20px;
        background-color: rgb(160, 255, 180);
        /* Fondo verde */
        padding: 15px;
        /* Espaciado interno */
        border-radius: 8px;
        /* Bordes redondeados */
        max-width: 600px;
        /* Limita el ancho máximo */
        margin-left: auto;
        /* Centra el bloque */
        margin-right: auto;
        /* Centra el bloque */

    }



    .container {
        width: 80%;
        /* Puedes ajustar el ancho para que no ocupe todo el espacio */
        max-width: 1200px;
        /* Asegura que no se expanda demasiado en pantallas grandes */
        margin-top: 200px;
        padding: 20px 40px;
        /* Espacio de 20px arriba y abajo, y 40px a los lados */
        background-color: #ffffff;
        border-radius: 12px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.6);
        margin-left: auto;
        /* Centra el contenedor */
        margin-right: auto;
        /* Centra el contenedor */
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

    .btn {
        display: inline-block;
        background-color: #c9aa5f;
        color: white;
        padding: 10px 20px;
        margin-right: 10px;  /*espacio entre los botones */
        font-size: 25px;
        font-weight: bold;
        text-align: center;
        text-decoration: none;
        border-radius: 5px;
        margin-bottom: 20px;
        transition: background-color 0.3s;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.6);
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
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.6);

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

    .form-container {
        display: flex;
        justify-content: center;
        /* Centra el contenido horizontalmente */
        align-items: center;
        /* Centra el contenido verticalmente */
        width: 100%;
        /* Asegura que el contenedor ocupe todo el ancho disponible */
    }
</style>

</html>

<?php
// Finaliza y envía el búfer
ob_end_flush();
?>