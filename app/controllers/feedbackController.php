<?php
namespace app\controllers;

use app\models\mainModel;
use app\models\feedbackModel;

class feedbackController extends mainModel {

    private $modelo;

    public function __construct() {
        $this->modelo = new feedbackModel();
    }

    public function panelFeedback() {

        // Capturar cualquier output previo para no corromper JSON en AJAX
        ob_start();

        // ── Seguridad: líderes funcionales y admins ──────────────────────────
        $nivelCargo = $_SESSION['nivelcargo'] ?? 'NC001';
        $esAdmin    = (int)($_SESSION['esadmin'] ?? 0);
        $idempleado = (int)$_SESSION['idempleado'];

        // Verificar si es líder funcional por HUMEMPLEADOEVAL
        $connLF  = $this->conectar();
        $sqlLF   = "SELECT ES_LIDER_FUNCIONAL FROM VAADINWEB.HUMEMPLEADOEVAL
                    WHERE IDEMPLEADO = $idempleado AND ACTIVO = 1 AND ROWNUM = 1";
        $qLF     = oci_parse($connLF, $sqlLF);
        oci_execute($qLF);
        $rowLF   = oci_fetch_assoc($qLF);
        oci_free_statement($qLF);
        $esLider    = $rowLF && (int)$rowLF['ES_LIDER_FUNCIONAL'] === 1;

        // Verificar si es director (puede hacer feedback a líderes con P12-P16)
        $sqlDir  = "SELECT ES_DIRECTOR FROM VAADINWEB.HUMEMPLEADOEVAL
                    WHERE IDEMPLEADO = $idempleado AND ACTIVO = 1 AND ROWNUM = 1";
        $qDir    = oci_parse($connLF, $sqlDir);
        oci_execute($qDir);
        $rowDir  = oci_fetch_assoc($qDir);
        oci_free_statement($qDir);
        $esDirector = $rowDir && (int)$rowDir['ES_DIRECTOR'] === 1;

        if (!$esLider && !$esAdmin) {
            echo "<script>window.location.href='" . APP_URL . "home/';</script>";
            exit();
        }

        // ── Período activo ────────────────────────────────────────────────────
        $periodoActivo = $this->modelo->getPeriodoActivo();
        if (!$periodoActivo) {
            $_SESSION['eval_warning'] = 'No hay un período de evaluación activo.';
        }

        // ── Config SMART ──────────────────────────────────────────────────────
        $config         = $this->modelo->getConfig();
        $maxObjetivos   = (int)($config['MAX_OBJETIVOS']   ?? 3);
        $feedbackActivo = (int)($config['FEEDBACK_ACTIVO'] ?? 0) === 1;

        // Si el módulo está deshabilitado y no es admin → pantalla de espera
        if (!$feedbackActivo && !$esAdmin) {
            $data = [
                'periodoActivo'  => $periodoActivo,
                'equipo'         => [],
                'nivelCargo'     => $nivelCargo,
                'idempleado'     => $idempleado,
                'maxObjetivos'   => $maxObjetivos,
                'dictComp'       => [],
                'config'         => $config,
                'esDirector'     => false,
                'lideresACargo'  => [],
                'acuerdosLider'  => [],
                'feedbackActivo' => false,
                'esAdmin'        => false,
            ];
            extract($data);
            ob_end_flush();
            require_once "./app/views/content/feedback-view.php";
            return; // No exit() — deja que index.php termine y renderice el footer/visibility script
        }

        // ── Interceptar AJAX ──────────────────────────────────────────────────
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        // También responder JSON si viene action en GET (fetch puede no enviar X-Requested-With)
        $isGetAction = isset($_GET['action']) && in_array($_GET['action'], [
            'getCalificaciones','getObjetivos','getAcuerdos',
            'getCalificacionesLider','getLideresACargo','getAcuerdosLider'
        ]);

        if (($isAjax || $isGetAction) && isset($_GET['action'])) {
            ob_end_clean(); // Limpiar buffer antes de enviar JSON
            header('Content-Type: application/json');

            // GET: calificaciones de un colaborador
            if ($_GET['action'] === 'getCalificaciones' && isset($_GET['idEmpleado'])) {
                $calsV2 = $this->modelo->getCalificacionesColaborador(
                    (int)$_GET['idEmpleado'], $idempleado, $periodoActivo, $nivelCargo
                );

                // Convertir formato V2 [IDCOMPETENCIA => datos] al formato
                // que espera el JS: ['PREGUNTAN' => etiqueta]
                $autoLegacy   = [];
                $liderLegacy  = [];
                foreach ($calsV2['auto'] ?? [] as $idComp => $datos) {
                    $n = $datos['NUM_PREGUNTA'] ?? 0;
                    if ($n) $autoLegacy['PREGUNTA' . $n] = $datos['ETIQUETA'] ?? '';
                }
                foreach ($calsV2['lider'] ?? [] as $idComp => $datos) {
                    $n = $datos['NUM_PREGUNTA'] ?? 0;
                    if ($n) $liderLegacy['PREGUNTA' . $n] = $datos['ETIQUETA'] ?? '';
                }

                echo json_encode(['auto' => $autoLegacy, 'lider' => $liderLegacy]);
                exit();
            }

            // GET: objetivos SMART por competencia y calificación
            if ($_GET['action'] === 'getObjetivos' && isset($_GET['numComp'])) {
                $objs = $this->modelo->getObjetivosSmart(
                    (int)$_GET['numComp'],
                    $_GET['calificacion'] ?? ''
                );
                echo json_encode($objs);
                exit();
            }

            // GET: acuerdos asignados — flujo=1 P1-P11, flujo=2 P12-P16
            if ($_GET['action'] === 'getAcuerdos' && isset($_GET['idEmpleado'])) {
                $flujo    = (int)($_GET['flujo'] ?? 1);
                $acuerdos = $this->modelo->getAcuerdosFeedback(
                    (int)$_GET['idEmpleado'],
                    (int)$periodoActivo['IDPERIODO'],
                    $idempleado,
                    $flujo
                );
                echo json_encode($acuerdos);
                exit();
            }

            // GET: acuerdos de liderazgo P12-P16 del líder como evaluado
            if ($_GET['action'] === 'getAcuerdosLider' && isset($_GET['idLider'])) {
                $acuerdos = $this->modelo->getAcuerdosLider(
                    (int)$_GET['idLider'],
                    (int)$periodoActivo['IDPERIODO']
                );
                echo json_encode($acuerdos);
                exit();
            }

            // ── Flujo 2: calificaciones P12-P16 promediadas del líder ─────────
            if ($_GET['action'] === 'getCalificacionesLider' && isset($_GET['idLider'])) {
                $cals = $this->modelo->getCalificacionesLider(
                    (int)$_GET['idLider'],
                    (int)$periodoActivo['IDPERIODO']
                );
                // Convertir a formato PREGUNTA12..16 para JS
                $legacy = [];
                foreach ($cals as $numPreg => $datos) {
                    $legacy['PREGUNTA' . $numPreg] = $datos['ETIQUETA'] ?? '';
                }
                echo json_encode(['cals' => $legacy, 'detalle' => $cals]);
                exit();
            }

            // ── Flujo 2: líderes bajo cargo del director ──────────────────────
            if ($_GET['action'] === 'getLideresACargo') {
                $lideres = $this->modelo->getLideresACargo($idempleado, $periodoActivo);
                echo json_encode($lideres);
                exit();
            }
        }

        if ($isAjax && isset($_POST['action'])) {
            ob_end_clean(); // Limpiar buffer antes de enviar JSON
            header('Content-Type: application/json');

            // POST: registrar feedback Flujo 2 (director → líder)
            if ($_POST['action'] === 'registrarFeedbackLider') {
                $resultado = $this->modelo->registrarFeedbackLider(
                    (int)$_POST['idLider'],
                    $idempleado,
                    (int)$periodoActivo['IDPERIODO'],
                    trim($_POST['fechaFeedback'] ?? date('d/m/Y')),
                    trim($_POST['observacion'] ?? '')
                );
                echo json_encode($resultado);
                exit();
            }

            // POST: registrar feedback
            if ($_POST['action'] === 'registrarFeedback') {
                $ok = $this->modelo->registrarFeedback(
                    (int)$_POST['idEmpleado'],
                    $idempleado,
                    (int)$periodoActivo['IDPERIODO'],
                    trim($_POST['fechaFeedback'] ?? date('d/m/Y')),
                    trim($_POST['observacion'] ?? '')
                );
                // Obtener el IDFEEDBACK recién creado
                $acuerdos = $this->modelo->getAcuerdosFeedback(
                    (int)$_POST['idEmpleado'],
                    (int)$periodoActivo['IDPERIODO'],
                    $idempleado
                );
                echo json_encode(['ok' => $ok]);
                exit();
            }

            // POST: asignar objetivo SMART
            if ($_POST['action'] === 'asignarObjetivo') {
                // Detectar flujo por NUM_COMPETENCIA
                $numCompPost = (int)($_POST['numComp'] ?? 0);
                $flujoPost   = $numCompPost >= 12 ? 2 : 1;
                // Verificar máximo según flujo
                $existentes = $this->modelo->getAcuerdosFeedback(
                    (int)$_POST['idEmpleado'],
                    (int)$periodoActivo['IDPERIODO'],
                    $idempleado,
                    $flujoPost
                );
                if (count($existentes) >= $maxObjetivos) {
                    echo json_encode(['ok' => false, 'msg' => "Máximo $maxObjetivos objetivos asignados."]);
                    exit();
                }

                $ok = $this->modelo->asignarObjetivoSmart(
                    (int)$_POST['idEmpleado'],
                    (int)$_POST['idObjetivo'],
                    (int)$_POST['numComp'],
                    trim($_POST['calificacion'] ?? ''),
                    (int)$periodoActivo['IDPERIODO'],
                    $idempleado,
                    (int)($_POST['idFeedback'] ?? 0),
                    trim($_POST['apoyo']      ?? ''),
                    trim($_POST['indicador']  ?? ''),
                    trim($_POST['meta']       ?? ''),
                    trim($_POST['plazo']      ?? ''),
                    trim($_POST['evidencia']  ?? ''),
                    trim($_POST['compromiso'] ?? '')
                );
                echo json_encode(['ok' => $ok, 'msg' => $ok ? 'Objetivo asignado.' : 'Error al asignar.']);
                exit();
            }
            // POST: colaborador firma el recibido del feedback
            if ($_POST['action'] === 'actualizarSeguimiento') {
                $ok = $this->modelo->actualizarSeguimiento(
                    (int)$_POST['idAcuerdo'],
                    $idempleado,
                    $_POST['estado']     ?? 'PENDIENTE',
                    $_POST['comentario'] ?? ''
                );
                echo json_encode(['ok' => $ok]);
                exit();
            }

            // POST: firma del líder en Flujo 2
            if ($_POST['action'] === 'firmarFeedbackLider') {
                $idLiderFirma = (int)($_POST['idLider']   ?? 0);
                $cedula       = trim($_POST['cedula']     ?? '');
                $password     = trim($_POST['password']   ?? '');
                if (!$idLiderFirma || !$cedula || !$password) {
                    echo json_encode(['ok' => false, 'msg' => 'Datos incompletos.']);
                    exit();
                }
                $resultado = $this->modelo->firmarFeedbackLider(
                    $idLiderFirma, $idempleado,
                    (int)$periodoActivo['IDPERIODO'],
                    $cedula, $password
                );
                echo json_encode($resultado);
                exit();
            }

            if ($_POST['action'] === 'firmarFeedback') {
                $idEmpleadoFirma = (int)($_POST['idEmpleado'] ?? 0);
                $cedula          = trim($_POST['cedula']   ?? '');
                $password        = trim($_POST['password'] ?? '');

                if (!$idEmpleadoFirma || !$cedula || !$password) {
                    echo json_encode(['ok' => false, 'msg' => 'Datos incompletos.']);
                    exit();
                }

                $resultado = $this->modelo->firmarFeedback(
                    $idEmpleadoFirma,
                    $idempleado,
                    (int)$periodoActivo['IDPERIODO'],
                    $cedula,
                    $password
                );
                echo json_encode($resultado);
                exit();
            }
        }

        // ── Equipo del líder ──────────────────────────────────────────────────
        $equipo = [];
        if ($periodoActivo) {
            $equipo = $this->modelo->getEquipoConEstado($idempleado, $nivelCargo, $periodoActivo);
        }

        // ── Diccionario de competencias ───────────────────────────────────────
        $dictComp = \app\models\reportModel::getDictColaborador();

        // Flujo 2 — líderes bajo cargo (solo para directores)
        $lideresACargo = [];
        if ($esDirector && $periodoActivo) {
            $lideresACargo = $this->modelo->getLideresACargo($idempleado, $periodoActivo);
        }

        $data = [
            'periodoActivo' => $periodoActivo,
            'equipo'        => $equipo,
            'nivelCargo'    => $nivelCargo,
            'idempleado'    => $idempleado,
            'maxObjetivos'  => $maxObjetivos,
            'dictComp'      => $dictComp,
            'config'        => $config,
            'esDirector'    => $esDirector,
            'lideresACargo' => $lideresACargo,
        ];

        // Plan de mejora liderazgo — objetivos P12-P16 que el líder recibió
        $acuerdosLider = [];
        if ($periodoActivo) {
            $acuerdosLider = $this->modelo->getAcuerdosLider($idempleado, (int)$periodoActivo['IDPERIODO']);
        }

        $data['acuerdosLider'] = $acuerdosLider;

        extract($data);
        ob_end_flush(); // Enviar buffer acumulado con la vista
        require_once "./app/views/content/feedback-view.php";
    }
}
