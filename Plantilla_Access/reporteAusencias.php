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
        <div style="display: flex; justify-content: center; margin-bottom: 20px;">
    <button type="submit" class="btn">Exportar Reporte</button>
</div>

        </form>
        <table id="dataTable">
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
        <div class="pagination" id="pagination">
        </div>
    </div>

    
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
    <script>
        const rowsPerPage = 10; // Número de filas por página
        const table = document.getElementById('dataTable');
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        const pagination = document.getElementById('pagination');

        // Función para mostrar una página específica
        function displayPage(page) {
            const start = (page - 1) * rowsPerPage;
            const end = start + rowsPerPage;

            rows.forEach((row, index) => {
                row.style.display = index >= start && index < end ? '' : 'none';
            });

            // Actualizar botones de paginación
            const buttons = pagination.querySelectorAll('button');
            buttons.forEach((button, index) => {
                button.classList.toggle('active', index + 1 === page);
            });
        }

        // Crear botones de paginación
        function setupPagination() {
            const totalPages = Math.ceil(rows.length / rowsPerPage);
            pagination.innerHTML = '';

            for (let i = 1; i <= totalPages; i++) {
                const button = document.createElement('button');
                button.textContent = i;
                button.addEventListener('click', () => displayPage(i));
                pagination.appendChild(button);
            }

            // Mostrar la primera página por defecto
            displayPage(1);
        }

        // Inicializar paginación
        setupPagination();
    </script>
</body>

</html>
<style>
    .pagination {
    display: flex;
    justify-content: center;
    margin-top: 20px;
}

.pagination button {
    margin: 0 5px;
    padding: 8px 12px;
    border: 1px solid #ccc;
    background-color: #f7f7f7;
    cursor: pointer;
    border-radius: 5px;
    transition: background-color 0.3s;
}

.pagination button.active {
    background-color: #147964;
    color: white;
}

.pagination button:hover {
    background-color: #ddd;
}
body {
    font-family: 'Ruda', sans-serif;
    background-color: #f7f7f7;
    margin: 0;
    padding: 0;
}

.container {
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    align-items: center;
    background-color: #fff;
    margin-top: 50px;
    padding: 40px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    width: 80%; /* Reduced width */
    max-width: 1000px; /* Limiting the width */
}

h1 {
    font-size: 2em; /* Adjust font size */
    color: #333;
    text-align: center;
    margin-bottom: 30px;
    font-weight: bold;
}

h2 {
    font-size: 1.5em;
    text-align: center;
    color: #333;
    margin-top: 40px;
    font-weight: bold;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    border-radius: 8px;
    overflow: hidden;
}

th, td {
    padding: 10px 12px; /* Reduced padding */
    text-align: center;
    font-size: 14px; /* Reduced font size */
    color: #555;
    border-bottom: 1px solid #ddd;
}

th {
    background-color: #116B67;
    color: #fff;
}

tr:hover {
    background-color: #f1f1f1;
}

td {
    background-color: #f9f9f9;
}

.btn {
    display: inline-block;
    background-color: #147964;
    color: white;
    padding: 10px 18px; /* Adjusted padding */
    font-size: 14px; /* Adjusted font size */
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

.btn:active {
    background-color: #0B4F6C;
}

select {
    width: 70%;
    padding: 10px;
    font-size: 14px; /* Adjusted font size */
    border: 2px solid rgb(15, 15, 15);
    border-radius: 5px;
    background: #f9f9f9;
    margin-bottom: 15px;
    cursor: pointer;
    transition: all 0.3s ease;
}

select:hover {
    border-color: #116B67;
}

select:focus {
    outline: none;
    border-color: #147964;
    box-shadow: 0 0 5px rgba(20, 121, 100, 0.4);
}

form {
    width: 100%;
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 20px;
}

.form-group {
    flex-basis: 48%;
    display: flex;
    flex-direction: column;
}

label {
    margin-bottom: 8px;
    font-weight: bold;
    color: #333;
}

input[type="date"], input[type="text"], select {
    padding: 12px;
    font-size: 14px;
    border-radius: 8px;
    border: 1px solid #ddd;
    background-color: #f9f9f9;
    transition: all 0.3s ease;
}

input[type="date"]:focus, input[type="text"]:focus, select:focus {
    border-color: #147964;
    box-shadow: 0 0 8px rgba(20, 121, 100, 0.4);
    outline: none;
}

@media (max-width: 768px) {
    .container {
        margin-top: 20px;
        padding: 20px;
    }

    h1 {
        font-size: 2em;
    }

    .form-group {
        flex-basis: 100%;
    }

    .btn {
        padding: 10px 18px;
        font-size: 1em;
    }

    table {
        font-size: 14px;
    }
}
</style>