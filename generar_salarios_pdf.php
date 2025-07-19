<?php
ob_start(); // Inicia el búfer de salida para evitar problemas con las cabeceras

require 'conexion.php';
require_once 'fpdf/fpdf.php'; // Incluir la librería FPDF

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_usuario'])) {
    echo "Debes iniciar sesión para acceder a esta página.";
    exit;
}

$id_usuario = $_SESSION['id_usuario'];

// Consulta para obtener la suma total de salarios activos
$query = "SELECT SUM(salario_neto) AS monto_total_salarios FROM planilla";
$result = mysqli_query($conn, $query);

if ($result) {
    $row = mysqli_fetch_assoc($result);
    $monto_total = $row['monto_total_salarios'];

    // Crear el objeto FPDF para generar el PDF
    $pdf = new FPDF();
    $pdf->AddPage();

    // Establecer fuentes y márgenes
    $pdf->SetMargins(15, 15, 15);
    $pdf->SetFont('Arial', 'B', 16);

    // Agregar encabezado
    $pdf->Image('assets/img/logo_acces_perssonel.jpeg', 0, 0, 75);
    $pdf->Cell(0, 50, 'Reporte de Salarios Totales', 0, 1, 'C');
    $pdf->Cell(0, 10, 'Acces Perssonel.', 0, 1, 'C');



    // Agregar información de la empresa
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, 'Fecha: ' . date('d/m/Y'), 0, 1, 'C');
    $pdf->Ln(0); // Espacio entre la información de la empresa y el reporte

    // Crear la tabla para mostrar los salarios
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, 'Monto Total de Salarios', 0, 1, 'C'); // Título centrado

    // Configurar las celdas de la tabla
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(90, 10, 'Monto Total de Salarios:', 1, 0, 'C'); // Primera columna
    $pdf->Cell(0, 10, number_format($monto_total, 2, ',', '.'), 1, 1, 'C'); // Segunda columna


    // Agregar pie de página
    $pdf->SetY(-31);
    $pdf->SetFont('Arial', 'I', 8);
    $pdf->Cell(0, 10, 'Generado por Sistema de Reportes - Acces Perssonel.', 0, 0, 'C');

    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="salarios.pdf"'); // 'inline' hace que se muestre en una nueva pestaña
    header('Content-Transfer-Encoding: binary');
    header('Accept-Ranges: bytes');
    // Generar el PDF y enviarlo al navegador
    $pdf->Output('salarios_acces_perssonel.pdf', 'D'); // 'D' descarga el pdf

    exit; // Termina la ejecución del script
} else {
    echo "<p>Error al obtener el total de salarios: " . mysqli_error($conn) . "</p>";
}

mysqli_close($conn);

ob_end_flush(); // Liberar el búfer y enviar la salida al navegador
?>