<?php 
session_start();
include 'template.php';

// Validar sesión iniciada
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}

// Conexión a Azure MySQL con SSL
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

// Obtener feriados
$sql = "SELECT * FROM feriado ORDER BY fecha";
$result = $conn->query($sql);

// Agrupar por mes
$feriadosPorMes = [];
while ($row = $result->fetch_assoc()) {
    $fecha = new DateTime($row['fecha']);
    $mes = strtoupper($fecha->format("F")); // Ej: JULIO
    $emoji = $row['doble_pago'] == 1 ? "💰 " : "";

    $feriadosPorMes[$mes][] = [
        "nombre" => $emoji . $row['nombre_feriado'],
        "fecha" => $fecha->format("d/m/Y"),
        "tipo" => $row['tipo_feriado']
    ];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Feriados del Sistema</title>
    <link href="assets/css/bootstrap.css" rel="stylesheet">
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        td, div {
            color: black !important;
        }
    </style>
</head>
<body class="p-8 bg-gray-200">
    <div class="max-w-6xl mx-auto bg-white p-6 rounded-lg shadow-lg">
        <h1 class="text-3xl font-bold mb-6 text-center" style="color:#0B4F6C">Feriados del Sistema</h1>

        <?php if (count($feriadosPorMes) > 0): ?>
            <?php foreach ($feriadosPorMes as $mes => $feriados): ?>
                <div class="bg-white p-4 rounded-lg shadow-md mb-6">
                    <h2 class="text-2xl font-bold mb-4 uppercase text-gray-700"><?= $mes ?></h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <?php foreach ($feriados as $f): ?>
                            <div class="bg-gray-100 p-4 rounded-lg shadow-md">
                                <p class="text-lg font-semibold text-gray-800"><?= $f['nombre'] ?></p>
                                <p class="text-sm text-gray-600">📅 <?= $f['fecha'] ?></p>
                                <p class="text-sm text-gray-500">🏷️ <?= $f['tipo'] ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-center text-gray-600">No hay feriados registrados.</p>
        <?php endif; ?>
    </div>
</body>
</html>
