<?php
namespace app\models;

class equipoModel extends mainModel {

    // ── Guard: verifica si el empleado es líder funcional ────────────────────
    public function esLiderFuncional(int $idEmpleado): bool {
        $sql = "SELECT ES_LIDER_FUNCIONAL FROM VAADINWEB.HUMEMPLEADOEVAL
                WHERE IDEMPLEADO = $idEmpleado AND ACTIVO = 1 AND ROWNUM = 1";
        $res = $this->ejecutarConsulta($sql);
        $row = $res ? oci_fetch_assoc($res) : null;
        if ($res) oci_free_statement($res);
        return $row && (int)$row['ES_LIDER_FUNCIONAL'] === 1;
    }

    // ── Guard: verifica si el empleado es director (puede hacer Flujo 2) ─────
    public function esDirector(int $idEmpleado): bool {
        $sql = "SELECT ES_DIRECTOR FROM VAADINWEB.HUMEMPLEADOEVAL
                WHERE IDEMPLEADO = $idEmpleado AND ACTIVO = 1 AND ROWNUM = 1";
        $res = $this->ejecutarConsulta($sql);
        $row = $res ? oci_fetch_assoc($res) : null;
        if ($res) oci_free_statement($res);
        return $row && (int)$row['ES_DIRECTOR'] === 1;
    }

    // ── KPIs generales del equipo ────────────────────────────────────────────
    public function getKPIsEquipo(int $idLider, ?array $periodo): array {
        $idP = $periodo ? (int)$periodo['IDPERIODO'] : 0;
        if (!$idP) return [];

        $sql = "SELECT
            (SELECT COUNT(*) FROM VAADINWEB.HUMEMPLEADOEVAL
             WHERE IDEMPLEADO_EVAL = $idLider AND ACTIVO = 1) AS TOTAL_COLAB,

            (SELECT COUNT(DISTINCT R.IDEMPLEADO)
             FROM VAADINWEB.HUMRESPUESTA R
             INNER JOIN VAADINWEB.HUMEMPLEADOEVAL HE ON R.IDEMPLEADO = HE.IDEMPLEADO
             WHERE HE.IDEMPLEADO_EVAL = $idLider AND HE.ACTIVO = 1
               AND R.TIPO_EVAL = 'AUTO' AND R.CONFIRMADO = 1
               AND R.IDPERIODO = $idP) AS CON_AUTO,

            (SELECT COUNT(DISTINCT R.IDEMPLEADO)
             FROM VAADINWEB.HUMRESPUESTA R
             WHERE R.IDEMPLEADO_EVAL = $idLider
               AND R.TIPO_EVAL = 'LIDER_A_COLAB' AND R.CONFIRMADO = 1
               AND R.IDPERIODO = $idP) AS CON_EVAL,

            (SELECT COUNT(*) FROM VAADINWEB.HUMFEEDBACK
             WHERE IDEMPLEADO_LIDER = $idLider AND IDPERIODO = $idP
               AND TIPO_FEEDBACK = 1) AS TOTAL_FB,

            (SELECT COUNT(*) FROM VAADINWEB.HUMFEEDBACK
             WHERE IDEMPLEADO_LIDER = $idLider AND IDPERIODO = $idP
               AND TIPO_FEEDBACK = 1
               AND NVL(FIRMADO_COLAB,0) = 1) AS FB_COMPLETO,

            (SELECT COUNT(*) FROM VAADINWEB.HUMACUERDOMEJORA
             WHERE IDEMPLEADO_LIDER = $idLider AND IDPERIODO = $idP) AS TOTAL_AC,

            (SELECT COUNT(*) FROM VAADINWEB.HUMACUERDOMEJORA
             WHERE IDEMPLEADO_LIDER = $idLider AND IDPERIODO = $idP
               AND ESTADO = 'PENDIENTE') AS AC_PEND,

            (SELECT COUNT(*) FROM VAADINWEB.HUMACUERDOMEJORA
             WHERE IDEMPLEADO_LIDER = $idLider AND IDPERIODO = $idP
               AND ESTADO = 'APROBADO') AS AC_APRO
        FROM DUAL";

        $res = $this->ejecutarConsulta($sql);
        $row = $res ? oci_fetch_assoc($res) : [];
        if ($res) oci_free_statement($res);
        return $row ?: [];
    }

    // ── Equipo detallado con todos los estados por colaborador ───────────────
    public function getEquipoDetallado(int $idLider, ?array $periodo): array {
        $idP = $periodo ? (int)$periodo['IDPERIODO'] : 0;
        if (!$idP) return [];

        $subQuery = "
            SELECT HE.IDEMPLEADO,
                   NVL(GE.PNOMBRE||' '||GE.SNOMBRE||' '||GE.PAPELLIDO||' '||GE.SAPELLIDO,
                       HE.NOMBRE) AS EMPLEADO,
                   HE.CARGO, HE.PROCESO
            FROM VAADINWEB.HUMEMPLEADOEVAL HE
            LEFT JOIN ZAYMAWEB.GHEMPEMPLEADOS GE ON HE.IDEMPLEADO = GE.IDEMPLEADO
            WHERE HE.IDEMPLEADO_EVAL = $idLider AND HE.ACTIVO = 1";

        $sql = "
            SELECT
                E.IDEMPLEADO, E.EMPLEADO, E.CARGO, E.PROCESO,
                CASE WHEN A.IDEMPLEADO IS NOT NULL THEN 1 ELSE 0 END AS TIENE_AUTO,
                CASE WHEN C.IDEMPLEADO IS NOT NULL THEN 1 ELSE 0 END AS TIENE_EVAL,
                CASE WHEN F.IDFEEDBACK IS NOT NULL THEN 1 ELSE 0 END AS TIENE_FEEDBACK,
                NVL(F.FIRMADO_LIDER, 0) AS FIRMADO_LIDER,
                NVL(F.FIRMADO_COLAB, 0) AS FIRMADO_COLAB,
                TO_CHAR(F.FECHA_FEEDBACK,'DD/MM/YYYY') AS FECHA_FEEDBACK,
                TO_CHAR(F.FECHA_FIRMA,'DD/MM/YYYY')    AS FECHA_FIRMA,
                NVL(AC.TOTAL_AC, 0) AS TOTAL_AC,
                NVL(AC.PEND_AC,  0) AS PEND_AC,
                NVL(AC.APRO_AC,  0) AS APRO_AC,
                F.IDFEEDBACK
            FROM ($subQuery) E
            LEFT JOIN (
                SELECT DISTINCT IDEMPLEADO FROM VAADINWEB.HUMRESPUESTA
                WHERE TIPO_EVAL = 'AUTO' AND CONFIRMADO = 1 AND IDPERIODO = $idP
            ) A ON E.IDEMPLEADO = A.IDEMPLEADO
            LEFT JOIN (
                SELECT DISTINCT IDEMPLEADO FROM VAADINWEB.HUMRESPUESTA
                WHERE TIPO_EVAL = 'LIDER_A_COLAB' AND IDEMPLEADO_EVAL = $idLider
                  AND CONFIRMADO = 1 AND IDPERIODO = $idP
            ) C ON E.IDEMPLEADO = C.IDEMPLEADO
            LEFT JOIN VAADINWEB.HUMFEEDBACK F
                ON E.IDEMPLEADO = F.IDEMPLEADO
                AND F.IDEMPLEADO_LIDER = $idLider
                AND F.IDPERIODO = $idP AND F.TIPO_FEEDBACK = 1
            LEFT JOIN (
                SELECT IDEMPLEADO,
                       COUNT(*) AS TOTAL_AC,
                       SUM(CASE WHEN ESTADO='PENDIENTE' THEN 1 ELSE 0 END) AS PEND_AC,
                       SUM(CASE WHEN ESTADO='APROBADO'  THEN 1 ELSE 0 END) AS APRO_AC
                FROM VAADINWEB.HUMACUERDOMEJORA
                WHERE IDEMPLEADO_LIDER = $idLider AND IDPERIODO = $idP
                GROUP BY IDEMPLEADO
            ) AC ON E.IDEMPLEADO = AC.IDEMPLEADO
            ORDER BY E.EMPLEADO ASC";

        $res  = $this->ejecutarConsulta($sql);
        $rows = [];
        if ($res) {
            while ($r = oci_fetch_assoc($res)) {
                $r['EMPLEADO'] = fromOracleEncoding($r['EMPLEADO'] ?? '');
                $r['CARGO']    = fromOracleEncoding($r['CARGO']    ?? '');
                $r['PROCESO']  = fromOracleEncoding($r['PROCESO']  ?? '');
                $rows[] = $r;
            }
            oci_free_statement($res);
        }
        return $rows;
    }

    // ── Acuerdos asignados por este líder (paginados) ────────────────────────
    public function getAcuerdosEquipo(int $idLider, ?array $periodo, string $estado, int $page, int $perPage = 15): array {
        $idP    = $periodo ? (int)$periodo['IDPERIODO'] : 0;
        if (!$idP) return [];
        $offset = ($page - 1) * $perPage;
        $maxRow = $offset + $perPage;
        $filtroEstado = $estado ? "AND A.ESTADO = '" . str_replace("'", "''", $estado) . "'" : '';

        $sql = "SELECT * FROM (
            SELECT ROWNUM AS RN, Q.* FROM (
                SELECT A.IDACUERDO,
                       NVL(GE.PNOMBRE||' '||GE.SNOMBRE||' '||GE.PAPELLIDO||' '||GE.SAPELLIDO, HE.NOMBRE) AS COLABORADOR,
                       HE.CARGO, HE.PROCESO,
                       O.OBJETIVO,
                       C.NOMBRE AS NOMBRE_COMP,
                       A.NUM_COMPETENCIA,
                       CASE WHEN A.NUM_COMPETENCIA <= 11 THEN 1 ELSE 2 END AS FLUJO,
                       A.CALIFICACION, A.ESTADO, A.META, A.PLAZO,
                       A.PLAN_ACCION, A.COMENTARIO_LIDER
                FROM VAADINWEB.HUMACUERDOMEJORA A
                INNER JOIN VAADINWEB.HUMOBJETIVOMEJORA O  ON A.IDOBJETIVO = O.IDOBJETIVO
                INNER JOIN VAADINWEB.HUMEMPLEADOEVAL   HE ON A.IDEMPLEADO = HE.IDEMPLEADO
                LEFT  JOIN ZAYMAWEB.GHEMPEMPLEADOS     GE ON HE.IDEMPLEADO = GE.IDEMPLEADO
                LEFT  JOIN VAADINWEB.HUMCOMPETENCIA    C
                    ON C.NUM_PREGUNTA = A.NUM_COMPETENCIA AND C.ACTIVO = 1
                WHERE A.IDEMPLEADO_LIDER = $idLider
                  AND A.IDPERIODO        = $idP
                  $filtroEstado
                ORDER BY HE.NOMBRE ASC, A.NUM_COMPETENCIA ASC
            ) Q WHERE ROWNUM <= $maxRow
        ) WHERE RN > $offset";

        $res  = $this->ejecutarConsulta($sql);
        $rows = [];
        if ($res) {
            $campos = ['COLABORADOR','CARGO','PROCESO','OBJETIVO','NOMBRE_COMP','META','PLAZO','PLAN_ACCION','COMENTARIO_LIDER'];
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

    public function getAllAcuerdosEquipo(int $idLider, ?array $periodo): array {
        $idP = $periodo ? (int)$periodo['IDPERIODO'] : 0;
        if (!$idP) return [];

        $sql = "SELECT A.IDACUERDO,
                       NVL(GE.PNOMBRE||' '||GE.SNOMBRE||' '||GE.PAPELLIDO||' '||GE.SAPELLIDO, HE.NOMBRE) AS COLABORADOR,
                       HE.CARGO, HE.PROCESO,
                       O.OBJETIVO, C.NOMBRE AS NOMBRE_COMP,
                       A.NUM_COMPETENCIA, A.ESTADO, A.META, A.PLAZO, A.PLAN_ACCION
                       CASE WHEN A.NUM_COMPETENCIA <= 11 THEN 1 ELSE 2 END AS FLUJO
                FROM VAADINWEB.HUMACUERDOMEJORA A
                INNER JOIN VAADINWEB.HUMOBJETIVOMEJORA O  ON A.IDOBJETIVO = O.IDOBJETIVO
                INNER JOIN VAADINWEB.HUMEMPLEADOEVAL   HE ON A.IDEMPLEADO = HE.IDEMPLEADO
                LEFT  JOIN ZAYMAWEB.GHEMPEMPLEADOS     GE ON HE.IDEMPLEADO = GE.IDEMPLEADO
                LEFT  JOIN VAADINWEB.HUMCOMPETENCIA    C
                    ON C.NUM_PREGUNTA = A.NUM_COMPETENCIA AND C.ACTIVO = 1
                WHERE A.IDEMPLEADO_LIDER = $idLider
                  AND A.IDPERIODO        = $idP
                ORDER BY HE.NOMBRE ASC, A.NUM_COMPETENCIA ASC";

        $res  = $this->ejecutarConsulta($sql);
        $rows = [];
        if ($res) {
            $campos = ['COLABORADOR','CARGO','PROCESO','OBJETIVO','NOMBRE_COMP','META','PLAZO','PLAN_ACCION'];
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

    public function countAcuerdosEquipo(int $idLider, ?array $periodo, string $estado): int {
        $idP = $periodo ? (int)$periodo['IDPERIODO'] : 0;
        if (!$idP) return 0;
        $filtroEstado = $estado ? "AND ESTADO = '" . str_replace("'", "''", $estado) . "'" : '';
        $sql = "SELECT COUNT(*) AS TOTAL FROM VAADINWEB.HUMACUERDOMEJORA
                WHERE IDEMPLEADO_LIDER = $idLider AND IDPERIODO = $idP $filtroEstado";
        $res = $this->ejecutarConsulta($sql);
        $row = $res ? oci_fetch_assoc($res) : null;
        if ($res) oci_free_statement($res);
        return (int)($row['TOTAL'] ?? 0);
    }

    // ── Historial de feedback realizados por este líder ──────────────────────
    public function getFeedbackRealizados(int $idLider, ?array $periodo): array {
        $idP = $periodo ? (int)$periodo['IDPERIODO'] : 0;
        if (!$idP) return [];

        // Solo el feedback más reciente por colaborador (evita duplicados si se registró más de una vez)
        $sql = "
            SELECT F.IDFEEDBACK, F.IDEMPLEADO,
                   NVL(GE.PNOMBRE||' '||GE.SNOMBRE||' '||GE.PAPELLIDO||' '||GE.SAPELLIDO, HE.NOMBRE) AS COLABORADOR,
                   HE.CARGO,
                   TO_CHAR(F.FECHA_FEEDBACK,'DD/MM/YYYY HH24:MI') AS FECHA_FEEDBACK,
                   TO_CHAR(F.FECHA_FIRMA,'DD/MM/YYYY HH24:MI')     AS FECHA_FIRMA,
                   NVL(F.FIRMADO_LIDER, 0) AS FIRMADO_LIDER,
                   NVL(F.FIRMADO_COLAB, 0) AS FIRMADO_COLAB,
                   F.OBSERVACION,
                   NVL(AC.TOTAL_AC, 0) AS TOTAL_AC
            FROM VAADINWEB.HUMFEEDBACK F
            INNER JOIN VAADINWEB.HUMEMPLEADOEVAL HE
                ON F.IDEMPLEADO = HE.IDEMPLEADO AND HE.ACTIVO = 1 AND HE.IDEMPLEADO_EVAL = $idLider
            LEFT  JOIN ZAYMAWEB.GHEMPEMPLEADOS   GE ON HE.IDEMPLEADO = GE.IDEMPLEADO
            LEFT  JOIN (
                SELECT IDEMPLEADO, COUNT(*) AS TOTAL_AC
                FROM VAADINWEB.HUMACUERDOMEJORA
                WHERE IDEMPLEADO_LIDER = $idLider AND IDPERIODO = $idP
                  AND NUM_COMPETENCIA BETWEEN 1 AND 11
                GROUP BY IDEMPLEADO
            ) AC ON F.IDEMPLEADO = AC.IDEMPLEADO
            WHERE F.IDEMPLEADO_LIDER = $idLider
              AND F.IDPERIODO        = $idP
              AND F.TIPO_FEEDBACK    = 1
              AND F.IDFEEDBACK IN (
                  SELECT MAX(IDFEEDBACK) FROM VAADINWEB.HUMFEEDBACK
                  WHERE IDEMPLEADO_LIDER = $idLider AND IDPERIODO = $idP AND TIPO_FEEDBACK = 1
                  GROUP BY IDEMPLEADO
              )
            ORDER BY HE.NOMBRE ASC";

        $res  = $this->ejecutarConsulta($sql);
        $rows = [];
        if ($res) {
            while ($r = oci_fetch_assoc($res)) {
                $r['COLABORADOR'] = fromOracleEncoding($r['COLABORADOR'] ?? '');
                $r['CARGO']       = fromOracleEncoding($r['CARGO']       ?? '');
                $r['OBSERVACION'] = fromOracleEncoding($r['OBSERVACION'] ?? '');
                $rows[] = $r;
            }
            oci_free_statement($res);
        }
        return $rows;
    }

    // ── Feedback Flujo 2 realizado por este usuario como director ────────────
    public function getFeedbackLideresRealizados(int $idDirector, ?array $periodo): array {
        $idP = $periodo ? (int)$periodo['IDPERIODO'] : 0;
        if (!$idP) return [];

        $sql = "
            SELECT F.IDFEEDBACK, F.IDEMPLEADO,
                   NVL(GE.PNOMBRE||' '||GE.SNOMBRE||' '||GE.PAPELLIDO||' '||GE.SAPELLIDO, 'Líder') AS LIDER,
                   NVL(HE.CARGO, '') AS CARGO,
                   TO_CHAR(F.FECHA_FEEDBACK,'DD/MM/YYYY HH24:MI') AS FECHA_FEEDBACK,
                   TO_CHAR(F.FECHA_FIRMA_LIDER,'DD/MM/YYYY HH24:MI') AS FECHA_FIRMA_LIDER,
                   NVL(F.FIRMADO_LIDER, 0) AS FIRMADO_LIDER,
                   F.OBSERVACION
            FROM VAADINWEB.HUMFEEDBACK F
            LEFT JOIN ZAYMAWEB.GHEMPEMPLEADOS GE ON F.IDEMPLEADO = GE.IDEMPLEADO
            LEFT JOIN VAADINWEB.HUMEMPLEADOEVAL HE
                ON F.IDEMPLEADO = HE.IDEMPLEADO AND HE.IDEMPLEADO_EVAL = $idDirector AND HE.ACTIVO = 1
            WHERE F.IDEMPLEADO_LIDER = $idDirector
              AND F.IDPERIODO        = $idP
              AND F.TIPO_FEEDBACK    = 2
              AND F.IDFEEDBACK IN (
                  SELECT MAX(IDFEEDBACK) FROM VAADINWEB.HUMFEEDBACK
                  WHERE IDEMPLEADO_LIDER = $idDirector AND IDPERIODO = $idP AND TIPO_FEEDBACK = 2
                  GROUP BY IDEMPLEADO
              )
            ORDER BY GE.PNOMBRE ASC";

        $res  = $this->ejecutarConsulta($sql);
        $rows = [];
        if ($res) {
            while ($r = oci_fetch_assoc($res)) {
                $r['LIDER']       = fromOracleEncoding($r['LIDER']       ?? '');
                $r['CARGO']       = fromOracleEncoding($r['CARGO']       ?? '');
                $r['OBSERVACION'] = fromOracleEncoding($r['OBSERVACION'] ?? '');
                $rows[] = $r;
            }
            oci_free_statement($res);
        }
        return $rows;
    }

    // ── Alertas del líder agrupadas por tipo ─────────────────────────────────
    public function getAlertasLider(int $idLider, ?array $periodo): array {
        $idP = $periodo ? (int)$periodo['IDPERIODO'] : 0;
        $vacio = ['sin_auto' => [], 'sin_eval' => [], 'sin_fb' => [], 'ac_pendientes' => []];
        if (!$idP) return $vacio;

        $sqlNombre = "NVL(GE.PNOMBRE||' '||GE.PAPELLIDO, HE.NOMBRE) AS NOMBRE, HE.CARGO";
        $joinGe    = "LEFT JOIN ZAYMAWEB.GHEMPEMPLEADOS GE ON HE.IDEMPLEADO = GE.IDEMPLEADO";
        $baseEquipo = "VAADINWEB.HUMEMPLEADOEVAL HE $joinGe
                       WHERE HE.IDEMPLEADO_EVAL = $idLider AND HE.ACTIVO = 1";

        // Sin autoevaluación
        $res = $this->ejecutarConsulta("
            SELECT $sqlNombre FROM $baseEquipo
              AND HE.IDEMPLEADO NOT IN (
                  SELECT DISTINCT IDEMPLEADO FROM VAADINWEB.HUMRESPUESTA
                  WHERE TIPO_EVAL = 'AUTO' AND CONFIRMADO = 1 AND IDPERIODO = $idP
              ) ORDER BY HE.NOMBRE ASC");
        $sinAuto = $this->fetchNombreCargo($res);

        // Sin evaluación del líder
        $res = $this->ejecutarConsulta("
            SELECT $sqlNombre FROM $baseEquipo
              AND HE.IDEMPLEADO NOT IN (
                  SELECT DISTINCT IDEMPLEADO FROM VAADINWEB.HUMRESPUESTA
                  WHERE TIPO_EVAL = 'LIDER_A_COLAB' AND IDEMPLEADO_EVAL = $idLider
                    AND CONFIRMADO = 1 AND IDPERIODO = $idP
              ) ORDER BY HE.NOMBRE ASC");
        $sinEval = $this->fetchNombreCargo($res);

        // Sin feedback registrado
        $res = $this->ejecutarConsulta("
            SELECT $sqlNombre FROM $baseEquipo
              AND NOT EXISTS (
                  SELECT 1 FROM VAADINWEB.HUMFEEDBACK F
                  WHERE F.IDEMPLEADO = HE.IDEMPLEADO AND F.IDEMPLEADO_LIDER = $idLider
                    AND F.IDPERIODO = $idP AND F.TIPO_FEEDBACK = 1
              ) ORDER BY HE.NOMBRE ASC");
        $sinFb = $this->fetchNombreCargo($res);

        // Acuerdos Flujo 1 pendientes (P1-P11)
        $res = $this->ejecutarConsulta("
            SELECT NVL(GE.PNOMBRE||' '||GE.SNOMBRE||' '||GE.PAPELLIDO||' '||GE.SAPELLIDO, HE.NOMBRE) AS COLABORADOR,
                   HE.CARGO, O.OBJETIVO, A.PLAZO, A.META
            FROM VAADINWEB.HUMACUERDOMEJORA A
            INNER JOIN VAADINWEB.HUMOBJETIVOMEJORA O  ON A.IDOBJETIVO = O.IDOBJETIVO
            INNER JOIN VAADINWEB.HUMEMPLEADOEVAL   HE ON A.IDEMPLEADO = HE.IDEMPLEADO
            LEFT  JOIN ZAYMAWEB.GHEMPEMPLEADOS     GE ON HE.IDEMPLEADO = GE.IDEMPLEADO
            WHERE A.IDEMPLEADO_LIDER = $idLider AND A.IDPERIODO = $idP
              AND A.ESTADO = 'PENDIENTE' AND A.NUM_COMPETENCIA BETWEEN 1 AND 11
            ORDER BY HE.NOMBRE ASC");
        $acPend = [];
        if ($res) {
            while ($r = oci_fetch_assoc($res)) {
                $acPend[] = [
                    'COLABORADOR' => fromOracleEncoding($r['COLABORADOR'] ?? ''),
                    'CARGO'       => fromOracleEncoding($r['CARGO']       ?? ''),
                    'OBJETIVO'    => fromOracleEncoding($r['OBJETIVO']    ?? ''),
                    'PLAZO'       => fromOracleEncoding($r['PLAZO']       ?? ''),
                    'META'        => fromOracleEncoding($r['META']        ?? ''),
                ];
            }
            oci_free_statement($res);
        }

        // Líderes sin feedback Flujo 2 de este director
        $res = $this->ejecutarConsulta("
            SELECT $sqlNombre FROM $baseEquipo
              AND HE.ES_LIDER_FUNCIONAL = 1
              AND NOT EXISTS (
                  SELECT 1 FROM VAADINWEB.HUMFEEDBACK F
                  WHERE F.IDEMPLEADO = HE.IDEMPLEADO AND F.IDEMPLEADO_LIDER = $idLider
                    AND F.IDPERIODO = $idP AND F.TIPO_FEEDBACK = 2
              ) ORDER BY HE.NOMBRE ASC");
        $sinFb2 = $this->fetchNombreCargo($res);

        // Acuerdos Flujo 2 pendientes (P12-P16)
        $res = $this->ejecutarConsulta("
            SELECT NVL(GE.PNOMBRE||' '||GE.SNOMBRE||' '||GE.PAPELLIDO||' '||GE.SAPELLIDO, HE.NOMBRE) AS COLABORADOR,
                   HE.CARGO, O.OBJETIVO, A.PLAZO, A.META
            FROM VAADINWEB.HUMACUERDOMEJORA A
            INNER JOIN VAADINWEB.HUMOBJETIVOMEJORA O  ON A.IDOBJETIVO = O.IDOBJETIVO
            INNER JOIN VAADINWEB.HUMEMPLEADOEVAL   HE ON A.IDEMPLEADO = HE.IDEMPLEADO
            LEFT  JOIN ZAYMAWEB.GHEMPEMPLEADOS     GE ON HE.IDEMPLEADO = GE.IDEMPLEADO
            WHERE A.IDEMPLEADO_LIDER = $idLider AND A.IDPERIODO = $idP
              AND A.ESTADO = 'PENDIENTE' AND A.NUM_COMPETENCIA BETWEEN 12 AND 16
            ORDER BY HE.NOMBRE ASC");
        $acPend2 = [];
        if ($res) {
            while ($r = oci_fetch_assoc($res)) {
                $acPend2[] = [
                    'COLABORADOR' => fromOracleEncoding($r['COLABORADOR'] ?? ''),
                    'CARGO'       => fromOracleEncoding($r['CARGO']       ?? ''),
                    'OBJETIVO'    => fromOracleEncoding($r['OBJETIVO']    ?? ''),
                    'PLAZO'       => fromOracleEncoding($r['PLAZO']       ?? ''),
                    'META'        => fromOracleEncoding($r['META']        ?? ''),
                ];
            }
            oci_free_statement($res);
        }

        return [
            'sin_auto'       => $sinAuto,
            'sin_eval'       => $sinEval,
            'sin_fb'         => $sinFb,
            'ac_pendientes'  => $acPend,
            'sin_fb_2'       => $sinFb2,
            'ac_pendientes_2'=> $acPend2,
        ];
    }

    private function fetchNombreCargo($res): array {
        $rows = [];
        if ($res) {
            while ($r = oci_fetch_assoc($res)) {
                $rows[] = [
                    'NOMBRE' => fromOracleEncoding($r['NOMBRE'] ?? ''),
                    'CARGO'  => fromOracleEncoding($r['CARGO']  ?? ''),
                ];
            }
            oci_free_statement($res);
        }
        return $rows;
    }

    // ── Perfil de un colaborador visto por su líder (AJAX modal) ─────────────
    public function getPerfilColaboradorLider(int $idColab, int $idLider, ?array $periodo): array {
        $idP = $periodo ? (int)$periodo['IDPERIODO'] : 0;

        // Seguridad: verificar que el colaborador pertenece a este líder
        $sqlCheck = "SELECT COUNT(*) AS CNT FROM VAADINWEB.HUMEMPLEADOEVAL
                     WHERE IDEMPLEADO = $idColab AND IDEMPLEADO_EVAL = $idLider AND ACTIVO = 1";
        $resCheck = $this->ejecutarConsulta($sqlCheck);
        $rowCheck = $resCheck ? oci_fetch_assoc($resCheck) : null;
        if ($resCheck) oci_free_statement($resCheck);
        if (!$rowCheck || (int)$rowCheck['CNT'] === 0) {
            return ['error' => 'Acceso no permitido'];
        }

        // Info del colaborador
        $sqlEmp = "SELECT * FROM (
            SELECT NVL(GE.PNOMBRE||' '||GE.PAPELLIDO, HE.NOMBRE) AS NOMBRE,
                   HE.CARGO, HE.PROCESO
            FROM VAADINWEB.HUMEMPLEADOEVAL HE
            LEFT JOIN ZAYMAWEB.GHEMPEMPLEADOS GE ON HE.IDEMPLEADO = GE.IDEMPLEADO
            WHERE HE.IDEMPLEADO = $idColab
        ) WHERE ROWNUM = 1";
        $resEmp = $this->ejecutarConsulta($sqlEmp);
        $emp    = $resEmp ? oci_fetch_assoc($resEmp) : [];
        if ($resEmp) oci_free_statement($resEmp);
        foreach (['NOMBRE','CARGO','PROCESO'] as $f) {
            if (isset($emp[$f])) $emp[$f] = fromOracleEncoding($emp[$f]);
        }

        // Calificaciones del líder al colaborador
        $califs = [];
        if ($idP) {
            $sqlCal = "SELECT C.NOMBRE AS NOMBRE_COMP, C.NUM_PREGUNTA, R.VALOR, O.ETIQUETA
                       FROM VAADINWEB.HUMRESPUESTA R
                       INNER JOIN VAADINWEB.HUMCOMPETENCIA   C ON R.IDCOMPETENCIA = C.IDCOMPETENCIA
                       INNER JOIN VAADINWEB.HUMOPCIONESCALA  O ON R.IDOPCION      = O.IDOPCION
                       WHERE R.IDEMPLEADO      = $idColab
                         AND R.IDEMPLEADO_EVAL = $idLider
                         AND R.TIPO_EVAL       = 'LIDER_A_COLAB'
                         AND R.CONFIRMADO      = 1
                         AND R.IDPERIODO       = $idP
                       ORDER BY C.NUM_PREGUNTA";
            $resCal = $this->ejecutarConsulta($sqlCal);
            if ($resCal) {
                while ($r = oci_fetch_assoc($resCal)) {
                    $r['NOMBRE_COMP'] = fromOracleEncoding($r['NOMBRE_COMP'] ?? '');
                    $r['ETIQUETA']    = fromOracleEncoding($r['ETIQUETA']    ?? '');
                    $califs[] = $r;
                }
                oci_free_statement($resCal);
            }
        }

        // Acuerdos asignados por este líder a este colaborador
        $acuerdos = [];
        if ($idP) {
            $sqlAc = "SELECT A.IDACUERDO, O.OBJETIVO, C.NOMBRE AS NOMBRE_COMP,
                             A.ESTADO, A.META, A.PLAZO, A.PLAN_ACCION
                      FROM VAADINWEB.HUMACUERDOMEJORA  A
                      INNER JOIN VAADINWEB.HUMOBJETIVOMEJORA O ON A.IDOBJETIVO = O.IDOBJETIVO
                      LEFT  JOIN VAADINWEB.HUMCOMPETENCIA    C ON C.NUM_PREGUNTA = A.NUM_COMPETENCIA AND C.ACTIVO = 1
                      WHERE A.IDEMPLEADO       = $idColab
                        AND A.IDEMPLEADO_LIDER = $idLider
                        AND A.IDPERIODO        = $idP
                        AND A.NUM_COMPETENCIA BETWEEN 1 AND 11
                      ORDER BY A.NUM_COMPETENCIA";
            $resAc = $this->ejecutarConsulta($sqlAc);
            if ($resAc) {
                while ($r = oci_fetch_assoc($resAc)) {
                    foreach (['OBJETIVO','NOMBRE_COMP','META','PLAZO','PLAN_ACCION'] as $f) {
                        if (isset($r[$f])) $r[$f] = fromOracleEncoding($r[$f]);
                    }
                    $acuerdos[] = $r;
                }
                oci_free_statement($resAc);
            }
        }

        // Feedback Flujo 1 de este líder a este colaborador
        $fb = null;
        if ($idP) {
            $sqlFb = "SELECT * FROM (
                SELECT TO_CHAR(F.FECHA_FEEDBACK,'DD/MM/YYYY HH24:MI') AS FECHA_FEEDBACK,
                       TO_CHAR(F.FECHA_FIRMA,'DD/MM/YYYY HH24:MI')    AS FECHA_FIRMA,
                       NVL(F.FIRMADO_LIDER,0) AS FIRMADO_LIDER,
                       NVL(F.FIRMADO_COLAB,0) AS FIRMADO_COLAB,
                       F.OBSERVACION
                FROM VAADINWEB.HUMFEEDBACK F
                WHERE F.IDEMPLEADO       = $idColab
                  AND F.IDEMPLEADO_LIDER = $idLider
                  AND F.IDPERIODO        = $idP
                  AND F.TIPO_FEEDBACK    = 1
                ORDER BY F.IDFEEDBACK DESC
            ) WHERE ROWNUM = 1";
            $resFb = $this->ejecutarConsulta($sqlFb);
            $fb    = $resFb ? oci_fetch_assoc($resFb) : null;
            if ($resFb) oci_free_statement($resFb);
            if ($fb) $fb['OBSERVACION'] = fromOracleEncoding($fb['OBSERVACION'] ?? '');
        }

        // ── Datos Flujo 2 si el colaborador es también líder funcional ───────
        $esLiderColab   = false;
        $califsLider    = [];
        $feedbackFlujo2 = null;
        $acuerdosFlujo2 = [];

        $sqlEsLf = "SELECT NVL(ES_LIDER_FUNCIONAL, 0) AS ES_LF
                    FROM VAADINWEB.HUMEMPLEADOEVAL
                    WHERE IDEMPLEADO = $idColab AND ACTIVO = 1 AND ROWNUM = 1";
        $resEsLf = $this->ejecutarConsulta($sqlEsLf);
        $rowEsLf = $resEsLf ? oci_fetch_assoc($resEsLf) : null;
        if ($resEsLf) oci_free_statement($resEsLf);
        $esLiderColab = $rowEsLf && (int)$rowEsLf['ES_LF'] === 1;

        if ($idP && $esLiderColab) {
            // Promedio de calificaciones recibidas como líder (COLAB_A_LIDER, P12-P16)
            $sqlCalL = "SELECT C.NOMBRE AS NOMBRE_COMP, C.NUM_PREGUNTA,
                               ROUND(AVG(R.VALOR * 1.0), 0) AS VALOR_PROM
                        FROM VAADINWEB.HUMRESPUESTA R
                        INNER JOIN VAADINWEB.HUMCOMPETENCIA C ON R.IDCOMPETENCIA = C.IDCOMPETENCIA
                        WHERE R.IDEMPLEADO   = $idColab
                          AND R.TIPO_EVAL    = 'COLAB_A_LIDER'
                          AND R.CONFIRMADO   = 1
                          AND R.IDPERIODO    = $idP
                          AND C.NUM_PREGUNTA BETWEEN 12 AND 16
                        GROUP BY C.NOMBRE, C.NUM_PREGUNTA
                        ORDER BY C.NUM_PREGUNTA";
            $resCalL = $this->ejecutarConsulta($sqlCalL);
            if ($resCalL) {
                while ($r = oci_fetch_assoc($resCalL)) {
                    $r['NOMBRE_COMP'] = fromOracleEncoding($r['NOMBRE_COMP'] ?? '');
                    $califsLider[] = $r;
                }
                oci_free_statement($resCalL);
            }

            // Feedback Flujo 2 que recibió como líder (del director)
            $sqlFb2 = "SELECT * FROM (
                SELECT TO_CHAR(F.FECHA_FEEDBACK,'DD/MM/YYYY HH24:MI')      AS FECHA_FEEDBACK,
                       TO_CHAR(F.FECHA_FIRMA_LIDER,'DD/MM/YYYY HH24:MI')   AS FECHA_FIRMA_LIDER,
                       NVL(F.FIRMADO_LIDER, 0) AS FIRMADO_LIDER,
                       F.OBSERVACION,
                       NVL(GE.PNOMBRE||' '||GE.PAPELLIDO, 'Director') AS NOMBRE_DIRECTOR
                FROM VAADINWEB.HUMFEEDBACK F
                LEFT JOIN ZAYMAWEB.GHEMPEMPLEADOS GE ON F.IDEMPLEADO_LIDER = GE.IDEMPLEADO
                WHERE F.IDEMPLEADO     = $idColab
                  AND F.IDPERIODO      = $idP
                  AND F.TIPO_FEEDBACK  = 2
                ORDER BY F.IDFEEDBACK DESC
            ) WHERE ROWNUM = 1";
            $resFb2 = $this->ejecutarConsulta($sqlFb2);
            $feedbackFlujo2 = $resFb2 ? oci_fetch_assoc($resFb2) : null;
            if ($resFb2) oci_free_statement($resFb2);
            if ($feedbackFlujo2) {
                $feedbackFlujo2['OBSERVACION']     = fromOracleEncoding($feedbackFlujo2['OBSERVACION']     ?? '');
                $feedbackFlujo2['NOMBRE_DIRECTOR'] = fromOracleEncoding($feedbackFlujo2['NOMBRE_DIRECTOR'] ?? '');
            }

            // Acuerdos Flujo 2 (P12-P16 como líder evaluado)
            $sqlAc2 = "SELECT A.IDACUERDO, O.OBJETIVO, C.NOMBRE AS NOMBRE_COMP,
                              A.ESTADO, A.META, A.PLAZO, A.PLAN_ACCION
                       FROM VAADINWEB.HUMACUERDOMEJORA  A
                       INNER JOIN VAADINWEB.HUMOBJETIVOMEJORA O ON A.IDOBJETIVO = O.IDOBJETIVO
                       LEFT  JOIN VAADINWEB.HUMCOMPETENCIA    C
                           ON C.NUM_PREGUNTA = A.NUM_COMPETENCIA AND C.ACTIVO = 1
                       WHERE A.IDEMPLEADO = $idColab
                         AND A.IDPERIODO  = $idP
                         AND A.NUM_COMPETENCIA BETWEEN 12 AND 16
                       ORDER BY A.NUM_COMPETENCIA";
            $resAc2 = $this->ejecutarConsulta($sqlAc2);
            if ($resAc2) {
                while ($r = oci_fetch_assoc($resAc2)) {
                    foreach (['OBJETIVO','NOMBRE_COMP','META','PLAZO','PLAN_ACCION'] as $f) {
                        if (isset($r[$f])) $r[$f] = fromOracleEncoding($r[$f]);
                    }
                    $acuerdosFlujo2[] = $r;
                }
                oci_free_statement($resAc2);
            }
        }

        return [
            'empleado'       => $emp,
            'califs'         => $califs,
            'califsLider'    => $califsLider,
            'acuerdos'       => $acuerdos,
            'acuerdosFlujo2' => $acuerdosFlujo2,
            'feedback'       => $fb,
            'feedbackFlujo2' => $feedbackFlujo2,
            'esLider'        => $esLiderColab,
        ];
    }
}
