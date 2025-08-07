<?php
session_start();
require "template.php";

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
// Parámetros de conexión
$host = "accespersoneldb.mysql.database.azure.com";
$user = "adminUser";
$password = "admin123+";
$dbname = "gestionEmpleados";
$port = 3306;

// Inicializamos mysqli
$conn = mysqli_init();

// Configuramos SSL
mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);
mysqli_options($conn, MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);

// Intentamos conectar usando SSL (con la bandera MYSQLI_CLIENT_SSL)
if (!$conn->real_connect($host, $user, $password, $dbname, $port, NULL, MYSQLI_CLIENT_SSL)) {
    die("Error de conexión: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");

// Variables de sesión
$id_usuario = $_SESSION['id_usuario'];
$rol = $_SESSION['id_rol']; // Asegúrate que este dato está en sesión
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Invitado';

// Obtener departamento si rol = 1 (admin normal)
$mi_departamento = null;
if ($rol == 1) {
    $res_dep = mysqli_query($conn, "SELECT id_departamento FROM usuario WHERE id_usuario = '$id_usuario'");
    if ($res_dep && $dep_row = mysqli_fetch_assoc($res_dep)) {
        $mi_departamento = $dep_row['id_departamento'];
    }
}

// Paginación
$registros_por_pagina = 10;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($pagina_actual < 1) $pagina_actual = 1;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

// Construir consulta base con filtro por rol
$where = "WHERE 1=1 ";

if ($rol == 3) {
    // Usuario común: solo sus registros
    $where .= "AND hc.id_usuario = $id_usuario ";
} elseif ($rol == 1 && $mi_departamento !== null) {
    // Admin normal: solo registros usuarios de su departamento
    $where .= "AND u.id_departamento = $mi_departamento ";
} 
// Admin master (rol 2) ve todo, no filtro adicional

// Consulta total para contar registros
$sql_total = "SELECT COUNT(*) AS total
              FROM Historial_Cargos hc
              JOIN Usuario u ON hc.id_usuario = u.id_usuario
              $where";

$res_total = $conn->query($sql_total);
$total_registros = 0;
if ($res_total) {
    $row_total = $res_total->fetch_assoc();
    $total_registros = (int)$row_total['total'];
}

$total_paginas = ceil($total_registros / $registros_por_pagina);

// Consulta principal con límite y orden
$sql = "SELECT 
            hc.id_historial_cargos, 
            u.nombre AS nombre_usuario,
            hc.nuevo_puesto, 
            hc.fecha_cambio, 
            hc.motivo, 
            hc.fechacreacion, 
            hc.sueldo_nuevo,
            hc.sueldo_anterior
        FROM Historial_Cargos hc
        JOIN Usuario u ON hc.id_usuario = u.id_usuario
        $where
        ORDER BY hc.fecha_cambio DESC
        LIMIT $registros_por_pagina OFFSET $offset";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial</title>
    <link href="assets/css/bootstrap.css" rel="stylesheet">
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/style-responsive.css" rel="stylesheet">
    <style>
        /* Tus estilos actuales */
        body { background-color: #f5f5f5; font-family: Arial, sans-serif; }
        .container { max-width: 95%; margin: 50px auto; padding: 30px; background: #fff; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);}
        h1 { text-align: center; margin-bottom: 30px; font-size: 28px; color: #333; }
        .btn { display: inline-block; background-color: #147964; color: white; padding: 10px 20px; font-weight: bold; text-decoration: none; border-radius: 6px; margin-bottom: 20px; }
        .btn:hover { background-color: #116B67; }
        table { width: 100%; border-collapse: collapse; border-radius: 8px; overflow: hidden; }
        thead { background-color: #116B67; color: white; }
        th, td { padding: 12px 15px; text-align: left; color: #333; }
        tbody tr:nth-child(even) { background-color: #f9f9f9; }
        tbody tr:hover { background-color: #f1f1f1; }
        .no-records { text-align: center; font-style: italic; color: #888; }
        .pagination {
            text-align: center;
            margin-top: 20px;
        }
        .pagination a, .pagination span {
            display: inline-block;
            margin: 0 3px;
            padding: 6px 12px;
            color: #116B67;
            text-decoration: none;
            border: 1px solid #116B67;
            border-radius: 4px;
        }
        .pagination .current {
            background-color: #116B67;
            color: white;
            pointer-events: none;
        }
    </style>
</head>
<body>
<section id="container">
    <section id="main-content">
        <section class="wrapper site-min-height">

            <div class="container">
                <h1>Historial de Cambios de Puesto</h1>

                <a href="registrar_cambio_puesto.php" class="btn">Ir al Formulario de Cambio de Puesto</a>

                <form action="reporte_puestos.php" method="get" target="_blank">
                    <input type="hidden" name="id_usuario" value="<?= htmlspecialchars($id_usuario) ?>">
                    <button type="submit" class="btn">Descargar PDF</button>
                </form>

                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuario</th>
                            <th>Nuevo Puesto</th>
                            <th>Fecha de Cambio</th>
                            <th>Motivo</th>
                            <th>Fecha de Creación</th>
                            <th>Sueldo Anterior</th>
                            <th>Sueldo Nuevo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $row['id_historial_cargos'] ?></td>
                                    <td><?= htmlspecialchars($row['nombre_usuario']) ?></td>
                                    <td><?= htmlspecialchars($row['nuevo_puesto']) ?></td>
                                    <td><?= htmlspecialchars($row['fecha_cambio']) ?></td>
                                    <td><?= htmlspecialchars($row['motivo']) ?></td>
                                    <td><?= htmlspecialchars($row['fechacreacion']) ?></td>
                                    <td>₡<?= number_format($row['sueldo_anterior'], 2) ?></td>
                                    <td>₡<?= number_format($row['sueldo_nuevo'], 2) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="8" class="no-records">No se encontraron registros.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <?php if ($total_paginas > 1): ?>
                    <div class="pagination">
                        <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                            <?php if ($i == $pagina_actual): ?>
                                <span class="current"><?= $i ?></span>
                            <?php else: ?>
                                <a href="?pagina=<?= $i ?>"><?= $i ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>

            </div>

        </section>
    </section>
</section>
</body>
</html>
<?php $conn->close(); ?>
