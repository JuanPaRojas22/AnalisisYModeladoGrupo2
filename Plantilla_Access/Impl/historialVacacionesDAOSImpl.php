<?php
require_once __DIR__ . '/../Interfaces/historial_vacacionesDAO.php';
require_once __DIR__ . '/../Models/historial_vacaciones.php';

class HistorialVacacionesDAOSImpl implements historial_vacacionesDAO
{
    private $conn;

    public function __construct()
    {
        $this->conn = new mysqli("localhost", "root", "", "GestionEmpleados");

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
    // Para hacer este calculo, cada mes desde que empezo a trabajar el empleado cuenta como un dia de vacacion y se acumula hasta el mes actual
    // Con estos dias sumados, se resta la cantidad de dias que ya ha tomado de vacaciones y se compara con los dias que se quieren tomar
    
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

    // Funcion que devuelve el historial de vacaciones de un empleado en especifico
    public function getHistorialVacaciones($id_usuario){
        $sql = "SELECT id_historial FROM historial_vacaciones WHERE id_usuario = ?";

        // Prepara la consulta
        $stmt = $this->conn->prepare($sql);

        // Enlaza el parámetro (i = entero)
        $stmt->bind_param("i", $id_usuario);

        // Ejecuta la consulta
        $stmt->execute();

        // Obtiene el resultado
        $result = $stmt->get_result();

        // Obtiene el id_historial
        $id_historial = null;
        if ($row = $result->fetch_assoc()) {
            $id_historial = $row['id_historial'];
        }
            
        // Devuelve el id_historial
        return $id_historial;

    }

    // Funcion para mostrar la cantidad de dias restanes de vacaciones de un empleado
    public function getDiasRestantes($id_usuario){
        $sql = "SELECT DiasRestantes FROM historial_vacaciones WHERE id_usuario = ?";

        // Prepara la consulta
        $stmt = $this->conn->prepare($sql);

        // Enlaza el parámetro (i = entero)
        $stmt->bind_param("i", $id_usuario);

        // Ejecuta la consulta
        $stmt->execute();

        // Obtiene el resultado
        $result = $stmt->get_result();

        // Obtiene los dias tomados
        $dias_tomados = null;
        if ($row = $result->fetch_assoc()) {
            $dias_tomados = $row['DiasRestantes'];
        }
            
        // Devuelve los dias tomados
        return $dias_tomados;
    }

}


?>