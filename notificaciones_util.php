<?php
function insertarNotificacion($id_usuario, $mensaje) {
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

    // Intentamos conectar usando SSL
    if (!$conn->real_connect($host, $user, $password, $dbname, $port, NULL, MYSQLI_CLIENT_SSL)) {
        die("Error de conexión: " . mysqli_connect_error());
    }

    mysqli_set_charset($conn, "utf8mb4");

    // Insertar la notificación
    $sql = "INSERT INTO notificaciones (id_usuario, mensaje, leida, fecha) VALUES (?, ?, 0, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $id_usuario, $mensaje);

    if (!$stmt->execute()) {
        error_log("Error al insertar notificación: " . $stmt->error);
    }

    $stmt->close();
    $conn->close();
}
?>
