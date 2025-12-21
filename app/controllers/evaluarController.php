<?php
namespace app\controllers;
use app\models\mainModel;

class evaluarController extends mainModel {

    public function autoEvaluacion() {
        $IDEMPLEADO = $_SESSION['idempleado'];
    
        $consulta = "SELECT 
            CASE WHEN CONFIRMADO IS NULL THEN 0 ELSE CONFIRMADO END AS CONFIRMADO 
        FROM VAADINWEB.HUMAUTOEVALUACION 
        WHERE EMPLEADO_ID = $IDEMPLEADO";
    
        $resultado = $this->ejecutarConsulta($consulta);
    
        // Variable para verificar si hay resultados
        $hasResults = false;
    
        ob_start();
        ?>
    
        <?php while ($employee = oci_fetch_assoc($resultado)): ?>
            <?php $hasResults = true; // Si hay al menos un resultado, cambia a true ?>
            <div class="flex flex-wrap mt-6 gap-4">
                <?php if ($employee['CONFIRMADO'] == 1): ?>
                    <a href="#" onclick="showMessageAuto();" class="btn bg-success hover:bg-primaryemphasis text-white btn-md py-2.5 px-4 w-fit block">
                        Autoevaluación Completado
                    </a>
                <?php else: ?>
                    <a href="<?php echo APP_URL;?>autoEvaluacion/" onclick="submitForm('<?php echo $IDEMPLEADO; ?>');" class="btn bg-primary hover:bg-primaryemphasis text-white btn-md py-2.5 px-4 w-fit block">
                        Iniciar Autoevaluación
                    </a>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    
        <!-- Si no hay resultados, mostrar el botón "Iniciar Autoevaluación" -->
        <?php if (!$hasResults): ?>
            <div class="flex flex-wrap mt-6 gap-4">
                <a href="<?php echo APP_URL;?>autoEvaluacion/" onclick="submitForm('<?php echo $IDEMPLEADO; ?>');" class="btn bg-primary hover:bg-primaryemphasis text-white btn-md py-2.5 px-4 w-fit block">
                    Iniciar Autoevaluación
                </a>
            </div>
        <?php endif; ?>
    
        <script>
            function submitForm(employeeId) {
                document.getElementById('employeeForm-' + employeeId).submit();
            }
    
            function showMessageAuto() {
                Swal.fire({
                    icon: 'success',
                    title: 'Excelente',
                    text: 'Ya has completado la autoevaluación',
                    showConfirmButton: false,
                    timer: 2000
                });
            }
        </script>
    
        <?php
        return ob_get_clean();
    }
    

    public function listarColaboradoresEvaluar() {
        $IDEMPLEADO = $_SESSION['idempleado'];
        $NIVELCARGO = $_SESSION['nivelcargo'];

        if($NIVELCARGO == 'NC001'){//TRBAJADOR
            $consulta = "SELECT
                CDIR.IDEMPLEADO,
                CDIR.EMPLEADO,
                CDIR.CARGO,
                CASE WHEN F.CONFIRMADO IS NULL THEN 0 ELSE F.CONFIRMADO END AS CONFIRMADO,
                CDIR.CODNIVELCARGO
                FROM ZAYMAWEB.VST_GHEMPEMPLEADOSCARGOS CD
                INNER JOIN ZAYMAWEB.GHEMPCARGOSSUBORDINADOS DIR ON CD.IDEMPCARGO = DIR.IDEMPCARGOSUB
                INNER JOIN ZAYMAWEB.VST_GHEMPEMPLEADOSCARGOS CDIR ON DIR.IDEMPCARGO = CDIR.IDEMPCARGO
                LEFT JOIN VAADINWEB.HUMAUTOEVALUACIONLIDER F ON CDIR.IDEMPLEADO = F.EMPLEADO_ID AND F.EMPLEADO_CONFIRMA = $IDEMPLEADO
                WHERE CD.IDEMPLEADO = $IDEMPLEADO AND IDEMPCARGO <> 1062 AND TRUNC(SYSDATE - CDIR.FECHAINGRESO) >= 90"
                ;
        } else {
            if($NIVELCARGO == 'NC002'){//COORDINADOR
                //DIRECTOR
                $consulta = "SELECT * FROM (
                    SELECT
                            CDIR.IDEMPLEADO,
                            CDIR.EMPLEADO,
                            CDIR.CARGO,
                            CASE WHEN F.CONFIRMADO IS NULL THEN 0 ELSE F.CONFIRMADO END AS CONFIRMADO,
                            CDIR.CODNIVELCARGO
                        FROM ZAYMAWEB.VST_GHEMPEMPLEADOSCARGOS CD
                        INNER JOIN ZAYMAWEB.GHEMPCARGOSSUBORDINADOS DIR ON CD.IDEMPCARGO = DIR.IDEMPCARGOSUB
                        INNER JOIN ZAYMAWEB.VST_GHEMPEMPLEADOSCARGOS CDIR ON DIR.IDEMPCARGO = CDIR.IDEMPCARGO
                        LEFT JOIN VAADINWEB.HUMAUTOEVALUACIONLIDER F ON CDIR.IDEMPLEADO = F.EMPLEADO_ID AND F.EMPLEADO_CONFIRMA = $IDEMPLEADO
                        WHERE CD.IDEMPLEADO = $IDEMPLEADO AND IDEMPCARGO <> 106 AND TRUNC(SYSDATE - CDIR.FECHAINGRESO) >= 90
            
                        UNION ALL
            
                        -- SUBORDINADOS
                        SELECT
                            CSUB.IDEMPLEADO,
                            CSUB.EMPLEADO AS EMPLEADO,
                            CSUB.CARGO,
                            CASE WHEN F.CONFIRMADO IS NULL THEN 0 ELSE F.CONFIRMADO END AS CONFIRMADO,
                            CSUB.CODNIVELCARGO
                        FROM ZAYMAWEB.VST_GHEMPEMPLEADOSCARGOS C
                        INNER JOIN ZAYMAWEB.GHEMPCARGOSSUBORDINADOS SUB ON C.IDEMPCARGO = SUB.IDEMPCARGO
                        INNER JOIN ZAYMAWEB.VST_GHEMPEMPLEADOSCARGOS CSUB ON SUB.IDEMPCARGOSUB = CSUB.IDEMPCARGO
                        LEFT JOIN VAADINWEB.HUMEVALUACIONCOLABO F ON CSUB.IDEMPLEADO = F.EMPLEADO_ID AND F.EMPLEADO_CONFIRMA = $IDEMPLEADO
                        WHERE C.IDEMPLEADO = $IDEMPLEADO AND IDEMPCARGO <> 106 AND TRUNC(SYSDATE - CSUB.FECHAINGRESO) >= 90
                    )";
            } else {
                if($NIVELCARGO == 'NC003'){
                   //DIRECTOR
                   $consulta = "SELECT * FROM (
                    SELECT
                            CDIR.IDEMPLEADO,
                            CDIR.EMPLEADO,
                            CDIR.CARGO,
                            CASE WHEN F.CONFIRMADO IS NULL THEN 0 ELSE F.CONFIRMADO END AS CONFIRMADO,
                            CDIR.CODNIVELCARGO
                        FROM ZAYMAWEB.VST_GHEMPEMPLEADOSCARGOS CD
                        INNER JOIN ZAYMAWEB.GHEMPCARGOSSUBORDINADOS DIR ON CD.IDEMPCARGO = DIR.IDEMPCARGOSUB
                        INNER JOIN ZAYMAWEB.VST_GHEMPEMPLEADOSCARGOS CDIR ON DIR.IDEMPCARGO = CDIR.IDEMPCARGO
                        LEFT JOIN VAADINWEB.HUMAUTOEVALUACIONLIDER F ON CDIR.IDEMPLEADO = F.EMPLEADO_ID AND F.EMPLEADO_CONFIRMA = $IDEMPLEADO
                        WHERE CD.IDEMPLEADO = $IDEMPLEADO AND IDEMPCARGO <> 106 AND TRUNC(SYSDATE - CDIR.FECHAINGRESO) >= 90
            
                        UNION ALL
            
                        -- SUBORDINADOS
                        SELECT
                            CSUB.IDEMPLEADO,
                            CSUB.EMPLEADO AS EMPLEADO,
                            CSUB.CARGO,
                            CASE WHEN F.CONFIRMADO IS NULL THEN 0 ELSE F.CONFIRMADO END AS CONFIRMADO,
                            CSUB.CODNIVELCARGO
                        FROM ZAYMAWEB.VST_GHEMPEMPLEADOSCARGOS C
                        INNER JOIN ZAYMAWEB.GHEMPCARGOSSUBORDINADOS SUB ON C.IDEMPCARGO = SUB.IDEMPCARGO
                        INNER JOIN ZAYMAWEB.VST_GHEMPEMPLEADOSCARGOS CSUB ON SUB.IDEMPCARGOSUB = CSUB.IDEMPCARGO
                        LEFT JOIN VAADINWEB.HUMEVALUACIONCOLABO F ON CSUB.IDEMPLEADO = F.EMPLEADO_ID AND F.EMPLEADO_CONFIRMA = $IDEMPLEADO
                        WHERE C.IDEMPLEADO = $IDEMPLEADO AND IDEMPCARGO <> 106 AND TRUNC(SYSDATE - CSUB.FECHAINGRESO) >= 90
                    )";
                }else{
                    if($NIVELCARGO == 'NC004'){//DIRECTOR
                        //DIRECTOR
                            $consulta = "SELECT * FROM (
                                SELECT
                                        CDIR.IDEMPLEADO,
                                        CDIR.EMPLEADO,
                                        CDIR.CARGO,
                                        CASE WHEN F.CONFIRMADO IS NULL THEN 0 ELSE F.CONFIRMADO END AS CONFIRMADO,
                                        CDIR.CODNIVELCARGO
                                    FROM ZAYMAWEB.VST_GHEMPEMPLEADOSCARGOS CD
                                    INNER JOIN ZAYMAWEB.GHEMPCARGOSSUBORDINADOS DIR ON CD.IDEMPCARGO = DIR.IDEMPCARGOSUB
                                    INNER JOIN ZAYMAWEB.VST_GHEMPEMPLEADOSCARGOS CDIR ON DIR.IDEMPCARGO = CDIR.IDEMPCARGO
                                    LEFT JOIN VAADINWEB.HUMAUTOEVALUACIONLIDER F ON CDIR.IDEMPLEADO = F.EMPLEADO_ID AND F.EMPLEADO_CONFIRMA = $IDEMPLEADO
                                    WHERE CD.IDEMPLEADO = $IDEMPLEADO AND IDEMPCARGO <> 106 AND TRUNC(SYSDATE - CDIR.FECHAINGRESO) >= 90
                        
                                    UNION ALL
                        
                                    -- SUBORDINADOS
                                    SELECT
                                        CSUB.IDEMPLEADO,
                                        CSUB.EMPLEADO AS EMPLEADO,
                                        CSUB.CARGO,
                                        CASE WHEN F.CONFIRMADO IS NULL THEN 0 ELSE F.CONFIRMADO END AS CONFIRMADO,
                                        CSUB.CODNIVELCARGO
                                    FROM ZAYMAWEB.VST_GHEMPEMPLEADOSCARGOS C
                                    INNER JOIN ZAYMAWEB.GHEMPCARGOSSUBORDINADOS SUB ON C.IDEMPCARGO = SUB.IDEMPCARGO
                                    INNER JOIN ZAYMAWEB.VST_GHEMPEMPLEADOSCARGOS CSUB ON SUB.IDEMPCARGOSUB = CSUB.IDEMPCARGO
                                    LEFT JOIN VAADINWEB.HUMEVALUACIONCOLABO F ON CSUB.IDEMPLEADO = F.EMPLEADO_ID AND F.EMPLEADO_CONFIRMA = $IDEMPLEADO
                                    WHERE C.IDEMPLEADO = $IDEMPLEADO AND IDEMPCARGO <> 106 AND TRUNC(SYSDATE - CSUB.FECHAINGRESO) >= 90
                            )";
                    }else{
                        if($NIVELCARGO == 'NC005'){//GERENTE
                            //SUBORDINADOS
                            $consulta = "SELECT
                                CSUB.IDEMPLEADO,
                                CSUB.EMPLEADO AS EMPLEADO,
                                CSUB.CARGO,
                                CASE WHEN F.CONFIRMADO IS NULL THEN 0 ELSE F.CONFIRMADO END AS CONFIRMADO,
                                CSUB.CODNIVELCARGO
                            FROM ZAYMAWEB.VST_GHEMPEMPLEADOSCARGOS C
                            INNER JOIN ZAYMAWEB.GHEMPCARGOSSUBORDINADOS SUB ON C.IDEMPCARGO = SUB.IDEMPCARGO
                            INNER JOIN ZAYMAWEB.VST_GHEMPEMPLEADOSCARGOS CSUB ON SUB.IDEMPCARGOSUB = CSUB.IDEMPCARGO
                            LEFT JOIN VAADINWEB.HUMEVALUACIONCOLABO F ON CSUB.IDEMPLEADO = F.EMPLEADO_ID AND F.EMPLEADO_CONFIRMA = $IDEMPLEADO
                            WHERE C.IDEMPLEADO = $IDEMPLEADO AND IDEMPCARGO <> 106 AND TRUNC(SYSDATE - CSUB.FECHAINGRESO) >= 90";
                        }
                    }
                }
            }
        }

        $resultado = $this->ejecutarConsulta($consulta);
        
        ob_start();
        ?>

        <table class="min-w-full divide-y divide-border dark:divide-darkborder">
            <thead>
                <tr>
                    <th class="p-4 text-start text-base font-semibold text-link dark:text-white capitalize">Colaborador</th>
                    <th class="p-4 text-start text-base font-semibold text-link dark:text-white capitalize">Progreso</th>
                    <th class="p-4 text-start text-base font-semibold text-link dark:text-white capitalize">Estado</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border dark:divide-darkborder">
                <?php while ($employee = oci_fetch_assoc($resultado)): ?>
                <tr>
                    <td class="p-4 whitespace-nowrap">
                        <div class="flex gap-4 items-center">
                            <div>
                                <h6 class="text-base"><?php echo htmlspecialchars(mb_convert_encoding($employee['EMPLEADO'], 'UTF-8', 'ISO-8859-1')); ?></h6>
                                <p class="user-work text-xs text-bodytext dark:text-darklink"><?php echo htmlspecialchars(mb_convert_encoding($employee['CARGO'], 'UTF-8', 'ISO-8859-1')); ?></p>
                            </div>
                        </div>
                    </td>
                    <td class="p-4 whitespace-nowrap">
                    <?php
                        if ($employee['CONFIRMADO'] == 1){ ?>
                        <div class="flex items-center gap-x-3 whitespace-nowrap">
                            <div class="flex w-full h-1 bg-gray-200 rounded-full overflow-hidden dark:bg-gray-700" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100">
                                <div class="flex flex-col justify-center rounded-full overflow-hidden bg-primary text-xs text-white text-center whitespace-nowrap transition duration-500 dark:bg-primary" style="width: 100%"></div>
                            </div>
                            <div class="w-10 text-end">
                                <span class="text-sm text-gray-800 dark:text-white">100%</span>
                            </div>
                        </div>
                    <?php
                        }else{?>
                            <div class="flex items-center gap-x-3 whitespace-nowrap">
                            <div class="flex w-full h-1 bg-gray-200 rounded-full overflow-hidden dark:bg-gray-700" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100">
                                <div class="flex flex-col justify-center rounded-full overflow-hidden bg-primary text-xs text-white text-center whitespace-nowrap transition duration-500 dark:bg-primary" style="width: 1%"></div>
                            </div>
                            <div class="w-10 text-end">
                                <span class="text-sm text-gray-800 dark:text-white">0%</span>
                            </div>
                        </div>
                    <?php
                        }
                    ?>

                    </td>
                    <td class="p-4 whitespace-nowrap text-start">
                        <!-- Botón -->
                        <?php if ($employee['CONFIRMADO'] == 1): ?>
                            <a href="#" onclick="showMessage();"><span class="badge-md bg-lightsuccess text-success dark:bg-darksuccess dark:text-success">
                              Completado
                            </span></a>
                            
                        <?php else: ?>
                            <?php
								// Obtener el cargo del evaluador desde la sesión
                                $evaluadorCargo  = strtoupper($_SESSION['nivelcargo']);
                                $evaluadoCargo = strtoupper($employee['CODNIVELCARGO']);

                                echo "<script>console.log('Evaluador: $evaluadorCargo, Evaluado: {$employee['CODNIVELCARGO']}');</script>";
                                
								// Lógica de asignación de formularios según la jerarquía
								if (
									($evaluadorCargo == 'NC004' && $evaluadoCargo == 'NC003') ||  // Director evalúa a Coordinador -> submitForm
									($evaluadorCargo == 'NC005' && $evaluadoCargo == 'NC004') ||  // Gerente evalúa a Director -> submitForm
									($evaluadorCargo == 'NC002' && $evaluadoCargo == 'NC001') ||   // Coordinador evalúa a Trabajador
									($evaluadorCargo == 'NC003' && !in_array($evaluadoCargo, ['NC003', 'NC004', 'NC005'])) // Coordinador evalúa a subordinados -> submitForm

								){
									$formFunction = "submitForm";
								}elseif (
									($evaluadorCargo == 'NC004' && $evaluadoCargo == 'NC005') ||  // Director evalúa a Gerente -> submitFormLider
									($evaluadorCargo == 'NC003' && $evaluadoCargo == 'NC004') ||  // Coordinador evalúa a Director -> submitFormLider
									($evaluadorCargo == 'NC003' && $evaluadoCargo == 'NC003') ||  // Coordinador evalúa a Coordinador (Líder)
									($evaluadorCargo == 'NC002' && $evaluadoCargo == 'NC004') ||  // Coordinador evalúa a Director
									($evaluadorCargo == 'NC001' && $evaluadoCargo == 'NC004') ||  // Trabajor posiblemte evalúa a Director
                                    (!in_array($evaluadorCargo, ['NC003', 'NC004', 'NC005']) && in_array($evaluadoCargo, ['NC002','NC003'])) // Subordinados evalúan a Coordinador -> submitFormLider
								){
									$formFunction = "submitFormLider";
								} else {
									// Caso por defecto si no se cumple ninguna condición
									$formFunction = "submitForm";
								}
							?>
							<a href="#" onclick="<?php echo $formFunction; ?>('<?php echo $employee['IDEMPLEADO']; ?>');">
								<span class="badge-md bg-lightwarning text-warning dark:bg-darkwarning dark:text-warning">
									Pendiente
								</span>
							</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <script>
            function submitForm(employeeId) {
                //window.location.href = "<?php echo APP_URL;?>subEvaluacion?empleadoevaluado=" + employeeId;
                    var form = document.createElement("form");
                    form.method = "POST";
                    form.action = "<?php echo APP_URL;?>subEvaluacion";

                    var input = document.createElement("input");
                    input.type = "hidden";
                    input.name = "empleadoevaluado";
                    input.value = employeeId;

                    form.appendChild(input);
                    document.body.appendChild(form);
                    form.submit();
            }

            function submitFormLider(employeeId) {
                //window.location.href = "<?php echo APP_URL;?>evaluacionLiderazgo?empleadoevaluado=" + employeeId;
                var form = document.createElement("form");
                    form.method = "POST";
                    form.action = "<?php echo APP_URL;?>evaluacionLiderazgo";

                    var input = document.createElement("input");
                    input.type = "hidden";
                    input.name = "empleadoevaluado";
                    input.value = employeeId;

                    form.appendChild(input);
                    document.body.appendChild(form);
                    form.submit();
            }

            function showMessage() {
            Swal.fire({
                icon: 'success',
                title: 'Excelente',
                text: 'Ya has evaluado a este colaborador',
                showConfirmButton: false,
                timer: 2000
            });
        }
        </script>

        <?php
        return ob_get_clean();
    }

    public function registrarFormularioControlador(){

        $resultado = $this->ejecutarConsulta("SELECT COALESCE(MAX(IDDESEMPENO),0)+1 AS CONSECUTIVO FROM VAADINWEB.HUMDESEMPENO");
        $row = oci_fetch_assoc($resultado);
        $CNS = $row['CONSECUTIVO'];

        $IDEMPLEADO = $_SESSION['idempleado'];
        $IDEMPLEADOEVALUADO = $_POST['empleadoevaluado'];
        $SECCION1 = $_POST['section1'];
        $SECCION2 = $_POST['section2'];
        $SECCION3 = $_POST['section3'];
        $SECCION4 = $_POST['section4'];

        $consulta = "INSERT INTO VAADINWEB.HUMDESEMPENO (IDDESEMPENO, EMPLEADO_ID, CONFIRMADO, PREGUNTA1, PREGUNTA2, PREGUNTA3, PREGUNTA4, EMPL_CONFIR_ID) 
        VALUES ($CNS, $IDEMPLEADOEVALUADO, 1, '$SECCION1', '$SECCION2', '$SECCION3', '$SECCION4',  $IDEMPLEADO)";

        $resultado = $this->ejecutarConsulta($consulta);

        if ($resultado) {
            echo "<script>
                    window.location.href = '" . APP_URL . "evaluarList/';
                  </script>";
        } else {
            echo "<script>
                    window.history.back();
                 </script>";
        }
    }

    public function registrarEvaluacionDesempenoControlador() {
        try {
            $IDEMPLEADO = $_SESSION['idempleado'];
            $PREGUNTA1 = $_POST['pregunta1'];
            $PREGUNTA2 = $_POST['pregunta2'];
            $PREGUNTA3 = $_POST['pregunta3'];
            $PREGUNTA4 = $_POST['pregunta4'];
            $PREGUNTA5 = $_POST['pregunta5'];
            $PREGUNTA6 = $_POST['pregunta6'];
            $PREGUNTA7 = $_POST['pregunta7'];
            $PREGUNTA8 = $_POST['pregunta8'];
            $PREGUNTA9 = $_POST['pregunta9'];
            $PREGUNTA10 = $_POST['pregunta10'];
            $PREGUNTA11 = $_POST['pregunta11'];
    
            // Obtener el próximo IDAUTOEVALUACION
            $idautoevaluacion = "SELECT NVL(MAX(IDAUTOEVALUACION), 0) + 1 AS IDAUTOEVALUACION FROM VAADINWEB.HUMAUTOEVALUACION";
    
            $conexion = $this->conectar();
            $queryMaxId = oci_parse($conexion, $idautoevaluacion);
            oci_execute($queryMaxId);
            $resultadoMaxId = oci_fetch_assoc($queryMaxId);
            $idevaluacion = $resultadoMaxId['IDAUTOEVALUACION'];
    
            // Corrección en la consulta SQL (se eliminó el paréntesis extra)
            $consulta = "INSERT INTO VAADINWEB.HUMAUTOEVALUACION 
                (IDAUTOEVALUACION, EMPLEADO_ID, CONFIRMADO, PREGUNTA1, PREGUNTA2, PREGUNTA3, PREGUNTA4, PREGUNTA5, PREGUNTA6, PREGUNTA7, PREGUNTA8, PREGUNTA9, PREGUNTA10, PREGUNTA11, FECHAREALIZA)
                VALUES (:id, :empleadoconfirma, 1, :pregunta1, :pregunta2, :pregunta3, :pregunta4, :pregunta5, :pregunta6, :pregunta7, :pregunta8, :pregunta9, :pregunta10, :pregunta11, SYSDATE)";
    
            // Preparar la consulta
            $queryInsert = oci_parse($conexion, $consulta);
    
            // Vincular los parámetros
            oci_bind_by_name($queryInsert, ":id", $idevaluacion);
            oci_bind_by_name($queryInsert, ":empleadoconfirma", $IDEMPLEADO);
            oci_bind_by_name($queryInsert, ":pregunta1", $PREGUNTA1);
            oci_bind_by_name($queryInsert, ":pregunta2", $PREGUNTA2);
            oci_bind_by_name($queryInsert, ":pregunta3", $PREGUNTA3);
            oci_bind_by_name($queryInsert, ":pregunta4", $PREGUNTA4);
            oci_bind_by_name($queryInsert, ":pregunta5", $PREGUNTA5);
            oci_bind_by_name($queryInsert, ":pregunta6", $PREGUNTA6);
            oci_bind_by_name($queryInsert, ":pregunta7", $PREGUNTA7);
            oci_bind_by_name($queryInsert, ":pregunta8", $PREGUNTA8);
            oci_bind_by_name($queryInsert, ":pregunta9", $PREGUNTA9);
            oci_bind_by_name($queryInsert, ":pregunta10", $PREGUNTA10);
            oci_bind_by_name($queryInsert, ":pregunta11", $PREGUNTA11);

            // Ejecutar la consulta
            $resultado = oci_execute($queryInsert, OCI_COMMIT_ON_SUCCESS);

            if ($resultado) {
                echo "<script>
                        window.location.href = '" . APP_URL . "evaluarList/';
                      </script>";
            } else {
                echo "<script>
                        window.history.back();
                     </script>";
            }
    
        } catch (\Exception $e) {
            error_log("Error en evaluarController: " . $e->getMessage());
            $this->mostrarAlerta('error', 'Error inesperado', 'No se logró guardar la autoevaluación, por favor intente nuevamente.');
        }
    }


    public function registrarEvaluacionLiderControlador(){
        try{
            $IDEMPLEADO = $_POST['empleadoevaluado'];
            $IDEMPLEADOCONFIRMA = $_SESSION['idempleado'];
            $PREGUNTA1 = $_POST['pregunta1'];
            $PREGUNTA2 = $_POST['pregunta2'];
            $PREGUNTA3 = $_POST['pregunta3'];
            $PREGUNTA4 = $_POST['pregunta4'];
            $PREGUNTA5 = $_POST['pregunta5'];
            $PREGUNTA6 = $_POST['pregunta6'];
            $PREGUNTA7 = $_POST['pregunta7'];
            $PREGUNTA8 = $_POST['pregunta8'];

            // Obtener el próximo IDAUTOEVALUACION
            $idautoevaluacion = "SELECT NVL(MAX(IDAUTOLIDER), 0) + 1 AS IDAUTOLIDER FROM VAADINWEB.HUMAUTOEVALUACIONLIDER";

            $conexion = $this->conectar();
            $queryMaxId = oci_parse($conexion, $idautoevaluacion);
            oci_execute($queryMaxId);
            $resultadoMaxId = oci_fetch_assoc($queryMaxId);
            $idevaluacion = $resultadoMaxId['IDAUTOLIDER'];

            // Corrección en la consulta SQL (se eliminó el paréntesis extra)
            $consulta = "INSERT INTO VAADINWEB.HUMAUTOEVALUACIONLIDER 
            (IDAUTOLIDER, EMPLEADO_ID, EMPLEADO_CONFIRMA, CONFIRMADO, PREGUNTA1, PREGUNTA2, PREGUNTA3, PREGUNTA4, PREGUNTA5, PREGUNTA6, PREGUNTA7, PREGUNTA8, FECHACONFIRMA)
            VALUES (:id, :empleadoevaluado, :empleadoconfirma, 1, :pregunta1, :pregunta2, :pregunta3, :pregunta4, :pregunta5, :pregunta6, :pregunta7, :pregunta8, SYSDATE)";

            // Preparar la consulta
            $queryInsert = oci_parse($conexion, $consulta);

            // Vincular los parámetros
            oci_bind_by_name($queryInsert, ":id", $idevaluacion);
            oci_bind_by_name($queryInsert, ":empleadoevaluado", $IDEMPLEADO);
            oci_bind_by_name($queryInsert, ":empleadoconfirma", $IDEMPLEADOCONFIRMA);
            oci_bind_by_name($queryInsert, ":pregunta1", $PREGUNTA1);
            oci_bind_by_name($queryInsert, ":pregunta2", $PREGUNTA2);
            oci_bind_by_name($queryInsert, ":pregunta3", $PREGUNTA3);
            oci_bind_by_name($queryInsert, ":pregunta4", $PREGUNTA4);
            oci_bind_by_name($queryInsert, ":pregunta5", $PREGUNTA5);
            oci_bind_by_name($queryInsert, ":pregunta6", $PREGUNTA6);
            oci_bind_by_name($queryInsert, ":pregunta7", $PREGUNTA7);
            oci_bind_by_name($queryInsert, ":pregunta8", $PREGUNTA8);

            // Ejecutar la consulta
            $resultado = oci_execute($queryInsert, OCI_COMMIT_ON_SUCCESS);

            if ($resultado) {
                echo "<script>
                        window.location.href = '" . APP_URL . "evaluarList/';
                        </script>";
            } else {
                echo "<script>
                        window.history.back();
                      </script>";
            }

        } catch (\Exception $e) {
            error_log("Error en evaluarController: " . $e->getMessage());
            $this->mostrarAlerta('error', 'Error inesperado', 'No se logró guardar la evaluación del lider, por favor intente nuevamente.');
        }
    }

    public function registrarEvaluacionColaboradorControlador(){
        try{
            $IDEMPLEADO = $_POST['empleadoevaluado'];
            $IDEMPLEADOCONFIRMA = $_SESSION['idempleado'];
            $PREGUNTA1 = $_POST['pregunta1'];
            $PREGUNTA2 = $_POST['pregunta2'];
            $PREGUNTA3 = $_POST['pregunta3'];
            $PREGUNTA4 = $_POST['pregunta4'];
            $PREGUNTA5 = $_POST['pregunta5'];
            $PREGUNTA6 = $_POST['pregunta6'];
            $PREGUNTA7 = $_POST['pregunta7'];
            $PREGUNTA8 = $_POST['pregunta8'];
            $PREGUNTA9 = $_POST['pregunta9'];
            $PREGUNTA10 = $_POST['pregunta10'];
            $PREGUNTA11 = $_POST['pregunta11'];

            // Obtener el próximo IDAUTOEVALUACION
            $idautoevaluacion = "SELECT NVL(MAX(IDEVACOLABORADOR), 0) + 1 AS IDEVACOLABORADOR FROM VAADINWEB.HUMEVALUACIONCOLABO";

            $conexion = $this->conectar();
            $queryMaxId = oci_parse($conexion, $idautoevaluacion);
            oci_execute($queryMaxId);
            $resultadoMaxId = oci_fetch_assoc($queryMaxId);
            $idevaluacion = $resultadoMaxId['IDEVACOLABORADOR'];

            // Corrección en la consulta SQL (se eliminó el paréntesis extra)
            $consulta = "INSERT INTO VAADINWEB.HUMEVALUACIONCOLABO 
            (IDEVACOLABORADOR, EMPLEADO_ID, EMPLEADO_CONFIRMA, CONFIRMADO, PREGUNTA1, PREGUNTA2, PREGUNTA3, PREGUNTA4, PREGUNTA5, PREGUNTA6, PREGUNTA7, PREGUNTA8, PREGUNTA9, PREGUNTA10, PREGUNTA11, FECHACONFIRMA)
            VALUES (:id, :empleadoevaluado, :empleadoconfirma, 1, :pregunta1, :pregunta2, :pregunta3, :pregunta4, :pregunta5, :pregunta6, :pregunta7, :pregunta8, :pregunta9, :pregunta10, :pregunta11, SYSDATE)";

            // Preparar la consulta
            $queryInsert = oci_parse($conexion, $consulta);

            // Vincular los parámetros
            oci_bind_by_name($queryInsert, ":id", $idevaluacion);
            oci_bind_by_name($queryInsert, ":empleadoevaluado", $IDEMPLEADO);
            oci_bind_by_name($queryInsert, ":empleadoconfirma", $IDEMPLEADOCONFIRMA);
            oci_bind_by_name($queryInsert, ":pregunta1", $PREGUNTA1);
            oci_bind_by_name($queryInsert, ":pregunta2", $PREGUNTA2);
            oci_bind_by_name($queryInsert, ":pregunta3", $PREGUNTA3);
            oci_bind_by_name($queryInsert, ":pregunta4", $PREGUNTA4);
            oci_bind_by_name($queryInsert, ":pregunta5", $PREGUNTA5);
            oci_bind_by_name($queryInsert, ":pregunta6", $PREGUNTA6);
            oci_bind_by_name($queryInsert, ":pregunta7", $PREGUNTA7);
            oci_bind_by_name($queryInsert, ":pregunta8", $PREGUNTA8);
            oci_bind_by_name($queryInsert, ":pregunta9", $PREGUNTA9);
            oci_bind_by_name($queryInsert, ":pregunta10", $PREGUNTA10);
            oci_bind_by_name($queryInsert, ":pregunta11", $PREGUNTA11);


            // Ejecutar la consulta
            $resultado = oci_execute($queryInsert, OCI_COMMIT_ON_SUCCESS);

            if ($resultado) {
				// Redirección del servidor: PRG
				header("Location: " . APP_URL . "evaluarList/");
				exit;
            } else {
                // Obtener y registrar el error de Oracle
                $e = oci_error($queryInsert);
                $msg = isset($e['message']) ? $e['message'] : 'Error desconocido OCI';
                // Mostrar alerta con mensaje de Oracle (tercer parámetro)
                $this->mostrarAlerta('error', 'Error inesperado', $msg);
            }

        } catch (\Exception $e) {
            error_log("Error en evaluarController: " . $e->getMessage());
            $this->mostrarAlerta('error', 'Error inesperado', 'No se logró guardar la evaluación del Colaborardor, por favor intente nuevamente.');
        }
    }


    /*--------------------- Función para mostrar alertas ---------------------*/
    private function mostrarAlerta($icon, $title, $text) {
        echo "<script>
            Swal.fire({
                icon: '$icon',
                title: '$title',
                text: '$text'
            });
        </script>";
    }

}
?>