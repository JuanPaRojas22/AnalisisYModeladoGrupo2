<?php
header('Content-Type: text/html; charset=utf-8');

require_once 'fpdf/fpdf.php'; // Incluir la librería FPDF

// Conectar a la base de datos
$conn = new mysqli("localhost", "root", "", "GestionEmpleados");
mysqli_set_charset($conn, "utf8mb4");

// Validar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

class PDF extends FPDF
{
    // Encabezado personalizado
    function Header()
    {
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, 'Reporte de Historial de Vacaciones', 0, 1, 'C');
        $this->SetFont('Arial', 'I', 12);
        $this->Cell(0, 10, 'Generado el ' . date('d/m/Y'), 0, 1, 'C');
        $this->Ln(10);
    }

    // Pie de página
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 10);
        $this->Cell(0, 10, 'Página ' . $this->PageNo(), 0, 0, 'C');
    }

    // Dibuja las filas de la tabla
    function TableRow($label, $value)
    {
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(50, 10, utf8_decode($label), 1, 0, 'L');
        $this->SetFont('Arial', '', 12);
        $this->Cell(130, 10, utf8_decode($value), 1, 1, 'L');
    }
}

class GenerarReporteHistorial
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    // Función para obtener el historial de vacaciones
    public function getHistorialVacaciones($fecha_inicio, $fecha_fin, $id_usuario, $id_departamento)
    {
        $sql = "SELECT 
                    h.id_historial,
                    u.nombre AS empleado,
                    d.nombre AS departamento,
                    h.Razon,
                    h.DiasTomados,
                    h.FechaInicio,
                    h.FechaFin,
                    h.DiasRestantes
                FROM historial_vacaciones h
                LEFT JOIN usuario u ON h.id_usuario = u.id_usuario
                LEFT JOIN departamento d ON u.id_departamento = d.id_departamento
                WHERE h.FechaInicio BETWEEN ? AND ?";

        $param_types = "ss";
        $params = [$fecha_inicio, $fecha_fin];

        if (!empty($id_usuario)) {
            $sql .= " AND h.id_usuario = ?";
            $param_types .= "i";
            $params[] = $id_usuario;
        }
        if (!empty($id_departamento)) {
            $sql .= " AND u.id_departamento = ?";
            $param_types .= "i";
            $params[] = $id_departamento;
        }

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            die("Error en la consulta SQL: " . $this->conn->error);
        }

        $stmt->bind_param($param_types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $historial = [];

        while ($fila = $result->fetch_assoc()) {
            $historial[] = $fila;
        }

        $stmt->close();
        return $historial;
    }

    public function generarPDF($fecha_inicio, $fecha_fin, $id_usuario, $id_departamento)
    {
        $historial = $this->getHistorialVacaciones($fecha_inicio, $fecha_fin, $id_usuario, $id_departamento);

        if (empty($historial)) {
            echo "<script>
                alert('No hay datos para generar el PDF.');
                window.history.back();
            </script>";
            exit;
        }

        $pdf = new PDF();
        $pdf->AddPage();

        // Agregar datos al PDF
        foreach ($historial as $fila) {
            $pdf->SetFont('Arial', 'B', 14);
            $pdf->Cell(0, 10, 'ID Historial: ' . $fila['id_historial'], 0, 1, 'C');
            $pdf->Ln(5);

            $pdf->TableRow('Empleado', $fila['empleado']);
            $pdf->TableRow('Departamento', $fila['departamento']);
            $pdf->TableRow('Razón', $fila['Razon']);
            $pdf->TableRow('Días Tomados', $fila['DiasTomados']);
            $pdf->TableRow('Fecha Inicio', $fila['FechaInicio']);
            $pdf->TableRow('Fecha Fin', $fila['FechaFin']);
            $pdf->TableRow('Días Restantes', $fila['DiasRestantes']);

            $pdf->Ln(10);
        }

        // Descargar el PDF
        $pdf->Output('D', 'reporte_historial_vacaciones.pdf');
    }
}

// Obtener los parámetros de la URL
$fecha_inicio = !empty($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : '2000-01-01';
$fecha_fin = !empty($_GET['fecha_fin']) ? $_GET['fecha_fin'] : date('Y-m-d');
$id_usuario = !empty($_GET['id_usuario']) ? $_GET['id_usuario'] : null;
$id_departamento = !empty($_GET['id_departamento']) ? $_GET['id_departamento'] : null;

// Generar el PDF
$reporte = new GenerarReporteHistorial($conn);
$reporte->generarPDF($fecha_inicio, $fecha_fin, $id_usuario, $id_departamento);
?>
