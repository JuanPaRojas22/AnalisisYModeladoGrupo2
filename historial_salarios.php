<?php
session_start();
require 'conexion.php';
require 'template.php';

if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['id_rol'])) {
    header("Location: login.php");
    exit;
}

$id_usuario = $_SESSION['id_usuario'];
$id_rol = $_SESSION['id_rol'];
$id_departamento = $_SESSION['id_departamento'] ?? null;
$departamento_filtro = $_GET['departamento'] ?? '';

$departamentos = [];
if ($id_rol == 2) { // Solo el admin general puede ver todos los departamentos
    $query_dept = "SELECT id_departamento, nombre FROM departamento";
    $result_dept = $conn->query($query_dept);
    while ($row = $result_dept->fetch_assoc()) {
        $departamentos[] = $row;
    }
}

if ($id_rol == 3) {
    // Empleado: ve solo sus propios registros
    $sql = "SELECT * FROM pago_planilla WHERE id_usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_usuario);

} elseif ($id_rol == 1) {
    // Admin normal: ve los empleados de su mismo departamento
    $sql = "SELECT p.*, u.nombre, u.apellido
            FROM pago_planilla p
            JOIN usuario u ON p.id_usuario = u.id_usuario
            WHERE u.id_departamento = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_departamento);

} elseif ($id_rol == 2) {
    // Admin general: puede filtrar por departamento
    $sql = "SELECT p.*, u.nombre, u.apellido, d.Nombre AS departamento
            FROM pago_planilla p
            JOIN usuario u ON p.id_usuario = u.id_usuario
            JOIN departamento d ON u.id_departamento = d.id_departamento";

    if (!empty($departamento_filtro)) {
        $sql .= " WHERE d.id_departamento = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $departamento_filtro);
    } else {
        $stmt = $conn->prepare($sql);
    }
}

$stmt->execute();
$result = $stmt->get_result();
?>

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial de Pagos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f7f7f7;
        }
        .main-container {
            max-width: 1000px;
            margin: 40px auto;
        }
        .card-style {
            background-color: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }
        h2 {
            font-weight: bold;
            margin-bottom: 20px;
            text-align: center;
        }
        .custom-table {
            width: 100%;
            font-size: 14px;
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 10px;
            overflow: hidden;
        }
        .custom-table thead {
            background-color: #116B67;
            color: white;
        }
        .custom-table th,
        .custom-table td {
            padding: 8px 10px;
            text-align: center;
            border: none;
            white-space: nowrap;
        }
        .custom-table tr:nth-child(even) td {
            background-color: #f1f1f1;
        }
        .custom-table tr:nth-child(odd) td {
            background-color: #f9f9f9;
        }
        .custom-table thead th:first-child {
            border-top-left-radius: 10px;
        }
        .custom-table thead th:last-child {
            border-top-right-radius: 10px;
        }
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
        <p class="fw-bold" style="font-size: 16px;">Departamento: <?= htmlspecialchars($nombre_departamento) ?></p>
    </div>
<?php endif; ?>
      <?php if (in_array($id_rol, [1, 2])): ?>
<div class="d-flex justify-content-center align-items-center gap-3 mb-4 flex-wrap">

    <?php if ($id_rol == 2): ?>
        <form method="GET" class="d-flex align-items-center gap-2 flex-wrap">
            <label for="departamento" class="form-label mb-0 fw-bold" style="font-size: 18px;">Departamento:</label>
            <select name="departamento" id="departamento" class="form-select" style="width: 300px; height: 45px; font-size: 16px;">
                <option value="">Todos</option>
                <?php foreach ($departamentos as $dep): ?>
                    <option value="<?= $dep['id_departamento'] ?>" <?= $departamento_filtro == $dep['id_departamento'] ? 'selected' : '' ?>>
                        <?= $dep['nombre'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn" style="background-color: #116B67; color: white; font-weight: bold; padding: 10px 20px; border-radius: 8px; font-size: 16px;">
                Filtrar
            </button>
        </form>
        <a href="reporte_historial_pagos.php?departamento=<?= htmlspecialchars($departamento_filtro) ?>"
           target="_blank"
           class="btn"
           style="background-color: #168761; color: white; font-weight: bold; padding: 10px 20px; border-radius: 8px; font-size: 16px;">
           📄 Descargar PDF
        </a>

    <?php elseif ($id_rol == 1): ?>
       <a href="reporte_historial_pagos.php?id_departamento=<?= $id_departamento ?>"
   target="_blank"
   class="btn"
   style="background-color: #168761; color: white; font-weight: bold; padding: 10px 20px; border-radius: 8px; font-size: 16px;">
   📄 Descargar PDF
</a>

    <?php endif; ?>

</div>
<?php endif; ?>


        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <?php if ($id_rol != 3): ?>
                            <th>Empleado</th>
                        <?php endif; ?>
                        <th>Salario Base</th>
                        <th>Bono</th>
                        <th>Deducción</th>
                        <th>Horas Extra</th>
                        <th>Salario Neto</th>
                        <th>Fecha de Pago</th>
                        <th>Tipo Quincena</th>
                        <?php if ($id_rol == 2): ?>
                            <th>Departamento</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows == 0): ?>
                        <tr><td colspan="10" class="text-muted">No hay registros.</td></tr>
                    <?php else: ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <?php if ($id_rol != 3): ?>
                                    <td><?= $row['nombre'] . ' ' . $row['apellido'] ?></td>
                                <?php endif; ?>
                                <td>₡<?= number_format($row['salario_base'], 2) ?></td>
                                <td>₡<?= number_format($row['total_bonos'], 2) ?></td>
                                <td>₡<?= number_format($row['total_deducciones'], 2) ?></td>
                                <td>₡<?= number_format($row['pago_horas_extras'], 2) ?></td>
                                <td>₡<?= number_format($row['salario_neto'], 2) ?></td>
                                <td><?= $row['fecha_pago'] ?></td>
                                <td><?= $row['tipo_quincena'] ?></td>
                                <?php if ($id_rol == 2): ?>
                                    <td><?= $row['departamento'] ?></td>
                                <?php endif; ?>
                            </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div> 
    </div>
</div>

</body>
</html>
