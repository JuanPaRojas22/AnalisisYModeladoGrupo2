<?php 
include 'conexion.php'; 
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
include 'template.php';

$id_usuario = $_GET['id_usuario'] ?? null;
$id_departamento = $_GET['id_departamento'] ?? null;

$sql = "SELECT 
            h.id_historial,
            u.nombre AS empleado,
            d.nombre AS departamento,
            h.Razon,
            h.DiasTomados,
            h.FechaInicio,
            h.FechaFin,
            h.DiasRestantes
        FROM historial_vacaciones h
        LEFT JOIN usuario u ON h.id_usuario = u.id_usuario
        LEFT JOIN departamento d ON u.id_departamento = d.id_departamento
        WHERE 1 = 1";

$params = [];
if ($id_usuario) { 
    $sql .= " AND h.id_usuario = ?";
    $params[] = $id_usuario;
}
if ($id_departamento) { 
    $sql .= " AND u.id_departamento = ?";
    $params[] = $id_departamento;
}

$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param(str_repeat("i", count($params)), ...$params);
$stmt->execute();
$result = $stmt->get_result();
$historial = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Historial de Vacaciones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Ruda:wght@400;700&display=swap');

        body {
            font-family: 'Ruda', sans-serif;
            background-color: #f7f7f7;
            padding: 20px;
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
        }

        /* 📌 FILTROS AJUSTADOS */
        .form-select, .form-control {
            font-size: 16px;
            padding: 10px;
        }

        .btn-primary {
            font-size: 16px;
            padding: 10px 20px;
        }

        /* 📌 ESTILO EXACTO DE LA TABLA */
        .table-container {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            border-radius: 8px;
            overflow: hidden;
            font-size: 16px;
        }

        /* 📌 ENCABEZADOS DORADOS */
        thead {
            background-color: #c9aa5f !important;
        }

        th {
            background-color: #c9aa5f !important; /* Color dorado forzado */
            color: white !important;
            text-align: center;
            padding: 14px;
            border: 1px solid #c9aa5f !important; /* Bordes dorados */
        }

        td {
            text-align: center;
            padding: 12px;
            border: 1px solid #c9aa5f; /* Bordes dorados */
            background-color: #f9f9f9; /* Fondo blanco */
        }

        tr:nth-child(even) td {
            background-color: #f1f1f1; /* Filas alternas gris claro */
        }

        tr:hover td {
            background-color: #e0d5b9; /* Efecto hover */
        }

        /* Botón de Descargar PDF */
        .pdf-container {
            display: flex;
            justify-content: center;
            margin-top: 15px;
        }

        .btn-danger {
            font-size: 16px;
            padding: 10px 20px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="title-container">
        <h2 class="fw-bold">Reporte de Historial de Vacaciones</h2>
    </div>

    <div class="card shadow">
        <!-- Formulario de filtros -->
        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-6">
                <label class="form-label">Empleado:</label>
                <select class="form-select" name="id_usuario">
                    <option value="">Todos</option>
                    <?php
                    $query_emp = "SELECT id_usuario, nombre FROM usuario";
                    $result_emp = $conn->query($query_emp);
                    while ($emp = $result_emp->fetch_assoc()) {
                        $selected = ($id_usuario == $emp['id_usuario']) ? "selected" : "";
                        echo "<option value='{$emp['id_usuario']}' $selected>{$emp['nombre']}</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Departamento:</label>
                <select class="form-select" name="id_departamento">
                    <option value="">Todos</option>
                    <?php
                    $query_dept = "SELECT id_departamento, nombre FROM departamento";
                    $result_dept = $conn->query($query_dept);
                    while ($dept = $result_dept->fetch_assoc()) {
                        $selected = ($id_departamento == $dept['id_departamento']) ? "selected" : "";
                        echo "<option value='{$dept['id_departamento']}' $selected>{$dept['nombre']}</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="col-md-12 text-center">
                <button type="submit" class="btn btn-primary">Filtrar</button>
            </div>
        </form>
    </div>

    <!-- 📌 TABLA ESTILIZADA -->
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Empleado</th>
                    <th>Departamento</th>
                    <th>Razón</th>
                    <th>Días Tomados</th>
                    <th>Fecha Inicio</th>
                    <th>Fecha Fin</th>
                    <th>Días Restantes</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($historial)) : ?>
                    <tr><td colspan="8" class="text-center text-muted">No se encontraron registros.</td></tr>
                <?php else: ?>
                    <?php foreach ($historial as $fila) : ?>
                    <tr>
                        <td><?= $fila['id_historial'] ?></td>
                        <td><?= $fila['empleado'] ?></td>
                        <td><?= $fila['departamento'] ?></td>
                        <td><?= $fila['Razon'] ?></td>
                        <td><?= $fila['DiasTomados'] ?></td>
                        <td><?= $fila['FechaInicio'] ?></td>
                        <td><?= $fila['FechaFin'] ?></td>
                        <td><?= $fila['DiasRestantes'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="pdf-container">
    <form action="generar_pdf.php" method="GET">
        <input type="hidden" name="id_usuario" value="<?= htmlspecialchars($id_usuario) ?>">
        <input type="hidden" name="id_departamento" value="<?= htmlspecialchars($id_departamento) ?>">
        <button type="submit" class="btn btn-danger">Descargar PDF</button>
    </form>
</div>
</div>

</body>
</html>
