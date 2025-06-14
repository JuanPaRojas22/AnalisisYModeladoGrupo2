<?php
include 'conexion.php';
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
if ($_SESSION['id_rol'] == 3 || $_SESSION['id_rol'] == 1) {
    header("Location: index.php");
    exit;
}
include 'template.php';

// Filtros
$id_usuario = $_GET['id_usuario'] ?? null;
$id_departamento = $_GET['id_departamento'] ?? null;
$id_estado_vacacion = $_GET['id_estado_vacacion'] ?? null;

// Paginación
$resultadosPorPagina = 5;
$paginaActual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$offset = ($paginaActual - 1) * $resultadosPorPagina;

// Armar filtros y conteo total
$sqlTotal = "SELECT COUNT(*) as total FROM vacacion v
    INNER JOIN usuario u ON v.id_usuario = u.id_usuario
    INNER JOIN departamento d ON u.id_departamento = d.id_departamento
    INNER JOIN estado_vacacion ev ON v.id_estado_vacacion = ev.id_estado_vacacion
    INNER JOIN historial_vacaciones h ON v.id_historial = h.id_historial
    WHERE 1 = 1";
$params = [];
$tipos = "";

if ($id_usuario) {
    $sqlTotal .= " AND h.id_usuario = ?";
    $params[] = $id_usuario;
    $tipos .= "i";
}
if ($id_departamento) {
    $sqlTotal .= " AND u.id_departamento = ?";
    $params[] = $id_departamento;
    $tipos .= "i";
}
if ($id_estado_vacacion) {
    $sqlTotal .= " AND v.id_estado_vacacion = ?";
    $params[] = $id_estado_vacacion;
    $tipos .= "i";
}
$stmtTotal = $conn->prepare($sqlTotal);
if ($params) $stmtTotal->bind_param($tipos, ...$params);
$stmtTotal->execute();
$totalFilas = $stmtTotal->get_result()->fetch_assoc()['total'];
$totalPaginas = ceil($totalFilas / $resultadosPorPagina);
$stmtTotal->close();

// Consulta con paginación
$sql = "SELECT
        v.id_vacacion,
        u.nombre AS empleado,
        d.nombre AS departamento,
        v.razon,
        v.diasTomado,
        v.fecha_inicio,
        v.fecha_fin,
        h.DiasRestantes,
        ev.descripcion AS estado
    FROM vacacion v
    INNER JOIN usuario u ON v.id_usuario = u.id_usuario
    INNER JOIN departamento d ON u.id_departamento = d.id_departamento
    INNER JOIN estado_vacacion ev ON v.id_estado_vacacion = ev.id_estado_vacacion
    INNER JOIN historial_vacaciones h ON v.id_historial = h.id_historial
    WHERE 1 = 1";

if ($id_usuario) $sql .= " AND h.id_usuario = ?";
if ($id_departamento) $sql .= " AND u.id_departamento = ?";
if ($id_estado_vacacion) $sql .= " AND v.id_estado_vacacion = ?";
$sql .= " LIMIT ? OFFSET ?";

$params[] = $resultadosPorPagina;
$params[] = $offset;
$tipos .= "ii";

$stmt = $conn->prepare($sql);
$stmt->bind_param($tipos, ...$params);
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

        .btn{
            display: inline-block;
            color: #fff;
    background-color: #0B4F6C;
    border-color: #0B4F6C;
            padding: 12px 20px;
            font-size: 16px;
            font-weight: bold;
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 20px;
            transition: background-color 0.3s;
            cursor: pointer;
            border: none;
                    }

                    .btn:hover {
                        background-color: #0B4F6C;
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
            background-color: #116B67 !important;
        }

        th {
            background-color: #116B67 !important; /* Color dorado forzado */
            color: white !important;
            text-align: center;
            padding: 14px;
            border: 1px solid #116B67 !important; /* Bordes dorados */
        }

        td {
            text-align: center;
            padding: 12px;
            border: 1px solid #116B67; /* Bordes dorados */
            background-color: #f9f9f9; /* Fondo blanco */
        }

        tr:nth-child(even) td {
            background-color: #f1f1f1; /* Filas alternas gris claro */
        }

        

        /* Botón de Descargar PDF */
        .pdf-container {
            display: flex;
            justify-content: center;
            margin-top: 15px;
        }

        .btn-export {
                        display: inline-block;
                        background-color: #168761;
                        color: white;
                        padding: 12px 20px;
                        font-size: 16px;
                        font-weight: bold;
                        text-decoration: none;
                        border-radius: 5px;
                        margin-bottom: 20px;
                        transition: background-color 0.3s;
                        cursor: pointer;
                        border: none;
                    }

                    .btn-export:hover {
                        background-color: #168761;
                    }
                    .pagination {
    width: 80%;
    margin: 40px auto 60px auto; /* aumenta separación superior e inferior */
    justify-content: center; /* centrado */
    gap: 10px; /* espacio entre botones */
}

.pagination .page-link {
    color: #147964;
    background-color: #f9f9f9;
    border: 2px solid #147964;
    font-weight: bold;
    font-size: 18px; /* ✅ más grande */
    padding: 12px 18px; /* ✅ más ancho y alto */
    border-radius: 6px;
    transition: all 0.2s ease-in-out;
}

.pagination .page-link:hover {
    background-color: #e0f4f2;
}

.pagination .page-item.active .page-link {
    background-color: #116B67;
    color: white;
    border-color: #116B67;
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

            <div class="col-md-6">
                <label class="form-label">Estado:</label>
                <select class="form-select" name="id_estado_vacacion">
                    <option value="">Todos</option>
                    <?php
                    $query_dept = "SELECT id_estado_vacacion, descripcion FROM estado_vacacion";
                    $result_dept = $conn->query($query_dept);
                    while ($dept = $result_dept->fetch_assoc()) {
                        $selected = ($id_estado_vacacion == $dept['id_estado_vacacion']) ? "selected" : "";
                        echo "<option value='{$dept['id_estado_vacacion']}' $selected>{$dept['descripcion']}</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="col-md-12 text-center">
                <button type="submit" class="btn">Filtrar</button>
            </div>
        </form>
    </div>

    <!-- 📌 TABLA ESTILIZADA -->
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Empleado</th>
                    <th>Departamento</th>
                    <th>Razón</th>
                    <th>Días Tomados</th>
                    <th>Fecha Inicio</th>
                    <th>Fecha Fin</th>
                    <th>Días Restantes</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($historial)) : ?>
                    <tr><td colspan="8" class="text-center text-muted">No se encontraron registros.</td></tr>
                <?php else: ?>
                    <?php foreach ($historial as $fila) : ?>
                    <tr>
                        <td><?= $fila['empleado'] ?></td>
                        <td><?= $fila['departamento'] ?></td>
                        <td><?= $fila['razon'] ?></td>
                        <td><?= $fila['diasTomado'] ?></td>
                        <td><?= $fila['fecha_inicio'] ?></td>
                        <td><?= $fila['fecha_fin'] ?></td>
                        <td><?= $fila['DiasRestantes'] ?></td>
                        <td><?= $fila['estado'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="text-center mt-4">
    <!-- Botón para descargar reporte -->
    <form action="generar_pdf.php" method="GET" class="mb-3">
        <input type="hidden" name="id_usuario" value="<?= htmlspecialchars($id_usuario) ?>">
        <input type="hidden" name="id_departamento" value="<?= htmlspecialchars($id_departamento) ?>">
        <input type="hidden" name="id_estado_vacacion" value="<?= htmlspecialchars($id_estado_vacacion) ?>">
        <button type="submit" class="btn btn-success">
            📥 Descargar Reporte
        </button>
    </form>

    <!-- Paginación debajo del botón -->
    <nav>
        <ul class="pagination justify-content-center">
            <?php if ($paginaActual > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?pagina=<?= $paginaActual - 1 ?>&id_usuario=<?= $id_usuario ?>&id_departamento=<?= $id_departamento ?>&id_estado_vacacion=<?= $id_estado_vacacion ?>">Anterior</a>
                </li>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                <li class="page-item <?= ($i == $paginaActual) ? 'active' : '' ?>">
                    <a class="page-link" href="?pagina=<?= $i ?>&id_usuario=<?= $id_usuario ?>&id_departamento=<?= $id_departamento ?>&id_estado_vacacion=<?= $id_estado_vacacion ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>

            <?php if ($paginaActual < $totalPaginas): ?>
                <li class="page-item">
                    <a class="page-link" href="?pagina=<?= $paginaActual + 1 ?>&id_usuario=<?= $id_usuario ?>&id_departamento=<?= $id_departamento ?>&id_estado_vacacion=<?= $id_estado_vacacion ?>">Siguiente</a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
</div>

</div>
</div>

</body>
</html>
