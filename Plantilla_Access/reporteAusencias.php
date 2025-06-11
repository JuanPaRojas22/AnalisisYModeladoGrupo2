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


// 1. Lectura de filtros desde el formulario
$justificada = $_POST['justificada'] ?? 'Todas';
$desde       = $_POST['desde']       ?? '';
$hasta       = $_POST['hasta']       ?? '';

// 2. Construcción dinámica de la cláusula WHERE
$where   = [];
$params  = [];
$types   = '';

// Filtro: justificada
if ($justificada !== 'Todas') {
    $where[]    = 'a.justificada = ?';
    $types     .= 's';
    $params[]   = $justificada;
}
// Filtro: fecha desde
if (!empty($desde)) {
    $where[]    = 'a.fecha >= ?';
    $types     .= 's';
    $params[]   = $desde;
}
// Filtro: fecha hasta
if (!empty($hasta)) {
    $where[]    = 'a.fecha <= ?';
    $types     .= 's';
    $params[]   = $hasta;
}

// 3. Consulta principal con agrupación por empleado, mes y justificación
$sql = "
    SELECT 
      u.nombre             AS empleado,
      COUNT(a.id_ausencia) AS total_ausencias,
      MONTH(a.fecha)       AS mes,
      a.justificada
    FROM Ausencias a
    JOIN Usuario u ON a.id_usuario = u.id_usuario
";
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= "
    GROUP BY 
      u.nombre, 
      MONTH(a.fecha), 
      a.justificada
    ORDER BY mes ASC, empleado ASC
";

// 4. Preparar y ejecutar
$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$data   = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Reporte de Ausencias</title>
  <style>
    body { font-family: 'Ruda', 
        sans-serif; 
        background:#f7f7f7; 
        margin:0; 
        padding:0;
    }
    .container { width:80%; 
        max-width:1000px; 
        margin:50px auto; 
        background:#fff;
       padding:40px;
        border-radius:12px;
        box-shadow:0 4px 12px rgba(0,0,0,0.1); 
    }
    h1 { text-align:center; 
        color:#333; 
        margin-bottom:30px; 
        font-size:2em; 
    }
    form.filters { display:flex; 
        flex-wrap:wrap;
         gap:20px; 
         margin-bottom:20px; 
         align-items: flex-end;
        }
    .filters .form-group { 
        flex:1 1 200px; 
        display:flex; 
        flex-direction:column; 
    }
    label { 
        font-weight:bold; 
        margin-bottom:8px; 
        color:#333; 
    }
    input, select { 
        padding:12px; 
        font-size:14px; 
        border:1px solid #ddd; 
        border-radius:8px;
        background:#f9f9f9; 
        transition:0.3s; 
    }
    input:focus, select:focus { 
        border-color:#147964;
         box-shadow:0 0 8px rgba(20,121,100,0.4); 
         outline:none; 
        }
    .btn { background:#147964;
         color:#fff; 
         padding:10px 18px; 
         font-weight:bold;
          border:none;
        border-radius:5px; 
        cursor:pointer; 
        transition:0.3s;
     }
    .btn:hover {
         background:#126e58;
         }
    table { 
        width:100%; 
        border-collapse:collapse;
         margin-top:20px;
          border-radius:8px; 
          overflow:hidden; }
    th, td { padding:10px 12px; 
        text-align:center;
         font-size:14px; 
         color:#555;
          border-bottom:1px solid #ddd;
         }
    th { 
        background:#116B67;
         color:#fff;
         }
    tr:nth-child(even) td { 
        background:#f9f9f9;
     }
    tr:hover td {
         background:#eaeaea;
         }
    .pagination {
         display:flex;
          justify-content:center; 
          margin-top:20px; 
        }
    .pagination button { margin:0 5px;
         padding:8px 12px; 
         border:1px solid #ccc;
         background:#f7f7f7; 
         cursor:pointer; border-radius:5px; 
         transition:0.3s; 
        }
    .pagination button.active { background:#147964; 
        color:#fff;
     }
    .pagination button:hover { 
        background:#ddd; 
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>Reporte de Ausencias</h1>

    <!-- 1) Formulario de filtros -->
    <form method="POST" action="" class="filters">
      <div class="form-group">
        <label for="justificada">Justificada:</label>
        <select name="justificada" id="justificada" style="color: #000;">
  <option value="Todas" <?= $justificada==='Todas'?'selected':'' ?>>Todas</option>
  <option value="Sí"     <?= $justificada==='Sí'    ?'selected':'' ?>>Sí</option>
  <option value="No"     <?= $justificada==='No'    ?'selected':'' ?>>No</option>
</select>

      </div>
      <div class="form-group">
        <label for="desde">Fecha desde:</label>
        <input type="date" name="desde" id="desde" value="<?= htmlspecialchars($desde) ?>">
      </div>
      <div class="form-group">
        <label for="hasta">Fecha hasta:</label>
        <input type="date" name="hasta" id="hasta" value="<?= htmlspecialchars($hasta) ?>">
      </div>
      <div class="form-group" style="align-self:flex-end;">
        <button type="submit" class="btn">Filtrar</button>
      </div>
    </form>

   <!-- 2) Botón de exportación con filtros ocultos -->
<?php if (count($data) > 0): ?>
  <form method="POST" action="exportarReporte.php">
    <input type="hidden" name="justificada" value="<?= htmlspecialchars($justificada) ?>">
    <input type="hidden" name="desde"       value="<?= htmlspecialchars($desde) ?>">
    <input type="hidden" name="hasta"       value="<?= htmlspecialchars($hasta) ?>">
    <button type="submit" class="btn">Exportar Reporte</button>
  </form>
<?php else: ?>
  <p style="color:#a00; text-align:center; margin:20px 0;">
    No hay ausencias que coincidan con los filtros. No se puede exportar el reporte.
  </p>
<?php endif; ?>

    <!-- 3) Tabla de resultados -->
    <table id="dataTable">
      <thead>
        <tr>
          <th>Empleado</th>
          <th>Total Ausencias</th>
          <th>Mes</th>
          <th>Justificada</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($data as $row): ?>
        <tr>
          <td><?= htmlspecialchars($row['empleado']) ?></td>
          <td><?= htmlspecialchars($row['total_ausencias']) ?></td>
          <td><?= htmlspecialchars($row['mes']) ?></td>
          <td><?= htmlspecialchars($row['justificada']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <!-- 4) Paginación -->
    <div class="pagination" id="pagination"></div>

    <!-- 5) Gráfico con Chart.js -->
    <h2>Ausencias por Mes</h2>
    <canvas id="ausenciasChart" width="400" height="200"></canvas>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    // Datos para la tabla paginada
    const rows = Array.from(document.querySelectorAll('#dataTable tbody tr'));
    const rowsPerPage = 10;
    const pagination = document.getElementById('pagination');

    function displayPage(page) {
      const start = (page - 1) * rowsPerPage;
      const end = start + rowsPerPage;
      rows.forEach((r, i) => r.style.display = (i>=start && i<end) ? '' : 'none');
      Array.from(pagination.children)
           .forEach((btn,i) => btn.classList.toggle('active', i+1===page));
    }

    function setupPagination() {
      const totalPages = Math.ceil(rows.length / rowsPerPage);
      pagination.innerHTML = '';
      for(let i=1;i<=totalPages;i++){
        const btn = document.createElement('button');
        btn.textContent = i;
        btn.onclick = ()=>displayPage(i);
        pagination.appendChild(btn);
      }
      if(totalPages) displayPage(1);
    }
    setupPagination();

    // Datos para el gráfico
    const data = <?= json_encode($data) ?>;
    const labels = [...new Set(data.map(item => item.mes))];
    const dataset = labels.map(m =>
      data.filter(i=>i.mes==m)
          .reduce((sum,i)=>sum + parseInt(i.total_ausencias),0)
    );

    const ctx = document.getElementById('ausenciasChart').getContext('2d');
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [{ label: 'Ausencias', data: dataset }]
      },
      options: { scales: { y: { beginAtZero:true } } }
    });
  </script>
</body>
</html>

</html>
<style>
    .pagination {
    display: flex;
    justify-content: center;
    margin-top: 20px;
}

.pagination button {
    margin: 0 5px;
    padding: 8px 12px;
    border: 1px solid #ccc;
    background-color: #f7f7f7;
    cursor: pointer;
    border-radius: 5px;
    transition: background-color 0.3s;
}

.pagination button.active {
    background-color: #147964;
    color: white;
}

.pagination button:hover {
    background-color: #ddd;
}
body {
    font-family: 'Ruda', sans-serif;
    background-color: #f7f7f7;
    margin: 0;
    padding: 0;
}

.container {
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    align-items: center;
    background-color: #fff;
    margin-top: 50px;
    padding: 40px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    width: 80%; /* Reduced width */
    max-width: 1000px; /* Limiting the width */
}

h1 {
    font-size: 2em; /* Adjust font size */
    color: #333;
    text-align: center;
    margin-bottom: 30px;
    font-weight: bold;
}

h2 {
    font-size: 1.5em;
    text-align: center;
    color: #333;
    margin-top: 40px;
    font-weight: bold;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    border-radius: 8px;
    overflow: hidden;
}

th, td {
    padding: 10px 12px; /* Reduced padding */
    text-align: center;
    font-size: 14px; /* Reduced font size */
    color: #555;
    border-bottom: 1px solid #ddd;
}

th {
    background-color: #116B67;
    color: #fff;
}

tr:hover {
    background-color: #f1f1f1;
}

td {
    background-color: #f9f9f9;
}

.btn {
    display: inline-block;
    background-color: #147964;
    color: white;
    padding: 10px 18px; /* Adjusted padding */
    font-size: 14px; /* Adjusted font size */
    font-weight: bold;
    text-decoration: none;
    border-radius: 5px;
    margin-bottom: 20px;
    transition: background-color 0.3s;
    cursor: pointer;
    border: none;
}

.btn:hover {
    background-color: #0B4F6C;
}

.btn:active {
    background-color: #0B4F6C;
}

select {
    width: 70%;
    padding: 10px;
    font-size: 14px; /* Adjusted font size */
    border: 2px solid rgb(15, 15, 15);
    border-radius: 5px;
    background: #f9f9f9;
    margin-bottom: 15px;
    cursor: pointer;
    transition: all 0.3s ease;
}

select:hover {
    border-color: #116B67;
}

select:focus {
    outline: none;
    border-color: #147964;
    box-shadow: 0 0 5px rgba(20, 121, 100, 0.4);
}

form {
    width: 100%;
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 20px;
}

.form-group {
    flex-basis: 48%;
    display: flex;
    flex-direction: column;
}

label {
    margin-bottom: 8px;
    font-weight: bold;
    color: #333;
}

input[type="date"], input[type="text"], select {
    padding: 12px;
    font-size: 14px;
    border-radius: 8px;
    border: 1px solid #ddd;
    background-color: #f9f9f9;
    transition: all 0.3s ease;
}

input[type="date"]:focus, input[type="text"]:focus, select:focus {
    border-color: #147964;
    box-shadow: 0 0 8px rgba(20, 121, 100, 0.4);
    outline: none;
}

@media (max-width: 768px) {
    .container {
        margin-top: 20px;
        padding: 20px;
    }

    h1 {
        font-size: 2em;
    }

    .form-group {
        flex-basis: 100%;
    }

    .btn {
        padding: 10px 18px;
        font-size: 1em;
    }

    table {
        font-size: 14px;
    }
}
</style>