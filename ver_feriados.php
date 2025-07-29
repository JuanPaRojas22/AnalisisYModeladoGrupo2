<?php
session_start();
include 'template.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}

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

// Obtener feriados
$sql = "SELECT nombre_feriado, fecha, tipo_feriado, doble_pago FROM dias_feriados ORDER BY fecha ASC";
$result = $conn->query($sql);

// Organizar por mes
$feriadosPorMes = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $fecha = new DateTime($row['fecha']);
        $mes = mb_strtoupper($fecha->format('F'), 'UTF-8');
        $feriadosPorMes[$mes][] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Feriados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <h1 class="text-center mb-4 text-primary">📅 Feriados Registrados</h1>

    <?php if (!empty($feriadosPorMes)): ?>
        <?php foreach ($feriadosPorMes as $mes => $feriados): ?>
            <div class="bg-white rounded shadow-sm p-4 mb-4">
                <h4 class="text-uppercase text-secondary"><?= $mes ?></h4>
                <div class="row mt-3">
                    <?php foreach ($feriados as $feriado): ?>
                        <div class="col-md-4 mb-3">
                            <div class="card h-100 shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <?= $feriado['doble_pago'] ? '💰 ' : '' ?>
                                        <?= htmlspecialchars($feriado['nombre_feriado']) ?>
                                    </h5>
                                    <p class="card-text">📆 <?= htmlspecialchars($feriado['fecha']) ?></p>
                                    <p class="card-text">🏷️ <?= htmlspecialchars($feriado['tipo_feriado']) ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="text-center text-muted">No hay feriados registrados.</p>
    <?php endif; ?>
</div>
</body>
</html>
