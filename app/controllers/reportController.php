<?php
namespace app\controllers;

use app\models\mainModel;
use app\models\reportModel;

class reportController extends mainModel {
    private $modelo;

    public function __construct() {
        $this->modelo = new reportModel();
    }

    public function obtenerMisEvaluaciones() {
        // 1. Verificación de Seguridad
        if (!isset($_SESSION['idempleado'])) {
            die("Error: La sesión no tiene el ID del empleado. ¿Iniciaste sesión?");
        }

        $idEmpleado = $_SESSION['idempleado']; // El usuario logueado
        $nivelCargo = $_SESSION['nivelcargo'];
        $activeTab = $_POST['activeTab'] ?? 'autoeval';
        $idTrabajo = $idEmpleado; // Por defecto veo mis datos
        $resultadosBusqueda = [];
        $searchTerm = isset($_POST['searchTerm']) ? trim($_POST['searchTerm']) : '';

        // 2. Lógica de Seguimiento (Búsqueda y Cambio de Sujeto)
        if (isset($_POST['action'])) {
            // ACCIÓN: Buscar personal
            if ($_POST['action'] == 'buscarEmpleado' && strlen($searchTerm) >= 2) {
                $resultadosBusqueda = $this->modelo->buscarPersonalEquipo($searchTerm);
                $activeTab = 'seguimiento';
            }
            
            // ACCIÓN: Ver reporte de alguien
            if ($_POST['action'] == 'verIndicadores' && isset($_POST['empleadoId'])) {
                $idTrabajo = (int)$_POST['empleadoId'];
                $activeTab = 'indicadores';
            }
        }

        // 3. Obtención de Datos (IMPORTANTE: Usar $idTrabajo)
        $idEmpCargo = $this->modelo->getCargoId($idEmpleado);
        
        // Consultas al modelo usando el ID del sujeto evaluado ($idTrabajo)
        $autoevalData = $this->modelo->getAutoEvaluacion($idTrabajo);
        $recibidasColab = $this->modelo->getEvaluacionesRecibidasColaborador($idTrabajo);
        $recibidasLider = $this->modelo->getEvaluacionesRecibidasLiderazgo($idTrabajo);

        // 4. Preparación de Datos para la Vista
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
                'lider' => $recibidasLider
            ],
            'realizadas'         => [
                'colab' => $this->modelo->getEvaluacionesRealizadasColaborador($idTrabajo),
                'lider' => $this->modelo->getEvaluacionesRealizadasLiderazgo($idTrabajo)
            ],
            'evaluadores'        => $this->modelo->obtenerEvaluadores($idTrabajo),
            
            // Estos son los que llenan las tarjetas de Promedio (Imagen 2)
            'indicadores'        => [
                'auto'           => $autoevalData,
                'lider'          => (!empty($recibidasColab)) ? $recibidasColab[0] : null,
                'lider_reciente' => (!empty($recibidasColab)) ? $recibidasColab[0] : null,
                'todas_colab'    => $recibidasColab,
                'todas_lider'    => $recibidasLider
            ],
            
            'agregados'          => [
                'desempeno'      => $this->modelo->getIndicadoresDesempeno($idTrabajo),
                'liderazgo'      => $this->modelo->getIndicadoresLiderazgo($idTrabajo),
                'dictLider'      => ["Visión Estratégica", "Desarrollo de Talento", "Toma de Decisiones", "Comunicación", "Motivación", "Innovación", "Integridad", "Eficiencia"]
            ]
        ];

        return $this->renderView($data);
    }

    private function renderView($data) {
        extract($data);
        $idTrabajo = $data['idTrabajo'];
        $recibidasColab = $data['recibidas']['colab'] ?? [];
        $recibidasLider = $data['recibidas']['lider'] ?? [];
        $dictColab = $data['dictColab'] ?? [];

        require_once "./app/views/content/reportes-view.php";
    }
}
