<?php
    namespace app\models;

    class viewsModel {

        /*---------- Modelo obtener vista ----------*/
        protected function getViewsModel($views) {
            $listaBlanca = [
                "home", "user", "logout", "evaluarList",
                "evaluacionLiderazgo", "autoEvaluacion",
                "subEvaluacion", "usuariosList","seguridad", "reportes"
            ];

            $basePath = dirname(__DIR__) . "/views/content/";

            if (in_array($views, $listaBlanca)) {
                $filePath = $basePath . $views . "-view.php";
                return file_exists($filePath) ? $filePath : "404";
            } 

            if ($views === "login" || $views === "index") {
                return "login";
            }

            return "404"; // Vista no encontrada
        }
    }