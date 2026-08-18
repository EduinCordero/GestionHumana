<?php
namespace app\models;
use app\models\mainModel;

class adminModel extends mainModel {

    // ══════════════════════════════════════════════════════════════════
    // PERÍODOS DE EVALUACIÓN
    // ══════════════════════════════════════════════════════════════════

    /** Devuelve el período activo o null si no hay ninguno */
    public function getPeriodoActivo() {
        $sql = "SELECT IDPERIODO, NOMBRE, ESTADO, OBSERVACION,
                       TO_CHAR(FECHAAPERTURA,'DD/MM/YYYY') AS FECHAAPERTURA,
                       TO_CHAR(FECHACIERRE,  'DD/MM/YYYY') AS FECHACIERRE
                FROM VAADINWEB.HUMPERIODOEVALUACION
                WHERE ESTADO = 1
                AND ROWNUM = 1";
        $res = $this->ejecutarConsulta($sql);
        $row = $res ? oci_fetch_assoc($res) : null;
        if ($res) oci_free_statement($res);
        if (is_array($row)) {
            $row['NOMBRE']      = fromOracleEncoding($row['NOMBRE']      ?? '');
            $row['OBSERVACION'] = fromOracleEncoding($row['OBSERVACION'] ?? '');
        }
        return $row;
    }

    /** Devuelve todos los períodos ordenados por fecha de creación */
    public function getPeriodos() {
        $sql = "SELECT IDPERIODO, NOMBRE, ESTADO, OBSERVACION,
                       TO_CHAR(FECHAAPERTURA, 'DD/MM/YYYY') AS FECHAAPERTURA,
                       TO_CHAR(FECHACIERRE,   'DD/MM/YYYY') AS FECHACIERRE,
                       TO_CHAR(FECHACREACION, 'DD/MM/YYYY') AS FECHACREACION
                FROM VAADINWEB.HUMPERIODOEVALUACION
                ORDER BY FECHACREACION DESC";
        $res  = $this->ejecutarConsulta($sql);
        $rows = [];
        if ($res) {
            while ($r = oci_fetch_assoc($res)) {
                $r['NOMBRE']      = fromOracleEncoding($r['NOMBRE']      ?? '');
                $r['OBSERVACION'] = fromOracleEncoding($r['OBSERVACION'] ?? '');
                $rows[] = $r;
            }
            oci_free_statement($res);
        }
        return $rows;
    }

    /** Crea un nuevo período (inactivo por defecto) */
    public function crearPeriodo($nombre, $fechaApertura, $fechaCierre, $observacion, $idUsuario) {
        // Las fechas vienen en formato YYYY-MM-DD desde el input HTML type=date
        // Las convertimos a DD/MM/YYYY para TO_DATE de Oracle
        $fAp = date('d/m/Y', strtotime($fechaApertura));
        $fCi = date('d/m/Y', strtotime($fechaCierre));

        $sql = "INSERT INTO VAADINWEB.HUMPERIODOEVALUACION
                    (IDPERIODO, NOMBRE, FECHAAPERTURA, FECHACIERRE, ESTADO, OBSERVACION, IDUSUARIO_CREA, FECHACREACION)
                VALUES
                    (VAADINWEB.SEQ_HUMPERIODOEVALUACION.NEXTVAL, :nombre,
                     TO_DATE(:apertura, 'DD/MM/YYYY'),
                     TO_DATE(:cierre,   'DD/MM/YYYY'),
                     0, :obs, :idusuario, SYSDATE)";
        $conn  = $this->conectar();
        $query = oci_parse($conn, $sql);
        oci_bind_by_name($query, ':nombre',    $nombre);
        oci_bind_by_name($query, ':apertura',  $fAp);
        oci_bind_by_name($query, ':cierre',    $fCi);
        oci_bind_by_name($query, ':obs',       $observacion);
        oci_bind_by_name($query, ':idusuario', $idUsuario);
        $ok = oci_execute($query, OCI_COMMIT_ON_SUCCESS);
        if (!$ok) error_log('crearPeriodo OCI error: ' . print_r(oci_error($query), true));
        oci_free_statement($query);
        return $ok;
    }

    /** Activa un período (el trigger desactiva automáticamente el anterior) */
    public function activarPeriodo($idPeriodo) {
        $idPeriodo = (int)$idPeriodo;
        $conn = $this->conectar();

        // Paso 1: Desactivar todos los períodos activos manualmente
        // (por si el trigger tiene problemas con la sesión PHP)
        $sqlDeact  = "UPDATE VAADINWEB.HUMPERIODOEVALUACION SET ESTADO = 0 WHERE ESTADO = 1 AND IDPERIODO != :id";
        $qDeact    = oci_parse($conn, $sqlDeact);
        oci_bind_by_name($qDeact, ':id', $idPeriodo);
        oci_execute($qDeact, OCI_NO_AUTO_COMMIT);
        oci_free_statement($qDeact);

        // Paso 2: Activar el período seleccionado
        $sql   = "UPDATE VAADINWEB.HUMPERIODOEVALUACION SET ESTADO = 1 WHERE IDPERIODO = :id";
        $query = oci_parse($conn, $sql);
        oci_bind_by_name($query, ':id', $idPeriodo);
        $ok = oci_execute($query, OCI_NO_AUTO_COMMIT);

        if ($ok) {
            oci_commit($conn);
        } else {
            oci_rollback($conn);
            error_log('activarPeriodo error: ' . print_r(oci_error($query), true));
        }
        oci_free_statement($query);
        return $ok;
    }

    /** Cierra (desactiva) un período */
    /** Editar fecha de cierre del período (ampliar plazo) */
    public function editarCierrePeriodo($idPeriodo, $nuevoCierre) {
        $idPeriodo = (int)$idPeriodo;
        $sql   = "UPDATE VAADINWEB.HUMPERIODOEVALUACION
                  SET FECHACIERRE = TO_DATE(:cierre, 'YYYY-MM-DD')
                  WHERE IDPERIODO = :id";
        $conn  = $this->conectar();
        $query = oci_parse($conn, $sql);
        oci_bind_by_name($query, ':cierre', $nuevoCierre);
        oci_bind_by_name($query, ':id',     $idPeriodo);
        $ok = oci_execute($query, OCI_COMMIT_ON_SUCCESS);
        if (!$ok) error_log('editarCierrePeriodo error: ' . print_r(oci_error($query), true));
        oci_free_statement($query);
        return $ok;
    }


    public function cerrarPeriodo($idPeriodo) {
        $idPeriodo = (int)$idPeriodo;
        $sql   = "UPDATE VAADINWEB.HUMPERIODOEVALUACION SET ESTADO = 0 WHERE IDPERIODO = :id";
        $conn  = $this->conectar();
        $query = oci_parse($conn, $sql);
        oci_bind_by_name($query, ':id', $idPeriodo);
        $ok = oci_execute($query, OCI_COMMIT_ON_SUCCESS);
        oci_free_statement($query);
        return $ok;
    }

    // ══════════════════════════════════════════════════════════════════
    // GESTIÓN DE USUARIOS Y PERMISOS
    // ══════════════════════════════════════════════════════════════════

    /** Busca usuarios por nombre o cédula */
    public function buscarUsuarios($term) {
        $term = strtoupper(trim($term));
        $sql  = "SELECT
                    HU.IDUSUARIO,
                    HU.IDENTIFICACION,
                    HU.ESADMIN,
                    HU.VER_DETALLE_REP,
                    HU.CUENTA_ACTIVA,
                    NVL(EM.PNOMBRE || ' ' || EM.PAPELLIDO, HU.IDENTIFICACION) AS NOMBRE,
                    NVL(CA.CARGO, 'Sin cargo') AS CARGO,
                    NVL(CA.PROCESO, '') AS CODNIVELCARGO
                FROM VAADINWEB.HUMUSUARIOS HU
                LEFT JOIN ZAYMAWEB.GHEMPEMPLEADOS EM ON TRIM(HU.IDENTIFICACION) = TRIM(EM.IDENTIFICACION)
                LEFT JOIN VAADINWEB.HUMEMPLEADOEVAL CA ON EM.IDEMPLEADO = CA.IDEMPLEADO AND CA.ACTIVO = 1
                WHERE (UPPER(EM.PNOMBRE || ' ' || EM.PAPELLIDO) LIKE '%' || :term || '%'
                   OR HU.IDENTIFICACION LIKE '%' || :term2 || '%')
                AND ROWNUM <= 20
                ORDER BY NOMBRE ASC";
        $conn  = $this->conectar();
        $query = oci_parse($conn, $sql);
        oci_bind_by_name($query, ':term',  $term);
        oci_bind_by_name($query, ':term2', $term);
        oci_execute($query);
        $rows = [];
        while ($r = oci_fetch_assoc($query)) {
            $r['NOMBRE'] = fromOracleEncoding($r['NOMBRE'] ?? '');
            $r['CARGO']  = fromOracleEncoding($r['CARGO']  ?? '');
            $rows[] = $r;
        }
        oci_free_statement($query);
        return $rows;
    }

    /** Actualiza los permisos de un usuario */
    public function actualizarPermisos($idUsuario, $esAdmin, $verDetalleRep) {
        $idUsuario    = (int)$idUsuario;
        $esAdmin      = (int)$esAdmin;
        $verDetalle   = (int)$verDetalleRep;
        $sql   = "UPDATE VAADINWEB.HUMUSUARIOS
                  SET ESADMIN = :esadmin, VER_DETALLE_REP = :verdetalle
                  WHERE IDUSUARIO = :id";
        $conn  = $this->conectar();
        $query = oci_parse($conn, $sql);
        oci_bind_by_name($query, ':esadmin',   $esAdmin);
        oci_bind_by_name($query, ':verdetalle', $verDetalle);
        oci_bind_by_name($query, ':id',         $idUsuario);
        $ok = oci_execute($query, OCI_COMMIT_ON_SUCCESS);
        oci_free_statement($query);
        return $ok;
    }

    // ══════════════════════════════════════════════════════════════════
    // REPORTE DE AVANCE POR PROCESO (IDDEPENDENCIA)
    // ══════════════════════════════════════════════════════════════════

    /** 
     * Devuelve el porcentaje de cumplimiento agrupado por proceso/área.
     * Cruza empleados activos con +90 días contra las tablas de evaluación.
     * $idPeriodo se usa para contexto pero el cruce es por existencia de registro.
     */
    public function getAvancePorProceso($periodo = null) {
        $filtroP = $periodo ? "AND IDPERIODO = " . (int)$periodo['IDPERIODO'] : "";

        $sql = "
            SELECT
                E.PROCESO                                                             AS IDDEPENDENCIA,
                E.PROCESO                                                             AS DEPENDENCIA,
                COUNT(DISTINCT E.IDEMPLEADO)                                         AS TOTAL,
                COUNT(DISTINCT A.IDEMPLEADO)                                         AS CON_AUTOEVAL,
                COUNT(DISTINCT CASE
                    WHEN A.IDEMPLEADO IS NOT NULL
                         AND (E.IDEMPLEADO_EVAL IS NULL OR C.IDEMPLEADO IS NOT NULL)
                         AND (NVL(SUB_E.TOTAL_SUB, 0) = 0 OR R_E.TOTAL_REAL >= SUB_E.TOTAL_SUB)
                    THEN E.IDEMPLEADO END)                                            AS COMPLETOS,
                ROUND(COUNT(DISTINCT A.IDEMPLEADO) * 100.0
                      / NULLIF(COUNT(DISTINCT E.IDEMPLEADO), 0), 1)                  AS PCT_AUTOEVAL,
                ROUND(COUNT(DISTINCT CASE
                    WHEN A.IDEMPLEADO IS NOT NULL
                         AND (E.IDEMPLEADO_EVAL IS NULL OR C.IDEMPLEADO IS NOT NULL)
                         AND (NVL(SUB_E.TOTAL_SUB, 0) = 0 OR R_E.TOTAL_REAL >= SUB_E.TOTAL_SUB)
                    THEN E.IDEMPLEADO END) * 100.0
                      / NULLIF(COUNT(DISTINCT E.IDEMPLEADO), 0), 1)                  AS PCT_COMPLETO
            FROM VAADINWEB.HUMEMPLEADOEVAL E
            LEFT JOIN (
                SELECT DISTINCT IDEMPLEADO FROM VAADINWEB.HUMRESPUESTA
                WHERE TIPO_EVAL = 'AUTO' AND CONFIRMADO = 1 $filtroP
            ) A ON E.IDEMPLEADO = A.IDEMPLEADO
            LEFT JOIN (
                SELECT DISTINCT IDEMPLEADO FROM VAADINWEB.HUMRESPUESTA
                WHERE TIPO_EVAL = 'LIDER_A_COLAB' AND CONFIRMADO = 1 $filtroP
            ) C ON E.IDEMPLEADO = C.IDEMPLEADO
            LEFT JOIN (
                SELECT IDEMPLEADO_EVAL, COUNT(*) AS TOTAL_SUB
                FROM VAADINWEB.HUMEMPLEADOEVAL
                WHERE ACTIVO = 1 AND IDEMPLEADO_EVAL IS NOT NULL
                GROUP BY IDEMPLEADO_EVAL
            ) SUB_E ON E.IDEMPLEADO = SUB_E.IDEMPLEADO_EVAL
            LEFT JOIN (
                SELECT IDEMPLEADO_EVAL, COUNT(DISTINCT IDEMPLEADO) AS TOTAL_REAL
                FROM VAADINWEB.HUMRESPUESTA
                WHERE TIPO_EVAL IN ('LIDER_A_COLAB','COLAB_A_LIDER')
                  AND CONFIRMADO = 1 $filtroP
                GROUP BY IDEMPLEADO_EVAL
            ) R_E ON E.IDEMPLEADO = R_E.IDEMPLEADO_EVAL
            WHERE E.ACTIVO = 1
              AND E.PROCESO IS NOT NULL
            GROUP BY E.PROCESO
            ORDER BY PCT_COMPLETO DESC NULLS LAST,
                     PCT_AUTOEVAL DESC NULLS LAST,
                     E.PROCESO ASC
        ";
        $res  = $this->ejecutarConsulta($sql);
        $rows = [];
        if ($res) {
            while ($r = oci_fetch_assoc($res)) {
                $r['DEPENDENCIA'] = fromOracleEncoding($r['DEPENDENCIA'] ?? '');
                $rows[] = $r;
            }
            oci_free_statement($res);
        }
        return $rows;
    }

    // ══════════════════════════════════════════════════════════════════
    // PROMEDIO DE CALIFICACIÓN POR PROCESO / COMPETENCIA
    // ══════════════════════════════════════════════════════════════════

    /** 
     * Devuelve para cada proceso el promedio de cada competencia 
     * (PREGUNTA1..PREGUNTA11) de las evaluaciones de colaborador recibidas
     */
    public function getPromedioCompetenciasPorProceso($periodo = null) {
        $filtroP = $periodo ? "AND IDPERIODO = " . (int)$periodo['IDPERIODO'] : "";

        $sql = "
            SELECT
                E.PROCESO                                    AS IDDEPENDENCIA,
                E.PROCESO                                    AS DEPENDENCIA,
                C.NUM_PREGUNTA,
                C.NOMBRE                                     AS NOMBRE_COMP,
                ROUND(AVG(O.VALOR), 2)                       AS PROMEDIO,
                COUNT(DISTINCT R.IDEMPLEADO)                 AS EVALUADOS
            FROM VAADINWEB.HUMEMPLEADOEVAL E
            INNER JOIN VAADINWEB.HUMRESPUESTA R
                ON E.IDEMPLEADO = R.IDEMPLEADO
               AND R.TIPO_EVAL  = 'LIDER_A_COLAB'
               AND R.CONFIRMADO = 1
               $filtroP
            INNER JOIN VAADINWEB.HUMCOMPETENCIA  C ON R.IDCOMPETENCIA = C.IDCOMPETENCIA
            INNER JOIN VAADINWEB.HUMOPCIONESCALA O ON R.IDOPCION      = O.IDOPCION
            WHERE E.ACTIVO = 1
              AND E.PROCESO IS NOT NULL
            GROUP BY E.PROCESO, C.NUM_PREGUNTA, C.NOMBRE
            ORDER BY E.PROCESO ASC, C.NUM_PREGUNTA ASC
        ";
        $res  = $this->ejecutarConsulta($sql);
        $rows = [];
        if ($res) {
            while ($r = oci_fetch_assoc($res)) {
                $r['DEPENDENCIA']  = fromOracleEncoding($r['DEPENDENCIA']  ?? '');
                $r['NOMBRE_COMP']  = fromOracleEncoding($r['NOMBRE_COMP']  ?? '');
                $r['PROMEDIO']     = (float)str_replace(',', '.', $r['PROMEDIO'] ?? 0);
                $rows[] = $r;
            }
            oci_free_statement($res);
        }
        // Agrupar por dependencia para compatibilidad con la vista
        $grouped = [];
        foreach ($rows as $r) {
            $dep = $r['IDDEPENDENCIA'];
            if (!isset($grouped[$dep])) {
                $grouped[$dep] = [
                    'IDDEPENDENCIA' => $dep,
                    'DEPENDENCIA'   => $r['DEPENDENCIA'],
                    'EVALUADOS'     => $r['EVALUADOS'],
                ];
            }
            $pNum = 'PROM_P' . $r['NUM_PREGUNTA'];
            $grouped[$dep][$pNum] = $r['PROMEDIO'];
        }
        return array_values($grouped);
    }


    // ══════════════════════════════════════════════════════════════════
    // ESTADO DE EVALUACIONES DEL EQUIPO (para seguimiento admin)
    // ══════════════════════════════════════════════════════════════════

    /** Lista todos los colaboradores activos con su estado de evaluación */
    public function getEstadoEquipoCompleto($periodo = null) {
        $filtroP = $periodo ? "AND IDPERIODO = " . (int)$periodo['IDPERIODO'] : "";

        $sql = "
            SELECT
                HE.IDEMPLEADO,
                HE.NOMBRE                                        AS EMPLEADO,
                HE.CARGO,
                HE.PROCESO                                       AS AREAFUNCIONAL,
                NVL(HE.EMAIL, GE.EMAIL)                          AS EMAIL,
                TO_CHAR(HU.ULTIMA_NOTIF, 'YYYY-MM-DD HH24:MI')  AS ULTIMA_NOTIF,
                CASE WHEN A.IDEMPLEADO IS NOT NULL THEN 1 ELSE 0 END AS TIENE_AUTOEVAL,
                NVL(REC.EVALUADORES_OK, 0)                             AS EVAL_RECIBIDAS,
                NVL(SUB.TOTAL_SUBORDINADOS, 0)
                    + CASE WHEN HE.IDEMPLEADO_EVAL IS NOT NULL THEN 1 ELSE 0 END AS EVAL_ESPERADAS,
                NVL(R.TOTAL_REALIZADAS, 0)                             AS EVAL_REALIZADAS,
                NVL(SUB.TOTAL_SUBORDINADOS, 0)
                    + CASE WHEN HE.IDEMPLEADO_EVAL IS NOT NULL THEN 1 ELSE 0 END AS TOTAL_SUBORDINADOS,
                CASE
                    WHEN A.IDEMPLEADO IS NOT NULL
                         AND NVL(REC.EVALUADORES_OK, 0) >=
                             (NVL(SUB.TOTAL_SUBORDINADOS, 0)
                              + CASE WHEN HE.IDEMPLEADO_EVAL IS NOT NULL THEN 1 ELSE 0 END)
                         AND (NVL(SUB.TOTAL_SUBORDINADOS, 0) = 0
                              OR NVL(R.TOTAL_REALIZADAS, 0) >= NVL(SUB.TOTAL_SUBORDINADOS, 0))
                    THEN 'completo'
                    WHEN A.IDEMPLEADO IS NOT NULL
                    THEN 'en_progreso'
                    ELSE 'sin_iniciar'
                END AS ESTADO
            FROM VAADINWEB.HUMEMPLEADOEVAL HE
            LEFT JOIN ZAYMAWEB.GHEMPEMPLEADOS GE
                ON HE.IDEMPLEADO = GE.IDEMPLEADO
            LEFT JOIN VAADINWEB.HUMUSUARIOS HU
                ON TRIM(HU.IDENTIFICACION) = TRIM(HE.IDENTIFICACION)
            LEFT JOIN (
                SELECT DISTINCT IDEMPLEADO FROM VAADINWEB.HUMRESPUESTA
                WHERE TIPO_EVAL = 'AUTO' AND CONFIRMADO = 1 $filtroP
            ) A ON HE.IDEMPLEADO = A.IDEMPLEADO
            LEFT JOIN (
                SELECT IDEMPLEADO,
                       COUNT(DISTINCT IDEMPLEADO_EVAL) AS EVALUADORES_OK
                FROM VAADINWEB.HUMRESPUESTA
                WHERE TIPO_EVAL IN ('LIDER_A_COLAB', 'COLAB_A_LIDER')
                  AND CONFIRMADO = 1 $filtroP
                GROUP BY IDEMPLEADO
            ) REC ON HE.IDEMPLEADO = REC.IDEMPLEADO
            LEFT JOIN (
                SELECT IDEMPLEADO_EVAL,
                       COUNT(DISTINCT IDEMPLEADO) AS TOTAL_REALIZADAS
                FROM VAADINWEB.HUMRESPUESTA
                WHERE TIPO_EVAL IN ('LIDER_A_COLAB', 'COLAB_A_LIDER')
                  AND CONFIRMADO = 1 $filtroP
                GROUP BY IDEMPLEADO_EVAL
            ) R ON HE.IDEMPLEADO = R.IDEMPLEADO_EVAL
            LEFT JOIN (
                SELECT IDEMPLEADO_EVAL,
                       COUNT(*) AS TOTAL_SUBORDINADOS
                FROM VAADINWEB.HUMEMPLEADOEVAL
                WHERE ACTIVO = 1 AND IDEMPLEADO_EVAL IS NOT NULL
                GROUP BY IDEMPLEADO_EVAL
            ) SUB ON HE.IDEMPLEADO = SUB.IDEMPLEADO_EVAL
            WHERE HE.ACTIVO = 1
            ORDER BY
                CASE WHEN A.IDEMPLEADO IS NOT NULL
                          AND NVL(REC.EVALUADORES_OK,0) >=
                              (NVL(SUB.TOTAL_SUBORDINADOS,0)
                               + CASE WHEN HE.IDEMPLEADO_EVAL IS NOT NULL THEN 1 ELSE 0 END)
                          AND (NVL(SUB.TOTAL_SUBORDINADOS,0) = 0
                               OR NVL(R.TOTAL_REALIZADAS,0) >= NVL(SUB.TOTAL_SUBORDINADOS,0))
                     THEN 0
                     WHEN A.IDEMPLEADO IS NOT NULL THEN 1
                     ELSE 2 END ASC,
                HE.NOMBRE ASC
        ";
        $campos = ['EMPLEADO','CARGO','AREAFUNCIONAL','EMAIL'];
        $res    = $this->ejecutarConsulta($sql);
        $rows   = [];
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


    public function getResumenEstado($rows) {
        $r = ['total' => 0, 'completo' => 0, 'en_progreso' => 0, 'sin_iniciar' => 0];
        foreach ($rows as $row) {
            $r['total']++;
            $r[$row['ESTADO']]++;
        }
        return $r;
    }

    // ══════════════════════════════════════════════════════════════════
    // GESTIÓN DE OBJETIVOS DE MEJORA (desde panel admin)
    // ══════════════════════════════════════════════════════════════════

    /** Devuelve todos los objetivos agrupados */
    public function getObjetivos($soloActivos = false) {
        $filtro = $soloActivos ? "WHERE ACTIVO = 1" : "";
        $sql = "SELECT IDOBJETIVO, NUM_COMPETENCIA, CALIFICACION, OBJETIVO, MODELO,
                       INDICADOR, META, PLAZO, EVIDENCIA, SEGUIMIENTO, USO_RECOMENDADO, ACTIVO
                FROM VAADINWEB.HUMOBJETIVOMEJORA
                $filtro
                ORDER BY NUM_COMPETENCIA ASC, CALIFICACION ASC, IDOBJETIVO ASC";
        $campos = ['OBJETIVO','MODELO','INDICADOR','META','PLAZO','EVIDENCIA','SEGUIMIENTO','USO_RECOMENDADO'];
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

    /** Crea un nuevo objetivo predeterminado SMART */
    public function crearObjetivo($numComp, $calificacion, $objetivo, $idUsuario,
                                  $modelo='', $indicador='', $meta='', $plazo='',
                                  $evidencia='', $seguimiento='', $usoRecomendado='') {
        $numComp      = (int)$numComp;
        $calificacion = (int)$calificacion;
        $objetivo      = $this->toOracle($objetivo);
        $modelo        = $this->toOracle($modelo);
        $indicador     = $this->toOracle($indicador);
        $meta          = $this->toOracle($meta);
        $plazo         = $this->toOracle($plazo);
        $evidencia     = $this->toOracle($evidencia);
        $seguimiento   = $this->toOracle($seguimiento);
        $usoRecomendado = $this->toOracle($usoRecomendado);
        $sql = "INSERT INTO VAADINWEB.HUMOBJETIVOMEJORA
                    (IDOBJETIVO, NUM_COMPETENCIA, CALIFICACION, OBJETIVO, MODELO,
                     INDICADOR, META, PLAZO, EVIDENCIA, SEGUIMIENTO, USO_RECOMENDADO,
                     ACTIVO, FECHACREACION, IDUSUARIO_CREA)
                VALUES
                    (VAADINWEB.SEQ_HUMOBJETIVOMEJORA.NEXTVAL, :comp, :cal, :obj, :mod,
                     :ind, :met, :pla, :evi, :seg, :uso, 1, SYSDATE, :usr)";
        $conn  = $this->conectar();
        $query = oci_parse($conn, $sql);
        oci_bind_by_name($query, ':comp', $numComp);
        oci_bind_by_name($query, ':cal',  $calificacion);
        oci_bind_by_name($query, ':obj',  $objetivo);
        oci_bind_by_name($query, ':mod',  $modelo);
        oci_bind_by_name($query, ':ind',  $indicador);
        oci_bind_by_name($query, ':met',  $meta);
        oci_bind_by_name($query, ':pla',  $plazo);
        oci_bind_by_name($query, ':evi',  $evidencia);
        oci_bind_by_name($query, ':seg',  $seguimiento);
        oci_bind_by_name($query, ':uso',  $usoRecomendado);
        oci_bind_by_name($query, ':usr',  $idUsuario);
        $ok = oci_execute($query, OCI_COMMIT_ON_SUCCESS);
        oci_free_statement($query);
        return $ok;
    }

    /** Edita un objetivo existente con campos SMART */
    public function editarObjetivo($id, $numComp, $calificacion, $objetivo, $modelo,
                                   $indicador, $meta, $plazo, $evidencia, $seguimiento,
                                   $usoRecomendado) {
        $id = (int)$id; $numComp = (int)$numComp; $calificacion = (int)$calificacion;
        $objetivo      = $this->toOracle($objetivo);
        $modelo        = $this->toOracle($modelo);
        $indicador     = $this->toOracle($indicador);
        $meta          = $this->toOracle($meta);
        $plazo         = $this->toOracle($plazo);
        $evidencia     = $this->toOracle($evidencia);
        $seguimiento   = $this->toOracle($seguimiento);
        $usoRecomendado = $this->toOracle($usoRecomendado);
        $sql = "UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET
                    NUM_COMPETENCIA = :nc, CALIFICACION = :cal, OBJETIVO = :obj,
                    MODELO = :mod, INDICADOR = :ind, META = :met, PLAZO = :pla,
                    EVIDENCIA = :evi, SEGUIMIENTO = :seg, USO_RECOMENDADO = :uso
                WHERE IDOBJETIVO = :id";
        $conn = $this->conectar();
        $q    = oci_parse($conn, $sql);
        oci_bind_by_name($q, ':nc',  $numComp);
        oci_bind_by_name($q, ':cal', $calificacion);
        oci_bind_by_name($q, ':obj', $objetivo);
        oci_bind_by_name($q, ':mod', $modelo);
        oci_bind_by_name($q, ':ind', $indicador);
        oci_bind_by_name($q, ':met', $meta);
        oci_bind_by_name($q, ':pla', $plazo);
        oci_bind_by_name($q, ':evi', $evidencia);
        oci_bind_by_name($q, ':seg', $seguimiento);
        oci_bind_by_name($q, ':uso', $usoRecomendado);
        oci_bind_by_name($q, ':id',  $id);
        $ok = oci_execute($q, OCI_COMMIT_ON_SUCCESS);
        oci_free_statement($q);
        return $ok;
    }

    /** Promedio de competencias de liderazgo por área (COLAB_A_LIDER) */
    public function getPromedioLiderazgoPorProceso($periodo = null): array {
        $filtroP = $periodo ? "AND R.IDPERIODO = " . (int)$periodo['IDPERIODO'] : "";
        $sql = "
            SELECT
                E.PROCESO                         AS IDDEPENDENCIA,
                E.PROCESO                         AS DEPENDENCIA,
                C.NUM_PREGUNTA,
                C.NOMBRE                          AS NOMBRE_COMP,
                ROUND(AVG(O.VALOR), 2)            AS PROMEDIO,
                COUNT(DISTINCT R.IDEMPLEADO)      AS EVALUADOS
            FROM VAADINWEB.HUMEMPLEADOEVAL E
            INNER JOIN VAADINWEB.HUMRESPUESTA R
                ON E.IDEMPLEADO = R.IDEMPLEADO
               AND R.TIPO_EVAL  = 'COLAB_A_LIDER'
               AND R.CONFIRMADO = 1
               $filtroP
            INNER JOIN VAADINWEB.HUMCOMPETENCIA  C ON R.IDCOMPETENCIA = C.IDCOMPETENCIA
            INNER JOIN VAADINWEB.HUMOPCIONESCALA O ON R.IDOPCION      = O.IDOPCION
            WHERE E.ACTIVO = 1
              AND E.PROCESO IS NOT NULL
            GROUP BY E.PROCESO, C.NUM_PREGUNTA, C.NOMBRE
            ORDER BY E.PROCESO ASC, C.NUM_PREGUNTA ASC
        ";
        $res  = $this->ejecutarConsulta($sql);
        $rows = [];
        if ($res) {
            while ($r = oci_fetch_assoc($res)) {
                $r['DEPENDENCIA'] = fromOracleEncoding($r['DEPENDENCIA'] ?? '');
                $r['NOMBRE_COMP'] = fromOracleEncoding($r['NOMBRE_COMP'] ?? '');
                $r['PROMEDIO']    = (float)str_replace(',', '.', $r['PROMEDIO'] ?? 0);
                $rows[] = $r;
            }
            oci_free_statement($res);
        }
        $grouped = [];
        foreach ($rows as $r) {
            $dep = $r['IDDEPENDENCIA'];
            if (!isset($grouped[$dep])) {
                $grouped[$dep] = ['IDDEPENDENCIA'=>$dep,'DEPENDENCIA'=>$r['DEPENDENCIA'],'EVALUADOS'=>$r['EVALUADOS']];
            }
            $grouped[$dep]['PROM_P' . $r['NUM_PREGUNTA']] = $r['PROMEDIO'];
        }
        return array_values($grouped);
    }


    // ══════════════════════════════════════════════════════════════════
    // GESTIÓN DE HUMEMPLEADOEVAL
    // ══════════════════════════════════════════════════════════════════

    /** Convierte cadena de Oracle (Windows-1252) a UTF-8 */
    private function toUtf8(string $str): string {
        // Forzar siempre la conversión — no confiar en detect_encoding
        $result = fromOracleEncoding($str);
        return $result !== false ? $result : $str;
    }

    /** Lista todos los registros de HUMEMPLEADOEVAL */
    public function getEmpleadosEval(string $buscar = '', int $soloActivos = 1): array {
        $filtroActivo = $soloActivos ? "AND HE.ACTIVO = 1" : "";
        $filtroBuscar = '';
        if ($buscar) {
            $b = addslashes($buscar);
            $filtroBuscar = "AND (UPPER(HE.NOMBRE) LIKE UPPER('%$b%')
                              OR HE.IDENTIFICACION LIKE '%$b%'
                              OR UPPER(HE.CARGO) LIKE UPPER('%$b%'))";
        }
        $rolNombres = [1 => 'ASISTENCIAL', 2 => 'ADMINISTRATIVO', 3 => 'LÍDER'];
        $sql = "SELECT HE.IDASIGNACION, HE.IDEMPLEADO, HE.IDENTIFICACION,
                       HE.NOMBRE, HE.CARGO, HE.IDROL, HE.IDEMPLEADO_EVAL,
                       HE.APLICA_EXP_AZUL, HE.PROCESO, HE.EMAIL, HE.CELULAR,
                       HE.NOMBRE_JEFE, HE.ACTIVO, HE.ES_LIDER_FUNCIONAL, HE.ES_DIRECTOR
                FROM VAADINWEB.HUMEMPLEADOEVAL HE
                WHERE 1=1 $filtroActivo $filtroBuscar
                ORDER BY HE.IDROL ASC, HE.NOMBRE ASC";
        $campos = ['NOMBRE','CARGO','PROCESO','EMAIL','CELULAR','NOMBRE_JEFE'];
        $res  = $this->ejecutarConsulta($sql);
        $rows = [];
        if ($res) {
            while ($r = oci_fetch_assoc($res)) {
                foreach ($campos as $c) {
                    if (isset($r[$c])) $r[$c] = $this->toUtf8($r[$c]);
                }
                // ROL_NOMBRE construido en PHP para evitar problemas de encoding
                $r['ROL_NOMBRE'] = $rolNombres[(int)$r['IDROL']] ?? '?';
                $rows[] = $r;
            }
            oci_free_statement($res);
        }
        return $rows;
    }


    /** Busca un empleado en GHEMPEMPLEADOS por cédula para autocompletar */
    public function buscarEmpleadoPorCedula(string $cedula): ?array {
        $cedula = addslashes(trim($cedula));
        $sql = "SELECT IDEMPLEADO, IDENTIFICACION,
                       PNOMBRE || ' ' || SNOMBRE || ' ' || PAPELLIDO || ' ' || SAPELLIDO AS NOMBRE,
                       ESTADOEMPLEADO
                FROM ZAYMAWEB.GHEMPEMPLEADOS
                WHERE IDENTIFICACION = '$cedula' AND ESTADOEMPLEADO = 1 AND ROWNUM = 1";
        $res = $this->ejecutarConsulta($sql);
        if ($res) {
            $r = oci_fetch_assoc($res);
            oci_free_statement($res);
            if ($r) {
                $r['NOMBRE'] = $this->toUtf8($r['NOMBRE'] ?? '');
                return $r;
            }
        }
        return null;
    }

    /** Crea un nuevo registro en HUMEMPLEADOEVAL */
    public function crearEmpleadoEval(int $idempleado, string $identificacion,
                                      string $nombre, string $cargo, int $idRol,
                                      int $idJefe, int $aplicaExpAzul, string $proceso,
                                      string $email, string $celular, string $nombreJefe): bool {
        // Verificar que no exista ya
        $checkSql = "SELECT COUNT(*) AS CNT FROM VAADINWEB.HUMEMPLEADOEVAL WHERE IDEMPLEADO = $idempleado";
        $chk = $this->ejecutarConsulta($checkSql);
        if ($chk) {
            $r = oci_fetch_assoc($chk);
            oci_free_statement($chk);
            if ((int)$r['CNT'] > 0) return false; // Ya existe
        }

        // Obtener siguiente IDASIGNACION
        $seqSql = "SELECT NVL(MAX(IDASIGNACION),0)+1 AS NXT FROM VAADINWEB.HUMEMPLEADOEVAL";
        $seq = $this->ejecutarConsulta($seqSql);
        $nxt = 1;
        if ($seq) {
            $r = oci_fetch_assoc($seq);
            oci_free_statement($seq);
            $nxt = (int)$r['NXT'];
        }

        $sql = "INSERT INTO VAADINWEB.HUMEMPLEADOEVAL
                    (IDASIGNACION, IDEMPLEADO, IDENTIFICACION, NOMBRE, CARGO,
                     IDROL, IDEMPLEADO_EVAL, APLICA_EXP_AZUL,
                     PROCESO, EMAIL, CELULAR, NOMBRE_JEFE, ACTIVO, FECHACREACION)
                VALUES
                    (:seq, :id, :ident, :nom, :cargo,
                     :rol, :jefe, :az,
                     :proc, :email, :cel, :njefe, 1, SYSDATE)";
        $conn = $this->conectar();
        $q    = oci_parse($conn, $sql);
        oci_bind_by_name($q, ':seq',   $nxt);
        oci_bind_by_name($q, ':id',    $idempleado);
        oci_bind_by_name($q, ':ident', $identificacion, 20);
        oci_bind_by_name($q, ':nom',   $nombre,   200);
        oci_bind_by_name($q, ':cargo', $cargo,    200);
        oci_bind_by_name($q, ':rol',   $idRol);
        oci_bind_by_name($q, ':jefe',  $idJefe);
        oci_bind_by_name($q, ':az',    $aplicaExpAzul);
        oci_bind_by_name($q, ':proc',  $proceso,  10);
        oci_bind_by_name($q, ':email', $email,    100);
        oci_bind_by_name($q, ':cel',   $celular,  20);
        oci_bind_by_name($q, ':njefe', $nombreJefe, 200);
        $ok = oci_execute($q, OCI_COMMIT_ON_SUCCESS);
        oci_free_statement($q);
        return (bool)$ok;
    }


    /** Lista todos los líderes activos para el select de jefe */
    public function getLideresEval(): array {
        $sql = "SELECT IDEMPLEADO, NOMBRE, PROCESO
                FROM VAADINWEB.HUMEMPLEADOEVAL
                WHERE (ES_LIDER_FUNCIONAL = 1 OR IDROL = 3) AND ACTIVO = 1
                ORDER BY NOMBRE ASC";
        $res  = $this->ejecutarConsulta($sql);
        $rows = [];
        if ($res) {
            while ($r = oci_fetch_assoc($res)) {
                $r['NOMBRE']  = $this->toUtf8($r['NOMBRE']  ?? '');
                $r['PROCESO'] = $this->toUtf8($r['PROCESO'] ?? '');
                $rows[] = $r;
            }
            oci_free_statement($res);
        }
        return $rows;
    }

    /** Edita un registro de HUMEMPLEADOEVAL */
    public function editarEmpleadoEval(int $id, int $idRol, int $aplicaExpAzul,
                                       string $cargo, string $proceso,
                                       string $email, string $celular,
                                       int $idJefe, string $nombreJefe,
                                       int $esDirector = 0,
                                       int $esLiderFuncional = 0): bool {
        $conn = $this->conectar();

        // Obtener jefe anterior para recalcular ES_LIDER_FUNCIONAL
        $sqlPrev = "SELECT IDEMPLEADO, IDEMPLEADO_EVAL FROM VAADINWEB.HUMEMPLEADOEVAL
                    WHERE IDASIGNACION = $id";
        $qPrev   = oci_parse($conn, $sqlPrev);
        oci_execute($qPrev);
        $prev    = oci_fetch_assoc($qPrev);
        oci_free_statement($qPrev);
        $idEmpleado  = (int)($prev['IDEMPLEADO']      ?? 0);
        $jefeAnterior = (int)($prev['IDEMPLEADO_EVAL'] ?? 0);

        // Actualizar registro principal
        $sql = "UPDATE VAADINWEB.HUMEMPLEADOEVAL SET
                    IDROL              = :rol,
                    APLICA_EXP_AZUL    = :az,
                    CARGO              = :cargo,
                    PROCESO            = :proceso,
                    EMAIL              = :email,
                    CELULAR            = :cel,
                    IDEMPLEADO_EVAL    = :jefe,
                    NOMBRE_JEFE        = :njefe,
                    ES_DIRECTOR        = :dir,
                    ES_LIDER_FUNCIONAL = :lf
                WHERE IDASIGNACION = :id";
        $q = oci_parse($conn, $sql);
        $cargo      = $this->toOracle($cargo);
        $proceso    = $this->toOracle($proceso);
        $email      = $this->toOracle($email);
        $celular    = $this->toOracle($celular);
        $nombreJefe = $this->toOracle($nombreJefe);
        oci_bind_by_name($q, ':rol',   $idRol);
        oci_bind_by_name($q, ':az',    $aplicaExpAzul);
        oci_bind_by_name($q, ':cargo', $cargo,     200);
        oci_bind_by_name($q, ':proceso', $proceso, 10);
        oci_bind_by_name($q, ':email', $email,     100);
        oci_bind_by_name($q, ':cel',   $celular,   20);
        oci_bind_by_name($q, ':jefe',  $idJefe);
        oci_bind_by_name($q, ':njefe', $nombreJefe, 200);
        oci_bind_by_name($q, ':dir',   $esDirector);
        oci_bind_by_name($q, ':lf',    $esLiderFuncional);
        oci_bind_by_name($q, ':id',    $id);
        $ok = oci_execute($q, OCI_NO_AUTO_COMMIT);
        oci_free_statement($q);

        if ($ok) {
            // Recalcular ES_LIDER_FUNCIONAL para jefe anterior y nuevo
            foreach (array_unique(array_filter([$jefeAnterior, $idJefe])) as $idJefeCalc) {
                $sqlLF = "UPDATE VAADINWEB.HUMEMPLEADOEVAL
                          SET ES_LIDER_FUNCIONAL = (
                              SELECT CASE WHEN COUNT(*) > 0 THEN 1 ELSE 0 END
                              FROM VAADINWEB.HUMEMPLEADOEVAL
                              WHERE IDEMPLEADO_EVAL = $idJefeCalc AND ACTIVO = 1
                          )
                          WHERE IDEMPLEADO = $idJefeCalc";
                $qLF = oci_parse($conn, $sqlLF);
                oci_execute($qLF, OCI_NO_AUTO_COMMIT);
                oci_free_statement($qLF);
            }
            oci_commit($conn);
        }

        return (bool)$ok;
    }

    /** Activa o inactiva un registro de HUMEMPLEADOEVAL */
    public function toggleEmpleadoEval(int $id, int $activo): bool {
        $sql  = "UPDATE VAADINWEB.HUMEMPLEADOEVAL SET ACTIVO = :activo WHERE IDASIGNACION = :id";
        $conn = $this->conectar();
        $q    = oci_parse($conn, $sql);
        oci_bind_by_name($q, ':activo', $activo);
        oci_bind_by_name($q, ':id',     $id);
        $ok = oci_execute($q, OCI_COMMIT_ON_SUCCESS);
        oci_free_statement($q);
        return (bool)$ok;
    }

    /** Cambia el jefe directo de un colaborador */
    public function cambiarJefeEmpleadoEval(int $id, int $idJefe): bool {
        $sql  = "UPDATE VAADINWEB.HUMEMPLEADOEVAL SET IDEMPLEADO_EVAL = :jefe WHERE IDASIGNACION = :id";
        $conn = $this->conectar();
        $q    = oci_parse($conn, $sql);
        oci_bind_by_name($q, ':jefe', $idJefe);
        oci_bind_by_name($q, ':id',   $id);
        $ok = oci_execute($q, OCI_COMMIT_ON_SUCCESS);
        oci_free_statement($q);
        return (bool)$ok;
    }


    // ══════════════════════════════════════════════════════════════════
    // GESTIÓN DE COMPETENCIAS
    // ══════════════════════════════════════════════════════════════════

    /** Trae todas las competencias agrupadas con su dimensión */
    public function getCompetencias(): array {
        $sql = "SELECT C.IDCOMPETENCIA, C.IDDIMENSION, C.NOMBRE, C.PREGUNTA,
                       C.NUM_PREGUNTA, C.ORDEN, C.ACTIVO,
                       D.NOMBRE AS NOMBRE_DIMENSION
                FROM VAADINWEB.HUMCOMPETENCIA C
                INNER JOIN VAADINWEB.HUMDIMENSION D ON C.IDDIMENSION = D.IDDIMENSION
                ORDER BY C.IDDIMENSION ASC, C.NUM_PREGUNTA ASC";
        $res  = $this->ejecutarConsulta($sql);
        $rows = [];
        if ($res) {
            while ($r = oci_fetch_assoc($res)) {
                $r['NOMBRE']           = fromOracleEncoding($r['NOMBRE']           ?? '');
                $r['PREGUNTA']         = fromOracleEncoding($r['PREGUNTA']         ?? '');
                $r['NOMBRE_DIMENSION'] = fromOracleEncoding($r['NOMBRE_DIMENSION'] ?? '');
                $rows[] = $r;
            }
            oci_free_statement($res);
        }
        return $rows;
    }

    /** Trae las opciones (tooltips) de una competencia */
    public function getOpcionesDeCompetencia(int $idComp): array {
        $sql = "SELECT OC.IDOPCIONCOMP, OC.IDOPCION, OC.DESCRIPCION, OC.ACTIVO,
                       OS.ETIQUETA, OS.VALOR
                FROM VAADINWEB.HUMOPCIONCOMPETENCIA OC
                INNER JOIN VAADINWEB.HUMOPCIONESCALA OS ON OC.IDOPCION = OS.IDOPCION
                WHERE OC.IDCOMPETENCIA = $idComp
                ORDER BY OS.VALOR DESC";
        $res  = $this->ejecutarConsulta($sql);
        $rows = [];
        if ($res) {
            while ($r = oci_fetch_assoc($res)) {
                $r['DESCRIPCION'] = fromOracleEncoding($r['DESCRIPCION'] ?? '');
                $r['ETIQUETA']    = fromOracleEncoding($r['ETIQUETA']    ?? '');
                $rows[] = $r;
            }
            oci_free_statement($res);
        }
        return $rows;
    }

    /** Edita nombre y pregunta de una competencia */
    public function editarCompetencia(int $id, string $nombre, string $pregunta): bool {
        $idC  = $id;
        $nom  = $this->toOracle($nombre);
        $preg = $this->toOracle($pregunta);
        $sql  = "UPDATE VAADINWEB.HUMCOMPETENCIA SET NOMBRE = :nom, PREGUNTA = :preg WHERE IDCOMPETENCIA = :id";
        $conn = $this->conectar();
        $q    = oci_parse($conn, $sql);
        oci_bind_by_name($q, ':nom',  $nom,  200);
        oci_bind_by_name($q, ':preg', $preg, 500);
        oci_bind_by_name($q, ':id',   $idC);
        $ok = oci_execute($q, OCI_COMMIT_ON_SUCCESS);
        oci_free_statement($q);
        return (bool)$ok;
    }

    /** Activa o inactiva una competencia */
    public function toggleCompetencia(int $id, int $activo): bool {
        $sql  = "UPDATE VAADINWEB.HUMCOMPETENCIA SET ACTIVO = :activo WHERE IDCOMPETENCIA = :id";
        $conn = $this->conectar();
        $q    = oci_parse($conn, $sql);
        oci_bind_by_name($q, ':activo', $activo);
        oci_bind_by_name($q, ':id',     $id);
        $ok = oci_execute($q, OCI_COMMIT_ON_SUCCESS);
        oci_free_statement($q);
        return $ok;
    }

    /**
     * Trae TODAS las opciones de todas las competencias en una sola consulta.
     * Reemplaza el N+1 loop de getOpcionesDeCompetencia().
     * Devuelve [IDCOMPETENCIA => [opciones...]]
     */
    public function getTodasOpcionesCompetencias(): array {
        $sql = "SELECT OC.IDOPCIONCOMP, OC.IDCOMPETENCIA, OC.IDOPCION,
                       OC.DESCRIPCION, OC.ACTIVO, OS.ETIQUETA, OS.VALOR
                FROM VAADINWEB.HUMOPCIONCOMPETENCIA OC
                INNER JOIN VAADINWEB.HUMOPCIONESCALA OS ON OC.IDOPCION = OS.IDOPCION
                ORDER BY OC.IDCOMPETENCIA ASC, OS.VALOR DESC";
        $res  = $this->ejecutarConsulta($sql);
        $rows = [];
        if ($res) {
            while ($r = oci_fetch_assoc($res)) {
                $r['DESCRIPCION'] = fromOracleEncoding($r['DESCRIPCION'] ?? '');
                $r['ETIQUETA']    = fromOracleEncoding($r['ETIQUETA']    ?? '');
                $idComp = (int)$r['IDCOMPETENCIA'];
                if (!isset($rows[$idComp])) $rows[$idComp] = [];
                $rows[$idComp][] = $r;
            }
            oci_free_statement($res);
        }
        return $rows;
    }

    /** Edita la descripción de una opción de competencia */
    public function editarOpcionCompetencia(int $idOpcionComp, string $descripcion): bool {
        $id  = $idOpcionComp;
        $txt = $this->toOracle($descripcion);
        $sql = "UPDATE VAADINWEB.HUMOPCIONCOMPETENCIA SET DESCRIPCION = :txt WHERE IDOPCIONCOMP = :id";
        $conn = $this->conectar();
        $q    = oci_parse($conn, $sql);
        oci_bind_by_name($q, ':txt', $txt, 1000);
        oci_bind_by_name($q, ':id',  $id);
        $ok = oci_execute($q, OCI_COMMIT_ON_SUCCESS);
        oci_free_statement($q);
        return (bool)$ok;
    }


    /** Activa o desactiva un objetivo */
    public function toggleObjetivo($idObjetivo, $activo) {
        $idObjetivo = (int)$idObjetivo;
        $activo     = (int)$activo;
        $sql   = "UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET ACTIVO = :activo WHERE IDOBJETIVO = :id";
        $conn  = $this->conectar();
        $query = oci_parse($conn, $sql);
        oci_bind_by_name($query, ':activo', $activo);
        oci_bind_by_name($query, ':id',     $idObjetivo);
        $ok = oci_execute($query, OCI_COMMIT_ON_SUCCESS);
        oci_free_statement($query);
        return $ok;
    }

    // ══════════════════════════════════════════════════════════════════

    /** Actualizar fecha de última notificación enviada */
    public function actualizarUltimaNotif($idEmpleado) {
        $idEmpleado = (int)$idEmpleado;
        $sql = "UPDATE VAADINWEB.HUMUSUARIOS HU
                SET HU.ULTIMA_NOTIF = SYSDATE
                WHERE HU.IDENTIFICACION = (
                    SELECT EM.IDENTIFICACION FROM ZAYMAWEB.GHEMPEMPLEADOS EM
                    WHERE EM.IDEMPLEADO = $idEmpleado
                )";
        $conn  = $this->conectar();
        $query = oci_parse($conn, $sql);
        $ok    = oci_execute($query, OCI_COMMIT_ON_SUCCESS);
        oci_free_statement($query);
        return $ok;
    }

    // ══════════════════════════════════════════════════════════════════
    // CONTROL MÓDULO FEEDBACK
    // ══════════════════════════════════════════════════════════════════

    /** Lee si el módulo de Feedback está habilitado */
    public function getFeedbackActivo(): bool {
        $sql = "SELECT VALOR FROM VAADINWEB.HUMCONFIGMEJORA
                WHERE PARAMETRO = 'FEEDBACK_ACTIVO' AND ACTIVO = 1 AND ROWNUM = 1";
        $res = $this->ejecutarConsulta($sql);
        $row = $res ? oci_fetch_assoc($res) : null;
        if ($res) oci_free_statement($res);
        return (int)($row['VALOR'] ?? 0) === 1;
    }

    /** Activa o desactiva el módulo de Feedback (0 = deshabilitado, 1 = habilitado) */
    public function setFeedbackActivo(int $valor): bool {
        $valor = $valor ? 1 : 0;
        $conn  = $this->conectar();
        $sql   = "UPDATE VAADINWEB.HUMCONFIGMEJORA
                  SET VALOR = :valor
                  WHERE PARAMETRO = 'FEEDBACK_ACTIVO' AND ACTIVO = 1";
        $query = oci_parse($conn, $sql);
        oci_bind_by_name($query, ':valor', $valor);
        $ok = oci_execute($query, OCI_COMMIT_ON_SUCCESS);
        oci_free_statement($query);
        return $ok;
    }

    /** Lee un parámetro de HUMCONFIGMEJORA. Devuelve $default si no existe. */
    public function getConfigParam(string $parametro, string $default = ''): string {
        $par = addslashes($parametro);
        $sql = "SELECT VALOR FROM VAADINWEB.HUMCONFIGMEJORA
                WHERE PARAMETRO = '$par' AND ACTIVO = 1 AND ROWNUM = 1";
        $res = $this->ejecutarConsulta($sql);
        $row = $res ? oci_fetch_assoc($res) : null;
        if ($res) oci_free_statement($res);
        return $row ? (string)($row['VALOR'] ?? $default) : $default;
    }

    /** Actualiza o inserta un parámetro en HUMCONFIGMEJORA (UPSERT). */
    public function setConfigParam(string $parametro, string $valor): bool {
        $conn = $this->conectar();
        $sql  = "BEGIN
                   UPDATE VAADINWEB.HUMCONFIGMEJORA SET VALOR = :val
                   WHERE PARAMETRO = :par AND ACTIVO = 1;
                   IF SQL%ROWCOUNT = 0 THEN
                     INSERT INTO VAADINWEB.HUMCONFIGMEJORA (PARAMETRO, VALOR, ACTIVO)
                     VALUES (:par, :val, 1);
                   END IF;
                 END;";
        $q = oci_parse($conn, $sql);
        oci_bind_by_name($q, ':par', $parametro);
        oci_bind_by_name($q, ':val', $valor);
        $ok = oci_execute($q, OCI_COMMIT_ON_SUCCESS);
        oci_free_statement($q);
        return $ok;
    }

    /** Devuelve el nombre completo de un colaborador para mensajes de error/UI */
    public function getNombreColaborador($idempleado): string {
        $idempleado = (int)$idempleado;
        $sql = "SELECT NVL(
                    TRIM(GE.PNOMBRE||' '||NVL(GE.SNOMBRE||' ','')||GE.PAPELLIDO||NVL(' '||GE.SAPELLIDO,'')),
                    HE.NOMBRE
                ) AS NOMBRE
                FROM VAADINWEB.HUMEMPLEADOEVAL HE
                LEFT JOIN ZAYMAWEB.GHEMPEMPLEADOS GE ON HE.IDEMPLEADO = GE.IDEMPLEADO
                WHERE HE.IDEMPLEADO = $idempleado AND HE.ACTIVO = 1 AND ROWNUM = 1";
        $res = $this->ejecutarConsulta($sql);
        $row = $res ? oci_fetch_assoc($res) : null;
        if ($res) oci_free_statement($res);
        return fromOracleEncoding(trim($row['NOMBRE'] ?? ''));
    }

    /** Obtener líder directo de un colaborador para notificaciones */
    public function getEmailColaborador($idempleado): string {
        $idempleado = (int)$idempleado;
        // Prioridad 1: email en HUMEMPLEADOEVAL (más actualizado)
        // Prioridad 2: email en GHEMPEMPLEADOS (nómina)
        $sql = "SELECT NVL(HE.EMAIL, GE.EMAIL) AS EMAIL
                FROM VAADINWEB.HUMEMPLEADOEVAL HE
                LEFT JOIN ZAYMAWEB.GHEMPEMPLEADOS GE ON HE.IDEMPLEADO = GE.IDEMPLEADO
                WHERE HE.IDEMPLEADO = $idempleado AND HE.ACTIVO = 1 AND ROWNUM = 1";
        $res = $this->ejecutarConsulta($sql);
        $row = $res ? oci_fetch_assoc($res) : null;
        if ($res) oci_free_statement($res);
        return trim($row['EMAIL'] ?? '');
    }

    public function getLiderDeColaborador($idempleado) {
        $idempleado = (int)$idempleado;
        $sql = "
            SELECT HE_L.IDEMPLEADO, HE_L.NOMBRE AS EMPLEADO, HE_L.CARGO,
                   NVL(HE_L.EMAIL, GE.EMAIL) AS EMAIL
            FROM VAADINWEB.HUMEMPLEADOEVAL HE_C
            JOIN VAADINWEB.HUMEMPLEADOEVAL HE_L
                ON HE_C.IDEMPLEADO_EVAL = HE_L.IDEMPLEADO
            LEFT JOIN ZAYMAWEB.GHEMPEMPLEADOS GE ON HE_L.IDEMPLEADO = GE.IDEMPLEADO
            WHERE HE_C.IDEMPLEADO = $idempleado
              AND HE_C.ACTIVO = 1 AND HE_L.ACTIVO = 1
              AND ROWNUM = 1
        ";
        $res = $this->ejecutarConsulta($sql);
        $row = $res ? oci_fetch_assoc($res) : null;
        if ($row) {
            $row['EMPLEADO'] = fromOracleEncoding($row['EMPLEADO'] ?? '');
            $row['CARGO']    = fromOracleEncoding($row['CARGO'] ?? '');
            if ($res) oci_free_statement($res);
        }
        return $row;
    }
}
?>
