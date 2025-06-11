<?php
require 'conexion.php';
require 'fpdf/fpdf.php';  // Para exportar a PDF

// Consulta sin filtro
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

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Codificar a ISO-8859-1 para evitar errores con FPDF
        foreach ($row as $key => $value) {
            $row[$key] = mb_convert_encoding($value, 'ISO-8859-1', 'UTF-8');
        }
        $data[] = $row;
    }
}

// Crear el PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetMargins(15, 15, 15);
$pdf->SetFont('Arial', 'B', 16);

// Logo
$pdf->Image('assets/img/logo_acces_perssonel.jpeg', 75, 10, 60);
$pdf->Ln(45);

// Encabezado
$pdf->Cell(0, 10, 'Historial de Puestos', 0, 1, 'C');
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Acces Personnel', 0, 1, 'C');
$pdf->Ln(5);

// Fecha
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, 'Fecha: ' . date('d/m/Y'), 0, 1, 'C');
$pdf->Ln(5); 

// Subtítulo
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'Detalle completo de cambios de puesto', 0, 1, 'C');
$pdf->Ln(5);

// Encabezados tabla
$pdf->SetFont('Arial', 'B', 11);
$pdf->SetFillColor(200, 200, 200);
$pdf->Cell(40, 10, 'Empleado', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Puesto', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Fecha Cambio', 1, 0, 'C', true);
$pdf->Cell(45, 10, 'Motivo', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Sueldo Nuevo', 1, 1, 'C', true);

// Cuerpo tabla
$pdf->SetFont('Arial', '', 10);
foreach ($data as $row) {
    $pdf->Cell(40, 10, $row['nombre_usuario'], 1, 0, 'C');
    $pdf->Cell(30, 10, $row['nuevo_puesto'], 1, 0, 'C');
    $pdf->Cell(30, 10, $row['fecha_cambio'], 1, 0, 'C');
    $pdf->Cell(45, 10, $row['motivo'], 1, 0, 'C');
    $pdf->Cell(40, 10, $row['sueldo_nuevo'], 1, 1, 'C');
}

// Pie de página
$pdf->SetY(-50);
$pdf->SetFont('Arial', 'I', 8);
$pdf->Cell(0, 10, 'Generado por Sistema de Reportes - Acces Personnel', 0, 0, 'C');

// Salida PDF
$pdf->Output('D', 'historial_puestos_todos.pdf');
exit;
?>
