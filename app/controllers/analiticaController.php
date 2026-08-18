<?php
namespace app\controllers;
use app\models\mainModel;
use app\models\analiticaModel;
use app\models\adminModel;

class analiticaController extends mainModel {

    private analiticaModel $modelo;
    private adminModel $adminModelo;

    public function __construct() {
        $this->modelo      = new analiticaModel();
        $this->adminModelo = new adminModel();
    }

    public function panel(): void {
        if (!isset($_SESSION['esadmin']) || (int)$_SESSION['esadmin'] !== 1) {
            http_response_code(403);
            die('Acceso denegado.');
        }

        $periodoActivo = $this->adminModelo->getPeriodoActivo();
        $periodos      = $this->adminModelo->getPeriodos();

        $idPeriodoFiltro = isset($_GET['idperiodo']) ? (int)$_GET['idperiodo'] : 0;
        if ($idPeriodoFiltro) {
            foreach ($periodos as $p) {
                if ((int)$p['IDPERIODO'] === $idPeriodoFiltro) {
                    $periodoActivo = $p;
                    break;
                }
            }
        }

        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                   strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        $action  = $_GET['action'] ?? '';

        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');

            if ($action === 'getPerfilColaborador') {
                $id = (int)($_GET['idempleado'] ?? 0);
                if (!$id) { echo json_encode(['error' => 'ID requerido']); exit(); }
                echo json_encode($this->modelo->getPerfilColaborador($id, $periodoActivo));
                exit();
            }

            if ($action === 'buscarColaboradores') {
                $q = trim($_GET['q'] ?? '');
                if (mb_strlen($q) < 2) { echo json_encode([]); exit(); }
                echo json_encode($this->modelo->buscarColaboradores($q, $periodoActivo));
                exit();
            }

            if ($action === 'getAlertas') {
                echo json_encode($this->modelo->getAlertas($periodoActivo));
                exit();
            }

            echo json_encode(['error' => 'Acción no reconocida']);
            exit();
        }

        $activeTab = $_GET['tab'] ?? 'dashboard';
        $proceso   = trim($_GET['proceso'] ?? '');

        $tiposEvalOk = ['LIDER_A_COLAB', 'COLAB_A_LIDER', 'AUTO'];
        $tipoEval    = in_array($_GET['tipoeval'] ?? '', $tiposEvalOk) ? $_GET['tipoeval'] : 'LIDER_A_COLAB';

        $estadosOk = ['PENDIENTE', 'RESPONDIDO', 'APROBADO'];
        $estadoAc  = in_array($_GET['estado'] ?? '', $estadosOk) ? $_GET['estado'] : '';
        $pageAc    = max(1, (int)($_GET['page'] ?? 1));

        // V2 — filtros para tab Colaboradores
        $pageColab    = max(1, (int)($_GET['page'] ?? 1));
        $filtrosColab = [
            'nombre'  => trim($_GET['q']       ?? ''),
            'proceso' => trim($_GET['proceso'] ?? ''),
            'estado'  => trim($_GET['estcolab'] ?? ''),
        ];

        $kpis               = [];
        $evolucion          = [];
        $ranking            = [];
        $topColaboradores   = [];
        $bottomColaboradores = [];
        $resumenProcesos    = [];
        $acuerdos           = [];
        $totalAcuerdos      = 0;
        $alertas            = ['sin_autoeval' => [], 'acuerdos_vencidos' => [], 'lideres_sin_evaluar' => []];
        $colaboradores      = [];
        $totalColaboradores = 0;
        $procesosLista      = [];
        $brechas            = [];
        $expAzul            = ['kpi' => [], 'competencias' => [], 'pendientes' => [], 'evaluados' => []];
        $resumenFeedback    = ['kpis' => [], 'porLider' => []];

        switch ($activeTab) {
            case 'dashboard':
                $kpis            = $this->modelo->getKPIsGlobales($periodoActivo);
                $evolucion       = $this->modelo->getEvolucionHistorica(6);
                $resumenProcesos = $this->modelo->getResumenPorProceso($periodoActivo);
                break;
            case 'ranking':
                $ranking             = $this->modelo->getRankingCompetencias($periodoActivo, $proceso, $tipoEval);
                $topColaboradores    = $this->modelo->getTopColaboradores($periodoActivo, $tipoEval, $proceso, 10, 'DESC');
                $bottomColaboradores = $this->modelo->getTopColaboradores($periodoActivo, $tipoEval, $proceso, 10, 'ASC');
                break;
            case 'historico':
                $evolucion = $this->modelo->getEvolucionHistorica(12);
                break;
            case 'acuerdos':
                $acuerdos      = $this->modelo->getAcuerdosDetalle($periodoActivo, $estadoAc, $pageAc);
                $totalAcuerdos = $this->modelo->countAcuerdos($periodoActivo, $estadoAc);
                break;
            case 'procesos':
                $resumenProcesos = $this->modelo->getResumenPorProceso($periodoActivo);
                break;
            case 'alertas':
                $alertas = $this->modelo->getAlertas($periodoActivo);
                break;
            case 'colaboradores':
                $procesosLista      = $this->modelo->getProcesosLista();
                $totalColaboradores = $this->modelo->countListaColaboradores($periodoActivo, $filtrosColab);
                $colaboradores      = $this->modelo->getListaColaboradores($periodoActivo, $filtrosColab, $pageColab);
                break;
            case 'brechas':
                $procesosLista = $this->modelo->getProcesosLista();
                $brechas       = $this->modelo->getBrechasCompetencias($periodoActivo, $proceso);
                break;
            case 'exp_azul':
                $expAzul = $this->modelo->getResumenExpAzul($periodoActivo);
                break;
            case 'feedback':
                $resumenFeedback = $this->modelo->getResumenFeedback($periodoActivo);
                break;
        }

        require_once __DIR__ . '/../views/content/analitica-view.php';
    }
}
