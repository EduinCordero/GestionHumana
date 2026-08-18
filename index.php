<?php
    // ── Los 'use' deben estar al inicio, nunca dentro de bloques ──────────────
    use app\controllers\viewsController;
    use app\controllers\loginController;
    use app\controllers\reportController;
    use app\controllers\adminController;
    use app\controllers\feedbackController;
    use app\controllers\evaluarController;
    use app\controllers\analiticaController;

    require_once "./config/app.php";
    require_once "./autoload.php";
    require_once "./icons.php";
    require_once "./app/views/inc/session_start.php";
    
    if(isset($_GET['views'])){
        $url = filter_var($_GET['views'], FILTER_SANITIZE_STRING);
        $url = explode("/", $url);
    } else {
        $url=["login"];
    }

    // ── Interceptar petición AJAX ANTES del DOCTYPE ───────────────────────────
    // Si la respuesta llegara después del HTML, el fetch no podría parsear JSON
    if (
        $url[0] === 'login' &&
        isset($_GET['action']) && $_GET['action'] === 'getCorreo' &&
        $_SERVER['REQUEST_METHOD'] === 'POST' &&
        isset($_POST['identificacion'])
    ) {
        header('Content-Type: application/json');

        $loginController = new loginController();
        $identificacion  = trim($_POST['identificacion']);

        $consulta = "SELECT EMAIL FROM ZAYMAWEB.GHEMPEMPLEADOS WHERE IDENTIFICACION = :identificacion AND ESTADOEMPLEADO = 1";
        $conexion  = $loginController->conectarPublico();
        $query     = oci_parse($conexion, $consulta);
        oci_bind_by_name($query, ':identificacion', $identificacion);
        oci_execute($query);
        $row = oci_fetch_assoc($query);
        oci_free_statement($query);

        if ($row && !empty($row['EMAIL'])) {
            $partes = explode('@', $row['EMAIL']);
            $usr    = $partes[0] ?? '';
            $dom    = $partes[1] ?? '';
            $mask   = mb_substr($usr, 0, 3)
                    . str_repeat('*', max(3, mb_strlen($usr) - 3))
                    . '@' . $dom;
            echo json_encode(['correo' => $mask]);
        } else {
            echo json_encode(['correo' => null]);
        }
        exit(); // Termina aquí — no se renderiza nada más
    }

    // Interceptar setEAEvaluado — guarda colaborador para Exp. Azul en sesión
    if (isset($_GET['action']) && $_GET['action'] === 'setEAEvaluado' &&
        $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idEvaluado'])) {
        $_SESSION['ea_idempleado_evaluado'] = (int)$_POST['idEvaluado'];
        header('Content-Type: application/json');
        echo json_encode(['ok' => true]);
        exit();
    }

    // Interceptar POST a registrarEvaluacion antes de enviar el HTML
    if (isset($url[0]) && $url[0] === 'registrarEvaluacion' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $insEvaluar = new evaluarController();
        $insEvaluar->registrarEvaluacion();
        exit();
    }

    // Interceptar AJAX del módulo Admin antes del HTML
    if ($url[0] === 'admin' &&
        !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest' &&
        isset($_GET['action']) && $_GET['action'] === 'buscarEmpleadoCedula') {
        $insAdmin = new adminController();
        $insAdmin->panelAdmin();
        exit();
    }

    // Interceptar AJAX de notificación individual (POST) antes del HTML
    if ($url[0] === 'admin' &&
        isset($_POST['action']) && $_POST['action'] === 'enviarNotifIndividualAjax') {
        $insAdmin = new adminController();
        $insAdmin->panelAdmin();
        exit();
    }

    // Interceptar AJAX del módulo Panel de Liderazgo antes del HTML
    if ($url[0] === 'liderPanel' &&
        !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        $insEquipo = new \app\controllers\equipoController();
        $insEquipo->panel();
        exit();
    }

    // Interceptar exportaciones CSV del Panel de Liderazgo antes del HTML
    if ($url[0] === 'liderPanel' &&
        isset($_GET['action']) && $_GET['action'] === 'exportar') {
        $insEquipo = new \app\controllers\equipoController();
        $insEquipo->panel();
        exit();
    }

    // Interceptar AJAX del módulo Analítica antes del HTML
    if ($url[0] === 'analitica' &&
        !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        $insAnalitica = new analiticaController();
        $insAnalitica->panel();
        exit();
    }

    // Interceptar TODAS las peticiones AJAX de reportes antes del HTML
    if ($url[0] === 'reportes' && (
        (isset($_GET['action']) && in_array($_GET['action'], ['getCompetenciasMejora','getPlanColaborador','getCalificacionesRealizadas'])) ||
        (isset($_POST['action']) && in_array($_POST['action'], ['asignarAcuerdos','guardarPlanAccion']) &&
         !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
    )) {
        $insReport = new reportController();
        $insReport->obtenerMisEvaluaciones();
        exit();
    }
    // ─────────────────────────────────────────────────────────────────────────
    // Interceptar AJAX del módulo Feedback antes del HTML
    if ($url[0] === 'feedback' && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        $insFeedback = new feedbackController();
        $insFeedback->panelFeedback();
        exit();
    }
?>
<!DOCTYPE html>
<html lang="es" dir="ltr" data-color-theme="Blue_Theme" class="light selected" data-layout="vertical" data-boxed-layout="boxed" data-card="shadow">
<head>
    <?php require_once "./app/views/inc/head.php"; ?>
    <style>body{visibility:hidden;}</style>
</head>
<body class="DEFAULT_THEME bg-white dark:bg-dark">
    <main>
        <!--start the project-->
        <div id="main-wrapper" class="flex pt-16 pb-14">
            <?php
                $loginController = new loginController();
                $viewsController = new viewsController();
                $views = $viewsController->getViewsController($url[0]) ?? "404";

                if (is_string($views) && file_exists($views)) {
                    $rutaVista = $views;
                } else {
                    $rutaVista = __DIR__ . "/app/views/content/" . $views . "-view.php";
                }

                if ($url[0] !== 'login' && (empty($_SESSION['id']) || empty($_SESSION['identificacion']))) {
                    header("Location: " . APP_URL . "login/");
                    exit();
                }

                if (!empty($_SESSION['id']) && !empty($_SESSION['identificacion']) && $url[0] !== 'login') {
                    require_once __DIR__ . "/app/views/inc/header.php";
                }
            ?>
            <div class="w-full" role="main">
                <?php
            if (file_exists($rutaVista)) {
                 if ($url[0] == "admin") {
                     $insAdmin = new adminController();
                     $insAdmin->panelAdmin();
                } else if ($url[0] == "liderPanel") {
                    $insEquipo = new \app\controllers\equipoController();
                    $insEquipo->panel();
                } else if ($url[0] == "analitica") {
                    $insAnalitica = new analiticaController();
                    $insAnalitica->panel();
                } else if ($url[0] == "reportes") {
                    $insReport = new reportController();
                    $insReport->obtenerMisEvaluaciones();
            } else if ($url[0] == "feedback") {
                    $insFeedback = new feedbackController();
                    $insFeedback->panelFeedback();
            } else if ($url[0] == "registrarEvaluacion") {
                    $insEvaluar = new evaluarController();
                    $insEvaluar->registrarEvaluacion();
            } else {
                require_once $rutaVista;
            }
                } else {
                    require_once __DIR__ . "/app/views/content/404-view.php";
                }
                ?>
            </div>
        </div>
        <?php
            require_once "./app/views/inc/footer.php";
            require_once "./app/views/inc/script.php"; 
        ?>
    </main>
    <script>document.addEventListener('DOMContentLoaded',function(){document.body.style.visibility='visible';});</script>
</body>
</html>
