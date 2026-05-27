<?php
namespace app\models;

use app\models\mainModel;
use app\models\competenciaModel;

/**
 * reportModel — Modelo de reportes V2
 *
 * Refactorizado para leer de HUMRESPUESTA en vez de las tablas V1.
 * Los diccionarios de competencias ahora vienen de HUMCOMPETENCIA via competenciaModel.
 */
class reportModel extends mainModel {

    // ── Diccionarios — ahora leen de BD via competenciaModel ─────────────────

    public static function getDictColaborador(): array {
        return competenciaModel::getDictColaborador();
    }

    public static function getDictLiderazgo(): array {
        return competenciaModel::getDictLiderazgo();
    }

    // ── Período activo ────────────────────────────────────────────────────────

    public function getPeriodoActivo(): ?array {
        $sql = "SELECT IDPERIODO, NOMBRE,
                       TO_CHAR(FECHAAPERTURA,'DD/MM/YYYY') AS FECHAAPERTURA,
                       TO_CHAR(FECHACIERRE,  'DD/MM/YYYY') AS FECHACIERRE,
                       ESTADO
                FROM VAADINWEB.HUMPERIODOEVALUACION
                WHERE ESTADO = 1 AND ROWNUM = 1";
        return $this->ejecutarConsultaUnicaUTF8($sql);
    }

    // ── Helper: filtro de período para HUMRESPUESTA ───────────────────────────

    private function filtroPeriodo(?array $periodo, string $campoFecha = 'R.FECHARESPUESTA'): string {
        if (!$periodo) return '';
        // Usar IDPERIODO cuando está disponible — más preciso que filtrar por fechas
        if (!empty($periodo['IDPERIODO'])) {
            return " AND R.IDPERIODO = " . (int)$periodo['IDPERIODO'];
        }
        $ap = $periodo['FECHAAPERTURA'];
        $ci = $periodo['FECHACIERRE'];
        return " AND TRUNC($campoFecha) BETWEEN TO_DATE('$ap','DD/MM/YYYY') AND TO_DATE('$ci','DD/MM/YYYY')";
    }

    // ── Helper: convierte filas con valores por competencia ──────────────────

    private function utf8Row(array $row): array {
        foreach ($row as $k => $v) {
            if (is_string($v)) {
                $row[$k] = fromOracleEncoding($v);
            }
        }
        return $row;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // AUTOEVALUACIÓN
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Devuelve la autoevaluación confirmada del período
     * Formato: [IDCOMPETENCIA => ['VALOR'=>N, 'ETIQUETA'=>'...', 'NUM_PREGUNTA'=>N]]
     */
    public function getAutoEvaluacion(int $idempleado, ?array $periodo = null): array {
        $filtro = $this->filtroPeriodo($periodo, 'R.FECHACONFIRMA');
        $sql = "SELECT R.IDCOMPETENCIA, R.VALOR, O.ETIQUETA, C.NUM_PREGUNTA,
                       C.NOMBRE AS NOMBRE_COMP
                FROM VAADINWEB.HUMRESPUESTA R
                INNER JOIN VAADINWEB.HUMOPCIONESCALA O ON R.IDOPCION = O.IDOPCION
                INNER JOIN VAADINWEB.HUMCOMPETENCIA  C ON R.IDCOMPETENCIA = C.IDCOMPETENCIA
                WHERE R.IDEMPLEADO      = $idempleado
                  AND R.IDEMPLEADO_EVAL = $idempleado
                  AND R.TIPO_EVAL       = 'AUTO'
                  AND R.CONFIRMADO      = 1
                  $filtro
                ORDER BY C.NUM_PREGUNTA";
        $rows = $this->ejecutarConsultaUTF8($sql);
        $result = [];
        foreach ($rows as $r) {
            $result[(int)$r['IDCOMPETENCIA']] = [
                'VALOR'        => (int)$r['VALOR'],
                'ETIQUETA'     => $r['ETIQUETA'] ?? '',
                'NOMBRE_COMP'  => $r['NOMBRE_COMP'] ?? '',
                'NUM_PREGUNTA' => (int)$r['NUM_PREGUNTA'],
            ];
        }
        return $result;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // EVALUACIONES RECIBIDAS
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Evaluaciones recibidas de colaboradores (líder evaluó al colaborador)
     * Devuelve una fila por evaluador con sus respuestas agrupadas
     */
    public function getEvaluacionesRecibidasColaborador(int $idempleado, ?array $periodo = null): array {
        $filtro = $this->filtroPeriodo($periodo, 'R.FECHACONFIRMA');
        $sql = "SELECT R.IDEMPLEADO_EVAL AS ID_EVALUADOR,
                       NVL(EM.EMPLEADO, 'Anónimo') AS EVALUADOR,
                       NVL(EM.CARGO, 'N/A') AS CARGO_EVALUADOR,
                       R.IDCOMPETENCIA, R.VALOR, O.ETIQUETA,
                       C.NUM_PREGUNTA, C.NOMBRE AS NOMBRE_COMP,
                       TO_CHAR(MAX(R.FECHACONFIRMA),'DD/MM/YYYY') AS FECHACONFIRMA
                FROM VAADINWEB.HUMRESPUESTA R
                INNER JOIN VAADINWEB.HUMOPCIONESCALA O ON R.IDOPCION = O.IDOPCION
                INNER JOIN VAADINWEB.HUMCOMPETENCIA  C ON R.IDCOMPETENCIA = C.IDCOMPETENCIA
                LEFT  JOIN ZAYMAWEB.VST_GHEMPEMPLEADOSCARGOS EM
                    ON R.IDEMPLEADO_EVAL = EM.IDEMPLEADO AND EM.ESPRINCIPAL = 1
                WHERE R.IDEMPLEADO  = $idempleado
                  AND R.TIPO_EVAL   = 'LIDER_A_COLAB'
                  AND R.CONFIRMADO  = 1
                  $filtro
                GROUP BY R.IDEMPLEADO_EVAL, EM.EMPLEADO, EM.CARGO,
                         R.IDCOMPETENCIA, R.VALOR, O.ETIQUETA, C.NUM_PREGUNTA, C.NOMBRE
                ORDER BY R.IDEMPLEADO_EVAL, C.NUM_PREGUNTA";
        $res  = $this->ejecutarConsulta($sql);
        $rows = [];
        if ($res) {
            while ($r = oci_fetch_assoc($res)) {
                $idEval = (int)$r['ID_EVALUADOR'];
                if (!isset($rows[$idEval])) {
                    $rows[$idEval] = [
                        'ID_EVALUADOR'   => $idEval,
                        'EVALUADOR'      => fromOracleEncoding($r['EVALUADOR']      ?? ''),
                        'CARGO_EVALUADOR'=> fromOracleEncoding($r['CARGO_EVALUADOR']?? ''),
                        'FECHACONFIRMA'  => $r['FECHACONFIRMA'],
                        'RESPUESTAS'     => [],
                    ];
                }
                $rows[$idEval]['RESPUESTAS'][(int)$r['IDCOMPETENCIA']] = [
                    'VALOR'        => (int)$r['VALOR'],
                    'ETIQUETA'     => fromOracleEncoding($r['ETIQUETA']    ?? ''),
                    'NOMBRE_COMP'  => fromOracleEncoding($r['NOMBRE_COMP'] ?? ''),
                    'NUM_PREGUNTA' => (int)$r['NUM_PREGUNTA'],
                ];
            }
            oci_free_statement($res);
        }
        return array_values($rows);
    }

    /**
     * Evaluaciones de liderazgo recibidas (colaboradores evaluaron al líder)
     */
    public function getEvaluacionesRecibidasLiderazgo(int $idempleado, ?array $periodo = null): array {
        $filtro = $this->filtroPeriodo($periodo, 'R.FECHACONFIRMA');
        $sql = "SELECT R.IDEMPLEADO_EVAL AS ID_EVALUADOR,
                       NVL(EM.EMPLEADO, 'Anónimo') AS EVALUADOR,
                       NVL(EM.CARGO, 'N/A') AS CARGO_EVALUADOR,
                       R.IDCOMPETENCIA, R.VALOR, O.ETIQUETA,
                       C.NUM_PREGUNTA, C.NOMBRE AS NOMBRE_COMP,
                       TO_CHAR(MAX(R.FECHACONFIRMA),'DD/MM/YYYY') AS FECHACONFIRMA
                FROM VAADINWEB.HUMRESPUESTA R
                INNER JOIN VAADINWEB.HUMOPCIONESCALA O ON R.IDOPCION = O.IDOPCION
                INNER JOIN VAADINWEB.HUMCOMPETENCIA  C ON R.IDCOMPETENCIA = C.IDCOMPETENCIA
                LEFT  JOIN ZAYMAWEB.VST_GHEMPEMPLEADOSCARGOS EM
                    ON R.IDEMPLEADO_EVAL = EM.IDEMPLEADO AND EM.ESPRINCIPAL = 1
                WHERE R.IDEMPLEADO  = $idempleado
                  AND R.TIPO_EVAL   = 'COLAB_A_LIDER'
                  AND R.CONFIRMADO  = 1
                  $filtro
                GROUP BY R.IDEMPLEADO_EVAL, EM.EMPLEADO, EM.CARGO,
                         R.IDCOMPETENCIA, R.VALOR, O.ETIQUETA, C.NUM_PREGUNTA, C.NOMBRE
                ORDER BY R.IDEMPLEADO_EVAL, C.NUM_PREGUNTA";
        $res  = $this->ejecutarConsulta($sql);
        $rows = [];
        if ($res) {
            while ($r = oci_fetch_assoc($res)) {
                $idEval = (int)$r['ID_EVALUADOR'];
                if (!isset($rows[$idEval])) {
                    $rows[$idEval] = [
                        'ID_EVALUADOR'   => $idEval,
                        'EVALUADOR'      => fromOracleEncoding($r['EVALUADOR']      ?? ''),
                        'CARGO_EVALUADOR'=> fromOracleEncoding($r['CARGO_EVALUADOR']?? ''),
                        'FECHACONFIRMA'  => $r['FECHACONFIRMA'],
                        'RESPUESTAS'     => [],
                    ];
                }
                $rows[$idEval]['RESPUESTAS'][(int)$r['IDCOMPETENCIA']] = [
                    'VALOR'        => (int)$r['VALOR'],
                    'ETIQUETA'     => fromOracleEncoding($r['ETIQUETA']    ?? ''),
                    'NOMBRE_COMP'  => fromOracleEncoding($r['NOMBRE_COMP'] ?? ''),
                    'NUM_PREGUNTA' => (int)$r['NUM_PREGUNTA'],
                ];
            }
            oci_free_statement($res);
        }
        return array_values($rows);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // EVALUACIONES REALIZADAS
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Evaluaciones realizadas a colaboradores (el líder evaluó a otros)
     */
    public function getEvaluacionesRealizadasColaborador(int $idempleado, ?array $periodo = null): array {
        $filtro = $this->filtroPeriodo($periodo, 'R.FECHACONFIRMA');
        $sql = "SELECT DISTINCT R.IDEMPLEADO AS ID_EVALUADO,
                       NVL(EM.EMPLEADO, 'Desconocido') AS EVALUADO,
                       NVL(EM.CARGO, 'N/A') AS CARGO_EVALUADO,
                       TO_CHAR(MAX(R.FECHACONFIRMA) OVER (PARTITION BY R.IDEMPLEADO),'DD/MM/YYYY') AS FECHACONFIRMA
                FROM VAADINWEB.HUMRESPUESTA R
                LEFT JOIN ZAYMAWEB.VST_GHEMPEMPLEADOSCARGOS EM
                    ON R.IDEMPLEADO = EM.IDEMPLEADO AND EM.ESPRINCIPAL = 1
                WHERE R.IDEMPLEADO_EVAL = $idempleado
                  AND R.TIPO_EVAL       = 'LIDER_A_COLAB'
                  AND R.CONFIRMADO      = 1
                  $filtro
                ORDER BY EVALUADO";
        $res  = $this->ejecutarConsulta($sql);
        $rows = [];
        if ($res) {
            while ($r = oci_fetch_assoc($res)) {
                $rows[] = $this->utf8Row($r);
            }
            oci_free_statement($res);
        }
        return $rows;
    }

    /**
     * Evaluaciones de liderazgo realizadas (colaborador evaluó a su líder)
     */
    public function getEvaluacionesRealizadasLiderazgo(int $idempleado, ?array $periodo = null): array {
        $filtro = $this->filtroPeriodo($periodo, 'R.FECHACONFIRMA');
        $sql = "SELECT DISTINCT R.IDEMPLEADO AS ID_EVALUADO,
                       NVL(EM.EMPLEADO, 'Desconocido') AS EVALUADO,
                       NVL(EM.CARGO, 'N/A') AS CARGO_EVALUADO,
                       TO_CHAR(MAX(R.FECHACONFIRMA) OVER (PARTITION BY R.IDEMPLEADO),'DD/MM/YYYY') AS FECHACONFIRMA
                FROM VAADINWEB.HUMRESPUESTA R
                LEFT JOIN ZAYMAWEB.VST_GHEMPEMPLEADOSCARGOS EM
                    ON R.IDEMPLEADO = EM.IDEMPLEADO AND EM.ESPRINCIPAL = 1
                WHERE R.IDEMPLEADO_EVAL = $idempleado
                  AND R.TIPO_EVAL       = 'COLAB_A_LIDER'
                  AND R.CONFIRMADO      = 1
                  $filtro
                ORDER BY EVALUADO";
        $res  = $this->ejecutarConsulta($sql);
        $rows = [];
        if ($res) {
            while ($r = oci_fetch_assoc($res)) {
                $rows[] = $this->utf8Row($r);
            }
            oci_free_statement($res);
        }
        return $rows;
    }


    /**
     * Calificaciones que dio el evaluador actual a un colaborador específico
     */
    public function getCalificacionesRealizadas(int $idEvaluador, int $idEvaluado, string $tipoEval, ?array $periodo = null): array {
        $tipoEval = in_array($tipoEval, ['LIDER_A_COLAB','COLAB_A_LIDER']) ? $tipoEval : 'LIDER_A_COLAB';
        $filtro   = $this->filtroPeriodo($periodo, 'R.FECHACONFIRMA');
        $sql = "SELECT C.NOMBRE AS NOMBRE_COMP,
                       C.NUM_PREGUNTA,
                       R.VALOR
                FROM VAADINWEB.HUMRESPUESTA R
                INNER JOIN VAADINWEB.HUMCOMPETENCIA C ON R.IDCOMPETENCIA = C.IDCOMPETENCIA
                WHERE R.IDEMPLEADO_EVAL = $idEvaluador
                  AND R.IDEMPLEADO      = $idEvaluado
                  AND R.TIPO_EVAL       = '$tipoEval'
                  AND R.CONFIRMADO      = 1
                  $filtro
                ORDER BY C.NUM_PREGUNTA";
        $res  = $this->ejecutarConsulta($sql);
        $rows = [];
        if ($res) {
            while ($r = oci_fetch_assoc($res)) {
                $r['NOMBRE_COMP'] = fromOracleEncoding($r['NOMBRE_COMP'] ?? '');
                $rows[] = $r;
            }
            oci_free_statement($res);
        }
        return $rows;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // INDICADORES Y PROMEDIOS
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Indicadores de desempeño — promedios por competencia (1-11)
     * recibidos de evaluadores (LIDER_A_COLAB)
     * [IDCOMPETENCIA => ['PROMEDIO'=>3.5, 'NUM_PREGUNTA'=>1, 'NOMBRE_COMP'=>'...']]
     */
    public function getIndicadoresDesempeno(int $idempleado, ?array $periodo = null): array {
        $filtro = $this->filtroPeriodo($periodo, 'R.FECHACONFIRMA');
        $sql = "SELECT R.IDCOMPETENCIA,
                       ROUND(AVG(R.VALOR), 2) AS PROMEDIO,
                       COUNT(*)               AS TOTAL_EVAL,
                       C.NUM_PREGUNTA,
                       C.NOMBRE               AS NOMBRE_COMP
                FROM VAADINWEB.HUMRESPUESTA R
                INNER JOIN VAADINWEB.HUMCOMPETENCIA C ON R.IDCOMPETENCIA = C.IDCOMPETENCIA
                WHERE R.IDEMPLEADO  = $idempleado
                  AND R.TIPO_EVAL   = 'LIDER_A_COLAB'
                  AND R.CONFIRMADO  = 1
                  AND C.NUM_PREGUNTA BETWEEN 1 AND 11
                  $filtro
                GROUP BY R.IDCOMPETENCIA, C.NUM_PREGUNTA, C.NOMBRE
                ORDER BY C.NUM_PREGUNTA";
        $res  = $this->ejecutarConsulta($sql);
        $rows = [];
        if ($res) {
            while ($r = oci_fetch_assoc($res)) {
                $rows[(int)$r['IDCOMPETENCIA']] = [
                    'PROMEDIO'    => (float)str_replace(',', '.', $r['PROMEDIO']),
                    'TOTAL_EVAL'  => (int)$r['TOTAL_EVAL'],
                    'NUM_PREGUNTA'=> (int)$r['NUM_PREGUNTA'],
                    'NOMBRE_COMP' => fromOracleEncoding($r['NOMBRE_COMP'] ?? ''),
                ];
            }
            oci_free_statement($res);
        }
        return $rows;
    }

    /**
     * Indicadores de liderazgo — promedios por competencia (12-16)
     * recibidos de colaboradores (COLAB_A_LIDER)
     */
    public function getIndicadoresLiderazgo(int $idempleado, ?array $periodo = null): array {
        $filtro = $this->filtroPeriodo($periodo, 'R.FECHACONFIRMA');
        $sql = "SELECT R.IDCOMPETENCIA,
                       ROUND(AVG(R.VALOR), 2) AS PROMEDIO,
                       COUNT(*)               AS TOTAL_EVAL,
                       C.NUM_PREGUNTA,
                       C.NOMBRE               AS NOMBRE_COMP
                FROM VAADINWEB.HUMRESPUESTA R
                INNER JOIN VAADINWEB.HUMCOMPETENCIA C ON R.IDCOMPETENCIA = C.IDCOMPETENCIA
                WHERE R.IDEMPLEADO  = $idempleado
                  AND R.TIPO_EVAL   = 'COLAB_A_LIDER'
                  AND R.CONFIRMADO  = 1
                  AND C.NUM_PREGUNTA BETWEEN 12 AND 16
                  $filtro
                GROUP BY R.IDCOMPETENCIA, C.NUM_PREGUNTA, C.NOMBRE
                ORDER BY C.NUM_PREGUNTA";
        $res  = $this->ejecutarConsulta($sql);
        $rows = [];
        if ($res) {
            while ($r = oci_fetch_assoc($res)) {
                $rows[(int)$r['IDCOMPETENCIA']] = [
                    'PROMEDIO'    => (float)str_replace(',', '.', $r['PROMEDIO']),
                    'TOTAL_EVAL'  => (int)$r['TOTAL_EVAL'],
                    'NUM_PREGUNTA'=> (int)$r['NUM_PREGUNTA'],
                    'NOMBRE_COMP' => fromOracleEncoding($r['NOMBRE_COMP'] ?? ''),
                ];
            }
            oci_free_statement($res);
        }
        return $rows;
    }

    /**
     * Devuelve auto y lider más recientes para comparativa
     * Reemplaza getLatestAutoAndLider() — ahora devuelve promedios
     */
    public function getLatestAutoAndLider(int $idempleado): array {
        $resultado = ['auto' => [], 'lider' => []];
        $periodo   = $this->getPeriodoActivo();

        $resultado['auto']  = $this->getAutoEvaluacion($idempleado, $periodo);
        $resultado['lider'] = $this->getIndicadoresDesempeno($idempleado, $periodo);

        return $resultado;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // EVALUADORES
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Lista de evaluadores únicos que han evaluado a un empleado
     * Reemplaza obtenerEvaluadores() — una sola consulta a HUMRESPUESTA
     */
    public function obtenerEvaluadores(int $idempleado): array {
        $sql = "SELECT DISTINCT R.IDEMPLEADO_EVAL AS ID_EVALUADOR,
                       NVL(EM.EMPLEADO, 'Desconocido') AS EMPLEADO,
                       NVL(EM.CARGO, 'N/A') AS CARGO,
                       TO_CHAR(MAX(R.FECHACONFIRMA) OVER (PARTITION BY R.IDEMPLEADO_EVAL),'DD/MM/YYYY') AS FECHACONFIRMA
                FROM VAADINWEB.HUMRESPUESTA R
                LEFT JOIN ZAYMAWEB.VST_GHEMPEMPLEADOSCARGOS EM
                    ON R.IDEMPLEADO_EVAL = EM.IDEMPLEADO AND EM.ESPRINCIPAL = 1
                WHERE R.IDEMPLEADO  = $idempleado
                  AND R.CONFIRMADO  = 1
                ORDER BY EMPLEADO";
        $res  = $this->ejecutarConsulta($sql);
        $rows = [];
        if ($res) {
            while ($r = oci_fetch_assoc($res)) {
                $rows[] = $this->utf8Row($r);
            }
            oci_free_statement($res);
        }
        return $rows;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // EQUIPO A CARGO
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Estado del equipo a cargo — ahora lee HUMRESPUESTA
     * TIENE_AUTOEVAL: el colaborador hizo su AUTO
     * TIENE_EVAL_COLAB: el líder ya lo evaluó (LIDER_A_COLAB)
     */
    public function getEquipoACargo(int $idempleado, string $nivelCargo, ?array $periodo = null): array {
        $idempleado = (int)$idempleado;
        $filtroAuto  = "R_A.CONFIRMADO = 1";
        $filtroColab = "R_C.CONFIRMADO = 1";
        if ($periodo) {
            $idP         = (int)$periodo['IDPERIODO'];
            $filtroAuto  = "R_A.CONFIRMADO = 1 AND R_A.IDPERIODO = $idP";
            $filtroColab = "R_C.CONFIRMADO = 1 AND R_C.IDPERIODO = $idP";
        }

        // Usar HUMEMPLEADOEVAL — fuente oficial desde Excel del director
        $subQueryPersonas = "
            SELECT HE.IDEMPLEADO,
                   NVL(GE.PNOMBRE||' '||NVL(GE.SNOMBRE||' ','')||GE.PAPELLIDO||' '||NVL(GE.SAPELLIDO,''),
                       HE.NOMBRE) AS EMPLEADO,
                   HE.CARGO,
                   NVL(VC.CODNIVELCARGO,'NC001') AS CODNIVELCARGO,
                   NVL(VC.IDDEPENDENCIA, 0) AS IDDEPENDENCIA,
                   HE.PROCESO AS DEPENDENCIA
            FROM VAADINWEB.HUMEMPLEADOEVAL HE
            LEFT JOIN ZAYMAWEB.GHEMPEMPLEADOS GE ON HE.IDEMPLEADO = GE.IDEMPLEADO
            LEFT JOIN ZAYMAWEB.VST_GHEMPEMPLEADOSCARGOS VC
                ON HE.IDEMPLEADO = VC.IDEMPLEADO AND VC.ESPRINCIPAL = 1
            WHERE HE.IDEMPLEADO_EVAL = $idempleado
              AND HE.ACTIVO = 1
              AND HE.IDROL IN (1, 2, 3)";

        $sql = "
            SELECT
                E.IDEMPLEADO, E.EMPLEADO, E.CARGO, E.CODNIVELCARGO, E.DEPENDENCIA,
                CASE WHEN A.IDEMPLEADO IS NOT NULL THEN 1 ELSE 0 END AS TIENE_AUTOEVAL,
                CASE WHEN C.IDEMPLEADO IS NOT NULL THEN 1 ELSE 0 END AS TIENE_EVAL_COLAB,
                CASE
                    WHEN A.IDEMPLEADO IS NOT NULL AND C.IDEMPLEADO IS NOT NULL THEN 'completo'
                    WHEN A.IDEMPLEADO IS NOT NULL THEN 'en_progreso'
                    ELSE 'sin_iniciar'
                END AS ESTADO
            FROM ($subQueryPersonas) E
            LEFT JOIN (
                SELECT DISTINCT IDEMPLEADO FROM VAADINWEB.HUMRESPUESTA R_A
                WHERE R_A.TIPO_EVAL = 'AUTO' AND $filtroAuto
            ) A ON E.IDEMPLEADO = A.IDEMPLEADO
            LEFT JOIN (
                SELECT DISTINCT IDEMPLEADO FROM VAADINWEB.HUMRESPUESTA R_C
                WHERE R_C.TIPO_EVAL = 'LIDER_A_COLAB'
                  AND R_C.IDEMPLEADO_EVAL = $idempleado AND $filtroColab
            ) C ON E.IDEMPLEADO = C.IDEMPLEADO
            ORDER BY
                CASE
                    WHEN A.IDEMPLEADO IS NULL THEN 1
                    WHEN A.IDEMPLEADO IS NOT NULL AND C.IDEMPLEADO IS NULL THEN 2
                    ELSE 3
                END, E.EMPLEADO ASC";

        $res  = $this->ejecutarConsulta($sql);
        $rows = [];
        if ($res) {
            while ($r = oci_fetch_assoc($res)) {
                $rows[] = $this->utf8Row($r);
            }
            oci_free_statement($res);
        }
        return $rows;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // MÉTODOS SIN CAMBIO — no usan tablas V1
    // ══════════════════════════════════════════════════════════════════════════

    public function getCargoId(int $idempleado): ?int {
        $sql = "SELECT IDEMPCARGO FROM ZAYMAWEB.VST_GHEMPEMPLEADOSCARGOS
                WHERE IDEMPLEADO = $idempleado AND ESPRINCIPAL = 1 AND ROWNUM = 1";
        $res = $this->ejecutarConsulta($sql);
        $row = $res ? oci_fetch_assoc($res) : null;
        if ($res) oci_free_statement($res);
        return $row ? (int)$row['IDEMPCARGO'] : null;
    }

    public function getLiderDeColaborador(int $idempleado): ?array {
        // Usar HUMEMPLEADOEVAL — IDEMPLEADO_EVAL es el jefe directo
        $sql = "SELECT HE_L.IDEMPLEADO, HE_L.NOMBRE AS EMPLEADO, HE_L.CARGO,
                       GE.EMAIL
                FROM VAADINWEB.HUMEMPLEADOEVAL HE_C
                JOIN VAADINWEB.HUMEMPLEADOEVAL HE_L
                    ON HE_C.IDEMPLEADO_EVAL = HE_L.IDEMPLEADO
                LEFT JOIN ZAYMAWEB.GHEMPEMPLEADOS GE ON HE_L.IDEMPLEADO = GE.IDEMPLEADO
                WHERE HE_C.IDEMPLEADO = $idempleado
                  AND HE_C.ACTIVO = 1 AND HE_L.ACTIVO = 1
                  AND ROWNUM = 1";
        $res = $this->ejecutarConsulta($sql);
        $row = $res ? oci_fetch_assoc($res) : null;
        if ($res) oci_free_statement($res);
        if ($row) {
            $row['EMPLEADO'] = fromOracleEncoding($row['EMPLEADO'] ?? '');
            $row['CARGO']    = fromOracleEncoding($row['CARGO']    ?? '');
        }
        return $row;
    }

    public function searchEmployees(string $term, int $limit = 10): array {
        $term = strtoupper($term);
        $sql  = "SELECT * FROM (
                    SELECT e.IDEMPLEADO, e.EMPLEADO, e.IDENTIFICACION, e.CARGO
                    FROM ZAYMAWEB.VST_GHEMPEMPLEADOSCARGOS e
                    WHERE UPPER(e.EMPLEADO) LIKE '%$term%' OR e.IDENTIFICACION LIKE '%$term%'
                    ORDER BY e.EMPLEADO ASC
                 ) WHERE ROWNUM <= $limit";
        $res  = $this->ejecutarConsulta($sql);
        $rows = [];
        if ($res) {
            while ($r = oci_fetch_assoc($res)) $rows[] = $this->utf8Row($r);
            oci_free_statement($res);
        }
        return $rows;
    }

    public function getEmployeeById(int $id): ?array {
        $sql = "SELECT EMPLEADO, CARGO, IDENTIFICACION
                FROM ZAYMAWEB.VST_GHEMPEMPLEADOSCARGOS
                WHERE IDEMPLEADO = $id AND ESPRINCIPAL = 1 AND ROWNUM = 1";
        $res = $this->ejecutarConsulta($sql);
        $row = $res ? oci_fetch_assoc($res) : null;
        if ($res) oci_free_statement($res);
        return $row ? $this->utf8Row($row) : null;
    }

    /**
     * Promedios de autoevaluación por empleado — para columna admin en Mi Equipo
     * Solo visible para usuarios con puedeVerDetalle = 1
     * [IDEMPLEADO => promedio_auto]
     */
    public function getPromediosAutoEquipo(array $idsEmpleados, ?array $periodo = null): array {
        if (empty($idsEmpleados)) return [];

        $ids       = implode(',', array_map('intval', $idsEmpleados));
        $idPeriodo = $periodo ? (int)$periodo['IDPERIODO'] : 0;
        $filtroPer = $idPeriodo ? "AND R.IDPERIODO = $idPeriodo" : '';

        $sql = "SELECT R.IDEMPLEADO,
                       ROUND(AVG(R.VALOR * 1.0), 2) AS PROMEDIO
                FROM VAADINWEB.HUMRESPUESTA R
                INNER JOIN VAADINWEB.HUMCOMPETENCIA C ON R.IDCOMPETENCIA = C.IDCOMPETENCIA
                WHERE R.IDEMPLEADO IN ($ids)
                  AND R.IDEMPLEADO_EVAL = R.IDEMPLEADO
                  AND R.TIPO_EVAL       = 'AUTO'
                  AND R.CONFIRMADO      = 1
                  AND C.NUM_PREGUNTA   <= 11
                  $filtroPer
                GROUP BY R.IDEMPLEADO";

        $res  = $this->ejecutarConsulta($sql);
        $rows = [];
        if ($res) {
            while ($r = oci_fetch_assoc($res)) {
                $rows[(int)$r['IDEMPLEADO']] = (float)str_replace(',', '.', $r['PROMEDIO']);
            }
            oci_free_statement($res);
        }
        return $rows;
    }

    public function buscarPersonalEquipo(string $term): array {
        $term = strtoupper(trim($term));
        $sql  = "SELECT * FROM (
                    SELECT IDEMPLEADO, EMPLEADO, IDENTIFICACION, CARGO
                    FROM ZAYMAWEB.VST_GHEMPEMPLEADOSCARGOS
                    WHERE (UPPER(EMPLEADO) LIKE '%$term%' OR IDENTIFICACION LIKE '%$term%')
                      AND ESTADOEMPLEADO = 1 AND CARGO IS NOT NULL
                    ORDER BY EMPLEADO ASC
                 ) WHERE ROWNUM <= 10";
        $res  = $this->ejecutarConsulta($sql);
        $rows = [];
        if ($res) {
            while ($r = oci_fetch_assoc($res)) $rows[] = $this->utf8Row($r);
            oci_free_statement($res);
        }
        return $rows;
    }
}
