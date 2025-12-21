<?php
    use app\controllers\reportController;

    $reportController = new reportController();
    echo $reportController->obtenerMisEvaluaciones();
?>
