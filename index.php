<?php
    require_once "./config/app.php";
    require_once "./autoload.php";
    require_once "./app/views/inc/session_start.php";
    
    if(isset($_GET['views'])){
        $url = filter_var($_GET['views'], FILTER_SANITIZE_STRING);
        $url = explode("/", $url);
    } else {
        $url=["login"];
    }
?>

<!DOCTYPE html>
<html lang="es" dir="ltr" data-color-theme="Blue_Theme" class="light selected" data-layout="vertical" data-boxed-layout="boxed" data-card="shadow">
<head>
    <?php require_once "./app/views/inc/head.php"; ?>
</head>
<body class="DEFAULT_THEME bg-white dark:bg-dark">
    <main>
        <!--start the project-->
        <div id="main-wrapper" class="flex pt-16">
            <?php
                use app\controllers\viewsController;
                use app\controllers\loginController;

                $loginController = new loginController();

                $viewsController = new viewsController();
                $views = $viewsController->getViewsController($url[0]) ?? "404";

                // Determinar ruta real de la vista: $views puede ser un nombre (e.g. 'reportes')
                // o una ruta absoluta devuelta por el modelo. Manejar ambos casos.
                if (is_string($views) && file_exists($views)) {
                    $rutaVista = $views; // modelo devolvió ruta absoluta
                } else {
                    $rutaVista = __DIR__ . "/app/views/content/" . $views . "-view.php";
                }

                // Si la vista no es login y no hay sesión válida, redirigir a login
                if ($url[0] !== 'login' && (empty($_SESSION['id']) || empty($_SESSION['identificacion']))) {
                    header("Location: " . APP_URL . "login/");
                    exit();
                }

                // Incluir header solo si hay sesión válida y no es login
                if (!empty($_SESSION['id']) && !empty($_SESSION['identificacion']) && $url[0] !== 'login') {
                    require_once __DIR__ . "/app/views/inc/header.php";
                }
            ?>
            <?php
    // Coloca el 'use' aquí, fuera de los bloques de código, para evitar errores de sintaxis
    use app\controllers\reportController;
?>

<div class="w-full" role="main">
    <?php
    if (file_exists($rutaVista)) {
        // Si la vista solicitada es reportes, ejecutamos el controlador primero Eduin
        if ($url[0] == "reportes") {
            $insReport = new reportController();
            // Esto buscará los datos y cargará la vista automáticamente
            $insReport->obtenerMisEvaluaciones();
        } else {
            // Para las demás páginas, carga la vista directamente
            require_once $rutaVista;
        }
    } else {
        require_once __DIR__ . "/app/views/content/404-view.php";
    }
    ?>
</div>
            <?php
            ?>
        </div>
            <?php
                require_once "./app/views/inc/footer.php";
                require_once "./app/views/inc/script.php"; 
            ?>
    </main>
</body>
</html>
