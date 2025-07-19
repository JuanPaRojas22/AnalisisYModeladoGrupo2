<?php
ob_start();  // Inicia el búfer de salida
// Conexión a la base de datos
// Parámetros de conexión
$host = "accespersoneldb.mysql.database.azure.com";
$user = "adminUser";
$password = "admin123+";
$dbname = "gestionEmpleados";
$port = 3306;

// Ruta al certificado CA para validar SSL
$ssl_ca = '/home/site/wwwroot/certs/BaltimoreCyberTrustRoot.crt.pem';

// Inicializamos mysqli
$conn = mysqli_init();

// Configuramos SSL
mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);
mysqli_options($conn, MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);


// Intentamos conectar usando SSL (con la bandera MYSQLI_CLIENT_SSL)
if (!$conn->real_connect($host, $user, $password, $dbname, $port, NULL, MYSQLI_CLIENT_SSL)) {
    die("Error de conexión: " . mysqli_connect_error());
}

// Establecemos el charset
mysqli_set_charset($conn, "utf8mb4");
session_start();
include "template.php";
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}


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

    // Verificar si la conexión sigue abierta antes de ejecutar la consulta
    $stmt = $conn->prepare($query_calculo);
    if (!$stmt) {
        die("Error en prepare: " . $conn->error);
    }
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
        if (!$stmt_check) {
            die("Error en prepare: " . $conn->error);
        }
        $stmt_check->bind_param("ii", $id_usuario, $year);
        $stmt_check->execute();
        $stmt_check->bind_result($count);
        $stmt_check->fetch();
        $stmt_check->close();
    
        if ($count > 0) {
            header("Location: Calcular_aguinaldo.php?aguinaldo_registrado=error");
            exit;  // Muy importante para evitar seguir ejecutando
        } else {
            $query_insert = "INSERT INTO historial_aguinaldo (id_usuario, total_aguinaldo, fecha_pago, metodo_pago) 
                             VALUES (?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($query_insert);
            if (!$stmt_insert) {
                die("Error en prepare: " . $conn->error);
            }
            $stmt_insert->bind_param("idss", $id_usuario, $aguinaldo, $fecha_pago, $metodo_pago);
    
            if ($stmt_insert->execute()) {
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
    
    $stmt->close();
    

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
                <label class="h1" style="color: black; font-size: 20px;" for="metodo_pago">Método de Pago:</label>
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
                margin: 0;
                padding: 0;
            }

            .container {
                width: 80%;
                flex-direction: column;
                background-color: #f7f7f7;
                justify-content: flex-start;
                align-items: center;
                /* Ensures the content is centered */
                padding: 10px;
                max-width: 90%;
                /* Reduced max-width for smaller appearance */
                box-shadow: 0 2px 6px rgba(0, 0, 0, 0.4);
                margin-top: 20px;
                border-radius: 12px;
            }




            h1 {
                text-align: center;
                color: black;
                margin-bottom: 20px;
                /* Reduced margin */
                font-size: 20px;
                /* Smaller font size */
                font-weight: bold;
            }

            select {
                width: 30%;
                /* Smaller width for the select dropdown */
                padding: 8px;
                /* Reduced padding */
                font-size: 14px;
                /* Smaller font size */
                border: 2px solid rgb(15, 15, 15);
                border-radius: 5px;
                background: #f9f9f9;
                color: black;
                cursor: pointer;
                transition: all 0.3s ease;
                text-align: center;
            }

            .btn {
                display: inline-block;
                background-color: #147665;
                color: #f7f7f7;
                padding: 8px 16px;
                /* Reduced padding */
                font-size: 14px;
                /* Smaller font size */
                font-weight: bold;
                text-align: center;
                text-decoration: none;
                border-radius: 5px;
                margin-bottom: 15px;
                margin-top: 15px;
                transition: background-color 0.3s;
                box-shadow: 0 2px 6px rgba(0, 0, 0, 0.4);
            }




            .table-container {
                display: flex;
                justify-content: center;
                align-items: center;
                width: 100%;
            }

            table {
                width: 80%;
                /* You can adjust this width to fit your design */
                margin-left: 130px;
                /* This will move the table to the right */
                border-collapse: collapse;
                margin-top: 15px;
                border-radius: 8px;
                overflow: hidden;
            }


            th,
            td {
                padding: 8px;
                /* Reduced padding */
                text-align: center;
                font-size: 14px;
                /* Smaller font size */
                color: #555;
                border-bottom: 1px solid #ddd;
            }

            th {
                background-color: #116B67;
                color: #fff;
            }

            tr:hover {
                background-color: #f7f7f7;
            }

            td {
                background-color: #f7f7f7;
            }

            .no-records {
                text-align: center;
                font-style: italic;
                color: #888;
            }
        </style>