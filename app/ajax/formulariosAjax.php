<?php
    require_once "../../config/app.php";
    require_once "../views/inc/session_start.php";
    require_once "../../autoload.php";

    use app\controllers\evaluarController;

    if(isset($_POST['modulo_evaluacion'])){

        $insEvaluacion = new evaluarController();

        if($_POST['modulo_evaluacion']=="registrarEvaluacionDesempeno"){
            echo $insEvaluacion->registrarEvaluacionDesempenoControlador();
        }

        if($_POST['modulo_evaluacion']=="registrarEvaluacionLider"){
            echo $insEvaluacion->registrarEvaluacionLiderControlador();
        }

        if($_POST['modulo_evaluacion']=="registrarEvaluacionColaborador"){
            echo $insEvaluacion->registrarEvaluacionColaboradorControlador();
        }

    }else{
        session_destroy();
        header("Location: ".APP_URL."login/");
    }
?>