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

class GenerarReporteVacacion
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    // Función para obtener las vacaciones
    public function getVacaciones($fecha_inicio, $fecha_fin, $id_usuario, $id_departamento, $id_estado_vacacion)
    {
        $sql = "SELECT 
                    v.id_vacacion,
                    u.nombre AS empleado,
                    d.nombre AS departamento,
                    v.razon,
                    v.diasTomado,
                    v.fecha_inicio,
                    v.fecha_fin,
                    h.DiasRestantes,
                    ev.descripcion AS estado
                FROM vacacion v
                INNER JOIN usuario u ON v.id_usuario = u.id_usuario
                INNER JOIN departamento d ON u.id_departamento = d.id_departamento
                INNER JOIN estado_vacacion ev ON v.id_estado_vacacion = ev.id_estado_vacacion
                INNER JOIN historial_vacaciones h ON v.id_historial = h.id_historial
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

        if (!empty($id_estado_vacacion)) {
            $sql .= " AND v.id_estado_vacacion = ?";
            $param_types .= "i";
            $params[] = $id_estado_vacacion;
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

    public function generarPDF($fecha_inicio, $fecha_fin, $id_usuario, $id_departamento, $id_estado_vacacion)
    {
        $historial = $this->getVacaciones($fecha_inicio, $fecha_fin, $id_usuario, $id_departamento, $id_estado_vacacion);

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
            //$pdf->Cell(0, 10, 'ID Historial: ' . $fila['id_historial'], 0, 1, 'C');
            $pdf->Ln(5);

            $pdf->TableRow('Empleado', $fila['empleado']);
            $pdf->TableRow('Departamento', $fila['departamento']);
            $pdf->TableRow('Razón', $fila['razon']);
            $pdf->TableRow('Días Tomados', $fila['diasTomado']);
            $pdf->TableRow('Fecha Inicio', $fila['fecha_inicio']);
            $pdf->TableRow('Fecha Fin', $fila['fecha_fin']);
            $pdf->TableRow('Días Restantes', $fila['DiasRestantes']);
            $pdf->TableRow('Estado', $fila['estado']);

            $pdf->Ln(10);
        }

        // Descargar el PDF
        $pdf->Output('D', 'reporte_vacaciones.pdf');
    }
}

// Obtener los parámetros de la URL
$fecha_inicio = !empty($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : '2000-01-01';
$fecha_fin = !empty($_GET['fecha_fin']) ? $_GET['fecha_fin'] : date('Y-m-d');
$id_usuario = !empty($_GET['id_usuario']) ? $_GET['id_usuario'] : null;
$id_departamento = !empty($_GET['id_departamento']) ? $_GET['id_departamento'] : null;
$id_estado_vacacion = !empty($_GET['id_estado_vacacion']) ? $_GET['id_estado_vacacion'] : null;

// Generar el PDF
$reporte = new GenerarReporteVacacion($conn);
$reporte->generarPDF($fecha_inicio, $fecha_fin, $id_usuario, $id_departamento, $id_estado_vacacion);
?>
