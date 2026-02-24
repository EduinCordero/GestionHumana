<?php
namespace app\models;

use app\models\mainModel;

class reportModel extends mainModel {

    // --- DICCIONARIOS DE COMPETENCIAS (Reglas de Negocio) ---
    public static function getDictColaborador() {
        return [
            1 => 'Calidez Humana y Servicio con Propósito',
            2 => 'Liderazgo e Integridad en la Acción',
            3 => 'Competitividad, Innovación y Adaptabilidad',
            4 => 'Comunicación Asertiva y Sentido de Pertenencia',
            5 => 'Compromiso con la calidad',
            6 => 'Compromiso institucional y cumplimiento de normas internas',
            7 => 'Participación y formación continua',
            8 => 'Gestión de Seguridad y Salud en el Trabajo (SST)',
            9 => 'Gestión de Relaciones Interpersonales',
            10 => 'Eficacia en la Ejecución de Responsabilidades y Contribución a los Resultados',
            11 => 'Gestión Eficiente del Tiempo y los Recursos'
        ];
    }

public static function getDictLiderazgo() {
    return [
        1 => 'Propósito',
        2 => 'Colaboración',
        3 => 'Consistencia',
        4 => 'Adaptabilidad',
        5 => 'Amor'
    ];
}

    /** Devuelve el id de cargo para el empleado */
    public function getCargoId($idempleado) {
        $sql = "SELECT IDEMPCARGO FROM ZAYMAWEB.VST_GHEMPEMPLEADOSCARGOS WHERE IDEMPLEADO = " . (int)$idempleado;
        $res = $this->ejecutarConsulta($sql);
        $row = oci_fetch_assoc($res);
        return $row ? $row['IDEMPCARGO'] : null;
    }

    public function getAutoEvaluacion($idempleado) {
        $sql = "SELECT IDAUTOEVALUACION, FECHAREALIZA, PREGUNTA1, PREGUNTA2, PREGUNTA3, PREGUNTA4, PREGUNTA5, PREGUNTA6, PREGUNTA7, PREGUNTA8, PREGUNTA9, PREGUNTA10, PREGUNTA11, CONFIRMADO
                FROM VAADINWEB.HUMAUTOEVALUACION
                WHERE EMPLEADO_ID = " . (int)$idempleado . " ORDER BY FECHAREALIZA DESC";
        $res = $this->ejecutarConsulta($sql);
        return oci_fetch_assoc($res) ?: null;
    }

       public function getEvaluacionesRecibidasColaborador($idempleado) {
        // Agregamos las 11 preguntas a la consulta
        $sql = "SELECT d.IDEVACOLABORADOR, d.CONFIRMADO, d.FECHACONFIRMA, 
                    d.PREGUNTA1, d.PREGUNTA2, d.PREGUNTA3, d.PREGUNTA4, d.PREGUNTA5, 
                    d.PREGUNTA6, d.PREGUNTA7, d.PREGUNTA8, d.PREGUNTA9, d.PREGUNTA10, d.PREGUNTA11,
                    NVL(em.EMPLEADO,'Anónimo') AS EVALUADOR
                FROM VAADINWEB.HUMEVALUACIONCOLABO d
                LEFT JOIN ZAYMAWEB.VST_GHEMPEMPLEADOSCARGOS em ON d.EMPLEADO_CONFIRMA = em.IDEMPLEADO
                WHERE d.EMPLEADO_ID = " . (int)$idempleado . " 
                AND em.ESPRINCIPAL = 1 
                ORDER BY d.IDEVACOLABORADOR DESC";
        
        $res = $this->ejecutarConsulta($sql);
        $rows = [];
        while ($r = oci_fetch_assoc($res)) { $rows[] = $r; }
        return $rows;
    }

    /*public function getEvaluacionesRecibidasLiderazgo($idempleado) {
        $sql = "SELECT hl.IDAUTOLIDER, hl.CONFIRMADO, hl.FECHACONFIRMA, NVL(em.EMPLEADO,'Anónimo') AS EVALUADOR, NVL(em.CARGO,'N/A') AS CARGO_EVALUADOR
                FROM VAADINWEB.HUMAUTOEVALUACIONLIDER hl
                LEFT JOIN ZAYMAWEB.VST_GHEMPEMPLEMPLEADOSCARGOS em ON hl.EMPLEADO_CONFIRMA = em.IDEMPLEADO
                WHERE hl.EMPLEADO_ID = " . (int)$idempleado . " ORDER BY hl.FECHACONFIRMA DESC";
        // NOTE: the LEFT JOIN alias in original code is ZAYMAWEB.VST_GHEMPEMPLEADOSCARGOS em, keep consistent
        $sql = "SELECT hl.IDAUTOLIDER, hl.CONFIRMADO, hl.FECHACONFIRMA, NVL(em.EMPLEADO,'Anónimo') AS EVALUADOR, NVL(em.CARGO,'N/A') AS CARGO_EVALUADOR
                FROM VAADINWEB.HUMAUTOEVALUACIONLIDER hl
                LEFT JOIN ZAYMAWEB.VST_GHEMPEMPLEADOSCARGOS em ON hl.EMPLEADO_CONFIRMA = em.IDEMPLEADO
                WHERE hl.EMPLEADO_ID = " . (int)$idempleado . " ORDER BY hl.FECHACONFIRMA DESC";
        $res = $this->ejecutarConsulta($sql);
        $rows = [];
        while ($r = oci_fetch_assoc($res)) { $rows[] = $r; }
        return $rows;
    }*/

    public function getEvaluacionesRecibidasLiderazgo($idempleado)
{
    $sql = "
        SELECT
            hl.IDAUTOLIDER, hl.CONFIRMADO, hl.FECHACONFIRMA,
            /* Nombre del evaluador */
            CASE
                WHEN emc.IDEMPLEADO IS NOT NULL THEN emc.EMPLEADO
                WHEN em.IDEMPLEADO IS NOT NULL  THEN em.EMPLEADO
                ELSE 'EMPLEADO DESCONOCIDO'
            END AS EVALUADOR,
            /* Cargo del evaluador */
            CASE
                WHEN emc.IDEMPLEADO IS NOT NULL THEN emc.CARGO
                WHEN em.IDEMPLEADO IS NOT NULL  THEN 'EMPLEADO (INACTIVO)'
                ELSE 'N/A'
            END AS CARGO_EVALUADOR
        FROM VAADINWEB.HUMAUTOEVALUACIONLIDER hl
        LEFT JOIN ZAYMAWEB.VST_GHEMPEMPLEADOSCARGOS emc ON hl.EMPLEADO_CONFIRMA = emc.IDEMPLEADO
        LEFT JOIN ZAYMAWEB.VST_GHEMPEMPLEADOS em ON hl.EMPLEADO_CONFIRMA = em.IDEMPLEADO
        WHERE hl.EMPLEADO_ID = " . (int)$idempleado . "
        ORDER BY hl.FECHACONFIRMA DESC
    ";

    $res = $this->ejecutarConsulta($sql);

    $rows = [];
    while ($r = oci_fetch_assoc($res)) {
        $rows[] = $r;
    }

    return $rows;
}


    public function getEvaluacionesRealizadasColaborador($idempleado) {
        $sql = "SELECT d.IDEVACOLABORADOR, d.PREGUNTA1, d.PREGUNTA2, d.PREGUNTA3, d.PREGUNTA4, d.PREGUNTA5, d.PREGUNTA6, d.PREGUNTA7, d.PREGUNTA8, d.PREGUNTA9, d.PREGUNTA10, d.PREGUNTA11, em.EMPLEADO AS EVALUADO, em.CARGO AS CARGO_EVALUADO, d.CONFIRMADO, d.FECHACONFIRMA
                FROM VAADINWEB.HUMEVALUACIONCOLABO d
                INNER JOIN ZAYMAWEB.VST_GHEMPEMPLEADOSCARGOS em ON d.EMPLEADO_ID = em.IDEMPLEADO
                WHERE d.EMPLEADO_CONFIRMA = " . (int)$idempleado . " ORDER BY d.IDEVACOLABORADOR DESC";
        $res = $this->ejecutarConsulta($sql);
        $rows = [];
        while ($r = oci_fetch_assoc($res)) { $rows[] = $r; }
        return $rows;
    }

    public function getEvaluacionesRealizadasLiderazgo($idempleado) {
        $sql = "SELECT hl.IDAUTOLIDER, hl.PREGUNTA1, hl.PREGUNTA2, hl.PREGUNTA3, hl.PREGUNTA4, hl.PREGUNTA5, hl.PREGUNTA6, hl.PREGUNTA7, hl.PREGUNTA8, em.EMPLEADO AS EVALUADO, em.CARGO AS CARGO_EVALUADO, hl.CONFIRMADO, hl.FECHACONFIRMA
                FROM VAADINWEB.HUMAUTOEVALUACIONLIDER hl
                INNER JOIN ZAYMAWEB.VST_GHEMPEMPLEADOSCARGOS em ON hl.EMPLEADO_ID = em.IDEMPLEADO
                WHERE hl.EMPLEADO_CONFIRMA = " . (int)$idempleado . " ORDER BY hl.FECHACONFIRMA DESC";
        $res = $this->ejecutarConsulta($sql);
        $rows = [];
        while ($r = oci_fetch_assoc($res)) { $rows[] = $r; }
        return $rows;
    }

    public function getLatestAutoAndLider($idempleado) {
        $resultado = [
            'auto' => null,
            'lider' => null
        ];

        $sqlAuto = "SELECT * FROM (SELECT PREGUNTA1, PREGUNTA2, PREGUNTA3, PREGUNTA4, PREGUNTA5, PREGUNTA6, PREGUNTA7, PREGUNTA8, PREGUNTA9, PREGUNTA10, PREGUNTA11, FECHAREALIZA FROM VAADINWEB.HUMAUTOEVALUACION WHERE EMPLEADO_ID = " . (int)$idempleado . " ORDER BY FECHAREALIZA DESC) WHERE ROWNUM = 1";
        $resA = $this->ejecutarConsulta($sqlAuto);
        $resultado['auto'] = oci_fetch_assoc($resA) ?: null;

        $sqlLider = "SELECT * FROM (SELECT PREGUNTA1, PREGUNTA2, PREGUNTA3, PREGUNTA4, PREGUNTA5, PREGUNTA6, PREGUNTA7, PREGUNTA8, FECHACONFIRMA FROM VAADINWEB.HUMAUTOEVALUACIONLIDER WHERE EMPLEADO_ID = " . (int)$idempleado . " ORDER BY FECHACONFIRMA DESC) WHERE ROWNUM = 1";
        $resL = $this->ejecutarConsulta($sqlLider);
        $resultado['lider'] = oci_fetch_assoc($resL) ?: null;

        return $resultado;
    }

    public function getIndicadoresDesempeno($idempleado) {
        $sql = "SELECT PREGUNTA1, PREGUNTA2, PREGUNTA3, PREGUNTA4, PREGUNTA5, PREGUNTA6, PREGUNTA7, PREGUNTA8, PREGUNTA9, PREGUNTA10, PREGUNTA11 FROM VAADINWEB.HUMEVALUACIONCOLABO WHERE EMPLEADO_ID = " . (int)$idempleado;
        $res = $this->ejecutarConsulta($sql);
        $rows = [];
        while ($r = oci_fetch_assoc($res)) { $rows[] = $r; }
        return $rows;
    }

    public function getIndicadoresLiderazgo($idempleado) {
        $sql = "SELECT PREGUNTA1, PREGUNTA2, PREGUNTA3, PREGUNTA4, PREGUNTA5, PREGUNTA6, PREGUNTA7, PREGUNTA8 FROM VAADINWEB.HUMAUTOEVALUACIONLIDER WHERE EMPLEADO_ID = " . (int)$idempleado;
        $res = $this->ejecutarConsulta($sql);
        $rows = [];
        while ($r = oci_fetch_assoc($res)) { $rows[] = $r; }
        return $rows;
    }
/*
    public function obtenerEvaluadores($idempleado) {
    $idEmpleadoBusca = (int)$idempleado;
    // Mantenemos tu lógica de UNION
    $sql = "SELECT * FROM ( 
                SELECT C.EMPLEADO_CONFIRMA AS ID_EVALUADOR, C.FECHACONFIRMA 
                FROM VAADINWEB.HUMEVALUACIONCOLABO C 
                WHERE C.EMPLEADO_ID = $idEmpleadoBusca 
                UNION ALL 
                SELECT L.EMPLEADO_CONFIRMA AS ID_EVALUADOR, L.FECHACONFIRMA 
                FROM VAADINWEB.HUMAUTOEVALUACIONLIDER L 
                WHERE L.EMPLEADO_ID = $idEmpleadoBusca 
                ORDER BY FECHACONFIRMA DESC 
            ) T WHERE ROWNUM <= 100"; // Bajamos a 100 para que el histórico no sea gigante

    $res = $this->ejecutarConsulta($sql);
    $evaluadorIds = [];
    $evaluadores = [];

        if ($res !== false) {
            while ($eval = oci_fetch_assoc($res)) { 
                $evaluadorIds[$eval['ID_EVALUADOR']] = $eval; 
            }
            oci_free_statement($res);

            if (!empty($evaluadorIds)) {
                $idsParaIn = implode(',', array_keys($evaluadorIds));
                $sqlEmpleados = "SELECT IDEMPLEADO, EMPLEADO, CARGO FROM ZAYMAWEB.VST_GHEMPEMPLEADOSCARGOS WHERE IDEMPLEADO IN ($idsParaIn)";
                $resEm = $this->ejecutarConsulta($sqlEmpleados);

                if ($resEm !== false) {
                    $datosEm = [];
                    while ($emp = oci_fetch_assoc($resEm)) { 
                        // Convertimos a UTF-8 para evitar errores de visualización
                        $emp['EMPLEADO'] = mb_convert_encoding($emp['EMPLEADO'], 'UTF-8', 'ISO-8859-1');
                        $emp['CARGO'] = mb_convert_encoding($emp['CARGO'], 'UTF-8', 'ISO-8859-1');
                        $datosEm[$emp['IDEMPLEADO']] = $emp; 
                    }
                    
                    foreach ($evaluadorIds as $id => $data) {
                        if (isset($datosEm[$id])) {
                            $evaluadores[] = array_merge($datosEm[$id], ['FECHACONFIRMA' => $data['FECHACONFIRMA']]);
                        }
                    }
                    oci_free_statement($resEm);
                }
            }
        }
        return $evaluadores;
    }
*/

public function obtenerEvaluadores($idempleado)
{
    $idEmpleadoBusca = (int)$idempleado;

    // 1 IDs de evaluadores (obtenidos de ambas tablas)
    $sql = "
        SELECT * FROM (
            SELECT C.EMPLEADO_CONFIRMA AS ID_EVALUADOR, C.FECHACONFIRMA
            FROM VAADINWEB.HUMEVALUACIONCOLABO C
            WHERE C.EMPLEADO_ID = $idEmpleadoBusca

            UNION ALL

            SELECT L.EMPLEADO_CONFIRMA AS ID_EVALUADOR, L.FECHACONFIRMA
            FROM VAADINWEB.HUMAUTOEVALUACIONLIDER L
            WHERE L.EMPLEADO_ID = $idEmpleadoBusca

            ORDER BY FECHACONFIRMA DESC
        )
        WHERE ROWNUM <= 100
    ";

    $res = $this->ejecutarConsulta($sql);

    $evaluadorIds = [];
    $evaluadores   = [];

    if ($res !== false) {
        while ($eval = oci_fetch_assoc($res)) {
            $evaluadorIds[$eval['ID_EVALUADOR']] = $eval;
        }
        oci_free_statement($res);
    }

    if (empty($evaluadorIds)) {
        return [];
    }

    $idsParaIn = implode(',', array_keys($evaluadorIds));

    // 2 Empleados ACTIVOS (nombre + cargo)
    $sqlActivos = "
        SELECT IDEMPLEADO, EMPLEADO, CARGO
        FROM ZAYMAWEB.VST_GHEMPEMPLEADOSCARGOS
        WHERE IDEMPLEADO IN ($idsParaIn)
    ";

    $resActivos = $this->ejecutarConsulta($sqlActivos);
    $empleadosActivos = [];

    if ($resActivos !== false) {
        while ($emp = oci_fetch_assoc($resActivos)) {
            $emp['EMPLEADO'] = mb_convert_encoding($emp['EMPLEADO'], 'UTF-8', 'ISO-8859-1');
            $emp['CARGO']    = mb_convert_encoding($emp['CARGO'], 'UTF-8', 'ISO-8859-1');
            $empleadosActivos[$emp['IDEMPLEADO']] = $emp;
        }
        oci_free_statement($resActivos);
    }

    // 3 Empleados INACTIVOS (solo nombre)
    $sqlTodos = "
        SELECT IDEMPLEADO, EMPLEADO
        FROM ZAYMAWEB.VST_GHEMPEMPLEADOS
        WHERE IDEMPLEADO IN ($idsParaIn)
    ";

    $resTodos = $this->ejecutarConsulta($sqlTodos);
    $empleadosTodos = [];

    if ($resTodos !== false) {
        while ($emp = oci_fetch_assoc($resTodos)) {
            $emp['EMPLEADO'] = mb_convert_encoding($emp['EMPLEADO'], 'UTF-8', 'ISO-8859-1');
            $empleadosTodos[$emp['IDEMPLEADO']] = $emp;
        }
        oci_free_statement($resTodos);
    }

    // 4 Construcción final (regla de negocio)
    foreach ($evaluadorIds as $id => $data) {

        if (isset($empleadosActivos[$id])) {
            // Activo
            $evaluadores[] = [
                'IDEMPLEADO'   => $id,
                'EMPLEADO'     => $empleadosActivos[$id]['EMPLEADO'],
                'CARGO'        => $empleadosActivos[$id]['CARGO'],
                'FECHACONFIRMA'=> $data['FECHACONFIRMA']
            ];

        } elseif (isset($empleadosTodos[$id])) {
            // Inactivo
            $evaluadores[] = [
                'IDEMPLEADO'   => $id,
                'EMPLEADO'     => $empleadosTodos[$id]['EMPLEADO'],
                'CARGO'        => 'EMPLEADO (INACTIVO)',
                'FECHACONFIRMA'=> $data['FECHACONFIRMA']
            ];
        }
    }

    return $evaluadores;
}

    public function searchEmployees($term, $limit = 10) {
        $searchTermUpper = strtoupper($term);
        $sql = "SELECT * FROM ( SELECT e.IDEMPLEADO, e.EMPLEADO, e.IDENTIFICACION, e.CARGO FROM ZAYMAWEB.VST_GHEMPEMPLEADOSCARGOS e WHERE UPPER(e.EMPLEADO) LIKE '%$searchTermUpper%' OR e.IDENTIFICACION LIKE '%$searchTermUpper%' ORDER BY e.EMPLEADO ASC ) WHERE ROWNUM <= " . (int)$limit;
        $res = $this->ejecutarConsulta($sql);
        $rows = [];
        while ($r = oci_fetch_assoc($res)) { $rows[] = $r; }
        return $rows;
    }

    public function getEmployeeById($id) {
        $sql = "SELECT EMPLEADO, CARGO, IDENTIFICACION FROM ZAYMAWEB.VST_GHEMPEMPLEADOSCARGOS WHERE IDEMPLEADO = " . (int)$id;
        $res = $this->ejecutarConsulta($sql);
        return oci_fetch_assoc($res) ?: null;
    }
  
    public function buscarPersonalEquipo($term) {
    $term = strtoupper(trim($term));
    // Consulta para buscar en la vista de empleados
    $sql = "SELECT * FROM (
                SELECT IDEMPLEADO, EMPLEADO, IDENTIFICACION, CARGO 
                FROM ZAYMAWEB.VST_GHEMPEMPLEADOSCARGOS 
                WHERE (UPPER(EMPLEADO) LIKE '%$term%' OR IDENTIFICACION LIKE '%$term%')
                AND ESTADOEMPLEADO = 1 AND CARGO IS NOT NULL
                ORDER BY EMPLEADO ASC
            ) WHERE ROWNUM <= 10";
    
    $res = $this->ejecutarConsulta($sql);
    $resultados = [];
    if($res) {
        while($row = oci_fetch_assoc($res)) {
            $resultados[] = $row;
        }
        oci_free_statement($res);
    }
    return $resultados;
}
}
?>

