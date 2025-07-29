<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}
include 'template.php';

// Conexión a Azure
$host = "accespersoneldb.mysql.database.azure.com";
$user = "adminUser";
$password = "admin123+";
$dbname = "gestionEmpleados";
$port = 3306;
$ssl_ca = '/home/site/wwwroot/certs/BaltimoreCyberTrustRoot.crt.pem';

$conn = mysqli_init();
mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);
mysqli_options($conn, MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
if (!$conn->real_connect($host, $user, $password, $dbname, $port, NULL, MYSQLI_CLIENT_SSL)) {
    die("Error de conexión: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8mb4");

// Consulta de feriados
$sql = "SELECT id_fecha, nombre_feriado, fecha, tipo_feriado, doble_pago FROM dias_feriados ORDER BY fecha";
$result = $conn->query($sql);

$feriadosPorMes = [];
while ($row = $result->fetch_assoc()) {
    $mes = strtoupper(strftime("%B", strtotime($row['fecha'])));
    $feriadosPorMes[$mes][] = $row;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Feriados</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="assets/css/bootstrap.css" rel="stylesheet">
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/style-responsive.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-8 bg-gray-200">
    <div class="max-w-6xl mx-auto bg-white p-6 rounded-lg shadow-lg">
        <h1 class="text-3xl font-bold mb-6 text-center" style="color:#0B4F6C">Días Feriados</h1>

        <?php foreach ($feriadosPorMes as $mes => $feriados): ?>
            <div class="bg-white p-4 rounded-lg shadow-md mb-6">
                <h2 class="text-2xl font-bold mb-4 uppercase text-gray-700"><?= $mes ?></h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php foreach ($feriados as $feriado): ?>
                        <div class="bg-gray-100 p-4 rounded-lg shadow-md">
                            <p class="text-lg font-semibold text-gray-800">
                                <?= $feriado['doble_pago'] ? "💰 " : "" ?><?= htmlspecialchars($feriado['nombre_feriado']) ?>
                            </p>
                            <p class="text-sm text-gray-600">📅 <?= date("d/m/Y", strtotime($feriado['fecha'])) ?></p>
                            <p class="text-sm text-gray-500">🏷️ <?= htmlspecialchars($feriado['tipo_feriado']) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
