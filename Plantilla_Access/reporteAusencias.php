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
    <h1>Reporte de Ausencias</h1>

    <form method="POST" action="exportarReporte.php">
    <button type="submit">Exportar Reporte</button>
  
</form>
    <table border="1">
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