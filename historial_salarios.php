<?php
session_start();
require 'conexion.php'; // Asegúrate que este archivo no imprime nada ni tiene BOM

// 1) Guardas/validas sesión ANTES de imprimir cualquier cosa
if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['id_rol'])) {
    header('Location: login.php', true, 303);
    exit;
}

// 2) PRG: manejar el filtro por POST y redirigir a GET limpio (sin id en URL)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && array_key_exists('departamento', $_POST)) {
    $_SESSION['filtro_departamento_pagos'] = ($_POST['departamento'] === '') ? '' : (int)$_POST['departamento'];
    header('Location: ' . $_SERVER['PHP_SELF'], true, 303);
    exit;
}

// 3) Ahora sí: abre la conexión y sigue con la lógica
$conn = obtenerConexion();

$id_usuario      = $_SESSION['id_usuario'];
$id_rol          = $_SESSION['id_rol'];
$id_departamento = $_SESSION['id_departamento'] ?? null;

$departamento_filtro = $_SESSION['filtro_departamento_pagos'] ?? '';

// Si usas template.php en esta vista, inclúyelo DESPUÉS del PRG, por ejemplo aquí:
 require 'template.php';
 
// =================== COMBO DE DEPARTAMENTOS PARA ROL 2 ===================
$departamentos = [];
if ($id_rol == 2) {
    $query_dept  = "SELECT id_departamento, nombre FROM departamento";
    $result_dept = $conn->query($query_dept);
    while ($row = $result_dept->fetch_assoc()) {
        $departamentos[] = $row;
    }
}

// =================== CONSULTAS POR ROL ===================
if ($id_rol == 3) {
    // Empleado: solo sus pagos
    $sql  = "SELECT p.*, u.nombre, u.apellido
             FROM pago_planilla p
             JOIN usuario u ON p.id_usuario = u.id_usuario
             WHERE p.id_usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_usuario);

} elseif ($id_rol == 1) {
    // Admin normal: solo su departamento (desde sesión, sin exponer en URL)
    if (empty($id_departamento)) {
        die("Error: No se ha encontrado el departamento para este administrador.");
    }
    $sql  = "SELECT p.*, u.nombre, u.apellido
             FROM pago_planilla p
             JOIN usuario u ON p.id_usuario = u.id_usuario
             WHERE u.id_departamento = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_departamento);

} elseif ($id_rol == 2) {
    // Admin master: todos o filtrado (desde sesión)
    if ($departamento_filtro !== '' && $departamento_filtro !== null) {
        $sql  = "SELECT p.*, u.nombre, u.apellido, d.nombre AS departamento
                 FROM pago_planilla p
                 JOIN usuario u ON p.id_usuario = u.id_usuario
                 JOIN departamento d ON u.id_departamento = d.id_departamento
                 WHERE d.id_departamento = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $departamento_filtro);
    } else {
        $sql  = "SELECT p.*, u.nombre, u.apellido, d.nombre AS departamento
                 FROM pago_planilla p
                 JOIN usuario u ON p.id_usuario = u.id_usuario
                 JOIN departamento d ON u.id_departamento = d.id_departamento";
        $stmt = $conn->prepare($sql);
    }
}

$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial de Pagos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="aportes.css" />
    <style>
        body { background-color:#f7f7f7; }
        .main-container { max-width:1000px; margin:40px auto; }
        .card-style { background:#fff; padding:25px; border-radius:15px; box-shadow:0 4px 15px rgba(0,0,0,.08); }
        h2 { font-weight:bold; margin-bottom:20px; text-align:center; }
        .custom-table { width:100%; font-size:14px; border-collapse:separate; border-spacing:0; border-radius:10px; overflow:hidden; }
        .custom-table thead { background:#116B67; color:#fff; }
        .custom-table th, .custom-table td { padding:8px 10px; text-align:center; border:none; white-space:nowrap; }
        .custom-table tr:nth-child(even) td { background:#f1f1f1; }
        .custom-table tr:nth-child(odd) td { background:#f9f9f9; }
    </style>
</head>
<body>
<div class="main-container">
<div class="card-style">

    <h2>Historial de Pagos</h2>

    <?php if ($id_rol == 1):
        $nombre_departamento = '';
        $stmt_dep = $conn->prepare("SELECT nombre FROM departamento WHERE id_departamento = ?");
        $stmt_dep->bind_param("i", $id_departamento);
        $stmt_dep->execute();
        $stmt_dep->bind_result($nombre_departamento);
        $stmt_dep->fetch();
        $stmt_dep->close();
    ?>
        <div class="text-center mb-2">
            <p class="fw-bold" style="font-size:16px;">Departamento: <?= htmlspecialchars($nombre_departamento) ?></p>
        </div>
    <?php endif; ?>

    <?php if (in_array($id_rol, [1,2])): ?>
    <div class="d-flex justify-content-center align-items-center gap-3 mb-4 flex-wrap">

        <?php if ($id_rol == 2): ?>
            <!-- FILTRO por POST (no expone id en URL) -->
            <form method="POST" class="d-flex align-items-center gap-2 flex-wrap">
                <label for="departamento" class="form-label mb-0 fw-bold" style="font-size:18px;">Departamento:</label>
                <select name="departamento" id="departamento" class="form-select" style="width:300px;height:45px;font-size:16px;">
                    <option value="">Todos</option>
                    <?php foreach ($departamentos as $dep): ?>
                        <option value="<?= (int)$dep['id_departamento'] ?>"
                            <?= ($departamento_filtro !== '' && (int)$departamento_filtro === (int)$dep['id_departamento']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($dep['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn" style="background-color:#116B67;color:white;font-weight:bold;padding:10px 20px;border-radius:8px;font-size:16px;">
                    Filtrar
                </button>
            </form>

            <!-- DESCARGA PDF por POST (respeta filtro actual) -->
            <form method="POST" action="reporte_historial_pagos.php" target="_blank" class="d-inline">
                <input type="hidden" name="departamento" value="<?= htmlspecialchars($departamento_filtro) ?>">
                <button type="submit" class="btn" style="background-color:#168761;color:white;font-weight:bold;padding:10px 20px;border-radius:8px;font-size:16px;">
                    📄 Descargar PDF
                </button>
            </form>

        <?php elseif ($id_rol == 1): ?>
            <!-- Admin normal: su depto desde sesión (sin URL) -->
            <form method="POST" action="reporte_historial_pagos.php" target="_blank" class="d-inline">
                <!-- puedes omitir este input si el PDF usa el depto desde la sesión -->
                <input type="hidden" name="id_departamento" value="<?= (int)$id_departamento ?>">
                <button type="submit" class="btn" style="background-color:#168761;color:white;font-weight:bold;padding:10px 20px;border-radius:8px;font-size:16px;">
                    📄 Descargar PDF
                </button>
            </form>
        <?php endif; ?>

    </div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <?php if ($id_rol != 3): ?><th>Empleado</th><?php endif; ?>
                    <th>Salario Base</th>
                    <th>Bono</th>
                    <th>Deducción</th>
                    <th>Horas Extra</th>
                    <th>Salario Neto</th>
                    <th>Fecha de Pago</th>
                    <th>Tipo Quincena</th>
                    <?php if ($id_rol == 2): ?><th>Departamento</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows == 0): ?>
                    <tr><td colspan="10" class="text-muted">No hay registros.</td></tr>
                <?php else: while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <?php if ($id_rol != 3): ?>
                            <td><?= htmlspecialchars($row['nombre'].' '.$row['apellido']) ?></td>
                        <?php endif; ?>
                        <td>₡<?= number_format($row['salario_base'], 2) ?></td>
                        <td>₡<?= number_format($row['total_bonos'], 2) ?></td>
                        <td>₡<?= number_format($row['total_deducciones'], 2) ?></td>
                        <td>₡<?= number_format($row['pago_horas_extras'], 2) ?></td>
                        <td>₡<?= number_format($row['salario_neto'], 2) ?></td>
                        <td><?= htmlspecialchars($row['fecha_pago']) ?></td>
                        <td><?= htmlspecialchars($row['tipo_quincena']) ?></td>
                        <?php if ($id_rol == 2): ?>
                            <td><?= htmlspecialchars($row['departamento']) ?></td>
                        <?php endif; ?>
                    </tr>
                <?php endwhile; endif; ?>
            </tbody>
        </table>
    </div>

</div>
</div>
</body>
</html>
