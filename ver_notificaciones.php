<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
include "template.php";
// Conexión segura con SSL (Azure)
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

// Corregido: id → id AS id_notificacion
$sql = "SELECT id AS id_notificacion, mensaje, leida, fecha FROM notificaciones WHERE id_usuario = ? ORDER BY fecha DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$resultado = $stmt->get_result();

$notificaciones = [];
while ($row = $resultado->fetch_assoc()) {
    $notificaciones[] = $row;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Notificaciones</title>
    <link href="assets/css/bootstrap.css" rel="stylesheet">
    <style>
    body {
        font-family: 'Segoe UI', sans-serif;
        background-color: #f5f6fa;
        margin: 0;
        padding: 0;
    }

    .container {
        max-width: 600px;
        margin: 60px auto;
        background: white;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        padding: 30px;
        text-align: center;
    }

    h1 {
        color: #2d3436;
        font-size: 24px;
        margin-bottom: 25px;
    }

    .notificacion {
        background-color: #dfe6e9;
        border-left: 6px solid #00b894;
        padding: 20px;
        margin-bottom: 20px;
        border-radius: 8px;
        text-align: left;
        display: flex;
        align-items: center;
        font-size: 16px;
        color: #2d3436;
    }

    .notificacion i {
        font-size: 20px;
        color: #00b894;
        margin-right: 12px;
    }

    .volver {
        display: inline-block;
        margin-top: 15px;
        text-decoration: none;
        background-color: #00b894;
        color: white;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: bold;
        transition: background-color 0.3s ease;
    }

    .volver:hover {
        background-color: #019870;
    }
</style>

</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">🔔 Mis notificaciones</h2>
        <?php if (count($notificaciones) > 0): ?>
            <ul class="list-group">
                <?php foreach ($notificaciones as $notif): ?>
                    <li class="list-group-item <?= $notif['leida'] ? '' : 'list-group-item-warning' ?>">
                        <?= htmlspecialchars($notif['mensaje']) ?>
                        <br><small class="text-muted"><?= $notif['fecha'] ?></small>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <div class="alert alert-info">No tenés notificaciones por el momento.</div>
        <?php endif; ?>
        <a href="inicio.php" class="btn btn-secondary mt-3">Volver</a>
    </div>
</body>
</html>




