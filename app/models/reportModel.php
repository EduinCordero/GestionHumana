<?php
namespace app\models;

use app\models\mainModel;

class reportModel extends mainModel {

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
        $sql = "SELECT d.IDEVACOLABORADOR, d.CONFIRMADO, d.FECHACONFIRMA, NVL(em.EMPLEADO,'Anónimo') AS EVALUADOR, NVL(em.CARGO,'N/A') AS CARGO_EVALUADOR
                FROM VAADINWEB.HUMEVALUACIONCOLABO d
                LEFT JOIN ZAYMAWEB.VST_GHEMPEMPLEADOSCARGOS em ON d.EMPLEADO_CONFIRMA = em.IDEMPLEADO
                WHERE d.EMPLEADO_ID = " . (int)$idempleado . " and em.ESPRINCIPAL = 1 ORDER BY d.IDEVACOLABORADOR DESC";
        $res = $this->ejecutarConsulta($sql);
        $rows = [];
        while ($r = oci_fetch_assoc($res)) { $rows[] = $r; }
        return $rows;
    }

    public function getEvaluacionesRecibidasLiderazgo($idempleado) {
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

    public function obtenerEvaluadores($idempleado) {
        $idEmpleadoBusca = (int)$idempleado;
        $sql = "SELECT * FROM ( SELECT C.EMPLEADO_CONFIRMA AS ID_EVALUADOR, C.FECHACONFIRMA FROM VAADINWEB.HUMEVALUACIONCOLABO C WHERE C.EMPLEADO_ID = $idEmpleadoBusca UNION ALL SELECT L.EMPLEADO_CONFIRMA AS ID_EVALUADOR, L.FECHACONFIRMA FROM VAADINWEB.HUMAUTOEVALUACIONLIDER L WHERE L.EMPLEADO_ID = $idEmpleadoBusca ORDER BY FECHACONFIRMA DESC ) T WHERE ROWNUM <= 100";
        $res = $this->ejecutarConsulta($sql);
        $evaluadorIds = [];
        $evaluadores = [];
        if ($res !== false) {
            while ($eval = oci_fetch_assoc($res)) { $evaluadorIds[$eval['ID_EVALUADOR']] = $eval; }
            oci_free_statement($res);
            if (!empty($evaluadorIds)) {
                $idsParaIn = implode(',', array_keys($evaluadorIds));
                $sqlEmpleados = "SELECT IDEMPLEADO, EMPLEADO, CARGO FROM ZAYMAWEB.VST_GHEMPEMPLEADOSCARGOS WHERE IDEMPLEADO IN ($idsParaIn)";
                $resEm = $this->ejecutarConsulta($sqlEmpleados);
                if ($resEm !== false) {
                    $datosEm = [];
                    while ($emp = oci_fetch_assoc($resEm)) { $datosEm[$emp['IDEMPLEADO']] = $emp; }
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

}
?>
