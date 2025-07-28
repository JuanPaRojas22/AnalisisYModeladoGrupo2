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

// ✅ Marcar notificaciones como leídas
$sql_leer = "UPDATE notificaciones SET leida = 1 WHERE id_usuario = ? AND leida = 0";
$stmt_leer = $conn->prepare($sql_leer);
$stmt_leer->bind_param("i", $id_usuario);
$stmt_leer->execute();
$stmt_leer->close();

// 🔎 Consultar notificaciones
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
    <title>🔔 Mis Notificaciones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/style-responsive.css" rel="stylesheet">



    <style>
        body {
            background-color: #f0f2f5;
            font-family: 'Segoe UI', sans-serif;
        }

        .container {
            max-width: 700px;
            margin: 80px auto;
            background: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .notificacion {
            background-color: #ecf0f1;
            border-left: 5px solid #00b894;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .notificacion.unread {
            background-color: #dff9fb;
            border-left-color: #0984e3;
        }

        .notificacion i {
            margin-right: 10px;
            color: #00b894;
        }

        .btn-back {
            margin-top: 20px;
            background-color: #00b894;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: bold;
            transition: 0.3s;
        }

        .btn-back:hover {
            background-color: #019870;
        }

        .fecha {
            font-size: 0.85em;
            color: #7f8c8d;
        }
    </style>
</head>

<body>

    <div class="container text-center">
        <h2 class="mb-4"><i class="fas fa-bell"></i> Mis Notificaciones</h2>

        <?php if (count($notificaciones) > 0): ?>
            <?php foreach ($notificaciones as $notif): ?>
                <div class="notificacion <?= $notif['leida'] ? '' : 'unread' ?>">
                    <div class="text-start">
                        <i class="fas fa-envelope-open-text"></i>
                        <?= htmlspecialchars($notif['mensaje']) ?>
                        <div class="fecha"><?= $notif['fecha'] ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="alert alert-info">No tenés notificaciones nuevas.</div>
        <?php endif; ?>

        <a href="index.php" class="btn btn-back">Volver</a>
    </div>

</body>

</html>