<?php
session_start();
require 'conexion.php';
include 'template.php';

$query = "SELECT u.nombre AS empleado, COUNT(a.id_ausencia) AS total_ausencias, MONTH(a.fecha) AS mes
          FROM Ausencias a
          JOIN Usuario u ON a.id_usuario = u.id_usuario
          GROUP BY u.nombre, MONTH(a.fecha)";
$result = $conn->query($query);

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Ausencias</title>
</head>

<body>
    <div class="container">
        <h1>Reporte de Ausencias</h1>

        <form method="POST" action="exportarReporte.php">
            <button class="btn" type="submit">Exportar Reporte</button>

        </form>
        <table >
            <thead>
                <tr>
                    <th>Empleado</th>
                    <th>Total Ausencias</th>
                    <th>Mes</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $row): ?>
                    <tr>
                        <td><?php echo $row['empleado']; ?></td>
                        <td><?php echo $row['total_ausencias']; ?></td>
                        <td><?php echo $row['mes']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <h2>Gráfico de Ausencias</h2>
    <canvas id="ausenciasChart" width="400" height="200"></canvas>
    <script>
        const data = <?php echo json_encode($data); ?>;
        const labels = [...new Set(data.map(item => item.mes))];
        const dataset = labels.map(label => {
            return data.filter(item => item.mes == label).reduce((sum, item) => sum + parseInt(item.total_ausencias), 0);
        });

        const ctx = document.getElementById('ausenciasChart').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Ausencias por Mes',
                    data: dataset,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

    </script>
</body>

</html>

<style>
    body {
        font-family: 'Ruda', sans-serif;
        background-color: #f7f7f7;
        /* Blanco cremoso */
        margin: 0;
        padding: 0;
    }

    .card-body {
        padding: 27px;
        margin-bottom: 0;
        margin-top: 10%;
        background-color: #f7f7f7;
        /* Blanco cremoso */

        /* Eliminar margen inferior */
        padding-bottom: 0;
        /* Eliminar padding inferior */
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.6);
    }

    .card-footer {
        margin-top: 0;
        /* Si tienes una sección card-footer, asegúrate de que no tenga márgenes */
    }

    select {
        width: 70%;
        padding: 10px;
        font-size: 16px;
        border: 2px solid rgb(15, 15, 15);
        border-radius: 5px;
        background: #f9f9f9;
        cursor: pointer;
        transition: all 0.3s ease;
        text-align: center;
    }

    select:hover {
        border-color: #a88c4a;
    }

    select:focus {
        outline: none;
        border-color: #805d24;
        box-shadow: 0 0 5px rgba(200, 150, 60, 0.6);
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
    }


    .row {
        display: flex;
        justify-content: center;
        align-items: center;

    }

    h2 {
        text-align: center;
        color: #333;
        margin-bottom: 50px;
        margin-right: 0%;
        font-weight: bold;
    }

    h3 {
        text-align: center;
        color: black;
        margin-bottom: 50px;
        margin-right: 10%;
        font-weight: bold;
    }

    h5 {
        color: black;
        font-weight: bold;
    }

    .btn {
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



    .btn:hover {
        background-color: #c9aa5f;
    }

    .btn:active {
        background-color: #c9aa5f;
    }


    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        border-radius: 8px;
        overflow: hidden;
    }

    th,
    td {
        padding: 12px;
        text-align: center;
        font-size: 16px;
        color: #555;
        border-bottom: 1px solid #ddd;
    }

    th {
        background-color: #f7f7f7;
        /* Blanco cremoso */
        color: black;
    }

    tr:hover {
        background-color: #f1f1f1;
    }

    td {
        background-color: #f9f9f9;
    }

    .no-records {
        text-align: center;
        font-style: italic;
        color: #888;
    }

    /* Estilos del fondo del modal */
    .modal {
        display: none;
        position: fixed;
        z-index: 1;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        justify-content: center;
        align-items: center;
    }

    /* Contenido del modal */
    .modal-content {
        background-color: white;
        padding: 20px;
        border-radius: 10px;
        width: 300px;
        text-align: center;
        margin-bottom: 5%;

    }

    /* Botón de cerrar */
    .close {
        position: absolute;
        top: 10px;
        right: 20px;
        font-size: 25px;
        cursor: pointer;
    }

    /* Botones dentro del modal */
    .modal-content a {
        display: block;
        margin: 10px 0;
        padding: 10px;
        text-decoration: none;
        color: white;
        background-color: gray;
        border-radius: 5px;
        background-color: #c9aa5f;
    }

    .modal-content a:hover {
        background-color: darkgray;
    }

    /* Estilos para los botones alineados */
    .button-container {
        display: flex;
        justify-content: space-between;
        /* Distribuye el espacio entre los botones */
        width: 100%;
    }

    .close-button {
        border: none;
        display: inline-block;
        padding: 8px 16px;
        vertical-align: middle;
        overflow: hidden;
        text-decoration: none;
        color: inherit;
        background-color: inherit;
        text-align: center;
        cursor: pointer;
        white-space: nowrap
    }

    .topright {
        position: absolute;
        right: 0;
        top: 0
    }
</style>