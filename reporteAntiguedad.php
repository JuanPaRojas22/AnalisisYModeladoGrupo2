<?php
// Conexión a la base de datos
// Parámetros de conexión
$host = "accespersoneldb.mysql.database.azure.com";
$user = "adminUser";
$password = "admin123+";
$dbname = "gestionEmpleados";
$port = 3306;

// Ruta al certificado CA para validar SSL
$ssl_ca = '/home/site/wwwroot/certs/BaltimoreCyberTrustRoot.crt.pem';

// Inicializamos mysqli
$conn = mysqli_init();

// Configuramos SSL
mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);
mysqli_options($conn, MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);


// Intentamos conectar usando SSL (con la bandera MYSQLI_CLIENT_SSL)
if (!$conn->real_connect($host, $user, $password, $dbname, $port, NULL, MYSQLI_CLIENT_SSL)) {
    die("Error de conexión: " . mysqli_connect_error());
}

// Establecemos el charset
mysqli_set_charset($conn, "utf8mb4");
session_start();
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
        margin-top: 3%;
        /*Correrlo para la derecha*/
        margin-left: 15%;
        justify-content: flex-start;
        /* Alinea hacia la parte superior */
        align-items: center;
        /* Centra los elementos horizontalmente */
        padding: 20px;
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
                    td, div {
            color: black !important;
        }
        .search-container {
            text-align: center;
            margin-bottom: 20px;
        }

        .search-container input {
            padding: 10px;
            width: 50%;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

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
        }

        .pagination button.active {
            background-color: #147964;
            color: white;
        }

        .pagination button:hover {
            background-color: #ddd;
        }
        .search-container {
            text-align: center;
            margin-bottom: 20px;
        }

        .search-container input {
            padding: 10px;
            width: 50%;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
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
        <table class="table table-bordered" id="dataTable">
            <thead>
                <tr>
                    
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
        <div class="pagination" id="pagination">
            <!-- Los botones de paginación se generarán aquí -->
        </div>
        <script>
        // Variables para la paginación
        const rowsPerPage = 10;
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

        // Función para buscar en la tabla
        function searchTable() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toLowerCase();

            rows.forEach(row => {
                const cells = Array.from(row.querySelectorAll('td'));
                const rowText = cells.map(cell => cell.textContent.toLowerCase()).join(' ');
                row.style.display = rowText.includes(filter) ? '' : 'none';
            });

            // Actualizar paginación después de filtrar
            setupPagination();
        }

        // Inicializar paginación
        setupPagination();
    </script>
</body>
</html>
