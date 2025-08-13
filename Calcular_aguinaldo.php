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

            if ($count > 0) continue; // Ya tiene aguinaldo, siguiente usuario

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
            $mensaje = "Aguinaldos calculados y registrados correctamente para {$usuarios_procesados} usuarios.";
        } else {
            $mensaje = "Todos los usuarios ya tienen aguinaldo registrado este año.";
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

            <?php if(!empty($mensaje)): ?>
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
                    ORDER BY h.fecha_pago DESC
                ";
                $result_historial = $conn->query($query_historial);

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
                } else {
                    echo "<tr><td colspan='6' class='no-records'>No se encontraron registros de aguinaldo.</td></tr>";
                }
                ?>
                </tbody>
            </table>

        </div>
    </section>
</section>
<?php
$conn->close();
ob_end_flush();
?>
