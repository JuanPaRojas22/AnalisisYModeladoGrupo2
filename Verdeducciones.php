<?php
require 'conexion.php';
session_start();
require 'template.php';

$rol = $_SESSION['rol'];
$id_usuario_logueado = $_SESSION['id_usuario'];

// Paginación
$por_pagina = 10;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($pagina_actual < 1) $pagina_actual = 1;
$offset = ($pagina_actual - 1) * $por_pagina;

// Filtro usuario desde POST (solo para roles 1 y 2)
$id_usuario_seleccionado = null;
if (($rol == 1 || $rol == 2) && isset($_POST['id_usuario']) && $_POST['id_usuario'] !== '') {
    $id_usuario_seleccionado = (int)$_POST['id_usuario'];
}

// Construir consulta según rol y filtro
$params = [];
$tipos = '';
$sql_base = "
    SELECT SQL_CALC_FOUND_ROWS
        d.id_deduccion, 
        d.id_usuario, 
        u.nombre, 
        u.apellido, 
        d.razon, 
        d.deudor, 
        d.concepto, 
        d.lugar, 
        d.deuda_total, 
        d.monto_mensual,
        d.aportes, 
        d.saldo_pendiente, 
        d.saldo_pendiente_dolares, 
        d.fechacreacion
    FROM Deducciones d
    INNER JOIN Usuario u ON d.id_usuario = u.id_usuario
";

$where_clauses = [];
if ($rol == 3) {
    // Usuario normal solo ve sus datos
    $where_clauses[] = "d.id_usuario = ?";
    $tipos .= "i";
    $params[] = $id_usuario_logueado;
} else {
    // Admins pueden filtrar o ver todo
    if ($id_usuario_seleccionado !== null) {
        $where_clauses[] = "d.id_usuario = ?";
        $tipos .= "i";
        $params[] = $id_usuario_seleccionado;
    }
}

if (count($where_clauses) > 0) {
    $sql_base .= " WHERE " . implode(" AND ", $where_clauses);
}

$sql_base .= " ORDER BY d.fechacreacion DESC LIMIT ? OFFSET ?";
$tipos .= "ii";
$params[] = $por_pagina;
$params[] = $offset;

// Preparar y ejecutar
$stmt = $conn->prepare($sql_base);
$stmt->bind_param($tipos, ...$params);
$stmt->execute();
$result_deducciones = $stmt->get_result();

// Obtener total registros para paginación
$total_resultado = $conn->query("SELECT FOUND_ROWS()")->fetch_row()[0];
$total_paginas = ceil($total_resultado / $por_pagina);

// Obtener todos los usuarios para el dropdown (solo roles 1 y 2)
$usuarios = [];
if ($rol == 1 || $rol == 2) {
    $sql_usuarios = "SELECT id_usuario, nombre, apellido FROM Usuario";
    $result_usuarios = $conn->query($sql_usuarios);
    while ($row = $result_usuarios->fetch_assoc()) {
        $usuarios[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ver Deducciones</title>
    <link href="assets/css/bootstrap.css" rel="stylesheet">
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/style-responsive.css" rel="stylesheet">
    <style>
        td, div { color: black !important; }
        /* Puedes dejar aquí tus estilos personalizados */
    </style>
</head>

<body>
    <section id="main-content">
        <section class="wrapper site-min-height">

            <div class="container">
                <h2 class="fw-bold text-center">Listado de Deducciones</h2>

                <?php if ($rol == 1 || $rol == 2): ?>
                <div class="filter-container my-3">
                    <form method="POST" action="">
                        <label for="id_usuario">Seleccionar usuario:</label>
                        <select name="id_usuario" id="id_usuario" class="form-select" style="width:auto; display:inline-block;">
                            <option value="">Ver todos</option>
                            <?php foreach ($usuarios as $u): ?>
                                <option value="<?= $u['id_usuario']; ?>" <?= ($id_usuario_seleccionado == $u['id_usuario']) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($u['nombre'] . " " . $u['apellido']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-primary">Filtrar</button>
                    </form>
                </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>Nombre del Usuario</th>
                                <th>Razón</th>
                                <th>Deudor</th>
                                <th>Concepto</th>
                                <th>Lugar</th>
                                <th>Monto Mensual</th>
                                <th>Aportes</th>
                                <th>Saldo Pendiente</th>
                                <th>Saldo Pendiente (USD)</th>
                                <th>Fecha de Creación</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result_deducciones->num_rows > 0): ?>
                                <?php while ($row = $result_deducciones->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['nombre'] . " " . $row['apellido']); ?></td>
                                        <td><?= htmlspecialchars($row['razon']); ?></td>
                                        <td><?= htmlspecialchars($row['deudor']); ?></td>
                                        <td><?= htmlspecialchars($row['concepto']); ?></td>
                                        <td><?= htmlspecialchars($row['lugar']); ?></td>
                                        <td><?= number_format($row['monto_mensual'], 2); ?></td>
                                        <td><?= number_format($row['aportes'], 2); ?></td>
                                        <td><?= number_format($row['saldo_pendiente'], 2); ?></td>
                                        <td><?= number_format($row['saldo_pendiente_dolares'], 2); ?></td>
                                        <td><?= htmlspecialchars($row['fechacreacion']); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="10" class="text-center">No hay deducciones para mostrar.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <?php if ($total_paginas > 1): ?>
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                            <li class="page-item <?= ($i == $pagina_actual) ? 'active' : '' ?>">
                                <a class="page-link" href="?pagina=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
                <?php endif; ?>

                <div class="text-center mt-3">
                    <a href="VerPlanilla.php" class="btn btn-secondary">Volver</a>
                </div>
            </div>

        </section>
    </section>
    <style>
  body {
    font-family: 'Arial', sans-serif;
    background-color: #f4f4f9;
    margin: 0;
    padding: 20px;
}
#id_usuario {
    font-size: 13px; /* Aumenta el tamaño de la fuente */
    padding: 13px 13px; /* Aumenta el espacio interno */
    width: auto; /* Ajusta el tamaño automáticamente */
    height: 50px; /* Aumenta la altura del campo */

}


.container {
    max-width: 1000px;
    margin: auto;
}

.title-container {
    text-align: center;
    margin-top: 50px;
    margin-bottom: 30px;
}

.card {
    border-radius: 10px;
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
    padding: 20px;
    text-align: center; /* Centra el contenido dentro de la card */
}

.form-group {
    display: flex;
    justify-content: center;  /* Centra el contenido */
    align-items: center;  /* Alinea verticalmente */
    gap: 10px;  /* Espacio entre el select y el botón */
    width: 100%;
}

.form-select, .btn {
    font-size: 16px;
    padding: 10px;
    width: 30%;  /* Ajusta el tamaño según lo necesario */
}

.btn {
    background-color: #0B4F6C;
    color: white;
    border: none;
    cursor: pointer;
    font-weight: bold;
    border-radius: 5px;
}

.btn:hover {
    background-color: #0a3c2c;
}
.table-container {
    overflow-x: auto;
    margin-top: 20px;
}

table {
    width: 100%;
    border-collapse: collapse;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    background-color: white;
    margin-top: 20px;
    border-radius: 8px;
    overflow: hidden;
}

th, td {
    padding: 12px;
    text-align: center;
    border: 1px solid #ddd;
}

th {
    background-color: #116B67;
    color: white;
}

td {
    background-color: #f9f9f9;
}

tr:nth-child(even) td {
    background-color: #f1f1f1;
}

tr:hover {
    background-color: #e9f7fc;
}

.form-group {
    margin-top: 30px;
    text-align: center;
}

.btn-secondary {
    padding: 10px 20px;
    background-color: #0B4F6C;
    color: white;
    border-radius: 5px;
    text-decoration: none;
    transition: background-color 0.3s ease;
}

.btn-secondary:hover {
    background-color: #0B4F6C;
}


    .filter-container {
        display: flex;
        justify-content: center;
        align-items: center;
        margin-top: 20px;
    }

    .filter-container select,
    .filter-container button {
        margin: 0 10px;
        padding: 10px;
        font-size: 16px;
    }
</style>
</body>

</html>
