<?php
session_start();
include 'template.php';

?>


<!DOCTYPE html>
<html lang="es">
<head>
    <!-- Bootstrap core CSS -->
    <link href="assets/css/bootstrap.css" rel="stylesheet">
    <!--external css-->
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />

    <!-- Custom styles for this template -->
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/style-responsive.css" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Días Feriados</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        let editando = false;
        let feriadoId = null;

        document.addEventListener("DOMContentLoaded", function() {
            listarFeriados();
        });

        function listarFeriados() {
            fetch("listar_feriados.php")
                .then(response => response.json())
                .then(data => {
                    let lista = document.getElementById("listaFeriados");
                    lista.innerHTML = "";

                    let feriadosPorMes = {};

                    data.forEach(feriado => {
                        let fecha = new Date(feriado.fecha);
                        let mes = fecha.toLocaleString("es-ES", { month: "long" }).toUpperCase();
                        let emoji = feriado.doble_pago == 1 ? "💰 " : ""; 

                        if (!feriadosPorMes[mes]) {
                            feriadosPorMes[mes] = [];
                        }
                        feriadosPorMes[mes].push({
                            id: feriado.id_fecha,
                            nombre: `${emoji}${feriado.nombre_feriado}`,
                            fecha: fecha.toLocaleDateString("es-ES"),
                            tipo: feriado.tipo_feriado
                        });
                    });

                    for (let mes in feriadosPorMes) {
                        let section = document.createElement("div");
                        section.classList.add("bg-white", "p-4", "rounded-lg", "shadow-md", "mb-6");

                        let title = document.createElement("h2");
                        title.classList.add("text-2xl", "font-bold", "mb-4", "uppercase", "text-gray-700");
                        title.innerText = mes;
                        section.appendChild(title);

                        let grid = document.createElement("div");
                        grid.classList.add("grid", "grid-cols-1", "md:grid-cols-2", "lg:grid-cols-3", "gap-4");

                        feriadosPorMes[mes].forEach(feriado => {
                            let card = document.createElement("div");
                            card.classList.add("bg-gray-100", "p-4", "rounded-lg", "shadow-md", "flex", "flex-col", "justify-between");

                            let nombre = document.createElement("p");
                            nombre.classList.add("text-lg", "font-semibold", "text-gray-800");
                            nombre.innerText = feriado.nombre;

                            let fecha = document.createElement("p");
                            fecha.classList.add("text-sm", "text-gray-600");
                            fecha.innerText = `📅 ${feriado.fecha}`;

                            let tipo = document.createElement("p");
                            tipo.classList.add("text-sm", "text-gray-500");
                            tipo.innerText = `🏷️ ${feriado.tipo}`;

                            let acciones = document.createElement("div");
                            acciones.classList.add("mt-3", "flex", "justify-between");

                            let btnEditar = document.createElement("button");
                            btnEditar.innerText = "Editar";
                            btnEditar.classList.add("bg-blue-500", "text-white", "px-3", "py-1", "rounded");
                            btnEditar.onclick = function() { abrirModalEditar(feriado); };

                            let btnEliminar = document.createElement("button");
                            btnEliminar.innerText = "Eliminar";
                            btnEliminar.classList.add("bg-red-500", "text-white", "px-3", "py-1", "rounded");
                            btnEliminar.onclick = function() { eliminarFeriado(feriado.id); };

                            acciones.appendChild(btnEditar);
                            acciones.appendChild(btnEliminar);

                            card.appendChild(nombre);
                            card.appendChild(fecha);
                            card.appendChild(tipo);
                            card.appendChild(acciones);

                            grid.appendChild(card);
                        });

                        section.appendChild(grid);
                        lista.appendChild(section);
                    }
                });
        }

        function eliminarFeriado(id) {
            if (confirm("¿Seguro que quieres eliminar este feriado?")) {
                fetch("eliminar_feriado.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: `id_fecha=${id}`
                }).then(() => listarFeriados());
            }
        }

        function abrirModal() {
            editando = false;
            feriadoId = null;

            document.getElementById("modalTitulo").innerText = "Agregar Feriado";
            document.getElementById("nombreFeriado").value = "";
            document.getElementById("fechaFeriado").value = "";
            document.getElementById("tipoFeriado").value = "";
            document.getElementById("doblePago").checked = false;
            document.getElementById("modal").classList.remove("hidden");
        }

        function abrirModalEditar(feriado) {
            editando = true;
            feriadoId = feriado.id;

            document.getElementById("modalTitulo").innerText = "Editar Feriado";
            document.getElementById("nombreFeriado").value = feriado.nombre.replace("💰 ", ""); 
            document.getElementById("fechaFeriado").value = feriado.fecha;
            document.getElementById("tipoFeriado").value = feriado.tipo;
            document.getElementById("doblePago").checked = feriado.nombre.includes("💰");

            document.getElementById("modal").classList.remove("hidden");
        }

        function cerrarModal() {
            document.getElementById("modal").classList.add("hidden");
        }

        function guardarFeriado() {
            let nombre = document.getElementById("nombreFeriado").value.trim();
            let fecha = document.getElementById("fechaFeriado").value;
            let tipo = document.getElementById("tipoFeriado").value.trim();
            let doblePago = document.getElementById("doblePago").checked ? "1" : "0";

            if (nombre === "" || fecha === "" || tipo === "") {
                alert("Por favor, completa todos los campos.");
                return;
            }

            let url = editando ? "editar_feriado.php" : "agregar_feriado.php";
            let body = `nombre_feriado=${encodeURIComponent(nombre)}&fecha=${encodeURIComponent(fecha)}&tipo_feriado=${encodeURIComponent(tipo)}&doble_pago=${doblePago}`;

            if (editando) {
                body += `&id_fecha=${feriadoId}`;
            }

            fetch(url, {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: body
            }).then(() => {
                listarFeriados();
                cerrarModal();
            }).catch(error => {
                console.error("Error en la solicitud:", error);
            });
        }
    </script>
</head>
<body class="p-8 bg-gray-200">
    <div class="max-w-6xl mx-auto bg-white p-6 rounded-lg shadow-lg">
        <h1 class="text-3xl font-bold mb-6 text-center"> Gestión de Días Feriados</h1>
        <button class="bg-green-500 text-white px-4 py-2 rounded mb-6 w-full" onclick="abrirModal()">Agregar Feriado</button>

        <div id="listaFeriados"></div>
    </div>

    <!-- MODAL -->
    <div id="modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-white p-6 rounded-lg shadow-lg max-w-md w-full">
            <h2 id="modalTitulo" class="text-xl font-bold mb-4">Agregar Feriado</h2>
            <input type="text" id="nombreFeriado" placeholder="Nombre del Feriado" class="w-full p-2 mb-2 border rounded">
            <input type="date" id="fechaFeriado" class="w-full p-2 mb-2 border rounded">
            <input type="text" id="tipoFeriado" placeholder="Tipo de Feriado" class="w-full p-2 mb-2 border rounded">
            <label class="flex items-center">
                <input type="checkbox" id="doblePago" class="mr-2"> Doble Pago
            </label>
            <div class="flex justify-end mt-4">
                <button onclick="cerrarModal()" class="px-4 py-2 bg-gray-400 rounded mr-2">Cancelar</button>
                <button onclick="guardarFeriado()" class="px-4 py-2 bg-blue-500 text-white rounded">Guardar</button>
            </div>
        </div>
    </div>
</body>
</html>

