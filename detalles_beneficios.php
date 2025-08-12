<?php
session_start();
include 'template.php';

// Validar sesión iniciada
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}

/* ===========================
   Conexión a MySQL (SSL)
   =========================== */
$host = "accespersoneldb.mysql.database.azure.com";
$user = "adminUser";
$password = "admin123+";
$dbname = "gestionEmpleados";
$port = 3306;

$conn = mysqli_init();
mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);
mysqli_options($conn, MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);

if (!$conn->real_connect($host, $user, $password, $dbname, $port, NULL, MYSQLI_CLIENT_SSL)) {
    die("Error de conexión: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8mb4");

/* ===========================
   Determinar usuario objetivo
   =========================== */
$isAdminMaster = (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin_master');

// Por defecto: el usuario logueado ve lo suyo
$targetUserId = (int)$_SESSION['id_usuario'];

// Si es admin master y hay usuario en foco desde admin_beneficios.php → ver ese usuario
if ($isAdminMaster && isset($_SESSION['benef_user_id'])) {
    $targetUserId = (int)$_SESSION['benef_user_id'];
}

/* ===========================
   Consultas para el usuario en foco
   =========================== */

// Datos del usuario (nombre para el título)
$sql_usuario = "SELECT CONCAT(nombre, ' ', apellido) AS nombre FROM usuario WHERE id_usuario = ?";
$stmt_usuario = $conn->prepare($sql_usuario);
$stmt_usuario->bind_param("i", $targetUserId);
$stmt_usuario->execute();
$result_usuario = $stmt_usuario->get_result();
$usuario = $result_usuario->fetch_assoc();
$stmt_usuario->close();

if (!$usuario) {
    // Si por alguna razón el foco no existe, vuelve al panel de admin
    if ($isAdminMaster) {
        header("Location: admin_beneficios.php");
        exit;
    }
    // Usuario normal: fallback a su propio nombre
    $usuario = ['nombre' => 'Usuario'];
}

// Beneficios del usuario en foco
$sql_beneficios = "SELECT * FROM beneficios WHERE id_usuario = ?";
$stmt_beneficios = $conn->prepare($sql_beneficios);
$stmt_beneficios->bind_param("i", $targetUserId);
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
    <h2 class="text-center mb-4">Beneficios de <?= htmlspecialchars($usuario['nombre'] ?? 'Usuario') ?></h2>

    <div class="text-start mb-4">
        <div class="boton-volver-container">
            <?php if ($isAdminMaster): ?>
                <!-- Si estás en modo admin viendo a otro usuario, muestra "Volver a Administración" y botón para salir del foco -->
                <a href="admin_beneficios.php" class="btn-volver">Volver a Administración</a>

                <form action="set_usuario.php" method="POST" style="display:inline-block; margin-left:10px;">
                    <input type="hidden" name="clear_focus" value="1">
                    <button type="submit" class="btn-volver" style="background:#6c757d;">Salir del modo admin</button>
                </form>
            <?php else: ?>
                <!-- Usuario normal -->
                <a href="beneficios.php" class="btn-volver">Volver</a>
            <?php endif; ?>
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
                        <p><strong>Monto:</strong> ₡<?= number_format((float)$beneficio['monto'], 2) ?></p>
                        <p><strong>ID MediSmart:</strong> <?= htmlspecialchars($beneficio['identificacion_medismart']) ?></p>
                        <p><strong>Valor Total:</strong> ₡<?= number_format((float)$beneficio['valor_plan_total'], 2) ?></p>
                        <p><strong>Aporte Patrono:</strong> ₡<?= number_format((float)$beneficio['aporte_patrono'], 2) ?></p>
                        <p><strong>Beneficiarios:</strong> <?= (int)$beneficio['beneficiarios'] ?></p>

                        <div class="beneficio-actions">
                            <button class="btn btn-warning"
                                onclick="abrirModal(
                                    <?= (int)$beneficio['id_beneficio'] ?>,
                                    '<?= htmlspecialchars($beneficio['razon'], ENT_QUOTES) ?>',
                                    <?= (float)$beneficio['monto'] ?>,
                                    '<?= htmlspecialchars($beneficio['identificacion_medismart'], ENT_QUOTES) ?>',
                                    <?= (float)$beneficio['valor_plan_total'] ?>,
                                    <?= (float)$beneficio['aporte_patrono'] ?>,
                                    <?= (int)$beneficio['beneficiarios'] ?>
                                )"
                                style="background-color: #0B4F6C; border-color: #0B4F6C;">
                                Editar
                            </button>

                            <button class="btn btn-danger ms-2"
                                onclick="eliminarBeneficio(<?= (int)$beneficio['id_beneficio'] ?>)">Eliminar</button>
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
        <h3 id="modalTitle" class="text-center mb-4">Editar Beneficio</h3>

        <form id="beneficioForm">
            <input type="hidden" id="id_beneficio" name="id_beneficio">

            <div class="modal-grid">
                <div class="form-group">
                    <label for="razon">Razón:</label>
                    <input type="text" id="razon" name="razon" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="monto">Monto:</label>
                    <input type="number" id="monto" name="monto" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="medismart">ID MediSmart:</label>
                    <input type="text" id="medismart" name="identificacion_medismart" class="form-control">
                </div>

                <div class="form-group">
                    <label for="valor_total">Valor total:</label>
                    <input type="number" id="valor_total" name="valor_plan_total" class="form-control">
                </div>

                <div class="form-group">
                    <label for="aporte_patrono">Aporte Patrono:</label>
                    <input type="number" id="aporte_patrono" name="aporte_patrono" class="form-control">
                </div>

                <div class="form-group">
                    <label for="beneficiarios">Beneficiarios:</label>
                    <input type="number" id="beneficiarios" name="beneficiarios" class="form-control">
                </div>
            </div>

            <div class="text-center mt-4">
                <button type="submit" class="btn btn-success px-4 py-2"
                    style="background-color: #147964; border-color: #147964;">
                    Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Scripts para Editar / Eliminar -->
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

    document.getElementById("beneficioForm").addEventListener("submit", function (event) {
        event.preventDefault();

        let formData = new FormData(this);
        formData.append("action", "edit");

        fetch("crud_beneficios.php", {
            method: "POST",
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                alert("Beneficio actualizado correctamente.");
                location.reload();
            } else {
                alert("Error al actualizar el beneficio: " + (data.message || ''));
            }
        })
        .catch(error => console.error("Error en la solicitud:", error));
    });

    function eliminarBeneficio(id_beneficio) {
        if (confirm("¿Seguro que quieres eliminar este beneficio?")) {
            fetch("crud_beneficios.php?action=delete&id=" + encodeURIComponent(id_beneficio), { method: "GET" })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert("Beneficio eliminado correctamente.");
                    location.reload();
                } else {
                    alert("Error al eliminar el beneficio: " + (data.message || ''));
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
        margin-bottom: 20px;
        /* Mayor separación con las cards */
        display: inline-block;
    }

    .btn-volver {
        background-color: #0E5D6A;
        /* Dorado elegante */
        color: #fff !important;
        /* No cambia el color en hover */
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

    .modal-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px 25px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .modal-content input,
    .modal-content select {
        border-radius: 8px;
        padding: 10px;
        border: 1px solid #ccc;
        margin-top: 5px;
    }

    @media (max-width: 768px) {
        .modal-grid {
            grid-template-columns: 1fr;
        }
    }
</style>