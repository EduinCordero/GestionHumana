<?php
namespace app\models;

use app\models\mainModel;

/**
 * competenciaModel — Modelo base del sistema de evaluación V2
 *
 * Lee toda la configuración desde BD:
 * HUMESCALA, HUMOPCIONESCALA, HUMDIMENSION, HUMCOMPETENCIA,
 * HUMOPCIONCOMPETENCIA, HUMROL, HUMROLCOMPETENCIA, HUMRESPUESTA
 *
 * Reemplaza los arrays hardcodeados en el código (getDictColaborador,
 * getDictLiderazgo, $escala, etc.)
 */
class competenciaModel extends mainModel {

    // ── Cache en memoria para evitar consultas repetidas ─────────────────────
    private static array $cacheEscalas      = [];
    private static array $cacheOpciones     = [];
    private static array $cacheDimensiones  = [];
    private static array $cacheCompetencias = [];

    // ══════════════════════════════════════════════════════════════════════════
    // ESCALAS Y OPCIONES
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Devuelve todas las escalas activas
     * [IDESCALA => ['NOMBRE' => ..., 'DESCRIPCION' => ...]]
     */
    public function getEscalas(): array {
        if (!empty(self::$cacheEscalas)) return self::$cacheEscalas;

        $sql = "SELECT IDESCALA, NOMBRE, DESCRIPCION
                FROM VAADINWEB.HUMESCALA
                WHERE ACTIVO = 1
                ORDER BY IDESCALA";
        $res  = $this->ejecutarConsulta($sql);
        $rows = [];
        if ($res) {
            while ($r = oci_fetch_assoc($res)) {
                $rows[(int)$r['IDESCALA']] = [
                    'NOMBRE'      => mb_convert_encoding($r['NOMBRE']      ?? '', 'UTF-8', 'ISO-8859-1'),
                    'DESCRIPCION' => mb_convert_encoding($r['DESCRIPCION'] ?? '', 'UTF-8', 'ISO-8859-1'),
                ];
            }
            oci_free_statement($res);
        }
        self::$cacheEscalas = $rows;
        return $rows;
    }

    /**
     * Devuelve las opciones de una escala ordenadas para mostrar en el formulario
     * [VALOR => ['ETIQUETA' => ..., 'DESCRIPCION_GEN' => ..., 'ORDEN' => ...]]
     */
    public function getOpcionesPorEscala(int $idEscala): array {
        if (isset(self::$cacheOpciones[$idEscala])) return self::$cacheOpciones[$idEscala];

        $sql = "SELECT IDOPCION, VALOR, ETIQUETA, DESCRIPCION_GEN, ORDEN
                FROM VAADINWEB.HUMOPCIONESCALA
                WHERE IDESCALA = $idEscala AND ACTIVO = 1
                ORDER BY ORDEN";
        $res  = $this->ejecutarConsulta($sql);
        $rows = [];
        if ($res) {
            while ($r = oci_fetch_assoc($res)) {
                $rows[(int)$r['VALOR']] = [
                    'IDOPCION'       => (int)$r['IDOPCION'],
                    'ETIQUETA'       => mb_convert_encoding($r['ETIQUETA']       ?? '', 'UTF-8', 'ISO-8859-1'),
                    'DESCRIPCION_GEN'=> mb_convert_encoding($r['DESCRIPCION_GEN']?? '', 'UTF-8', 'ISO-8859-1'),
                    'ORDEN'          => (int)$r['ORDEN'],
                ];
            }
            oci_free_statement($res);
        }
        self::$cacheOpciones[$idEscala] = $rows;
        return $rows;
    }

    /**
     * Devuelve el mapa etiqueta → valor numérico para una escala
     * Útil para convertir respuestas de texto a número en cálculos
     * ['Sobresaliente' => 5, 'Acorde' => 4, ...]
     */
    public function getMapaEtiquetaValor(int $idEscala): array {
        $opciones = $this->getOpcionesPorEscala($idEscala);
        $mapa = [];
        foreach ($opciones as $valor => $data) {
            $mapa[$data['ETIQUETA']] = $valor;
        }
        return $mapa;
    }

    /**
     * Devuelve el mapa valor numérico → etiqueta para una escala
     * ['5' => 'Sobresaliente', '4' => 'Acorde', ...]
     */
    public function getMapaValorEtiqueta(int $idEscala): array {
        $opciones = $this->getOpcionesPorEscala($idEscala);
        $mapa = [];
        foreach ($opciones as $valor => $data) {
            $mapa[$valor] = $data['ETIQUETA'];
        }
        return $mapa;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // DIMENSIONES
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Devuelve todas las dimensiones activas con su escala
     * [IDDIMENSION => ['NOMBRE' => ..., 'DESCRIPCION' => ..., 'IDESCALA' => ..., 'ORDEN' => ...]]
     */
    public function getDimensiones(): array {
        if (!empty(self::$cacheDimensiones)) return self::$cacheDimensiones;

        $sql = "SELECT D.IDDIMENSION, D.NOMBRE, D.DESCRIPCION, D.IDESCALA, D.ORDEN,
                       E.NOMBRE AS NOMBRE_ESCALA
                FROM VAADINWEB.HUMDIMENSION D
                INNER JOIN VAADINWEB.HUMESCALA E ON D.IDESCALA = E.IDESCALA
                WHERE D.ACTIVO = 1
                ORDER BY D.ORDEN";
        $res  = $this->ejecutarConsulta($sql);
        $rows = [];
        if ($res) {
            while ($r = oci_fetch_assoc($res)) {
                $rows[(int)$r['IDDIMENSION']] = [
                    'NOMBRE'        => mb_convert_encoding($r['NOMBRE']       ?? '', 'UTF-8', 'ISO-8859-1'),
                    'DESCRIPCION'   => mb_convert_encoding($r['DESCRIPCION']  ?? '', 'UTF-8', 'ISO-8859-1'),
                    'NOMBRE_ESCALA' => mb_convert_encoding($r['NOMBRE_ESCALA']?? '', 'UTF-8', 'ISO-8859-1'),
                    'IDESCALA'      => (int)$r['IDESCALA'],
                    'ORDEN'         => (int)$r['ORDEN'],
                ];
            }
            oci_free_statement($res);
        }
        self::$cacheDimensiones = $rows;
        return $rows;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // COMPETENCIAS
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Devuelve todas las competencias activas con su dimensión y escala
     * Ordenadas por dimensión y orden interno
     */
    public function getCompetencias(): array {
        if (!empty(self::$cacheCompetencias)) return self::$cacheCompetencias;

        $sql = "SELECT C.IDCOMPETENCIA, C.IDDIMENSION, C.NOMBRE, C.PREGUNTA,
                       C.ORDEN, C.NUM_PREGUNTA,
                       D.NOMBRE AS NOMBRE_DIMENSION, D.IDESCALA, D.ORDEN AS ORDEN_DIM
                FROM VAADINWEB.HUMCOMPETENCIA C
                INNER JOIN VAADINWEB.HUMDIMENSION D ON C.IDDIMENSION = D.IDDIMENSION
                WHERE C.ACTIVO = 1 AND D.ACTIVO = 1
                ORDER BY D.ORDEN, C.ORDEN";
        $res  = $this->ejecutarConsulta($sql);
        $rows = [];
        if ($res) {
            while ($r = oci_fetch_assoc($res)) {
                $rows[(int)$r['IDCOMPETENCIA']] = [
                    'IDCOMPETENCIA'    => (int)$r['IDCOMPETENCIA'],
                    'IDDIMENSION'      => (int)$r['IDDIMENSION'],
                    'NOMBRE'           => mb_convert_encoding($r['NOMBRE']           ?? '', 'UTF-8', 'ISO-8859-1'),
                    'PREGUNTA'         => mb_convert_encoding($r['PREGUNTA']         ?? '', 'UTF-8', 'ISO-8859-1'),
                    'NOMBRE_DIMENSION' => mb_convert_encoding($r['NOMBRE_DIMENSION'] ?? '', 'UTF-8', 'ISO-8859-1'),
                    'ORDEN'            => (int)$r['ORDEN'],
                    'NUM_PREGUNTA'     => (int)$r['NUM_PREGUNTA'],
                    'IDESCALA'         => (int)$r['IDESCALA'],
                    'ORDEN_DIM'        => (int)$r['ORDEN_DIM'],
                ];
            }
            oci_free_statement($res);
        }
        self::$cacheCompetencias = $rows;
        return $rows;
    }

    /**
     * Devuelve una sola competencia por ID
     */
    public function getCompetenciaPorId(int $idCompetencia): ?array {
        $todas = $this->getCompetencias();
        return $todas[$idCompetencia] ?? null;
    }

    /**
     * Devuelve el diccionario simple IDCOMPETENCIA → NOMBRE
     * Equivalente al antiguo getDictColaborador() + getDictLiderazgo()
     * [1 => 'Calidez Humana...', 2 => 'Integridad...', ...]
     */
    public function getDictCompetencias(): array {
        $dict = [];
        foreach ($this->getCompetencias() as $id => $comp) {
            $dict[$id] = $comp['NOMBRE'];
        }
        return $dict;
    }

    /**
     * Devuelve el diccionario NUM_PREGUNTA → NOMBRE
     * Útil para compatibilidad con código que usa números de pregunta
     */
    public function getDictPorNumPregunta(): array {
        $dict = [];
        foreach ($this->getCompetencias() as $comp) {
            $dict[$comp['NUM_PREGUNTA']] = $comp['NOMBRE'];
        }
        return $dict;
    }

    /**
     * Devuelve competencias agrupadas por dimensión
     * [IDDIMENSION => ['info' => [...], 'competencias' => [...]]]
     */
    public function getCompetenciasPorDimension(): array {
        $dimensiones = $this->getDimensiones();
        $competencias = $this->getCompetencias();
        $agrupadas = [];

        foreach ($dimensiones as $idDim => $dim) {
            $agrupadas[$idDim] = [
                'info'         => $dim,
                'competencias' => [],
            ];
        }

        foreach ($competencias as $comp) {
            $idDim = $comp['IDDIMENSION'];
            if (isset($agrupadas[$idDim])) {
                $agrupadas[$idDim]['competencias'][] = $comp;
            }
        }

        return $agrupadas;
    }

    /**
     * Devuelve las opciones específicas de una competencia
     * Combina descripción genérica de HUMOPCIONESCALA con descripción
     * específica de HUMOPCIONCOMPETENCIA si existe
     * [VALOR => ['ETIQUETA' => ..., 'DESCRIPCION' => ..., 'IDOPCION' => ...]]
     */
    public function getOpcionesPorCompetencia(int $idCompetencia): array {
        // Primero obtener la escala de la competencia
        $comp = $this->getCompetenciaPorId($idCompetencia);
        if (!$comp) return [];

        $idEscala = $comp['IDESCALA'];

        // Consulta que combina descripción genérica con específica
        $sql = "SELECT O.IDOPCION, O.VALOR, O.ETIQUETA, O.ORDEN,
                       NVL(OC.DESCRIPCION, O.DESCRIPCION_GEN) AS DESCRIPCION
                FROM VAADINWEB.HUMOPCIONESCALA O
                LEFT JOIN VAADINWEB.HUMOPCIONCOMPETENCIA OC
                    ON O.IDOPCION = OC.IDOPCION AND OC.IDCOMPETENCIA = $idCompetencia AND OC.ACTIVO = 1
                WHERE O.IDESCALA = $idEscala AND O.ACTIVO = 1
                ORDER BY O.ORDEN";
        $res  = $this->ejecutarConsulta($sql);
        $rows = [];
        if ($res) {
            while ($r = oci_fetch_assoc($res)) {
                $rows[(int)$r['VALOR']] = [
                    'IDOPCION'   => (int)$r['IDOPCION'],
                    'ETIQUETA'   => mb_convert_encoding($r['ETIQUETA']   ?? '', 'UTF-8', 'ISO-8859-1'),
                    'DESCRIPCION'=> mb_convert_encoding($r['DESCRIPCION']?? '', 'UTF-8', 'ISO-8859-1'),
                    'ORDEN'      => (int)$r['ORDEN'],
                ];
            }
            oci_free_statement($res);
        }
        return $rows;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // ROLES Y ASIGNACIÓN
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Devuelve los roles activos
     * [IDROL => ['NOMBRE' => ..., 'DESCRIPCION' => ...]]
     */
    public function getRoles(): array {
        $sql = "SELECT IDROL, NOMBRE, DESCRIPCION
                FROM VAADINWEB.HUMROL
                WHERE ACTIVO = 1 ORDER BY IDROL";
        $res  = $this->ejecutarConsulta($sql);
        $rows = [];
        if ($res) {
            while ($r = oci_fetch_assoc($res)) {
                $rows[(int)$r['IDROL']] = [
                    'NOMBRE'      => mb_convert_encoding($r['NOMBRE']      ?? '', 'UTF-8', 'ISO-8859-1'),
                    'DESCRIPCION' => mb_convert_encoding($r['DESCRIPCION'] ?? '', 'UTF-8', 'ISO-8859-1'),
                ];
            }
            oci_free_statement($res);
        }
        return $rows;
    }

    /**
     * Devuelve las competencias que debe evaluar un rol según el tipo de evaluación
     * Útil para generar el formulario dinámicamente
     *
     * @param int    $idRol    ID del rol (1=Colaborador, 2=Líder, etc.)
     * @param string $tipoEval AUTO|LIDER_A_COLAB|COLAB_A_LIDER|EXPERIENCIA_COLAB|EXPERIENCIA_LIDER
     * @return array Lista de competencias con toda su información
     */
    public function getCompetenciasPorRolTipo(int $idRol, string $tipoEval): array {
        $sql = "SELECT C.IDCOMPETENCIA, C.NOMBRE, C.PREGUNTA, C.ORDEN, C.NUM_PREGUNTA,
                       D.IDDIMENSION, D.NOMBRE AS NOMBRE_DIMENSION, D.DESCRIPCION AS DESC_DIMENSION,
                       D.IDESCALA, D.ORDEN AS ORDEN_DIM
                FROM VAADINWEB.HUMROLCOMPETENCIA RC
                INNER JOIN VAADINWEB.HUMCOMPETENCIA C  ON RC.IDCOMPETENCIA = C.IDCOMPETENCIA
                INNER JOIN VAADINWEB.HUMDIMENSION   D  ON C.IDDIMENSION    = D.IDDIMENSION
                WHERE RC.IDROL     = $idRol
                  AND RC.TIPO_EVAL = '$tipoEval'
                  AND RC.ACTIVO    = 1
                  AND C.ACTIVO     = 1
                  AND D.ACTIVO     = 1
                ORDER BY D.ORDEN, C.ORDEN";
        $res  = $this->ejecutarConsulta($sql);
        $rows = [];
        if ($res) {
            while ($r = oci_fetch_assoc($res)) {
                $rows[] = [
                    'IDCOMPETENCIA'    => (int)$r['IDCOMPETENCIA'],
                    'NOMBRE'           => mb_convert_encoding($r['NOMBRE']           ?? '', 'UTF-8', 'ISO-8859-1'),
                    'PREGUNTA'         => mb_convert_encoding($r['PREGUNTA']         ?? '', 'UTF-8', 'ISO-8859-1'),
                    'NOMBRE_DIMENSION' => mb_convert_encoding($r['NOMBRE_DIMENSION'] ?? '', 'UTF-8', 'ISO-8859-1'),
                    'DESC_DIMENSION'   => mb_convert_encoding($r['DESC_DIMENSION']   ?? '', 'UTF-8', 'ISO-8859-1'),
                    'ORDEN'            => (int)$r['ORDEN'],
                    'NUM_PREGUNTA'     => (int)$r['NUM_PREGUNTA'],
                    'IDDIMENSION'      => (int)$r['IDDIMENSION'],
                    'IDESCALA'         => (int)$r['IDESCALA'],
                    'ORDEN_DIM'        => (int)$r['ORDEN_DIM'],
                ];
            }
            oci_free_statement($res);
        }
        return $rows;
    }

    /**
     * Devuelve todos los tipos de evaluación que debe realizar un rol
     * Útil para saber cuántos formularios debe completar un empleado
     * ['AUTO', 'LIDER_A_COLAB', ...]
     */
    public function getTiposEvalPorRol(int $idRol): array {
        $sql = "SELECT DISTINCT TIPO_EVAL
                FROM VAADINWEB.HUMROLCOMPETENCIA
                WHERE IDROL = $idRol AND ACTIVO = 1
                ORDER BY TIPO_EVAL";
        $res  = $this->ejecutarConsulta($sql);
        $rows = [];
        if ($res) {
            while ($r = oci_fetch_assoc($res)) {
                $rows[] = $r['TIPO_EVAL'];
            }
            oci_free_statement($res);
        }
        return $rows;
    }

    /**
     * Determina el ID de rol de un empleado
     * Por ahora usa el nivel de cargo de sesión hasta que llegue el Excel
     * Cuando llegue: consultar HUMEMPLEADOEVAL
     */
    public function getRolPorNivelCargo(string $nivelCargo): int {
        // NC001 = Colaborador (1)
        // NC002+ = Líder (2)
        // Cuando llegue Excel del director: leer de HUMEMPLEADOEVAL
        return in_array($nivelCargo, ['NC002','NC003','NC004','NC005']) ? 2 : 1;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // RESPUESTAS — GUARDAR Y LEER
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Guarda una respuesta individual en HUMRESPUESTA
     * Usa MERGE para actualizar si ya existe (idempotente)
     */
    public function guardarRespuesta(
        int    $idPeriodo,
        int    $idEmpleado,
        int    $idEmpleadoEval,
        int    $idCompetencia,
        int    $idOpcion,
        int    $valor,
        string $tipoEval
    ): bool {
        $conn = $this->conectar();

        // Verificar si ya existe
        $sqlCheck = "SELECT IDRESPUESTA FROM VAADINWEB.HUMRESPUESTA
                     WHERE IDPERIODO       = :per
                       AND IDEMPLEADO      = :emp
                       AND IDEMPLEADO_EVAL = :eval
                       AND IDCOMPETENCIA   = :comp
                       AND TIPO_EVAL       = :tipo
                     AND ROWNUM = 1";
        $qCheck = oci_parse($conn, $sqlCheck);
        oci_bind_by_name($qCheck, ':per',  $idPeriodo);
        oci_bind_by_name($qCheck, ':emp',  $idEmpleado);
        oci_bind_by_name($qCheck, ':eval', $idEmpleadoEval);
        oci_bind_by_name($qCheck, ':comp', $idCompetencia);
        oci_bind_by_name($qCheck, ':tipo', $tipoEval);
        oci_execute($qCheck);
        $existing = oci_fetch_assoc($qCheck);
        oci_free_statement($qCheck);

        if ($existing) {
            // Actualizar
            $sql = "UPDATE VAADINWEB.HUMRESPUESTA
                    SET IDOPCION = :opc, VALOR = :val, FECHARESPUESTA = SYSDATE
                    WHERE IDRESPUESTA = :id";
            $q = oci_parse($conn, $sql);
            oci_bind_by_name($q, ':opc', $idOpcion);
            oci_bind_by_name($q, ':val', $valor);
            oci_bind_by_name($q, ':id',  $existing['IDRESPUESTA']);
        } else {
            // Insertar
            $sql = "INSERT INTO VAADINWEB.HUMRESPUESTA
                        (IDRESPUESTA, IDPERIODO, IDEMPLEADO, IDEMPLEADO_EVAL,
                         IDCOMPETENCIA, IDOPCION, VALOR, TIPO_EVAL, CONFIRMADO)
                    VALUES (VAADINWEB.SEQ_HUMRESPUESTA.NEXTVAL, :per, :emp, :eval,
                            :comp, :opc, :val, :tipo, 0)";
            $q = oci_parse($conn, $sql);
            oci_bind_by_name($q, ':per',  $idPeriodo);
            oci_bind_by_name($q, ':emp',  $idEmpleado);
            oci_bind_by_name($q, ':eval', $idEmpleadoEval);
            oci_bind_by_name($q, ':comp', $idCompetencia);
            oci_bind_by_name($q, ':opc',  $idOpcion);
            oci_bind_by_name($q, ':val',  $valor);
            oci_bind_by_name($q, ':tipo', $tipoEval);
        }

        $ok = oci_execute($q, OCI_COMMIT_ON_SUCCESS);
        oci_free_statement($q);
        return $ok;
    }

    /**
     * Confirma todas las respuestas de una evaluación
     * Se llama al hacer clic en "Enviar evaluación"
     */
    public function confirmarEvaluacion(
        int    $idPeriodo,
        int    $idEmpleado,
        int    $idEmpleadoEval,
        string $tipoEval
    ): bool {
        $conn = $this->conectar();
        $sql  = "UPDATE VAADINWEB.HUMRESPUESTA
                 SET CONFIRMADO = 1, FECHACONFIRMA = SYSDATE
                 WHERE IDPERIODO       = :per
                   AND IDEMPLEADO      = :emp
                   AND IDEMPLEADO_EVAL = :eval
                   AND TIPO_EVAL       = :tipo
                   AND CONFIRMADO      = 0";
        $q = oci_parse($conn, $sql);
        oci_bind_by_name($q, ':per',  $idPeriodo);
        oci_bind_by_name($q, ':emp',  $idEmpleado);
        oci_bind_by_name($q, ':eval', $idEmpleadoEval);
        oci_bind_by_name($q, ':tipo', $tipoEval);
        $ok = oci_execute($q, OCI_COMMIT_ON_SUCCESS);
        oci_free_statement($q);
        return $ok;
    }

    /**
     * Verifica si un empleado ya completó un tipo de evaluación en el período
     */
    public function estaConfirmada(
        int    $idPeriodo,
        int    $idEmpleado,
        int    $idEmpleadoEval,
        string $tipoEval
    ): bool {
        $sql = "SELECT COUNT(*) AS CNT
                FROM VAADINWEB.HUMRESPUESTA
                WHERE IDPERIODO       = $idPeriodo
                  AND IDEMPLEADO      = $idEmpleado
                  AND IDEMPLEADO_EVAL = $idEmpleadoEval
                  AND TIPO_EVAL       = '$tipoEval'
                  AND CONFIRMADO      = 1";
        $res = $this->ejecutarConsulta($sql);
        $row = $res ? oci_fetch_assoc($res) : null;
        if ($res) oci_free_statement($res);
        return $row ? (int)$row['CNT'] > 0 : false;
    }

    /**
     * Devuelve las respuestas confirmadas de un empleado en un período y tipo
     * [IDCOMPETENCIA => ['VALOR' => N, 'ETIQUETA' => '...', 'NOMBRE_COMP' => '...']]
     */
    public function getRespuestas(
        int    $idPeriodo,
        int    $idEmpleado,
        int    $idEmpleadoEval,
        string $tipoEval
    ): array {
        $sql = "SELECT R.IDCOMPETENCIA, R.VALOR, R.IDOPCION,
                       O.ETIQUETA, C.NOMBRE AS NOMBRE_COMP, C.NUM_PREGUNTA
                FROM VAADINWEB.HUMRESPUESTA R
                INNER JOIN VAADINWEB.HUMOPCIONESCALA O ON R.IDOPCION = O.IDOPCION
                INNER JOIN VAADINWEB.HUMCOMPETENCIA  C ON R.IDCOMPETENCIA = C.IDCOMPETENCIA
                WHERE R.IDPERIODO       = $idPeriodo
                  AND R.IDEMPLEADO      = $idEmpleado
                  AND R.IDEMPLEADO_EVAL = $idEmpleadoEval
                  AND R.TIPO_EVAL       = '$tipoEval'
                  AND R.CONFIRMADO      = 1
                ORDER BY C.NUM_PREGUNTA";
        $res  = $this->ejecutarConsulta($sql);
        $rows = [];
        if ($res) {
            while ($r = oci_fetch_assoc($res)) {
                $rows[(int)$r['IDCOMPETENCIA']] = [
                    'VALOR'       => (int)$r['VALOR'],
                    'IDOPCION'    => (int)$r['IDOPCION'],
                    'ETIQUETA'    => mb_convert_encoding($r['ETIQUETA']   ?? '', 'UTF-8', 'ISO-8859-1'),
                    'NOMBRE_COMP' => mb_convert_encoding($r['NOMBRE_COMP']?? '', 'UTF-8', 'ISO-8859-1'),
                    'NUM_PREGUNTA'=> (int)$r['NUM_PREGUNTA'],
                ];
            }
            oci_free_statement($res);
        }
        return $rows;
    }

    /**
     * Devuelve promedios por competencia para un empleado en un período
     * Calcula el promedio de todas las evaluaciones recibidas de un tipo
     * [IDCOMPETENCIA => ['PROMEDIO' => 3.5, 'TOTAL_EVAL' => 2, 'NOMBRE_COMP' => '...']]
     */
    public function getPromediosPorCompetencia(
        int    $idPeriodo,
        int    $idEmpleado,
        string $tipoEval
    ): array {
        $sql = "SELECT R.IDCOMPETENCIA,
                       ROUND(AVG(R.VALOR), 2) AS PROMEDIO,
                       COUNT(*)               AS TOTAL_EVAL,
                       C.NOMBRE               AS NOMBRE_COMP,
                       C.NUM_PREGUNTA
                FROM VAADINWEB.HUMRESPUESTA R
                INNER JOIN VAADINWEB.HUMCOMPETENCIA C ON R.IDCOMPETENCIA = C.IDCOMPETENCIA
                WHERE R.IDPERIODO  = $idPeriodo
                  AND R.IDEMPLEADO = $idEmpleado
                  AND R.TIPO_EVAL  = '$tipoEval'
                  AND R.CONFIRMADO = 1
                GROUP BY R.IDCOMPETENCIA, C.NOMBRE, C.NUM_PREGUNTA
                ORDER BY C.NUM_PREGUNTA";
        $res  = $this->ejecutarConsulta($sql);
        $rows = [];
        if ($res) {
            while ($r = oci_fetch_assoc($res)) {
                $rows[(int)$r['IDCOMPETENCIA']] = [
                    'PROMEDIO'    => (float)$r['PROMEDIO'],
                    'TOTAL_EVAL'  => (int)$r['TOTAL_EVAL'],
                    'NOMBRE_COMP' => mb_convert_encoding($r['NOMBRE_COMP'] ?? '', 'UTF-8', 'ISO-8859-1'),
                    'NUM_PREGUNTA'=> (int)$r['NUM_PREGUNTA'],
                ];
            }
            oci_free_statement($res);
        }
        return $rows;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // JUSTIFICACIONES
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Guarda una justificación para una respuesta extrema
     */
    public function guardarJustificacion(int $idRespuesta, string $justificacion): bool {
        $conn = $this->conectar();
        $sql  = "INSERT INTO VAADINWEB.HUMJUSTIFICACION_V2
                     (IDJUSTIFICACION, IDRESPUESTA, JUSTIFICACION)
                 VALUES (VAADINWEB.SEQ_HUMJUSTIFICACION_V2.NEXTVAL, :resp, :just)";
        $q = oci_parse($conn, $sql);
        oci_bind_by_name($q, ':resp', $idRespuesta);
        oci_bind_by_name($q, ':just', $justificacion);
        $ok = oci_execute($q, OCI_COMMIT_ON_SUCCESS);
        oci_free_statement($q);
        return $ok;
    }

    /**
     * Devuelve el IDRESPUESTA de una respuesta guardada
     * Necesario para luego guardar su justificación
     */
    public function getIdRespuesta(
        int    $idPeriodo,
        int    $idEmpleado,
        int    $idEmpleadoEval,
        int    $idCompetencia,
        string $tipoEval
    ): ?int {
        $sql = "SELECT IDRESPUESTA FROM VAADINWEB.HUMRESPUESTA
                WHERE IDPERIODO       = $idPeriodo
                  AND IDEMPLEADO      = $idEmpleado
                  AND IDEMPLEADO_EVAL = $idEmpleadoEval
                  AND IDCOMPETENCIA   = $idCompetencia
                  AND TIPO_EVAL       = '$tipoEval'
                AND ROWNUM = 1";
        $res = $this->ejecutarConsulta($sql);
        $row = $res ? oci_fetch_assoc($res) : null;
        if ($res) oci_free_statement($res);
        return $row ? (int)$row['IDRESPUESTA'] : null;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // COMPATIBILIDAD — métodos que reemplazan los hardcodeados anteriores
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Reemplaza reportModel::getDictColaborador()
     * Devuelve solo las competencias generales (preguntas 1-11)
     * [IDCOMPETENCIA => NOMBRE]
     */
    public static function getDictColaborador(): array {
        $model = new self();
        $dict  = [];
        foreach ($model->getCompetencias() as $id => $comp) {
            if ($comp['NUM_PREGUNTA'] <= 11) {
                $dict[$id] = $comp['NOMBRE'];
            }
        }
        return $dict;
    }

    /**
     * Reemplaza reportModel::getDictLiderazgo()
     * Devuelve solo las competencias de liderazgo (preguntas 12-16)
     * [IDCOMPETENCIA => NOMBRE]
     */
    public static function getDictLiderazgo(): array {
        $model = new self();
        $dict  = [];
        foreach ($model->getCompetencias() as $id => $comp) {
            if ($comp['NUM_PREGUNTA'] >= 12 && $comp['NUM_PREGUNTA'] <= 16) {
                $dict[$id] = $comp['NOMBRE'];
            }
        }
        return $dict;
    }


    /**
     * Devuelve todas las descripciones de opciones indexadas por NUM_PREGUNTA y VALOR
     * Usado para tooltips en las vistas de evaluación
     * [NUM_PREGUNTA][VALOR] => DESCRIPCION
     */
    public function getOpcionesCompetencia(): array {
        $sql = "SELECT C.NUM_PREGUNTA, OC.IDOPCION, O.VALOR, OC.DESCRIPCION
                FROM VAADINWEB.HUMOPCIONCOMPETENCIA OC
                INNER JOIN VAADINWEB.HUMCOMPETENCIA  C ON OC.IDCOMPETENCIA = C.IDCOMPETENCIA
                INNER JOIN VAADINWEB.HUMOPCIONESCALA O ON OC.IDOPCION      = O.IDOPCION
                WHERE OC.ACTIVO = 1
                ORDER BY C.NUM_PREGUNTA, O.VALOR DESC";
        $res  = $this->ejecutarConsulta($sql);
        $rows = [];
        if ($res) {
            while ($r = oci_fetch_assoc($res)) {
                $np   = (int)$r['NUM_PREGUNTA'];
                $val  = (int)$r['VALOR'];
                $desc = mb_convert_encoding($r['DESCRIPCION'] ?? '', 'UTF-8', 'ISO-8859-1');
                $rows[$np][$val] = $desc;
            }
            oci_free_statement($res);
        }
        return $rows;
    }

    /**
     * Reemplaza el array $escala hardcodeado en reportes-view.php
     * Devuelve el mapa etiqueta → valor para la escala de desempeño (IDESCALA=1)
     * ['Sobresaliente' => 5, 'Acorde' => 4, ...]
     */
    public static function getEscalaDesempeno(): array {
        $model = new self();
        return $model->getMapaEtiquetaValor(1);
    }

    /**
     * Reemplaza el array $escala para Experiencia Azul (IDESCALA=2)
     * ['Referente' => 5, 'Consistente' => 4, ...]
     */
    public static function getEscalaExperienciaAzul(): array {
        $model = new self();
        return $model->getMapaEtiquetaValor(2);
    }
}
