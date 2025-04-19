<?php
session_start();
require 'conexion.php';
include 'template.php';
// Consulta para calcular la antigüedad y obtener los beneficios
$query = "
    SELECT 
        u.id_usuario,
        CONCAT(u.nombre, ' ', u.apellido) AS nombre_completo,
        u.fecha_ingreso,
        TIMESTAMPDIFF(YEAR, u.fecha_ingreso, CURDATE()) AS antiguedad_anios,
        TIMESTAMPDIFF(MONTH, u.fecha_ingreso, CURDATE()) % 12 AS antiguedad_meses,
        b.razon AS beneficio,
        b.monto AS monto_beneficio,
        b.fechacreacion AS fecha_beneficio
    FROM Usuario u
    LEFT JOIN Beneficios b ON u.id_usuario = b.id_usuario AND b.razon = 'Bono por Antigüedad'
    WHERE u.fecha_ingreso IS NOT NULL
    ORDER BY antiguedad_anios DESC, antiguedad_meses DESC
";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Antigüedad Laboral</title>

  
</head>
<style>
    
    body {
            font-family: 'Ruda', sans-serif;
            background-color: #f7f7f7;
            padding: 20px;
        }

        .container {
        display: flex;
        flex-direction: column;
        background-color: #f7f7f7;
        /* Blanco cremoso */
        margin-top: 10%;
        justify-content: flex-start;
        /* Alinea hacia la parte superior */
        align-items: center;
        /* Centra los elementos horizontalmente */
        padding: 10px;
        max-width: 100%;
        box-shadow: 0 0 5px rgba(0, 0, 0, 0.6);
        border-radius: 10px;
        color: black;
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
      
            background-color: #f9f9f9; /* Fondo blanco */
        }

        tr:nth-child(even) td {
            background-color: #f1f1f1; /* Filas alternas gris claro */
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
    </style>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Reporte de Antigüedad Laboral</h2>
        <form method="POST" action="exportarAntiguedad.php">
        <button type="submit" class="btn btn-success" style="background-color: #147964; color: white; border: none; padding: 12px 20px; font-size: 16px; font-weight: bold; border-radius: 5px; transition: background-color 0.3s;">
    Exportar Reporte
</button>
</form>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre Completo</th>
                    <th>Fecha de Ingreso</th>
                    <th>Antigüedad (Años)</th>
                    <th>Antigüedad (Meses)</th>
                    <th>Beneficio</th>
                    <th>Monto del Beneficio</th>
                    <th>Fecha del Beneficio</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id_usuario'] ?></td>
                        <td><?= htmlspecialchars($row['nombre_completo']) ?></td>
                        <td><?= $row['fecha_ingreso'] ?></td>
                        <td><?= $row['antiguedad_anios'] ?></td>
                        <td><?= $row['antiguedad_meses'] ?></td>
                        <td><?= htmlspecialchars($row['beneficio'] ?? 'N/A') ?></td>
                        <td><?= $row['monto_beneficio'] ?? 'N/A' ?></td>
                        <td><?= $row['fecha_beneficio'] ?? 'N/A' ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>