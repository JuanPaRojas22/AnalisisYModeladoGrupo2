<?php
require_once __DIR__ . '/../Interfaces/VacacionDAO.php';
require_once __DIR__ . '/../Models/Vacacion.php';
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

class VacacionDAOSImpl implements VacacionDAO
{
    private $conn;

    public function __construct()
    {
        $this->conn = new mysqli("localhost", "root", "", "GestionEmpleados");

    }

    // Funcion para obtener el usuario de la vacacion actual
    public function getUserByIdVacacion($id_vacacion)
    {
        $sql = "SELECT id_usuario FROM vacacion WHERE id_vacacion = ?";

        // Prepara la consulta
        $stmt = $this->conn->prepare($sql);

        // Enlaza el parámetro (i = entero)
        $stmt->bind_param("i", $id_vacacion);

        // Ejecuta la consulta
        $stmt->execute();

        // Obtiene el resultado
        $result = $stmt->get_result();

        // Obtiene el id_usuario
        $id_usuario = null;
        if ($row = $result->fetch_assoc()) {
            $id_usuario = $row['id_usuario'];
        }

        // Devuelve el id_usuario
        return $id_usuario;

    }

    // Funcion para obtener el detalle de la vacacion actual
    public function getDetalleVacacion($id_vacacion)
    {
        $sql = "SELECT * FROM vacacion WHERE id_vacacion = ?";

        // Prepara la consulta
        $stmt = $this->conn->prepare($sql);

        // Enlaza el parámetro (i = entero)
        $stmt->bind_param("i", $id_vacacion);

        // Ejecuta la consulta
        $stmt->execute();

        // Obtiene el resultado
        $result = $stmt->get_result();

        // Array para almacenar las vacaciones
        $vacaciones = [];

        // Recorre cada fila
        while ($row = $result->fetch_assoc()) {
            $vacaciones[] = $row;  // Agrega la fila asociativa al array
        }

        // Devuelve el array de vacaciones
        return $vacaciones;

    }

    // Funcion que obtiene las vacaciones solicitadas por el usuario actual (Rol de Usuario). Se obtienen todas las vacaciones que esten en estado pendiente
    public function getVacacionesSolicitadas($id_usuario, $limit = 5, $offset = 0)
    {
        $conn = $this->conn;
        $stmt = $conn->prepare("
            SELECT 
                V.id_vacacion,
                U.nombre AS Nombre,
                U.apellido AS Apellido,
                D.nombre AS Departamento,
                V.fecha_inicio,
                V.fecha_fin,
                V.diasTomado,
                HV.DiasRestantes,
                EV.descripcion
            FROM vacacion V
            INNER JOIN usuario U ON V.id_usuario = U.id_usuario
            INNER JOIN departamento D ON U.id_departamento = D.id_departamento
            INNER JOIN historial_vacaciones HV ON V.id_historial = HV.id_historial
            INNER JOIN estado_vacacion EV ON V.id_estado_vacacion = EV.id_estado_vacacion
            WHERE V.id_estado_vacacion IN (1)
            AND U.id_usuario = ?
            ORDER BY V.fecha_inicio DESC
            LIMIT ? OFFSET ?
        ");
        
        $stmt->bind_param("iii", $id_usuario, $limit, $offset);
        $stmt->execute();
        return $stmt->get_result();
    }    

    // Funcion que obtiene las solicitudes pendientes de vacaciones de empleados y que sean del departamento del ADMINISTRADOR
    public function getSolicitudesPendientes($id_departamento, $limit = 5, $offset = 0)
    {
        $conn = $this->conn;
        $sql = $conn->prepare("SELECT V.id_vacacion, V.id_usuario, U.Nombre, U.Apellido, Dep.Nombre AS Departamento, 
                    V.fecha_inicio, V.fecha_fin, V.diasTomado, HV.DiasRestantes, EH.descripcion, V.razon
                FROM vacacion V
                INNER JOIN usuario U ON V.id_usuario = U.id_usuario
                INNER JOIN departamento Dep ON U.id_departamento = Dep.id_departamento
                INNER JOIN estado_vacacion EH ON V.id_estado_vacacion = EH.id_estado_vacacion
                INNER JOIN historial_vacaciones HV ON V.id_usuario = HV.id_usuario
                WHERE (V.id_estado_vacacion = 1 OR V.id_estado_vacacion = 4)
                AND U.id_departamento = ?
                /*AND V.id_vacacion = ?*/
                ORDER BY U.Nombre ASC LIMIT ? OFFSET ?
                ");

        $sql->bind_param("iii", $id_departamento, $limit, $offset);
        $sql->execute();
        return $sql->get_result();
    }

    // Funcion para aprobar una solicitud de vacaciones
    public function aprobarSolicitud($id_vacacion, $diasTomado, $id_usuario)
    {
        $function_conn = $this->conn;
        // Se actualiza el estado de la solicitud de vacaciones a aprobado (estado 2)
        $stmt = $function_conn->prepare(
            "UPDATE vacacion
            SET id_estado_vacacion = 2 -- Estado 2 es Aprobado
            WHERE id_vacacion = ?"
        );

        // Se enlazan los parámetros
        $stmt->bind_param("i", $id_vacacion);
        // Ejecuta la actualización
        $stmt->execute();
        $stmt->close();


        // Se tiene que restar los días de vacaciones tomados a los días restantes
        $stmt1 = $function_conn->prepare(
            "UPDATE historial_vacaciones
            SET DiasRestantes = DiasRestantes - ?
            WHERE id_usuario = ?"
        );

        // Se enlazan los parámetros
        $stmt1->bind_param("ii", $diasTomado, $id_usuario);
        // Ejecuta la actualización
        $stmt1->execute();
        $stmt1->close();

    }

    // Funcion para rechazar una solicitud de vacaciones
    public function rechazarSolicitud($id_vacacion)
    {
        $function_conn = $this->conn;
        // Se actualiza el estado de la solicitud de vacaciones a rechazado (estado 3)
        $stmt = $function_conn->prepare(
            "UPDATE vacacion
            SET id_estado_vacacion = 3 -- Estado 3 es Rechazado
            WHERE id_vacacion = ?"
        );

        // Se enlazan los parámetros
        $stmt->bind_param("i", $id_vacacion);
        // Ejecuta la actualización
        $stmt->execute();
        $stmt->close();

    }




    // Para hacer este calculo, cada mes desde que empezo a trabajar el empleado cuenta como un dia de vacacion y se acumula hasta el mes actual
    // Con estos dias sumados, se resta la cantidad de dias que ya ha tomado de vacaciones y se compara con los dias que se quieren tomar
    public function calcularDiasDisponibles($id_usuario, $diasTomado, $fecha_inicio, $DiasRestantes)
    {
        $function_conn = $this->conn;
        // Se obtiene la cantidad de dias restantes que el usuario tiene de vacaciones
        $stmt = $function_conn->prepare(
            "SELECT HV.DiasRestantes
            FROM historial_vacaciones HV
            WHERE HV.id_usuario = ?
            ORDER BY HV.FechaFin DESC
            LIMIT 1"
        );
        $stmt->bind_param(
            "i",
            $id_usuario
        );
        // Se ejecuta el comando
        $stmt->execute();
        $stmt->bind_result($DiasRestantes);
        $stmt->fetch();
        $stmt->close();

        // Se calcula la cantidad de dias solicitados
        $fecha_inicio = new DateTime($FechaInicio);
        $fecha_fin = new DateTime($FechaFin);
        // Se suma 1 porque si se toma vacaciones del 1 al 1, se cuenta como 1 dia
        $dias_solicitados = $fecha_fin->diff($fecha_inicio)->days + 1;

        // Se verifica si el empleado tiene suficientes dias de vacaciones disponibles
        if ($DiasRestantes >= $dias_solicitados) {
            return true;
        } else {
            return false;
        }
    }

    // Funcion para comprobar dias feriados y dias habiles
    public function validaFechasFeriados($fecha_inicio, $fecha_fin)
    {
        $function_conn = $this->conn;
        // Se tiene que obtener todos los dias desde la fecha de inicio hasta la fecha de fin
        $stmt = $function_conn->prepare(
            "SELECT DF.fecha
            FROM dias_feriados DF WHERE
            DF.fecha BETWEEN ? AND ?"
        );
        $stmt->bind_param(
            "ss",
            $fecha_inicio,
            $fecha_fin
        );
        // Se ejecuta el comando
        $stmt->execute();
        $result = $stmt->get_result();
        $feriados = [];
        while ($row = $result->fetch_assoc()) {
            $feriados[] = $row['fecha'];
        }
        $stmt->close();

        // Depuración
        error_log("Fechas feriadas encontradas: " . print_r($feriados, true));

        return $feriados;
    }

    // Funcion que calcula los dias de vacaciones disponibles de un empleado
    public function validarDiasDisponibles($id_usuario, $diasTomados)
    {
        $function_conn = $this->conn;
        // Se obtiene la cantidad de dias restantes que el usuario tiene de vacaciones
        $stmt = $function_conn->prepare(
            "SELECT HV.DiasRestantes
            FROM historial_vacaciones HV 
            WHERE HV.id_usuario = ?
            ORDER BY HV.FechaFin DESC
            LIMIT 1"
        );
        $stmt->bind_param(
            "i",
            $id_usuario
        );
        // Se ejecuta el comando
        $stmt->execute();
        $stmt->bind_result($DiasRestantes);
        $stmt->fetch();
        $stmt->close();

        // Se verifica si el empleado tiene suficientes dias de vacaciones disponibles
        if ($DiasRestantes >= $diasTomados) {
            return true;
        } else {
            return false;
        }

    }

    // Funcion para obtener las fechas ya reservadas por el empleado para que no pueda solicitar vacaciones en esas fechas
    public function getFechasReservadas($id_usuario, $fecha_inicio, $fecha_fin){
        $function_conn = $this->conn;
        $stmt = $function_conn->prepare(
            "SELECT V.fecha_inicio, V.fecha_fin
            FROM vacacion V
            WHERE V.id_estado_vacacion = 2 
            AND V.id_usuario = ?
            AND (
                (V.fecha_inicio BETWEEN ? AND ?) OR
                (V.fecha_fin BETWEEN ? AND ?) OR
                (? BETWEEN V.fecha_inicio AND V.fecha_fin) OR
                (? BETWEEN V.fecha_inicio AND V.fecha_fin)
            )"
        );
        $stmt->bind_param(
            "issssss",
            $id_usuario,
            $fecha_inicio,
            $fecha_fin,
            $fecha_inicio,
            $fecha_fin,
            $fecha_inicio,
            $fecha_fin
        );
    
        $stmt->execute();
        $result = $stmt->get_result();
        $fechas_reservadas = [];
        while ($row = $result->fetch_assoc()) {
            $fechas_reservadas[] = $row;
        }
        $stmt->close();
    
        return $fechas_reservadas;
    }
    

    // Funcion para ingresar una solicitud de vacaciones
    public function IngresarVacacion(
        $razon,
        $diasTomado,
        $FechaInicio,
        $observaciones,
        $id_usuario,
        $id_historial,
        $fechacreacion,
        $usuariocreacion,
        $fechamodificacion,
        $usuariomodificacion,
        $id_estado_vacacion,
        $SolicitudEditar,
        $fecha_fin
    ) {
        $function_conn = $this->conn;
    
        // Logica para calcular la cantidad de dias solicitados con las fechas ingresadas
        $fecha_inicio = new DateTime($FechaInicio);
        $fecha_fin = new DateTime($fecha_fin);
        // Se suma 1 porque si se toma vacaciones del 1 al 1, se cuenta como 1 dia
        $dias_solicitados = $fecha_fin->diff($fecha_inicio)->days + 1;

        // Array para almacenar los errores
        $errores = [];

        // Se tiene que comprobar antes de ingresar la vacacion que el empleado tiene suficientes dias de vacaciones disponibles
        if (!$this->validarDiasDisponibles($id_usuario, $diasTomado)) {
            $errores[] = "El empleado no tiene suficientes días de vacaciones disponibles.";
        }

        // Se comprueba que el usuario no haya ingresado una fecha de inicio mayor a la fecha de fin
        if ($FechaInicio > $fecha_fin->format('Y-m-d')) {
            $errores[] = "La fecha de inicio no puede ser mayor a la fecha de fin.";
        }

        // Se comprueba que los días totales de las fechas solicitadas no sean mayores o menores a la cantidad de días solicitados
        if ($dias_solicitados != $diasTomado) {
            $errores[] = "La cantidad de días solicitados no coincide con las fechas ingresadas";
        }

        // Se compruebas que las fechas ingresadas no estén reservadas por el empleado
        $fechas_reservadas = $this->getFechasReservadas($id_usuario, $FechaInicio, $fecha_fin->format('Y-m-d'));
        if (!empty($fechas_reservadas)) {
            foreach ($fechas_reservadas as $reservada) {
                $errores[] = "El empleado ya tiene vacaciones reservadas del " . $reservada['fecha_inicio'] . " al " . $reservada['fecha_fin'];
            }
        }        
        
        // Se comprueba que las fechas ingresadas no sean feriados
        $feriados = $this->validaFechasFeriados($FechaInicio, $fecha_fin->format('Y-m-d'));

        if (!empty($feriados)) {
            foreach ($feriados as $feriado) {
            $errores[] = "La fecha $feriado es un feriado.";
            }
        }
    

        // Si se da uno o mas errores se retorna la lista de errores y no se ingresa la vacacion
        if(!empty($errores)){
            return $errores;
            //var_dump($errores);
            //exit;
        }
    
        // Se enlazan los parametros
        $fecha_fin_formateada = $fecha_fin->format('Y-m-d');
        // Si es un medio día, el cálculo de días es 0.5

        /*
        if ($diasTomado == 0.5) {
            // Si el empleado pide medio día, solo se valida la fecha de inicio
            $dias_solicitados = 0.5;  // Medio día
            $fecha_fin_obj = null;     // No se necesita la fecha de fin para medio día
        } else {
            // Si no es medio día, calcula la cantidad de días solicitados
            $fecha_inicio = new DateTime($FechaInicio);
            $fecha_fin_obj = new DateTime($fecha_fin_formateada); // Definir la fecha de fin solo si no es medio día
    
            // Se suma 1 porque si se toma vacaciones del 1 al 1, se cuenta como 1 día
            $dias_solicitados = $fecha_fin_obj->diff($fecha_inicio)->days + 1;
        }
    
        // Si no es medio día, convertir la fecha de fin a cadena
        if ($fecha_fin_obj !== null) {
            $fecha_fin_str = $fecha_fin_obj->format('Y-m-d');
        } else {
            $fecha_fin_str = null; // No se necesita la fecha de fin para medio día
        }
        */

        // Se ingresa la vacacion por el administrador
        $stmt = $function_conn->prepare(
            "INSERT INTO vacacion (razon, diasTomado, fecha_inicio, observaciones,
            id_usuario, id_historial, fechacreacion, usuariocreacion,
            fechamodificacion, usuariomodificacion, id_estado_vacacion, SolicitudEditar, fecha_fin)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
    
        
        $stmt->bind_param(
            "ssssiissssiss",
            $razon,
            $diasTomado,
            $FechaInicio,
            $observaciones,
            $id_usuario,
            $id_historial,
            $fechacreacion,
            $usuariocreacion,
            $fechamodificacion,
            $usuariomodificacion,
            $id_estado_vacacion,
            $SolicitudEditar,
            $fecha_fin_formateada
        );
    
        // Ejecuta la inserción
        $stmt->execute();
        $stmt->close();
    
        // Se retorna exito si no hubo errores
        return [];
    }


    public function getFechasReservadasEmpleado($id_usuario)
{
    $function_conn = $this->conn;

    $fechas_reservadas = [];

    // 1. Obtener las fechas de vacaciones del usuario
    $stmtVacaciones = $function_conn->prepare(
        "SELECT fecha_inicio, fecha_fin
        FROM vacacion
        WHERE id_usuario = ? AND (id_estado_vacacion = 1 OR id_estado_vacacion = 2 OR id_estado_vacacion = 4)"
    );
    $stmtVacaciones->bind_param("i", $id_usuario);
    $stmtVacaciones->execute();
    $resultVacaciones = $stmtVacaciones->get_result();

    while ($row = $resultVacaciones->fetch_assoc()) {
        $fechas_reservadas[] = $row;
    }
    $stmtVacaciones->close();

    // 2. Obtener los días feriados
    $queryFeriados = "SELECT fecha AS fecha_inicio, fecha AS fecha_fin FROM dias_feriados";
    $resultFeriados = $function_conn->query($queryFeriados);

    while ($row = $resultFeriados->fetch_assoc()) {
        $fechas_reservadas[] = $row; // Cada día feriado es un rango de 1 solo día
    }

    return $fechas_reservadas;
}

    // Funcion para obtener una vacacion a editar y comparar si la fecha de hoy no sea menor a
    // 8 dias de la fecha de inicio de la vacacion
    public function puedeEditarVacacion($id_vacacion)
    {
        $stmt = $this->conn->prepare("SELECT fecha_inicio FROM vacacion WHERE id_vacacion = ?");
        $stmt->bind_param("i", $id_vacacion);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $fecha_inicio_vacacion = new DateTime($row['fecha_inicio']);
            $fecha_limite = new DateTime(); // hoy
            $fecha_limite->modify('+8 days'); // hoy + 8 días

            if ($fecha_inicio_vacacion > $fecha_limite) {
                return true; // Puede editar
            }
        }
        return false; // No puede editar
    }

    public function getVacacionesPorEstado($id_usuario, array $estados, $limit = 5, $offset = 0) {
        $placeholders = implode(',', array_fill(0, count($estados), '?'));
        $types = str_repeat('i', count($estados) + 3);
        $sql = "
        SELECT V.id_vacacion, U.nombre, U.apellido, D.nombre AS Departamento,
                V.fecha_inicio, V.fecha_fin, V.diasTomado, HV.DiasRestantes, EV.descripcion
        FROM vacacion V
        JOIN usuario U ON V.id_usuario = U.id_usuario
        JOIN departamento D ON U.id_departamento = D.id_departamento
        JOIN historial_vacaciones HV ON V.id_historial = HV.id_historial
        JOIN estado_vacacion EV ON V.id_estado_vacacion = EV.id_estado_vacacion
        WHERE V.id_estado_vacacion IN ($placeholders)
            AND U.id_usuario = ?
        ORDER BY V.fecha_inicio DESC
        LIMIT ? OFFSET ?
        ";
        $stmt = $this->conn->prepare($sql);
        $bindParams = array_merge($estados, [$id_usuario, $limit, $offset]);
        $stmt->bind_param($types, ...$bindParams);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function contarVacacionesPorEstado($id_usuario, $estados) {
       $conn = $this->conn;
        $placeholders = implode(',', array_fill(0, count($estados), '?'));
        $types = str_repeat('i', count($estados) + 1);
        $params = array_merge([$id_usuario], $estados);

        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM vacacion WHERE id_usuario = ? AND id_estado_vacacion IN ($placeholders)");
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['total'] ?? 0;
    }

}   

?>