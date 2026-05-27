<?php
namespace app\models;
use app\models\mainModel;

class analiticaModel extends mainModel {

    private function enc(array $row, array $campos): array {
        foreach ($campos as $c) {
            if (isset($row[$c]) && is_string($row[$c]))
                $row[$c] = fromOracleEncoding($row[$c]);
        }
        return $row;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // DASHBOARD — KPIs GLOBALES
    // ══════════════════════════════════════════════════════════════════════════

    public function getKPIsGlobales(?array $periodo): array {
        // $filtroSubq: para subqueries sin alias de tabla (HUMRESPUESTA sin alias)
        // $filtroR:    para queries con alias R = HUMRESPUESTA
        $idP        = $periodo ? (int)$periodo['IDPERIODO'] : 0;
        $filtroSubq = $idP ? "AND IDPERIODO = $idP"   : '';
        $filtroR    = $idP ? "AND R.IDPERIODO = $idP" : '';

        $sql = "
            SELECT
                COUNT(DISTINCT E.IDEMPLEADO)                                   AS TOTAL_EMPLEADOS,
                COUNT(DISTINCT A.IDEMPLEADO)                                   AS CON_AUTOEVAL,
                COUNT(DISTINCT LC.IDEMPLEADO)                                  AS EVALUADOS_LIDER,
                COUNT(DISTINCT CL.IDEMPLEADO)                                  AS EVALUARON_LIDER,
                ROUND(COUNT(DISTINCT A.IDEMPLEADO)  * 100.0
                      / NULLIF(COUNT(DISTINCT E.IDEMPLEADO), 0), 1)            AS PCT_AUTOEVAL,
                ROUND(COUNT(DISTINCT LC.IDEMPLEADO) * 100.0
                      / NULLIF(COUNT(DISTINCT E.IDEMPLEADO), 0), 1)            AS PCT_EVALUADOS,
                COUNT(DISTINCT CASE
                    WHEN A.IDEMPLEADO IS NOT NULL AND LC.IDEMPLEADO IS NOT NULL
                    THEN E.IDEMPLEADO END)                                      AS COMPLETOS,
                COUNT(DISTINCT CASE
                    WHEN A.IDEMPLEADO IS NOT NULL AND LC.IDEMPLEADO IS NULL
                    THEN E.IDEMPLEADO END)                                      AS EN_PROGRESO,
                COUNT(DISTINCT CASE
                    WHEN A.IDEMPLEADO IS NULL
                    THEN E.IDEMPLEADO END)                                      AS SIN_INICIAR
            FROM VAADINWEB.HUMEMPLEADOEVAL E
            LEFT JOIN (
                SELECT DISTINCT IDEMPLEADO FROM VAADINWEB.HUMRESPUESTA
                WHERE TIPO_EVAL = 'AUTO' AND CONFIRMADO = 1 $filtroSubq
            ) A  ON E.IDEMPLEADO = A.IDEMPLEADO
            LEFT JOIN (
                SELECT DISTINCT IDEMPLEADO FROM VAADINWEB.HUMRESPUESTA
                WHERE TIPO_EVAL = 'LIDER_A_COLAB' AND CONFIRMADO = 1 $filtroSubq
            ) LC ON E.IDEMPLEADO = LC.IDEMPLEADO
            LEFT JOIN (
                SELECT DISTINCT IDEMPLEADO FROM VAADINWEB.HUMRESPUESTA
                WHERE TIPO_EVAL = 'COLAB_A_LIDER' AND CONFIRMADO = 1 $filtroSubq
            ) CL ON E.IDEMPLEADO = CL.IDEMPLEADO
            WHERE E.ACTIVO = 1";

        $res = $this->ejecutarConsulta($sql);
        $row = $res ? oci_fetch_assoc($res) : [];
        if ($res) oci_free_statement($res);

        // Promedio global separado (evita product cartesiano)
        $sqlProm = "SELECT ROUND(AVG(O.VALOR), 2) AS PROM
                    FROM VAADINWEB.HUMRESPUESTA R
                    INNER JOIN VAADINWEB.HUMOPCIONESCALA O ON R.IDOPCION = O.IDOPCION
                    WHERE R.TIPO_EVAL = 'LIDER_A_COLAB' AND R.CONFIRMADO = 1 $filtroR";
        $resProm = $this->ejecutarConsulta($sqlProm);
        $rowProm = $resProm ? oci_fetch_assoc($resProm) : null;
        if ($resProm) oci_free_statement($resProm);

        $row['PROMEDIO_GLOBAL'] = $rowProm ? (float)str_replace(',', '.', $rowProm['PROM'] ?? 0) : 0;

        // Acuerdos
        $filtroPA = $periodo ? "WHERE IDPERIODO = " . (int)$periodo['IDPERIODO'] : "WHERE 1=1";
        $sqlAc = "SELECT
                    COUNT(*) AS TOTAL,
                    SUM(CASE WHEN ESTADO='PENDIENTE'  THEN 1 ELSE 0 END) AS PENDIENTES,
                    SUM(CASE WHEN ESTADO='RESPONDIDO' THEN 1 ELSE 0 END) AS RESPONDIDOS,
                    SUM(CASE WHEN ESTADO='APROBADO'   THEN 1 ELSE 0 END) AS APROBADOS
                  FROM VAADINWEB.HUMACUERDOMEJORA $filtroPA";
        $resAc = $this->ejecutarConsulta($sqlAc);
        $rowAc = $resAc ? oci_fetch_assoc($resAc) : [];
        if ($resAc) oci_free_statement($resAc);

        $row['ACUERDOS_TOTAL']     = (int)($rowAc['TOTAL']       ?? 0);
        $row['ACUERDOS_PENDIENTES']= (int)($rowAc['PENDIENTES']  ?? 0);
        $row['ACUERDOS_RESPONDIDOS']=(int)($rowAc['RESPONDIDOS'] ?? 0);
        $row['ACUERDOS_APROBADOS'] = (int)($rowAc['APROBADOS']   ?? 0);

        return $row;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // EVOLUCIÓN HISTÓRICA — Multi-período
    // ══════════════════════════════════════════════════════════════════════════

    public function getEvolucionHistorica(int $n = 6): array {
        $sql = "
            SELECT *
            FROM (
                SELECT
                    P.IDPERIODO,
                    P.NOMBRE                             AS PERIODO_LABEL,
                    TO_CHAR(P.FECHAAPERTURA,'MM/YYYY')   AS FECHA_CORTA,
                    ROUND(AVG(CASE WHEN R.TIPO_EVAL='LIDER_A_COLAB'
                                   THEN O.VALOR END), 2) AS PROM_DESEMPENO,
                    ROUND(AVG(CASE WHEN R.TIPO_EVAL='COLAB_A_LIDER'
                                   THEN O.VALOR END), 2) AS PROM_LIDERAZGO,
                    ROUND(AVG(CASE WHEN R.TIPO_EVAL='AUTO'
                                   THEN O.VALOR END), 2) AS PROM_AUTOEVAL,
                    COUNT(DISTINCT R.IDEMPLEADO)          AS TOTAL_PARTICIPANTES
                FROM VAADINWEB.HUMRESPUESTA R
                INNER JOIN VAADINWEB.HUMPERIODOEVALUACION P ON R.IDPERIODO = P.IDPERIODO
                INNER JOIN VAADINWEB.HUMOPCIONESCALA      O ON R.IDOPCION  = O.IDOPCION
                WHERE R.CONFIRMADO = 1
                GROUP BY P.IDPERIODO, P.NOMBRE, P.FECHAAPERTURA
                ORDER BY P.FECHAAPERTURA DESC
            )
            WHERE ROWNUM <= $n
            ORDER BY IDPERIODO ASC";

        $res  = $this->ejecutarConsulta($sql);
        $rows = [];
        if ($res) {
            while ($r = oci_fetch_assoc($res)) {
                $r['PERIODO_LABEL']    = fromOracleEncoding($r['PERIODO_LABEL'] ?? '');
                $r['PROM_DESEMPENO']   = (float)str_replace(',', '.', $r['PROM_DESEMPENO']  ?? 0);
                $r['PROM_LIDERAZGO']   = (float)str_replace(',', '.', $r['PROM_LIDERAZGO']  ?? 0);
                $r['PROM_AUTOEVAL']    = (float)str_replace(',', '.', $r['PROM_AUTOEVAL']    ?? 0);
                $rows[] = $r;
            }
            oci_free_statement($res);
        }
        return $rows;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // RANKING DE COMPETENCIAS
    // ══════════════════════════════════════════════════════════════════════════

    public function getRankingCompetencias(?array $periodo, string $proceso = '', string $tipoEval = 'LIDER_A_COLAB'): array {
        $idP       = $periodo ? (int)$periodo['IDPERIODO'] : 0;
        $filtroP   = $idP    ? "AND R.IDPERIODO = $idP"  : '';
        $tiposSafe = ['LIDER_A_COLAB','COLAB_A_LIDER','AUTO'];
        $tipo      = in_array($tipoEval, $tiposSafe) ? $tipoEval : 'LIDER_A_COLAB';

        $conn  = $this->conectar();
        $filtroPro = $proceso ? "AND E.PROCESO = :proceso" : '';

        $sql = "
            SELECT
                C.NUM_PREGUNTA,
                C.NOMBRE                              AS NOMBRE_COMP,
                D.NOMBRE                              AS DIMENSION,
                ROUND(AVG(O.VALOR), 2)                AS PROMEDIO,
                MIN(O.VALOR)                          AS MINIMO,
                MAX(O.VALOR)                          AS MAXIMO,
                COUNT(DISTINCT R.IDEMPLEADO)          AS TOTAL_EVALUADOS,
                ROUND(SUM(CASE WHEN O.VALOR <= 2 THEN 1 ELSE 0 END) * 100.0
                      / NULLIF(COUNT(*), 0), 1)       AS PCT_BAJO,
                ROUND(SUM(CASE WHEN O.VALOR  = 5 THEN 1 ELSE 0 END) * 100.0
                      / NULLIF(COUNT(*), 0), 1)       AS PCT_EXCELENTE
            FROM VAADINWEB.HUMRESPUESTA R
            INNER JOIN VAADINWEB.HUMCOMPETENCIA   C ON R.IDCOMPETENCIA = C.IDCOMPETENCIA
            INNER JOIN VAADINWEB.HUMDIMENSION     D ON C.IDDIMENSION   = D.IDDIMENSION
            INNER JOIN VAADINWEB.HUMOPCIONESCALA  O ON R.IDOPCION      = O.IDOPCION
            INNER JOIN VAADINWEB.HUMEMPLEADOEVAL  E ON R.IDEMPLEADO    = E.IDEMPLEADO
            WHERE R.CONFIRMADO = 1
              AND R.TIPO_EVAL  = '$tipo'
              AND E.ACTIVO     = 1
              $filtroP
              $filtroPro
            GROUP BY C.NUM_PREGUNTA, C.NOMBRE, D.NOMBRE
            ORDER BY PROMEDIO DESC";

        $query = oci_parse($conn, $sql);
        if ($proceso) oci_bind_by_name($query, ':proceso', $proceso);
        oci_execute($query);
        $rows = [];
        while ($r = oci_fetch_assoc($query)) {
            $r['NOMBRE_COMP'] = fromOracleEncoding($r['NOMBRE_COMP'] ?? '');
            $r['DIMENSION']   = fromOracleEncoding($r['DIMENSION']   ?? '');
            $r['PROMEDIO']    = (float)str_replace(',', '.', $r['PROMEDIO'] ?? 0);
            $r['PCT_BAJO']    = (float)str_replace(',', '.', $r['PCT_BAJO'] ?? 0);
            $r['PCT_EXCELENTE']=(float)str_replace(',', '.', $r['PCT_EXCELENTE'] ?? 0);
            $rows[] = $r;
        }
        oci_free_statement($query);
        return $rows;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // RESUMEN POR PROCESO
    // ══════════════════════════════════════════════════════════════════════════

    public function getResumenPorProceso(?array $periodo): array {
        $idP     = $periodo ? (int)$periodo['IDPERIODO'] : 0;
        $filtroP = $idP ? "AND IDPERIODO = $idP"    : '';
        $filtroAc= $idP ? "AND AC.IDPERIODO = $idP" : '';

        $sql = "
            SELECT
                E.PROCESO,
                COUNT(DISTINCT E.IDEMPLEADO)                              AS TOTAL,
                COUNT(DISTINCT A.IDEMPLEADO)                              AS CON_AUTOEVAL,
                COUNT(DISTINCT LC.IDEMPLEADO)                             AS EVALUADOS,
                ROUND(COUNT(DISTINCT A.IDEMPLEADO)  * 100.0
                      / NULLIF(COUNT(DISTINCT E.IDEMPLEADO), 0), 1)       AS PCT_AUTOEVAL,
                ROUND(COUNT(DISTINCT LC.IDEMPLEADO) * 100.0
                      / NULLIF(COUNT(DISTINCT E.IDEMPLEADO), 0), 1)       AS PCT_EVALUADOS,
                NVL(AC_S.ACUERDOS_PEND, 0)                                AS ACUERDOS_PEND,
                NVL(AC_S.ACUERDOS_APRO, 0)                                AS ACUERDOS_APRO
            FROM VAADINWEB.HUMEMPLEADOEVAL E
            LEFT JOIN (
                SELECT DISTINCT IDEMPLEADO FROM VAADINWEB.HUMRESPUESTA
                WHERE TIPO_EVAL = 'AUTO' AND CONFIRMADO = 1 $filtroP
            ) A  ON E.IDEMPLEADO = A.IDEMPLEADO
            LEFT JOIN (
                SELECT DISTINCT IDEMPLEADO FROM VAADINWEB.HUMRESPUESTA
                WHERE TIPO_EVAL = 'LIDER_A_COLAB' AND CONFIRMADO = 1 $filtroP
            ) LC ON E.IDEMPLEADO = LC.IDEMPLEADO
            LEFT JOIN (
                SELECT
                    HE2.PROCESO,
                    SUM(CASE WHEN AC.ESTADO='PENDIENTE' THEN 1 ELSE 0 END) AS ACUERDOS_PEND,
                    SUM(CASE WHEN AC.ESTADO='APROBADO'  THEN 1 ELSE 0 END) AS ACUERDOS_APRO
                FROM VAADINWEB.HUMACUERDOMEJORA AC
                INNER JOIN VAADINWEB.HUMEMPLEADOEVAL HE2 ON AC.IDEMPLEADO = HE2.IDEMPLEADO
                WHERE 1=1 $filtroAc
                GROUP BY HE2.PROCESO
            ) AC_S ON E.PROCESO = AC_S.PROCESO
            WHERE E.ACTIVO = 1 AND E.PROCESO IS NOT NULL
            GROUP BY E.PROCESO, AC_S.ACUERDOS_PEND, AC_S.ACUERDOS_APRO
            ORDER BY PCT_EVALUADOS DESC NULLS LAST, E.PROCESO ASC";

        $res  = $this->ejecutarConsulta($sql);
        $rows = [];
        if ($res) {
            while ($r = oci_fetch_assoc($res)) {
                $r['PROCESO'] = fromOracleEncoding($r['PROCESO'] ?? '');
                $rows[] = $r;
            }
            oci_free_statement($res);
        }
        return $rows;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // ALERTAS ADMINISTRATIVAS
    // ══════════════════════════════════════════════════════════════════════════

    public function getAlertas(?array $periodo): array {
        $idP    = $periodo ? (int)$periodo['IDPERIODO'] : 0;
        $alertas = ['sin_autoeval' => [], 'acuerdos_vencidos' => [], 'lideres_sin_evaluar' => []];

        // 1. Sin autoevaluación
        if ($idP) {
            $sql = "
                SELECT E.IDEMPLEADO, E.NOMBRE AS COLABORADOR, E.CARGO, E.PROCESO,
                       TO_CHAR(HU.ULTIMA_NOTIF,'DD/MM/YYYY HH24:MI') AS ULTIMA_NOTIF,
                       NVL(EL.PNOMBRE||' '||EL.PAPELLIDO, HL.NOMBRE) AS LIDER
                FROM VAADINWEB.HUMEMPLEADOEVAL E
                LEFT JOIN VAADINWEB.HUMUSUARIOS HU
                    ON TRIM(HU.IDENTIFICACION) = TRIM(E.IDENTIFICACION)
                LEFT JOIN VAADINWEB.HUMEMPLEADOEVAL HL ON E.IDEMPLEADO_EVAL = HL.IDEMPLEADO
                LEFT JOIN ZAYMAWEB.GHEMPEMPLEADOS EL   ON HL.IDEMPLEADO     = EL.IDEMPLEADO
                WHERE E.ACTIVO = 1
                  AND E.IDEMPLEADO NOT IN (
                      SELECT DISTINCT IDEMPLEADO FROM VAADINWEB.HUMRESPUESTA
                      WHERE TIPO_EVAL = 'AUTO' AND CONFIRMADO = 1 AND IDPERIODO = $idP
                  )
                ORDER BY E.PROCESO ASC, E.NOMBRE ASC";
            $res = $this->ejecutarConsulta($sql);
            if ($res) {
                while ($r = oci_fetch_assoc($res))
                    $alertas['sin_autoeval'][] = $this->enc($r, ['COLABORADOR','CARGO','PROCESO','LIDER']);
                oci_free_statement($res);
            }
        }

        // 2. Acuerdos vencidos > 15 días sin respuesta
        $filtroIdP = $idP ? "AND A.IDPERIODO = $idP" : '';
        $sql2 = "
            SELECT
                A.IDACUERDO,
                TRUNC(SYSDATE - A.FECHA_ASIGNACION)    AS DIAS_PENDIENTE,
                NVL(EC.PNOMBRE||' '||EC.PAPELLIDO, HE.NOMBRE) AS COLABORADOR,
                HE.CARGO, HE.PROCESO,
                NVL(EL.PNOMBRE||' '||EL.PAPELLIDO, HL.NOMBRE) AS LIDER
            FROM VAADINWEB.HUMACUERDOMEJORA A
            INNER JOIN VAADINWEB.HUMEMPLEADOEVAL HE  ON A.IDEMPLEADO       = HE.IDEMPLEADO
            INNER JOIN VAADINWEB.HUMEMPLEADOEVAL HL  ON A.IDEMPLEADO_LIDER = HL.IDEMPLEADO
            LEFT  JOIN ZAYMAWEB.GHEMPEMPLEADOS EC    ON HE.IDEMPLEADO = EC.IDEMPLEADO
            LEFT  JOIN ZAYMAWEB.GHEMPEMPLEADOS EL    ON HL.IDEMPLEADO = EL.IDEMPLEADO
            WHERE A.ESTADO = 'PENDIENTE'
              AND TRUNC(SYSDATE - A.FECHA_ASIGNACION) > 15
              $filtroIdP
            ORDER BY DIAS_PENDIENTE DESC";
        $res2 = $this->ejecutarConsulta($sql2);
        if ($res2) {
            while ($r = oci_fetch_assoc($res2))
                $alertas['acuerdos_vencidos'][] = $this->enc($r, ['COLABORADOR','CARGO','PROCESO','LIDER']);
            oci_free_statement($res2);
        }

        // 3. Líderes sin evaluar a su equipo
        if ($idP) {
            $sql3 = "
                SELECT E.IDEMPLEADO, E.NOMBRE AS LIDER, E.CARGO, E.PROCESO,
                       COUNT(SUB.IDEMPLEADO) AS PENDIENTES_EVALUAR
                FROM VAADINWEB.HUMEMPLEADOEVAL E
                INNER JOIN VAADINWEB.HUMEMPLEADOEVAL SUB
                    ON SUB.IDEMPLEADO_EVAL = E.IDEMPLEADO AND SUB.ACTIVO = 1
                WHERE E.ES_LIDER_FUNCIONAL = 1 AND E.ACTIVO = 1
                  AND SUB.IDEMPLEADO NOT IN (
                      SELECT DISTINCT IDEMPLEADO FROM VAADINWEB.HUMRESPUESTA
                      WHERE TIPO_EVAL = 'LIDER_A_COLAB' AND CONFIRMADO = 1
                        AND IDEMPLEADO_EVAL = E.IDEMPLEADO AND IDPERIODO = $idP
                  )
                GROUP BY E.IDEMPLEADO, E.NOMBRE, E.CARGO, E.PROCESO
                HAVING COUNT(SUB.IDEMPLEADO) > 0
                ORDER BY PENDIENTES_EVALUAR DESC, E.NOMBRE ASC";
            $res3 = $this->ejecutarConsulta($sql3);
            if ($res3) {
                while ($r = oci_fetch_assoc($res3))
                    $alertas['lideres_sin_evaluar'][] = $this->enc($r, ['LIDER','CARGO','PROCESO']);
                oci_free_statement($res3);
            }
        }

        return $alertas;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // ACUERDOS — Trazabilidad completa
    // ══════════════════════════════════════════════════════════════════════════

    public function getAcuerdosDetalle(?array $periodo, string $estado = '', int $page = 1): array {
        $idP     = $periodo ? (int)$periodo['IDPERIODO'] : 0;
        $filtroP = $idP    ? "AND A.IDPERIODO = $idP" : '';

        $estadosSafe = ['PENDIENTE','RESPONDIDO','APROBADO'];
        $filtroE = ($estado && in_array($estado, $estadosSafe)) ? "AND A.ESTADO = '$estado'" : '';

        $offset = max(0, ($page - 1) * 40);

        $sql = "
            SELECT * FROM (
                SELECT t.*, ROWNUM AS RN FROM (
                    SELECT
                        A.IDACUERDO,
                        A.ESTADO,
                        TO_CHAR(A.FECHA_ASIGNACION,'DD/MM/YYYY') AS FECHA_ASIG,
                        TO_CHAR(A.FECHA_RESPUESTA, 'DD/MM/YYYY') AS FECHA_RESP,
                        TO_CHAR(A.FECHA_APROBACION,'DD/MM/YYYY') AS FECHA_APRO,
                        TRUNC(NVL(A.FECHA_RESPUESTA,SYSDATE) - A.FECHA_ASIGNACION) AS DIAS_RESP,
                        A.NUM_COMPETENCIA,
                        A.PLAN_ACCION,
                        O.OBJETIVO,
                        O.CALIFICACION,
                        O.MODELO,
                        O.INDICADOR,
                        O.META,
                        O.PLAZO,
                        O.EVIDENCIA,
                        O.SEGUIMIENTO,
                        O.USO_RECOMENDADO,
                        C.NOMBRE AS NOMBRE_COMP,
                        NVL(EC.PNOMBRE||' '||EC.PAPELLIDO, HE.NOMBRE) AS COLABORADOR,
                        HE.IDEMPLEADO,
                        HE.CARGO,
                        HE.PROCESO,
                        NVL(EL.PNOMBRE||' '||EL.PAPELLIDO, HL.NOMBRE) AS LIDER,
                        P.NOMBRE AS NOMBRE_PERIODO
                    FROM VAADINWEB.HUMACUERDOMEJORA A
                    INNER JOIN VAADINWEB.HUMOBJETIVOMEJORA   O  ON A.IDOBJETIVO       = O.IDOBJETIVO
                    INNER JOIN VAADINWEB.HUMPERIODOEVALUACION P  ON A.IDPERIODO        = P.IDPERIODO
                    INNER JOIN VAADINWEB.HUMEMPLEADOEVAL     HE  ON A.IDEMPLEADO       = HE.IDEMPLEADO
                    INNER JOIN VAADINWEB.HUMEMPLEADOEVAL     HL  ON A.IDEMPLEADO_LIDER = HL.IDEMPLEADO
                    LEFT  JOIN VAADINWEB.HUMCOMPETENCIA      C   ON A.NUM_COMPETENCIA  = C.NUM_PREGUNTA
                    LEFT  JOIN ZAYMAWEB.GHEMPEMPLEADOS       EC  ON HE.IDEMPLEADO      = EC.IDEMPLEADO
                    LEFT  JOIN ZAYMAWEB.GHEMPEMPLEADOS       EL  ON HL.IDEMPLEADO      = EL.IDEMPLEADO
                    WHERE 1=1 $filtroP $filtroE
                    ORDER BY A.FECHA_ASIGNACION DESC
                ) t WHERE ROWNUM <= " . ($offset + 40) . "
            ) WHERE RN > $offset";

        $res  = $this->ejecutarConsulta($sql);
        $rows = [];
        if ($res) {
            while ($r = oci_fetch_assoc($res))
                $rows[] = $this->enc($r, [
                    'OBJETIVO','COLABORADOR','CARGO','PROCESO','LIDER','NOMBRE_PERIODO','PLAN_ACCION',
                    'MODELO','INDICADOR','META','PLAZO','EVIDENCIA','SEGUIMIENTO','USO_RECOMENDADO','NOMBRE_COMP'
                ]);
            oci_free_statement($res);
        }
        return $rows;
    }

    public function countAcuerdos(?array $periodo, string $estado = ''): int {
        $idP     = $periodo ? (int)$periodo['IDPERIODO'] : 0;
        $filtroP = $idP ? "AND IDPERIODO = $idP" : '';
        $estadosSafe = ['PENDIENTE','RESPONDIDO','APROBADO'];
        $filtroE = ($estado && in_array($estado, $estadosSafe)) ? "AND ESTADO = '$estado'" : '';
        $sql = "SELECT COUNT(*) AS CNT FROM VAADINWEB.HUMACUERDOMEJORA WHERE 1=1 $filtroP $filtroE";
        $res = $this->ejecutarConsulta($sql);
        $row = $res ? oci_fetch_assoc($res) : null;
        if ($res) oci_free_statement($res);
        return (int)($row['CNT'] ?? 0);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // PERFIL INDIVIDUAL — Para modal AJAX
    // ══════════════════════════════════════════════════════════════════════════

    public function getPerfilColaborador(int $idEmpleado, ?array $periodo): array {
        $idP = $periodo ? (int)$periodo['IDPERIODO'] : 0;

        // Datos del empleado
        $sqlEmp = "
            SELECT HE.IDEMPLEADO, HE.NOMBRE, HE.CARGO, HE.PROCESO, HE.EMAIL,
                   HE.IDROL, HE.ES_LIDER_FUNCIONAL,
                   NVL(GE.PNOMBRE||' '||NVL(GE.SNOMBRE||' ','')||GE.PAPELLIDO, HE.NOMBRE) AS NOMBRE_COMPLETO,
                   NVL(HL.NOMBRE, '—') AS NOMBRE_LIDER
            FROM VAADINWEB.HUMEMPLEADOEVAL HE
            LEFT JOIN ZAYMAWEB.GHEMPEMPLEADOS GE  ON HE.IDEMPLEADO      = GE.IDEMPLEADO
            LEFT JOIN VAADINWEB.HUMEMPLEADOEVAL HL ON HE.IDEMPLEADO_EVAL = HL.IDEMPLEADO
            WHERE HE.IDEMPLEADO = $idEmpleado AND ROWNUM = 1";
        $resEmp = $this->ejecutarConsulta($sqlEmp);
        $emp    = $resEmp ? oci_fetch_assoc($resEmp) : [];
        if ($resEmp) oci_free_statement($resEmp);
        if ($emp) $emp = $this->enc($emp, ['NOMBRE','NOMBRE_COMPLETO','CARGO','PROCESO','NOMBRE_LIDER']);

        // Promedios por tipo de evaluación en el período
        $filtroIdP = $idP ? "AND R.IDPERIODO = $idP" : '';
        $sqlProm = "
            SELECT R.TIPO_EVAL,
                   ROUND(AVG(O.VALOR), 2)              AS PROMEDIO,
                   COUNT(DISTINCT R.IDEMPLEADO_EVAL)    AS EVALUADORES
            FROM VAADINWEB.HUMRESPUESTA R
            INNER JOIN VAADINWEB.HUMOPCIONESCALA O ON R.IDOPCION = O.IDOPCION
            WHERE R.IDEMPLEADO = $idEmpleado AND R.CONFIRMADO = 1 $filtroIdP
            GROUP BY R.TIPO_EVAL";
        $resProm  = $this->ejecutarConsulta($sqlProm);
        $promedios = [];
        if ($resProm) {
            while ($r = oci_fetch_assoc($resProm)) {
                $promedios[$r['TIPO_EVAL']] = [
                    'PROMEDIO'    => (float)str_replace(',', '.', $r['PROMEDIO'] ?? 0),
                    'EVALUADORES' => (int)$r['EVALUADORES'],
                ];
            }
            oci_free_statement($resProm);
        }

        // Competencias del período activo (lider_a_colab)
        $sqlComp = "
            SELECT C.NUM_PREGUNTA, C.NOMBRE AS NOMBRE_COMP, R.TIPO_EVAL,
                   ROUND(AVG(O.VALOR), 2) AS PROMEDIO
            FROM VAADINWEB.HUMRESPUESTA R
            INNER JOIN VAADINWEB.HUMCOMPETENCIA  C ON R.IDCOMPETENCIA = C.IDCOMPETENCIA
            INNER JOIN VAADINWEB.HUMOPCIONESCALA O ON R.IDOPCION      = O.IDOPCION
            WHERE R.IDEMPLEADO = $idEmpleado AND R.CONFIRMADO = 1 $filtroIdP
            GROUP BY C.NUM_PREGUNTA, C.NOMBRE, R.TIPO_EVAL
            ORDER BY R.TIPO_EVAL, C.NUM_PREGUNTA";
        $resComp  = $this->ejecutarConsulta($sqlComp);
        $competencias = [];
        if ($resComp) {
            while ($r = oci_fetch_assoc($resComp)) {
                $r['NOMBRE_COMP'] = fromOracleEncoding($r['NOMBRE_COMP'] ?? '');
                $r['PROMEDIO']    = (float)str_replace(',', '.', $r['PROMEDIO'] ?? 0);
                $competencias[]   = $r;
            }
            oci_free_statement($resComp);
        }

        // Respuestas con justificación (evaluación LIDER_A_COLAB)
        $sqlJust = "
            SELECT R.IDRESPUESTA, C.NUM_PREGUNTA, C.NOMBRE AS NOMBRE_COMP, O.VALOR,
                   J.JUSTIFICACION
            FROM VAADINWEB.HUMRESPUESTA R
            INNER JOIN VAADINWEB.HUMCOMPETENCIA      C ON R.IDCOMPETENCIA = C.IDCOMPETENCIA
            INNER JOIN VAADINWEB.HUMOPCIONESCALA     O ON R.IDOPCION      = O.IDOPCION
            INNER JOIN VAADINWEB.HUMJUSTIFICACION_V2 J ON R.IDRESPUESTA   = J.IDRESPUESTA
            WHERE R.IDEMPLEADO = $idEmpleado
              AND R.TIPO_EVAL  = 'LIDER_A_COLAB'
              AND R.CONFIRMADO = 1 $filtroIdP
            ORDER BY C.NUM_PREGUNTA ASC";
        $resJust = $this->ejecutarConsulta($sqlJust);
        $justificaciones = [];
        if ($resJust) {
            while ($r = oci_fetch_assoc($resJust)) {
                $r['NOMBRE_COMP']   = fromOracleEncoding($r['NOMBRE_COMP']   ?? '');
                $r['JUSTIFICACION'] = fromOracleEncoding($r['JUSTIFICACION'] ?? '');
                $r['VALOR']         = (int)($r['VALOR'] ?? 0);
                $justificaciones[]  = $r;
            }
            oci_free_statement($resJust);
        }

        // Estado de acuerdos
        $filtroAc = $idP ? "AND IDPERIODO = $idP" : '';
        $sqlAc = "SELECT COUNT(*) AS TOTAL,
                    SUM(CASE WHEN ESTADO='PENDIENTE'  THEN 1 ELSE 0 END) AS PENDIENTES,
                    SUM(CASE WHEN ESTADO='RESPONDIDO' THEN 1 ELSE 0 END) AS RESPONDIDOS,
                    SUM(CASE WHEN ESTADO='APROBADO'   THEN 1 ELSE 0 END) AS APROBADOS
                  FROM VAADINWEB.HUMACUERDOMEJORA
                  WHERE IDEMPLEADO = $idEmpleado $filtroAc";
        $resAc = $this->ejecutarConsulta($sqlAc);
        $acuerdos = $resAc ? oci_fetch_assoc($resAc) : [];
        if ($resAc) oci_free_statement($resAc);

        // Evolución histórica del colaborador (promedios por período)
        $sqlHist = "
            SELECT P.NOMBRE AS PERIODO_LABEL, P.IDPERIODO,
                   ROUND(AVG(CASE WHEN R.TIPO_EVAL='AUTO'          THEN O.VALOR END),2) AS PROM_AUTO,
                   ROUND(AVG(CASE WHEN R.TIPO_EVAL='LIDER_A_COLAB' THEN O.VALOR END),2) AS PROM_LIDER
            FROM VAADINWEB.HUMRESPUESTA R
            INNER JOIN VAADINWEB.HUMPERIODOEVALUACION P ON R.IDPERIODO = P.IDPERIODO
            INNER JOIN VAADINWEB.HUMOPCIONESCALA      O ON R.IDOPCION  = O.IDOPCION
            WHERE R.IDEMPLEADO = $idEmpleado AND R.CONFIRMADO = 1
            GROUP BY P.NOMBRE, P.IDPERIODO, P.FECHAAPERTURA
            ORDER BY P.FECHAAPERTURA ASC";
        $resHist = $this->ejecutarConsulta($sqlHist);
        $historial = [];
        if ($resHist) {
            while ($r = oci_fetch_assoc($resHist)) {
                $r['PERIODO_LABEL'] = fromOracleEncoding($r['PERIODO_LABEL'] ?? '');
                $r['PROM_AUTO']     = (float)str_replace(',', '.', $r['PROM_AUTO']  ?? 0);
                $r['PROM_LIDER']    = (float)str_replace(',', '.', $r['PROM_LIDER'] ?? 0);
                $historial[]        = $r;
            }
            oci_free_statement($resHist);
        }

        return [
            'empleado'        => $emp,
            'promedios'       => $promedios,
            'competencias'    => $competencias,
            'acuerdos'        => $acuerdos ?: [],
            'historial'       => $historial,
            'justificaciones' => $justificaciones,
        ];
    }

    // ══════════════════════════════════════════════════════════════════════════
    // BÚSQUEDA DE COLABORADORES (para tab individual)
    // ══════════════════════════════════════════════════════════════════════════

    public function buscarColaboradores(string $q, ?array $periodo): array {
        $idP     = $periodo ? (int)$periodo['IDPERIODO'] : 0;
        $filtroP = $idP ? "AND IDPERIODO = $idP" : '';
        // Oracle escapa comillas simples duplicándolas, no con backslash
        $term    = strtoupper(str_replace("'", "''", trim($q)));

        $sql = "
            SELECT * FROM (
                SELECT E.IDEMPLEADO, E.NOMBRE, E.CARGO, E.PROCESO,
                       CASE WHEN A.IDEMPLEADO IS NOT NULL THEN 'Si' ELSE 'No' END AS AUTOEVAL,
                       CASE WHEN L.IDEMPLEADO IS NOT NULL THEN 'Si' ELSE 'No' END AS EVALUADO_LIDER,
                       CASE
                           WHEN A.IDEMPLEADO IS NOT NULL AND L.IDEMPLEADO IS NOT NULL THEN 'completo'
                           WHEN A.IDEMPLEADO IS NOT NULL THEN 'en_progreso'
                           ELSE 'sin_iniciar'
                       END AS ESTADO
                FROM VAADINWEB.HUMEMPLEADOEVAL E
                LEFT JOIN (
                    SELECT DISTINCT IDEMPLEADO FROM VAADINWEB.HUMRESPUESTA
                    WHERE TIPO_EVAL = 'AUTO' AND CONFIRMADO = 1 $filtroP
                ) A  ON E.IDEMPLEADO = A.IDEMPLEADO
                LEFT JOIN (
                    SELECT DISTINCT IDEMPLEADO FROM VAADINWEB.HUMRESPUESTA
                    WHERE TIPO_EVAL = 'LIDER_A_COLAB' AND CONFIRMADO = 1 $filtroP
                ) L  ON E.IDEMPLEADO = L.IDEMPLEADO
                WHERE E.ACTIVO = 1
                  AND (UPPER(E.NOMBRE) LIKE '%$term%'
                    OR E.IDENTIFICACION LIKE '%$term%'
                    OR UPPER(E.CARGO)   LIKE '%$term%')
                ORDER BY E.NOMBRE ASC
            ) WHERE ROWNUM <= 20";

        $res  = $this->ejecutarConsulta($sql);
        $rows = [];
        if ($res) {
            while ($r = oci_fetch_assoc($res))
                $rows[] = $this->enc($r, ['NOMBRE','CARGO','PROCESO']);
            oci_free_statement($res);
        }
        return $rows;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // EXPORTACIÓN — datos para los nuevos tipos de CSV
    // ══════════════════════════════════════════════════════════════════════════

    public function getRankingParaExport(?array $periodo): array {
        return $this->getRankingCompetencias($periodo, '', 'LIDER_A_COLAB');
    }

    public function getAcuerdosParaExport(?array $periodo): array {
        // Sin paginación para exportar todo
        $idP     = $periodo ? (int)$periodo['IDPERIODO'] : 0;
        $filtroP = $idP ? "AND A.IDPERIODO = $idP" : '';

        $sql = "
            SELECT
                A.ESTADO,
                TO_CHAR(A.FECHA_ASIGNACION,'DD/MM/YYYY') AS FECHA_ASIG,
                TO_CHAR(A.FECHA_RESPUESTA, 'DD/MM/YYYY') AS FECHA_RESP,
                TO_CHAR(A.FECHA_APROBACION,'DD/MM/YYYY') AS FECHA_APRO,
                TRUNC(NVL(A.FECHA_RESPUESTA,SYSDATE) - A.FECHA_ASIGNACION) AS DIAS_RESP,
                A.NUM_COMPETENCIA,
                O.OBJETIVO,
                NVL(EC.PNOMBRE||' '||EC.PAPELLIDO, HE.NOMBRE) AS COLABORADOR,
                HE.CARGO, HE.PROCESO,
                NVL(EL.PNOMBRE||' '||EL.PAPELLIDO, HL.NOMBRE) AS LIDER,
                P.NOMBRE AS NOMBRE_PERIODO,
                A.PLAN_ACCION
            FROM VAADINWEB.HUMACUERDOMEJORA A
            INNER JOIN VAADINWEB.HUMOBJETIVOMEJORA   O  ON A.IDOBJETIVO       = O.IDOBJETIVO
            INNER JOIN VAADINWEB.HUMPERIODOEVALUACION P  ON A.IDPERIODO        = P.IDPERIODO
            INNER JOIN VAADINWEB.HUMEMPLEADOEVAL     HE  ON A.IDEMPLEADO       = HE.IDEMPLEADO
            INNER JOIN VAADINWEB.HUMEMPLEADOEVAL     HL  ON A.IDEMPLEADO_LIDER = HL.IDEMPLEADO
            LEFT  JOIN ZAYMAWEB.GHEMPEMPLEADOS       EC  ON HE.IDEMPLEADO      = EC.IDEMPLEADO
            LEFT  JOIN ZAYMAWEB.GHEMPEMPLEADOS       EL  ON HL.IDEMPLEADO      = EL.IDEMPLEADO
            WHERE 1=1 $filtroP
            ORDER BY A.FECHA_ASIGNACION DESC";

        $res  = $this->ejecutarConsulta($sql);
        $rows = [];
        if ($res) {
            while ($r = oci_fetch_assoc($res))
                $rows[] = $this->enc($r, ['OBJETIVO','COLABORADOR','CARGO','PROCESO','LIDER','NOMBRE_PERIODO','PLAN_ACCION']);
            oci_free_statement($res);
        }
        return $rows;
    }

    public function getAlertasParaExport(?array $periodo): array {
        $alertas = $this->getAlertas($periodo);
        $rows = [];
        foreach ($alertas['sin_autoeval'] as $r)
            $rows[] = array_merge(['TIPO' => 'Sin autoevaluación'], $r);
        foreach ($alertas['acuerdos_vencidos'] as $r)
            $rows[] = array_merge(['TIPO' => 'Acuerdo vencido'], $r);
        foreach ($alertas['lideres_sin_evaluar'] as $r)
            $rows[] = array_merge(['TIPO' => 'Líder sin evaluar'], $r);
        return $rows;
    }

    public function getProcesosParaExport(?array $periodo): array {
        return $this->getResumenPorProceso($periodo);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // V2 — LISTA DE PROCESOS (dropdown)
    // ══════════════════════════════════════════════════════════════════════════

    public function getProcesosLista(): array {
        $sql = "SELECT DISTINCT PROCESO FROM VAADINWEB.HUMEMPLEADOEVAL
                WHERE ACTIVO = 1 AND PROCESO IS NOT NULL ORDER BY PROCESO ASC";
        $res  = $this->ejecutarConsulta($sql);
        $rows = [];
        if ($res) {
            while ($r = oci_fetch_assoc($res))
                $rows[] = fromOracleEncoding($r['PROCESO'] ?? '');
            oci_free_statement($res);
        }
        return $rows;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // V2 — LISTA DE COLABORADORES con filtros y paginación
    // ══════════════════════════════════════════════════════════════════════════

    private function _buildListaFiltros(?array $periodo, array $filtros): array {
        $idP     = $periodo ? (int)$periodo['IDPERIODO'] : 0;
        $filtroP = $idP ? "AND IDPERIODO = $idP" : '';

        $nombre  = trim($filtros['nombre']  ?? '');
        $proceso = trim($filtros['proceso'] ?? '');
        $estado  = trim($filtros['estado']  ?? '');

        $filtroNombre = '';
        if ($nombre) {
            $n = strtoupper(str_replace("'", "''", $nombre));
            $filtroNombre = "AND (UPPER(E.NOMBRE) LIKE '%$n%' OR E.IDENTIFICACION LIKE '%$n%')";
        }

        $filtroProceso = '';
        if ($proceso) {
            $p = str_replace("'", "''", $proceso);
            $filtroProceso = "AND E.PROCESO = '$p'";
        }

        $estadosSafe  = ['completo', 'en_progreso', 'sin_iniciar'];
        $filtroEstado = '';
        if ($estado && in_array($estado, $estadosSafe)) {
            if ($estado === 'completo') {
                $filtroEstado = "AND A.IDEMPLEADO IS NOT NULL AND LC.IDEMPLEADO IS NOT NULL";
            } elseif ($estado === 'en_progreso') {
                $filtroEstado = "AND A.IDEMPLEADO IS NOT NULL AND LC.IDEMPLEADO IS NULL";
            } else {
                $filtroEstado = "AND A.IDEMPLEADO IS NULL";
            }
        }

        return [$filtroP, $filtroNombre, $filtroProceso, $filtroEstado];
    }

    public function countListaColaboradores(?array $periodo, array $filtros = []): int {
        [$filtroP, $filtroNombre, $filtroProceso, $filtroEstado] = $this->_buildListaFiltros($periodo, $filtros);
        $sql = "
            SELECT COUNT(*) AS CNT FROM (
                SELECT E.IDEMPLEADO
                FROM VAADINWEB.HUMEMPLEADOEVAL E
                LEFT JOIN (
                    SELECT DISTINCT IDEMPLEADO FROM VAADINWEB.HUMRESPUESTA
                    WHERE TIPO_EVAL = 'AUTO' AND CONFIRMADO = 1 $filtroP
                ) A  ON E.IDEMPLEADO = A.IDEMPLEADO
                LEFT JOIN (
                    SELECT DISTINCT IDEMPLEADO FROM VAADINWEB.HUMRESPUESTA
                    WHERE TIPO_EVAL = 'LIDER_A_COLAB' AND CONFIRMADO = 1 $filtroP
                ) LC ON E.IDEMPLEADO = LC.IDEMPLEADO
                WHERE E.ACTIVO = 1 $filtroNombre $filtroProceso $filtroEstado
            )";
        $res = $this->ejecutarConsulta($sql);
        $row = $res ? oci_fetch_assoc($res) : null;
        if ($res) oci_free_statement($res);
        return (int)($row['CNT'] ?? 0);
    }

    public function getListaColaboradores(?array $periodo, array $filtros = [], int $page = 1): array {
        [$filtroP, $filtroNombre, $filtroProceso, $filtroEstado] = $this->_buildListaFiltros($periodo, $filtros);
        $offset = max(0, ($page - 1) * 30);

        $sql = "
            SELECT * FROM (
                SELECT t.*, ROWNUM AS RN FROM (
                    SELECT
                        E.IDEMPLEADO, E.NOMBRE, E.CARGO, E.PROCESO,
                        NVL(TRIM(GE.PNOMBRE||' '||NVL(GE.SNOMBRE||' ','')||GE.PAPELLIDO||NVL(' '||GE.SAPELLIDO,'')), E.NOMBRE) AS NOMBRE_COMPLETO,
                        NVL(HL.NOMBRE, '—') AS NOMBRE_LIDER,
                        CASE WHEN A.IDEMPLEADO  IS NOT NULL THEN 1 ELSE 0 END AS TIENE_AUTO,
                        CASE WHEN LC.IDEMPLEADO IS NOT NULL THEN 1 ELSE 0 END AS TIENE_LIDER,
                        CASE WHEN CL.IDEMPLEADO IS NOT NULL THEN 1 ELSE 0 END AS EVALUO_LIDER,
                        CASE
                            WHEN A.IDEMPLEADO IS NOT NULL AND LC.IDEMPLEADO IS NOT NULL THEN 'completo'
                            WHEN A.IDEMPLEADO IS NOT NULL THEN 'en_progreso'
                            ELSE 'sin_iniciar'
                        END AS ESTADO,
                        NVL(AC_S.TOTAL_AC, 0) AS TOTAL_ACUERDOS,
                        NVL(AC_S.PEND_AC,  0) AS ACUERDOS_PEND,
                        NVL(AC_S.APRO_AC,  0) AS ACUERDOS_APRO,
                        ROUND(NVL(PROM_L.PROM, 0), 2) AS PROM_LIDER,
                        ROUND(NVL(PROM_A.PROM, 0), 2) AS PROM_AUTO
                    FROM VAADINWEB.HUMEMPLEADOEVAL E
                    LEFT JOIN ZAYMAWEB.GHEMPEMPLEADOS   GE  ON E.IDEMPLEADO      = GE.IDEMPLEADO
                    LEFT JOIN VAADINWEB.HUMEMPLEADOEVAL HL  ON E.IDEMPLEADO_EVAL = HL.IDEMPLEADO
                    LEFT JOIN (
                        SELECT DISTINCT IDEMPLEADO FROM VAADINWEB.HUMRESPUESTA
                        WHERE TIPO_EVAL = 'AUTO' AND CONFIRMADO = 1 $filtroP
                    ) A  ON E.IDEMPLEADO = A.IDEMPLEADO
                    LEFT JOIN (
                        SELECT DISTINCT IDEMPLEADO FROM VAADINWEB.HUMRESPUESTA
                        WHERE TIPO_EVAL = 'LIDER_A_COLAB' AND CONFIRMADO = 1 $filtroP
                    ) LC ON E.IDEMPLEADO = LC.IDEMPLEADO
                    LEFT JOIN (
                        SELECT DISTINCT IDEMPLEADO FROM VAADINWEB.HUMRESPUESTA
                        WHERE TIPO_EVAL = 'COLAB_A_LIDER' AND CONFIRMADO = 1 $filtroP
                    ) CL ON E.IDEMPLEADO = CL.IDEMPLEADO
                    LEFT JOIN (
                        SELECT IDEMPLEADO,
                               COUNT(*) AS TOTAL_AC,
                               SUM(CASE WHEN ESTADO='PENDIENTE' THEN 1 ELSE 0 END) AS PEND_AC,
                               SUM(CASE WHEN ESTADO='APROBADO'  THEN 1 ELSE 0 END) AS APRO_AC
                        FROM VAADINWEB.HUMACUERDOMEJORA WHERE 1=1 $filtroP GROUP BY IDEMPLEADO
                    ) AC_S ON E.IDEMPLEADO = AC_S.IDEMPLEADO
                    LEFT JOIN (
                        SELECT IDEMPLEADO, ROUND(AVG(O.VALOR), 2) AS PROM
                        FROM VAADINWEB.HUMRESPUESTA R
                        INNER JOIN VAADINWEB.HUMOPCIONESCALA O ON R.IDOPCION = O.IDOPCION
                        WHERE R.TIPO_EVAL = 'LIDER_A_COLAB' AND R.CONFIRMADO = 1 $filtroP
                        GROUP BY IDEMPLEADO
                    ) PROM_L ON E.IDEMPLEADO = PROM_L.IDEMPLEADO
                    LEFT JOIN (
                        SELECT IDEMPLEADO, ROUND(AVG(O.VALOR), 2) AS PROM
                        FROM VAADINWEB.HUMRESPUESTA R
                        INNER JOIN VAADINWEB.HUMOPCIONESCALA O ON R.IDOPCION = O.IDOPCION
                        WHERE R.TIPO_EVAL = 'AUTO' AND R.CONFIRMADO = 1 $filtroP
                        GROUP BY IDEMPLEADO
                    ) PROM_A ON E.IDEMPLEADO = PROM_A.IDEMPLEADO
                    WHERE E.ACTIVO = 1 $filtroNombre $filtroProceso $filtroEstado
                    ORDER BY E.NOMBRE ASC
                ) t WHERE ROWNUM <= " . ($offset + 30) . "
            ) WHERE RN > $offset";

        $res  = $this->ejecutarConsulta($sql);
        $rows = [];
        if ($res) {
            while ($r = oci_fetch_assoc($res)) {
                $r = $this->enc($r, ['NOMBRE','NOMBRE_COMPLETO','CARGO','PROCESO','NOMBRE_LIDER']);
                $r['PROM_LIDER'] = (float)str_replace(',', '.', $r['PROM_LIDER'] ?? 0);
                $r['PROM_AUTO']  = (float)str_replace(',', '.', $r['PROM_AUTO']  ?? 0);
                $rows[] = $r;
            }
            oci_free_statement($res);
        }
        return $rows;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // V2 — BRECHAS AUTO vs LIDER_A_COLAB
    // ══════════════════════════════════════════════════════════════════════════

    public function getBrechasCompetencias(?array $periodo, string $proceso = ''): array {
        $idP     = $periodo ? (int)$periodo['IDPERIODO'] : 0;
        $filtroP = $idP ? "AND R.IDPERIODO = $idP" : '';

        $conn      = $this->conectar();
        $filtroPro = $proceso ? "AND E.PROCESO = :proceso" : '';

        $sql = "
            SELECT
                C.NUM_PREGUNTA,
                C.NOMBRE                                                               AS NOMBRE_COMP,
                D.NOMBRE                                                               AS DIMENSION,
                ROUND(AVG(CASE WHEN R.TIPO_EVAL='LIDER_A_COLAB' THEN O.VALOR END), 2) AS PROM_LIDER,
                ROUND(AVG(CASE WHEN R.TIPO_EVAL='AUTO'          THEN O.VALOR END), 2) AS PROM_AUTO,
                ROUND(AVG(CASE WHEN R.TIPO_EVAL='LIDER_A_COLAB' THEN O.VALOR END)
                    - AVG(CASE WHEN R.TIPO_EVAL='AUTO'          THEN O.VALOR END), 2) AS BRECHA,
                COUNT(DISTINCT CASE WHEN R.TIPO_EVAL='LIDER_A_COLAB' THEN R.IDEMPLEADO END) AS N_LIDER,
                COUNT(DISTINCT CASE WHEN R.TIPO_EVAL='AUTO'          THEN R.IDEMPLEADO END) AS N_AUTO
            FROM VAADINWEB.HUMRESPUESTA R
            INNER JOIN VAADINWEB.HUMCOMPETENCIA  C ON R.IDCOMPETENCIA = C.IDCOMPETENCIA
            INNER JOIN VAADINWEB.HUMDIMENSION    D ON C.IDDIMENSION   = D.IDDIMENSION
            INNER JOIN VAADINWEB.HUMOPCIONESCALA O ON R.IDOPCION      = O.IDOPCION
            INNER JOIN VAADINWEB.HUMEMPLEADOEVAL E ON R.IDEMPLEADO    = E.IDEMPLEADO
            WHERE R.CONFIRMADO = 1
              AND R.TIPO_EVAL IN ('LIDER_A_COLAB','AUTO')
              AND E.ACTIVO = 1
              $filtroP $filtroPro
            GROUP BY C.NUM_PREGUNTA, C.NOMBRE, D.NOMBRE
            HAVING COUNT(DISTINCT CASE WHEN R.TIPO_EVAL='LIDER_A_COLAB' THEN R.IDEMPLEADO END) > 0
               AND COUNT(DISTINCT CASE WHEN R.TIPO_EVAL='AUTO'          THEN R.IDEMPLEADO END) > 0
            ORDER BY ABS(
                AVG(CASE WHEN R.TIPO_EVAL='LIDER_A_COLAB' THEN O.VALOR END)
               -AVG(CASE WHEN R.TIPO_EVAL='AUTO'          THEN O.VALOR END)
            ) DESC NULLS LAST";

        $query = oci_parse($conn, $sql);
        if ($proceso) oci_bind_by_name($query, ':proceso', $proceso);
        oci_execute($query);
        $rows = [];
        while ($r = oci_fetch_assoc($query)) {
            $r['NOMBRE_COMP'] = fromOracleEncoding($r['NOMBRE_COMP'] ?? '');
            $r['DIMENSION']   = fromOracleEncoding($r['DIMENSION']   ?? '');
            $r['PROM_LIDER']  = (float)str_replace(',', '.', $r['PROM_LIDER'] ?? 0);
            $r['PROM_AUTO']   = (float)str_replace(',', '.', $r['PROM_AUTO']  ?? 0);
            $r['BRECHA']      = (float)str_replace(',', '.', $r['BRECHA']     ?? 0);
            $rows[] = $r;
        }
        oci_free_statement($query);
        return $rows;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // V2 — EXPERIENCIA AZUL
    // ══════════════════════════════════════════════════════════════════════════

    public function getResumenExpAzul(?array $periodo): array {
        $idP     = $periodo ? (int)$periodo['IDPERIODO'] : 0;
        $filtroP = $idP ? "AND IDPERIODO = $idP" : '';

        // KPIs: quiénes aplican y cuántos han completado cada parte
        $sqlKpi = "
            SELECT
                COUNT(DISTINCT HE.IDEMPLEADO)         AS TOTAL_APLICAN,
                COUNT(DISTINCT EC.IDEMPLEADO)          AS COMPLETARON_COLAB,
                COUNT(DISTINCT EL.IDEMPLEADO)          AS COMPLETARON_LIDER,
                ROUND(COUNT(DISTINCT EC.IDEMPLEADO) * 100.0
                      / NULLIF(COUNT(DISTINCT HE.IDEMPLEADO), 0), 1) AS PCT_COLAB,
                ROUND(COUNT(DISTINCT EL.IDEMPLEADO) * 100.0
                      / NULLIF(COUNT(DISTINCT HE.IDEMPLEADO), 0), 1) AS PCT_LIDER
            FROM VAADINWEB.HUMEMPLEADOEVAL HE
            LEFT JOIN (
                SELECT DISTINCT IDEMPLEADO FROM VAADINWEB.HUMRESPUESTA
                WHERE TIPO_EVAL = 'EXPERIENCIA_COLAB' AND CONFIRMADO = 1 $filtroP
            ) EC ON HE.IDEMPLEADO = EC.IDEMPLEADO
            LEFT JOIN (
                SELECT DISTINCT IDEMPLEADO FROM VAADINWEB.HUMRESPUESTA
                WHERE TIPO_EVAL = 'EXPERIENCIA_LIDER' AND CONFIRMADO = 1 $filtroP
            ) EL ON HE.IDEMPLEADO = EL.IDEMPLEADO
            WHERE HE.ACTIVO = 1 AND (HE.IDROL = 1 OR HE.APLICA_EXP_AZUL = 1)";
        $resKpi = $this->ejecutarConsulta($sqlKpi);
        $kpi    = $resKpi ? oci_fetch_assoc($resKpi) : [];
        if ($resKpi) oci_free_statement($resKpi);

        // Promedios por competencia Exp. Azul (ambos tipos separados)
        $sqlComp = "
            SELECT C.NUM_PREGUNTA, C.NOMBRE AS NOMBRE_COMP, R.TIPO_EVAL,
                   ROUND(AVG(O.VALOR), 2) AS PROMEDIO,
                   COUNT(DISTINCT R.IDEMPLEADO) AS EVALUADOS
            FROM VAADINWEB.HUMRESPUESTA R
            INNER JOIN VAADINWEB.HUMCOMPETENCIA  C ON R.IDCOMPETENCIA = C.IDCOMPETENCIA
            INNER JOIN VAADINWEB.HUMOPCIONESCALA O ON R.IDOPCION      = O.IDOPCION
            WHERE R.TIPO_EVAL IN ('EXPERIENCIA_COLAB','EXPERIENCIA_LIDER')
              AND R.CONFIRMADO = 1 $filtroP
            GROUP BY C.NUM_PREGUNTA, C.NOMBRE, R.TIPO_EVAL
            ORDER BY R.TIPO_EVAL, C.NUM_PREGUNTA";
        $resComp = $this->ejecutarConsulta($sqlComp);
        $competencias = [];
        if ($resComp) {
            while ($r = oci_fetch_assoc($resComp)) {
                $r['NOMBRE_COMP'] = fromOracleEncoding($r['NOMBRE_COMP'] ?? '');
                $r['PROMEDIO']    = (float)str_replace(',', '.', $r['PROMEDIO'] ?? 0);
                $competencias[]   = $r;
            }
            oci_free_statement($resComp);
        }

        // Pendientes: aplican pero falta alguna parte de Exp. Azul
        $sqlPend = "
            SELECT HE.IDEMPLEADO, HE.NOMBRE AS COLABORADOR, HE.CARGO, HE.PROCESO,
                   NVL(HL.NOMBRE,'—') AS LIDER,
                   CASE WHEN EC.IDEMPLEADO IS NOT NULL THEN 1 ELSE 0 END AS HIZO_COLAB,
                   CASE WHEN EL.IDEMPLEADO IS NOT NULL THEN 1 ELSE 0 END AS HIZO_LIDER
            FROM VAADINWEB.HUMEMPLEADOEVAL HE
            LEFT JOIN VAADINWEB.HUMEMPLEADOEVAL HL ON HE.IDEMPLEADO_EVAL = HL.IDEMPLEADO
            LEFT JOIN (
                SELECT DISTINCT IDEMPLEADO FROM VAADINWEB.HUMRESPUESTA
                WHERE TIPO_EVAL = 'EXPERIENCIA_COLAB' AND CONFIRMADO = 1 $filtroP
            ) EC ON HE.IDEMPLEADO = EC.IDEMPLEADO
            LEFT JOIN (
                SELECT DISTINCT IDEMPLEADO FROM VAADINWEB.HUMRESPUESTA
                WHERE TIPO_EVAL = 'EXPERIENCIA_LIDER' AND CONFIRMADO = 1 $filtroP
            ) EL ON HE.IDEMPLEADO = EL.IDEMPLEADO
            WHERE HE.ACTIVO = 1 AND (HE.IDROL = 1 OR HE.APLICA_EXP_AZUL = 1)
              AND (EC.IDEMPLEADO IS NULL OR EL.IDEMPLEADO IS NULL)
            ORDER BY HE.PROCESO ASC, HE.NOMBRE ASC";
        $resPend = $this->ejecutarConsulta($sqlPend);
        $pendientes = [];
        if ($resPend) {
            while ($r = oci_fetch_assoc($resPend))
                $pendientes[] = $this->enc($r, ['COLABORADOR','CARGO','PROCESO','LIDER']);
            oci_free_statement($resPend);
        }

        // Evaluados: han completado al menos una parte de Exp. Azul
        $sqlEval = "
            SELECT HE.IDEMPLEADO,
                   NVL(TRIM(GE.PNOMBRE||' '||NVL(GE.SNOMBRE||' ','')||GE.PAPELLIDO||NVL(' '||GE.SAPELLIDO,'')), HE.NOMBRE) AS COLABORADOR,
                   HE.CARGO, HE.PROCESO,
                   NVL(HL.NOMBRE,'—') AS LIDER,
                   CASE WHEN EC.IDEMPLEADO IS NOT NULL THEN 1 ELSE 0 END AS HIZO_COLAB,
                   CASE WHEN EL.IDEMPLEADO IS NOT NULL THEN 1 ELSE 0 END AS HIZO_LIDER,
                   ROUND(NVL(PROM_C.PROM, 0), 2) AS PROM_COLAB,
                   ROUND(NVL(PROM_L.PROM, 0), 2) AS PROM_LIDER
            FROM VAADINWEB.HUMEMPLEADOEVAL HE
            LEFT JOIN ZAYMAWEB.GHEMPEMPLEADOS   GE ON HE.IDEMPLEADO      = GE.IDEMPLEADO
            LEFT JOIN VAADINWEB.HUMEMPLEADOEVAL HL ON HE.IDEMPLEADO_EVAL = HL.IDEMPLEADO
            LEFT JOIN (
                SELECT DISTINCT IDEMPLEADO FROM VAADINWEB.HUMRESPUESTA
                WHERE TIPO_EVAL = 'EXPERIENCIA_COLAB' AND CONFIRMADO = 1 $filtroP
            ) EC ON HE.IDEMPLEADO = EC.IDEMPLEADO
            LEFT JOIN (
                SELECT DISTINCT IDEMPLEADO FROM VAADINWEB.HUMRESPUESTA
                WHERE TIPO_EVAL = 'EXPERIENCIA_LIDER' AND CONFIRMADO = 1 $filtroP
            ) EL ON HE.IDEMPLEADO = EL.IDEMPLEADO
            LEFT JOIN (
                SELECT R.IDEMPLEADO, ROUND(AVG(O.VALOR),2) AS PROM
                FROM VAADINWEB.HUMRESPUESTA R
                INNER JOIN VAADINWEB.HUMOPCIONESCALA O ON R.IDOPCION = O.IDOPCION
                WHERE R.TIPO_EVAL = 'EXPERIENCIA_COLAB' AND R.CONFIRMADO = 1 $filtroP
                GROUP BY R.IDEMPLEADO
            ) PROM_C ON HE.IDEMPLEADO = PROM_C.IDEMPLEADO
            LEFT JOIN (
                SELECT R.IDEMPLEADO, ROUND(AVG(O.VALOR),2) AS PROM
                FROM VAADINWEB.HUMRESPUESTA R
                INNER JOIN VAADINWEB.HUMOPCIONESCALA O ON R.IDOPCION = O.IDOPCION
                WHERE R.TIPO_EVAL = 'EXPERIENCIA_LIDER' AND R.CONFIRMADO = 1 $filtroP
                GROUP BY R.IDEMPLEADO
            ) PROM_L ON HE.IDEMPLEADO = PROM_L.IDEMPLEADO
            WHERE HE.ACTIVO = 1 AND (HE.IDROL = 1 OR HE.APLICA_EXP_AZUL = 1)
              AND (EC.IDEMPLEADO IS NOT NULL OR EL.IDEMPLEADO IS NOT NULL)
            ORDER BY HE.PROCESO ASC, HE.NOMBRE ASC";
        $resEval = $this->ejecutarConsulta($sqlEval);
        $evaluados = [];
        if ($resEval) {
            while ($r = oci_fetch_assoc($resEval)) {
                $r = $this->enc($r, ['COLABORADOR','CARGO','PROCESO','LIDER']);
                $r['PROM_COLAB'] = (float)str_replace(',', '.', $r['PROM_COLAB'] ?? 0);
                $r['PROM_LIDER'] = (float)str_replace(',', '.', $r['PROM_LIDER'] ?? 0);
                $evaluados[] = $r;
            }
            oci_free_statement($resEval);
        }

        return ['kpi' => $kpi, 'competencias' => $competencias, 'pendientes' => $pendientes, 'evaluados' => $evaluados];
    }

    // ══════════════════════════════════════════════════════════════════════════
    // V2 — EXPORTACIÓN
    // ══════════════════════════════════════════════════════════════════════════

    public function getColaboradoresParaExport(?array $periodo): array {
        $idP     = $periodo ? (int)$periodo['IDPERIODO'] : 0;
        $filtroP = $idP ? "AND IDPERIODO = $idP" : '';

        $sql = "
            SELECT E.NOMBRE, E.CARGO, E.PROCESO, E.IDENTIFICACION,
                   NVL(TRIM(GE.PNOMBRE||' '||NVL(GE.SNOMBRE||' ','')||GE.PAPELLIDO||NVL(' '||GE.SAPELLIDO,'')), E.NOMBRE) AS NOMBRE_COMPLETO,
                   NVL(HL.NOMBRE,'—') AS NOMBRE_LIDER,
                   CASE WHEN A.IDEMPLEADO  IS NOT NULL THEN 'Si' ELSE 'No' END AS TIENE_AUTO,
                   CASE WHEN LC.IDEMPLEADO IS NOT NULL THEN 'Si' ELSE 'No' END AS TIENE_LIDER,
                   CASE WHEN CL.IDEMPLEADO IS NOT NULL THEN 'Si' ELSE 'No' END AS EVALUO_LIDER,
                   CASE
                       WHEN A.IDEMPLEADO IS NOT NULL AND LC.IDEMPLEADO IS NOT NULL THEN 'Completo'
                       WHEN A.IDEMPLEADO IS NOT NULL THEN 'En progreso'
                       ELSE 'Sin iniciar'
                   END AS ESTADO,
                   NVL(AC_S.TOTAL_AC, 0) AS TOTAL_ACUERDOS,
                   NVL(AC_S.PEND_AC,  0) AS ACUERDOS_PEND,
                   NVL(AC_S.APRO_AC,  0) AS ACUERDOS_APRO,
                   ROUND(NVL(PROM_L.PROM, 0), 2) AS PROM_LIDER,
                   ROUND(NVL(PROM_A.PROM, 0), 2) AS PROM_AUTO
            FROM VAADINWEB.HUMEMPLEADOEVAL E
            LEFT JOIN ZAYMAWEB.GHEMPEMPLEADOS   GE  ON E.IDEMPLEADO      = GE.IDEMPLEADO
            LEFT JOIN VAADINWEB.HUMEMPLEADOEVAL HL  ON E.IDEMPLEADO_EVAL = HL.IDEMPLEADO
            LEFT JOIN (SELECT DISTINCT IDEMPLEADO FROM VAADINWEB.HUMRESPUESTA
                       WHERE TIPO_EVAL='AUTO'          AND CONFIRMADO=1 $filtroP) A  ON E.IDEMPLEADO=A.IDEMPLEADO
            LEFT JOIN (SELECT DISTINCT IDEMPLEADO FROM VAADINWEB.HUMRESPUESTA
                       WHERE TIPO_EVAL='LIDER_A_COLAB' AND CONFIRMADO=1 $filtroP) LC ON E.IDEMPLEADO=LC.IDEMPLEADO
            LEFT JOIN (SELECT DISTINCT IDEMPLEADO FROM VAADINWEB.HUMRESPUESTA
                       WHERE TIPO_EVAL='COLAB_A_LIDER' AND CONFIRMADO=1 $filtroP) CL ON E.IDEMPLEADO=CL.IDEMPLEADO
            LEFT JOIN (SELECT IDEMPLEADO, COUNT(*) AS TOTAL_AC,
                              SUM(CASE WHEN ESTADO='PENDIENTE' THEN 1 ELSE 0 END) AS PEND_AC,
                              SUM(CASE WHEN ESTADO='APROBADO'  THEN 1 ELSE 0 END) AS APRO_AC
                       FROM VAADINWEB.HUMACUERDOMEJORA WHERE 1=1 $filtroP GROUP BY IDEMPLEADO
                      ) AC_S ON E.IDEMPLEADO=AC_S.IDEMPLEADO
            LEFT JOIN (SELECT IDEMPLEADO, ROUND(AVG(O.VALOR),2) AS PROM
                       FROM VAADINWEB.HUMRESPUESTA R INNER JOIN VAADINWEB.HUMOPCIONESCALA O ON R.IDOPCION=O.IDOPCION
                       WHERE R.TIPO_EVAL='LIDER_A_COLAB' AND R.CONFIRMADO=1 $filtroP GROUP BY IDEMPLEADO
                      ) PROM_L ON E.IDEMPLEADO=PROM_L.IDEMPLEADO
            LEFT JOIN (SELECT IDEMPLEADO, ROUND(AVG(O.VALOR),2) AS PROM
                       FROM VAADINWEB.HUMRESPUESTA R INNER JOIN VAADINWEB.HUMOPCIONESCALA O ON R.IDOPCION=O.IDOPCION
                       WHERE R.TIPO_EVAL='AUTO' AND R.CONFIRMADO=1 $filtroP GROUP BY IDEMPLEADO
                      ) PROM_A ON E.IDEMPLEADO=PROM_A.IDEMPLEADO
            WHERE E.ACTIVO = 1
            ORDER BY E.PROCESO ASC, E.NOMBRE ASC";

        $res  = $this->ejecutarConsulta($sql);
        $rows = [];
        if ($res) {
            while ($r = oci_fetch_assoc($res)) {
                $r = $this->enc($r, ['NOMBRE','NOMBRE_COMPLETO','CARGO','PROCESO','NOMBRE_LIDER']);
                $r['PROM_LIDER'] = (float)str_replace(',', '.', $r['PROM_LIDER'] ?? 0);
                $r['PROM_AUTO']  = (float)str_replace(',', '.', $r['PROM_AUTO']  ?? 0);
                $rows[] = $r;
            }
            oci_free_statement($res);
        }
        return $rows;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // RANKING — Top N colaboradores por promedio
    // ══════════════════════════════════════════════════════════════════════════

    public function getTopColaboradores(?array $periodo, string $tipoEval = 'LIDER_A_COLAB', string $proceso = '', int $limit = 10): array {
        $tiposOk = ['LIDER_A_COLAB', 'AUTO', 'COLAB_A_LIDER'];
        if (!in_array($tipoEval, $tiposOk)) $tipoEval = 'LIDER_A_COLAB';
        $limit = max(1, min(50, $limit));

        $idP     = $periodo ? (int)$periodo['IDPERIODO'] : 0;
        $filtroP = $idP ? "AND R.IDPERIODO = $idP" : '';
        $filtroA = $idP ? "AND IDPERIODO = $idP"  : '';

        $tipoEvalSafe = str_replace("'", "''", $tipoEval);

        $sqlBase = "
            SELECT * FROM (
                SELECT
                    E.IDEMPLEADO,
                    NVL(GE.PNOMBRE||' '||GE.PAPELLIDO, E.NOMBRE) AS NOMBRE,
                    E.CARGO,
                    E.PROCESO,
                    ROUND(AVG(OS.VALOR), 2)  AS PROMEDIO,
                    NVL(AC.TOTAL_AC, 0)      AS TOTAL_AC,
                    NVL(AC.PEND_AC,  0)      AS PEND_AC,
                    NVL(AC.APRO_AC,  0)      AS APRO_AC
                FROM VAADINWEB.HUMEMPLEADOEVAL E
                INNER JOIN VAADINWEB.HUMRESPUESTA R
                        ON R.IDEMPLEADO = E.IDEMPLEADO
                INNER JOIN VAADINWEB.HUMOPCIONESCALA OS
                        ON R.IDOPCION = OS.IDOPCION
                LEFT JOIN ZAYMAWEB.GHEMPEMPLEADOS GE
                        ON E.IDEMPLEADO = GE.IDEMPLEADO
                LEFT JOIN (
                    SELECT IDEMPLEADO,
                           COUNT(*)                                                 AS TOTAL_AC,
                           SUM(CASE WHEN ESTADO='PENDIENTE' THEN 1 ELSE 0 END)     AS PEND_AC,
                           SUM(CASE WHEN ESTADO='APROBADO'  THEN 1 ELSE 0 END)     AS APRO_AC
                    FROM VAADINWEB.HUMACUERDOMEJORA WHERE 1=1 $filtroA
                    GROUP BY IDEMPLEADO
                ) AC ON E.IDEMPLEADO = AC.IDEMPLEADO
                WHERE E.ACTIVO = 1
                AND R.TIPO_EVAL = '$tipoEvalSafe'
                AND R.CONFIRMADO = 1
                $filtroP";

        if ($proceso !== '') {
            $procesoSafe = str_replace("'", "''", strtoupper($proceso));
            $sqlBase .= " AND UPPER(E.PROCESO) LIKE '%$procesoSafe%'";
        }

        $sqlBase .= "
                GROUP BY E.IDEMPLEADO, E.NOMBRE, E.CARGO, E.PROCESO,
                         GE.PNOMBRE, GE.PAPELLIDO,
                         AC.TOTAL_AC, AC.PEND_AC, AC.APRO_AC
                ORDER BY AVG(OS.VALOR) DESC
            ) WHERE ROWNUM <= $limit";

        $res  = $this->ejecutarConsulta($sqlBase);
        $rows = [];
        if ($res) {
            while ($r = oci_fetch_assoc($res)) {
                $r = $this->enc($r, ['NOMBRE','CARGO','PROCESO']);
                $r['PROMEDIO'] = (float)str_replace(',', '.', $r['PROMEDIO'] ?? 0);
                $rows[] = $r;
            }
            oci_free_statement($res);
        }
        return $rows;
    }

    public function getJustificacionesParaExport(?array $periodo): array {
        $idP     = $periodo ? (int)$periodo['IDPERIODO'] : 0;
        $filtroP = $idP ? "AND R.IDPERIODO = $idP" : '';

        $sql = "
            SELECT
                NVL(TRIM(GE.PNOMBRE||' '||NVL(GE.SNOMBRE||' ','')||GE.PAPELLIDO||NVL(' '||GE.SAPELLIDO,'')), HE.NOMBRE) AS COLABORADOR,
                HE.CARGO, HE.PROCESO,
                NVL(HL.NOMBRE,'—') AS LIDER,
                C.NUM_PREGUNTA,
                C.NOMBRE AS NOMBRE_COMP,
                O.VALOR,
                J.JUSTIFICACION
            FROM VAADINWEB.HUMRESPUESTA R
            INNER JOIN VAADINWEB.HUMCOMPETENCIA      C  ON R.IDCOMPETENCIA    = C.IDCOMPETENCIA
            INNER JOIN VAADINWEB.HUMOPCIONESCALA     O  ON R.IDOPCION         = O.IDOPCION
            INNER JOIN VAADINWEB.HUMJUSTIFICACION_V2 J  ON R.IDRESPUESTA      = J.IDRESPUESTA
            INNER JOIN VAADINWEB.HUMEMPLEADOEVAL     HE ON R.IDEMPLEADO       = HE.IDEMPLEADO
            LEFT  JOIN ZAYMAWEB.GHEMPEMPLEADOS       GE ON HE.IDEMPLEADO      = GE.IDEMPLEADO
            LEFT  JOIN VAADINWEB.HUMEMPLEADOEVAL     HL ON HE.IDEMPLEADO_EVAL = HL.IDEMPLEADO
            WHERE R.TIPO_EVAL  = 'LIDER_A_COLAB'
              AND R.CONFIRMADO = 1 $filtroP
            ORDER BY HE.PROCESO ASC, HE.NOMBRE ASC, C.NUM_PREGUNTA ASC";

        $res  = $this->ejecutarConsulta($sql);
        $rows = [];
        if ($res) {
            while ($r = oci_fetch_assoc($res)) {
                $r = $this->enc($r, ['COLABORADOR','CARGO','PROCESO','LIDER','NOMBRE_COMP','JUSTIFICACION']);
                $r['VALOR'] = (int)($r['VALOR'] ?? 0);
                $rows[] = $r;
            }
            oci_free_statement($res);
        }
        return $rows;
    }

    public function getBrechasParaExport(?array $periodo): array {
        return $this->getBrechasCompetencias($periodo);
    }

    public function getExpAzulParaExport(?array $periodo): array {
        $data = $this->getResumenExpAzul($periodo);
        return $data['pendientes'] ?? [];
    }

    public function getExpAzulCompParaExport(?array $periodo): array {
        $data = $this->getResumenExpAzul($periodo);
        return $data['competencias'] ?? [];
    }

    public function getExpAzulEvaluadosParaExport(?array $periodo): array {
        $data = $this->getResumenExpAzul($periodo);
        return $data['evaluados'] ?? [];
    }
}
