<?php

session_start();
require 'conexion.php';
require "template.php";

// Verificar si el usuario está logueado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Tutorial</title>
    <!-- Incluir el CSS y JS de Intro.js -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intro.js/minified/introjs.min.css">
    <script src="https://cdn.jsdelivr.net/npm/intro.js/minified/intro.min.js"></script>

    <!-- Bootstrap core CSS -->
    <link href="assets/css/bootstrap.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">
    <link rel="stylesheet" type="text/css" href="assets/css/style-responsive.css" rel="stylesheet">

    <script src="assets/js/chart-master/Chart.js"></script>

    <style>
        /* Estilo para el overlay con el tutorial */
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5); /* Fondo semitransparente */
            display: none; /* Por defecto oculto */
            justify-content: center;
            align-items: center;
            z-index: 1000; /* Asegura que el overlay esté por encima de otros elementos */
        }

        /* Estilo para el contenido del tutorial */
        .tutorial-content {
            background-color: aliceblue;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 300px;
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
    padding: 12px 25px;  /* Mantener el tamaño del botón original */
    background-color:  #147964;  /* Color de fondo */
    color: white;  /* Color del texto */
    border: 2px solidrgb(19, 110, 92);  /* Borde visible */
    border-radius: 8px;  /* Bordes redondeados */
    font-size: 1.2rem;  /* Tamaño de fuente */
    cursor: pointer;  /* Cambiar el cursor para que se vea interactivo */
    transition: background-color 0.3s, transform 0.2s;  /* Animación para el hover */
    width: 200px;  /* Ancho fijo */
    text-align: center;  /* Centrar el texto dentro del botón */
    box-shadow: 0 4px 6px rgba(7, 70, 78, 0.1);  /* Sombra sutil */
    margin: 10px 0;  /* Agregar margen para separarlos (arriba y abajo) */
}

.start-button:hover {
    background-color:rgb(16, 104, 87);  /* Cambiar el color de fondo cuando se pasa el cursor */
    transform: translateY(-2px);  /* Efecto de elevación al pasar el cursor */
    box-shadow: 0 8px 12px rgba(19, 93, 109, 0.2);  /* Aumentar la sombra en hover */
}

.start-button:focus {
    outline: none;  /* Quitar el borde de enfoque predeterminado */
    box-shadow: #0B4F6C;  /* Agregar un borde de enfoque azul */
}


        /* Sidebar */
        #sidebar {
            z-index: 1; /* Asegura que el menú esté por encima del overlay */
        }
    </style>
</head>

<body>
    <section id="container">
        <!-- Botón para iniciar el tutorial -->
       

        <!-- Menú de navegación (barra lateral) -->
        <aside id="sidebar">
            <div id="sidebar" class="nav-collapse">
                <ul class="sidebar-menu" id="nav-accordion">
                   

                    <li class="mt">
                        <a id="dashboard" href="index.php">
                            <i class="fa fa-dashboard"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>

                    <li class="sub-menu">
                        <a id="ui-elements" href="javascript:;">
                            <i class="fa fa-desktop"></i>
                            <span>UI Elements</span>
                        </a>
                    </li>

                    <li class="sub-menu">
                        <a id="components" href="javascript:;">
                            <i class="fa fa-cogs"></i>
                            <span>Components</span>
                        </a>
                    </li>

                    <li class="sub-menu">
                        <a id="reportes" href="javascript:;">
                            <i class="fa fa-desktop"></i>
                            <span>Reportes</span>
                        </a>
                    </li>

                    <li class="sub-menu">
                        <a id="administracion" href="javascript:;">
                            <i class="bi bi-person-fill-gear"></i>
                            <span>Administración</span>
                        </a>
                    </li>

                    <li class="sub-menu">
                        <a id="beneficios" href="javascript:;">
                            <i class="bi bi-sun"></i>
                            <span>Beneficios</span>
                        </a>
                    </li>

                    <li class="sub-menu">
                        <a id="vacaciones" href="javascript:;">
                            <i class="bi bi-sun"></i>
                            <span>Vacaciones</span>
                        </a>
                    </li>

                    <li class="sub-menu">
                        <a id="preguntas-frecuentes" href="javascript:;">
                            <i class="bi bi-question-circle"></i>
                            <span>Preguntas Frecuentes</span>
                        </a>
                    </li>
                </ul>
            </div>
        </aside>
    </section>

    <!-- Overlay para mostrar el tutorial -->
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

    <script>
    // Ejecutar automáticamente el tutorial cuando se carga la página
    window.onload = function() {
        startTutorial();
    };

    // Función para iniciar el tutorial
    function startTutorial() {
        // Mostrar el overlay
        document.getElementById('tutorialOverlay').style.display = 'flex';

        // Iniciar el tutorial con Intro.js
        introJs()
            .setOptions({
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
                showStepNumbers: false,  // Ocultar el número de pasos
                exitOnEsc: true,  // Permite cerrar el tutorial con la tecla Esc
                exitOnOverlayClick: true,  // Permite cerrar el tutorial al hacer clic fuera del tooltip
                nextLabel: "Siguiente",  // Personaliza el texto del botón "Next"
                prevLabel: "Anterior",  // Personaliza el texto del botón "Prev"
            })
            .start(); // Inicia el tutorial
    }
</script>

</body>
</html>
