<?php
ob_start();  // Inicia el búfer de salida

// Conexión a la base de datos
$host = "accespersoneldb.mysql.database.azure.com";
$user = "adminUser";
$password = "admin123+";
$dbname = "gestionEmpleados";
$port = 3306;

$conn = mysqli_init();
mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);
mysqli_options($conn, MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);

if (!$conn->real_connect($host, $user, $password, $dbname, $port, NULL, MYSQLI_CLIENT_SSL)) {
    die("Error de conexión: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");

session_start();
include "template.php";

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$mensaje = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $metodo_pago = $_POST["metodo_pago"];
    $fecha_pago = date("Y-m-d");
    $usuarios_procesados = 0;

    // Obtener todos los usuarios
    $query_usuarios = "SELECT id_usuario FROM usuario";
    $result_usuarios = $conn->query($query_usuarios);

    if ($result_usuarios && $result_usuarios->num_rows > 0) {
        while ($row = $result_usuarios->fetch_assoc()) {
            $id_usuario = $row['id_usuario'];

            // Verificar si ya tiene aguinaldo registrado este año
            $year = date("Y");
            $stmt_check = $conn->prepare("SELECT COUNT(*) FROM historial_aguinaldo WHERE id_usuario = ? AND YEAR(fecha_pago) = ?");
            $stmt_check->bind_param("ii", $id_usuario, $year);
            $stmt_check->execute();
            $stmt_check->bind_result($count);
            $stmt_check->fetch();
            $stmt_check->close();

            if ($count > 0)
                continue; // Ya tiene aguinaldo, siguiente usuario

            // Calcular total de pagos del último año
            $stmt_totales = $conn->prepare("
                SELECT SUM(salario_base), SUM(total_bonos), SUM(pago_horas_extras)
                FROM pago_planilla
                WHERE id_usuario = ? AND fecha_pago >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
            ");
            $stmt_totales->bind_param("i", $id_usuario);
            $stmt_totales->execute();
            $stmt_totales->bind_result($total_salario, $total_bonos, $total_horas_extras);
            $stmt_totales->fetch();
            $stmt_totales->close();

            $total_salario = $total_salario ?? 0;
            $total_bonos = $total_bonos ?? 0;
            $total_horas_extras = $total_horas_extras ?? 0;

            $aguinaldo = ($total_salario + $total_bonos + $total_horas_extras) / 12;

            // Insertar aguinaldo
            $stmt_insert = $conn->prepare("
                INSERT INTO historial_aguinaldo (id_usuario, total_aguinaldo, fecha_pago, metodo_pago)
                VALUES (?, ?, ?, ?)
            ");
            $stmt_insert->bind_param("idss", $id_usuario, $aguinaldo, $fecha_pago, $metodo_pago);
            $stmt_insert->execute();
            $stmt_insert->close();

            $usuarios_procesados++;
        }

        // Mensaje final
        if ($usuarios_procesados > 0) {
            $mensaje = "Aguinaldos calculados y registrados correctamente.";
        } else {
            $mensaje = "Los aguinaldos ya fueron calculados para el año {$year}.";
        }
    } else {
        $mensaje = "No se encontraron usuarios para procesar aguinaldo.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Listado de Aguinaldos</title>
    <link rel="stylesheet" href="tu_css_aqui.css"> <!-- Si usas CSS externo -->
</head>

<body>
    <section id="main-content">
        <section class="wrapper site-min-height">
            <div class="container">

                <h1>Listado de Aguinaldos</h1>

                <?php if (!empty($mensaje)): ?>
                    <div class="alert alert-success mt-3 text-center mx-auto text-dark">
                        <?php echo $mensaje; ?>
                    </div>
                <?php endif; ?>

                <form action="calcular_aguinaldo.php" method="post">
                    <label style="color: black; font-size: 20px;" for="metodo_pago">Método de Pago:</label>
                    <select name="metodo_pago" required>
                        <option value="Transferencia">Transferencia</option>
                        <option value="Efectivo">Efectivo</option>
                        <option value="Cheque">Cheque</option>
                    </select>
                    <button class="btn" type="submit"><i class="bi bi-calculator"></i> Calcular</button>
                </form>

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
                        $query_historial = "
                    SELECT u.nombre, u.apellido, u.correo_electronico,
                           h.total_aguinaldo, h.fecha_pago, h.metodo_pago
                    FROM historial_aguinaldo h
                    JOIN usuario u ON h.id_usuario = u.id_usuario
                    ORDER BY h.fecha_pago DESC";
                        $result_historial = $conn->query($query_historial);
                        // Calcular el total de aguinaldos
                        $query_total = "SELECT SUM(total_aguinaldo) AS total_general FROM historial_aguinaldo";
                        $result_total = $conn->query($query_total);
                        $total_general = 0;
                        if ($result_total && $row_total = $result_total->fetch_assoc()) {
                            $total_general = $row_total['total_general'] ?? 0;
                        }

                        if ($result_historial && $result_historial->num_rows > 0) {
                            while ($row = $result_historial->fetch_assoc()) {
                                echo "<tr>
                <td>{$row['nombre']}</td>
                <td>{$row['apellido']}</td>
                <td>{$row['correo_electronico']}</td>
                <td>{$row['total_aguinaldo']}</td>
                <td>{$row['fecha_pago']}</td>
                <td>{$row['metodo_pago']}</td>
            </tr>";
                            }
                            // Fila de total
                            echo "<tr>
            <td colspan='3' style='text-align:right; font-weight:bold;'>Total de Aguinaldos:</td>
            <td style='font-weight:bold;'>" . number_format($total_general, 2) . "</td>
            <td colspan='2'></td>
        </tr>";
                        } else {
                            echo "<tr><td colspan='6' class='no-records'>No se encontraron registros de aguinaldo.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>

            </div>
        </section>
    </section>

    <style>
        body {
            font-family: 'Ruda', sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            padding: 0;
            color: black;
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
    <?php
    $conn->close();
    ob_end_flush();
    ?>