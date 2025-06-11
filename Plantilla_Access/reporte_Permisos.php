<?php
require 'conexion.php';
require 'fpdf/fpdf.php';  // Para exportar a PDF

$id_usuario = isset($_GET['id_usuario']) ? $_GET['id_usuario'] : 0;

$sql = "SELECT u.nombre, u.apellido, h.FechaInicio, h.FechaFin, h.DiasTomados, h.Razon, h.Estado
        FROM historial_permisos h
        JOIN usuario u ON h.id_usuario = u.id_usuario
        WHERE h.id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // ✅ Convertir todos los valores del array a ISO-8859-1
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

// Logo centrado
$pdf->Image('assets/img/logo_acces_perssonel.jpeg', 75, 10, 60);
$pdf->Ln(45);

// Título
$pdf->Cell(0, 10, 'Historial de Permisos', 0, 1, 'C');
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Acces Personnel', 0, 1, 'C');
$pdf->Ln(5);

// Fecha actual
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, 'Fecha: ' . date('d/m/Y'), 0, 1, 'C');
$pdf->Ln(5); 

// Subtítulo
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'Detalle de permisos del empleado', 0, 1, 'C');
$pdf->Ln(5);

// Encabezados de tabla
$pdf->SetFont('Arial', 'B', 11);
$pdf->SetFillColor(200, 200, 200);
$pdf->Cell(50, 10, 'Empleado', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Inicio', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Fin', 1, 0, 'C', true);
$pdf->Cell(25, 10, mb_convert_encoding('Días', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', true);
$pdf->Cell(35, 10, mb_convert_encoding('Razón', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', true);

$pdf->Cell(20, 10, 'Estado', 1, 1, 'C', true);

// Contenido
$pdf->SetFont('Arial', '', 10);
foreach ($data as $row) {
    // Ya están codificados, no necesitas utf8_decode
    $pdf->Cell(50, 10, $row['nombre'] . ' ' . $row['apellido'], 1, 0, 'C');
    $pdf->Cell(30, 10, $row['FechaInicio'], 1, 0, 'C');
    $pdf->Cell(30, 10, $row['FechaFin'], 1, 0, 'C');
    $pdf->Cell(25, 10, $row['DiasTomados'], 1, 0, 'C');
    $pdf->Cell(35, 10, $row['Razon'], 1, 0, 'C');
    $pdf->Cell(20, 10, $row['Estado'], 1, 1, 'C');
}

// Pie de página
$pdf->SetY(-50);
$pdf->SetFont('Arial', 'I', 8);
$pdf->Cell(0, 10, 'Generado por Sistema de Reportes - Acces Personnel', 0, 0, 'C');

$pdf->Output('D', 'historial_permisos.pdf');
exit;
?>
