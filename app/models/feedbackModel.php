<?php
namespace app\models;

class feedbackModel extends mainModel {

    // ── Obtener período activo ────────────────────────────────────────────────
    public function getPeriodoActivo() {
        $sql = "SELECT IDPERIODO,
                       TO_CHAR(FECHAAPERTURA,'DD/MM/YYYY') AS FECHAAPERTURA,
                       TO_CHAR(FECHACIERRE,'DD/MM/YYYY')   AS FECHACIERRE,
                       NOMBRE
                FROM VAADINWEB.HUMPERIODOEVALUACION
                WHERE ESTADO = 1 AND ROWNUM = 1";
        $res = $this->ejecutarConsulta($sql);
        $row = $res ? oci_fetch_assoc($res) : null;
        if (is_array($row)) $row['NOMBRE'] = fromOracleEncoding($row['NOMBRE'] ?? '');
        return $row;
    }

    // ── Obtener equipo del líder con estado evaluaciones ─────────────────────
    public function getEquipoConEstado($idLider, $nivelCargo = '', $periodo = []) {
        $idLider = (int)$idLider;
        // Usar HUMEMPLEADOEVAL — fuente oficial desde el Excel del director
        // IDEMPLEADO_EVAL = jefe directo del colaborador
        $subQuery = "
            SELECT HE.IDEMPLEADO,
                   NVL(GE.PNOMBRE || ' ' || GE.SNOMBRE || ' ' || GE.PAPELLIDO || ' ' || GE.SAPELLIDO,
                       HE.NOMBRE) AS EMPLEADO,
                   HE.CARGO,
                   VC.CODNIVELCARGO
            FROM VAADINWEB.HUMEMPLEADOEVAL HE
            LEFT JOIN ZAYMAWEB.GHEMPEMPLEADOS GE ON HE.IDEMPLEADO = GE.IDEMPLEADO
            LEFT JOIN ZAYMAWEB.VST_GHEMPEMPLEADOSCARGOS VC
                ON HE.IDEMPLEADO = VC.IDEMPLEADO AND VC.ESPRINCIPAL = 1
            WHERE HE.IDEMPLEADO_EVAL = $idLider
              AND HE.ACTIVO = 1
              AND HE.IDROL IN (1, 2, 3)"; // Solo colaboradores (asistencial/administrativo)

        $sql = "
            SELECT
                E.IDEMPLEADO, E.EMPLEADO, E.CARGO, E.CODNIVELCARGO,
                -- Autoevaluación
                CASE WHEN A.IDEMPLEADO IS NOT NULL THEN 1 ELSE 0 END AS TIENE_AUTO,
                -- Fue evaluado por el líder
                CASE WHEN C.IDEMPLEADO IS NOT NULL THEN 1 ELSE 0 END AS TIENE_EVAL_COLAB,
                -- Feedback registrado
                CASE WHEN F.IDFEEDBACK IS NOT NULL THEN 1 ELSE 0 END AS TIENE_FEEDBACK,
                F.IDFEEDBACK,
                TO_CHAR(F.FECHA_FEEDBACK,'DD/MM/YYYY HH24:MI') AS FECHA_FEEDBACK,
                F.OBSERVACION,
                -- Acuerdos asignados
                NVL(AC.TOTAL_ACUERDOS, 0) AS TOTAL_ACUERDOS,
                NVL(AC.APROBADOS, 0) AS ACUERDOS_APROBADOS,
                -- Firma del colaborador
                NVL(F.FIRMADO_COLAB, 0) AS FIRMADO_COLAB,
                TO_CHAR(F.FECHA_FIRMA,'DD/MM/YYYY') AS FECHA_FIRMA
            FROM ($subQuery) E
            LEFT JOIN (
                SELECT DISTINCT IDEMPLEADO FROM VAADINWEB.HUMRESPUESTA
                WHERE TIPO_EVAL   = 'AUTO'
                  AND CONFIRMADO  = 1
                  AND IDPERIODO   = " . (int)$periodo['IDPERIODO'] . "
            ) A ON E.IDEMPLEADO = A.IDEMPLEADO
            LEFT JOIN (
                SELECT DISTINCT IDEMPLEADO FROM VAADINWEB.HUMRESPUESTA
                WHERE TIPO_EVAL       = 'LIDER_A_COLAB'
                  AND IDEMPLEADO_EVAL = $idLider
                  AND CONFIRMADO      = 1
                  AND IDPERIODO       = " . (int)$periodo['IDPERIODO'] . "
            ) C ON E.IDEMPLEADO = C.IDEMPLEADO
            LEFT JOIN VAADINWEB.HUMFEEDBACK F
                ON E.IDEMPLEADO = F.IDEMPLEADO
                AND F.IDEMPLEADO_LIDER = $idLider
                AND F.IDPERIODO = " . (int)$periodo['IDPERIODO'] . "
                AND F.TIPO_FEEDBACK = 1
            LEFT JOIN (
                SELECT IDEMPLEADO,
                       COUNT(*) AS TOTAL_ACUERDOS,
                       SUM(CASE WHEN ESTADO = 'APROBADO' THEN 1 ELSE 0 END) AS APROBADOS
                FROM VAADINWEB.HUMACUERDOMEJORA
                WHERE IDPERIODO = " . (int)$periodo['IDPERIODO'] . "
                  AND IDEMPLEADO_LIDER = $idLider
                  AND NUM_COMPETENCIA BETWEEN 1 AND 11
                GROUP BY IDEMPLEADO
            ) AC ON E.IDEMPLEADO = AC.IDEMPLEADO
            ORDER BY
                CASE WHEN F.IDFEEDBACK IS NOT NULL THEN 2 ELSE 1 END,
                E.EMPLEADO ASC";

        $res  = $this->ejecutarConsulta($sql);
        $rows = [];
        if ($res) {
            while ($r = oci_fetch_assoc($res)) {
                $r['EMPLEADO']    = fromOracleEncoding($r['EMPLEADO'] ?? '');
                $r['CARGO']       = fromOracleEncoding($r['CARGO'] ?? '');
                $r['OBSERVACION'] = fromOracleEncoding($r['OBSERVACION'] ?? '');
                $rows[] = $r;
            }
            oci_free_statement($res);
        }
        return $rows;
    }

    // ── Obtener calificaciones del colaborador en el período ─────────────────
    // Migrado a HUMRESPUESTA — devuelve [IDCOMPETENCIA => ['VALOR'=>N, 'ETIQUETA'=>'...']]
    // ── Registrar feedback Flujo 2 (director → líder) ──────────────────────
    public function registrarFeedbackLider($idLider, $idDirector, $idPeriodo, $fechaFeedback, $observacion) {
        $idLider    = (int)$idLider;
        $idDirector = (int)$idDirector;
        $idPeriodo  = (int)$idPeriodo;
        $observacion = $this->toOracle(trim($observacion));
        $conn = $this->conectar();

        // Verificar si ya existe registro Flujo 2
        $sqlCheck = "SELECT IDFEEDBACK,
                            TO_CHAR(FECHA_FEEDBACK,'DD/MM/YYYY HH24:MI') AS FECHA_FEEDBACK
                     FROM VAADINWEB.HUMFEEDBACK
                     WHERE IDEMPLEADO = $idLider
                       AND IDEMPLEADO_LIDER = $idDirector
                       AND IDPERIODO = $idPeriodo
                       AND TIPO_FEEDBACK = 2
                     AND ROWNUM = 1";
        $resCheck = oci_parse($conn, $sqlCheck);
        oci_execute($resCheck);
        $existing = oci_fetch_assoc($resCheck);
        oci_free_statement($resCheck);

        $fechaCambio = $existing
            && trim($existing['FECHA_FEEDBACK'] ?? '') !== trim($fechaFeedback);

        if ($existing) {
            $sql = "UPDATE VAADINWEB.HUMFEEDBACK
                    SET FECHA_FEEDBACK = TO_DATE(:fecha,'DD/MM/YYYY HH24:MI'),
                        OBSERVACION    = :obs
                    WHERE IDFEEDBACK   = :id";
            $q = oci_parse($conn, $sql);
            oci_bind_by_name($q, ':fecha', $fechaFeedback);
            oci_bind_by_name($q, ':obs',   $observacion);
            oci_bind_by_name($q, ':id',    $existing['IDFEEDBACK']);
            $ok = oci_execute($q, OCI_COMMIT_ON_SUCCESS);
            oci_free_statement($q);
            return ['ok' => (bool)$ok, 'esNuevo' => false, 'fechaCambio' => $fechaCambio, 'idFeedback' => (int)$existing['IDFEEDBACK']];
        } else {
            $sql = "INSERT INTO VAADINWEB.HUMFEEDBACK
                        (IDFEEDBACK, IDEMPLEADO, IDEMPLEADO_LIDER, IDPERIODO,
                         FECHA_FEEDBACK, OBSERVACION, IDUSUARIO_CREA, TIPO_FEEDBACK)
                    VALUES (VAADINWEB.SEQ_HUMFEEDBACK.NEXTVAL, :emp, :dir, :per,
                            TO_DATE(:fecha,'DD/MM/YYYY HH24:MI'), :obs, :usr, 2)";
            $q   = oci_parse($conn, $sql);
            $usr = (int)($_SESSION['id'] ?? 0);
            oci_bind_by_name($q, ':emp',   $idLider);
            oci_bind_by_name($q, ':dir',   $idDirector);
            oci_bind_by_name($q, ':per',   $idPeriodo);
            oci_bind_by_name($q, ':fecha', $fechaFeedback);
            oci_bind_by_name($q, ':obs',   $observacion);
            oci_bind_by_name($q, ':usr',   $usr);
            $ok = oci_execute($q, OCI_COMMIT_ON_SUCCESS);
            oci_free_statement($q);
            // Obtener el ID recién creado
            if ($ok) {
                $sqlId = "SELECT MAX(IDFEEDBACK) AS ID FROM VAADINWEB.HUMFEEDBACK
                          WHERE IDEMPLEADO = $idLider AND IDEMPLEADO_LIDER = $idDirector
                          AND TIPO_FEEDBACK = 2 AND IDPERIODO = $idPeriodo";
                $resId = $this->ejecutarConsulta($sqlId);
                $rowId = $resId ? oci_fetch_assoc($resId) : null;
                return ['ok' => true, 'esNuevo' => true, 'fechaCambio' => false, 'idFeedback' => (int)($rowId['ID'] ?? 0)];
            }
            return ['ok' => false, 'esNuevo' => true, 'fechaCambio' => false, 'idFeedback' => 0];
        }
    }

    public function getCalificacionesColaborador($idEmpleado, $idLider, $periodo, $nivelLider = 'NC002') {
        $idEmpleado = (int)$idEmpleado;
        $idLider    = (int)$idLider;
        $idPeriodo  = (int)$periodo['IDPERIODO'];

        // Autoevaluación del colaborador
        $sqlAuto = "SELECT R.IDCOMPETENCIA, R.VALOR, O.ETIQUETA, C.NUM_PREGUNTA, C.NOMBRE AS NOMBRE_COMP
                    FROM VAADINWEB.HUMRESPUESTA R
                    INNER JOIN VAADINWEB.HUMOPCIONESCALA O ON R.IDOPCION = O.IDOPCION
                    INNER JOIN VAADINWEB.HUMCOMPETENCIA  C ON R.IDCOMPETENCIA = C.IDCOMPETENCIA
                    WHERE R.IDEMPLEADO      = $idEmpleado
                      AND R.IDEMPLEADO_EVAL = $idEmpleado
                      AND R.TIPO_EVAL       = 'AUTO'
                      AND R.CONFIRMADO      = 1
                      AND R.IDPERIODO       = $idPeriodo
                      AND C.NUM_PREGUNTA   <= 11
                    ORDER BY C.NUM_PREGUNTA";
        $resAuto = $this->ejecutarConsulta($sqlAuto);
        $auto = [];
        if ($resAuto) {
            while ($r = oci_fetch_assoc($resAuto)) {
                $auto[(int)$r['IDCOMPETENCIA']] = [
                    'VALOR'        => (int)$r['VALOR'],
                    'ETIQUETA'     => fromOracleEncoding($r['ETIQUETA'] ?? ''),
                    'NOMBRE_COMP'  => fromOracleEncoding($r['NOMBRE_COMP'] ?? ''),
                    'NUM_PREGUNTA' => (int)$r['NUM_PREGUNTA'],
                ];
            }
            oci_free_statement($resAuto);
        }

        // Evaluación del líder al colaborador
        // Flujo 1: siempre evaluación directa del líder al colaborador
        $esDirector = false; // El promedio va en Flujo 2 (feedback director → líderes)
        $sqlLider = "SELECT R.IDCOMPETENCIA, R.VALOR,
                            O.ETIQUETA, C.NUM_PREGUNTA, C.NOMBRE AS NOMBRE_COMP
                     FROM VAADINWEB.HUMRESPUESTA R
                     INNER JOIN VAADINWEB.HUMOPCIONESCALA O ON R.IDOPCION = O.IDOPCION
                     INNER JOIN VAADINWEB.HUMCOMPETENCIA  C ON R.IDCOMPETENCIA = C.IDCOMPETENCIA
                     WHERE R.IDEMPLEADO      = $idEmpleado
                       AND R.IDEMPLEADO_EVAL = $idLider
                       AND R.TIPO_EVAL       = 'LIDER_A_COLAB'
                       AND R.CONFIRMADO      = 1
                       AND R.IDPERIODO       = $idPeriodo
                     ORDER BY C.NUM_PREGUNTA";

        $resLider = $this->ejecutarConsulta($sqlLider);
        $lider = [];
        if ($resLider) {
            while ($r = oci_fetch_assoc($resLider)) {
                $valor = $esDirector
                    ? (int)round((float)str_replace(',', '.', $r['VALOR_PROM'] ?? 0))
                    : (int)$r['VALOR'];
                $lider[(int)$r['IDCOMPETENCIA']] = [
                    'VALOR'        => $valor,
                    'ETIQUETA'     => fromOracleEncoding($r['ETIQUETA'] ?? ''),
                    'NOMBRE_COMP'  => fromOracleEncoding($r['NOMBRE_COMP'] ?? ''),
                    'NUM_PREGUNTA' => (int)$r['NUM_PREGUNTA'],
                ];
            }
            oci_free_statement($resLider);
        }

        return ['auto' => $auto, 'lider' => $lider];
    }

    // ── Consolidado para vista previa antes de feedback (P1-P11) ─────────────
    public function getConsolidadoParaFeedback(int $idEmpleado, int $idLider, int $idPeriodo): array {
        $cals = $this->getCalificacionesColaborador($idEmpleado, $idLider, ['IDPERIODO' => $idPeriodo]);

        // Justificaciones del líder
        $sqlJL = "SELECT C.NUM_PREGUNTA, J.JUSTIFICACION
                  FROM VAADINWEB.HUMRESPUESTA R
                  INNER JOIN VAADINWEB.HUMCOMPETENCIA      C ON R.IDCOMPETENCIA = C.IDCOMPETENCIA
                  INNER JOIN VAADINWEB.HUMJUSTIFICACION_V2 J ON R.IDRESPUESTA   = J.IDRESPUESTA
                  WHERE R.IDEMPLEADO      = $idEmpleado
                    AND R.IDEMPLEADO_EVAL = $idLider
                    AND R.TIPO_EVAL       = 'LIDER_A_COLAB'
                    AND R.CONFIRMADO      = 1
                    AND R.IDPERIODO       = $idPeriodo
                    AND C.NUM_PREGUNTA   <= 11
                  ORDER BY C.NUM_PREGUNTA";
        $justLider = [];
        $res = $this->ejecutarConsulta($sqlJL);
        if ($res) {
            while ($r = oci_fetch_assoc($res))
                $justLider[(int)$r['NUM_PREGUNTA']] = fromOracleEncoding($r['JUSTIFICACION'] ?? '');
            oci_free_statement($res);
        }

        // Justificaciones del colaborador (autoevaluación)
        $sqlJA = "SELECT C.NUM_PREGUNTA, J.JUSTIFICACION
                  FROM VAADINWEB.HUMRESPUESTA R
                  INNER JOIN VAADINWEB.HUMCOMPETENCIA      C ON R.IDCOMPETENCIA = C.IDCOMPETENCIA
                  INNER JOIN VAADINWEB.HUMJUSTIFICACION_V2 J ON R.IDRESPUESTA   = J.IDRESPUESTA
                  WHERE R.IDEMPLEADO      = $idEmpleado
                    AND R.IDEMPLEADO_EVAL = $idEmpleado
                    AND R.TIPO_EVAL       = 'AUTO'
                    AND R.CONFIRMADO      = 1
                    AND R.IDPERIODO       = $idPeriodo
                    AND C.NUM_PREGUNTA   <= 11
                  ORDER BY C.NUM_PREGUNTA";
        $justAuto = [];
        $res = $this->ejecutarConsulta($sqlJA);
        if ($res) {
            while ($r = oci_fetch_assoc($res))
                $justAuto[(int)$r['NUM_PREGUNTA']] = fromOracleEncoding($r['JUSTIFICACION'] ?? '');
            oci_free_statement($res);
        }

        // Indexar por NUM_PREGUNTA
        $liderByNum = [];
        foreach ($cals['lider'] as $d) $liderByNum[(int)$d['NUM_PREGUNTA']] = $d;
        $autoByNum  = [];
        foreach ($cals['auto']  as $d) $autoByNum[(int)$d['NUM_PREGUNTA']]  = $d;

        $competencias = [];
        for ($p = 1; $p <= 11; $p++) {
            if (!isset($liderByNum[$p]) && !isset($autoByNum[$p])) continue;
            $competencias[] = [
                'num'       => $p,
                'nombre'    => $liderByNum[$p]['NOMBRE_COMP'] ?? $autoByNum[$p]['NOMBRE_COMP'] ?? 'P'.$p,
                'lider'     => isset($liderByNum[$p])
                    ? ['valor' => $liderByNum[$p]['VALOR'], 'etiqueta' => $liderByNum[$p]['ETIQUETA']]
                    : null,
                'auto'      => isset($autoByNum[$p])
                    ? ['valor' => $autoByNum[$p]['VALOR'],  'etiqueta' => $autoByNum[$p]['ETIQUETA']]
                    : null,
                'justLider' => $justLider[$p] ?? '',
                'justAuto'  => $justAuto[$p]  ?? '',
            ];
        }

        return [
            'competencias' => $competencias,
            'tieneAuto'    => !empty($cals['auto']),
            'tieneLider'   => !empty($cals['lider']),
        ];
    }

    // ── Consolidado Flujo 2: promedio P12-P16 + justificaciones (COLAB_A_LIDER) ─
    public function getConsolidadoLider(int $idLider, int $idPeriodo): array {
        // Join via IDOPCION (no por VALOR) para garantizar la etiqueta correcta
        $sqlV = "SELECT C.NUM_PREGUNTA, C.NOMBRE AS NOMBRE_COMP, R.VALOR, O.ETIQUETA, R.IDEMPLEADO_EVAL
                 FROM VAADINWEB.HUMRESPUESTA R
                 INNER JOIN VAADINWEB.HUMOPCIONESCALA O ON R.IDOPCION      = O.IDOPCION
                 INNER JOIN VAADINWEB.HUMCOMPETENCIA  C ON R.IDCOMPETENCIA = C.IDCOMPETENCIA
                 WHERE R.IDEMPLEADO   = $idLider
                   AND R.TIPO_EVAL    = 'COLAB_A_LIDER'
                   AND R.CONFIRMADO   = 1
                   AND R.IDPERIODO    = $idPeriodo
                   AND C.NUM_PREGUNTA BETWEEN 12 AND 16
                 ORDER BY R.IDEMPLEADO_EVAL, C.NUM_PREGUNTA";
        // byPreg[num] = ['nombre'=>'', 'valores'=>[], 'etiquetas'=>{val=>label}]
        // byEval[evalId][num] = valor  (para líneas individuales en la gráfica)
        $byPreg = [];
        $byEval = [];
        $res = $this->ejecutarConsulta($sqlV);
        if ($res) {
            while ($r = oci_fetch_assoc($res)) {
                $p    = (int)$r['NUM_PREGUNTA'];
                $v    = (int)$r['VALOR'];
                $eval = (int)$r['IDEMPLEADO_EVAL'];
                if (!isset($byPreg[$p])) {
                    $byPreg[$p] = [
                        'nombre'    => fromOracleEncoding($r['NOMBRE_COMP'] ?? ''),
                        'valores'   => [],
                        'etiquetas' => [],
                    ];
                }
                $byPreg[$p]['valores'][]     = $v;
                $byPreg[$p]['etiquetas'][$v] = fromOracleEncoding($r['ETIQUETA'] ?? '');
                $byEval[$eval][$p]           = $v;
            }
            oci_free_statement($res);
        }

        $sqlJ = "SELECT C.NUM_PREGUNTA, J.JUSTIFICACION
                 FROM VAADINWEB.HUMRESPUESTA R
                 INNER JOIN VAADINWEB.HUMCOMPETENCIA      C ON R.IDCOMPETENCIA = C.IDCOMPETENCIA
                 INNER JOIN VAADINWEB.HUMJUSTIFICACION_V2 J ON R.IDRESPUESTA   = J.IDRESPUESTA
                 WHERE R.IDEMPLEADO   = $idLider
                   AND R.TIPO_EVAL    = 'COLAB_A_LIDER'
                   AND R.CONFIRMADO   = 1
                   AND R.IDPERIODO    = $idPeriodo
                   AND C.NUM_PREGUNTA BETWEEN 12 AND 16
                 ORDER BY C.NUM_PREGUNTA";
        $justMap = [];
        $res = $this->ejecutarConsulta($sqlJ);
        if ($res) {
            while ($r = oci_fetch_assoc($res)) {
                $p = (int)$r['NUM_PREGUNTA'];
                $t = fromOracleEncoding($r['JUSTIFICACION'] ?? '');
                if ($t !== '') $justMap[$p][] = $t;
            }
            oci_free_statement($res);
        }

        $competencias = [];
        ksort($byPreg);
        foreach ($byPreg as $num => $data) {
            $vals    = $data['valores'];
            $avgVal  = !empty($vals) ? (int)round(array_sum($vals) / count($vals)) : 0;
            // Busca etiqueta exacta del promedio; si no existe, usa la del valor más cercano
            static $escalaEstandar = [
                1 => 'Insuficiente', 2 => 'Requiere mejora',
                3 => 'Aceptable',    4 => 'Acorde', 5 => 'Sobresaliente',
            ];
            $etq = $data['etiquetas'][$avgVal] ?? $escalaEstandar[$avgVal] ?? '';
            // Deduplicar justificaciones (HUMJUSTIFICACION_V2 puede tener filas duplicadas)
            $justs = array_values(array_unique($justMap[$num] ?? []));

            // Contexto: cuántos evaluadores dieron valores extremos y cuáles
            $cnt5   = count(array_filter($vals, fn($v) => $v === 5));
            $cnt1   = count(array_filter($vals, fn($v) => $v === 1));
            $cntExt = $cnt5 + $cnt1;
            $partes = [];
            if ($cnt5 > 0) $partes[] = $cnt5 . ' ' . ($data['etiquetas'][5] ?? 'Sobresaliente');
            if ($cnt1 > 0) $partes[] = $cnt1 . ' ' . ($data['etiquetas'][1] ?? 'Insuficiente');
            $justContexto = $cntExt > 0
                ? ($cntExt === 1 ? '1 evaluador' : "$cntExt evaluadores") . ' · ' . implode(', ', $partes)
                : '';

            $competencias[] = [
                'num'           => $num,
                'nombre'        => $data['nombre'],
                'valor'         => $avgVal,
                'etiqueta'      => $etq,
                'justificacion' => $justs,
                'justContexto'  => $justContexto,
            ];
        }

        // Construir líneas individuales anonimizadas para la gráfica
        $pregOrden  = array_keys($byPreg); // [12,13,14,15,16] ordenados
        $individuales = [];
        $idx = 1;
        foreach ($byEval as $evalId => $pregVals) {
            $linea = [];
            foreach ($pregOrden as $p) {
                $linea[] = $pregVals[$p] ?? null; // null = sin dato para esa competencia
            }
            $individuales[] = ['label' => 'Colaborador ' . $idx, 'valores' => $linea];
            $idx++;
        }

        return ['competencias' => $competencias, 'individuales' => $individuales];
    }

    // ── Consolidado Flujo 3 EA: calificaciones P17-P22 + justificaciones ──────
    public function getConsolidadoEA(int $idEmpleado, int $idLider, int $idPeriodo): array {
        $sqlV = "SELECT R.VALOR, O.ETIQUETA, C.NUM_PREGUNTA, C.NOMBRE AS NOMBRE_COMP
                 FROM VAADINWEB.HUMRESPUESTA R
                 INNER JOIN VAADINWEB.HUMOPCIONESCALA O ON R.IDOPCION      = O.IDOPCION
                 INNER JOIN VAADINWEB.HUMCOMPETENCIA  C ON R.IDCOMPETENCIA = C.IDCOMPETENCIA
                 WHERE R.IDEMPLEADO      = $idEmpleado
                   AND R.IDEMPLEADO_EVAL = $idLider
                   AND R.TIPO_EVAL       = 'EXPERIENCIA_COLAB'
                   AND R.CONFIRMADO      = 1
                   AND R.IDPERIODO       = $idPeriodo
                   AND C.NUM_PREGUNTA   BETWEEN 17 AND 22
                 ORDER BY C.NUM_PREGUNTA";
        $cals = [];
        $res = $this->ejecutarConsulta($sqlV);
        if ($res) {
            while ($r = oci_fetch_assoc($res)) {
                $cals[(int)$r['NUM_PREGUNTA']] = [
                    'VALOR'       => (int)$r['VALOR'],
                    'ETIQUETA'    => fromOracleEncoding($r['ETIQUETA']     ?? ''),
                    'NOMBRE_COMP' => fromOracleEncoding($r['NOMBRE_COMP']  ?? ''),
                ];
            }
            oci_free_statement($res);
        }

        $sqlJ = "SELECT C.NUM_PREGUNTA, J.JUSTIFICACION
                 FROM VAADINWEB.HUMRESPUESTA R
                 INNER JOIN VAADINWEB.HUMCOMPETENCIA      C ON R.IDCOMPETENCIA = C.IDCOMPETENCIA
                 INNER JOIN VAADINWEB.HUMJUSTIFICACION_V2 J ON R.IDRESPUESTA   = J.IDRESPUESTA
                 WHERE R.IDEMPLEADO      = $idEmpleado
                   AND R.IDEMPLEADO_EVAL = $idLider
                   AND R.TIPO_EVAL       = 'EXPERIENCIA_COLAB'
                   AND R.CONFIRMADO      = 1
                   AND R.IDPERIODO       = $idPeriodo
                   AND C.NUM_PREGUNTA   BETWEEN 17 AND 22
                 ORDER BY C.NUM_PREGUNTA";
        $justMap = [];
        $res = $this->ejecutarConsulta($sqlJ);
        if ($res) {
            while ($r = oci_fetch_assoc($res)) {
                $t = fromOracleEncoding($r['JUSTIFICACION'] ?? '');
                if ($t !== '') $justMap[(int)$r['NUM_PREGUNTA']][] = $t;
            }
            oci_free_statement($res);
        }

        $competencias = [];
        for ($p = 17; $p <= 22; $p++) {
            if (!isset($cals[$p])) continue;
            $c = $cals[$p];
            $competencias[] = [
                'num'           => $p,
                'nombre'        => $c['NOMBRE_COMP'],
                'valor'         => $c['VALOR'],
                'etiqueta'      => $c['ETIQUETA'],
                'justificacion' => array_values(array_unique($justMap[$p] ?? [])),
            ];
        }
        return ['competencias' => $competencias];
    }

    // ── Obtener objetivos SMART según calificaciones ─────────────────────────
    // ── Flujo 2: calificaciones promediadas P12-P16 que el líder recibió ────
    // ── Plan de mejora del líder (P12-P16 recibidos como evaluado) ────────
    public function getAcuerdosLider(int $idLider, int $idPeriodo): array {
        $sql = "SELECT A.IDACUERDO, A.NUM_COMPETENCIA, A.CALIFICACION,
                       A.ESTADO, A.MODELO, A.INDICADOR, A.META, A.PLAZO,
                       A.EVIDENCIA, A.SEGUIMIENTO, A.APOYO_LIDER,
                       A.COMPROMISO_AJUSTADO, A.PLAN_ACCION, A.COMENTARIO_LIDER,
                       O.OBJETIVO,
                       NVL(HE.NOMBRE, 'Director') AS NOMBRE_LIDER,
                       NVL(F.FIRMADO_LIDER, 0) AS FIRMADO_COLAB,
                       TO_CHAR(F.FECHA_FEEDBACK,'DD/MM/YYYY HH24:MI') AS FECHA_FEEDBACK
                FROM VAADINWEB.HUMACUERDOMEJORA A
                INNER JOIN VAADINWEB.HUMOBJETIVOMEJORA O ON A.IDOBJETIVO = O.IDOBJETIVO
                LEFT JOIN VAADINWEB.HUMEMPLEADOEVAL HE ON HE.IDEMPLEADO = A.IDEMPLEADO_LIDER AND HE.ACTIVO = 1
                LEFT JOIN VAADINWEB.HUMFEEDBACK F
                    ON F.IDEMPLEADO = A.IDEMPLEADO
                    AND F.IDEMPLEADO_LIDER = A.IDEMPLEADO_LIDER
                    AND F.IDPERIODO = A.IDPERIODO
                    AND F.TIPO_FEEDBACK = 2
                WHERE A.IDEMPLEADO = $idLider
                  AND A.IDPERIODO  = $idPeriodo
                  AND A.NUM_COMPETENCIA BETWEEN 12 AND 16
                ORDER BY A.NUM_COMPETENCIA ASC, A.IDACUERDO ASC";
        $campos = ['OBJETIVO','MODELO','INDICADOR','META','PLAZO','EVIDENCIA',
                   'SEGUIMIENTO','APOYO_LIDER','COMPROMISO_AJUSTADO','PLAN_ACCION',
                   'COMENTARIO_LIDER','NOMBRE_LIDER','FECHA_FEEDBACK'];
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

    public function getCalificacionesLider(int $idLider, int $idPeriodo): array {
        // Calcular promedio primero, luego obtener etiqueta de la escala
        $sql = "SELECT P.IDCOMPETENCIA, P.VALOR_PROM,
                       O.ETIQUETA, P.NUM_PREGUNTA, P.NOMBRE_COMP
                FROM (
                    SELECT R.IDCOMPETENCIA,
                           ROUND(AVG(R.VALOR * 1.0), 0) AS VALOR_PROM,
                           C.NUM_PREGUNTA, C.NOMBRE AS NOMBRE_COMP
                    FROM VAADINWEB.HUMRESPUESTA R
                    INNER JOIN VAADINWEB.HUMCOMPETENCIA C ON R.IDCOMPETENCIA = C.IDCOMPETENCIA
                    WHERE R.IDEMPLEADO    = $idLider
                      AND R.TIPO_EVAL     = 'COLAB_A_LIDER'
                      AND R.CONFIRMADO    = 1
                      AND R.IDPERIODO     = $idPeriodo
                      AND C.NUM_PREGUNTA  BETWEEN 12 AND 16
                    GROUP BY R.IDCOMPETENCIA, C.NUM_PREGUNTA, C.NOMBRE
                ) P
                INNER JOIN VAADINWEB.HUMOPCIONESCALA O ON O.VALOR = P.VALOR_PROM
                ORDER BY P.NUM_PREGUNTA";
        $res = $this->ejecutarConsulta($sql);
        $cals = [];
        if ($res) {
            while ($r = oci_fetch_assoc($res)) {
                $valor = (int)($r['VALOR_PROM'] ?? 0);
                $cals[(int)$r['NUM_PREGUNTA']] = [
                    'VALOR'       => $valor,
                    'ETIQUETA'    => fromOracleEncoding($r['ETIQUETA']    ?? ''),
                    'NOMBRE_COMP' => fromOracleEncoding($r['NOMBRE_COMP'] ?? ''),
                ];
            }
            oci_free_statement($res);
        }
        return $cals;
    }

    // ── Flujo 2: líderes bajo cargo del director ──────────────────────────
    public function getLideresACargo(int $idDirector, array $periodo): array {
        $idPeriodo = (int)($periodo['IDPERIODO'] ?? 0);
        $sql = "SELECT HE.IDEMPLEADO, HE.NOMBRE, HE.CARGO,
                       NVL((SELECT COUNT(DISTINCT R.IDEMPLEADO_EVAL) 
                            FROM VAADINWEB.HUMRESPUESTA R
                            WHERE R.IDEMPLEADO = HE.IDEMPLEADO
                              AND R.TIPO_EVAL = 'COLAB_A_LIDER'
                              AND R.CONFIRMADO = 1
                              AND R.IDPERIODO = $idPeriodo
                              AND R.IDEMPLEADO_EVAL != $idDirector), 0) AS TIENE_EVAL,
                       (SELECT COUNT(*) FROM VAADINWEB.HUMEMPLEADOEVAL SUB
                        WHERE SUB.IDEMPLEADO_EVAL = HE.IDEMPLEADO
                          AND SUB.ACTIVO = 1) AS TOTAL_COLAB,
                       NVL((SELECT IDFEEDBACK FROM VAADINWEB.HUMFEEDBACK F2
                            WHERE F2.IDEMPLEADO = HE.IDEMPLEADO
                              AND F2.IDEMPLEADO_LIDER = $idDirector
                              AND F2.IDPERIODO = $idPeriodo
                              AND F2.TIPO_FEEDBACK = 2
                              AND ROWNUM = 1), 0) AS IDFEEDBACK,
                       (SELECT TO_CHAR(F4.FECHA_FEEDBACK,'DD/MM/YYYY HH24:MI')
                        FROM VAADINWEB.HUMFEEDBACK F4
                        WHERE F4.IDEMPLEADO = HE.IDEMPLEADO
                          AND F4.IDEMPLEADO_LIDER = $idDirector
                          AND F4.IDPERIODO = $idPeriodo
                          AND F4.TIPO_FEEDBACK = 2
                          AND ROWNUM = 1) AS FECHA_FEEDBACK_L2,
                       (SELECT F5.OBSERVACION
                        FROM VAADINWEB.HUMFEEDBACK F5
                        WHERE F5.IDEMPLEADO = HE.IDEMPLEADO
                          AND F5.IDEMPLEADO_LIDER = $idDirector
                          AND F5.IDPERIODO = $idPeriodo
                          AND F5.TIPO_FEEDBACK = 2
                          AND ROWNUM = 1) AS OBSERVACION_L2,
                       NVL((SELECT FIRMADO_LIDER FROM VAADINWEB.HUMFEEDBACK F3
                            WHERE F3.IDEMPLEADO = HE.IDEMPLEADO
                              AND F3.IDEMPLEADO_LIDER = $idDirector
                              AND F3.IDPERIODO = $idPeriodo
                              AND F3.TIPO_FEEDBACK = 2
                              AND ROWNUM = 1), 0) AS FIRMADO_COLAB,
                       NVL((SELECT COUNT(*) FROM VAADINWEB.HUMACUERDOMEJORA AM
                            WHERE AM.IDEMPLEADO = HE.IDEMPLEADO
                              AND AM.IDEMPLEADO_LIDER = $idDirector
                              AND AM.IDPERIODO = $idPeriodo
                              AND AM.NUM_COMPETENCIA BETWEEN 12 AND 16), 0) AS TOTAL_OBJETIVOS_L2,
                       NVL((SELECT COUNT(CASE WHEN AM2.ESTADO='APROBADO' THEN 1 END)
                            FROM VAADINWEB.HUMACUERDOMEJORA AM2
                            WHERE AM2.IDEMPLEADO = HE.IDEMPLEADO
                              AND AM2.IDEMPLEADO_LIDER = $idDirector
                              AND AM2.IDPERIODO = $idPeriodo
                              AND AM2.NUM_COMPETENCIA BETWEEN 12 AND 16), 0) AS ACUERDOS_APROBADOS_L2
                FROM VAADINWEB.HUMEMPLEADOEVAL HE
                WHERE HE.IDEMPLEADO_EVAL = $idDirector
                  AND HE.ES_LIDER_FUNCIONAL = 1
                  AND HE.ACTIVO = 1
                ORDER BY HE.NOMBRE ASC";
        $res = $this->ejecutarConsulta($sql);
        $lideres = [];
        if ($res) {
            while ($r = oci_fetch_assoc($res)) {
                $lideres[] = [
                    'IDEMPLEADO'         => (int)$r['IDEMPLEADO'],
                    'NOMBRE'             => fromOracleEncoding($r['NOMBRE'] ?? ''),
                    'CARGO'              => fromOracleEncoding($r['CARGO']  ?? ''),
                    'TIENE_EVAL'         => (int)$r['TIENE_EVAL'],
                    'TOTAL_COLAB'        => (int)$r['TOTAL_COLAB'],
                    'IDFEEDBACK'         => (int)$r['IDFEEDBACK'],
                    'FIRMADO_COLAB'      => (int)$r['FIRMADO_COLAB'],
                    'TOTAL_OBJETIVOS_L2'     => (int)$r['TOTAL_OBJETIVOS_L2'],
                    'ACUERDOS_APROBADOS_L2' => (int)$r['ACUERDOS_APROBADOS_L2'],
                    'FECHA_FEEDBACK_L2'  => $r['FECHA_FEEDBACK_L2'] ?? '',
                    'OBSERVACION_L2'     => fromOracleEncoding($r['OBSERVACION_L2'] ?? ''),
                ];
            }
            oci_free_statement($res);
        }
        return $lideres;
    }

    public function getObjetivosSmart($numComp, $calificacion) {
        $numComp = (int)$numComp;

        // Mapeo unificado: un modelo exacto por nivel de calificación.
        // EA: Referente→Expansión, Consistente/Esperado→Consolidación, Inconsistente/Crítico→Cierre.
        // FL1/FL2: Sobresaliente→Expansión, Acorde→Consolidación, resto→Cierre de brecha.
        $modeloMap = [
            'Referente'        => "MODELO = 'Expansi' || CHR(243) || 'n / referente'",
            'Consistente'      => "MODELO = 'Consolidaci' || CHR(243) || 'n'",
            'Esperado'         => "MODELO = 'Consolidaci' || CHR(243) || 'n'",
            'Inconsistente'    => "MODELO = 'Cierre de brecha'",
            'Crítico'          => "MODELO = 'Cierre de brecha'",
            'Sobresaliente'    => "MODELO = 'Expansi' || CHR(243) || 'n / referente'",
            'Acorde'           => "MODELO = 'Consolidaci' || CHR(243) || 'n'",
            'Aceptable'        => "MODELO = 'Cierre de brecha'",
            'Requiere mejora'  => "MODELO = 'Cierre de brecha'",
            'Necesita Mejorar' => "MODELO = 'Cierre de brecha'",
            'Insuficiente'     => "MODELO = 'Cierre de brecha'",
            '5'                => "MODELO = 'Expansi' || CHR(243) || 'n / referente'",
            '4'                => "MODELO = 'Consolidaci' || CHR(243) || 'n'",
            '3'                => "MODELO = 'Cierre de brecha'",
            '2'                => "MODELO = 'Cierre de brecha'",
            '1'                => "MODELO = 'Cierre de brecha'",
        ];

        $modeloFiltro = $modeloMap[$calificacion] ?? "MODELO = 'Cierre de brecha'";

        $sql = "SELECT IDOBJETIVO, OBJETIVO, MODELO, INDICADOR, META, PLAZO, EVIDENCIA, SEGUIMIENTO, USO_RECOMENDADO
                FROM VAADINWEB.HUMOBJETIVOMEJORA
                WHERE NUM_COMPETENCIA = $numComp
                  AND $modeloFiltro
                  AND ACTIVO = 1
                ORDER BY IDOBJETIVO";

        $res    = $this->ejecutarConsulta($sql);
        $campos = ['OBJETIVO','MODELO','INDICADOR','META','PLAZO','EVIDENCIA','SEGUIMIENTO','USO_RECOMENDADO'];
        $rows   = [];
        if ($res) {
            while ($r = oci_fetch_assoc($res)) {
                foreach ($campos as $c) {
                    if (isset($r[$c])) {
                        $r[$c] = fromOracleEncoding($r[$c]);
                    }
                }
                $rows[] = $r;
            }
            oci_free_statement($res);
        }
        return $rows;
    }

    // ── Registrar sesión de feedback ─────────────────────────────────────────
    public function registrarFeedback($idEmpleado, $idLider, $idPeriodo, $fechaFeedback, $observacion, $tipoFeedback = 1) {
        $idEmpleado    = (int)$idEmpleado;
        $idLider       = (int)$idLider;
        $idPeriodo     = (int)$idPeriodo;
        $tipoFeedback  = (int)$tipoFeedback;
        $observacion   = $this->toOracle(trim($observacion));

        $conn = $this->conectar();

        $sqlCheck = "SELECT IDFEEDBACK,
                            TO_CHAR(FECHA_FEEDBACK,'DD/MM/YYYY HH24:MI') AS FECHA_FEEDBACK
                     FROM VAADINWEB.HUMFEEDBACK
                     WHERE IDEMPLEADO = $idEmpleado
                       AND IDEMPLEADO_LIDER = $idLider
                       AND IDPERIODO = $idPeriodo
                       AND TIPO_FEEDBACK = $tipoFeedback
                     AND ROWNUM = 1";
        $resCheck = oci_parse($conn, $sqlCheck);
        oci_execute($resCheck);
        $existing = oci_fetch_assoc($resCheck);
        oci_free_statement($resCheck);

        $fechaCambio = $existing
            && trim($existing['FECHA_FEEDBACK'] ?? '') !== trim($fechaFeedback);

        if ($existing) {
            // Actualizar
            $sql = "UPDATE VAADINWEB.HUMFEEDBACK
                    SET FECHA_FEEDBACK = TO_DATE(:fecha,'DD/MM/YYYY HH24:MI'),
                        OBSERVACION    = :obs
                    WHERE IDFEEDBACK   = :id";
            $q   = oci_parse($conn, $sql);
            oci_bind_by_name($q, ':fecha', $fechaFeedback);
            oci_bind_by_name($q, ':obs',   $observacion);
            oci_bind_by_name($q, ':id',    $existing['IDFEEDBACK']);
        } else {
            // Insertar
            $sql = "INSERT INTO VAADINWEB.HUMFEEDBACK
                        (IDFEEDBACK, IDEMPLEADO, IDEMPLEADO_LIDER, IDPERIODO, FECHA_FEEDBACK, OBSERVACION, IDUSUARIO_CREA, TIPO_FEEDBACK)
                    VALUES (VAADINWEB.SEQ_HUMFEEDBACK.NEXTVAL, :emp, :lider, :per, TO_DATE(:fecha,'DD/MM/YYYY HH24:MI'), :obs, :usr, :tipo)";
            $q   = oci_parse($conn, $sql);
            $usr = (int)($_SESSION['id'] ?? 0);
            oci_bind_by_name($q, ':emp',   $idEmpleado);
            oci_bind_by_name($q, ':lider', $idLider);
            oci_bind_by_name($q, ':per',   $idPeriodo);
            oci_bind_by_name($q, ':fecha', $fechaFeedback);
            oci_bind_by_name($q, ':obs',   $observacion);
            oci_bind_by_name($q, ':usr',   $usr);
            oci_bind_by_name($q, ':tipo',  $tipoFeedback);
        }

        $ok = oci_execute($q, OCI_COMMIT_ON_SUCCESS);
        oci_free_statement($q);
        return ['ok' => $ok, 'esNuevo' => !$existing, 'fechaCambio' => $fechaCambio];
    }

    // ── Asignar objetivos SMART después del feedback ─────────────────────────
    public function asignarObjetivoSmart($idEmpleado, $idObjetivo, $numComp, $calificacion,
                                          $idPeriodo, $idLider, $idFeedback,
                                          $apoyo, $indicador, $meta, $plazo,
                                          $evidencia = '', $compromiso = '') {
        $idEmpleado = (int)$idEmpleado;
        $idObjetivo = (int)$idObjetivo;
        $numComp    = (int)$numComp;
        $idPeriodo  = (int)$idPeriodo;
        $idLider    = (int)$idLider;
        $idFeedback = (int)$idFeedback;

        // Convertir calificación texto a número
        $calMap = [
            'Insuficiente'=>1,'Necesita Mejorar'=>2,'Requiere mejora'=>2,'Aceptable'=>3,'Acorde'=>4,'Sobresaliente'=>5,
            // EA (Flujo 3)
            'Crítico'=>1,'Inconsistente'=>2,'Esperado'=>3,'Consistente'=>4,'Referente'=>5,
        ];
        $calificacionNum = isset($calMap[$calificacion]) ? $calMap[$calificacion] : (int)$calificacion;

        $conn = $this->conectar();

        // Obtener datos del objetivo
        $sqlObj = "SELECT OBJETIVO,MODELO,INDICADOR,META,PLAZO,EVIDENCIA,SEGUIMIENTO
                   FROM VAADINWEB.HUMOBJETIVOMEJORA WHERE IDOBJETIVO = $idObjetivo";
        $resObj = oci_parse($conn, $sqlObj);
        oci_execute($resObj);
        $obj = oci_fetch_assoc($resObj);
        oci_free_statement($resObj);

        if (!$obj) return false;

        // Usar valores ajustados del líder si los hay, o los sugeridos del objetivo
        // Convertir a Oracle encoding antes de insertar
        $apoyo          = $this->toOracle(trim($apoyo));
        $indicadorFinal = $this->toOracle(!empty(trim($indicador)) ? trim($indicador) : ($obj['INDICADOR'] ?? ''));
        $metaFinal      = $this->toOracle(!empty(trim($meta))      ? trim($meta)      : ($obj['META']      ?? ''));
        $plazoFinal     = $this->toOracle(!empty(trim($plazo))     ? trim($plazo)     : ($obj['PLAZO']     ?? ''));
        $modelo         = fromOracleEncoding($obj['MODELO'] ?? '');
        $evidencia      = fromOracleEncoding($obj['EVIDENCIA'] ?? '');
        $seguimiento    = fromOracleEncoding($obj['SEGUIMIENTO'] ?? '');
        $objetivo       = fromOracleEncoding($obj['OBJETIVO'] ?? '');

        // Obtener próximo ID
        $sqlId = "SELECT NVL(MAX(IDACUERDO),0)+1 AS IDACUERDO FROM VAADINWEB.HUMACUERDOMEJORA";
        $resId = oci_parse($conn, $sqlId);
        oci_execute($resId);
        $rowId = oci_fetch_assoc($resId);
        $idAcuerdo = (int)$rowId['IDACUERDO'];
        oci_free_statement($resId);

        $objetivoTxt = $obj['OBJETIVO']    ?? '';
        $modelo      = $obj['MODELO']      ?? '';
        // Usar evidencia del formulario si se proporcionó, sino del objetivo base
        $evidenciaFinal = !empty(trim($evidencia)) ? $this->toOracle(trim($evidencia)) : ($obj['EVIDENCIA'] ?? '');
        $compromisoFinal = $this->toOracle(trim($compromiso));
        $seguimiento = $obj['SEGUIMIENTO'] ?? '';

        $sql = "INSERT INTO VAADINWEB.HUMACUERDOMEJORA
                    (IDACUERDO, IDEMPLEADO, IDOBJETIVO, NUM_COMPETENCIA, CALIFICACION,
                     IDPERIODO, IDEMPLEADO_LIDER, ESTADO, FECHA_ASIGNACION,
                     MODELO, INDICADOR, META, PLAZO, EVIDENCIA, SEGUIMIENTO,
                     APOYO_LIDER, IDFEEDBACK, COMPROMISO_AJUSTADO)
                VALUES (:id, :emp, :obj, :comp, :cal,
                        :per, :lider, 'PENDIENTE_FIRMA', SYSDATE,
                        :modelo, :ind, :meta, :plazo, :evidencia, :seg,
                        :apoyo, :feedback, :compromiso)";

        $q = oci_parse($conn, $sql);
        oci_bind_by_name($q, ':id',       $idAcuerdo);
        oci_bind_by_name($q, ':emp',      $idEmpleado);
        oci_bind_by_name($q, ':obj',      $idObjetivo);
        oci_bind_by_name($q, ':comp',     $numComp);
        oci_bind_by_name($q, ':cal',      $calificacionNum);
        oci_bind_by_name($q, ':per',      $idPeriodo);
        oci_bind_by_name($q, ':lider',    $idLider);
        oci_bind_by_name($q, ':modelo',   $modelo);
        oci_bind_by_name($q, ':ind',      $indicadorFinal);
        oci_bind_by_name($q, ':meta',     $metaFinal);
        oci_bind_by_name($q, ':plazo',    $plazoFinal);
        oci_bind_by_name($q, ':evidencia', $evidenciaFinal);
        oci_bind_by_name($q, ':seg',       $seguimiento);
        oci_bind_by_name($q, ':apoyo',     $apoyo);
        oci_bind_by_name($q, ':feedback',  $idFeedback);
        oci_bind_by_name($q, ':compromiso',$compromisoFinal);

        $ok = oci_execute($q, OCI_COMMIT_ON_SUCCESS);
        oci_free_statement($q);
        return $ok;
    }

    // ── Obtener acuerdos asignados en el feedback ────────────────────────────
    public function getAcuerdosFeedback($idEmpleado, $idPeriodo, $idLider, $flujo = 1) {
        // flujo=1: P1-P11, flujo=2: P12-P16, flujo=3: P17-P22 (EA Colaborador)
        $compFiltro = match((int)$flujo) {
            2       => "AND A.NUM_COMPETENCIA BETWEEN 12 AND 16",
            3       => "AND A.NUM_COMPETENCIA BETWEEN 17 AND 22",
            default => "AND A.NUM_COMPETENCIA BETWEEN 1 AND 11",
        };
        $sql = "SELECT A.IDACUERDO, A.NUM_COMPETENCIA, A.CALIFICACION,
                       A.ESTADO, A.MODELO, A.INDICADOR, A.META, A.PLAZO,
                       A.EVIDENCIA, A.SEGUIMIENTO, A.APOYO_LIDER,
                       A.COMPROMISO_AJUSTADO, A.PLAN_ACCION,
                       A.COMENTARIO_LIDER,
                       O.OBJETIVO
                FROM VAADINWEB.HUMACUERDOMEJORA A
                INNER JOIN VAADINWEB.HUMOBJETIVOMEJORA O ON A.IDOBJETIVO = O.IDOBJETIVO
                WHERE A.IDEMPLEADO = " . (int)$idEmpleado . "
                  AND A.IDPERIODO  = " . (int)$idPeriodo . "
                  AND A.IDEMPLEADO_LIDER = " . (int)$idLider . "
                  $compFiltro
                ORDER BY A.NUM_COMPETENCIA";
        $res  = $this->ejecutarConsulta($sql);
        $campos = ['OBJETIVO','MODELO','INDICADOR','META','PLAZO','EVIDENCIA',
                   'SEGUIMIENTO','APOYO_LIDER','COMPROMISO_AJUSTADO','PLAN_ACCION','COMENTARIO_LIDER'];
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

    // ── Actualizar seguimiento de un acuerdo ─────────────────────────────────
    public function actualizarSeguimiento($idAcuerdo, $idLider, $estado, $comentario) {
        $idAcuerdo  = (int)$idAcuerdo;
        $idLider    = (int)$idLider;
        $estado     = in_array($estado, ['PENDIENTE','RESPONDIDO','APROBADO']) ? $estado : 'PENDIENTE';
        $comentario = $this->toOracle(mb_substr(trim($comentario), 0, 500));

        $fechaCampo = $estado === 'APROBADO'  ? ", FECHA_APROBACION = SYSDATE"
                    : ($estado === 'RESPONDIDO' ? ", FECHA_RESPUESTA  = SYSDATE" : "");

        $sql = "UPDATE VAADINWEB.HUMACUERDOMEJORA
                SET ESTADO           = :estado,
                    COMENTARIO_LIDER = :comentario
                    $fechaCampo
                WHERE IDACUERDO      = :id
                  AND IDEMPLEADO_LIDER = :lider";
        $q = oci_parse($this->conectar(), $sql);
        oci_bind_by_name($q, ':estado',     $estado);
        oci_bind_by_name($q, ':comentario', $comentario);
        oci_bind_by_name($q, ':id',         $idAcuerdo);
        oci_bind_by_name($q, ':lider',      $idLider);
        $ok = oci_execute($q, OCI_COMMIT_ON_SUCCESS);
        oci_free_statement($q);
        return $ok;
    }

    // ── Configuración del módulo ─────────────────────────────────────────────
    public function getConfig() {
        $sql = "SELECT PARAMETRO, VALOR FROM VAADINWEB.HUMCONFIGMEJORA WHERE ACTIVO = 1";
        $res = $this->ejecutarConsulta($sql);
        $cfg = [];
        if ($res) {
            while ($r = oci_fetch_assoc($res)) $cfg[$r['PARAMETRO']] = $r['VALOR'];
            oci_free_statement($res);
        }
        return $cfg;
    }

    // ── Obtener cita asignada al colaborador (para el home) ──────────────────────────────
    public function getCitaColaborador($idEmpleado, $idPeriodo) {
        $idEmpleado = (int)$idEmpleado;
        $idPeriodo  = (int)$idPeriodo;
        $sql = "
            SELECT
                TO_CHAR(F.FECHA_FEEDBACK,'DD/MM/YYYY HH24:MI') AS FECHA_FEEDBACK,
                NVL(EL.PNOMBRE || ' ' || EL.PAPELLIDO, 'Tu líder') AS NOMBRE_LIDER
            FROM VAADINWEB.HUMFEEDBACK F
            LEFT JOIN ZAYMAWEB.GHEMPEMPLEADOS EL ON F.IDEMPLEADO_LIDER = EL.IDEMPLEADO
            WHERE F.IDEMPLEADO = $idEmpleado
              AND F.IDPERIODO  = $idPeriodo
              AND TRUNC(F.FECHA_FEEDBACK) >= TRUNC(SYSDATE)
            ORDER BY F.FECHA_FEEDBACK ASC
        ";
        $res = $this->ejecutarConsulta($sql);
        $row = $res ? oci_fetch_assoc($res) : null;
        if ($res) oci_free_statement($res);
        if ($row) {
            $row['NOMBRE_LIDER'] = fromOracleEncoding($row['NOMBRE_LIDER'] ?? '');
        }
        return $row;
    }

    // ── Estado del feedback Flujo 1 para el colaborador (home — 2 estados) ───
    public function getFeedbackEstado(int $idEmpleado, int $idPeriodo): ?array {
        $sql = "SELECT * FROM (
                    SELECT NVL(F.FIRMADO_LIDER, 0) AS FIRMADO_LIDER,
                           NVL(F.FIRMADO_COLAB, 0) AS FIRMADO_COLAB,
                           TO_CHAR(F.FECHA_FEEDBACK,'DD/MM/YYYY HH24:MI') AS FECHA_FEEDBACK,
                           TO_CHAR(F.FECHA_FIRMA,'DD/MM/YYYY')            AS FECHA_FIRMA,
                           NVL(GE.PNOMBRE || ' ' || GE.PAPELLIDO, 'Tu líder') AS NOMBRE_LIDER
                    FROM VAADINWEB.HUMFEEDBACK F
                    LEFT JOIN ZAYMAWEB.GHEMPEMPLEADOS GE ON F.IDEMPLEADO_LIDER = GE.IDEMPLEADO
                    WHERE F.IDEMPLEADO    = $idEmpleado
                      AND F.IDPERIODO     = $idPeriodo
                      AND F.TIPO_FEEDBACK = 1
                    ORDER BY F.IDFEEDBACK DESC
                ) WHERE ROWNUM = 1";
        $res = $this->ejecutarConsulta($sql);
        $row = $res ? oci_fetch_assoc($res) : null;
        if ($res) oci_free_statement($res);
        if (!is_array($row)) return null;
        $row['NOMBRE_LIDER'] = fromOracleEncoding($row['NOMBRE_LIDER'] ?? '');
        return $row;
    }

    // ── Flujo 3: estado del feedback EA para el colaborador (home) ──────────
    public function getFeedbackEAEstado(int $idEmpleado, int $idPeriodo): ?array {
        $sql = "SELECT * FROM (
                    SELECT TO_CHAR(F.FECHA_FEEDBACK,'DD/MM/YYYY HH24:MI') AS FECHA_FEEDBACK,
                           NVL(GE.PNOMBRE || ' ' || GE.PAPELLIDO, 'Tu líder') AS NOMBRE_LIDER
                    FROM VAADINWEB.HUMFEEDBACK F
                    LEFT JOIN ZAYMAWEB.GHEMPEMPLEADOS GE ON F.IDEMPLEADO_LIDER = GE.IDEMPLEADO
                    WHERE F.IDEMPLEADO    = $idEmpleado
                      AND F.IDPERIODO     = $idPeriodo
                      AND F.TIPO_FEEDBACK = 3
                      AND (F.FIRMADO_COLAB IS NULL OR F.FIRMADO_COLAB = 0)
                    ORDER BY F.IDFEEDBACK DESC
                ) WHERE ROWNUM = 1";
        $res = $this->ejecutarConsulta($sql);
        $row = $res ? oci_fetch_assoc($res) : null;
        if ($res) oci_free_statement($res);
        if (!is_array($row)) return null;
        $row['NOMBRE_LIDER'] = fromOracleEncoding($row['NOMBRE_LIDER'] ?? '');
        return $row;
    }

    // ── Flujo 2: firma del líder ─────────────────────────────────────────────
    public function firmarFeedbackLider($idLider, $idDirector, $idPeriodo, $cedula, $password) {
        $idLider    = (int)$idLider;
        $idDirector = (int)$idDirector;
        $idPeriodo  = (int)$idPeriodo;
        $conn = $this->conectar();

        // 1. Verificar identificación del líder
        $sqlIdEmp = "SELECT IDENTIFICACION FROM ZAYMAWEB.GHEMPEMPLEADOS
                     WHERE IDEMPLEADO = $idLider AND ROWNUM = 1";
        $qIdEmp   = oci_parse($conn, $sqlIdEmp);
        oci_execute($qIdEmp);
        $rowEmp = oci_fetch_assoc($qIdEmp);
        oci_free_statement($qIdEmp);
        if (!$rowEmp || trim($rowEmp['IDENTIFICACION']) !== trim($cedula)) {
            return ['ok' => false, 'msg' => 'La identificación no corresponde al líder.'];
        }

        // 2. Verificar contraseña
        $sqlUser = "SELECT IDUSUARIO, PASSWORD, CUENTA_ACTIVA
                    FROM VAADINWEB.HUMUSUARIOS
                    WHERE IDENTIFICACION = :cedula AND ROWNUM = 1";
        $qUser = oci_parse($conn, $sqlUser);
        oci_bind_by_name($qUser, ':cedula', $cedula);
        oci_execute($qUser);
        $user = oci_fetch_assoc($qUser);
        oci_free_statement($qUser);
        if (!$user) return ['ok' => false, 'msg' => 'El líder no tiene cuenta activa.'];
        if ((int)$user['CUENTA_ACTIVA'] !== 1) return ['ok' => false, 'msg' => 'La cuenta no está activa.'];
        if (!password_verify($password, $user['PASSWORD'])) return ['ok' => false, 'msg' => 'Contraseña incorrecta.'];

        // 3. Registrar firma en FIRMADO_LIDER
        $sqlFirma = "UPDATE VAADINWEB.HUMFEEDBACK
                     SET FIRMADO_LIDER  = 1,
                         FECHA_FIRMA_LIDER = SYSDATE
                     WHERE IDEMPLEADO       = :emp
                       AND IDEMPLEADO_LIDER = :dir
                       AND IDPERIODO        = :per
                       AND TIPO_FEEDBACK    = 2";
        $qFirma = oci_parse($conn, $sqlFirma);
        oci_bind_by_name($qFirma, ':emp', $idLider);
        oci_bind_by_name($qFirma, ':dir', $idDirector);
        oci_bind_by_name($qFirma, ':per', $idPeriodo);
        $ok = oci_execute($qFirma, OCI_COMMIT_ON_SUCCESS);
        oci_free_statement($qFirma);
        if (!$ok) return ['ok' => false, 'msg' => 'Error al registrar la firma.'];

        // 4. Activar acuerdos P12-P16: PENDIENTE_FIRMA → PENDIENTE
        $sqlAcuerdo = "UPDATE VAADINWEB.HUMACUERDOMEJORA
                       SET ESTADO = 'PENDIENTE'
                       WHERE IDEMPLEADO       = :emp
                         AND IDEMPLEADO_LIDER = :dir
                         AND IDPERIODO        = :per
                         AND ESTADO           = 'PENDIENTE_FIRMA'
                         AND NUM_COMPETENCIA  BETWEEN 12 AND 16";
        $qAcuerdo = oci_parse($conn, $sqlAcuerdo);
        oci_bind_by_name($qAcuerdo, ':emp', $idLider);
        oci_bind_by_name($qAcuerdo, ':dir', $idDirector);
        oci_bind_by_name($qAcuerdo, ':per', $idPeriodo);
        oci_execute($qAcuerdo, OCI_COMMIT_ON_SUCCESS);
        oci_free_statement($qAcuerdo);

        return ['ok' => true, 'msg' => 'Firma registrada correctamente.'];
    }

    // ── Validar credenciales y registrar firma del colaborador ─────────────────────
    public function firmarFeedback($idEmpleado, $idLider, $idPeriodo, $cedula, $password) {
        $idEmpleado = (int)$idEmpleado;
        $idLider    = (int)$idLider;
        $idPeriodo  = (int)$idPeriodo;

        $conn = $this->conectar();

        // 1. Verificar que la identificación corresponde al colaborador
        // Paso 1a: obtener la identificación real del colaborador desde nómina
        $sqlIdEmp = "SELECT IDENTIFICACION FROM ZAYMAWEB.GHEMPEMPLEADOS
                     WHERE IDEMPLEADO = $idEmpleado AND ROWNUM = 1";
        $qIdEmp   = oci_parse($conn, $sqlIdEmp);
        oci_execute($qIdEmp);
        $rowEmp = oci_fetch_assoc($qIdEmp);
        oci_free_statement($qIdEmp);

        if (!$rowEmp || trim($rowEmp['IDENTIFICACION']) !== trim($cedula)) {
            return ['ok' => false, 'msg' => 'La identificación no corresponde al colaborador.'];
        }

        // Paso 1b: buscar el usuario en HUMUSUARIOS por identificación
        $sqlUser = "SELECT IDUSUARIO, PASSWORD, CUENTA_ACTIVA
                    FROM VAADINWEB.HUMUSUARIOS
                    WHERE IDENTIFICACION = :cedula
                    AND ROWNUM = 1";
        $qUser = oci_parse($conn, $sqlUser);
        oci_bind_by_name($qUser, ':cedula', $cedula);
        oci_execute($qUser);
        $user = oci_fetch_assoc($qUser);
        oci_free_statement($qUser);

        if (!$user) {
            return ['ok' => false, 'msg' => 'El colaborador no tiene cuenta activa en el sistema.'];
        }
        if ((int)$user['CUENTA_ACTIVA'] !== 1) {
            return ['ok' => false, 'msg' => 'La cuenta del colaborador no está activa.'];
        }
        if (!password_verify($password, $user['PASSWORD'])) {
            return ['ok' => false, 'msg' => 'Contraseña incorrecta. Intenta nuevamente.'];
        }

        // 2. Registrar firma en HUMFEEDBACK
        $sqlFirma = "UPDATE VAADINWEB.HUMFEEDBACK
                     SET FIRMADO_COLAB = 1,
                         FECHA_FIRMA   = SYSDATE
                     WHERE IDEMPLEADO       = :emp
                       AND IDEMPLEADO_LIDER = :lider
                       AND IDPERIODO        = :per
                       AND TIPO_FEEDBACK    = 1";
        $qFirma = oci_parse($conn, $sqlFirma);
        oci_bind_by_name($qFirma, ':emp',   $idEmpleado);
        oci_bind_by_name($qFirma, ':lider', $idLider);
        oci_bind_by_name($qFirma, ':per',   $idPeriodo);
        $ok = oci_execute($qFirma, OCI_COMMIT_ON_SUCCESS);
        oci_free_statement($qFirma);

        if (!$ok) {
            return ['ok' => false, 'msg' => 'Error al registrar la firma. Intenta nuevamente.'];
        }

        // 3. Activar acuerdos: PENDIENTE_FIRMA -> PENDIENTE
        // Solo activa acuerdos P1-P11 (Proceso 1)
        $sqlAcuerdo = "UPDATE VAADINWEB.HUMACUERDOMEJORA
                       SET ESTADO = 'PENDIENTE'
                       WHERE IDEMPLEADO       = :emp
                         AND IDEMPLEADO_LIDER = :lider
                         AND IDPERIODO        = :per
                         AND ESTADO           = 'PENDIENTE_FIRMA'
                         AND NUM_COMPETENCIA  BETWEEN 1 AND 11";
        $qAcuerdo = oci_parse($conn, $sqlAcuerdo);
        oci_bind_by_name($qAcuerdo, ':emp',   $idEmpleado);
        oci_bind_by_name($qAcuerdo, ':lider', $idLider);
        oci_bind_by_name($qAcuerdo, ':per',   $idPeriodo);
        oci_execute($qAcuerdo, OCI_COMMIT_ON_SUCCESS);
        oci_free_statement($qAcuerdo);

        return ['ok' => true, 'msg' => 'Firma registrada correctamente.'];
    }

    // ── Flujo 3: firma del colaborador asistencial ───────────────────────────
    public function firmarFeedbackEA($idEmpleado, $idLider, $idPeriodo, $cedula, $password) {
        $idEmpleado = (int)$idEmpleado;
        $idLider    = (int)$idLider;
        $idPeriodo  = (int)$idPeriodo;
        $conn = $this->conectar();

        $sqlIdEmp = "SELECT IDENTIFICACION FROM ZAYMAWEB.GHEMPEMPLEADOS
                     WHERE IDEMPLEADO = $idEmpleado AND ROWNUM = 1";
        $qIdEmp   = oci_parse($conn, $sqlIdEmp);
        oci_execute($qIdEmp);
        $rowEmp = oci_fetch_assoc($qIdEmp);
        oci_free_statement($qIdEmp);
        if (!$rowEmp || trim($rowEmp['IDENTIFICACION']) !== trim($cedula)) {
            return ['ok' => false, 'msg' => 'La identificación no corresponde al colaborador.'];
        }

        $sqlUser = "SELECT IDUSUARIO, PASSWORD, CUENTA_ACTIVA
                    FROM VAADINWEB.HUMUSUARIOS
                    WHERE IDENTIFICACION = :cedula AND ROWNUM = 1";
        $qUser = oci_parse($conn, $sqlUser);
        oci_bind_by_name($qUser, ':cedula', $cedula);
        oci_execute($qUser);
        $user = oci_fetch_assoc($qUser);
        oci_free_statement($qUser);
        if (!$user)                           return ['ok' => false, 'msg' => 'El colaborador no tiene cuenta activa.'];
        if ((int)$user['CUENTA_ACTIVA'] !== 1) return ['ok' => false, 'msg' => 'La cuenta del colaborador no está activa.'];
        if (!password_verify($password, $user['PASSWORD'])) return ['ok' => false, 'msg' => 'Contraseña incorrecta.'];

        $sqlFirma = "UPDATE VAADINWEB.HUMFEEDBACK
                     SET FIRMADO_COLAB = 1, FECHA_FIRMA = SYSDATE
                     WHERE IDEMPLEADO       = :emp
                       AND IDEMPLEADO_LIDER = :lider
                       AND IDPERIODO        = :per
                       AND TIPO_FEEDBACK    = 3";
        $qFirma = oci_parse($conn, $sqlFirma);
        oci_bind_by_name($qFirma, ':emp',   $idEmpleado);
        oci_bind_by_name($qFirma, ':lider', $idLider);
        oci_bind_by_name($qFirma, ':per',   $idPeriodo);
        $ok = oci_execute($qFirma, OCI_COMMIT_ON_SUCCESS);
        oci_free_statement($qFirma);
        if (!$ok) return ['ok' => false, 'msg' => 'Error al registrar la firma.'];

        // Activar acuerdos EA: PENDIENTE_FIRMA -> PENDIENTE (P17-P22)
        $sqlAcuerdo = "UPDATE VAADINWEB.HUMACUERDOMEJORA
                       SET ESTADO = 'PENDIENTE'
                       WHERE IDEMPLEADO       = :emp
                         AND IDEMPLEADO_LIDER = :lider
                         AND IDPERIODO        = :per
                         AND ESTADO           = 'PENDIENTE_FIRMA'
                         AND NUM_COMPETENCIA  BETWEEN 17 AND 22";
        $qAcuerdo = oci_parse($conn, $sqlAcuerdo);
        oci_bind_by_name($qAcuerdo, ':emp',   $idEmpleado);
        oci_bind_by_name($qAcuerdo, ':lider', $idLider);
        oci_bind_by_name($qAcuerdo, ':per',   $idPeriodo);
        oci_execute($qAcuerdo, OCI_COMMIT_ON_SUCCESS);
        oci_free_statement($qAcuerdo);

        return ['ok' => true, 'msg' => 'Firma registrada correctamente.'];
    }

    // ── Flujo 2: feedback del director para el líder (home banner) ───────────
    public function getFeedbackDirEstadoLider(int $idLider, int $idPeriodo): ?array {
        $sql = "SELECT * FROM (
                    SELECT TO_CHAR(F.FECHA_FEEDBACK,'DD/MM/YYYY HH24:MI') AS FECHA_FEEDBACK,
                           NVL(GE.PNOMBRE || ' ' || GE.PAPELLIDO, 'Tu director') AS NOMBRE_DIRECTOR
                    FROM VAADINWEB.HUMFEEDBACK F
                    LEFT JOIN ZAYMAWEB.GHEMPEMPLEADOS GE ON F.IDEMPLEADO_LIDER = GE.IDEMPLEADO
                    WHERE F.IDEMPLEADO    = $idLider
                      AND F.IDPERIODO     = $idPeriodo
                      AND F.TIPO_FEEDBACK = 2
                      AND (F.FIRMADO_LIDER IS NULL OR F.FIRMADO_LIDER = 0)
                    ORDER BY F.IDFEEDBACK DESC
                ) WHERE ROWNUM = 1";
        $res = $this->ejecutarConsulta($sql);
        $row = $res ? oci_fetch_assoc($res) : null;
        if ($res) oci_free_statement($res);
        if (!is_array($row)) return null;
        $row['NOMBRE_DIRECTOR'] = fromOracleEncoding($row['NOMBRE_DIRECTOR'] ?? '');
        return $row;
    }

    // ── Flujo 3: equipo EA del líder con estado ──────────────────────────────
    public function getEquipoEAConEstado($idLider, $periodo) {
        $idLider   = (int)$idLider;
        $idPeriodo = (int)$periodo['IDPERIODO'];

        $sql = "
            SELECT
                HE.IDEMPLEADO,
                NVL(GE.PNOMBRE || ' ' || GE.PAPELLIDO, HE.NOMBRE) AS EMPLEADO,
                HE.CARGO,
                CASE WHEN A.IDEMPLEADO IS NOT NULL THEN 1 ELSE 0 END AS TIENE_AUTO_EA,
                CASE WHEN C.IDEMPLEADO IS NOT NULL THEN 1 ELSE 0 END AS TIENE_EVAL_EA,
                CASE WHEN F.IDFEEDBACK IS NOT NULL THEN 1 ELSE 0 END AS TIENE_FEEDBACK_EA,
                F.IDFEEDBACK,
                TO_CHAR(F.FECHA_FEEDBACK,'DD/MM/YYYY HH24:MI') AS FECHA_FEEDBACK,
                F.OBSERVACION,
                NVL(AC.TOTAL_ACUERDOS, 0) AS TOTAL_ACUERDOS_EA,
                NVL(AC.APROBADOS, 0) AS ACUERDOS_APROBADOS_EA,
                NVL(F.FIRMADO_COLAB, 0) AS FIRMADO_COLAB_EA
            FROM VAADINWEB.HUMEMPLEADOEVAL HE
            LEFT JOIN ZAYMAWEB.GHEMPEMPLEADOS GE ON HE.IDEMPLEADO = GE.IDEMPLEADO
            LEFT JOIN (
                SELECT DISTINCT R.IDEMPLEADO
                FROM VAADINWEB.HUMRESPUESTA R
                INNER JOIN VAADINWEB.HUMCOMPETENCIA CC ON R.IDCOMPETENCIA = CC.IDCOMPETENCIA
                WHERE R.TIPO_EVAL  = 'AUTO'
                  AND R.CONFIRMADO = 1
                  AND R.IDPERIODO  = $idPeriodo
                  AND CC.NUM_PREGUNTA BETWEEN 17 AND 22
            ) A ON HE.IDEMPLEADO = A.IDEMPLEADO
            LEFT JOIN (
                SELECT DISTINCT R.IDEMPLEADO
                FROM VAADINWEB.HUMRESPUESTA R
                INNER JOIN VAADINWEB.HUMCOMPETENCIA CC ON R.IDCOMPETENCIA = CC.IDCOMPETENCIA
                WHERE R.TIPO_EVAL       = 'EXPERIENCIA_COLAB'
                  AND R.IDEMPLEADO_EVAL = $idLider
                  AND R.CONFIRMADO      = 1
                  AND R.IDPERIODO       = $idPeriodo
                  AND CC.NUM_PREGUNTA BETWEEN 17 AND 22
            ) C ON HE.IDEMPLEADO = C.IDEMPLEADO
            LEFT JOIN VAADINWEB.HUMFEEDBACK F
                ON HE.IDEMPLEADO      = F.IDEMPLEADO
               AND F.IDEMPLEADO_LIDER = $idLider
               AND F.IDPERIODO        = $idPeriodo
               AND F.TIPO_FEEDBACK    = 3
            LEFT JOIN (
                SELECT IDEMPLEADO,
                       COUNT(*) AS TOTAL_ACUERDOS,
                       SUM(CASE WHEN ESTADO = 'APROBADO' THEN 1 ELSE 0 END) AS APROBADOS
                FROM VAADINWEB.HUMACUERDOMEJORA
                WHERE IDPERIODO        = $idPeriodo
                  AND IDEMPLEADO_LIDER = $idLider
                  AND NUM_COMPETENCIA  BETWEEN 17 AND 22
                GROUP BY IDEMPLEADO
            ) AC ON HE.IDEMPLEADO = AC.IDEMPLEADO
            WHERE HE.IDEMPLEADO_EVAL  = $idLider
              AND HE.ACTIVO           = 1
              AND HE.APLICA_EXP_AZUL  = 1
            ORDER BY
                CASE WHEN F.IDFEEDBACK IS NOT NULL THEN 2 ELSE 1 END,
                EMPLEADO ASC";

        $res  = $this->ejecutarConsulta($sql);
        $rows = [];
        if ($res) {
            while ($r = oci_fetch_assoc($res)) {
                $r['EMPLEADO']         = fromOracleEncoding($r['EMPLEADO'] ?? '');
                $r['CARGO']            = fromOracleEncoding($r['CARGO'] ?? '');
                $r['OBSERVACION']      = fromOracleEncoding($r['OBSERVACION'] ?? '');
                $r['FIRMADO_COLAB_EA'] = (int)($r['FIRMADO_COLAB_EA'] ?? 0);
                $rows[] = $r;
            }
            oci_free_statement($res);
        }
        return $rows;
    }

    // ── Flujo 3: calificaciones EA del colaborador (P17-P22) ─────────────────
    public function getCalificacionesEA($idEmpleado, $idLider, $periodo) {
        $idEmpleado = (int)$idEmpleado;
        $idLider    = (int)$idLider;
        $idPeriodo  = (int)$periodo['IDPERIODO'];

        $sqlAuto = "SELECT R.IDCOMPETENCIA, R.VALOR, O.ETIQUETA, C.NUM_PREGUNTA, C.NOMBRE AS NOMBRE_COMP
                    FROM VAADINWEB.HUMRESPUESTA R
                    INNER JOIN VAADINWEB.HUMOPCIONESCALA O ON R.IDOPCION = O.IDOPCION
                    INNER JOIN VAADINWEB.HUMCOMPETENCIA  C ON R.IDCOMPETENCIA = C.IDCOMPETENCIA
                    WHERE R.IDEMPLEADO      = $idEmpleado
                      AND R.IDEMPLEADO_EVAL = $idEmpleado
                      AND R.TIPO_EVAL       = 'AUTO'
                      AND R.CONFIRMADO      = 1
                      AND R.IDPERIODO       = $idPeriodo
                      AND C.NUM_PREGUNTA   BETWEEN 17 AND 22
                    ORDER BY C.NUM_PREGUNTA";
        $resAuto = $this->ejecutarConsulta($sqlAuto);
        $auto = [];
        if ($resAuto) {
            while ($r = oci_fetch_assoc($resAuto)) {
                $auto[(int)$r['IDCOMPETENCIA']] = [
                    'VALOR'        => (int)$r['VALOR'],
                    'ETIQUETA'     => fromOracleEncoding($r['ETIQUETA'] ?? ''),
                    'NOMBRE_COMP'  => fromOracleEncoding($r['NOMBRE_COMP'] ?? ''),
                    'NUM_PREGUNTA' => (int)$r['NUM_PREGUNTA'],
                ];
            }
            oci_free_statement($resAuto);
        }

        $sqlLider = "SELECT R.IDCOMPETENCIA, R.VALOR, O.ETIQUETA, C.NUM_PREGUNTA, C.NOMBRE AS NOMBRE_COMP
                     FROM VAADINWEB.HUMRESPUESTA R
                     INNER JOIN VAADINWEB.HUMOPCIONESCALA O ON R.IDOPCION = O.IDOPCION
                     INNER JOIN VAADINWEB.HUMCOMPETENCIA  C ON R.IDCOMPETENCIA = C.IDCOMPETENCIA
                     WHERE R.IDEMPLEADO      = $idEmpleado
                       AND R.IDEMPLEADO_EVAL = $idLider
                       AND R.TIPO_EVAL       = 'EXPERIENCIA_COLAB'
                       AND R.CONFIRMADO      = 1
                       AND R.IDPERIODO       = $idPeriodo
                       AND C.NUM_PREGUNTA   BETWEEN 17 AND 22
                     ORDER BY C.NUM_PREGUNTA";
        $resLider = $this->ejecutarConsulta($sqlLider);
        $lider = [];
        if ($resLider) {
            while ($r = oci_fetch_assoc($resLider)) {
                $lider[(int)$r['IDCOMPETENCIA']] = [
                    'VALOR'        => (int)$r['VALOR'],
                    'ETIQUETA'     => fromOracleEncoding($r['ETIQUETA'] ?? ''),
                    'NOMBRE_COMP'  => fromOracleEncoding($r['NOMBRE_COMP'] ?? ''),
                    'NUM_PREGUNTA' => (int)$r['NUM_PREGUNTA'],
                ];
            }
            oci_free_statement($resLider);
        }

        return ['auto' => $auto, 'lider' => $lider];
    }

    // ── Notificación de agendamiento por correo ──────────────────────────────
    // $idDest:        empleado que recibe el correo
    // $idRemitente:   líder/director que agendó
    // $flujo:         'COLAB' | 'LIDER' | 'EA'
    // $fechaFeedback: fecha/hora de la sesión en formato DD/MM/YYYY HH:MM
    // Feedbacks agendados por el líder/director con firma pendiente, todos los flujos
    // Retorna array de filas: TIPO_FEEDBACK, NOMBRE_EMPLEADO, FECHA_FEEDBACK
    public function getFeedbacksPendientesLider(int $idLider, int $idPeriodo): array {
        try {
            $sql = "SELECT F.TIPO_FEEDBACK,
                           TO_CHAR(F.FECHA_FEEDBACK,'DD/MM/YYYY HH24:MI') AS FECHA_FEEDBACK,
                           GE.PNOMBRE || ' ' || GE.PAPELLIDO AS NOMBRE_EMPLEADO
                    FROM VAADINWEB.HUMFEEDBACK F
                    INNER JOIN ZAYMAWEB.GHEMPEMPLEADOS GE ON GE.IDEMPLEADO = F.IDEMPLEADO
                    WHERE F.IDEMPLEADO_LIDER = :idl
                      AND F.IDPERIODO        = :idp
                      AND (
                          (F.TIPO_FEEDBACK IN (1,3) AND NVL(F.FIRMADO_COLAB,0) = 0)
                          OR
                          (F.TIPO_FEEDBACK = 2      AND NVL(F.FIRMADO_LIDER,0) = 0)
                      )
                    ORDER BY F.FECHA_FEEDBACK ASC NULLS LAST";
            $conn = $this->conectar();
            $q    = oci_parse($conn, $sql);
            oci_bind_by_name($q, ':idl', $idLider);
            oci_bind_by_name($q, ':idp', $idPeriodo);
            if (!oci_execute($q)) return [];
            $rows = [];
            while ($row = oci_fetch_assoc($q)) {
                $rows[] = [
                    'TIPO_FEEDBACK'   => (int)$row['TIPO_FEEDBACK'],
                    'NOMBRE_EMPLEADO' => fromOracleEncoding(trim($row['NOMBRE_EMPLEADO'] ?? '')),
                    'FECHA_FEEDBACK'  => trim($row['FECHA_FEEDBACK'] ?? ''),
                ];
            }
            oci_free_statement($q);
            return $rows;
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function enviarCorreoAgendamiento(int $idDest, int $idRemitente, string $flujo, string $fechaFeedback = ''): void {
        try {
            $conn = $this->conectar();

            // Nombre y correo del destinatario (prioridad: HUMEMPLEADOEVAL.EMAIL)
            $sqlDest = "SELECT NVL(GE.PNOMBRE || ' ' || GE.PAPELLIDO, 'Colaborador') AS NOMBRE,
                               NVL(
                                   (SELECT HE.EMAIL FROM VAADINWEB.HUMEMPLEADOEVAL HE
                                    WHERE HE.IDEMPLEADO = GE.IDEMPLEADO AND HE.ACTIVO = 1 AND ROWNUM = 1),
                                   GE.EMAIL
                               ) AS EMAIL
                        FROM ZAYMAWEB.GHEMPEMPLEADOS GE
                        WHERE GE.IDEMPLEADO = $idDest AND ROWNUM = 1";
            $resDest = $this->ejecutarConsulta($sqlDest);
            $rowDest = $resDest ? oci_fetch_assoc($resDest) : null;
            if ($resDest) oci_free_statement($resDest);

            $email  = trim($rowDest['EMAIL'] ?? '');
            $nombre = fromOracleEncoding(trim($rowDest['NOMBRE'] ?? 'Colaborador'));
            if (empty($email)) return;

            // Nombre del remitente
            $sqlRem = "SELECT NVL(PNOMBRE || ' ' || PAPELLIDO, 'tu lider') AS NOMBRE
                       FROM ZAYMAWEB.GHEMPEMPLEADOS
                       WHERE IDEMPLEADO = $idRemitente AND ROWNUM = 1";
            $resRem = $this->ejecutarConsulta($sqlRem);
            $rowRem = $resRem ? oci_fetch_assoc($resRem) : null;
            if ($resRem) oci_free_statement($resRem);
            $nombreRem = fromOracleEncoding(trim($rowRem['NOMBRE'] ?? 'tu lider'));

            // Formatear fecha legible en español
            $meses = [1=>'enero',2=>'febrero',3=>'marzo',4=>'abril',5=>'mayo',6=>'junio',
                      7=>'julio',8=>'agosto',9=>'septiembre',10=>'octubre',11=>'noviembre',12=>'diciembre'];
            $partesFecha = explode(' ', trim($fechaFeedback));
            $partesD     = explode('/', $partesFecha[0] ?? '');
            $dia         = (int)($partesD[0] ?? 0);
            $mes         = (int)($partesD[1] ?? 0);
            $anio        = $partesD[2] ?? '';
            $hora        = $partesFecha[1] ?? '';
            $fechaLinea  = ($dia && $mes && $anio)
                ? 'el ' . $dia . ' de ' . ($meses[$mes] ?? '') . ' de ' . $anio . ($hora ? ' a las ' . $hora : '')
                : '';

            // Contenido según flujo
            if ($flujo === 'EA') {
                $asunto    = 'Tu sesion de Experiencia Azul ha sido agendada';
                $color1    = '#0891b2'; $color2 = '#06b6d4';
                $rol       = 'tu lider';
                $tipoSesion = 'Experiencia Azul';
                $paso1     = 'Reunirte con tu lider ' . ($fechaLinea ? $fechaLinea : 'en la fecha acordada') . '.';
                $paso2     = 'Recibir la retroalimentacion sobre las competencias de Experiencia Azul.';
                $paso3     = 'Firmar el recibido despues de la retroalimentacion para poder ver los objetivos asignados en tu <strong>Plan de Mejora</strong>.';
            } elseif ($flujo === 'LIDER') {
                $asunto    = 'Tu sesion de feedback de liderazgo ha sido agendada';
                $color1    = '#7c3aed'; $color2 = '#a78bfa';
                $rol       = 'tu director';
                $tipoSesion = 'feedback de liderazgo';
                $paso1     = 'Reunirte con tu director ' . ($fechaLinea ? $fechaLinea : 'en la fecha acordada') . '.';
                $paso2     = 'Recibir la retroalimentacion sobre tus competencias de liderazgo.';
                $paso3     = 'Firmar el recibido despues de la retroalimentacion para poder ver los objetivos asignados en tu <strong>Plan de Mejora</strong>.';
            } else {
                $asunto    = 'Tu sesion de feedback de desempeno ha sido agendada';
                $color1    = '#0058af'; $color2 = '#2563eb';
                $rol       = 'tu lider';
                $tipoSesion = 'feedback de desempeno';
                $paso1     = 'Reunirte con tu lider ' . ($fechaLinea ? $fechaLinea : 'en la fecha acordada') . '.';
                $paso2     = 'Recibir la retroalimentacion de tu proceso de evaluacion de desempeno.';
                $paso3     = 'Firmar el recibido despues de la retroalimentacion para poder ver los objetivos asignados en tu <strong>Plan de Mejora</strong>.';
            }

            $fechaDestacada = $fechaLinea
                ? '<div style="background:#f0f9ff;border:1px solid #bae6fd;border-radius:8px;'
                .      'padding:12px 16px;margin:16px 0;text-align:center;">'
                . '<p style="margin:0;font-size:13px;color:#0369a1;font-weight:700;">Fecha y hora de la sesion</p>'
                . '<p style="margin:4px 0 0;font-size:15px;color:#0c4a6e;font-weight:600;">' . ucfirst($fechaLinea) . '</p>'
                . '</div>'
                : '';

            $pasos =
                '<table style="width:100%;border-collapse:collapse;margin:16px 0;">'
                . '<tr><td style="width:28px;padding:6px 10px 6px 0;vertical-align:top;">'
                .     '<div style="width:24px;height:24px;border-radius:50%;background:' . $color1 . ';'
                .          'color:#fff;font-size:12px;font-weight:700;text-align:center;line-height:24px;">1</div>'
                . '</td><td style="padding:6px 0;font-size:13px;color:#475569;">' . $paso1 . '</td></tr>'
                . '<tr><td style="padding:6px 10px 6px 0;vertical-align:top;">'
                .     '<div style="width:24px;height:24px;border-radius:50%;background:' . $color1 . ';'
                .          'color:#fff;font-size:12px;font-weight:700;text-align:center;line-height:24px;">2</div>'
                . '</td><td style="padding:6px 0;font-size:13px;color:#475569;">' . $paso2 . '</td></tr>'
                . '<tr><td style="padding:6px 10px 6px 0;vertical-align:top;">'
                .     '<div style="width:24px;height:24px;border-radius:50%;background:' . $color1 . ';'
                .          'color:#fff;font-size:12px;font-weight:700;text-align:center;line-height:24px;">3</div>'
                . '</td><td style="padding:6px 0;font-size:13px;color:#475569;">' . $paso3 . '</td></tr>'
                . '</table>';

            // Charset iso-8859-1 para compatibilidad con Oracle WE8MSWIN1252
            $mensaje =
                '<html><head><meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/></head><body>'
                . '<div style="font-family:Arial,sans-serif;max-width:600px;margin:auto;padding:24px;">'
                . '<div style="background:linear-gradient(135deg,' . $color1 . ',' . $color2 . ');'
                .      'padding:24px;border-radius:12px 12px 0 0;text-align:center;">'
                . '<h2 style="color:#ffffff;margin:0;font-size:20px;">Gestion Humana y Cultura</h2>'
                . '<p style="color:rgba(255,255,255,.8);margin:8px 0 0;font-size:13px;">Clinica Zayma</p>'
                . '</div>'
                . '<div style="background:#ffffff;padding:28px;border:1px solid #e2e8f0;border-radius:0 0 12px 12px;">'
                . '<p style="color:#1e293b;font-size:15px;margin-top:0;">Hola <strong>' . $nombre . '</strong>,</p>'
                . '<p style="color:#475569;font-size:14px;line-height:1.6;margin-bottom:4px;">'
                . $rol . ' <strong>' . $nombreRem . '</strong> ha registrado tu sesion de '
                . '<strong>' . $tipoSesion . '</strong>. A continuacion encontraras los detalles y los pasos a seguir.'
                . '</p>'
                . $fechaDestacada
                . '<div style="border-left:3px solid ' . $color1 . ';padding-left:14px;margin:16px 0;">'
                . '<p style="margin:0 0 8px;color:#1e293b;font-size:13px;font-weight:700;">&iquest;Que debes hacer?</p>'
                . $pasos
                . '</div>'
                . '<p style="color:#94a3b8;font-size:11px;margin-top:24px;border-top:1px solid #f1f5f9;padding-top:14px;">'
                . 'Este correo fue generado automaticamente por el sistema de Gestion Humana y Cultura de Clinica Zayma. '
                . 'Por favor no respondas a este mensaje.'
                . '</p>'
                . '</div></div></body></html>';

            // Convertir UTF-8 a Windows-1252 para Oracle (evita caracteres corruptos)
            $mensaje = $this->toOracle($mensaje);
            $asunto  = $this->toOracle($asunto);

            $sqlMail = 'BEGIN ZAYMAWEB.PKT_NOTIFICACION_MAIL.NOTIFICACION(:cat,:asunto,:msg,:tipo,:correo,1); END;';
            $q    = oci_parse($conn, $sqlMail);
            $cat  = 'FEEDBACK AGENDADO';
            $tipo = 'GESTION HUMANA';
            $clob = oci_new_descriptor($conn, OCI_DTYPE_LOB);
            $clob->writetemporary($mensaje, OCI_TEMP_CLOB);
            oci_bind_by_name($q, ':cat',    $cat,    100);
            oci_bind_by_name($q, ':asunto', $asunto, 200);
            oci_bind_by_name($q, ':msg',    $clob,   -1, OCI_B_CLOB);
            oci_bind_by_name($q, ':tipo',   $tipo,   100);
            oci_bind_by_name($q, ':correo', $email,  300);
            oci_execute($q, OCI_COMMIT_ON_SUCCESS);
            $clob->free();
            oci_free_statement($q);
        } catch (\Throwable $e) {
            // Correo es auxiliar — no interrumpir el flujo principal
        }
    }
}
