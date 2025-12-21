<?php
    namespace app\controllers;
    use app\models\mainModel;

    class reportController extends mainModel {

        /**
         * Obtiene las evaluaciones del usuario logueado según su rol
         * Retorna HTML con tablas de datos
         */
public function obtenerMisEvaluaciones() {
    $IDEMPLEADO = $_SESSION['idempleado'];
    $NIVELCARGO = $_SESSION['nivelcargo'];
    
    // 1. 🔑 CLAVE: Capturar la pestaña activa del POST, si no existe, usa 'autoeval' como defecto.
    $activeTab = isset($_POST['activeTab']) ? $_POST['activeTab'] : 'autoeval';

    // Obtener el id del cargo (IDEMPCARGO) para controlar accesos por cargo específico
    $consultaCargo = "SELECT IDEMPCARGO FROM ZAYMAWEB.VST_GHEMPEMPLEADOSCARGOS WHERE IDEMPLEADO = $IDEMPLEADO";
    $resultCargo = $this->ejecutarConsulta($consultaCargo);
    $rowCargo = oci_fetch_assoc($resultCargo);
    $IDEMPCARGO = $rowCargo ? $rowCargo['IDEMPCARGO'] : null;

    ob_start();
    ?>
    <div class="w-full flex justify-center py-6 px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-7xl">
            <div class="card shadow-lg rounded-lg">
                <div class="card-body p-6 md:p-10"
                    <div class="text-center mb-10">
                        <h2 class="text-4xl md:text-5xl font-bold text-center mb-3">Mis Reportes de Evaluación</h2>
                        <p class="text-gray-600 dark:text-gray-400 text-base md:text-lg max-w-2xl mx-auto">Gestión y seguimiento de evaluaciones de desempeño y liderazgo</p>
                    </div>

                    <div class="mb-8 border-b-2 border-border dark:border-darkborder flex flex-wrap gap-2 justify-center md:justify-start">
                        
                        <?php 
                        // Función auxiliar para determinar las clases de botón
                        $getTabClasses = function($tabName, $activeTab) {
                            $baseClasses = 'tab-btn px-4 md:px-6 py-3 border-b-4 font-semibold text-sm md:text-base transition-all duration-200 rounded-t-lg';
                            if ($tabName === $activeTab) {
                                return $baseClasses . ' border-primary text-primary'; // Clase activa
                            }
                            return $baseClasses . ' border-transparent text-gray-600 dark:text-gray-400 hover:text-primary dark:hover:text-primary hover:bg-lightprimary dark:hover:bg-darkprimary';
                        };
                        
                        // Función auxiliar para determinar las clases de contenido
                        $getContentClasses = function($tabName, $activeTab) {
                            return ($tabName === $activeTab) ? 'tab-content pt-6' : 'tab-content hidden pt-6';
                        };
                        ?>
                        
                        <button class="<?php echo $getTabClasses('autoeval', $activeTab); ?>" data-tab="autoeval" onclick="window.switchTab('autoeval', this); return false;">
                            Mi Autoevaluación
                        </button>
                        
                        <button class="<?php echo $getTabClasses('recibidas', $activeTab); ?>" data-tab="recibidas" onclick="window.switchTab('recibidas', this); return false;">
                            Evaluaciones Recibidas
                        </button>
                        
                        <button class="<?php echo $getTabClasses('realizadas', $activeTab); ?>" data-tab="realizadas" onclick="window.switchTab('realizadas', this); return false;">
                            Evaluaciones Realizadas
                        </button>
                        
                        <?php if ($IDEMPCARGO == 60 or 118) { ?>
                        <button class="<?php echo $getTabClasses('seguimiento', $activeTab); ?>" data-tab="seguimiento" onclick="window.switchTab('seguimiento', this); return false;">
                            Seguimiento
                        </button>
                        <?php } ?>
                        
                        <button class="<?php echo $getTabClasses('indicadores', $activeTab); ?>" data-tab="indicadores" onclick="window.switchTab('indicadores', this); return false;">
                            Indicadores
                        </button>
                        
                    </div>

                    <div id="tab-autoeval" class="<?php echo $getContentClasses('autoeval', $activeTab); ?>">
                        <?php $this->mostrarAutoEvaluacion($IDEMPLEADO); ?>
                    </div>

                    <div id="tab-recibidas" class="<?php echo $getContentClasses('recibidas', $activeTab); ?>">
                        <?php $this->mostrarEvaluacionesRecibidas($IDEMPLEADO, $NIVELCARGO); ?>
                    </div>

                    <div id="tab-realizadas" class="<?php echo $getContentClasses('realizadas', $activeTab); ?>">
                        <?php $this->mostrarEvaluacionesRealizadas($IDEMPLEADO, $NIVELCARGO); ?>
                    </div>

                    <?php if ($IDEMPCARGO == 60 or 118) { ?>
                    <div id="tab-seguimiento" class="<?php echo $getContentClasses('seguimiento', $activeTab); ?>">
                        <?php $this->mostrarSeguimiento($IDEMPLEADO, $NIVELCARGO); ?>
                    </div>
                    <?php } ?>

                    <div id="tab-indicadores" class="<?php echo $getContentClasses('indicadores', $activeTab); ?>">
                        <?php $this->mostrarPromedioColaborador($IDEMPLEADO); ?> 
                        <?php $this->mostrarIndicadoresLider($IDEMPLEADO); ?>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <?php
    return ob_get_clean();
}

        /**
         * Muestra la autoevaluación del usuario
         */
        private function mostrarAutoEvaluacion($IDEMPLEADO) {
            $consulta = "SELECT 
                IDAUTOEVALUACION,
                FECHAREALIZA,
                PREGUNTA1, PREGUNTA2, PREGUNTA3, PREGUNTA4, PREGUNTA5,
                PREGUNTA6, PREGUNTA7, PREGUNTA8, PREGUNTA9, PREGUNTA10, PREGUNTA11,
                CONFIRMADO
            FROM VAADINWEB.HUMAUTOEVALUACION 
            WHERE EMPLEADO_ID = $IDEMPLEADO
            ORDER BY FECHAREALIZA DESC";

            $resultado = $this->ejecutarConsulta($consulta);
            $autoEval = oci_fetch_assoc($resultado);

            if (!$autoEval) {
                echo "<div class='p-4 bg-lightinfo dark:bg-darkinfo text-info rounded-md'>No hay autoevaluación registrada.</div>";
                return;
            }

            // Mapeo de preguntas a sus competencias
            $competencias = [
                1 => 'Calidez Humana y Servicio con Propósito',
                2 => 'Liderazgo e Integridad en la Acción',
                3 => 'Competitividad, Innovación y Adaptabilidad',
                4 => 'Comunicación Asertiva y Sentido de Pertenencia',
                5 => 'Compromiso con la calidad',
                6 => 'Compromiso institucional y cumplimiento de normas internas',
                7 => 'Participación y formación continua',
                8 => 'Gestión de Seguridad y Salud en el Trabajo (SST)',
                9 => 'Gestión de Relaciones Interpersonales',
                10 => 'Eficacia en la Ejecución de Responsabilidades y Contribución a los Resultados',
                11 => 'Gestión Eficiente del Tiempo y los Recursos',
            ];

            echo "<div class='mb-6'>";
            if ($autoEval['FECHAREALIZA']) {
                echo "<h3 class='text-lg font-semibold mb-4'>Fecha de realización: " . date('d/m/Y', strtotime($autoEval['FECHAREALIZA'])) . "</h3>";
            }
            echo "<div class='overflow-x-auto rounded-lg'>";
            echo "<table class='w-full divide-y divide-border dark:divide-darkborder'>";
            echo "<thead class='bg-lightprimary dark:bg-darkprimary'>";
            echo "<tr>";
            echo "<th class='p-4 text-start text-base font-semibold text-link dark:text-white'>#</th>";
            echo "<th class='p-4 text-start text-base font-semibold text-link dark:text-white'>Competencia</th>";
            echo "<th class='p-4 text-start text-base font-semibold text-link dark:text-white'>Respuesta</th>";
            echo "</tr>";
            echo "</thead>";
            echo "<tbody class='divide-y divide-border dark:divide-darkborder'>";

            for ($i = 1; $i <= 11; $i++) {
                $pregunta = $autoEval['PREGUNTA' . $i];
                $competencia = $competencias[$i];
                echo "<tr>";
                echo "<td class='p-4 whitespace-nowrap font-semibold text-sm'>#$i</td>";
                echo "<td class='p-4 text-sm'>" . htmlspecialchars($competencia) . "</td>";
                echo "<td class='p-4'>";
                echo "<span class='badge badge-md bg-lightinfo dark:bg-darkinfo text-info dark:text-info'>" . htmlspecialchars($pregunta) . "</span>";
                echo "</td>";
                echo "</tr>";
            }

            echo "</tbody>";
            echo "</table>";
            echo "</div>";
            echo "</div>";
        }

        /**
         * Muestra evaluaciones que recibió el usuario (de su jefe o líderes)
         * Versión anónima - solo muestra estado, sin detalles de respuestas
         */
        private function mostrarEvaluacionesRecibidas($IDEMPLEADO, $NIVELCARGO) {
            // Consulta: evaluaciones de colaborador (de jefe directo) - traer evaluador y cargo, pero sin respuestas
            $consultaDesempeno = "SELECT 
                d.IDEVACOLABORADOR,
                d.CONFIRMADO,
                d.FECHACONFIRMA,
                NVL(em.EMPLEADO, 'Anónimo') AS EVALUADOR,
                NVL(em.CARGO, 'N/A') AS CARGO_EVALUADOR
            FROM VAADINWEB.HUMEVALUACIONCOLABO d
            LEFT JOIN ZAYMAWEB.VST_GHEMPEMPLEADOSCARGOS em ON d.EMPLEADO_CONFIRMA = em.IDEMPLEADO
            WHERE d.EMPLEADO_ID = $IDEMPLEADO and em.ESPRINCIPAL = 1 
            ORDER BY d.IDEVACOLABORADOR DESC";

            $resultDesempeno = $this->ejecutarConsulta($consultaDesempeno);
            $evaluacionesDesempeno = [];
            while ($eval = oci_fetch_assoc($resultDesempeno)) {
                $evaluacionesDesempeno[] = $eval;
            }

            // Consulta: evaluaciones de liderazgo (de jefes/líderes) - traer evaluador y cargo, pero sin respuestas
            $consultaLiderazgo = "SELECT 
                hl.IDAUTOLIDER,
                hl.CONFIRMADO,
                hl.FECHACONFIRMA,
                NVL(em.EMPLEADO, 'Anónimo') AS EVALUADOR,
                NVL(em.CARGO, 'N/A') AS CARGO_EVALUADOR
            FROM VAADINWEB.HUMAUTOEVALUACIONLIDER hl
            LEFT JOIN ZAYMAWEB.VST_GHEMPEMPLEADOSCARGOS em ON hl.EMPLEADO_CONFIRMA = em.IDEMPLEADO
            WHERE hl.EMPLEADO_ID = $IDEMPLEADO
            ORDER BY hl.FECHACONFIRMA DESC";

            $resultLiderazgo = $this->ejecutarConsulta($consultaLiderazgo);
            $evaluacionesLiderazgo = [];
            while ($eval = oci_fetch_assoc($resultLiderazgo)) {
                $evaluacionesLiderazgo[] = $eval;
            }

            // Mostrar conteo de registros
            $countDesempeno = count($evaluacionesDesempeno);
            $countLiderazgo = count($evaluacionesLiderazgo);
            echo "<div class='mb-4 text-sm text-gray-500 text-center md:text-left'>Evaluaciones - Colaborador: " . $countDesempeno . " | Liderazgo: " . $countLiderazgo . "</div>";
            
            echo "<div class='mb-10'>";
            echo "<h3 class='text-2xl font-bold mb-6 pb-3 border-b-2 border-primary'>Evaluaciones de Colaborador Recibidas</h3>";
            
            if (count($evaluacionesDesempeno) > 0) {
                echo "<div class='overflow-x-auto rounded-lg mb-6'>";
                echo "<table class='w-full divide-y divide-border dark:divide-darkborder'>";
                echo "<thead class='bg-lightprimary dark:bg-darkprimary'>";
                echo "<tr>";
                echo "<th class='p-4 text-start text-sm md:text-base font-semibold text-link dark:text-white'>Colaborador</th>";
                echo "<th class='p-4 text-start text-sm md:text-base font-semibold text-link dark:text-white'>Cargo</th>";
                echo "<th class='p-4 text-start text-sm md:text-base font-semibold text-link dark:text-white'>Fecha</th>";
                echo "<th class='p-4 text-start text-sm md:text-base font-semibold text-link dark:text-white'>Estado</th>";
                echo "</tr>";
                echo "</thead>";
                echo "<tbody class='divide-y divide-border dark:divide-darkborder'>";

                foreach ($evaluacionesDesempeno as $eval) {
                    echo "<tr class='hover:bg-lightgray dark:hover:bg-darkgray'>";
                    echo "<td class='p-4 whitespace-nowrap text-sm'>" . htmlspecialchars(mb_convert_encoding($eval['EVALUADOR'], 'UTF-8', 'ISO-8859-1')) . "</td>";
                    echo "<td class='p-4 whitespace-nowrap text-sm'>" . htmlspecialchars(mb_convert_encoding($eval['CARGO_EVALUADOR'], 'UTF-8', 'ISO-8859-1')) . "</td>";
                    echo "<td class='p-4 whitespace-nowrap text-xs md:text-sm'>" . ($eval['FECHACONFIRMA'] ? date('d/m/Y H:i', strtotime($eval['FECHACONFIRMA'])) : 'Pendiente') . "</td>";
                    echo "<td class='p-4 whitespace-nowrap'>";
                    if ($eval['CONFIRMADO'] == 1) {
                        echo "<span class='badge badge-sm bg-lightsuccess text-success'>✓ Completado</span>";
                    } else {
                        echo "<span class='badge badge-sm bg-lightwarning text-warning'>⏳ Pendiente</span>";
                    }
                    echo "</td>";
                    echo "</tr>";
                }

                echo "</tbody>";
                echo "</table>";
                echo "</div>";
            } else {
                echo "<p class='text-gray-500 text-center py-4'>No hay evaluaciones de colaborador registradas.</p>";
            }

            echo "</div>";

            echo "<div class='mb-10'>";
            echo "<h3 class='text-2xl font-bold mb-6 pb-3 border-b-2 border-primary'>Evaluaciones de Liderazgo Recibidas</h3>";

            if (count($evaluacionesLiderazgo) > 0) {
                echo "<div class='overflow-x-auto rounded-lg'>";
                echo "<table class='w-full divide-y divide-border dark:divide-darkborder'>";
                echo "<thead class='bg-lightprimary dark:bg-darkprimary'>";
                echo "<tr>";
                echo "<th class='p-4 text-start text-sm md:text-base font-semibold text-link dark:text-white'>Colaborador</th>";
                echo "<th class='p-4 text-start text-sm md:text-base font-semibold text-link dark:text-white'>Cargo</th>";
                echo "<th class='p-4 text-start text-sm md:text-base font-semibold text-link dark:text-white'>Fecha</th>";
                echo "<th class='p-4 text-start text-sm md:text-base font-semibold text-link dark:text-white'>Estado</th>";
                echo "</tr>";
                echo "</thead>";
                echo "<tbody class='divide-y divide-border dark:divide-darkborder'>";

                foreach ($evaluacionesLiderazgo as $eval) {
                    echo "<tr class='hover:bg-lightgray dark:hover:bg-darkgray'>";
                    echo "<td class='p-4 whitespace-nowrap text-sm'>" . htmlspecialchars(mb_convert_encoding($eval['EVALUADOR'], 'UTF-8', 'ISO-8859-1')) . "</td>";
                    echo "<td class='p-4 whitespace-nowrap text-sm'>" . htmlspecialchars(mb_convert_encoding($eval['CARGO_EVALUADOR'], 'UTF-8', 'ISO-8859-1')) . "</td>";
                    echo "<td class='p-4 whitespace-nowrap text-xs md:text-sm'>" . ($eval['FECHACONFIRMA'] ? date('d/m/Y H:i', strtotime($eval['FECHACONFIRMA'])) : 'Pendiente') . "</td>";
                    echo "<td class='p-4 whitespace-nowrap'>";
                    if ($eval['CONFIRMADO'] == 1) {
                        echo "<span class='badge badge-sm bg-lightsuccess text-success'>✓ Completado</span>";
                    } else {
                        echo "<span class='badge badge-sm bg-lightwarning text-warning'>⏳ Pendiente</span>";
                    }
                    echo "</td>";
                    echo "</tr>";
                }

                echo "</tbody>";
                echo "</table>";
                echo "</div>";
            } else {
                echo "<p class='text-gray-500 text-center py-4'>No hay evaluaciones de liderazgo registradas.</p>";
            }

            echo "</div>";
        }

        /**
         * Muestra evaluaciones que realizó el usuario (para líderes y colaboradores)
         */
        private function mostrarEvaluacionesRealizadas($IDEMPLEADO, $NIVELCARGO) {
            // Ahora todos (líderes y colaboradores) pueden ver sus evaluaciones realizadas
            
            // Mapeo de competencias para evaluaciones de colaborador (11 preguntas)
            $competenciasColaborador = [
                1 => "Calidad del Trabajo",
                2 => "Cumplimiento de Plazos",
                3 => "Responsabilidad",
                4 => "Profesionalismo",
                5 => "Iniciativa",
                6 => "Trabajo en Equipo",
                7 => "Adaptabilidad",
                8 => "Orientación al Cliente",
                9 => "Cumplimiento Normativo",
                10 => "Mejora Continua",
                11 => "Excelencia"
            ];
            
            // Mapeo de competencias para evaluaciones de liderazgo (8 preguntas)
            $competenciasLiderazgo = [
                1 => "Visión Estratégica",
                2 => "Desarrollo de Talento",
                3 => "Toma de Decisiones",
                4 => "Comunicación",
                5 => "Motivación",
                6 => "Innovación",
                7 => "Integridad",
                8 => "Eficiencia"
            ];

            // Evaluaciones de colaborador realizadas
            echo "<div class='mb-10'>";
            echo "<h3 class='text-2xl font-bold mb-6 pb-3 border-b-2 border-primary'>Evaluaciones de Colaborador que Realicé</h3>";

            $consultaDesempenoRealizado = "SELECT 
                d.IDEVACOLABORADOR,
                d.PREGUNTA1, d.PREGUNTA2, d.PREGUNTA3, d.PREGUNTA4, d.PREGUNTA5, 
                d.PREGUNTA6, d.PREGUNTA7, d.PREGUNTA8, d.PREGUNTA9, d.PREGUNTA10, d.PREGUNTA11,
                em.EMPLEADO AS EVALUADO,
                em.CARGO AS CARGO_EVALUADO,
                d.CONFIRMADO,
                d.FECHACONFIRMA
            FROM VAADINWEB.HUMEVALUACIONCOLABO d
            INNER JOIN ZAYMAWEB.VST_GHEMPEMPLEADOSCARGOS em ON d.EMPLEADO_ID = em.IDEMPLEADO
            WHERE d.EMPLEADO_CONFIRMA = $IDEMPLEADO
            ORDER BY d.IDEVACOLABORADOR DESC";

            $resultDesempenoRealizado = $this->ejecutarConsulta($consultaDesempenoRealizado);
            $evaluacionesDesempenoRealizadas = [];
            while ($eval = oci_fetch_assoc($resultDesempenoRealizado)) {
                $evaluacionesDesempenoRealizadas[] = $eval;
            }

            $countDesempenoRealizadas = count($evaluacionesDesempenoRealizadas);
            echo "<div class='mb-2 text-sm text-gray-500'>Evaluaciones de colaborador realizadas: " . $countDesempenoRealizadas . "</div>";
            if ($countDesempenoRealizadas > 0) {
                echo "<div class='overflow-x-auto rounded-lg mb-6'>";
                echo "<table class='w-full divide-y divide-border dark:divide-darkborder'>";
                echo "<thead class='bg-lightprimary dark:bg-darkprimary'>";
                echo "<tr>";
                echo "<th class='p-4 text-start text-sm md:text-base font-semibold text-link dark:text-white'>Colaborador</th>";
                echo "<th class='p-4 text-start text-sm md:text-base font-semibold text-link dark:text-white'>Cargo</th>";
                echo "<th class='p-4 text-start text-sm md:text-base font-semibold text-link dark:text-white'>Fecha</th>";
                echo "<th class='p-4 text-start text-sm md:text-base font-semibold text-link dark:text-white'>Evaluaciones por Competencia</th>";
                echo "<th class='p-4 text-start text-sm md:text-base font-semibold text-link dark:text-white'>Estado</th>";
                echo "</tr>";
                echo "</thead>";
                echo "<tbody class='divide-y divide-border dark:divide-darkborder'>";

                foreach ($evaluacionesDesempenoRealizadas as $eval) {
                    echo "<tr class='hover:bg-lightgray dark:hover:bg-darkgray'>";
                    echo "<td class='p-4 whitespace-nowrap text-sm'>" . htmlspecialchars(mb_convert_encoding($eval['EVALUADO'], 'UTF-8', 'ISO-8859-1')) . "</td>";
                    echo "<td class='p-4 whitespace-nowrap text-xs md:text-sm'>" . htmlspecialchars(mb_convert_encoding($eval['CARGO_EVALUADO'], 'UTF-8', 'ISO-8859-1')) . "</td>";
                    echo "<td class='p-4 whitespace-nowrap text-xs md:text-sm'>" . ($eval['FECHACONFIRMA'] ? date('d/m/Y', strtotime($eval['FECHACONFIRMA'])) : 'N/A') . "</td>";
                    echo "<td class='p-4'>";
                    echo "<div class='flex flex-col gap-2'>";
                    for ($i = 1; $i <= 11; $i++) {
                        $pregunta = $eval['PREGUNTA' . $i];
                        $competencia = $competenciasColaborador[$i];
                        echo "<div class='flex items-center gap-2'>";
                        echo "<span class='text-xs md:text-sm font-medium min-w-fit'>" . htmlspecialchars($competencia) . ":</span>";
                        echo "<span class='badge badge-sm bg-lightinfo text-info'>" . htmlspecialchars($pregunta) . "</span>";
                        echo "</div>";
                    }
                    echo "</div>";
                    echo "</td>";
                    echo "<td class='p-4 whitespace-nowrap'>";
                    if ($eval['CONFIRMADO'] == 1) {
                        echo "<span class='badge badge-sm bg-lightsuccess text-success'>Completado</span>";
                    } else {
                        echo "<span class='badge badge-sm bg-lightwarning text-warning'>Pendiente</span>";
                    }
                    echo "</td>";
                    echo "</tr>";
                }

                echo "</tbody>";
                echo "</table>";
                echo "</div>";
            } else {
                echo "<p class='text-gray-500 text-center py-4'>No hay evaluaciones de colaborador realizadas.</p>";
            }

            echo "</div>";

            // Evaluaciones de liderazgo realizadas
            echo "<div class='mb-10'>";
            echo "<h3 class='text-2xl font-bold mb-6 pb-3 border-b-2 border-primary'>Evaluaciones de Liderazgo que Realicé</h3>";

            $consultaLiderazgoRealizado = "SELECT 
                hl.IDAUTOLIDER,
                hl.PREGUNTA1, hl.PREGUNTA2, hl.PREGUNTA3, hl.PREGUNTA4,
                hl.PREGUNTA5, hl.PREGUNTA6, hl.PREGUNTA7, hl.PREGUNTA8,
                em.EMPLEADO AS EVALUADO,
                em.CARGO AS CARGO_EVALUADO,
                hl.CONFIRMADO,
                hl.FECHACONFIRMA
            FROM VAADINWEB.HUMAUTOEVALUACIONLIDER hl
            INNER JOIN ZAYMAWEB.VST_GHEMPEMPLEADOSCARGOS em ON hl.EMPLEADO_ID = em.IDEMPLEADO
            WHERE hl.EMPLEADO_CONFIRMA = $IDEMPLEADO
            ORDER BY hl.FECHACONFIRMA DESC";

            $resultLiderazgoRealizado = $this->ejecutarConsulta($consultaLiderazgoRealizado);
            $evaluacionesLiderazgoRealizadas = [];
            while ($eval = oci_fetch_assoc($resultLiderazgoRealizado)) {
                $evaluacionesLiderazgoRealizadas[] = $eval;
            }

            $countLiderazgoRealizadas = count($evaluacionesLiderazgoRealizadas);
            echo "<div class='mb-2 text-sm text-gray-500'>Evaluaciones de liderazgo realizadas: " . $countLiderazgoRealizadas . "</div>";
            if ($countLiderazgoRealizadas > 0) {
                echo "<div class='overflow-x-auto rounded-lg'>";
                echo "<table class='w-full divide-y divide-border dark:divide-darkborder'>";
                echo "<thead class='bg-lightprimary dark:bg-darkprimary'>";
                echo "<tr>";
                echo "<th class='p-4 text-start text-sm md:text-base font-semibold text-link dark:text-white'>Colaborador</th>";
                echo "<th class='p-4 text-start text-sm md:text-base font-semibold text-link dark:text-white'>Cargo</th>";
                echo "<th class='p-4 text-start text-sm md:text-base font-semibold text-link dark:text-white'>Fecha</th>";
                echo "<th class='p-4 text-start text-sm md:text-base font-semibold text-link dark:text-white'>Evaluaciones por Competencia</th>";
                echo "<th class='p-4 text-start text-sm md:text-base font-semibold text-link dark:text-white'>Estado</th>";
                echo "</tr>";
                echo "</thead>";
                echo "<tbody class='divide-y divide-border dark:divide-darkborder'>";

                foreach ($evaluacionesLiderazgoRealizadas as $eval) {
                    echo "<tr class='hover:bg-lightgray dark:hover:bg-darkgray'>";
                    echo "<td class='p-4 whitespace-nowrap text-sm'>" . htmlspecialchars(mb_convert_encoding($eval['EVALUADO'], 'UTF-8', 'ISO-8859-1')) . "</td>";
                    echo "<td class='p-4 whitespace-nowrap text-xs md:text-sm'>" . htmlspecialchars(mb_convert_encoding($eval['CARGO_EVALUADO'], 'UTF-8', 'ISO-8859-1')) . "</td>";
                    echo "<td class='p-4 whitespace-nowrap text-xs md:text-sm'>" . ($eval['FECHACONFIRMA'] ? date('d/m/Y', strtotime($eval['FECHACONFIRMA'])) : 'N/A') . "</td>";
                    echo "<td class='p-4'>";
                    echo "<div class='flex flex-col gap-2'>";
                    for ($i = 1; $i <= 8; $i++) {
                        $pregunta = $eval['PREGUNTA' . $i];
                        $competencia = $competenciasLiderazgo[$i];
                        echo "<div class='flex items-center gap-2'>";
                        echo "<span class='text-xs md:text-sm font-medium min-w-fit'>" . htmlspecialchars($competencia) . ":</span>";
                        echo "<span class='badge badge-sm bg-lightinfo text-info'>" . htmlspecialchars($pregunta) . "</span>";
                        echo "</div>";
                    }
                    echo "</div>";
                    echo "</td>";
                    echo "<td class='p-4 whitespace-nowrap'>";
                    if ($eval['CONFIRMADO'] == 1) {
                        echo "<span class='badge badge-sm bg-lightsuccess text-success'>Completado</span>";
                    } else {
                        echo "<span class='badge badge-sm bg-lightwarning text-warning'>Pendiente</span>";
                    }
                    echo "</td>";
                    echo "</tr>";
                }

                echo "</tbody>";
                echo "</table>";
                echo "</div>";
            } else {
                echo "<p class='text-gray-500 text-center py-4'>No hay evaluaciones de liderazgo realizadas.</p>";
            }

            echo "</div>";
        }

/**
 * Muestra el promedio de evaluaciones para colaboradores
 * Calcula el promedio entre la Autoevaluación (11 preguntas) 
 * y la Evaluación de Colaborador Recibida (del líder, 11 preguntas).
 * * USO: Consulta optimizada para Oracle < 12c (usa ROWNUM).
 */
private function mostrarPromedioColaborador($IDEMPLEADO) {
    // Escala de conversión de texto a número (asumiendo que las respuestas son textuales)
    $escala = [
        'Sobresaliente' => 5,
        'Acorde' => 4,
        'Aceptable' => 3,
        'Necesita Mejorar' => 2,
        'Insuficiente' => 1
    ];

    // Mapeo de competencias (debe coincidir con las 11 preguntas de ambas tablas)
    $competencias = [
        1 => 'Calidez Humana y Servicio con Propósito',
        2 => 'Liderazgo e Integridad en la Acción',
        3 => 'Competitividad, Innovación y Adaptabilidad',
        4 => 'Comunicación Asertiva y Sentido de Pertenencia',
        5 => 'Compromiso con la calidad',
        6 => 'Compromiso institucional y cumplimiento de normas internas',
        7 => 'Participación y formación continua',
        8 => 'Gestión de Seguridad y Salud en el Trabajo (SST)',
        9 => 'Gestión de Relaciones Interpersonales',
        10 => 'Eficacia en la Ejecución de Responsabilidades y Contribución a los Resultados',
        11 => 'Gestión Eficiente del Tiempo y los Recursos',
    ];

    $IDEMPLEADO = (int)$IDEMPLEADO;
    $promedios = [];
    $hasData = false;
    
    // --- 1. OBTENER AUTOEVALUACIÓN MÁS RECIENTE (11 PREGUNTAS) ---
    // Usando ROWNUM = 1 para obtener la fila más reciente tras el ORDER BY
    $sqlAuto = "SELECT * FROM (
                    SELECT PREGUNTA1, PREGUNTA2, PREGUNTA3, PREGUNTA4, PREGUNTA5, 
                           PREGUNTA6, PREGUNTA7, PREGUNTA8, PREGUNTA9, PREGUNTA10, PREGUNTA11,
                           FECHAREALIZA
                    FROM VAADINWEB.HUMAUTOEVALUACION 
                    WHERE EMPLEADO_ID = " . $IDEMPLEADO . "
                    ORDER BY FECHAREALIZA DESC
                ) WHERE ROWNUM = 1";
    
    // Usamos la función de consulta existente (ejecutarConsulta)
    $resultAuto = $this->ejecutarConsulta($sqlAuto);
    $autoEval = oci_fetch_assoc($resultAuto) ?: null;

    // --- 2. OBTENER EVALUACIÓN RECIBIDA DEL LÍDER MÁS RECIENTE (11 PREGUNTAS) ---
    // La evaluación que recibes de tu líder se guarda en HUMEVALUACIONCOLABO
    $sqlLider = "SELECT * FROM (
                    SELECT PREGUNTA1, PREGUNTA2, PREGUNTA3, PREGUNTA4, PREGUNTA5, 
                           PREGUNTA6, PREGUNTA7, PREGUNTA8, PREGUNTA9, PREGUNTA10, PREGUNTA11,
                           FECHACONFIRMA
                    FROM VAADINWEB.HUMEVALUACIONCOLABO 
                    WHERE EMPLEADO_ID = " . $IDEMPLEADO . "
                    ORDER BY FECHACONFIRMA DESC
                ) WHERE ROWNUM = 1";

    // Usamos la función de consulta existente (ejecutarConsulta)
    $resultLider = $this->ejecutarConsulta($sqlLider);
    $liderEval = oci_fetch_assoc($resultLider) ?: null;

    if ($autoEval && $liderEval) {
        $hasData = true;
        // --- 3. CALCULAR PROMEDIO Y BRECHA ---
        for ($i = 1; $i <= 11; $i++) {
            $preguntaKey = 'PREGUNTA' . $i;
            
            // Convertir respuestas a valor numérico (1-5)
            // Usamos trim() para asegurar que no hay espacios extra
            $autoVal = $escala[trim($autoEval[$preguntaKey])] ?? 0;
            $liderVal = $escala[trim($liderEval[$preguntaKey])] ?? 0;

            // Calcular el promedio y la brecha
            $promedio = ($autoVal > 0 && $liderVal > 0) ? round(($autoVal + $liderVal) / 2, 2) : 'N/A';
            $brecha = $autoVal - $liderVal;
            
            $promedios[] = [
                'competencia' => $competencias[$i],
                'auto' => htmlspecialchars($autoEval[$preguntaKey]),
                'lider' => htmlspecialchars($liderEval[$preguntaKey]),
                'promedio' => $promedio,
                'brecha' => $brecha,
            ];
        }
    }
    
    // -----------------------------------------------------
    // --- 4. GENERAR VISTA DE LA TABLA COMPARATIVA ---
    // -----------------------------------------------------

    echo "<div class='mb-6'>";
    echo "<h3 class='text-lg font-semibold mb-4'>Promedio de Evaluación de Desempeño vs. Percepción Propia</h3>";
    
    if ($hasData) {
        $fechaAuto = isset($autoEval['FECHAREALIZA']) ? date('d/m/Y', strtotime($autoEval['FECHAREALIZA'])) : 'N/A';
        $fechaLider = isset($liderEval['FECHACONFIRMA']) ? date('d/m/Y', strtotime($liderEval['FECHACONFIRMA'])) : 'N/A';
        
        echo "<p class='text-gray-600 mb-4'>Comparación de la autoevaluación (realizada en **{$fechaAuto}**) contra la evaluación recibida del líder (realizada en **{$fechaLider}**).</p>";
        
        echo "<div class='overflow-x-auto rounded-lg'>";
        echo "<table class='w-full divide-y divide-border dark:divide-darkborder'>";
        echo "<thead class='bg-lightprimary dark:bg-darkprimary'>";
        echo "<tr>";
        echo "<th class='p-4 text-start text-sm font-semibold text-link dark:text-white'>Competencia</th>";
        echo "<th class='p-4 text-center text-sm font-semibold text-link dark:text-white'>Tu Autoevaluación</th>";
        echo "<th class='p-4 text-center text-sm font-semibold text-link dark:text-white'>Evaluación del Líder</th>";
        echo "<th class='p-4 text-center text-sm font-semibold text-link dark:text-white'>Promedio</th>";
        echo "<th class='p-4 text-center text-sm font-semibold text-link dark:text-white'>Brecha (Auto - Líder)</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody class='divide-y divide-border dark:divide-darkborder'>";

        foreach ($promedios as $item) {
            // Lógica para colorear la Brecha
            $brechaClass = 'text-gray-600'; // Neutral
            if ($item['brecha'] > 0) {
                $brechaClass = 'text-danger font-bold'; // Autoevaluación más alta (Brecha Positiva)
            } elseif ($item['brecha'] < 0) {
                $brechaClass = 'text-success font-bold'; // Evaluación del Líder más alta (Brecha Negativa)
            }

            echo "<tr class='hover:bg-lightgray dark:hover:bg-darkgray'>";
            echo "<td class='p-4 text-sm font-medium'>".$item['competencia']."</td>";
            echo "<td class='p-4 text-center'><span class='badge badge-sm bg-lightinfo text-info'>".$item['auto']."</span></td>";
            echo "<td class='p-4 text-center'><span class='badge badge-sm bg-lightinfo text-info'>".$item['lider']."</span></td>";
            echo "<td class='p-4 text-center text-base font-bold text-primary'>".$item['promedio']."</td>";
            echo "<td class='p-4 text-center text-base ".$brechaClass."'>".$item['brecha']."</td>";
            echo "</tr>";
        }

        echo "</tbody>";
        echo "</table>";
        echo "</div>"; // end overflow
        
        echo "<p class='mt-4 text-sm text-gray-500'>*La Brecha indica la diferencia de puntaje: Positivo (+) significa que te calificaste más alto que tu líder; Negativo (-) significa que tu líder te calificó más alto.</p>";

    } else {
        echo "<div class='p-4 bg-lightinfo dark:bg-darkinfo text-info rounded-md'>⚠️ No se encontró la autoevaluación o la evaluación recibida del líder más reciente. Asegúrate de que ambas han sido completadas.</div>";
    }

    echo "</div>"; // end mb-6
}

        /**
         * Muestra indicadores agregados para líderes y colaboradores
         * Estadísticas de todas las evaluaciones recibidas
         */
        private function mostrarIndicadoresLider($IDEMPLEADO) {
            // Conversión de texto a número
            $escala = [
                'Sobresaliente' => 5,
                'Acorde' => 4,
                'Aceptable' => 3,
                'Necesita Mejorar' => 2,
                'Insuficiente' => 1
            ];

            // Obtener todas las evaluaciones de colaborador recibidas (11 preguntas)
            $sqlDesempeno = "SELECT PREGUNTA1, PREGUNTA2, PREGUNTA3, PREGUNTA4, PREGUNTA5, PREGUNTA6, PREGUNTA7, PREGUNTA8, PREGUNTA9, PREGUNTA10, PREGUNTA11
                            FROM VAADINWEB.HUMEVALUACIONCOLABO 
                            WHERE EMPLEADO_ID = " . (int)$IDEMPLEADO;
            
            $resultDesempeno = $this->ejecutarConsulta($sqlDesempeno);
            $evaluacionesDesempeno = [];
            while ($row = oci_fetch_assoc($resultDesempeno)) {
                $evaluacionesDesempeno[] = $row;
            }

            // Obtener todas las evaluaciones de liderazgo recibidas (8 preguntas)
                $sqlLiderazgo = "SELECT PREGUNTA1, PREGUNTA2, PREGUNTA3, PREGUNTA4, PREGUNTA5,
                            PREGUNTA6, PREGUNTA7, PREGUNTA8
                        FROM VAADINWEB.HUMAUTOEVALUACIONLIDER 
                        WHERE EMPLEADO_ID = " . (int)$IDEMPLEADO;
            
            $resultLiderazgo = $this->ejecutarConsulta($sqlLiderazgo);
            $evaluacionesLiderazgo = [];
            while ($row = oci_fetch_assoc($resultLiderazgo)) {
                $evaluacionesLiderazgo[] = $row;
            }

            echo "<div class='mb-6'>";
            echo "<h3 class='text-lg font-semibold mb-4'>Indicadores de Desempeño</h3>";
            echo "<p class='text-gray-600 mb-4'>Agregación anónima de evaluaciones recibidas</p>";
            echo "<div class='mb-2 text-sm text-gray-500'>Registros - Colaborador: " . count($evaluacionesDesempeno) . " | Liderazgo: " . count($evaluacionesLiderazgo) . "</div>";

            // Variables para almacenar promedios totales
            $sumaPromediosDesempeno = 0;
            $sumaPromediosLiderazgo = 0;
            $conteoPromediosDesempeno = 0;
            $conteoPromediosLiderazgo = 0;

            // Sección Colaborador
            if (!empty($evaluacionesDesempeno)) {
                echo "<div class='mb-8'>";
                echo "<h4 class='font-semibold mb-3'>Evaluaciones obtenidas como Colaborador (11 preguntas)</h4>";
                echo "<div class='overflow-x-auto'>";
                echo "<table class='min-w-full border-collapse border border-border dark:border-darkborder'>";
                echo "<thead class='bg-lightprimary dark:bg-darkprimary'>";
                echo "<tr>";
                echo "<th class='p-4 text-left border border-border dark:border-darkborder'>Pregunta</th>";
                echo "<th class='p-4 text-center border border-border dark:border-darkborder'>Sobresaliente</th>";
                echo "<th class='p-4 text-center border border-border dark:border-darkborder'>Acorde</th>";
                echo "<th class='p-4 text-center border border-border dark:border-darkborder'>Aceptable</th>";
                echo "<th class='p-4 text-center border border-border dark:border-darkborder'>Necesita Mejorar</th>";
                echo "<th class='p-4 text-center border border-border dark:border-darkborder'>Insuficiente</th>";
                echo "<th class='p-4 text-center border border-border dark:border-darkborder'>Promedio</th>";
                echo "</tr>";
                echo "</thead>";
                echo "<tbody>";

                $preguntasDesempeno = ["Calidad del Trabajo", "Cumplimiento de Plazos", "Responsabilidad", "Profesionalismo", "Iniciativa", "Trabajo en Equipo", "Adaptabilidad", "Orientación al Cliente", "Cumplimiento Normativo", "Mejora Continua", "Excelencia"];

                for ($i = 1; $i <= 11; $i++) {
                    $preguntaKey = 'PREGUNTA' . $i;
                    $frecuencia = ['Sobresaliente' => 0, 'Acorde' => 0, 'Aceptable' => 0, 'Necesita Mejorar' => 0, 'Insuficiente' => 0];
                    $sumaNotas = 0;

                    foreach ($evaluacionesDesempeno as $eval) {
                        $respuesta = $eval[$preguntaKey] ?? 'N/A';
                        if (isset($frecuencia[$respuesta])) {
                            $frecuencia[$respuesta]++;
                            $sumaNotas += $escala[$respuesta];
                        }
                    }

                    $totalEvaluaciones = count($evaluacionesDesempeno);
                    $promedio = $totalEvaluaciones > 0 ? round($sumaNotas / $totalEvaluaciones, 2) : 0;
                    
                    // Sumar para promedio total
                    if ($promedio > 0) {//aquí se valida que exista una respuesta de lo contrario no suma nada
                        $sumaPromediosDesempeno += $promedio;
                        $conteoPromediosDesempeno++;
                    }

                    echo "<tr>";
                    echo "<td class='p-4 border border-border dark:border-darkborder font-medium'>" . $preguntasDesempeno[$i-1] . "</td>";
                    echo "<td class='p-4 text-center border border-border dark:border-darkborder'>" . $frecuencia['Sobresaliente'] . "</td>";
                    echo "<td class='p-4 text-center border border-border dark:border-darkborder'>" . $frecuencia['Acorde'] . "</td>";
                    echo "<td class='p-4 text-center border border-border dark:border-darkborder'>" . $frecuencia['Aceptable'] . "</td>";
                    echo "<td class='p-4 text-center border border-border dark:border-darkborder'>" . $frecuencia['Necesita Mejorar'] . "</td>";
                    echo "<td class='p-4 text-center border border-border dark:border-darkborder'>" . $frecuencia['Insuficiente'] . "</td>";
                    echo "<td class='p-4 text-center border border-border dark:border-darkborder font-semibold'><span class='badge badge-md bg-primary text-white'>" . $promedio . "/5.0</span></td>";
                    echo "</tr>";
                }

                echo "</tbody>";
                echo "</table>";
                echo "</div>";
                echo "</div>";
            }

            // Sección Liderazgo
            if (!empty($evaluacionesLiderazgo)) {
                echo "<div>";
                echo "<h4 class='font-semibold mb-3'>Evaluaciones obtenidas como Líder (8 preguntas)</h4>";
                echo "<div class='overflow-x-auto'>";
                echo "<table class='min-w-full border-collapse border border-border dark:border-darkborder'>";
                echo "<thead class='bg-lightprimary dark:bg-darkprimary'>";
                echo "<tr>";
                echo "<th class='p-4 text-left border border-border dark:border-darkborder'>Pregunta</th>";
                echo "<th class='p-4 text-center border border-border dark:border-darkborder'>Sobresaliente</th>";
                echo "<th class='p-4 text-center border border-border dark:border-darkborder'>Acorde</th>";
                echo "<th class='p-4 text-center border border-border dark:border-darkborder'>Aceptable</th>";
                echo "<th class='p-4 text-center border border-border dark:border-darkborder'>Necesita Mejorar</th>";
                echo "<th class='p-4 text-center border border-border dark:border-darkborder'>Insuficiente</th>";
                echo "<th class='p-4 text-center border border-border dark:border-darkborder'>Promedio</th>";
                echo "</tr>";
                echo "</thead>";
                echo "<tbody>";

                $preguntasLiderazgo = ["Visión Estratégica", "Desarrollo de Talento", "Toma de Decisiones", "Comunicación", "Motivación", "Innovación", "Integridad", "Eficiencia"];

                for ($i = 1; $i <= 8; $i++) {
                    $preguntaKey = 'PREGUNTA' . $i;
                    $frecuencia = ['Sobresaliente' => 0, 'Acorde' => 0, 'Aceptable' => 0, 'Necesita Mejorar' => 0, 'Insuficiente' => 0];
                    $sumaNotas = 0;

                    foreach ($evaluacionesLiderazgo as $eval) {
                        $respuesta = $eval[$preguntaKey] ?? 'N/A';
                        if (isset($frecuencia[$respuesta])) {
                            $frecuencia[$respuesta]++;
                            $sumaNotas += $escala[$respuesta];
                        }
                    }

                    $totalEvaluaciones = count($evaluacionesLiderazgo);
                    $promedio = $totalEvaluaciones > 0 ? round($sumaNotas / $totalEvaluaciones, 2) : 0;
                    
                    // Sumar para promedio total
                    if ($promedio > 0) { //aquí se valida que exista una respuesta de lo contrario no suma nada
                        $sumaPromediosLiderazgo += $promedio;
                        $conteoPromediosLiderazgo++;
                    }

                    echo "<tr>";
                    echo "<td class='p-4 border border-border dark:border-darkborder font-medium'>" . $preguntasLiderazgo[$i-1] . "</td>";
                    echo "<td class='p-4 text-center border border-border dark:border-darkborder'>" . $frecuencia['Sobresaliente'] . "</td>";
                    echo "<td class='p-4 text-center border border-border dark:border-darkborder'>" . $frecuencia['Acorde'] . "</td>";
                    echo "<td class='p-4 text-center border border-border dark:border-darkborder'>" . $frecuencia['Aceptable'] . "</td>";
                    echo "<td class='p-4 text-center border border-border dark:border-darkborder'>" . $frecuencia['Necesita Mejorar'] . "</td>";
                    echo "<td class='p-4 text-center border border-border dark:border-darkborder'>" . $frecuencia['Insuficiente'] . "</td>";
                    echo "<td class='p-4 text-center border border-border dark:border-darkborder font-semibold'><span class='badge badge-md bg-primary text-white'>" . $promedio . "/5.0</span></td>";
                    echo "</tr>";
                }

                echo "</tbody>";
                echo "</table>";
                echo "</div>";
                echo "</div>";
            }

            // Mostrar promedio general total
            $promedioTotalDesempeno = $conteoPromediosDesempeno > 0 ? round($sumaPromediosDesempeno / $conteoPromediosDesempeno, 2) : 0;
            $promedioTotalLiderazgo = $conteoPromediosLiderazgo > 0 ? round($sumaPromediosLiderazgo / $conteoPromediosLiderazgo, 2) : 0;
            $promedioGeneralTotal = 0;
            $divisor = 0;
            
            if ($promedioTotalDesempeno > 0) {
                $promedioGeneralTotal += $promedioTotalDesempeno;
                $divisor++;
            }
            if ($promedioTotalLiderazgo > 0) {
                $promedioGeneralTotal += $promedioTotalLiderazgo;
                $divisor++;
            }
            
            if ($divisor > 0) {
                $promedioGeneralTotal = round($promedioGeneralTotal / $divisor, 2);
            }

            echo "<div class='mt-8 p-6 bg-lightprimary dark:bg-darkprimary rounded-lg border-2 border-primary'>";
            echo "<h4 class='font-bold text-lg mb-4'>Resumen General</h4>";
            if (!empty($evaluacionesDesempeno)) {
                echo "<div class='mb-3'><span class='font-semibold'>Promedio Evaluaciones de Colaborador:</span> <span class='badge badge-lg bg-primary text-white'>" . $promedioTotalDesempeno . "/5.0</span></div>";
            }
            if (!empty($evaluacionesLiderazgo)) {
                echo "<div class='mb-3'><span class='font-semibold'>Promedio Evaluaciones de Liderazgo:</span> <span class='badge badge-lg bg-primary text-white'>" . $promedioTotalLiderazgo . "/5.0</span></div>";
            }
            if ($promedioGeneralTotal > 0) {
                echo "<div class='pt-3 border-t-2 border-primary'><span class='font-bold text-lg'>Promedio Total General:</span> <span class='badge badge-lg bg-success text-white text-lg'>" . $promedioGeneralTotal . "/5.0</span></div>";
            }
            echo "</div>";

            if (empty($evaluacionesDesempeno) && empty($evaluacionesLiderazgo)) {
                echo "<p class='text-gray-500'>No hay evaluaciones registradas.</p>";
            }

            echo "</div>";
        }

/**
 * Obtiene la lista unificada de personas (Líderes o Colaboradores) que evaluaron 
 * al empleado buscado, consultando tanto la tabla de Colaboradores como la de Líderes.
 * Usa EMPLEADO_CONFIRMA como el ID del evaluador en ambas.
 */
private function obtenerEvaluadores($IDEMPLEADO) {
    // ID del empleado a buscar
    $idEmpleadoBusca = (int)$IDEMPLEADO;

    // Consulta que une los evaluadores de ambas tablas de evaluaciones
    // Se usa UNION ALL para incluir duplicados si la misma persona evaluó en ambos contextos (Líder y Colaborador)
    $sql = "SELECT * FROM (
                -- 1. Evaluadores registrados en la tabla de Colaboradores (HUMEVALUACIONCOLABO - 11 Preguntas)
                SELECT 
                    C.EMPLEADO_CONFIRMA AS ID_EVALUADOR,
                    C.FECHACONFIRMA
                FROM VAADINWEB.HUMEVALUACIONCOLABO C
                WHERE C.EMPLEADO_ID = $idEmpleadoBusca

                UNION ALL

                -- 2. Evaluadores registrados en la tabla de Líderes (HUMAUTOEVALUACIONLIDER - 8 Preguntas)
                SELECT 
                    L.EMPLEADO_CONFIRMA AS ID_EVALUADOR,
                    L.FECHACONFIRMA
                FROM VAADINWEB.HUMAUTOEVALUACIONLIDER L
                WHERE L.EMPLEADO_ID = $idEmpleadoBusca

                ORDER BY FECHACONFIRMA DESC
            ) T
            WHERE ROWNUM <= 100"; 

    $resultUnion = $this->ejecutarConsulta($sql);
    $evaluadores = [];
    
    if ($resultUnion !== false) { 
        $evaluadorIds = [];
        
        // 1. Recoger IDs únicos de evaluadores de la unión
        while ($eval = oci_fetch_assoc($resultUnion)) {
            // Recogemos el ID del evaluador y su fecha de evaluación
            $evaluadorIds[$eval['ID_EVALUADOR']] = $eval;
        }

        // Liberar recursos de la primera consulta
        oci_free_statement($resultUnion);

        // 2. Consulta para obtener EMPLEADO y CARGO basado en los IDs únicos
        if (!empty($evaluadorIds)) {
            // Creamos una lista de IDs para el IN
            $idsParaIn = implode(',', array_keys($evaluadorIds));
            
            $sqlEmpleados = "SELECT IDEMPLEADO, EMPLEADO, CARGO 
                             FROM ZAYMAWEB.VST_GHEMPEMPLEADOSCARGOS 
                             WHERE IDEMPLEADO IN ($idsParaIn)";

            $resultEmpleados = $this->ejecutarConsulta($sqlEmpleados);

            if ($resultEmpleados !== false) {
                $datosEmpleados = [];
                while ($emp = oci_fetch_assoc($resultEmpleados)) {
                    $datosEmpleados[$emp['IDEMPLEADO']] = $emp;
                }
                
                // 3. Unir los datos del empleado con la fecha de confirmación
                foreach ($evaluadorIds as $id => $data) {
                    if (isset($datosEmpleados[$id])) {
                        $evaluadores[] = array_merge($datosEmpleados[$id], ['FECHACONFIRMA' => $data['FECHACONFIRMA']]);
                    }
                }
                oci_free_statement($resultEmpleados);
            }
        }
    }
    
    return $evaluadores;
}

        /**
 * Muestra pestaña Seguimiento para administrador/director (IDEMPCARGO = 60)
 * Permite buscar empleados y ver sus indicadores
 */
private function mostrarSeguimiento($IDEMPLEADO, $NIVELCARGO) {
    // 1. Persistencia de la Pestaña
    // Capturamos el ID de empleado buscado o seleccionado de los datos POST
    $empleadoIdBuscado = isset($_POST['empleadoId']) ? (int)$_POST['empleadoId'] : null;
    $searchTerm = isset($_POST['searchTerm']) ? trim($_POST['searchTerm']) : '';

    echo "<div class='max-w-7xl mx-auto'>";
    
    // Formulario de búsqueda
    echo "<div class='mb-8'>";
    echo "<h3 class='text-2xl font-bold mb-6 pb-3 border-b-2 border-primary'>Búsqueda de Empleados</h3>";
    echo "<div class='bg-white dark:bg-darkcard rounded-lg p-6 shadow-sm'>";
    echo "<form method='POST' class='flex flex-col md:flex-row gap-4'>";
    echo "<input type='hidden' name='action' value='buscarEmpleado'>";
    echo "<input type='hidden' name='activeTab' value='seguimiento'>"; // Clave para la persistencia
    echo "<input type='text' name='searchTerm' placeholder='Buscar por nombre o identificación...' value='" . htmlspecialchars($searchTerm) . "' ";
    echo "class='flex-1 px-4 py-2 border border-border dark:border-darkborder rounded-md focus:outline-none focus:ring-2 focus:ring-primary'>";
    echo "<button type='submit' class='px-6 py-2 bg-primary text-white rounded-md hover:bg-opacity-90 transition'>";
    echo "Buscar</button>";
    echo "</form>";
    echo "</div>";
    echo "</div>";

    // Resultados de búsqueda (si existe POST y action es buscarEmpleado)
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['searchTerm']) && $_POST['action'] == 'buscarEmpleado') {
        
        if (strlen($searchTerm) >= 2) {
            $searchTermUpper = strtoupper($searchTerm);
            
            //  LÍMITE DE 10 RESULTADOS APLICADO AQUÍ, SOLO EN LA BÚSQUEDA DEL EMPLEADO
            $consultaBusqueda = "SELECT * FROM (
                SELECT 
                    e.IDEMPLEADO,
                    e.EMPLEADO,
                    e.IDENTIFICACION,
                    e.CARGO
                FROM ZAYMAWEB.VST_GHEMPEMPLEADOSCARGOS e
                WHERE UPPER(e.EMPLEADO) LIKE '%$searchTermUpper%' 
                    OR e.IDENTIFICACION LIKE '%$searchTermUpper%' 
                ORDER BY e.EMPLEADO ASC
            ) WHERE ROWNUM <= 10"; // Se limita a 10 filas

            $resultBusqueda = $this->ejecutarConsulta($consultaBusqueda);
            
            if (!$resultBusqueda) {
                echo "<div class='p-4 bg-danger text-white rounded-md'>🚨 Error de Consulta: La búsqueda falló.</div>";
            } else {
                $empleados = [];
                while ($emp = oci_fetch_assoc($resultBusqueda)) {
                    $empleados[] = $emp;
                }

                echo "<div class='mb-8'>";
                echo "<h3 class='text-2xl font-bold mb-6 pb-3 border-b-2 border-primary'>Resultados de Búsqueda</h3>";
                
                if (count($empleados) > 0) {
                    echo "<div class='overflow-x-auto rounded-lg'>";
                    echo "<table class='w-full divide-y divide-border dark:divide-darkborder'>";
                    echo "<thead class='bg-lightprimary dark:bg-darkprimary'>";
                    echo "<tr>";
                    echo "<th class='p-4 text-start text-sm md:text-base font-semibold text-link dark:text-white'>Empleado</th>";
                    echo "<th class='p-4 text-start text-sm md:text-base font-semibold text-link dark:text-white'>Identificación</th>";
                    echo "<th class='p-4 text-start text-sm md:text-base font-semibold text-link dark:text-white'>Cargo</th>";
                    echo "<th class='p-4 text-start text-sm md:text-base font-semibold text-link dark:text-white'>Acciones</th>";
                    echo "</tr>";
                    echo "</thead>";
                    echo "<tbody class='divide-y divide-border dark:divide-darkborder'>";

                    foreach ($empleados as $emp) {
                        echo "<tr class='hover:bg-lightgray dark:hover:bg-darkgray'>";
                        echo "<td class='p-4 text-sm'>" . htmlspecialchars(mb_convert_encoding($emp['EMPLEADO'], 'UTF-8', 'ISO-8859-1')) . "</td>";
                        echo "<td class='p-4 text-sm'>" . htmlspecialchars($emp['IDENTIFICACION']) . "</td>";
                        echo "<td class='p-4 text-sm'>" . htmlspecialchars(mb_convert_encoding($emp['CARGO'], 'UTF-8', 'ISO-8859-1')) . "</td>";
                        echo "<td class='p-4'>";
                        echo "<form method='POST' style='display:inline;'>";
                        echo "<input type='hidden' name='action' value='verIndicadores'>";
                        echo "<input type='hidden' name='activeTab' value='seguimiento'>"; // Clave para la persistencia
                        echo "<input type='hidden' name='empleadoId' value='" . htmlspecialchars($emp['IDEMPLEADO']) . "'>";
                        echo "<input type='hidden' name='searchTerm' value='" . htmlspecialchars($searchTerm) . "'>"; // Mantener el término de búsqueda
                        echo "<button type='submit' class='px-4 py-2 text-sm bg-info text-white rounded-md hover:bg-opacity-90 transition'>";
                        echo "Ver Indicadores</button>";
                        echo "</form>";
                        echo "</td>";
                        echo "</tr>";
                    }

                    echo "</tbody>";
                    echo "</table>";
                    echo "</div>";
                } else {
                    echo "<p class='text-gray-500 text-center py-4'>No se encontraron empleados con ese criterio de búsqueda.</p>";
                }
                echo "</div>";
            }
        } else {
            echo "<p class='text-warning text-center py-4'>Por favor, ingresa al menos 2 caracteres para buscar.</p>";
        }
    }

    // Mostrar indicadores si se seleccionó un empleado
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && $empleadoIdBuscado && $_POST['action'] == 'verIndicadores') {
        
        // Obtener datos del empleado
        $consultaEmp = "SELECT EMPLEADO, CARGO, IDENTIFICACION FROM ZAYMAWEB.VST_GHEMPEMPLEADOSCARGOS WHERE IDEMPLEADO = $empleadoIdBuscado";
        $resultEmp = $this->ejecutarConsulta($consultaEmp);
        $empData = oci_fetch_assoc($resultEmp);

        if ($empData) {
                echo "<div class='mb-8'>";
                echo "<h3 class='text-2xl font-bold mb-6 pb-3 border-b-2 border-primary'>Indicadores de " . htmlspecialchars(mb_convert_encoding($empData['EMPLEADO'], 'UTF-8', 'ISO-8859-1')) . "</h3>";
                echo "<div class='bg-lightgray dark:bg-darkgray rounded-lg p-4 mb-6'>";
                echo "<p><strong>Identificación:</strong> " . htmlspecialchars($empData['IDENTIFICACION']) . "</p>";
                echo "<p><strong>Cargo:</strong> " . htmlspecialchars(mb_convert_encoding($empData['CARGO'], 'UTF-8', 'ISO-8859-1')) . "</p>";
                echo "</div>";
                
                // --- 1. EVALUACIONES OBTENIDAS COMO COLABORADOR (11 Preguntas) ---
                // Esta función calcula y muestra el promedio entre Autoevaluación (11) y Evaluación Recibida (11)
                echo "<div class='mt-6 border-t pt-6'>";
                echo "<h4 class='text-xl font-semibold mb-4 text-info dark:text-lightinfo'> Promedio de Evaluación de Desempeño vs. Percepción Propia (11 Competencias)</h4>";
                $this->mostrarPromedioColaborador($empleadoIdBuscado); 
                echo "</div>";

                // --- 2. EVALUACIONES OBTENIDAS COMO LÍDER (8 Preguntas) ---
                // Esta función calcula y muestra el promedio entre Autoevaluación Líder (8) y Evaluación 360 Liderazgo Recibida (8)
                echo "<div class='mt-8 pt-6 border-t'>";
                echo "<h4 class='text-xl font-semibold mb-4 text-info dark:text-lightinfo'> Indicadores de Evaluación de Liderazgo (8 Competencias)</h4>";
                $this->mostrarIndicadoresLider($empleadoIdBuscado); 
                echo "</div>";

                // --- 3. MOSTRAR LISTA DE EVALUADORES ---
                $evaluadores = $this->obtenerEvaluadores($empleadoIdBuscado);
                
                echo "<div class='mt-8 pt-6 border-t'>";
                echo "<h4 class='text-xl font-semibold mb-4'>Histórico Reciente: Líderes/Colaboradores que lo han Evaluado</h4>";

                if (count($evaluadores) > 0) {
                    echo "<ul class='list-disc list-inside space-y-2 pl-4 text-gray-700 dark:text-gray-300'>";
                    foreach ($evaluadores as $eval) {
                        $nombre = htmlspecialchars(mb_convert_encoding($eval['EMPLEADO'], 'UTF-8', 'ISO-8859-1'));
                        $cargo = htmlspecialchars(mb_convert_encoding($eval['CARGO'], 'UTF-8', 'ISO-8859-1'));
                        $fecha = $eval['FECHACONFIRMA'] ? date('d/m/Y', strtotime($eval['FECHACONFIRMA'])) : 'Fecha no disponible';
                        
                        echo "<li class='text-sm'>**{$nombre}** ({$cargo}) - Evaluado el {$fecha}</li>";
                    }
                    echo "</ul>";
                } else {
                    echo "<p class='text-gray-500'>No se encontraron registros de evaluaciones recientes recibidas.</p>";
                }
                echo "</div>";

                echo "</div>";
            } else {
            echo "<div class='p-4 bg-danger text-white rounded-md'>🚨 Error: No se encontraron datos para el ID de empleado seleccionado.</div>";
        }
    }

    echo "</div>";
}

        /**
         * Helper para obtener clase de color según score
         */
        private function getBadgeColor($score) {
            return match($score) {
                'Sobresaliente' => 'bg-lightsuccess text-success',
                'Acorde' => 'bg-lightinfo text-info',
                'Aceptable' => 'bg-lightwarning text-warning',
                'Necesita Mejorar' => 'bg-lightdanger text-danger',
                'Insuficiente' => 'bg-lightdark text-dark',
                default => 'bg-lightgray text-gray'
            };
        }

    }
?>
