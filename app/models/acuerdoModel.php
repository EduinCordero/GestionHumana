<?php
namespace app\models;
use app\models\mainModel;

class acuerdoModel extends mainModel {

    // ══════════════════════════════════════════════════════════════════
    // OBJETIVOS PREDETERMINADOS (gestión por admin/director)
    // ══════════════════════════════════════════════════════════════════

    /** Devuelve todos los objetivos activos agrupados por competencia y calificación */
    public function getObjetivos($soloActivos = true) {
        $filtro = $soloActivos ? "WHERE ACTIVO = 1" : "";
        $sql = "SELECT IDOBJETIVO, NUM_COMPETENCIA, CALIFICACION, OBJETIVO, ACTIVO, FECHACREACION
                FROM VAADINWEB.HUMOBJETIVOMEJORA
                $filtro
                ORDER BY NUM_COMPETENCIA ASC, CALIFICACION ASC, IDOBJETIVO ASC";
        $res  = $this->ejecutarConsulta($sql);
        $rows = [];
        if ($res) {
            while ($r = oci_fetch_assoc($res)) {
                $r['OBJETIVO'] = fromOracleEncoding($r['OBJETIVO'] ?? '');
                $rows[] = $r;
            }
            oci_free_statement($res);
        }
        return $rows;
    }

    /** Devuelve objetivos para una competencia y calificación específica */
    public function getObjetivosPorCompetenciaCalificacion($numComp, $calificacion) {
        $numComp     = (int)$numComp;
        $calificacion = (int)$calificacion;
        $sql = "SELECT IDOBJETIVO, OBJETIVO
                FROM VAADINWEB.HUMOBJETIVOMEJORA
                WHERE NUM_COMPETENCIA = $numComp
                AND CALIFICACION = $calificacion
                AND ACTIVO = 1
                ORDER BY IDOBJETIVO ASC";
        $res  = $this->ejecutarConsulta($sql);
        $rows = [];
        if ($res) {
            while ($r = oci_fetch_assoc($res)) {
                $r['OBJETIVO'] = fromOracleEncoding($r['OBJETIVO'] ?? '');
                $rows[] = $r;
            }
            oci_free_statement($res);
        }
        return $rows;
    }

    /** Crea un nuevo objetivo predeterminado */
    public function crearObjetivo($numComp, $calificacion, $objetivo, $idUsuario) {
        $numComp     = (int)$numComp;
        $calificacion = (int)$calificacion;
        $sql = "INSERT INTO VAADINWEB.HUMOBJETIVOMEJORA
                    (IDOBJETIVO, NUM_COMPETENCIA, CALIFICACION, OBJETIVO, ACTIVO, FECHACREACION, IDUSUARIO_CREA)
                VALUES
                    (VAADINWEB.SEQ_HUMOBJETIVOMEJORA.NEXTVAL, :comp, :cal, :obj, 1, SYSDATE, :usr)";
        $conn  = $this->conectar();
        $query = oci_parse($conn, $sql);
        oci_bind_by_name($query, ':comp', $numComp);
        oci_bind_by_name($query, ':cal',  $calificacion);
        oci_bind_by_name($query, ':obj',  $objetivo);
        oci_bind_by_name($query, ':usr',  $idUsuario);
        $ok = oci_execute($query, OCI_COMMIT_ON_SUCCESS);
        if (!$ok) error_log('crearObjetivo error: ' . print_r(oci_error($query), true));
        oci_free_statement($query);
        return $ok;
    }

    /** Desactiva un objetivo (no elimina) */
    public function desactivarObjetivo($idObjetivo) {
        $idObjetivo = (int)$idObjetivo;
        $sql   = "UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET ACTIVO = 0 WHERE IDOBJETIVO = :id";
        $conn  = $this->conectar();
        $query = oci_parse($conn, $sql);
        oci_bind_by_name($query, ':id', $idObjetivo);
        $ok = oci_execute($query, OCI_COMMIT_ON_SUCCESS);
        oci_free_statement($query);
        return $ok;
    }

    /** Reactiva un objetivo */
    public function activarObjetivo($idObjetivo) {
        $idObjetivo = (int)$idObjetivo;
        $sql   = "UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET ACTIVO = 1 WHERE IDOBJETIVO = :id";
        $conn  = $this->conectar();
        $query = oci_parse($conn, $sql);
        oci_bind_by_name($query, ':id', $idObjetivo);
        $ok = oci_execute($query, OCI_COMMIT_ON_SUCCESS);
        oci_free_statement($query);
        return $ok;
    }

    // ══════════════════════════════════════════════════════════════════
    // ACUERDOS DE MEJORA (asignación por el líder)
    // ══════════════════════════════════════════════════════════════════

    /**
     * Devuelve las competencias con calificación 1, 2 o 3 del colaborador
     * evaluado por su líder en el período activo
     */
    public function getCompetenciasConMejora($idEmpleado, $idPeriodo) {
        $idEmpleado = (int)$idEmpleado;
        $idPeriodo  = (int)$idPeriodo;

        // Escala de calificaciones
        $sql = "
            SELECT * FROM (
                SELECT
                    CASE C.PREGUNTA1  WHEN 'Insuficiente' THEN 1 WHEN 'Necesita Mejorar' THEN 2 WHEN 'Aceptable' THEN 3
                                      WHEN 'Acorde' THEN 4 WHEN 'Sobresaliente' THEN 5 END AS CAL1,
                    CASE C.PREGUNTA2  WHEN 'Insuficiente' THEN 1 WHEN 'Necesita Mejorar' THEN 2 WHEN 'Aceptable' THEN 3
                                      WHEN 'Acorde' THEN 4 WHEN 'Sobresaliente' THEN 5 END AS CAL2,
                    CASE C.PREGUNTA3  WHEN 'Insuficiente' THEN 1 WHEN 'Necesita Mejorar' THEN 2 WHEN 'Aceptable' THEN 3
                                      WHEN 'Acorde' THEN 4 WHEN 'Sobresaliente' THEN 5 END AS CAL3,
                    CASE C.PREGUNTA4  WHEN 'Insuficiente' THEN 1 WHEN 'Necesita Mejorar' THEN 2 WHEN 'Aceptable' THEN 3
                                      WHEN 'Acorde' THEN 4 WHEN 'Sobresaliente' THEN 5 END AS CAL4,
                    CASE C.PREGUNTA5  WHEN 'Insuficiente' THEN 1 WHEN 'Necesita Mejorar' THEN 2 WHEN 'Aceptable' THEN 3
                                      WHEN 'Acorde' THEN 4 WHEN 'Sobresaliente' THEN 5 END AS CAL5,
                    CASE C.PREGUNTA6  WHEN 'Insuficiente' THEN 1 WHEN 'Necesita Mejorar' THEN 2 WHEN 'Aceptable' THEN 3
                                      WHEN 'Acorde' THEN 4 WHEN 'Sobresaliente' THEN 5 END AS CAL6,
                    CASE C.PREGUNTA7  WHEN 'Insuficiente' THEN 1 WHEN 'Necesita Mejorar' THEN 2 WHEN 'Aceptable' THEN 3
                                      WHEN 'Acorde' THEN 4 WHEN 'Sobresaliente' THEN 5 END AS CAL7,
                    CASE C.PREGUNTA8  WHEN 'Insuficiente' THEN 1 WHEN 'Necesita Mejorar' THEN 2 WHEN 'Aceptable' THEN 3
                                      WHEN 'Acorde' THEN 4 WHEN 'Sobresaliente' THEN 5 END AS CAL8,
                    CASE C.PREGUNTA9  WHEN 'Insuficiente' THEN 1 WHEN 'Necesita Mejorar' THEN 2 WHEN 'Aceptable' THEN 3
                                      WHEN 'Acorde' THEN 4 WHEN 'Sobresaliente' THEN 5 END AS CAL9,
                    CASE C.PREGUNTA10 WHEN 'Insuficiente' THEN 1 WHEN 'Necesita Mejorar' THEN 2 WHEN 'Aceptable' THEN 3
                                      WHEN 'Acorde' THEN 4 WHEN 'Sobresaliente' THEN 5 END AS CAL10,
                    CASE C.PREGUNTA11 WHEN 'Insuficiente' THEN 1 WHEN 'Necesita Mejorar' THEN 2 WHEN 'Aceptable' THEN 3
                                      WHEN 'Acorde' THEN 4 WHEN 'Sobresaliente' THEN 5 END AS CAL11,
                    C.PREGUNTA1, C.PREGUNTA2, C.PREGUNTA3, C.PREGUNTA4, C.PREGUNTA5,
                    C.PREGUNTA6, C.PREGUNTA7, C.PREGUNTA8, C.PREGUNTA9, C.PREGUNTA10, C.PREGUNTA11
                FROM VAADINWEB.HUMEVALUACIONCOLABO C
                INNER JOIN VAADINWEB.HUMPERIODOEVALUACION P
                    ON TRUNC(C.FECHACONFIRMA) BETWEEN P.FECHAAPERTURA AND P.FECHACIERRE
                    AND P.IDPERIODO = $idPeriodo
                WHERE C.EMPLEADO_ID = $idEmpleado
                  AND C.CONFIRMADO = 1
                ORDER BY C.FECHACONFIRMA DESC
            ) WHERE ROWNUM = 1";

        $res = $this->ejecutarConsulta($sql);
        $row = $res ? oci_fetch_assoc($res) : null;
        if ($res) oci_free_statement($res);

        if (!$row) return [];

        // Filtrar solo las que tienen calificación 1, 2 o 3
        $result = [];
        for ($i = 1; $i <= 11; $i++) {
            $cal = (int)($row["CAL$i"] ?? 0);
            if ($cal >= 1 && $cal <= 3) {
                $result[] = [
                    'NUM_COMPETENCIA' => $i,
                    'CALIFICACION'    => $cal,
                    'TEXTO_CAL'       => $row["PREGUNTA$i"],
                ];
            }
        }
        return $result;
    }

    /** Asigna un objetivo a un colaborador */
    public function asignarAcuerdo($idEmpleado, $idObjetivo, $numComp, $calificacion, $idPeriodo, $idLider) {
        // Verificar que no exista ya ese objetivo asignado en este período
        $idEmpleado  = (int)$idEmpleado;
        $idObjetivo  = (int)$idObjetivo;
        $numComp     = (int)$numComp;
        $calificacion = (int)$calificacion;
        $idPeriodo   = (int)$idPeriodo;
        $idLider     = (int)$idLider;

        $sqlCheck = "SELECT COUNT(*) AS CNT FROM VAADINWEB.HUMACUERDOMEJORA
                     WHERE IDEMPLEADO = $idEmpleado AND IDOBJETIVO = $idObjetivo AND IDPERIODO = $idPeriodo";
        $resCheck = $this->ejecutarConsulta($sqlCheck);
        $rowCheck = $resCheck ? oci_fetch_assoc($resCheck) : null;
        if ($rowCheck && (int)$rowCheck['CNT'] > 0) return true; // ya existe, no duplicar

        $sql = "INSERT INTO VAADINWEB.HUMACUERDOMEJORA
                    (IDACUERDO, IDEMPLEADO, IDOBJETIVO, NUM_COMPETENCIA, CALIFICACION,
                     IDPERIODO, IDEMPLEADO_LIDER, ESTADO, FECHA_ASIGNACION)
                VALUES
                    (VAADINWEB.SEQ_HUMACUERDOMEJORA.NEXTVAL, :emp, :obj, :comp, :cal,
                     :per, :lider, 'PENDIENTE', SYSDATE)";
        $conn  = $this->conectar();
        $query = oci_parse($conn, $sql);
        oci_bind_by_name($query, ':emp',   $idEmpleado);
        oci_bind_by_name($query, ':obj',   $idObjetivo);
        oci_bind_by_name($query, ':comp',  $numComp);
        oci_bind_by_name($query, ':cal',   $calificacion);
        oci_bind_by_name($query, ':per',   $idPeriodo);
        oci_bind_by_name($query, ':lider', $idLider);
        $ok = oci_execute($query, OCI_COMMIT_ON_SUCCESS);
        if (!$ok) error_log('asignarAcuerdo error: ' . print_r(oci_error($query), true));
        oci_free_statement($query);
        return $ok;
    }

    /** Elimina un acuerdo asignado (solo si está pendiente) */
    public function eliminarAcuerdo($idAcuerdo, $idLider) {
        $idAcuerdo = (int)$idAcuerdo;
        $idLider   = (int)$idLider;
        $sql   = "DELETE FROM VAADINWEB.HUMACUERDOMEJORA
                  WHERE IDACUERDO = $idAcuerdo AND IDEMPLEADO_LIDER = $idLider AND ESTADO = 'PENDIENTE'";
        $conn  = $this->conectar();
        $query = oci_parse($conn, $sql);
        $ok    = oci_execute($query, OCI_COMMIT_ON_SUCCESS);
        oci_free_statement($query);
        return $ok;
    }

    /** Devuelve los acuerdos asignados a un colaborador en un período */
    public function getAcuerdosColaborador($idEmpleado, $idPeriodo) {
        $idEmpleado = (int)$idEmpleado;
        $idPeriodo  = (int)$idPeriodo;
        $sql = "
            SELECT
                A.IDACUERDO,
                A.NUM_COMPETENCIA,
                A.CALIFICACION,
                A.ESTADO,
                A.PLAN_ACCION,
                A.FECHA_ASIGNACION,
                A.FECHA_RESPUESTA,
                A.INDICADOR,
                A.META,
                A.PLAZO,
                A.EVIDENCIA,
                A.APOYO_LIDER,
                A.SEGUIMIENTO,
                A.COMPROMISO_AJUSTADO,
                A.COMENTARIO_LIDER,
                O.OBJETIVO,
                NVL(EL.PNOMBRE || ' ' || EL.PAPELLIDO, 'N/A') AS NOMBRE_LIDER,
                TO_CHAR(F.FECHA_FEEDBACK, 'DD/MM/YYYY HH24:MI') AS FECHA_FEEDBACK,
                NVL(F.FIRMADO_COLAB, 0) AS FIRMADO_COLAB
            FROM VAADINWEB.HUMACUERDOMEJORA A
            INNER JOIN VAADINWEB.HUMOBJETIVOMEJORA O ON A.IDOBJETIVO = O.IDOBJETIVO
            LEFT  JOIN ZAYMAWEB.GHEMPEMPLEADOS EL   ON A.IDEMPLEADO_LIDER = EL.IDEMPLEADO
            LEFT  JOIN VAADINWEB.HUMFEEDBACK F
                ON F.IDEMPLEADO       = A.IDEMPLEADO
               AND F.IDEMPLEADO_LIDER = A.IDEMPLEADO_LIDER
               AND F.IDPERIODO        = A.IDPERIODO
               AND F.TIPO_FEEDBACK    = 1
            WHERE A.IDEMPLEADO = $idEmpleado
              AND A.IDPERIODO  = $idPeriodo
              AND A.NUM_COMPETENCIA BETWEEN 1 AND 11
            ORDER BY A.NUM_COMPETENCIA ASC, A.IDACUERDO ASC";
        $campos = ['OBJETIVO','NOMBRE_LIDER','PLAN_ACCION','COMENTARIO_LIDER',
                   'INDICADOR','META','PLAZO','EVIDENCIA','APOYO_LIDER',
                   'SEGUIMIENTO','COMPROMISO_AJUSTADO'];
        $res  = $this->ejecutarConsulta($sql);
        $rows = [];
        if ($res) {
            while ($r = oci_fetch_assoc($res)) {
                foreach ($campos as $c) {
                    if (isset($r[$c])) $r[$c] = fromOracleEncoding($r[$c]);
                }
                $rows[] = $r;
            }
            oci_free_statement($res);
        }
        return $rows;
    }

    /** Devuelve los acuerdos asignados a un colaborador visibles para el líder */
    public function getAcuerdosEquipo($idEmpleado, $idPeriodo) {
        return $this->getAcuerdosColaborador($idEmpleado, $idPeriodo);
    }

    /** El líder aprueba el plan de acción del colaborador */
    public function aprobarPlanAccion($idAcuerdo, $idLider, $comentario) {
        $idAcuerdo = (int)$idAcuerdo;
        $idLider   = (int)$idLider;
        $sql = "UPDATE VAADINWEB.HUMACUERDOMEJORA
                SET ESTADO = 'APROBADO',
                    COMENTARIO_LIDER = :comentario,
                    FECHA_APROBACION = SYSDATE
                WHERE IDACUERDO = :id AND IDEMPLEADO_LIDER = :lider";
        $conn  = $this->conectar();
        $query = oci_parse($conn, $sql);
        oci_bind_by_name($query, ':comentario', $comentario);
        oci_bind_by_name($query, ':id',         $idAcuerdo);
        oci_bind_by_name($query, ':lider',      $idLider);
        $ok = oci_execute($query, OCI_COMMIT_ON_SUCCESS);
        oci_free_statement($query);
        return $ok;
    }



    /** El colaborador guarda su plan de acción */
    public function guardarPlanAccion($idAcuerdo, $idEmpleado, $planAccion) {
        $idAcuerdo  = (int)$idAcuerdo;
        $idEmpleado = (int)$idEmpleado;
        $planAccion = $this->toOracle(trim($planAccion));
        $sql = "UPDATE VAADINWEB.HUMACUERDOMEJORA
                SET PLAN_ACCION = :plan, ESTADO = 'RESPONDIDO', FECHA_RESPUESTA = SYSDATE
                WHERE IDACUERDO = :id AND IDEMPLEADO = :emp";
        $conn  = $this->conectar();
        $query = oci_parse($conn, $sql);
        oci_bind_by_name($query, ':plan', $planAccion);
        oci_bind_by_name($query, ':id',   $idAcuerdo);
        oci_bind_by_name($query, ':emp',  $idEmpleado);
        $ok = oci_execute($query, OCI_COMMIT_ON_SUCCESS);
        oci_free_statement($query);
        return $ok;
    }

    /** Verifica si un colaborador tiene acuerdos pendientes en el período activo */
    public function tienePlanPendiente($idEmpleado, $idPeriodo) {
        $idEmpleado = (int)$idEmpleado;
        $idPeriodo  = (int)$idPeriodo;
        $sql = "SELECT COUNT(*) AS CNT FROM VAADINWEB.HUMACUERDOMEJORA
                WHERE IDEMPLEADO = $idEmpleado AND IDPERIODO = $idPeriodo AND ESTADO = 'PENDIENTE'";
        $res = $this->ejecutarConsulta($sql);
        $row = $res ? oci_fetch_assoc($res) : null;
        if ($res) oci_free_statement($res);
        return $row ? (int)$row['CNT'] > 0 : false;
    }

    /** Traer colaboradores con competencias que requieren mejora */
    public function getColaboradoresConMejora($idLider, $idPeriodo) {
    $idLider   = (int)$idLider;
    $idPeriodo = (int)$idPeriodo;
    $sql = "
        SELECT DISTINCT C.EMPLEADO_ID
        FROM VAADINWEB.HUMEVALUACIONCOLABO C
        INNER JOIN VAADINWEB.HUMPERIODOEVALUACION P
            ON TRUNC(C.FECHACONFIRMA) BETWEEN P.FECHAAPERTURA AND P.FECHACIERRE
            AND P.IDPERIODO = $idPeriodo
        WHERE C.CONFIRMADO = 1
          AND C.EMPLEADO_CONFIRMA = $idLider
          AND (
            CASE C.PREGUNTA1  WHEN 'Insuficiente' THEN 1 WHEN 'Necesita Mejorar' THEN 2
                              WHEN 'Aceptable' THEN 3 ELSE 9 END <= 3
            OR
            CASE C.PREGUNTA2  WHEN 'Insuficiente' THEN 1 WHEN 'Necesita Mejorar' THEN 2
                              WHEN 'Aceptable' THEN 3 ELSE 9 END <= 3
            OR
            CASE C.PREGUNTA3  WHEN 'Insuficiente' THEN 1 WHEN 'Necesita Mejorar' THEN 2
                              WHEN 'Aceptable' THEN 3 ELSE 9 END <= 3
            OR
            CASE C.PREGUNTA4  WHEN 'Insuficiente' THEN 1 WHEN 'Necesita Mejorar' THEN 2
                              WHEN 'Aceptable' THEN 3 ELSE 9 END <= 3
            OR
            CASE C.PREGUNTA5  WHEN 'Insuficiente' THEN 1 WHEN 'Necesita Mejorar' THEN 2
                              WHEN 'Aceptable' THEN 3 ELSE 9 END <= 3
            OR
            CASE C.PREGUNTA6  WHEN 'Insuficiente' THEN 1 WHEN 'Necesita Mejorar' THEN 2
                              WHEN 'Aceptable' THEN 3 ELSE 9 END <= 3
            OR
            CASE C.PREGUNTA7  WHEN 'Insuficiente' THEN 1 WHEN 'Necesita Mejorar' THEN 2
                              WHEN 'Aceptable' THEN 3 ELSE 9 END <= 3
            OR
            CASE C.PREGUNTA8  WHEN 'Insuficiente' THEN 1 WHEN 'Necesita Mejorar' THEN 2
                              WHEN 'Aceptable' THEN 3 ELSE 9 END <= 3
            OR
            CASE C.PREGUNTA9  WHEN 'Insuficiente' THEN 1 WHEN 'Necesita Mejorar' THEN 2
                              WHEN 'Aceptable' THEN 3 ELSE 9 END <= 3
            OR
            CASE C.PREGUNTA10 WHEN 'Insuficiente' THEN 1 WHEN 'Necesita Mejorar' THEN 2
                              WHEN 'Aceptable' THEN 3 ELSE 9 END <= 3
            OR
            CASE C.PREGUNTA11 WHEN 'Insuficiente' THEN 1 WHEN 'Necesita Mejorar' THEN 2
                              WHEN 'Aceptable' THEN 3 ELSE 9 END <= 3
          )";
    $res  = $this->ejecutarConsulta($sql);
    $ids  = [];
    if ($res) {
        while ($r = oci_fetch_assoc($res)) $ids[] = (int)$r['EMPLEADO_ID'];
        oci_free_statement($res);
    }
    return $ids;
}

    /** Devuelve los IDs de objetivos ya asignados a un colaborador en un período */
    public function getObjetivosAsignados($idEmpleado, $idPeriodo) {
        $idEmpleado = (int)$idEmpleado;
        $idPeriodo  = (int)$idPeriodo;
        $sql = "SELECT IDOBJETIVO, NUM_COMPETENCIA
                FROM VAADINWEB.HUMACUERDOMEJORA
                WHERE IDEMPLEADO = $idEmpleado
                AND IDPERIODO = $idPeriodo";
        $res  = $this->ejecutarConsulta($sql);
        $rows = [];
        if ($res) {
            while ($r = oci_fetch_assoc($res)) {
                $rows[(int)$r['NUM_COMPETENCIA']][] = (int)$r['IDOBJETIVO'];
            }
            oci_free_statement($res);
        }
        return $rows; // [numComp => [idObj1, idObj2, ...]]
    }

    /**
     * Devuelve el estado del plan de mejora de cada colaborador del equipo
     * Result: [IDEMPLEADO => ['total'=>N, 'respondidos'=>N, 'pendientes'=>N]]
     */
    public function getEstadoPlanEquipo($idPeriodo, $idsEmpleados, $idLider = null) {
        if (empty($idsEmpleados)) return [];
        $idPeriodo = (int)$idPeriodo;
        $ids = implode(',', array_map('intval', $idsEmpleados));
        $filtroLider = $idLider ? "AND IDEMPLEADO_LIDER = " . (int)$idLider : "";
        $sql = "
            SELECT
                IDEMPLEADO,
                COUNT(*) AS TOTAL,
                SUM(CASE WHEN ESTADO = 'RESPONDIDO' THEN 1 ELSE 0 END) AS RESPONDIDOS,
                SUM(CASE WHEN ESTADO = 'PENDIENTE'  THEN 1 ELSE 0 END) AS PENDIENTES,
                SUM(CASE WHEN ESTADO = 'APROBADO'   THEN 1 ELSE 0 END) AS APROBADOS
            FROM VAADINWEB.HUMACUERDOMEJORA
            WHERE IDPERIODO  = $idPeriodo
              AND IDEMPLEADO IN ($ids)
              $filtroLider
            GROUP BY IDEMPLEADO";
        $res  = $this->ejecutarConsulta($sql);
        $rows = [];
        if ($res) {
            while ($r = oci_fetch_assoc($res)) {
                $rows[(int)$r['IDEMPLEADO']] = [
                    'total'       => (int)$r['TOTAL'],
                    'respondidos' => (int)$r['RESPONDIDOS'],
                    'pendientes'  => (int)$r['PENDIENTES'],
                    'aprobados'   => (int)$r['APROBADOS'],
                ];
            }
            oci_free_statement($res);
        }
        return $rows;
    }

    /**
     * Devuelve los acuerdos de un colaborador con su plan de acción (para vista del líder)
     */
    public function getAcuerdosParaLider($idEmpleado, $idPeriodo) {
        $idEmpleado = (int)$idEmpleado;
        $idPeriodo  = (int)$idPeriodo;
        $sql = "
            SELECT
                A.IDACUERDO,
                A.NUM_COMPETENCIA,
                A.CALIFICACION,
                A.ESTADO,
                A.PLAN_ACCION,
                A.FECHA_ASIGNACION,
                A.FECHA_RESPUESTA,
                A.COMENTARIO_LIDER,
                A.FECHA_APROBACION,
                O.OBJETIVO
            FROM VAADINWEB.HUMACUERDOMEJORA A
            INNER JOIN VAADINWEB.HUMOBJETIVOMEJORA O ON A.IDOBJETIVO = O.IDOBJETIVO
            WHERE A.IDEMPLEADO = $idEmpleado
              AND A.IDPERIODO  = $idPeriodo
            ORDER BY A.NUM_COMPETENCIA ASC, A.IDACUERDO ASC";
        $res  = $this->ejecutarConsulta($sql);
        $rows = [];
        if ($res) {
            while ($r = oci_fetch_assoc($res)) {
                $r['OBJETIVO']    = fromOracleEncoding($r['OBJETIVO']   ?? '');
                $r['PLAN_ACCION'] = fromOracleEncoding($r['PLAN_ACCION'] ?? '');
                $rows[] = $r;
            }
            oci_free_statement($res);
        }
        return $rows;
    }
}
?>
