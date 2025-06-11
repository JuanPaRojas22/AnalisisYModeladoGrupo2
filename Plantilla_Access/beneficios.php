<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "gestionempleados");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Verificar autenticación del usuario
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}

$id_usuario = $_SESSION['id_usuario'];
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Invitado';

// Incluir la plantilla
include 'template.php';
?>

<!-- Contenedor Principal -->
<div class="container mt-4">
    <h2 class="text-center">Mis Beneficios Médicos Activos</h2>
    <h4 class="text-center text-primary">Empleado: <span id="nombreUsuario">No existen beneficios médicos activos asignados a su perfil.</span></h4>

    <!-- Contenedor de Cards -->
    <div class="row" id="beneficiosContainer">
        <p class="text-center">Cargando beneficios...</p>
    </div>
</div>

<!-- Estilos para las Cards -->
<style>
.container {
    max-width: 1100px;
    margin: auto;
    padding-bottom: 40px;
}

h2 {
    font-size: 2.2em;
    font-weight: bold;
    color: #0B4F6C;
    text-align: center;
    margin-top: 40px;  /* Antes estaba muy pegado, ahora tiene más espacio */
    margin-bottom: 30px;
}

h4 {
    margin-bottom: 30px; /* Más espacio debajo del nombre del empleado */
    font-size: 1.5em;
    font-weight: bold;
    color:rgb(0, 0, 0);
}

.card-beneficio {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
    padding: 20px;
    text-align: left;
    margin-bottom: 20px;
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.card-beneficio:hover {
    transform: translateY(-5px);
    box-shadow: 0px 6px 12px rgba(0, 0, 0, 0.15);
}

.card-title {
    font-size: 1.2em;
    font-weight: bold;
    color: #0D566B;
    text-transform: capitalize;
    border-bottom: 2px solid #0D566B;
    padding-bottom: 5px;
    margin-bottom: 10px;
}

.card-text {
    font-size: 1em;
    color: #555;
    margin-bottom: 5px;
}

.card-text strong {
    color: #333;
}

</style>

<!-- Script para cargar los beneficios -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    fetch("get_beneficios.php")
        .then(response => response.json())
        .then(data => {
            console.log("Datos recibidos:", data); // Depuración
            let beneficiosContainer = document.getElementById("beneficiosContainer");
            let nombreUsuario = document.getElementById("nombreUsuario");

            beneficiosContainer.innerHTML = ""; 

            if (data.error) {
                beneficiosContainer.innerHTML = `<p class="text-center">${data.error}</p>`;
                return;
            }

            if (data.length === 0) {
                beneficiosContainer.innerHTML = `<p class="text-center">No se encontraron beneficios activos.</p>`;
                return;
            }

            // Asignar el nombre del usuario
            nombreUsuario.textContent = data[0].empleado;

            // Generar las cards dinámicamente
            data.forEach(beneficio => {
                beneficiosContainer.innerHTML += `
                    <div class="col-md-4">
                        <div class="card-beneficio p-3">
                            <h5 class="card-title">${beneficio.razon}</h5>
                            <p class="card-text"><strong>Monto:</strong> ₡${beneficio.monto}</p>
                            <p class="card-text"><strong>ID MediSmart:</strong> ${beneficio.identificacion_medismart}</p>
                            <p class="card-text"><strong>Valor Total:</strong> ₡${beneficio.valor_plan_total}</p>
                            <p class="card-text"><strong>Aporte Patrono:</strong> ₡${beneficio.aporte_patrono}</p>
                            <p class="card-text"><strong>Beneficiarios:</strong> ${beneficio.beneficiarios}</p>
                            <p class="card-text"><strong>Fecha Creación:</strong> ${beneficio.fechacreacion}</p>
                        </div>
                    </div>
                `;
            });
        })
        .catch(error => console.error("Error cargando beneficios:", error));
});
</script>
