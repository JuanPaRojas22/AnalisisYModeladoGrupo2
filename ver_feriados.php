<?php
session_start();
include 'template.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Días feriados</title>
    <link href="assets/css/bootstrap.css" rel="stylesheet">
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/style-responsive.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-8 bg-gray-200">

<div class="max-w-6xl mx-auto bg-white p-6 rounded-lg shadow-lg">
    <h1 class="text-3xl font-bold mb-6 text-center" style="color:#0B4F6C">Días feriados</h1>

    <?php
    $host = "accespersoneldb.mysql.database.azure.com";
    $user = "adminUser";
    $password = "admin123+";
    $dbname = "gestionEmpleados";
    $port = 3306;
    $ssl_ca = '/home/site/wwwroot/certs/BaltimoreCyberTrustRoot.crt.pem';

    $conn = mysqli_init();
    mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);
    mysqli_options($conn, MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
    $conn->real_connect($host, $user, $password, $dbname, $port, NULL, MYSQLI_CLIENT_SSL);
    mysqli_set_charset($conn, "utf8mb4");

    $sql = "SELECT * FROM dias_feriados ORDER BY fecha";
    $result = $conn->query($sql);

    $feriadosPorMes = [];

    while ($row = $result->fetch_assoc()) {
        $fecha = new DateTime($row['fecha']);
        $mes = strtoupper($fecha->format('F'));
        $feriadosPorMes[$mes][] = $row;
    }

    foreach ($feriadosPorMes as $mes => $feriados) {
        echo '<div class="bg-white p-4 rounded-lg shadow-md mb-6">';
        echo '<h2 class="text-2xl font-bold mb-4 uppercase text-gray-700">' . $mes . '</h2>';
        echo '<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">';

        foreach ($feriados as $f) {
            $clases = "bg-gray-100 p-4 rounded-lg shadow-md";
            if ($f['doble_pago']) {
                $clases .= " bg-green-100 border-l-4 border-green-500";
            }

            echo '<div class="' . $clases . '">';
            echo '<p class="text-lg font-semibold text-gray-800">' . ($f['doble_pago'] ? "💰 " : "") . htmlspecialchars($f['nombre_feriado']) . '</p>';
            echo '<p class="text-sm text-gray-600">📅 ' . date("d/m/Y", strtotime($f['fecha'])) . '</p>';
            echo '<p class="text-sm text-gray-500">🏷️ ' . htmlspecialchars($f['tipo_feriado']) . '</p>';

            if ($f['doble_pago']) {
                echo '<span class="inline-block mt-2 px-2 py-1 text-xs bg-green-200 text-green-800 rounded">Doble Pago</span>';

            }

            echo '</div>';
        }

        echo '</div></div>';
    }

    $conn->close();
    ?>
</div>

</body>
</html>
