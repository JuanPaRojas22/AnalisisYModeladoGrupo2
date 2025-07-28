<?php
session_start();
require 'conexion.php';
require 'template.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}
?>

<!-- Incluir estilos de Intro.js -->
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
        background-color: #f0f8ff;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        width: 320px;
        text-align: center;
    }

    .welcome-text {
        font-size: 1.4rem;
        font-weight: bold;
        color: #333;
        margin-bottom: 15px;
    }

    .start-button {
        padding: 10px 20px;
        background-color: #147964;
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 1rem;
        margin: 10px;
        cursor: pointer;
        transition: 0.3s;
    }

    .start-button:hover {
        background-color: #0f5e51;
        transform: scale(1.02);
    }
</style>

<!-- Overlay de bienvenida -->
<div class="overlay" id="tutorialOverlay">
    <div class="tutorial-content">
        <h2 class="welcome-text">Bienvenido al Sistema</h2>
        <p class="welcome-text">Este es un tutorial interactivo para guiarte por las funciones del sistema.</p>
        <button onclick="startTutorial()" class="start-button">Iniciar Tutorial</button>
        <a href="index.php"><button class="start-button">Volver a Inicio</button></a>
    </div>
</div>

<script>
    window.onload = function () {
        document.getElementById('tutorialOverlay').style.display = 'flex';
    };

    function startTutorial() {
        document.getElementById('tutorialOverlay').style.display = 'none';

        introJs().setOptions({
            steps: [
                {
                    element: document.querySelector('a[href="index.php"]'),
                    intro: 'Este es el panel principal donde verás un resumen del sistema.'
                },
                {
                    element: document.querySelector('a[href="reporte_ins.php"]'),
                    intro: 'Aquí podés ver el reporte para INS.'
                },
                {
                    element: document.querySelector('a[href="admin_beneficios.php"]'),
                    intro: 'Desde aquí podés gestionar los beneficios.'
                },
                {
                    element: document.querySelector('a[href="vacaciones.php"]'),
                    intro: 'Accedé al módulo para gestionar vacaciones.'
                },
                {
                    element: document.querySelector('a[href="historial_salarios.php"]'),
                    intro: 'Aquí podés ver el historial de salarios.'
                },
                {
                    element: document.querySelector('a[href="preguntasfreq.php"]'),
                    intro: 'Si tenés dudas, revisá las preguntas frecuentes.'
                },
                {
                    element: document.querySelector('a[href="ver_notificaciones.php"]'),
                    intro: 'Aquí se muestran tus notificaciones pendientes.'
                }
            ],
            nextLabel: 'Siguiente',
            prevLabel: 'Anterior',
            doneLabel: 'Finalizar',
            exitOnEsc: true,
            exitOnOverlayClick: true,
            showStepNumbers: false
        }).start();
    }
</script>
