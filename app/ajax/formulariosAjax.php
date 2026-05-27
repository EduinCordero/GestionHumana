<?php
    ob_start(); // Capturar cualquier output inesperado
    require_once "../../config/app.php";
    require_once "../views/inc/session_start.php";
    require_once "../../autoload.php";

    use app\controllers\evaluarController;
    use app\models\competenciaModel;

    if (!isset($_POST['modulo_evaluacion'])) {
        session_destroy();
        header("Location: " . APP_URL . "login/");
        exit;
    }

    $insEvaluacion = new evaluarController();
    $modulo        = $_POST['modulo_evaluacion'];

    // V2 metodo unificado
    if ($modulo === 'registrarEvaluacionV2') {
        $insEvaluacion->registrarEvaluacion();
        exit;
    }

    // Adaptador legacy V1 → V2
    function adaptarCamposLegacy(array $mapeo): void {
        $compModelo = new competenciaModel();
        $dictNumId  = [];
        foreach ($compModelo->getCompetencias() as $idComp => $comp) {
            $dictNumId[$comp['NUM_PREGUNTA']] = $idComp;
        }
        foreach ($mapeo as $numPost => $numPregGlobal) {
            $postKey = 'pregunta' . $numPost;
            if (!isset($_POST[$postKey]) || $_POST[$postKey] === '') continue;
            $idComp = $dictNumId[$numPregGlobal] ?? null;
            if (!$idComp) continue;
            $_POST['competencia_' . $idComp] = $_POST[$postKey];
            $justPost = 'justificacion' . $numPost;
            if (!empty($_POST[$justPost])) {
                $_POST['justificacion_' . $idComp] = $_POST[$justPost];
            }
        }
    }

    if ($modulo === 'registrarEvaluacionDesempeno') {
        $_POST['tipoEval'] = 'AUTO';
        adaptarCamposLegacy([1=>1,2=>2,3=>3,4=>4,5=>5,6=>6,7=>7,8=>8,9=>9,10=>10,11=>11]);
        $insEvaluacion->registrarEvaluacion();
        exit;
    }

    if ($modulo === 'registrarEvaluacionLider') {
        $_POST['tipoEval'] = 'COLAB_A_LIDER';
        adaptarCamposLegacy([4=>12, 5=>13, 6=>14, 7=>15, 8=>16]);
        $insEvaluacion->registrarEvaluacion();
        exit;
    }

    if ($modulo === 'registrarEvaluacionColaborador') {
        $_POST['tipoEval'] = 'LIDER_A_COLAB';
        adaptarCamposLegacy([1=>1,2=>2,3=>3,4=>4,5=>5,6=>6,7=>7,8=>8,9=>9,10=>10,11=>11]);
        $insEvaluacion->registrarEvaluacion();
        exit;
    }

    // Experiencia Azul — ya viene con tipoEval correcto desde la vista
    if (in_array($modulo, ['registrarEvaluacionDesempeno']) && isset($_POST['tipoEval']) &&
        in_array($_POST['tipoEval'], ['EXPERIENCIA_COLAB','EXPERIENCIA_LIDER'])) {
        $insEvaluacion->registrarEvaluacion();
        exit;
    }
