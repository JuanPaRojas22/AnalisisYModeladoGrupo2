<?php 
session_start();
include 'template.php';

// Validar sesión iniciada
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php"); // Redirige si no hay sesión
    exit;
}

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

// Obtener ID del usuario desde la sesión
$id_usuario = $_SESSION['id_usuario'];

// Obtener datos del usuario
$sql_usuario = "SELECT CONCAT(nombre, ' ', apellido) AS nombre FROM usuario WHERE id_usuario = ?";
$stmt_usuario = $conn->prepare($sql_usuario);
$stmt_usuario->bind_param("i", $id_usuario);
$stmt_usuario->execute();
$result_usuario = $stmt_usuario->get_result();
$usuario = $result_usuario->fetch_assoc();
$stmt_usuario->close();

// Obtener beneficios del usuario
$sql_beneficios = "SELECT * FROM beneficios WHERE id_usuario = ?";
$stmt_beneficios = $conn->prepare($sql_beneficios);
$stmt_beneficios->bind_param("i", $id_usuario);
$stmt_beneficios->execute();
$result_beneficios = $stmt_beneficios->get_result();
$beneficios = [];
while ($row = $result_beneficios->fetch_assoc()) {
    $beneficios[] = $row;
}
$stmt_beneficios->close();
?>
<head>
    <title>Detalles de Beneficios</title>
</head>

<div class="container mt-5">
    <h2 class="text-center mb-4">Beneficios de <?= htmlspecialchars($usuario['nombre']) ?></h2>
    <div class="text-start mb-4">
    <div class="boton-volver-container">
    <a href="admin_beneficios.php" class="btn-volver">
        Volver a Administración
    </a>
</div>
</div>
    <div class="row">
        <?php if (empty($beneficios)): ?>
            <p class="text-center">Este usuario no tiene beneficios registrados.</p>
        <?php else: ?>
            <?php foreach ($beneficios as $beneficio): ?>
                <div class="col-md-6">
                    <div class="card beneficio-card">
                        <h5 class="beneficio-title"><?= htmlspecialchars($beneficio['razon']) ?></h5>
                        <p><strong>Monto:</strong> ₡<?= number_format($beneficio['monto'], 2) ?></p>
                        <p><strong>ID MediSmart:</strong> <?= htmlspecialchars($beneficio['identificacion_medismart']) ?></p>
                        <p><strong>Valor Total:</strong> ₡<?= number_format($beneficio['valor_plan_total'], 2) ?></p>
                        <p><strong>Aporte Patrono:</strong> ₡<?= number_format($beneficio['aporte_patrono'], 2) ?></p>
                        <p><strong>Beneficiarios:</strong> <?= $beneficio['beneficiarios'] ?></p>

                        <div class="beneficio-actions">

                        <button class="btn btn-warning" 
        onclick="abrirModal(<?= $beneficio['id_beneficio'] ?>, '<?= htmlspecialchars($beneficio['razon']) ?>', <?= $beneficio['monto'] ?>, '<?= htmlspecialchars($beneficio['identificacion_medismart']) ?>', <?= $beneficio['valor_plan_total'] ?>, <?= $beneficio['aporte_patrono'] ?>, <?= $beneficio['beneficiarios'] ?>)"
        style="background-color: #0B4F6C; border-color: #0B4F6C;">
    Editar
</button>


                            <button class="btn btn-danger ms-2" onclick="eliminarBeneficio(<?= $beneficio['id_beneficio'] ?>)">Eliminar</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal para Editar Beneficio -->
<div id="beneficioModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="cerrarModal()">&times;</span>
        <h3 id="modalTitle">Editar Beneficio</h3>

        <form id="beneficioForm">
            <input type="hidden" id="id_beneficio" name="id_beneficio">

            <label>Razón:</label>
            <input type="text" id="razon" name="razon" required>

            <label>Monto:</label>
            <input type="number" id="monto" name="monto" required>

            <label>ID MediSmart:</label>
            <input type="text" id="medismart" name="identificacion_medismart">

            <label>Valor Total:</label>
            <input type="number" id="valor_total" name="valor_plan_total">

            <label>Aporte Patrono:</label>
            <input type="number" id="aporte_patrono" name="aporte_patrono">

            <label>Beneficiarios:</label>
            <input type="number" id="beneficiarios" name="beneficiarios">

            <button type="submit" class="btn btn-success" style="background-color: #147964; border-color: #147964;">
    Guardar
</button>
        </form>
    </div>
</div>

<!-- Scripts para Editar -->
<script>
function abrirModal(id, razon, monto, medismart, valorTotal, aportePatrono, beneficiarios) {
    document.getElementById("id_beneficio").value = id;
    document.getElementById("razon").value = razon;
    document.getElementById("monto").value = monto;
    document.getElementById("medismart").value = medismart;
    document.getElementById("valor_total").value = valorTotal;
    document.getElementById("aporte_patrono").value = aportePatrono;
    document.getElementById("beneficiarios").value = beneficiarios;
    
    document.getElementById("beneficioModal").style.display = "block";
}

function cerrarModal() {
    document.getElementById("beneficioModal").style.display = "none";
}

document.getElementById("beneficioForm").addEventListener("submit", function(event) {
    event.preventDefault();

    let formData = new FormData(this);
    formData.append("action", "edit");

    fetch("crud_beneficios.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        console.log(data); // <--- muestra en consola lo que responde el servidor
        if (data.success) {
            alert("Beneficio actualizado correctamente.");
            location.reload();
        } else {
            alert("Error al actualizar el beneficio: " + data.message); // <--- alerta con mensaje real del backend
        }
    })
    .catch(error => console.error("Error en la solicitud:", error));
});


function eliminarBeneficio(id_beneficio) {
    if (confirm("¿Seguro que quieres eliminar este beneficio?")) {
        fetch("crud_beneficios.php?action=delete&id=" + id_beneficio, {
            method: "GET"
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Beneficio eliminado correctamente.");
                location.reload();
            } else {
                alert("Error al eliminar el beneficio: " + data.message);
            }
        })
        .catch(error => console.error("Error en la solicitud:", error));
    }
}
</script>

<!-- Manteniendo los Estilos -->
<style>
.container {
    max-width: 1100px;
    margin: auto;
    color: black;
}
.boton-volver-container {
    position: relative;
    margin-bottom: 20px; /* Mayor separación con las cards */
    display: inline-block;
}

.btn-volver {
    background-color: #0E5D6A; /* Dorado elegante */
    color: #fff !important; /* No cambia el color en hover */
    font-size: 1.1em;
    font-weight: bold;
    padding: 12px 20px;
    border-radius: 8px;
    text-decoration: none;
    display: inline-block;
    transition: 0.3s ease-in-out;
    box-shadow: 2px 4px 10px rgba(0, 0, 0, 0.2);
}

.btn-volver:hover {
    display: inline-block;
                        background-color: #0B4F6C;
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
    transform: translateY(-2px);
    
}


h2 {
    font-size: 2.2em;
    font-weight: bold;
    color: #2c3e50;
    text-align: center;
    margin-top: 40px;
    margin-bottom: 30px;
}

.beneficio-card {
    background: #ffffff;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0px 6px 12px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
    transition: transform 0.2s ease-in-out;
}

.beneficio-card:hover {
    transform: translateY(-4px);
    box-shadow: 0px 10px 15px rgba(0, 0, 0, 0.15);
}

.beneficio-title {
    font-size: 1.4em;
    font-weight: bold;
    color: #2c3e50;
    margin-bottom: 10px;
}

.beneficio-actions {
    margin-top: 15px;
}

.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.4);
}

.modal-content {
    background-color: white;
    margin: 10% auto;
    padding: 20px;
    width: 40%;
    border-radius: 10px;
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
    color: black;
}

.close {
    float: right;
    font-size: 1.5em;
    cursor: pointer;
}
</style>
