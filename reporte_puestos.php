<?php
session_start();
require 'conexion.php';
require 'fpdf/fpdf.php';

$conn = obtenerConexion();

// Variables de sesión
$id_usuario = $_SESSION['id_usuario'];
$rol = $_SESSION['id_rol'];

// Obtener departamento si es admin normal
$mi_departamento = null;
if ($rol == 1) {
    $res_dep = mysqli_query($conn, "SELECT id_departamento FROM usuario WHERE id_usuario = $id_usuario");
    if ($res_dep && $dep_row = mysqli_fetch_assoc($res_dep)) {
        $mi_departamento = $dep_row['id_departamento'];
    }
}

// Construir WHERE según el rol
$where = "WHERE 1=1 ";

if ($rol == 3) {
    $where .= "AND hc.id_usuario = $id_usuario ";
} elseif ($rol == 1 && $mi_departamento !== null) {
    $where .= "AND u.id_departamento = $mi_departamento ";
}

// Consulta
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
        $where
        ORDER BY hc.fecha_cambio DESC";

$result = $conn->query($sql);

// Crear PDF
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
$pdf->Cell(0, 10, 'Detalle de cambios de puesto', 0, 1, 'C');
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
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Codificar correctamente caracteres especiales
        $pdf->Cell(40, 10, mb_convert_encoding($row['nombre_usuario'], 'ISO-8859-1', 'UTF-8'), 1, 0, 'C');
        $pdf->Cell(30, 10, mb_convert_encoding($row['nuevo_puesto'], 'ISO-8859-1', 'UTF-8'), 1, 0, 'C');
        $pdf->Cell(30, 10, $row['fecha_cambio'], 1, 0, 'C');
        $pdf->Cell(45, 10, mb_convert_encoding($row['motivo'], 'ISO-8859-1', 'UTF-8'), 1, 0, 'C');
        $pdf->Cell(40, 10, '₡' . number_format($row['sueldo_nuevo'], 2), 1, 1, 'C');
    }
} else {
    $pdf->Cell(185, 10, 'No se encontraron registros.', 1, 1, 'C');
}

// Pie de página
$pdf->SetY(-30);
$pdf->SetFont('Arial', 'I', 8);
$pdf->Cell(0, 10, 'Generado por el sistema - Acces Personnel', 0, 0, 'C');

// Salida PDF
$pdf->Output('D', 'historial_puestos.pdf');
exit;
?>
