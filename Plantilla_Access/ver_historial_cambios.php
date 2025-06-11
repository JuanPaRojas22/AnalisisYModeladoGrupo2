<?php

session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "gestionempleados");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Verificar autenticación del usuario
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}

$id_usuario = $_SESSION['id_usuario'];
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Invitado';

// Incluir la plantilla
include 'template.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial</title>
    <link href="assets/css/bootstrap.css" rel="stylesheet">
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/style-responsive.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f5f5;
            font-family: Arial, sans-serif;
        }

        .container {
            max-width: 95%;
            margin: 50px auto;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 28px;
            color: #333;
        }

        .btn {
            display: inline-block;
            background-color: #147964;
            color: white;
            padding: 10px 20px;
            font-weight: bold;
            text-decoration: none;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .btn:hover {
            background-color: #116B67;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            border-radius: 8px;
            overflow: hidden;
        }

        thead {
            background-color: #116B67;
            color: white;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            color: #333;
        }

        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tbody tr:hover {
            background-color: #f1f1f1;
        }

        .no-records {
            text-align: center;
            font-style: italic;
            color: #888;
        }
    </style>
</head>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<script>
    function generarPDF() {
        const { jsPDF } = window.jspdf;

        const contenedor = document.querySelector('.container');

        html2canvas(contenedor).then(canvas => {
            const imgData = canvas.toDataURL('image/png');
            const pdf = new jsPDF('p', 'mm', 'a4');
            const imgProps = pdf.getImageProperties(imgData);
            const pdfWidth = pdf.internal.pageSize.getWidth();
            const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;

            pdf.addImage(imgData, 'PNG', 0, 10, pdfWidth, pdfHeight);
            pdf.save("reporte_historial_cambios.pdf");
        });
    }
</script>
<body>
<section id="container">
    <section id="main-content">
        <section class="wrapper site-min-height">

            <?php
            $conn = new mysqli("localhost", "root", "", "gestionempleados");
            if ($conn->connect_error) {
                die("Error de conexión: " . $conn->connect_error);
            }

            $sql = "SELECT 
                        hc.id_historial_cargos, 
                        u.nombre AS nombre_usuario, 
                        hc.nuevo_puesto, 
                        hc.fecha_cambio, 
                        hc.motivo, 
                        hc.fechacreacion, 
                        hc.sueldo_nuevo
                    FROM Historial_Cargos hc
                    JOIN Usuario u ON hc.id_usuario = u.id_usuario
                    ORDER BY hc.fecha_cambio DESC";

            $result = $conn->query($sql);
            ?>

            <div class="container">
                <h1>Historial de Cambios de Puesto</h1>

                
                <a href="registrar_cambio_puesto.php" class="btn">
                    Ir al Formulario de Cambio de Puesto
                </a>
                
                <form action="reporte_puestos.php" method="get" target="_blank">
                    <input type="hidden" name="id_usuario" value="<?= $id_usuario ?>">
                    <button type="submit" class="btn">Descargar PDF</button>
                </form>


                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuario</th>
                            <th>Nuevo Puesto</th>
                            <th>Fecha de Cambio</th>
                            <th>Motivo</th>
                            <th>Fecha de Creación</th>
                            <th>Sueldo Nuevo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>
                                    <td>{$row['id_historial_cargos']}</td>
                                    <td>{$row['nombre_usuario']}</td>
                                    <td>{$row['nuevo_puesto']}</td>
                                    <td>{$row['fecha_cambio']}</td>
                                    <td>{$row['motivo']}</td>
                                    <td>{$row['fechacreacion']}</td>
                                    <td>₡" . number_format($row['sueldo_nuevo'], 2) . "</td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7' class='no-records'>No se encontraron registros.</td></tr>";
                        }

                        $conn->close();
                        ?>
                    </tbody>
                </table>
            </div>
        </section>
    </section>
</section>
</body>
</html>
