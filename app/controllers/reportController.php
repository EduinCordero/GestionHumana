<?php
namespace app\controllers;

use app\models\mainModel;
use app\models\reportModel;
use app\models\acuerdoModel;

class reportController extends mainModel {
    private $modelo;

    public function __construct() {
        $this->modelo = new reportModel();
    }

    public function obtenerMisEvaluaciones() {
        // ══════════════════════════════════════════════════════════════════
        // INTERCEPTAR TODAS LAS ACCIONES AJAX ANTES DE CUALQUIER OUTPUT
        // ══════════════════════════════════════════════════════════════════
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        if (isset($_GET['action']) || ($isAjax && isset($_POST['action']))) {
            $acuerdoModeloA = new acuerdoModel();
            $periodoA       = (new reportModel())->getPeriodoActivo();
            $idPeriodoA     = $periodoA ? (int)$periodoA['IDPERIODO'] : 0;

            // GET: calificaciones que el usuario dio a un evaluado
            if (isset($_GET['action']) && $_GET['action'] === 'getCalificacionesRealizadas' && isset($_GET['idEvaluado'])) {
                $idEvaluado    = (int)$_GET['idEvaluado'];
                $tipoEval      = $_GET['tipoEval'] ?? 'LIDER_A_COLAB';
                $idEval        = (int)$_SESSION['idempleado'];
                $periodoAjax   = $this->modelo->getPeriodoActivo();
                header('Content-Type: application/json');
                echo json_encode($this->modelo->getCalificacionesRealizadas($idEval, $idEvaluado, $tipoEval, $periodoAjax));
                exit();
            }

            // GET: cargar competencias con mejora + estado de asignados
            if (isset($_GET['action']) && $_GET['action'] === 'getCompetenciasMejora' && isset($_GET['idEmpleado'])) {
                $idEmpA       = (int)$_GET['idEmpleado'];
                $competencias = $acuerdoModeloA->getCompetenciasConMejora($idEmpA, $idPeriodoA);
                $dictCA       = reportModel::getDictColaborador();
                $yaAsignados  = $acuerdoModeloA->getObjetivosAsignados($idEmpA, $idPeriodoA);
                foreach ($competencias as &$comp) {
                    $numComp = $comp['NUM_COMPETENCIA'];
                    $comp['nombre']    = $dictCA[$numComp] ?? 'Competencia ' . $numComp;
                    $comp['num']       = $numComp;
                    $asignadosComp     = $yaAsignados[$numComp] ?? [];
                    $comp['objetivos'] = array_map(
                        fn($o) => [
                            'id'       => $o['IDOBJETIVO'],
                            'texto'    => $o['OBJETIVO'],
                            'asignado' => in_array((int)$o['IDOBJETIVO'], $asignadosComp),
                        ],
                        $acuerdoModeloA->getObjetivosPorCompetenciaCalificacion($numComp, $comp['CALIFICACION'])
                    );
                }
                header('Content-Type: application/json');
                echo json_encode(['competencias' => $competencias, 'ok' => true]);
                exit();
            }

            // GET: ver plan de acción de un colaborador (para líder)
            if (isset($_GET['action']) && $_GET['action'] === 'getPlanColaborador' && isset($_GET['idEmpleado'])) {
                $idEmpA    = (int)$_GET['idEmpleado'];
                $acuerdosA = $acuerdoModeloA->getAcuerdosParaLider($idEmpA, $idPeriodoA);
                header('Content-Type: application/json');
                echo json_encode(['acuerdos' => $acuerdosA, 'ok' => true]);
                exit();
            }

            // POST AJAX: asignar acuerdos (responde JSON)
            if ($isAjax && isset($_POST['action']) && $_POST['action'] === 'asignarAcuerdos' && isset($_POST['idEmpleado'])) {
                $idEmpColab   = (int)$_POST['idEmpleado'];
                $idLiderA     = (int)($_SESSION['idempleado'] ?? 0);
                $competencias = $acuerdoModeloA->getCompetenciasConMejora($idEmpColab, $idPeriodoA);
                if (is_array($competencias)) {
                    foreach ($competencias as $comp) {
                        $key = 'obj_' . $comp['NUM_COMPETENCIA'];
                        if (isset($_POST[$key]) && is_array($_POST[$key])) {
                            foreach ($_POST[$key] as $idObj) {
                                $acuerdoModeloA->asignarAcuerdo(
                                    $idEmpColab, (int)$idObj,
                                    $comp['NUM_COMPETENCIA'], $comp['CALIFICACION'],
                                    $idPeriodoA, $idLiderA
                                );
                            }
                        }
                    }
                }
                header('Content-Type: application/json');
                echo json_encode(['ok' => true]);
                exit();
            }

            // POST AJAX: colaborador guarda plan de acción (responde JSON)
            if ($isAjax && isset($_POST['action']) && $_POST['action'] === 'guardarPlanAccion' && isset($_POST['idAcuerdo'])) {
                $idAcuerdoA  = (int)$_POST['idAcuerdo'];
                $planAccionA = trim($_POST['plan_accion'] ?? '');
                $ok = false;
                if ($planAccionA) {
                    $ok = $acuerdoModeloA->guardarPlanAccion($idAcuerdoA, (int)$_SESSION['idempleado'], $planAccionA);
                }
                header('Content-Type: application/json');
                echo json_encode(['ok' => (bool)$ok]);
                exit();
            }

            // POST AJAX: líder aprueba plan de acción
            if ($isAjax && isset($_POST['action']) && $_POST['action'] === 'aprobarPlanAccion' && isset($_POST['idAcuerdo'])) {
                $idAcuerdoA   = (int)$_POST['idAcuerdo'];
                $comentarioA  = trim($_POST['comentario_lider'] ?? '');
                $idLiderA     = (int)$_SESSION['idempleado'];
                $ok = $acuerdoModeloA->aprobarPlanAccion($idAcuerdoA, $idLiderA, $comentarioA);
                header('Content-Type: application/json');
                echo json_encode(['ok' => (bool)$ok]);
                exit();
            }
        }


        // 1. Verificación de Seguridad
        if (!isset($_SESSION['idempleado'])) {
            die("Error: La sesión no tiene el ID del empleado. ¿Iniciaste sesión?");
        }

        $idEmpleado = $_SESSION['idempleado'];
        $nivelCargo = $_SESSION['nivelcargo'];
        $activeTab  = $_GET['tab'] ?? $_POST['activeTab'] ?? 'autoeval';
        $idTrabajo  = $idEmpleado;
        $resultadosBusqueda = [];
        $searchTerm = isset($_POST['searchTerm']) ? trim($_POST['searchTerm']) : '';

        // 2. Lógica de acciones POST
        if (isset($_POST['action'])) {

            // ACCIÓN: Buscar personal en seguimiento
            if ($_POST['action'] == 'buscarEmpleado' && strlen($searchTerm) >= 2) {
                $resultadosBusqueda = $this->modelo->buscarPersonalEquipo($searchTerm);
                $activeTab = 'seguimiento';
            }

            // ACCIÓN: Ver reporte de un colaborador específico
            if ($_POST['action'] == 'verIndicadores' && isset($_POST['empleadoId'])) {
                $idTrabajo = (int)$_POST['empleadoId'];
                $activeTab = 'indicadores';
            }

            // ACCIÓN: Enviar recordatorio de evaluación pendiente
            // Notifica al colaborador Y a su líder directo vía Oracle
            if ($_POST['action'] == 'enviarRecordatorio' && isset($_POST['idColaborador'])) {
                $idColab   = (int)$_POST['idColaborador'];
                $activeTab = 'seguimiento';

                try {
                    $sqlRecordatorio = "BEGIN ZAYMAWEB.PKT_NOTIFICACION_MAIL.SEND_MAIL_RECORDATORIO(:destino, :ref, :tipo); END;";
                    $conn = $this->conectar();

                    // Notificar al colaborador
                    $stmt  = oci_parse($conn, $sqlRecordatorio);
                    $tipoC = 'COLABORADOR';
                    oci_bind_by_name($stmt, ':destino', $idColab);
                    oci_bind_by_name($stmt, ':ref',     $idColab);
                    oci_bind_by_name($stmt, ':tipo',    $tipoC);
                    oci_execute($stmt);
                    oci_free_statement($stmt);

                    // Notificar al líder directo del colaborador
                    $lider = $this->modelo->getLiderDeColaborador($idColab);
                    if ($lider && is_array($lider) && !empty($lider['IDEMPLEADO'])) {
                        $idLider = (int)$lider['IDEMPLEADO'];
                        $stmtL   = oci_parse($conn, $sqlRecordatorio);
                        $tipoL   = 'LIDER';
                        oci_bind_by_name($stmtL, ':destino', $idLider);
                        oci_bind_by_name($stmtL, ':ref',     $idColab);
                        oci_bind_by_name($stmtL, ':tipo',    $tipoL);
                        oci_execute($stmtL);
                        oci_free_statement($stmtL);
                    }

                    $_SESSION['recordatorio_ok']     = true;
                    $_SESSION['recordatorio_nombre'] = $_POST['nombreColaborador'] ?? '';

                } catch (\Exception $e) {
                    error_log('Error enviarRecordatorio: ' . $e->getMessage());
                    $_SESSION['recordatorio_error'] = true;
                }
            }
        }

        // ── Acciones AJAX y POST de acuerdos ───────────────────────────────────
        $acuerdoModelo = new acuerdoModel();
        $periodoAux    = (new reportModel())->getPeriodoActivo();
        $idPeriodoAux  = $periodoAux ? (int)$periodoAux['IDPERIODO'] : 0;

        // AJAX: ver plan de un colaborador (para el líder)
        if (isset($_GET['action']) && $_GET['action'] === 'getPlanColaborador' && isset($_GET['idEmpleado'])) {
            $acuerdoModelo2 = new acuerdoModel();
            $periodoAux2    = (new reportModel())->getPeriodoActivo();
            $idPeriodoAux2  = $periodoAux2 ? (int)$periodoAux2['IDPERIODO'] : 0;
            $idEmp2         = (int)$_GET['idEmpleado'];
            $acuerdos2      = $acuerdoModelo2->getAcuerdosParaLider($idEmp2, $idPeriodoAux2);
            header('Content-Type: application/json');
            echo json_encode(['acuerdos' => $acuerdos2, 'ok' => true]);
            exit();
        }

        // AJAX: cargar competencias con mejora para modal
        if (isset($_GET['action']) && $_GET['action'] === 'getCompetenciasMejora' && isset($_GET['idEmpleado'])) {
            $idEmp = (int)$_GET['idEmpleado'];
            $competencias = $acuerdoModelo->getCompetenciasConMejora($idEmp, $idPeriodoAux);
            $dictC = reportModel::getDictColaborador();
            // Enriquecer con nombre y objetivos disponibles
            foreach ($competencias as &$comp) {
                $comp['nombre']   = $dictC[$comp['NUM_COMPETENCIA']] ?? 'Competencia ' . $comp['NUM_COMPETENCIA'];
                $comp['num']      = $comp['NUM_COMPETENCIA'];
                $comp['objetivos'] = array_map(fn($o) => ['id' => $o['IDOBJETIVO'], 'texto' => $o['OBJETIVO']],
                    $acuerdoModelo->getObjetivosPorCompetenciaCalificacion($comp['NUM_COMPETENCIA'], $comp['CALIFICACION']));
            }
            header('Content-Type: application/json');
            echo json_encode(['competencias' => $competencias]);
            exit();
        }

        // POST: asignar acuerdos
        if (isset($_POST['action']) && $_POST['action'] === 'asignarAcuerdos' && isset($_POST['idEmpleado'])) {
            $idEmpColab = (int)$_POST['idEmpleado'];
            $idLider    = (int)($_SESSION['idempleado'] ?? 0);
            // Buscar las competencias y calificaciones
            $competencias = $acuerdoModelo->getCompetenciasConMejora($idEmpColab, $idPeriodoAux);
            if (!is_array($competencias)) {
                $_SESSION['admin_error'] = 'Error al obtener competencias del colaborador.';
                echo "<script>window.location.href='" . APP_URL . "reportes/?tab=equipo';</script>";
                exit();
            }
            try {
                foreach ($competencias as $comp) {
                    $key = 'obj_' . $comp['NUM_COMPETENCIA'];
                    if (isset($_POST[$key]) && is_array($_POST[$key])) {
                        foreach ($_POST[$key] as $idObj) {
                            $acuerdoModelo->asignarAcuerdo(
                                $idEmpColab, (int)$idObj,
                                $comp['NUM_COMPETENCIA'], $comp['CALIFICACION'],
                                $idPeriodoAux, $idLider
                            );
                        }
                    }
                }
                $_SESSION['admin_ok'] = 'Acuerdos de mejora asignados correctamente.';
            } catch (\Exception $e) {
                error_log('Error asignarAcuerdos: ' . $e->getMessage());
                $_SESSION['admin_error'] = 'Error al asignar acuerdos.';
            }
            echo "<script>window.location.href='" . APP_URL . "reportes/?tab=equipo';</script>";
            exit();
        }

        // POST: colaborador guarda su plan de acción
        if (isset($_POST['action']) && $_POST['action'] === 'guardarPlanAccion' && isset($_POST['idAcuerdo'])) {
            $idAcuerdo  = (int)$_POST['idAcuerdo'];
            $planAccion = trim($_POST['plan_accion'] ?? '');
            if ($planAccion) {
                $acuerdoModelo->guardarPlanAccion($idAcuerdo, (int)$_SESSION['idempleado'], $planAccion);
            }
            echo "<script>window.location.href='" . APP_URL . "reportes/?tab=plan';</script>";
            exit();
        }





        // 3. Obtención de Datos
        $idEmpCargo    = $this->modelo->getCargoId($idEmpleado);

        // Leer VER_DETALLE_REP desde BD en cada request (no depender de sesión)
        $sqlDetalle = "SELECT NVL(VER_DETALLE_REP, 0) AS VER_DETALLE_REP
                       FROM VAADINWEB.HUMUSUARIOS
                       WHERE IDUSUARIO = " . (int)($_SESSION['id'] ?? 0);
        $resDetalle = $this->ejecutarConsulta($sqlDetalle);
        $rowDetalle = $resDetalle ? oci_fetch_assoc($resDetalle) : null;
        if ($resDetalle) oci_free_statement($resDetalle);
        $verDetalleRep = (int)($rowDetalle['VER_DETALLE_REP'] ?? 0);
        $_SESSION['ver_detalle_rep'] = $verDetalleRep;
        $periodoActivo = $this->modelo->getPeriodoActivo();
        $autoevalData   = $this->modelo->getAutoEvaluacion($idTrabajo, $periodoActivo);
        $recibidasColab = $this->modelo->getEvaluacionesRecibidasColaborador($idTrabajo, $periodoActivo);
        $recibidasLider = $this->modelo->getEvaluacionesRecibidasLiderazgo($idTrabajo, $periodoActivo);

        // 4. Preparación de datos para la vista
        $data = [
            'idTrabajo'          => $idTrabajo,
            'idEmpleado'         => $idEmpleado,
            'idEmpCargo'         => $idEmpCargo,
            'nivelCargo'         => $nivelCargo,
            'activeTab'          => $activeTab,
            'searchTerm'         => $searchTerm,
            'resultadosBusqueda' => $resultadosBusqueda,
            'dictColab'          => reportModel::getDictColaborador(),
            'dictLider'          => reportModel::getDictLiderazgo(),

            'autoeval'           => $autoevalData,
            'recibidas'          => [
                'colab' => $recibidasColab,
                'lider' => $recibidasLider,
            ],
            'realizadas'         => [
                'colab' => $this->modelo->getEvaluacionesRealizadasColaborador($idTrabajo, $periodoActivo),
                'lider' => $this->modelo->getEvaluacionesRealizadasLiderazgo($idTrabajo, $periodoActivo),
            ],
            'evaluadores'        => $this->modelo->obtenerEvaluadores($idTrabajo),
            'periodoActivo'      => $periodoActivo,
            'acuerdosColaborador'   => $idPeriodoAux ? $acuerdoModelo->getAcuerdosColaborador((int)$_SESSION['idempleado'], $idPeriodoAux) : [],
            'acuerdosColaboradorEA' => $idPeriodoAux ? $acuerdoModelo->getAcuerdosColaboradorEA((int)$_SESSION['idempleado'], $idPeriodoAux) : [],

            'agregados'          => [
                'desempeno' => $this->modelo->getIndicadoresDesempeno($idTrabajo, $periodoActivo),
                'liderazgo' => $this->modelo->getIndicadoresLiderazgo($idTrabajo, $periodoActivo),
            ],

            // Datos del panel de seguimiento (solo admin)
            'estadoEquipo'        => [],
            'resumenEquipo'       => ['total' => 0, 'completo' => 0, 'en_progreso' => 0, 'sin_iniciar' => 0],

            // Mensajes de confirmación del recordatorio
            'recordatorio_ok'     => $_SESSION['recordatorio_ok']     ?? false,
            'recordatorio_error'  => $_SESSION['recordatorio_error']  ?? false,
            'recordatorio_nombre' => $_SESSION['recordatorio_nombre'] ?? '',
        ];

    
        // Cargar equipo a cargo para líderes (NC002 en adelante)
        if (in_array($nivelCargo, ['NC002','NC003','NC004','NC005'])) {
            $equipo = $this->modelo->getEquipoACargo($idEmpleado, $nivelCargo, $periodoActivo);
            $data['estadoEquipo']  = $equipo;
            $data['resumenEquipo'] = [
                'total'       => count($equipo),
                'completo'    => count(array_filter($equipo, fn($r) => $r['ESTADO'] === 'completo')),
                'en_progreso' => count(array_filter($equipo, fn($r) => $r['ESTADO'] === 'en_progreso')),
                'sin_iniciar' => count(array_filter($equipo, fn($r) => $r['ESTADO'] === 'sin_iniciar')),
            ];
            // Colaboradores con al menos una competencia baja (1, 2 o 3)
            $data['colsConMejora'] = $idPeriodoAux
                ? $acuerdoModelo->getColaboradoresConMejora((int)$_SESSION['idempleado'], $idPeriodoAux)
                : [];
            // Estado del plan de mejora por colaborador
            $idsEquipo = array_column($equipo, 'IDEMPLEADO');
            $data['estadoPlanEquipo'] = $idPeriodoAux && !empty($idsEquipo)
                ? $acuerdoModelo->getEstadoPlanEquipo($idPeriodoAux, $idsEquipo, (int)$_SESSION['idempleado'])
                : [];

            // Promedios auto por colaborador — solo si tiene permiso de ver detalle
            $data['agregados']['promedios'] = ($verDetalleRep == 1 && !empty($idsEquipo))
                ? $this->modelo->getPromediosAutoEquipo($idsEquipo, $periodoActivo)
                : [];
        }

        // Plan de liderazgo — acuerdos P12-P16 que el director asignó al líder
        $feedbackModeloRep = new \app\models\feedbackModel();
        $esLiderFuncional  = false;
        $acuerdosLider     = [];
        if ($periodoActivo) {
            $sqlLF = "SELECT ES_LIDER_FUNCIONAL FROM VAADINWEB.HUMEMPLEADOEVAL
                      WHERE IDEMPLEADO = " . (int)$_SESSION['idempleado'] . "
                        AND ACTIVO = 1 AND ROWNUM = 1";
            $resLF = $this->ejecutarConsulta($sqlLF);
            $rowLF = $resLF ? oci_fetch_assoc($resLF) : null;
            if ($resLF) oci_free_statement($resLF);
            $esLiderFuncional = $rowLF && (int)$rowLF['ES_LIDER_FUNCIONAL'] === 1;

            if ($esLiderFuncional) {
                $acuerdosLider = $feedbackModeloRep->getAcuerdosLider(
                    (int)$_SESSION['idempleado'],
                    (int)$periodoActivo['IDPERIODO']
                );
            }
        }
        $data['acuerdosLider']    = $acuerdosLider;
        $data['esLiderFuncional'] = $esLiderFuncional;

        // Limpiar mensajes de sesión después de leerlos
        unset($_SESSION['recordatorio_ok'], $_SESSION['recordatorio_error'], $_SESSION['recordatorio_nombre']);

        return $this->renderView($data);
    }

    private function renderView($data) {
        extract($data);
        $idTrabajo        = $data['idTrabajo'];
        $recibidasColab   = $data['recibidas']['colab'] ?? [];
        $recibidasLider   = $data['recibidas']['lider'] ?? [];
        $dictColab        = $data['dictColab'] ?? [];
        $acuerdosLider    = $data['acuerdosLider']    ?? [];
        $esLiderFuncional = $data['esLiderFuncional'] ?? false;

        require_once "./app/views/content/reportes-view.php";
    }
}
