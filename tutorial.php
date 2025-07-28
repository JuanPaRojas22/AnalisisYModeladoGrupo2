<?php
session_start();
require 'conexion.php';
require 'template.php'; // Se mantiene tu nuevo template con el layout actualizado

// Verificar si el usuario está logueado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}
?>

<!-- Incluir Intro.js -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intro.js/minified/introjs.min.css">
<script src="https://cdn.jsdelivr.net/npm/intro.js/minified/intro.min.js"></script>

<style>
    .overlay {
        position: fixed;
        top: 0; left: 0;
        width: 100%; height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 2000;
    }

    .tutorial-content {
        background-color: aliceblue;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        text-align: center;
        width: 300px;
        color: black;
    }

    .welcome-text {
        font-family: 'Ruda', sans-serif;
        font-size: 1.5rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 20px;
        line-height: 1.5;
    }

    .start-button {
        padding: 12px 25px;
        background-color: #147964;
        color: white;
        border: 2px solid rgb(19, 110, 92);
        border-radius: 8px;
        font-size: 1.2rem;
        cursor: pointer;
        transition: background-color 0.3s, transform 0.2s;
        width: 200px;
        text-align: center;
        box-shadow: 0 4px 6px rgba(7, 70, 78, 0.1);
        margin: 10px 0;
    }

    .start-button:hover {
        background-color: rgb(16, 104, 87);
        transform: translateY(-2px);
        box-shadow: 0 8px 12px rgba(19, 93, 109, 0.2);
    }

    .start-button:focus {
        outline: none;
        box-shadow: #0B4F6C;
    }

    #sidebar {
        z-index: 1;
    }

    .introjs-tooltiptext {
        color: black;
        font-family: 'Ruda', sans-serif;
        font-size: 1.2rem;
    }
</style>

<!-- Overlay de bienvenida -->
<div class="overlay" id="tutorialOverlay">
    <div class="tutorial-content">
        <h1 class="welcome-text">Bienvenido al Sistema</h1>
        <p class="welcome-text">
            Este es un tutorial interactivo para guiarte por las funciones del sistema.
            Haz clic en el botón de abajo para comenzar el tutorial y aprender cómo utilizar las principales secciones del menú.
        </p>
        <button onclick="startTutorial()" class="start-button">Iniciar Tutorial</button>
        <a href="index.php"><button class="start-button">Volver a Inicio</button></a>
    </div>
</div>

<!-- Script del tutorial -->
<script>
    window.onload = function () {
        document.getElementById('tutorialOverlay').style.display = 'flex';
    };

    function startTutorial() {
        document.getElementById('tutorialOverlay').style.display = 'none';

        introJs().setOptions({
            steps: [
                {
                    element: document.querySelector('#dashboard'),
                    intro: 'Este es el Dashboard, donde puedes ver información clave.'
                },
                {
                    element: document.querySelector('#ui-elements'),
                    intro: 'En los Elementos de UI, puedes configurar la interfaz.'
                },
                {
                    element: document.querySelector('#components'),
                    intro: 'Aquí puedes acceder a varios componentes útiles para la plataforma.'
                },
                {
                    element: document.querySelector('#reportes'),
                    intro: 'En Reportes, podrás ver y generar reportes específicos.'
                },
                {
                    element: document.querySelector('#administracion'),
                    intro: 'Desde la Administración, los administradores pueden gestionar usuarios y configuraciones.'
                },
                {
                    element: document.querySelector('#beneficios'),
                    intro: 'Aquí puedes ver y gestionar los beneficios de los empleados.'
                },
                {
                    element: document.querySelector('#vacaciones'),
                    intro: 'En Vacaciones, puedes gestionar las solicitudes de vacaciones.'
                },
                {
                    element: document.querySelector('#preguntas-frecuentes'),
                    intro: 'Consulta las preguntas frecuentes para obtener más información sobre el sistema.'
                }
            ],
            showStepNumbers: false,
            exitOnEsc: true,
            exitOnOverlayClick: true,
            nextLabel: "Siguiente",
            prevLabel: "Anterior",
            doneLabel: "Finalizar"
        }).start();
    }
</script>
