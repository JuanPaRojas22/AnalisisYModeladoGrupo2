<?php
session_start();
/*error_reporting(E_ALL);
ini_set('display_errors', 1);*/

require 'fpdf/fpdf.php';  // Para exportar a PDF

ob_start();


// Si viene POST y trae datos_json, usar esos datos
$data = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['datos_json'])) {
    $json = $_POST['datos_json'];
    $data = json_decode($json, true);
    if (!is_array($data)) {
        die("Error: datos JSON inválidos.");
    }
} else {
    die("No se recibieron datos para generar el reporte.");
}

// Crear PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetMargins(15, 15, 15);
$pdf->SetFont('Arial', 'B', 16);

// Logo centrado
$pdf->Image('assets/img/logo_acces_perssonel.jpeg', 75, 10, 60);
$pdf->Ln(45);

$pdf->Cell(0, 10, 'Reporte de Horas Extras', 0, 1, 'C');
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Acces Personnel', 0, 1, 'C');
$pdf->Ln(5);

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, 'Fecha: ' . date('d/m/Y'), 0, 1, 'C');
$pdf->Ln(5);

$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'Total de Horas Extras del Empleado', 0, 1, 'C');
$pdf->Ln(5);

$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(200, 200, 200);
$pdf->Cell(70, 10, 'Nombre', 1, 0, 'C', true);
$pdf->Cell(50, 10, 'Departamento', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Horas Extras', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Monto Pago', 1, 1, 'C', true);

$pdf->SetFont('Arial', '', 12);
foreach ($data as $row) {
    $pdf->Cell(70, 10, utf8_decode($row['nombre']), 1, 0, 'C');
    $pdf->Cell(50, 10, utf8_decode($row['departamento']), 1, 0, 'C');
    $pdf->Cell(40, 10, number_format(floatval(str_replace(',', '', $row['horas_extras'])), 2), 1, 0, 'C');
    $pdf->Cell(30, 10, number_format(floatval(str_replace(',', '', $row['monto_pago'])), 2), 1, 1, 'C');
}

$pdf->SetY(-50);
$pdf->SetFont('Arial', 'I', 8);
$pdf->Cell(0, 10, 'Generado por Sistema de Reportes - Acces Personnel', 0, 0, 'C');

ob_end_clean();

$pdf->Output('I', 'reporte_horas_extras.pdf');
exit;
?>
