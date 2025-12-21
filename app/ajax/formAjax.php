<?php
    require_once "../../config/app.php";
    require_once "../views/inc/session_start.php";
    require_once "../../autoload.php";

    use app\controllers\evaluarController;

    if(isset($_POST['modulo_formulario'])){

        $insForm = new evaluarController();

        if($_POST['modulo_formulario']=="registrar"){
            $response = $insForm->registrarFormularioControlador();
            echo $response;
        }
        
    }else{
        session_destroy();
        header("Location: ".APP_URL."login/");
    }