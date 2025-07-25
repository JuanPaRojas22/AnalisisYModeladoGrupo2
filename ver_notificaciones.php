 <?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit();
}

$id_usuario = $_SESSION['id_usuario'];

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

$sql = "SELECT id_notificacion, mensaje, leida, fecha FROM notificaciones WHERE id_usuario = ? ORDER BY fecha DESC";
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
            <p>No tenés notificaciones por el momento.</p>
        <?php endif; ?>
        <a href="inicio.php" class="btn btn-secondary mt-3">Volver</a>
    </div>
</body>
</html>
