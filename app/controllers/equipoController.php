<?php
namespace app\controllers;

use app\models\mainModel;
use app\models\equipoModel;
use app\models\adminModel;

class equipoController extends mainModel {

    private equipoModel $modelo;
    private adminModel  $adminModelo;

    public function __construct() {
        $this->modelo      = new equipoModel();
        $this->adminModelo = new adminModel();
    }

    public function panel(): void {
        $idempleado = (int)($_SESSION['idempleado'] ?? 0);

        $esAdmin = ($_SESSION['esadmin'] ?? 0) === 1;
        if (!$this->modelo->esLiderFuncional($idempleado) && !$esAdmin) {
            http_response_code(403);
            die('Acceso denegado.');
        }

        $periodoActivo   = $this->adminModelo->getPeriodoActivo();
        $periodos        = $this->adminModelo->getPeriodos();
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

        if ($action === 'exportar') {
            $tipo = $_GET['tipo'] ?? 'equipo';
            $this->exportarCSV($tipo, $idempleado, $periodoActivo);
            return;
        }

        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');

            if ($action === 'getPerfilColaborador') {
                $idColab = (int)($_GET['idcolab'] ?? 0);
                if (!$idColab) { echo json_encode(['error' => 'ID requerido']); exit(); }
                echo json_encode($this->modelo->getPerfilColaboradorLider($idColab, $idempleado, $periodoActivo));
                exit();
            }

            echo json_encode(['error' => 'Acción no reconocida']);
            exit();
        }

        $activeTab  = $_GET['tab'] ?? 'dashboard';
        $esDirector = $this->modelo->esDirector($idempleado);

        $kpis             = [];
        $equipo           = [];
        $acuerdos         = [];
        $totalAcuerdos    = 0;
        $feedbacks        = [];
        $feedbacksLideres = [];
        $alertas          = ['sin_auto'=>[], 'sin_eval'=>[], 'sin_fb'=>[], 'ac_pendientes'=>[], 'sin_fb_2'=>[], 'ac_pendientes_2'=>[]];
        $estadoAc      = $_GET['estado'] ?? '';
        $pageAc        = max(1, (int)($_GET['page'] ?? 1));

        switch ($activeTab) {
            case 'dashboard':
                $kpis   = $this->modelo->getKPIsEquipo($idempleado, $periodoActivo);
                $equipo = $this->modelo->getEquipoDetallado($idempleado, $periodoActivo);
                break;
            case 'colaboradores':
                $equipo = $this->modelo->getEquipoDetallado($idempleado, $periodoActivo);
                break;
            case 'acuerdos':
                $estadosOk = ['PENDIENTE', 'RESPONDIDO', 'APROBADO'];
                $estadoAc  = in_array($estadoAc, $estadosOk) ? $estadoAc : '';
                $acuerdos      = $this->modelo->getAcuerdosEquipo($idempleado, $periodoActivo, $estadoAc, $pageAc);
                $totalAcuerdos = $this->modelo->countAcuerdosEquipo($idempleado, $periodoActivo, $estadoAc);
                break;
            case 'feedback':
                $feedbacks = $this->modelo->getFeedbackRealizados($idempleado, $periodoActivo);
                if ($esDirector) {
                    $feedbacksLideres = $this->modelo->getFeedbackLideresRealizados($idempleado, $periodoActivo);
                }
                break;
            case 'alertas':
                $alertas = $this->modelo->getAlertasLider($idempleado, $periodoActivo);
                break;
        }

        require_once __DIR__ . '/../views/content/equipo-view.php';
    }

    private function exportarCSV(string $tipo, int $idLider, ?array $periodo): void {
        $nombrePeriodo = $periodo ? preg_replace('/[^a-zA-Z0-9_-]/', '_', $periodo['NOMBRE']) : 'sin_periodo';

        if ($tipo === 'acuerdos') {
            $datos    = $this->modelo->getAllAcuerdosEquipo($idLider, $periodo);
            $archivo  = "acuerdos_equipo_{$nombrePeriodo}.csv";
            $cabecera = ['Colaborador', 'Cargo', 'Competencia', 'Objetivo', 'Meta', 'Plazo', 'Estado', 'Plan de accion'];
            $fila = function(array $r): array {
                return [
                    $r['COLABORADOR']  ?? '',
                    $r['CARGO']        ?? '',
                    $r['NOMBRE_COMP']  ?? ('Comp. ' . ($r['NUM_COMPETENCIA'] ?? '')),
                    $r['OBJETIVO']     ?? '',
                    $r['META']         ?? '',
                    $r['PLAZO']        ?? '',
                    $r['ESTADO']       ?? '',
                    $r['PLAN_ACCION']  ?? '',
                ];
            };
        } else {
            $datos    = $this->modelo->getEquipoDetallado($idLider, $periodo);
            $archivo  = "equipo_{$nombrePeriodo}.csv";
            $cabecera = ['Colaborador', 'Cargo', 'Proceso', 'Autoevaluacion', 'Evaluado', 'Feedback', 'Total acuerdos', 'Aprobados', 'Pendientes'];
            $fila = function(array $r): array {
                $fbEstado = ((int)$r['FIRMADO_LIDER'] && (int)$r['FIRMADO_COLAB'])
                    ? 'Completo'
                    : ((int)$r['TIENE_FEEDBACK'] ? 'Registrado' : 'Sin feedback');
                return [
                    $r['EMPLEADO']  ?? '',
                    $r['CARGO']     ?? '',
                    $r['PROCESO']   ?? '',
                    (int)$r['TIENE_AUTO']  ? 'Si' : 'Pendiente',
                    (int)$r['TIENE_EVAL']  ? 'Si' : 'Pendiente',
                    $fbEstado,
                    (int)($r['TOTAL_AC'] ?? 0),
                    (int)($r['APRO_AC']  ?? 0),
                    (int)($r['PEND_AC']  ?? 0),
                ];
            };
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $archivo . '"');
        header('Cache-Control: no-cache, no-store, must-revalidate');

        $out = fopen('php://output', 'w');
        // BOM para compatibilidad con Excel
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, $cabecera, ';');
        foreach ($datos as $r) {
            fputcsv($out, $fila($r), ';');
        }
        fclose($out);
    }
}
