<?php

// Inicializamos las variables para evitar errores si no vienen del controlador
$idEmpCargo = $idEmpCargo ?? ''; 
$activeTab = $activeTab ?? 'autoeval';
$autoeval = $autoeval ?? null;
$dictColab = $dictColab ?? [];
$realizadasColab = $realizadas['colab'] ?? [];
$realizadasLider = $realizadas['lider'] ?? [];
$indicadores = $indicadores ?? ['auto' => null, 'lider' => null];
$autoData = $indicadores['auto'] ?? null;
$liderData = $indicadores['lider'] ?? null;
$autoEval = $indicadores['auto'] ?? null;
    
    // El controlador manda las recibidas en 'lider'
    $todasLider = $indicadores['lider'] ?? []; 
    $liderEval = $indicadores['lider'] ?? null; // Ahora viene de HUMEVALUACIONCOLABO
    
?>
<style>
    /* Forzamos que los botones de pestañas no tengan fondo azul */
    .tab-btn {
        background-color: transparent !important;
        background: none !important;
        outline: none !important;
        box-shadow: none !important;
        -webkit-tap-highlight-color: transparent !important;
    }

    /* Solo cuando esté activa, le damos el color al texto y al borde inferior */
    .tab-btn.text-primary {
        color: #4e73df !important; /* Reemplaza por color morado/azul de texto si es distinto */
        border-bottom-color: #4e73df !important;
    }

    /* Quitamos el azul que sale al hacer click (focus) */
    .tab-btn:focus, .tab-btn:active, .tab-btn:visited {
        background-color: transparent !important;
        color: inherit;
    }
</style>

<div class="w-full flex justify-center py-6 px-4">
    <div class="w-full max-w-7xl">
        <div class="card shadow-lg rounded-lg bg-white dark:bg-darkcard border border-gray-200 dark:border-gray-700">
            <div class="card-body p-6 md:p-10">
                
                <div class="text-center mb-10">
                    <h2 class="text-4xl font-bold text-gray-800 dark:text-white">Mis Reportes de Evaluación</h2>
                    <p class="text-gray-500 mt-2">Consulta tus resultados y el seguimiento de tu equipo</p>
                </div>

                <div class="flex border-b border-gray-200 mb-6 overflow-x-auto">
                    <?php 
                    $tabs = [
                        'autoeval'    => 'Mi Autoevaluación',
                        'recibidas'   => 'Evaluaciones Recibidas',
                        'realizadas'  => 'Evaluaciones Realizadas',
                        'indicadores' => 'Indicadores'
                    ];

                    if (in_array($idEmpCargo, [60, 118])) {
                        $tabs['seguimiento'] = 'Seguimiento';
                    }

                    foreach ($tabs as $id => $label): 
        $isActive = ($id === $activeTab);
    ?>
        <button 
            class="tab-btn px-6 py-3 border-b-2 font-bold transition-all focus:outline-none whitespace-nowrap <?= $isActive ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-primary' ?>" 
            onclick="window.switchTab('<?= $id ?>', this)">
            <?= $label ?>
        </button>
    <?php endforeach; ?>
</div>

                <div id="tab-autoeval" class="tab-content <?= ($activeTab !== 'autoeval') ? 'hidden' : '' ?>">
                    <?php if ($autoeval && isset($autoeval['FECHAREALIZA'])): ?>
                        <div class="bg-blue-50 p-4 rounded-lg mb-4 border-l-4 border-blue-500">
                            <p class="text-sm text-blue-700">Última actualización: <strong><?= date('d/m/Y', strtotime($autoeval['FECHAREALIZA'])) ?></strong></p>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="table-auto w-full text-left border-collapse">
                                <thead class="bg-gray-100 dark:bg-gray-800">
                                    <tr>
                                        <th class="p-4 border-b">Competencia Evaluada</th>
                                        <th class="p-4 border-b text-center">Calificación</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($dictColab as $i => $nombre): ?>
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                            <td class="p-4 border-b dark:border-gray-700"><?= $nombre ?></td>
                                            <td class="p-4 border-b text-center font-bold text-primary dark:border-gray-700">
                                                <?= $autoeval["PREGUNTA$i"] ?? '0' ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-10">
                            <p class="text-gray-400 italic">No se encontraron registros de autoevaluación.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <div id="tab-recibidas" class="tab-content <?= ($activeTab !== 'recibidas') ? 'hidden' : '' ?>">
                    <h3 class="text-xl font-bold mb-4">Evaluaciones que he recibido</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="p-4 border rounded-lg bg-gray-50 dark:bg-gray-800">
                            <h4 class="font-bold border-b pb-2 mb-3">Como Colaborador</h4>
                            <?php if(!empty($recibidasColab)): ?>
                                <ul class="space-y-2">
                                    <?php foreach($recibidasColab as $r): ?>
                                        <li class="text-sm flex justify-between items-center">
                                            <span>Por: <strong><?= $r['EVALUADOR'] ?></strong></span>
                                            <span class="text-xs text-gray-500"><?= $r['FECHACONFIRMA'] ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p class="text-sm text-gray-400">Sin evaluaciones recibidas.</p>
                            <?php endif; ?>
                        </div>
                        <div class="p-4 border rounded-lg bg-gray-50 dark:bg-gray-800">
                            <h4 class="font-bold border-b pb-2 mb-3">Como Líder</h4>
                            <?php if(!empty($recibidasLider)): ?>
                                <ul class="space-y-2">
                                    <?php foreach($recibidasLider as $r): ?>
                                        <li class="text-sm flex justify-between items-center">
                                            <span>Por: <strong><?= $r['EVALUADOR'] ?></strong></span>
                                            <span class="text-xs text-gray-500"><?= $r['FECHACONFIRMA'] ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p class="text-sm text-gray-400">Sin evaluaciones de liderazgo.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            <div id="tab-realizadas" class="tab-content <?= ($activeTab !== 'realizadas') ? 'hidden' : '' ?>">
                <h3 class="text-xl font-bold mb-4">Evaluaciones que has realizado</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <div class="p-4 border rounded-lg bg-gray-50 dark:bg-gray-800">
                        <h4 class="font-bold border-b pb-2 mb-3">A Colaboradores</h4>
                        <?php if(!empty($realizadasColab)): ?>
                            <ul class="space-y-2">
                                <?php foreach($realizadasColab as $r): ?>
                                    <li class="text-sm flex justify-between items-center p-2 bg-white dark:bg-gray-700 rounded shadow-sm">
                                        <span>Evaluaste a: <strong><?= $r['EVALUADO'] ?></strong></span>
                                        <span class="text-xs text-gray-500"><?= date('d/m/Y', strtotime($r['FECHACONFIRMA'])) ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-sm text-gray-400">No has realizado evaluaciones a colaboradores.</p>
                        <?php endif; ?>
                    </div>

                    <div class="p-4 border rounded-lg bg-gray-50 dark:bg-gray-800">
                        <h4 class="font-bold border-b pb-2 mb-3">De Liderazgo</h4>
                        <?php if(!empty($realizadasLider)): ?>
                            <ul class="space-y-2">
                                <?php foreach($realizadasLider as $r): ?>
                                    <li class="text-sm flex justify-between items-center p-2 bg-white dark:bg-gray-700 rounded shadow-sm">
                                        <span>Evaluaste a: <strong><?= $r['EVALUADO'] ?></strong></span>
                                        <span class="text-xs text-gray-500"><?= date('d/m/Y', strtotime($r['FECHACONFIRMA'])) ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-sm text-gray-400">No has realizado evaluaciones de liderazgo.</p>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
           <div id="tab-indicadores" class="tab-content <?= ($activeTab !== 'indicadores') ? 'hidden' : '' ?>">
    <?php
    $escala = ['Sobresaliente' => 5, 'Acorde' => 4, 'Aceptable' => 3, 'Necesita Mejorar' => 2, 'Insuficiente' => 1];
    $competencias = $dictColab; 
    
    $autoEval = $indicadores['auto'] ?? null;
    $liderEval = $indicadores['lider_reciente'] ?? null;

    // Variables para acumular los totales
    $sumaAuto = 0;
    $sumaLider = 0;
    $conteo = 0;
    ?>

    <div class="mb-10">
        <h3 class="text-xl font-bold mb-4 text-gray-800 dark:text-white">Promedio de Evaluación de Desempeño vs. Percepción Propia</h3>
        
        <?php if ($autoEval && $liderEval): ?>
            <div class="overflow-x-auto shadow rounded-lg">
                <table class="w-full text-sm text-left">
                    <thead class="bg-lightprimary dark:bg-darkprimary text-primary dark:text-white">
                        <tr>
                            <th class="p-4">Competencia</th>
                            <th class="p-4 text-center">Tu Autoevaluación</th>
                            <th class="p-4 text-center">Evaluación del Líder</th>
                            <th class="p-4 text-center">Promedio</th>
                            <th class="p-4 text-center">Brecha</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y dark:divide-gray-700">
                <?php foreach ($competencias as $i => $nombre): 
                    $key = "PREGUNTA$i";
                    $autoVal = $escala[trim($autoEval[$key] ?? '')] ?? 0;
                    $liderVal = $escala[trim($liderEval[$key] ?? '')] ?? 0;

                    // Acumulamos solo si ambos tienen valor
                    if ($autoVal > 0 && $liderVal > 0) {
                        $sumaAuto += $autoVal;
                        $sumaLider += $liderVal;
                        $conteo++;
                    }

                    $promedio = ($autoVal > 0 && $liderVal > 0) ? round(($autoVal + $liderVal) / 2, 2) : 'N/A';
                    
                    if ($autoVal > 0 && $liderVal > 0) {
                        $brechaNum = $liderVal - $autoVal;
                        $brechaDisplay = ($brechaNum > 0) ? '+' . $brechaNum : $brechaNum;
                        $brechaClass = ($brechaNum < 0) ? 'text-red-600 font-bold' : (($brechaNum > 0) ? 'text-green-600 font-bold' : 'text-gray-500');
                    } else {
                        $brechaDisplay = '--'; 
                        $brechaClass = 'text-gray-400';
                    }
                    ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                        <td class="p-4 font-medium"><?= $nombre ?></td>
                        <td class="p-4 text-center"><span class="badge bg-lightinfo text-info"><?= $autoEval[$key] ?? 'N/A' ?></span></td>
                        <td class="p-4 text-center"><span class="badge bg-lightinfo text-info"><?= $liderEval[$key] ?? 'N/A' ?></span></td>
                        <td class="p-4 text-center font-bold text-primary"><?= $promedio ?></td>
                        <td class="p-4 text-center <?= $brechaClass ?>"><?= $brechaDisplay ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                
                <tfoot class="bg-gray-100 dark:bg-gray-900 font-black border-t-2 border-gray-300">
                    <?php 
                        $promedioGeneralAuto = ($conteo > 0) ? round($sumaAuto / $conteo, 2) : 0;
                        $promedioGeneralLider = ($conteo > 0) ? round($sumaLider / $conteo, 2) : 0;
                        $promedioFinalTotal = round(($promedioGeneralAuto + $promedioGeneralLider) / 2, 2);
                    ?>
                    <tr>
                        <td class="p-4 text-right uppercase text-xs">Promedio General Obtenido:</td>
                        <td class="p-4 text-center text-primary"><?= $promedioGeneralAuto ?></td>
                        <td class="p-4 text-center text-primary"><?= $promedioGeneralLider ?></td>
                        <td class="p-4 text-center bg-primary text-white text-lg"><?= $promedioFinalTotal ?></td>
                       <!-- <td class="p-4 text-center">
                            <?php 
                                $brechaTotal = $promedioGeneralLider - $promedioGeneralAuto;
                                echo ($brechaTotal > 0 ? '+' : '') . $brechaTotal;
                            ?>
                        </td> -->
                    </tr>
                </tfoot>
            </table>
        </div>
        <?php else: ?>
            <div class="p-4 bg-yellow-50 text-yellow-700 rounded-lg">⚠️ No se encontró autoevaluación o evaluación del líder reciente.</div>
        <?php endif; ?>
    </div>

    <p class="text-gray-500 mt-2 text-xs">La Brecha indica la diferencia de puntaje: Negativo (-) significa que te calificaste más alto que tu líder; Positivo (+) significa que tu líder te calificó más alto</p>

<div class="mt-8">
    <h3 class="text-xl font-bold mb-2 text-gray-800">Indicadores de Desempeño (Agregación)</h3>
    <p class="text-gray-500 mb-6">Distribución histórica de frecuencias y promedios generales.</p>

    <?php
    $escala = ['Sobresaliente' => 5, 'Acorde' => 4, 'Aceptable' => 3, 'Necesita Mejorar' => 2, 'Insuficiente' => 1];
    $resumenCalculado = ['sumaD' => 0, 'conteoD' => 0, 'sumaL' => 0, 'conteoL' => 0];
    ?>

    <?php if (!empty($agregados['desempeno'])): ?>
        <div class="mb-10">
            <h4 class="font-bold mb-4 text-blue-700 flex items-center">
                <span class="w-3 h-3 bg-blue-500 rounded-full mr-2"></span>
                Evaluaciones como Colaborador
            </h4>
            <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 text-gray-700 border-b">
                        <tr>
                            <th class="p-4 font-semibold">Competencia</th>
                            <?php foreach($escala as $label => $v) echo "<th class='p-4 text-center font-semibold'>$label</th>"; ?>
                            <th class="p-4 text-center font-bold bg-blue-50 text-blue-700">Promedio</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php foreach ($dictColab as $i => $nombre): 
                            $key = "PREGUNTA$i";
                            $frecuencia = array_fill_keys(array_keys($escala), 0);
                            $sumaNotas = 0;
                            $respuestasValidas = 0;

                            foreach ($agregados['desempeno'] as $eval) {
                                $r = trim($eval[$key] ?? '');
                                if (isset($escala[$r])) {
                                    $frecuencia[$r]++;
                                    $sumaNotas += $escala[$r];
                                    $respuestasValidas++;
                                }
                            }
                            $promedio = $respuestasValidas > 0 ? round($sumaNotas / $respuestasValidas, 2) : 0;
                            if($promedio > 0) { $resumenCalculado['sumaD'] += $promedio; $resumenCalculado['conteoD']++; }
                        ?>
                        <tr class="hover:bg-gray-50">
                            <td class="p-4 font-medium text-gray-700"><?= $nombre ?></td>
                            <?php foreach($escala as $label => $v): ?>
                                <td class="p-4 text-center"><?= $frecuencia[$label] ?></td>
                            <?php endforeach; ?>
                            <td class="p-4 text-center font-bold bg-blue-50/50 text-blue-800"><?= $promedio ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
       <?php if (!empty($agregados['liderazgo'])): ?>
    <div class="mb-10">
        <h4 class="font-bold mb-4 text-blue-700 flex items-center">
                <span class="w-3 h-3 bg-blue-500 rounded-full mr-2"></span>
                Evaluaciones como Líder
            </h4>
        <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-700 border-b">
                    <tr>
                        <th class="p-4 font-semibold">Competencia de Liderazgo</th>
                        <?php foreach($escala as $label => $v) echo "<th class='p-4 text-center font-semibold'>$label</th>"; ?>
                        <th class="p-4 text-center font-bold bg-purple-50 text-purple-700">Promedio</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
    <?php 
                // --- NOTA TÉCNICA (DEUDA TÉCNICA / TEMPORAL) ---
                // EL ERROR: Desfase de índices en la base de datos. El formulario de origen guarda la 1ra 
                // competencia ('Propósito') en la columna 'PREGUNTA4'.
                //
                // AJUSTE REALIZADO: 
                // 1. Se define el diccionario localmente para evitar errores de Namespace y asegurar los nombres.
                // 2. Se usa el mapeo ($i + 3) para sincronizar las etiquetas con sus datos reales (ID 1 -> Columna 4).
                //
                // SOLUCIÓN DEFINITIVA: Es imperativo corregir los 'name' de los inputs en el formulario de 
                // captura para que inicien en 'pregunta1' y así normalizar la estructura de datos.

                    $dictLiderazgoZayma = [
                        1 => 'Propósito',
                        2 => 'Colaboración',
                        3 => 'Consistencia',
                        4 => 'Adaptabilidad',
                        5 => 'Amor'
                    ];

                    foreach ($dictLiderazgoZayma as $i => $nombre): 
                        // Sincronización: ID 1 + 3 = Columna 'PREGUNTA4'
                        $key = "PREGUNTA" . ($i + 3); 

                        $frecuencia = array_fill_keys(array_keys($escala), 0);
                        $sumaNotas = 0;
                        $respuestasValidas = 0;

                        foreach ($agregados['liderazgo'] as $eval) {
                            $val = isset($eval[$key]) ? trim($eval[$key]) : '';
                            if (isset($escala[$val])) {
                                $frecuencia[$val]++;
                                $sumaNotas += $escala[$val];
                                $respuestasValidas++;
                            }
                        }

                        // Si la columna no tiene datos, no mostramos la fila
                        if ($respuestasValidas === 0) continue;

                        $promedio = round($sumaNotas / $respuestasValidas, 2);
                        
                        // Acumulación para el resumen general
                        $resumenCalculado['sumaL'] += $promedio; 
                        $resumenCalculado['conteoL']++; 
                    ?>
                    <tr class="hover:bg-gray-50">
                        <td class="p-4 font-medium text-gray-700"><?= $nombre ?></td>
                        <?php foreach($escala as $label => $v): ?>
                            <td class="p-4 text-center"><?= $frecuencia[$label] ?></td>
                        <?php endforeach; ?>
                        <td class="p-4 text-center font-bold bg-purple-50/50 text-purple-800"><?= $promedio ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>                         
   

    <?php 
        $finalD = $resumenCalculado['conteoD'] > 0 ? round($resumenCalculado['sumaD'] / $resumenCalculado['conteoD'], 2) : 0;
        $finalL = $resumenCalculado['conteoL'] > 0 ? round($resumenCalculado['sumaL'] / $resumenCalculado['conteoL'], 2) : 0;
        
        $divisorTotal = 0;
        $sumaTotal = 0;
        if($finalD > 0) { $sumaTotal += $finalD; $divisorTotal++; }
        if($finalL > 0) { $sumaTotal += $finalL; $divisorTotal++; }
        $totalGeneral = $divisorTotal > 0 ? round($sumaTotal / $divisorTotal, 2) : 0;
    ?>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
        <div class="p-6 bg-white border-2 border-blue-100 rounded-2xl shadow-sm">
            <p class="text-sm font-semibold text-blue-600 uppercase mb-1">Promedio Colaborador</p>
            <p class="text-3xl font-black text-gray-800"><?= $finalD ?> <span class="text-lg font-normal text-gray-400">/ 5.0</span></p>
        </div>
            <div class="p-6 bg-white border-2 border-purple-100 rounded-2xl shadow-sm">
            <p class="text-sm font-semibold text-purple-600 uppercase mb-1">Promedio Líder</p>
            <?php if($finalL > 0): ?>
                <p class="text-3xl font-black text-gray-800"><?= $finalL ?> <span class="text-lg font-normal text-gray-400">/ 5.0</span></p>
            <?php else: ?>
                <p class="text-xl font-bold text-gray-400 italic mt-2">No aplica</p>
                <p class="text-[10px] text-gray-400">Sin registros de liderazgo</p>
            <?php endif; ?>
        </div>
        <div class="p-6 bg-primary rounded-2xl shadow-md text-white">
            <p class="text-sm font-semibold uppercase mb-1 opacity-80">Promedio General Total</p>
            <p class="text-3xl font-black"><?= $totalGeneral ?> <span class="text-lg font-normal opacity-60">/ 5.0</span></p>
        </div>
    </div>
</div>   
    </div>


            </div>
                <?php if (in_array($idEmpCargo, [60, 118])): ?>
    <div id="tab-seguimiento" class="tab-content <?= ($activeTab !== 'seguimiento') ? 'hidden' : '' ?>">
        <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Seguimiento de Equipo</h3>
            
            <form method="POST" class="flex gap-2">
                <input type="hidden" name="action" value="buscarEmpleado">
                <input type="hidden" name="activeTab" value="seguimiento">
                <input type="text" name="searchTerm" 
                       value="<?= $searchTerm ?? '' ?>"
                       placeholder="Nombre o identificación..." 
                       class="flex-1 px-4 py-2 border border-gray-200 dark:border-gray-600 rounded-xl dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-xl font-semibold hover:bg-blue-700 transition">
                    Buscar
                </button>
            </form>

            <div id="resultadosBusqueda" class="mt-8">
                <?php if (!empty($resultadosBusqueda)): ?>
                    <div class="overflow-hidden border border-gray-100 dark:border-gray-700 rounded-xl">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-300 text-xs uppercase">
                                <tr>
                                    <th class="p-4">Empleado</th>
                                    <th class="p-4">Cargo</th>
                                    <th class="p-4 text-center">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <?php foreach ($resultadosBusqueda as $emp): ?>
                                    <tr class="hover:bg-blue-50/50 dark:hover:bg-blue-900/20 transition">
                                        <td class="p-4">
                                            <p class="font-bold text-gray-800 dark:text-gray-200"><?= mb_convert_encoding($emp['EMPLEADO'], 'UTF-8', 'ISO-8859-1') ?></p>
                                            <p class="text-xs text-gray-500"><?= $emp['IDENTIFICACION'] ?></p>
                                        </td>
                                        <td class="p-4 text-sm text-gray-600 dark:text-gray-400"><?= mb_convert_encoding($emp['CARGO'], 'UTF-8', 'ISO-8859-1') ?></td>
                                        <td class="p-4 text-center">
                                            <form method="POST">
                                                <input type="hidden" name="action" value="verIndicadores">
                                                <input type="hidden" name="empleadoId" value="<?= $emp['IDEMPLEADO'] ?>">
                                                <input type="hidden" name="temp_nombre" value="<?= mb_convert_encoding($emp['EMPLEADO'], 'UTF-8', 'ISO-8859-1') ?>">
                                                <input type="hidden" name="temp_cargo" value="<?= mb_convert_encoding($emp['CARGO'], 'UTF-8', 'ISO-8859-1') ?>">
                                                <input type="hidden" name="activeTab" value="seguimiento">
                                                <button type="submit" class="text-blue-600 dark:text-blue-400 font-bold text-sm hover:underline">Ver Reporte</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php elseif (isset($_POST['action']) && $_POST['action'] == 'buscarEmpleado'): ?>
                    <p class="text-center text-gray-500 py-4 italic">No se encontraron resultados para la búsqueda.</p>
                <?php endif; ?>
            </div>
        </div>
            
        <div class="mt-10 border-t border-dashed border-gray-300 pt-8">
            <?php 
            // Capturamos los datos enviados por el formulario anterior
            $nombreSujeto = $_POST['temp_nombre'] ?? '';
            $cargoSujeto = $_POST['temp_cargo'] ?? '';
            ?>

            <?php if ($idTrabajo != $idEmpleado && !empty($nombreSujeto)): ?>
                <div class="mb-6 border-l-4 border-blue-600 pl-4">
                    <h4 class="text-[10px] font-black text-blue-600 uppercase tracking-widest mb-1">
                        Informe de Seguimiento
                    </h4>
                    <h2 class="text-2xl font-bold text-gray-800 uppercase">
                        <?= $nombreSujeto ?>
                    </h2>
                    <p class="text-sm text-gray-500 font-medium italic">
                        <?= $cargoSujeto ?>
                    </p>
                </div>
            <?php else: ?>
                <h4 class="text-center text-sm font-bold text-gray-400 uppercase tracking-widest mb-6">
                    Quién me ha evaluado recientemente
                </h4>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php foreach ($evaluadores as $ev): ?>
                    <?php endforeach; ?>
            </div>
        </div>
        <?php if (!empty($evaluadores) && $idTrabajo != $idEmpleado): ?>
            <div class="mt-8 bg-blue-50/50 dark:bg-gray-800/50 p-6 rounded-2xl border border-dashed border-blue-200 dark:border-gray-600">
                <h4 class="text-sm font-bold text-blue-600 dark:text-blue-400 uppercase tracking-widest mb-4 text-center md:text-left">Evaluadores de este colaborador</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php foreach ($evaluadores as $ev): ?>
                        <div class="flex items-center gap-3 bg-white dark:bg-gray-800 p-3 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
                            <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-300 rounded-full flex items-center justify-center font-bold shrink-0">
                                <?= substr($ev['EMPLEADO'] ?? 'E', 0, 1) ?>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-800 dark:text-gray-200 leading-tight"><?= $ev['EMPLEADO'] ?></p>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400">
                                    <?= date('d M, Y', strtotime($ev['FECHACONFIRMA'])) ?> • <?= $ev['CARGO'] ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

    </div>
<?php endif; ?>

            </div>
        </div>
    </div>
</div>

<script>
window.switchTab = function(tabName, element) {
    // 1. Ocultar todos los contenidos
    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
    
    // 2. Mostrar el seleccionado
    const target = document.getElementById('tab-' + tabName);
    if(target) target.classList.remove('hidden');
    
    // 3. Resetear TODOS los botones a su estado base (Sin fondos)
    document.querySelectorAll('.tab-btn').forEach(btn => {
        // Esto elimina cualquier clase de fondo (bg-...) o texto blanco
        btn.className = "tab-btn px-6 py-3 border-b-2 font-bold transition-all focus:outline-none whitespace-nowrap border-transparent text-gray-500 hover:text-primary";
    });
    
    // 4. Aplicar estilo activo solo al botón actual
    element.classList.remove('border-transparent', 'text-gray-500');
    element.classList.add('border-primary', 'text-primary');
};
</script>

