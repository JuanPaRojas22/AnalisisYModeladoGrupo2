<?php
include 'conexion.php'; // Conexión a la BD
session_start();
include 'template.php';

// Obtener filtros desde el formulario
$id_usuario = !empty($_GET['id_usuario']) ? $_GET['id_usuario'] : null;
$id_departamento = !empty($_GET['id_departamento']) ? $_GET['id_departamento'] : null;

// Construcción de la consulta SQL con `JOIN`
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

$param_types = "";
$params = [];

if (!empty($id_usuario)) { 
    $sql .= " AND h.id_usuario = ?";
    $param_types .= "i"; 
    $params[] = $id_usuario;
}
if (!empty($id_departamento)) { 
    $sql .= " AND u.id_departamento = ?";
    $param_types .= "i"; 
    $params[] = $id_departamento;
}

// Preparar la consulta SQL
$stmt = $conn->prepare($sql);

// Verificar si la preparación fue exitosa
if (!$stmt) { 
    die("Error en la consulta SQL: " . $conn->error);
}

// Enlazar los parámetros si existen
if (!empty($param_types)) {
    $stmt->bind_param($param_types, ...$params);
}

// Ejecutar la consulta
$stmt->execute();

// Obtener los resultados
$result = $stmt->get_result();
$historial = [];
while ($fila = $result->fetch_assoc()) { 
    $historial[] = $fila;
}

// Cerrar la consulta
$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Historial de Vacaciones</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 20px; background-color: #f8f9fa; }
        .container { max-width: 1000px; margin: auto; }
        .title-container { text-align: center; margin-top: 50px; margin-bottom: 30px; }
        .card { border-radius: 10px; box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1); }
        .table { font-size: 14px; }
        .form-select, .form-control { font-size: 14px; }
        .btn { font-size: 14px; padding: 6px 12px; }
    </style>
</head>
<body>

<div class="container">
    <div class="title-container">
        <h2 class="fw-bold">Reporte de Historial de Vacaciones</h2>
    </div>

    <div class="card shadow p-4">
        <!-- Formulario de filtros -->
        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-6">
                <label class="form-label">Empleado:</label>
                <select class="form-select" name="id_usuario">
                    <option value="">Todos</option>
                    <?php
                    // Obtener todos los empleados desde la BD
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
                    // Obtener todos los departamentos desde la BD
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

    <!-- Tabla de resultados -->
    <div class="card shadow p-4 mt-4">
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-dark text-center">
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
                    <?php foreach ($historial as $fila) : ?>
                    <tr>
                        <td class="text-center"><?= $fila['id_historial'] ?></td>
                        <td><?= $fila['empleado'] ?></td>
                        <td><?= $fila['departamento'] ?></td>
                        <td><?= $fila['Razon'] ?></td>
                        <td class="text-center"><?= $fila['DiasTomados'] ?></td>
                        <td class="text-center"><?= $fila['FechaInicio'] ?></td>
                        <td class="text-center"><?= $fila['FechaFin'] ?></td>
                        <td class="text-center"><?= $fila['DiasRestantes'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Botón para generar PDF -->
        <div class="text-center mt-3">
            <form action="generar_pdf.php" method="GET">
                <input type="hidden" name="id_usuario" value="<?= $id_usuario ?>">
                <input type="hidden" name="id_departamento" value="<?= $id_departamento ?>">
                <button type="submit" class="btn btn-danger">Descargar PDF</button>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
