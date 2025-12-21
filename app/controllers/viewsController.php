<?php
    namespace app\controllers;
    use app\models\viewsModel;

    class viewsController extends viewsModel{
        /*--------------------- Controlador de vistas ---------------------*/
        public function getViewsController($views){
        if (!empty($views)) {
            $respuesta = $this->getViewsModel($views);
         }else{
            $respuesta = "login";
        }
            return $respuesta;
        }
    }

