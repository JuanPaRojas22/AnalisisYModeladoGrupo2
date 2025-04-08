<?php
// filepath: c:\xampp\htdocs\AnalisisYModeladoGrupo2\Plantilla_Access\reporte_ccss.php

// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "gestionempleados");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Consulta para obtener los datos de las tablas usuario, planilla, nacionalidades, ocupaciones y departamento
$sql = "
    SELECT 
        u.id_usuario,
        u.nombre,
        u.apellido,
        u.correo_electronico,
        u.numero_telefonico,
        u.fecha_nacimiento,
        u.sexo,
        u.estado_civil,
        n.pais AS nacionalidad,
        o.nombre_ocupacion,
        p.jornada,
        p.hrs,
        p.salario_base,
        p.salario_neto,
        p.codigo_CCSS,
        u.direccion_domicilio,
        p.tipo_quincena,
        d.nombre AS departamento
    FROM usuario u
    JOIN planilla p ON u.id_usuario = p.id_usuario
    JOIN nacionalidades n ON u.id_nacionalidad = n.id_nacionalidad
    LEFT JOIN ocupaciones o ON u.id_ocupacion = o.id_ocupacion
    LEFT JOIN departamento d ON u.id_departamento = d.id_departamento
";

$resultado = $conexion->query($sql);

session_start();
include 'template.php';
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Invitado';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte CCSS</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Ruda:wght@400;700&display=swap');

        body {
            font-family: 'Ruda', sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 1000px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        h1 {
            color: #333;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .btn-export {
            display: inline-block;
            background-color: #c9aa5f;
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
            background-color: #b5935b;
        }

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #c9aa5f;
            color: white;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .btn-more {
            background-color: #c9aa5f;
            color: white;
            padding: 6px 12px;
            font-size: 14px;
            font-weight: bold;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-more:hover {
            background-color: #b5935b;
        }

        .details {
            display: none;
            background: #fff7e6;
            padding: 15px;
            border-radius: 8px;
            margin-top: 5px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease-in-out;
        }

        .details.show {
            display: block;
            animation: fadeIn 0.3s ease-in-out;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>📋 Reporte CCSS</h1>

    <!-- Botón para exportar el reporte -->
    <a href="exportar_ccss_excel.php" class="btn-export">
        📥 Descargar Reporte
    </a>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Codigo CCSS</th>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Correo</th>
                    <th>Teléfono</th>
                    <th>Salario Base</th>
                    <th>Salario Neto</th>
                    <th>Nacionalidad</th>
                    <th>Ocupación</th>
                    <th>Departamento</th>
                    <th>Tipo de Quincena</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($fila = $resultado->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $fila['codigo_CCSS']; ?></td>
                    <td><?php echo $fila['nombre']; ?></td>
                    <td><?php echo $fila['apellido']; ?></td>
                    <td><?php echo $fila['correo_electronico']; ?></td>
                    <td><?php echo $fila['numero_telefonico']; ?></td>
                    <td>₡<?php echo number_format($fila['salario_base'], 2); ?></td>
                    <td>₡<?php echo number_format($fila['salario_neto'], 2); ?></td>
                    <td><?php echo $fila['nacionalidad']; ?></td>
                    <td><?php echo $fila['nombre_ocupacion']; ?></td>
                    <td><?php echo $fila['departamento']; ?></td>
                    <td><?php echo $fila['tipo_quincena']; ?></td>
                    <td>
                        <button class="btn-more" onclick="toggleDetails('details-<?php echo $fila['id_usuario']; ?>')">
                            Ver más
                        </button>
                    </td>
                </tr>
                <tr class="details" id="details-<?php echo $fila['id_usuario']; ?>">
                    <td colspan="12">
                        <p><strong>Fecha de Nacimiento:</strong> <?php echo $fila['fecha_nacimiento']; ?></p>
                        <p><strong>Sexo:</strong> <?php echo $fila['sexo']; ?></p>
                        <p><strong>Estado Civil:</strong> <?php echo $fila['estado_civil']; ?></p>
                        <p><strong>Jornada:</strong> <?php echo $fila['jornada']; ?></p>
                        <p><strong>Horas Trabajadas:</strong> <?php echo $fila['hrs']; ?></p>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    function toggleDetails(id) {
        var element = document.getElementById(id);
        if (element.classList.contains("show")) {
            element.classList.remove("show");
        } else {
            element.classList.add("show");
        }
    }
</script>

</body>
</html>

<?php
// Cerrar la conexión
$conexion->close();
?>