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

    // Funcion para obtener el usuario de la vacacion actual
    public function getUserByIdVacacion($id_vacacion){
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
    public function getDetalleVacacion($id_vacacion){
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

    // Funcion que obtiene las vacaciones solicitadas por el usuario actual. Se obtienen todas las vacaciones que esten en estado pendiente
    public function getVacacionesSolicitadas($id_usuario){
        $function_conn = $this->conn;
        // Se obtienen las solicitudes de vacaciones pendientes y que comparten el departamento del administrador
        $stmt = $function_conn->prepare(
            "SELECT V.id_vacacion, V.id_usuario, U.Nombre, U.Apellido, Dep.Nombre AS Departamento, V.fecha_inicio, V.fecha_fin, V.diasTomado, HV.DiasRestantes, EH.descripcion
            FROM vacacion V
            INNER JOIN usuario U ON V.id_usuario = U.id_usuario
            INNER JOIN departamento Dep ON U.id_departamento = Dep.id_departamento
            INNER JOIN estado_vacacion EH ON V.id_estado_vacacion = EH.id_estado_vacacion
            INNER JOIN historial_vacaciones HV ON V.id_usuario = HV.id_usuario
            WHERE (V.id_estado_vacacion = 1 OR V.id_estado_vacacion = 4) AND
            U.id_usuario = ?
            ORDER BY U.Nombre ASC");
        
        // Se ejecuta el comando
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();

        return $stmt->get_result(); // Se retorna el resultado de la consulta
    }

    // Funcion que obtiene las solicitudes pendientes de vacaciones de empleados y que sean del departamento del administrador
    public function getSolicitudesPendientes($id_departamento){
        $function_conn = $this->conn;
        // Se obtienen las solicitudes de vacaciones pendientes y que comparten el departamento del administrador
        $stmt = $function_conn->prepare(
            "SELECT V.id_vacacion, V.id_usuario, U.Nombre, U.Apellido, Dep.Nombre AS Departamento, V.fecha_inicio, V.fecha_fin, V.diasTomado, HV.DiasRestantes, EH.descripcion
            FROM vacacion V
            INNER JOIN usuario U ON V.id_usuario = U.id_usuario
            INNER JOIN departamento Dep ON U.id_departamento = Dep.id_departamento
            INNER JOIN estado_vacacion EH ON V.id_estado_vacacion = EH.id_estado_vacacion
            INNER JOIN historial_vacaciones HV ON V.id_usuario = HV.id_usuario
            WHERE (V.id_estado_vacacion = 1 OR V.id_estado_vacacion = 4) AND
            U.id_departamento = ?
            ORDER BY U.Nombre ASC");
        
        // Se ejecuta el comando
        $stmt->bind_param("i", $id_departamento);
        $stmt->execute();

        return $stmt->get_result(); // Se retorna el resultado de la consulta
    }

    // Funcion para aprobar una solicitud de vacaciones
    public function aprobarSolicitud($id_vacacion){
        $function_conn = $this->conn;
        $stmt = $function_conn->prepare(
            "UPDATE vacacion
            SET id_estado_vacacion = 2 -- Estado 2 es Aprobado
            WHERE id_vacacion = ?");
        $stmt->bind_param(
            "i",
            $id_vacacion
        );
        $stmt->execute();
        echo "Solicitud aprobada" . "<br>";
        $stmt->close();

    }

    // Funcion para rechazar una solicitud de vacaciones
    public function rechazarSolicitud($id_vacacion){
        $function_conn = $this->conn;
        $stmt = $function_conn->prepare(
            "UPDATE vacacion
            SET id_estado_vacacion = 3 -- Estado 3 es Rechazado
            WHERE id_vacacion = ?");
        $stmt->bind_param(
            "i",
            $id_vacacion
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
            return true;
        } else {
            return false;
        }
    }

    // Funcion para comprobar dias feriados y dias habiles
    // SE TIENE QUE DESAROLLAR ESTA FUNCION, AUN NO ESTA LISTA
    public function validaFechasFeriados($fecha_inicio, $fecha_fin){
        $function_conn = $this->conn;
        // Se tiene que obtener todos los dias desde la fecha de inicio hasta la fecha de fin
        $stmt = $function_conn->prepare(
            "SELECT COUNT(*) as DiasFeriados
            FROM dias_feriados DF
            WHERE DF.Fecha BETWEEN ? AND ?");
        $stmt->bind_param(
            "ss",
            $fecha_inicio,
            $fecha_fin
        );
        // Se ejecuta el comando
        $stmt->execute();
        $stmt->bind_result($DiasFeriados);
        $stmt->fetch();
        $stmt->close();

        // Luego se tiene que devolver estos dias para que el usuario pueda elegir otras fechas y no las de feriados
        
    }

    // Funcion que calcula los dias de vacaciones disponibles de un empleado
    public function validarDiasDisponibles($id_usuario, $diasTomados){
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

        // Se verifica si el empleado tiene suficientes dias de vacaciones disponibles
        if ($DiasRestantes >= $diasTomados) {
            return true;
        } else {
            return false;
        }
    }
    

    public function IngresarVacacion($razon, $diasTomado, $FechaInicio, $observaciones, $id_usuario, $id_historial, $fechacreacion, 
    $usuariocreacion, $fechamodificacion, $usuariomodificacion, $id_estado_vacacion, $SolicitudEditar, $fecha_fin)
    {
        $function_conn = $this->conn;
        // Se tiene que comprobar antes de ingresar la vacacion que el empleado tiene suficientes dias de vacaciones disponibles
        
        if (!$this->validarDiasDisponibles($id_usuario, $diasTomado)) {
            echo "<script>alert('El empleado no tiene suficientes días de vacaciones disponibles.');</script>";
            return;
        }
        
        // Se tiene que comprobar que las fechas de inicio y fin no sean feriados o fines de semana
        //  Para eso, se tiene que devolver estos dias para que el usuario pueda elegir otras fechas y no las de feriados
        /*
        if (!$this->validaFechasFeriados($FechaInicio, $fecha_fin)) {
            echo "<script>alert('Las fechas de inicio y fin no pueden ser feriados o fines de semana.');</script>";
            return;
        }
        */
        // Se comprueba que el usuario no haya ingresado una fecha de inicio mayor a la fecha de fin
        if ($FechaInicio > $fecha_fin) {
            echo "La fecha de inicio no puede ser mayor a la fecha de fin";
            return;
        }

        // Se comprueba que los dias totales de las fechas solicitadas no sea mayores o menores a la cantidad de dias solicitados
        $fecha_inicio = new DateTime($FechaInicio);
        $fecha_fin_obj = new DateTime($fecha_fin);
        // Se suma 1 porque si se toma vacaciones del 1 al 1, se cuenta como 1 dia
        $dias_solicitados = $fecha_fin_obj->diff($fecha_inicio)->days + 1;
        if ($dias_solicitados != $diasTomado) {
            echo "La cantidad de dias solicitados no coincide con las fechas ingresadas";
            return;
        }

        // Convertir el objeto DateTime a cadena
        $fecha_fin_str = $fecha_fin_obj->format('Y-m-d');

        // Se ingresa la vacacion por el administrador
        $stmt = $function_conn->prepare(
            "INSERT INTO vacacion (razon, diasTomado, fecha_inicio, observaciones, 
            id_usuario, id_historial, fechacreacion, usuariocreacion, 
            fechamodificacion, usuariomodificacion, id_estado_vacacion, SolicitudEditar, fecha_fin) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        // Se enlazan los parametros
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
            
            $fecha_fin_str
        );

        // Ejecuta la inserción
        $stmt->execute();
        $stmt->close();

        // Se tiene que restar los dias de vacaciones tomados a los dias restantes
        $stmt = $function_conn->prepare(
            "UPDATE historial_vacaciones
            SET DiasRestantes = DiasRestantes - ?
            WHERE id_usuario = ?");

        // Se enlazan los parametros
        $stmt->bind_param("ii", $diasTomado, $id_usuario);

        // Ejecuta la actualización
        $stmt->execute();
        $stmt->close();

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