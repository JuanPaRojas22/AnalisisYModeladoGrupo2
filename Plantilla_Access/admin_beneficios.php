<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
$conexion = new mysqli("localhost", "root", "", "gestionempleados");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Obtener todos los usuarios con sus beneficios
$sql = "SELECT u.id_usuario, CONCAT(u.nombre, ' ', u.apellido) AS nombre, COUNT(b.id_beneficio) AS total_beneficios
        FROM usuario u
        LEFT JOIN beneficios b ON u.id_usuario = b.id_usuario
        GROUP BY u.id_usuario
        ORDER BY u.nombre";

$resultado = $conexion->query($sql);
$usuarios = [];

while ($row = $resultado->fetch_assoc()) {
    $usuarios[] = $row;
}

include 'template.php';
?>

<div class="container mt-5">
<h2 class="titulo-beneficios text-center">Gestión de Beneficios</h2>


    <div class="row">
        <?php foreach ($usuarios as $usuario): ?>
        <div class="col-md-6">
            <div class="usuario-card">
                <h4 class="usuario-nombre"><?= htmlspecialchars($usuario['nombre']) ?></h4>
                <p class="usuario-texto"><strong>Total de Beneficios:</strong> <?= $usuario['total_beneficios'] ?></p>

                <div class="usuario-botones">
                <a href="detalles_beneficios.php?id_usuario=<?= $usuario['id_usuario'] ?>" class="btn btn-primary" style="background-color: #0C536C; border-color: #0C536C;">
    Ver Beneficios
</a>

<button class="btn btn-success ms-2" onclick="abrirModalAgregar(<?= $usuario['id_usuario'] ?>)" style="background-color: #147665; border-color: #147665;">
    Agregar Beneficio
</button>

                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal para Agregar Beneficio -->
<div id="beneficioModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="cerrarModal()">&times;</span>
        <h3 id="modalTitle" class="modal-title">Agregar Beneficio</h3>

        <form id="beneficioForm">
    <input type="hidden" id="id_usuario" name="id_usuario">
    <input type="hidden" name="action" value="add">

    <div class="modal-body">
        <div class="form-group">
            <label>Razón:</label>
            <input type="text" id="razon" name="razon" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Monto:</label>
            <input type="number" id="monto" name="monto" class="form-control" required>
        </div>

        <div class="form-group">
            <label>ID MediSmart:</label>
            <input type="text" id="medismart" name="identificacion_medismart" class="form-control">
        </div>

        <div class="form-group">
            <label>Valor Total:</label>
            <input type="number" id="valor_total" name="valor_plan_total" class="form-control">
        </div>

        <div class="form-group">
            <label>Aporte Patrono:</label>
            <input type="number" id="aporte_patrono" name="aporte_patrono" class="form-control">
        </div>

        <div class="form-group">
            <label>Beneficiarios:</label>
            <input type="number" id="beneficiarios" name="beneficiarios" class="form-control">
        </div>
    </div>

    <div class="modal-footer">
    <button type="submit" class="btn btn-success" style="background-color: #147964; border-color: #147964;">
    Guardar
</button>

        <button type="button" class="btn btn-secondary" onclick="cerrarModal()">Cancelar</button>
    </div>
</form>

    </div>
</div>

<!-- Estilos Mejorados -->
<style>
    .titulo-beneficios {
    margin-top: 30px; /* Ajusta el espacio */
    font-size: 2.2em;
    font-weight: bold;
    color: #2c3e50;
}
.container {
    max-width: 1000px;
    margin: auto;
}

h2 {
    font-size: 2em;
    font-weight: bold;
    color: #2c3e50;
    text-align: center;
    margin-bottom: 30px;
}

/* Estilo para cada usuario */
.usuario-card {
    background: #ffffff;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0px 6px 12px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
    transition: transform 0.2s ease-in-out;
    text-align: center;
}

.usuario-card:hover {
    transform: translateY(-4px);
    box-shadow: 0px 10px 15px rgba(0, 0, 0, 0.15);
}

/* Nombre del usuario */
.usuario-nombre {
    font-size: 1.4em;
    font-weight: bold;
    color: #34495e;
    margin-bottom: 10px;
}

/* Texto dentro del card */
.usuario-texto {
    font-size: 1.1em;
    color: #555;
}

/* Botones */
.usuario-botones {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-top: 15px;
}

.btn {
    font-size: 1em;
    padding: 10px 15px;
    border-radius: 8px;
    transition: 0.2s;
}

.btn-primary {
    color: #fff;
    background-color: #428bca;
    border-color: #357ebd;

}

.btn-success {
    color: #fff;
    background-color: #5cb85c;
    border-color: #4cae4c;

}

.btn-primary:hover, .btn-success:hover {
    opacity: 0.85;
}

/* Modal */
/* Modal */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    align-items: center;
    justify-content: center;
}

.modal-content {
    background-color: white;
    padding: 15px;  /* Reducir padding */
    border-radius: 10px;
    width: 30%;  /* Reducir el ancho del modal */
    max-width: 500px;  /* Establecer un tamaño máximo */
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.3);
    color: black;
}

.modal-title {
    font-size: 1.2em;  /* Reducir el tamaño del título */
    font-weight: bold;
    color: #2c3e50;
    text-align: center;
    margin-bottom: 10px;  /* Reducir el margen inferior */
}

.modal-body {
    display: flex;
    flex-direction: column;
    gap: 8px;  /* Reducir el espacio entre los campos */
}

.form-group label {
    font-weight: bold;
    margin-bottom: 5px;
}

.form-control {
    padding: 6px;  /* Reducir el padding de los inputs */
    font-size: 0.9em;  /* Reducir el tamaño de la fuente */
    border: 1px solid #ccc;
    border-radius: 5px;
    width: 100%;
}

.modal-footer {
    display: flex;
    justify-content: space-between;
    margin-top: 15px;
}

.btn {
    font-size: 0.9em;  /* Reducir el tamaño de los botones */
    padding: 8px 15px;  /* Reducir el padding de los botones */
    border-radius: 5px;
}

/* Botón "Cancelar" */
.btn-secondary {
    background-color: #95a5a6;
}
</style>
<script>
    
// Asegurar que el modal esté oculto al cargar la página
document.addEventListener("DOMContentLoaded", function() {
    document.getElementById("beneficioModal").style.display = "none";
});

// Función para abrir el modal de agregar beneficio
function abrirModalAgregar(id_usuario) {
    document.getElementById("modalTitle").innerText = "Agregar Beneficio";
    document.getElementById("id_usuario").value = id_usuario;

    // Limpiar los campos del formulario
    document.getElementById("razon").value = "";
    document.getElementById("monto").value = "";
    document.getElementById("medismart").value = "";
    document.getElementById("valor_total").value = "";
    document.getElementById("aporte_patrono").value = "";
    document.getElementById("beneficiarios").value = "";

    // Mostrar el modal correctamente
    document.getElementById("beneficioModal").style.display = "flex";
}

// Función para cerrar el modal
function cerrarModal() {
    document.getElementById("beneficioModal").style.display = "none";
}

// Manejo del formulario para agregar beneficio
document.getElementById("beneficioForm").addEventListener("submit", function(event) {
    event.preventDefault(); // Evitar recarga de página

    let formData = new FormData(this);
    
    fetch("crud_beneficios.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            cerrarModal(); // Cerrar el modal después de guardar
            location.reload(); // Recargar para ver los cambios
        } else {
            alert("Error: " + data.message);
        }
    })
    .catch(error => console.error("Error:", error));
});

</script>
