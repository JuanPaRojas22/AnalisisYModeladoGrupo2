<?php
header('Content-Type: text/html; charset=utf-8');
require_once 'fpdf/fpdf.php';

// Parámetros de conexión a Azure
$host = "accespersoneldb.mysql.database.azure.com";
$user = "adminUser@accespersoneldb"; // 👈 IMPORTANTE: el usuario debe ir con el @host
$password = "admin123+";
$dbname = "gestionEmpleados";
$port = 3306;

// Ruta al certificado SSL (ajústala si estás en un entorno local diferente o en Azure App Service)
$ssl_ca = "/home/site/wwwroot/certs/BaltimoreCyberTrustRoot.crt.pem"; // o './certs/BaltimoreCyberTrustRoot.crt.pem' si es local

// Inicializar conexión mysqli
$conn = mysqli_init();

// Configurar SSL (puedes incluir el CA si estás en entorno de producción seguro)
mysqli_ssl_set($conn, NULL, NULL, $ssl_ca, NULL, NULL);

// Establecer opción para no verificar el nombre del servidor (opcional, depende del entorno)
mysqli_options($conn, MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);

// Intentar conexión usando SSL
if (!$conn->real_connect($host, $user, $password, $dbname, $port, NULL, MYSQLI_CLIENT_SSL)) {
    die("Error de conexión: " . mysqli_connect_error());
}

// Charset
mysqli_set_charset($conn, "utf8mb4");

// Iniciar sesión
session_start();

class PDF extends FPDF
{
    function Header()
    {
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, utf8_decode('Reporte de Historial de Pagos'), 0, 1, 'C');
        $this->SetFont('Arial', 'I', 12);
        $this->Cell(0, 10, 'Generado el ' . date('d/m/Y'), 0, 1, 'C');
        $this->Ln(10);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 10);
        $this->Cell(0, 10, 'Página ' . $this->PageNo(), 0, 0, 'C');
    }

    function TableRow($label, $value)
    {
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(50, 10, utf8_decode($label), 1, 0, 'L');
        $this->SetFont('Arial', '', 12);
        $this->Cell(130, 10, utf8_decode($value), 1, 1, 'L');
    }
}

class GenerarReportePagos
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function getHistorialPagos($fecha_inicio, $fecha_fin, $id_usuario = null, $id_departamento = null)
    {
        $sql = "SELECT p.*, u.nombre, u.apellido, d.Nombre AS departamento 
                FROM pago_planilla p
                INNER JOIN usuario u ON p.id_usuario = u.id_usuario
                INNER JOIN departamento d ON u.id_departamento = d.id_departamento
                WHERE p.fecha_pago BETWEEN ? AND ?";

        $param_types = "ss";
        $params = [$fecha_inicio, $fecha_fin];

        if (!empty($id_usuario)) {
            $sql .= " AND p.id_usuario = ?";
            $param_types .= "i";
            $params[] = $id_usuario;
        }

        if (!empty($id_departamento)) {
            $sql .= " AND u.id_departamento = ?";
            $param_types .= "i";
            $params[] = $id_departamento;
        }

        $sql .= " ORDER BY p.fecha_pago DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($param_types, ...$params);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function generarPDF($fecha_inicio, $fecha_fin, $id_usuario, $id_departamento)
    {
        $pagos = $this->getHistorialPagos($fecha_inicio, $fecha_fin, $id_usuario, $id_departamento);

        if (empty($pagos)) {
            echo "<script>
                alert('No hay datos para generar el PDF.');
                window.history.back();
            </script>";
            exit;
        }

        $pdf = new PDF();
        $pdf->AddPage();

        foreach ($pagos as $row) {
            $empleado = $row['nombre'] . ' ' . $row['apellido'];

            $pdf->TableRow('Empleado', $empleado);
            $pdf->TableRow('Departamento', $row['departamento']);
            $pdf->TableRow('Salario Base', number_format($row['salario_base'], 2));
            $pdf->TableRow('Bono', number_format($row['total_bonos'], 2));
            $pdf->TableRow('Deducción', number_format($row['total_deducciones'], 2));
            $pdf->TableRow('Horas Extra', number_format($row['pago_horas_extras'], 2));
            $pdf->TableRow('Salario Neto', number_format($row['salario_neto'], 2));
            $pdf->TableRow('Fecha de Pago', $row['fecha_pago']);
            $pdf->TableRow('Tipo de Quincena', $row['tipo_quincena']);
            $pdf->Ln(10);
        }

        $pdf->Output('D', 'reporte_historial_pagos.pdf');
    }
}

// Parámetros desde la URL
$fecha_inicio = $_GET['fecha_inicio'] ?? '2000-01-01';
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');
$id_usuario = $_GET['id_usuario'] ?? null;
$id_departamento = $_GET['id_departamento'] ?? null;

$reporte = new GenerarReportePagos($conn);
$reporte->generarPDF($fecha_inicio, $fecha_fin, $id_usuario, $id_departamento);
?>
