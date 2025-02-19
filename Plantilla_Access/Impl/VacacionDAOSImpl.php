<?php
require_once __DIR__ . '/../Interfaces/VacacionDAO.php';
require_once __DIR__ . '/../Models/Vacacion.php';

class VacacionDAOSImpl implements VacacionDAO
{
    private $conn;

    public function __construct()
    {
        $this->conn = new mysqli("localhost", "root", "", "GestionEmpleados");

    }

    // Funcion que obtiene las solicitudes pendientes de vacaciones de empleados
    public function getSolicitudesPendientes(){
        $function_conn = $this->conn;
        $stmt = $function_conn->prepare(
            "SELECT V.id_usuario, U.Nombre, U.Apellido, V.fecha_inicio, V.diasTomado, HV.DiasRestantes, EH.descripcion
            FROM vacacion V
            INNER JOIN usuario U ON V.id_usuario = U.id_usuario
            inner join estado_vacacion EH ON V.id_estado_vacacion = EH.id_estado_vacacion
            INNER JOIN historial_vacaciones HV ON V.id_usuario = HV.id_usuario
            WHERE V.id_estado_vacacion = 1
            ORDER BY U.nombre ASC");
        
        return $stmt;
        $stmt->close();
    }

    // Funcion para aprobar una solicitud de vacaciones
    public function aprobarSolicitud($id_usuario){
        $function_conn = $this->conn;
        $stmt = $function_conn->prepare(
            "UPDATE vacacion
            SET id_estado_vacacion = 2 -- Estado 2 es Aprobado
            WHERE id_usuario = ?");
        $stmt->bind_param(
            "i",
            $id_usuario
        );
        $stmt->execute();
        echo "Solicitud aprobada" . "<br>";
        $stmt->close();

    }

    // Funcion para rechazar una solicitud de vacaciones
    public function rechazarSolicitud($id_usuario){
        $function_conn = $this->conn;
        $stmt = $function_conn->prepare(
            "UPDATE vacacion
            SET id_estado_vacacion = 3 -- Estado 3 es Rechazado
            WHERE id_usuario = ?");
        $stmt->bind_param(
            "i",
            $id_usuario
        );
        $stmt->execute();
        echo "Solicitud rechazada" . "<br>";
        $stmt->close();

    }
    // Para hacer este calculo, cada mes desde que empezo a trabajar el empleado cuenta como un dia de vacacion y se acumula hasta el mes actual
    // Con estos dias sumados, se resta la cantidad de dias que ya ha tomado de vacaciones y se compara con los dias que se quieren tomar
    public function calcularDiasDisponibles($id_usuario, $diasTomado, $fecha_inicio, $DiasRestantes){
        $function_conn = $this->conn;
        // Se obtiene la cantidad de dias restantes que el usuario tiene de vacaciones
        $stmt = $function_conn->prepare(
            "SELECT HV.DiasRestantes
            FROM historial_vacaciones HV 
            WHERE HV.id_usuario = ? 
            ORDER BY HV.FechaFin DESC
            LIMIT 1");
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
            echo "El empleado tiene suficientes días de vacaciones disponibles";
        } else {
            echo "El empleado no tiene suficientes días de vacaciones disponibles";
        }
    }

    // Funcion para comprobar dias feriados y dias habiles
    public function validaFechas($fecha_inicio, $fecha_fin){
        $function_conn = $this->conn;
        // Se obtiene la cantidad de dias que el usuario ha tomado de vacaciones
        $stmt = $function_conn->prepare(
            "SELECT SUM(HV.DiasTomados) as DiasTomados
            FROM historial_vacaciones HV 
            WHERE HV.id_usuario = ?");
        $stmt->bind_param(
            "i",
            $id_usuario
        );
        // Se ejecuta el comando
        $stmt->execute();
        $stmt->bind_result($DiasTomadosTomados);
        $stmt->fetch();
        $stmt->close();

        // Se calcula la cantidad de dias de vacaciones acumulados desde la fecha de ingreso
        $fecha_actual = new DateTime();
        $fecha_ingreso = new DateTime($fecha_ingreso);
        $interval = $fecha_ingreso->diff($fecha_actual);
        // La logica del siguiente funciona que cada mes trabajado por empleado, cuenta como un dia de vacacion
        $meses_trabajados = ($interval->y * 12) + $interval->m;
        $dias_acumulados = $meses_trabajados;

        // Se calcula la cantidad de dias solicitados
        $fecha_inicio = new DateTime($FechaInicio);
        $fecha_fin = new DateTime($FechaFin);
        // Se suma 1 porque si se toma vacaciones del 1 al 1, se cuenta como 1 dia 
        $dias_solicitados = $fecha_fin->diff($fecha_inicio)->days + 1;

        // Se verifica si el empleado tiene suficientes dias de vacaciones disponibles
        $dias_disponibles = $dias_acumulados - $DiasTomadosTomados;
        if ($dias_disponibles >= $dias_solicitados) {
            echo "El empleado tiene suficientes días de vacaciones disponibles";
        } else {
            echo "El empleado no tiene suficientes días de vacaciones disponibles";
        }
    }

    // Hacer funcion que valide si el usuario tiene dias disponibles de vacaciones antes de que el admin las ingrese.
    // Para hacer este calculo, cada mes desde que empezo a trabajar el empleado cuenta como un dia de vacacion y se acumula hasta el mes actual
    // Con estos dias sumados, se resta la cantidad de dias que ya ha tomado de vacaciones y se compara con los dias que se quieren tomar
    public function validarDiasDisponibles($id_usuario, $FechaInicio, $FechaFin){
        $function_conn = $this->conn;
        // Se obtiene la cantidad de dias restantes que el usuario tiene de vacaciones
        $stmt = $function_conn->prepare(
            "SELECT HV.DiasRestantes
            FROM historial_vacaciones HV 
            WHERE HV.id_usuario = ?
            ORDER BY HV.FechaFin DESC
            LIMIT 1");
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
            echo "El empleado tiene suficientes días de vacaciones disponibles";
        } else {
            echo "El empleado no tiene suficientes días de vacaciones disponibles";
        }
    }

    public function IngresarVacacion($id_usuario, $FechaInicio, $FechaFin, $DiasTomados, $Razon)
    {
        $function_conn = $this->conn;
        // Se ingresa la vacacion por el administrador
        $stmt = $function_conn->prepare(
            "INSERT INTO historial_vacaciones (id_usuario, FechaInicio, FechaFin, DiasTomados, Razon) 
            VALUES ('$id_usuario', '$FechaInicio', '$FechaFin', '$DiasTomados', '$Razon')");
        $stmt->bind_param(
            "issss",
            $id_usuario,
            $FechaInicio,
            $FechaFin,
            $DiasTomados,
            $Razon
        );
        $stmt->execute();
        echo "Nuevo solicitud ingresada" . "<br>";
    }
    // Hacer funcion que valide si el usuario tiene dias disponibles de vacaciones antes de que el admin las ingrese.
    
    
    public function ValidarVacaciones($id_usuario, $FechaInicio, $FechaFin, $fecha_ingreso, $DiasTomados)
    {
        $function_conn = $this->conn;
        // Se obtiene la cantidad de dias que el usuario ha tomado de vacaciones
        $stmt = $function_conn->prepare(
            "SELECT SUM(HV.DiasTomados) as DiasTomados
            FROM historial_vacaciones HV 
            WHERE HV.id_usuario = ?");
        $stmt->bind_param(
            "i",
            $id_usuario
        );
        // Se ejecuta el comando
        $stmt->execute();
        $stmt->bind_result($DiasTomadosTomados);
        $stmt->fetch();
        $stmt->close();

        // Se calcula la cantidad de dias de vacaciones acumulados desde la fecha de ingreso
        $fecha_actual = new DateTime();
        $fecha_ingreso = new DateTime($fecha_ingreso);
        $interval = $fecha_ingreso->diff($fecha_actual);
        // La logica del siguiente funciona que cada mes trabajado por empleado, cuenta como un dia de vacacion
        $meses_trabajados = ($interval->y * 12) + $interval->m;
        $dias_acumulados = $meses_trabajados;

        // Se calcula la cantidad de dias solicitados
        $fecha_inicio = new DateTime($FechaInicio);
        $fecha_fin = new DateTime($FechaFin);
        // Se suma 1 porque si se toma vacaciones del 1 al 1, se cuenta como 1 dia 
        $dias_solicitados = $fecha_fin->diff($fecha_inicio)->days + 1;

        // Se verifica si el empleado tiene suficientes dias de vacaciones disponibles
        $dias_disponibles = $dias_acumulados - $DiasTomadosTomados;
        if ($dias_disponibles >= $dias_solicitados) {
            echo "El empleado tiene suficientes días de vacaciones disponibles";
        } else {
            echo "El empleado no tiene suficientes días de vacaciones disponibles";
        }
    }

}

?>