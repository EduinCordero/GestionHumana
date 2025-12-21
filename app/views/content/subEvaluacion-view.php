<?php
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['empleadoevaluado'])) {
        $_SESSION['empleadoevaluado'] = $_POST['empleadoevaluado'];
    }

    $idempleado = isset($_SESSION['empleadoevaluado']) ? $_SESSION['empleadoevaluado'] : '';
?>

<main class="h-screen w-full">
    <div class="container full-container py-5">
        <div class="flex justify-center w-full">
                <form id="evaluationForm" class="FormularioAjax" action="<?php echo APP_URL; ?>app/ajax/formulariosAjax.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="modulo_evaluacion" value="registrarEvaluacionColaborador">
                    <input type="hidden" name="empleadoevaluado" value="<?php echo $idempleado; ?>">

                    <div class="section active" id="section1" style="text-align: center;">
                        <h1 class="md:text-40 text-32 font-bold text-link dark:text-white leading-tight text-center" style="color: #0058af;">Calidez Humana y Servicio con Propósito</h1>
                        <p class="mt-5 text-center w-fit mx-auto py-1 px-2 rounded-md border-2 border-dashed border-border dark:border-darkborder text-sm font-medium justify-center text-lightmuted dark:text-darklink flex items-center flex-wrap gap-1">¿El evaluado refleja cercanía y disposición en su atención, ofreciendo un servicio humanizado que priorice las necesidades y el bienestar de los demás con un compromiso genuino?</p>
                            <div class="options mt-5">
								<div class="option-container">
									<button type="button" class="option-button" onclick="selectOption(this, 'section1', 'Sobresaliente')">Sobresaliente</button>
									<button type="button" class="info-button" onclick="openModal('modalSobresaliente')">?</button>
									<!-- Modal para la opción "Sobresaliente" -->
									<div id="modalSobresaliente" class="modal">
										<div class="modal-content">
											<span class="close" onclick="closeModal('modalSobresaliente')">&times;</span>
											<p>El colaborador supera las expectativas, demuestra un compromiso excepcional con este principio y sus valores, y actúa de manera proactiva, inspirando a otros con su actitud y resultados.</p>
										</div>
									</div>
								</div>
								<div class="option-container">
									<button type="button" class="option-button" onclick="selectOption(this, 'section1', 'Acorde')">Acorde</button>
									<button type="button" class="info-button" onclick="openModal('modalAcorde')">?</button>
								</div>
								<!-- Modal para la opción "Sobresaliente" -->
								<div id="modalAcorde" class="modal">
									<div class="modal-content">
										<span class="close" onclick="closeModal('modalAcorde')">&times;</span>
										<p>El colaborador cumple consistentemente con las expectativas, refleja un alineamiento sólido con el principio y sus valores, y ocasionalmente toma la iniciativa para mejorar y aportar soluciones innovadoras.</p>
									</div>
								</div>
								<div class="option-container">
									<button type="button" class="option-button" onclick="selectOption(this, 'section1', 'Aceptable')">Aceptable</button>
									<button type="button" class="info-button" onclick="openModal('modalAceptable')">?</button>
								</div>
								<!-- Modal para la opción "Sobresaliente" -->
								<div id="modalAceptable" class="modal">
									<div class="modal-content">
										<span class="close" onclick="closeModal('modalAceptable')">&times;</span>
										<p>El colaborador cumple con las expectativas básicas. Aunque actúa acorde al principio y sus valores, hay áreas donde podría ser más proactivo o comprometido.</p>
									</div>
								</div>
								<div class="option-container">
									<button type="button" class="option-button" onclick="selectOption(this, 'section1', 'Necesita Mejorar')">Necesita Mejorar</button>
									<button type="button" class="info-button" onclick="openModal('modalMejorar')">?</button>
								</div>
								<!-- Modal para la opción "Sobresaliente" -->
								<div id="modalMejorar" class="modal">
									<div class="modal-content">
										<span class="close" onclick="closeModal('modalMejorar')">&times;</span>
										<p>El colaborador presenta algunas deficiencias en la vivencia del principio y sus valores. Si bien cumple con algunas expectativas, necesita mejorar en áreas clave para alinearse con la cultura organizacional.</p>
									</div>
								</div>
								<div class="option-container">
									<button type="button" class="option-button" onclick="selectOption(this, 'section1', 'Insuficiente')">Insuficiente</button>
									<button type="button" class="info-button" onclick="openModal('modalInsuficiente')">?</button>
								</div>
								<!-- Modal para la opción "Sobresaliente" -->
								<div id="modalInsuficiente" class="modal">
									<div class="modal-content">
										<span class="close" onclick="closeModal('modalInsuficiente')">&times;</span>
										<p>El colaborador no cumple con las expectativas. Se observan acciones que no reflejan adecuadamente el principio y sus valores, y es necesario un esfuerzo significativo para mejorar su desempeño.</p>
									</div>
								</div>
                            </div>
                            <input type="hidden" name="pregunta1" id="section1-input" required>
                            <div class="sm:flex justify-center gap-4 mt-8">
                                <button type="button" class="next-button" onclick="nextSection(1)">Siguiente</button>
                            </div>
                    </div>

                    <div class="section" id="section2" style="text-align: center;">
                        <h1 class="md:text-40 text-32 font-bold text-link dark:text-white leading-tight text-center" style="color: #0058af;">Liderazgo e Integridad en la Acción</h1>
                        <p class="mt-5 text-center w-fit mx-auto py-1 px-2 rounded-md border-2 border-dashed border-border dark:border-darkborder text-sm font-medium justify-center text-lightmuted dark:text-darklink flex items-center flex-wrap gap-1">¿El evaluado actúa con integridad al ser confiable, honesto y ético, liderando con el ejemplo para fomentar un ambiente de respeto y confianza?</p>
                        <div class="options mt-5">
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section2', 'Sobresaliente')">Sobresaliente</button>
								<button type="button" class="info-button" onclick="openModal('modalSobresaliente2')">?</button>
							</div>
							<!-- Modal para la opción "Sobresaliente" -->
							<div id="modalSobresaliente2" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalSobresaliente2')">&times;</span>
									<p>El colaborador supera las expectativas, demuestra un compromiso excepcional con este principio y sus valores, y actúa de manera proactiva, inspirando a otros con su actitud y resultados.</p>
								</div>
							</div>
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section2', 'Acorde')">Acorde</button>
								<button type="button" class="info-button" onclick="openModal('modalAcorde2')">?</button>
							</div>
							<!-- Modal para la opción "Acorde" -->
							<div id="modalAcorde2" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalAcorde2')">&times;</span>
									<p>El colaborador cumple consistentemente con las expectativas, refleja un alineamiento sólido con el principio y sus valores, y ocasionalmente toma la iniciativa para mejorar y aportar soluciones innovadoras.</p>
								</div>
							</div>
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section2', 'Aceptable')">Aceptable</button>
								<button type="button" class="info-button" onclick="openModal('modalAceptable2')">?</button>
							</div>
							<!-- Modal para la opción "Aceptable" -->
							<div id="modalAceptable2" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalAceptable2')">&times;</span>
									<p>El colaborador cumple con las expectativas básicas. Aunque actúa acorde al principio y sus valores, hay áreas donde podría ser más proactivo o comprometido.</p>
								</div>
							</div>
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section2', 'Necesita Mejorar')">Necesita Mejorar</button>
								<button type="button" class="info-button" onclick="openModal('modalMejorar2')">?</button>
							</div>
							<!-- Modal para la opción "Mejorar" -->
							<div id="modalMejorar2" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalMejorar2')">&times;</span>
									<p>El colaborador presenta algunas deficiencias en la vivencia del principio y sus valores. Si bien cumple con algunas expectativas, necesita mejorar en áreas clave para alinearse con la cultura organizacional.</p>
								</div>
							</div>
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section2', 'Insuficiente')">Insuficiente</button>
								<button type="button" class="info-button" onclick="openModal('modalInsuficiente2')">?</button>
							</div>
							<!-- Modal para la opción "Insuficiente" -->
							<div id="modalInsuficiente2" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalInsuficiente2')">&times;</span>
									<p>El colaborador no cumple con las expectativas. Se observan acciones que no reflejan adecuadamente el principio y sus valores, y es necesario un esfuerzo significativo para mejorar su desempeño.</p>
								</div>
							</div>
                        </div>
                        <input type="hidden" name="pregunta2" id="section2-input" required>
                        <div class="sm:flex justify-center gap-4 mt-8">
                            <button type="button" class="back-button" onclick="previousSection(2)">Anterior</button>
                            <button type="button" class="next-button" onclick="nextSection(2)">Siguiente</button>
                        </div>
                    </div>

                    <div class="section" id="section3" style="text-align: center;"> 
                        <h1 class="md:text-40 text-32 font-bold text-link dark:text-white leading-tight text-center" style="color: #0058af;">Competitividad, Innovación y Adaptabilidad</h1>
                        <p class="mt-5 text-center w-fit mx-auto py-1 px-2 rounded-md border-2 border-dashed border-border dark:border-darkborder text-sm font-medium justify-center text-lightmuted dark:text-darklink flex items-center flex-wrap gap-1">¿El evaluado muestra iniciativa para proponer mejoras, adaptarse rápidamente a los cambios y ofrecer soluciones innovadoras que impulsen la excelencia?</p>
                        <div class="options mt-5">
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section3', 'Sobresaliente')">Sobresaliente</button>
								<button type="button" class="info-button" onclick="openModal('modalSobresaliente3')">?</button>
							</div>
							<!-- Modal para la opción "Sobresaliente" -->
							<div id="modalSobresaliente3" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalSobresaliente3')">&times;</span>
									<p>El colaborador supera las expectativas, demuestra un compromiso excepcional con este principio y sus valores, y actúa de manera proactiva, inspirando a otros con su actitud y resultados.</p>
								</div>
							</div>
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section3', 'Acorde')">Acorde</button>
								<button type="button" class="info-button" onclick="openModal('modalAcorde3')">?</button>
							</div>
							<!-- Modal para la opción "Sobresaliente" -->
							<div id="modalAcorde3" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalAcorde3')">&times;</span>
									<p>El colaborador cumple consistentemente con las expectativas, refleja un alineamiento sólido con el principio y sus valores, y ocasionalmente toma la iniciativa para mejorar y aportar soluciones innovadoras.</p>
								</div>
							</div>
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section3', 'Aceptable')">Aceptable</button>
								<button type="button" class="info-button" onclick="openModal('modalAceptable3')">?</button>
							</div>
							<!-- Modal para la opción "Sobresaliente" -->
							<div id="modalAceptable3" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalAceptable3')">&times;</span>
									<p>El colaborador cumple con las expectativas básicas. Aunque actúa acorde al principio y sus valores, hay áreas donde podría ser más proactivo o comprometido.</p>
								</div>
							</div>
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section3', 'Necesita Mejorar')">Necesita Mejorar</button>
								<button type="button" class="info-button" onclick="openModal('modalMejorar3')">?</button>
							</div>
							<!-- Modal para la opción "Sobresaliente" -->
							<div id="modalMejorar3" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalMejorar3')">&times;</span>
									<p>El colaborador presenta algunas deficiencias en la vivencia del principio y sus valores. Si bien cumple con algunas expectativas, necesita mejorar en áreas clave para alinearse con la cultura organizacional.</p>
								</div>
							</div>
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section3', 'Insuficiente')">Insuficiente</button>
								<button type="button" class="info-button" onclick="openModal('modalInsuficiente3')">?</button>
							</div>
							<!-- Modal para la opción "Sobresaliente" -->
							<div id="modalInsuficiente3" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalInsuficiente3')">&times;</span>
									<p>El colaborador no cumple con las expectativas. Se observan acciones que no reflejan adecuadamente el principio y sus valores, y es necesario un esfuerzo significativo para mejorar su desempeño.</p>
								</div>
							</div>
                        </div>
                        <input type="hidden" name="pregunta3" id="section3-input" required>
                        <div class="sm:flex justify-center gap-4 mt-8">
                            <button type="button" class="back-button" onclick="previousSection(3)">Anterior</button>
                            <button type="button" class="next-button" onclick="nextSection(3)">Siguiente</button>
                        </div>
                    </div>

                    <div class="section" id="section4" style="text-align: center;">
                        <h1 class="md:text-40 text-32 font-bold text-link dark:text-white leading-tight text-center" style="color: #0058af;">Comunicación Asertiva y Sentido de Pertenencia</h1>
                        <p class="mt-5 text-center w-fit mx-auto py-1 px-2 rounded-md border-2 border-dashed border-border dark:border-darkborder text-sm font-medium justify-center text-lightmuted dark:text-darklink flex items-center flex-wrap gap-1">¿El evaluado fomenta una comunicación abierta y efectiva, participa activamente en las iniciativas institucionales y contribuye con sentido de pertenencia al propósito y cuidado de la institución?</p>
                        <div class="options mt-5">
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section4', 'Sobresaliente')">Sobresaliente</button>
								<button type="button" class="info-button" onclick="openModal('modalSobresaliente4')">?</button>
							</div>
							<!-- Modal para la opción "Sobresaliente" -->
							<div id="modalSobresaliente4" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalSobresaliente4')">&times;</span>
									<p>El colaborador supera las expectativas, demuestra un compromiso excepcional con este principio y sus valores, y actúa de manera proactiva, inspirando a otros con su actitud y resultados.</p>
								</div>
							</div>
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section4', 'Acorde')">Acorde</button>
								<button type="button" class="info-button" onclick="openModal('modalAcorde4')">?</button>
							</div>
							<!-- Modal para la opción "Sobresaliente" -->
							<div id="modalAcorde4" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalAcorde4')">&times;</span>
									<p>El colaborador cumple consistentemente con las expectativas, refleja un alineamiento sólido con el principio y sus valores, y ocasionalmente toma la iniciativa para mejorar y aportar soluciones innovadoras.</p>
								</div>
							</div>
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section4', 'Aceptable')">Aceptable</button>
								<button type="button" class="info-button" onclick="openModal('modalAceptable4')">?</button>
							</div>
							<!-- Modal para la opción "Sobresaliente" -->
							<div id="modalAceptable4" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalAceptable4')">&times;</span>
									<p>El colaborador cumple con las expectativas básicas. Aunque actúa acorde al principio y sus valores, hay áreas donde podría ser más proactivo o comprometido.</p>
								</div>
							</div>
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section4', 'Necesita Mejorar')">Necesita Mejorar</button>
								<button type="button" class="info-button" onclick="openModal('modalMejorar4')">?</button>
							</div>
							<!-- Modal para la opción "Sobresaliente" -->
							<div id="modalMejorar4" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalMejorar4')">&times;</span>
									<p>El colaborador presenta algunas deficiencias en la vivencia del principio y sus valores. Si bien cumple con algunas expectativas, necesita mejorar en áreas clave para alinearse con la cultura organizacional.</p>
								</div>
							</div>
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section4', 'Insuficiente')">Insuficiente</button>
								<button type="button" class="info-button" onclick="openModal('modalInsuficiente4')">?</button>
							</div>
							<!-- Modal para la opción "Sobresaliente" -->
							<div id="modalInsuficiente4" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalInsuficiente4')">&times;</span>
									<p>El colaborador no cumple con las expectativas. Se observan acciones que no reflejan adecuadamente el principio y sus valores, y es necesario un esfuerzo significativo para mejorar su desempeño.</p>
								</div>
							</div>
                        </div>
                        <input type="hidden" name="pregunta4" id="section4-input" required>
                        <div class="sm:flex justify-center gap-4 mt-8">
                            <button type="button" class="back-button" onclick="previousSection(4)">Anterior</button>
                            <button type="button" class="next-button" onclick="nextSection(4)">Siguiente</button>
                        </div>
                    </div>

                    <div class="section" id="section5" style="text-align: center;"> 
                        <h1 class="md:text-40 text-32 font-bold text-link dark:text-white leading-tight text-center" style="color: #0058af;">Compromiso con la calidad</h1>
                        <p class="mt-5 text-center w-fit mx-auto py-1 px-2 rounded-md border-2 border-dashed border-border dark:border-darkborder text-sm font-medium justify-center text-lightmuted dark:text-darklink flex items-center flex-wrap gap-1">¿De qué manera el evaluado ha contribuido al cumplimiento de los estándares institucionales (políticas, planes, reglamentos y procedimientos), comunicando oportunamente incidentes o no conformidades y asegurando que su trabajo impacte positivamente en la experiencia de nuestros pacientes?</p>
                        <div class="options mt-5">
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section5', 'Sobresaliente')">Sobresaliente</button>
								<button type="button" class="info-button" onclick="openModal('modalSobresaliente5')">?</button>
							</div>
							<!-- Modal para la opción "Sobresaliente" -->
							<div id="modalSobresaliente5" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalSobresaliente5')">&times;</span>
									<p>El colaborador excede consistentemente las expectativas en este criterio, demostrando un compromiso excepcional, creatividad y resultados que trascienden los estándares establecidos. Su desempeño genera impacto positivo y es un referente de excelencia para sus compañeros y el equipo.</p>
								</div>
							</div>
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section5', 'Acorde')">Acorde</button>
								<button type="button" class="info-button" onclick="openModal('modalAcorde5')">?</button>
							</div>
							<!-- Modal para la opción "Sobresaliente" -->
							<div id="modalAcorde5" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalAcorde5')">&times;</span>
									<p>El colaborador cumple con las expectativas y estándares definidos, manteniendo un desempeño consistente y adecuado en sus responsabilidades. Contribuye de manera efectiva al logro de los objetivos organizacionales.</p>
								</div>
							</div>
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section5', 'Aceptable')">Aceptable</button>
								<button type="button" class="info-button" onclick="openModal('modalAceptable5')">?</button>
							</div>
							<!-- Modal para la opción "Sobresaliente" -->
							<div id="modalAceptable5" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalAceptable5')">&times;</span>
									<p>El colaborador cumple con los aspectos básicos del criterio, pero su desempeño podría beneficiarse de un mayor enfoque, compromiso o consistencia para alcanzar su máximo potencial.</p>
								</div>
							</div>
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section5', 'Necesita Mejorar')">Necesita Mejorar</button>
								<button type="button" class="info-button" onclick="openModal('modalMejorar5')">?</button>
							</div>
							<!-- Modal para la opción "Sobresaliente" -->
							<div id="modalMejorar5" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalMejorar5')">&times;</span>
									<p>El desempeño del colaborador está por debajo de lo esperado, lo que afecta la calidad de los resultados o el cumplimiento de sus responsabilidades. Se requiere un plan de acción para abordar las áreas de mejora.</p>
								</div>
							</div>
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section5', 'Insuficiente')">Insuficiente</button>
								<button type="button" class="info-button" onclick="openModal('modalInsuficiente5')">?</button>
							</div>
							<!-- Modal para la opción "Sobresaliente" -->
							<div id="modalInsuficiente5" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalInsuficiente5')">&times;</span>
									<p>El desempeño del colaborador es significativamente inferior a los estándares esperados, con un impacto negativo en el equipo o los resultados organizacionales. Se requiere intervención inmediata para corregir la situación.</p>
								</div>
							</div>
                        </div>
                        <input type="hidden" name="pregunta5" id="section5-input" required>
                        <div class="sm:flex justify-center gap-4 mt-8">
                            <button type="button" class="back-button" onclick="previousSection(5)">Anterior</button>
                            <button type="button" class="next-button" onclick="nextSection(5)">Siguiente</button>
                        </div>
                    </div>

                    <div class="section" id="section6" style="text-align: center;"> 
                        <h1 class="md:text-40 text-32 font-bold text-link dark:text-white leading-tight text-center" style="color: #0058af;">Compromiso institucional y cumplimiento de normas internas</h1>
                        <p class="mt-5 text-center w-fit mx-auto py-1 px-2 rounded-md border-2 border-dashed border-border dark:border-darkborder text-sm font-medium justify-center text-lightmuted dark:text-darklink flex items-center flex-wrap gap-1">¿De qué manera el evaluado se convierte en un modelo de respeto y pertenencia, inspirando con su puntualidad, cuidado de la imagen profesional y compromiso con las normas, mientras cultiva un entorno de armonía y excelencia?</p>
                        <div class="options mt-5">
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section6', 'Sobresaliente')">Sobresaliente</button>
								<button type="button" class="info-button" onclick="openModal('modalSobresaliente6')">?</button>
							</div>
							<!-- Modal para la opción "Sobresaliente" -->
							<div id="modalSobresaliente6" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalSobresaliente6')">&times;</span>
									<p>El colaborador excede consistentemente las expectativas en este criterio, demostrando un compromiso excepcional, creatividad y resultados que trascienden los estándares establecidos. Su desempeño genera impacto positivo y es un referente de excelencia para sus compañeros y el equipo.</p>
								</div>
							</div>
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section6', 'Acorde')">Acorde</button>
								<button type="button" class="info-button" onclick="openModal('modalAcorde6')">?</button>
							</div>
							<!-- Modal para la opción "Sobresaliente" -->
							<div id="modalAcorde6" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalAcorde6')">&times;</span>
									<p>El colaborador cumple con las expectativas y estándares definidos, manteniendo un desempeño consistente y adecuado en sus responsabilidades. Contribuye de manera efectiva al logro de los objetivos organizacionales.</p>
								</div>
							</div>
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section6', 'Aceptable')">Aceptable</button>
								<button type="button" class="info-button" onclick="openModal('modalAceptable6')">?</button>
							</div>
							<!-- Modal para la opción "Sobresaliente" -->
							<div id="modalAceptable6" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalAceptable6')">&times;</span>
									<p>El colaborador cumple con los aspectos básicos del criterio, pero su desempeño podría beneficiarse de un mayor enfoque, compromiso o consistencia para alcanzar su máximo potencial.</p>
								</div>
							</div>
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section6', 'Necesita Mejorar')">Necesita Mejorar</button>
								<button type="button" class="info-button" onclick="openModal('modalMejorar6')">?</button>
							</div>
							<!-- Modal para la opción "Sobresaliente" -->
							<div id="modalMejorar6" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalMejorar6')">&times;</span>
									<p>El desempeño del colaborador está por debajo de lo esperado, lo que afecta la calidad de los resultados o el cumplimiento de sus responsabilidades. Se requiere un plan de acción para abordar las áreas de mejora.</p>
								</div>
							</div>
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section6', 'Insuficiente')">Insuficiente</button>
								<button type="button" class="info-button" onclick="openModal('modalInsuficiente6')">?</button>
							</div>
							<!-- Modal para la opción "Sobresaliente" -->
							<div id="modalInsuficiente6" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalInsuficiente6')">&times;</span>
									<p>El desempeño del colaborador es significativamente inferior a los estándares esperados, con un impacto negativo en el equipo o los resultados organizacionales. Se requiere intervención inmediata para corregir la situación.</p>
								</div>
							</div>
                        </div>
                        <input type="hidden" name="pregunta6" id="section6-input" required>
                        <div class="sm:flex justify-center gap-4 mt-8">
                            <button type="button" class="back-button" onclick="previousSection(6)">Anterior</button>
                            <button type="button" class="next-button" onclick="nextSection(6)">Siguiente</button>
                        </div>
                    </div>

                    <div class="section" id="section7" style="text-align: center;"> 
                        <h1 class="md:text-40 text-32 font-bold text-link dark:text-white leading-tight text-center" style="color: #0058af;">Participación y formación continua</h1>
                        <p class="mt-5 text-center w-fit mx-auto py-1 px-2 rounded-md border-2 border-dashed border-border dark:border-darkborder text-sm font-medium justify-center text-lightmuted dark:text-darklink flex items-center flex-wrap gap-1">¿En qué grado el colaborador demuestra su pasión por aprender y enseñar, integrando conocimientos adquiridos en cada interacción y potenciando la cooperación en su equipo para crear soluciones que trasciendan en nuestra misión de humanización y calidad?</p>
                        <div class="options mt-5">
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section7', 'Sobresaliente')">Sobresaliente</button>
								<button type="button" class="info-button" onclick="openModal('modalSobresaliente7')">?</button>
							</div>
							<!-- Modal para la opción "Sobresaliente" -->
							<div id="modalSobresaliente7" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalSobresaliente7')">&times;</span>
									<p>El colaborador excede consistentemente las expectativas en este criterio, demostrando un compromiso excepcional, creatividad y resultados que trascienden los estándares establecidos. Su desempeño genera impacto positivo y es un referente de excelencia para sus compañeros y el equipo.</p>
								</div>
							</div>
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section7', 'Acorde')">Acorde</button>
								<button type="button" class="info-button" onclick="openModal('modalAcorde7')">?</button>
							</div>
							<!-- Modal para la opción "Sobresaliente" -->
							<div id="modalAcorde7" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalAcorde7')">&times;</span>
									<p>El colaborador cumple con las expectativas y estándares definidos, manteniendo un desempeño consistente y adecuado en sus responsabilidades. Contribuye de manera efectiva al logro de los objetivos organizacionales.</p>
								</div>
							</div>
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section7', 'Aceptable')">Aceptable</button>
								<button type="button" class="info-button" onclick="openModal('modalAceptable7')">?</button>
							</div>
							<!-- Modal para la opción "Sobresaliente" -->
							<div id="modalAceptable7" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalAceptable7')">&times;</span>
									<p>El colaborador cumple con los aspectos básicos del criterio, pero su desempeño podría beneficiarse de un mayor enfoque, compromiso o consistencia para alcanzar su máximo potencial.</p>
								</div>
							</div>
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section7', 'Necesita Mejorar')">Necesita Mejorar</button>
								<button type="button" class="info-button" onclick="openModal('modalMejorar7')">?</button>
							</div>
							<!-- Modal para la opción "Sobresaliente" -->
							<div id="modalMejorar7" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalMejorar7')">&times;</span>
									<p>El desempeño del colaborador está por debajo de lo esperado, lo que afecta la calidad de los resultados o el cumplimiento de sus responsabilidades. Se requiere un plan de acción para abordar las áreas de mejora.</p>
								</div>
							</div>
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section7', 'Insuficiente')">Insuficiente</button>
								<button type="button" class="info-button" onclick="openModal('modalInsuficiente7')">?</button>
							</div>
							<!-- Modal para la opción "Sobresaliente" -->
							<div id="modalInsuficiente7" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalInsuficiente7')">&times;</span>
									<p>El desempeño del colaborador es significativamente inferior a los estándares esperados, con un impacto negativo en el equipo o los resultados organizacionales. Se requiere intervención inmediata para corregir la situación.</p>
								</div>
							</div>
                        </div>
                        <input type="hidden" name="pregunta7" id="section7-input" required>
                        <div class="sm:flex justify-center gap-4 mt-8">
                            <button type="button" class="back-button" onclick="previousSection(7)">Anterior</button>
                            <button type="button" class="next-button" onclick="nextSection(7)">Siguiente</button>
                        </div>
                    </div>

                    <div class="section" id="section8" style="text-align: center;"> 
                        <h1 class="md:text-40 text-32 font-bold text-link dark:text-white leading-tight text-center" style="color: #0058af;">Gestión de Seguridad y Salud en el Trabajo (SST)</h1>
                        <p class="mt-5 text-center w-fit mx-auto py-1 px-2 rounded-md border-2 border-dashed border-border dark:border-darkborder text-sm font-medium justify-center text-lightmuted dark:text-darklink flex items-center flex-wrap gap-1">¿Cómo evalúas el rol del colaborador en la promoción de un entorno seguro, cumpliendo con las normas de bioseguridad, participando en programas preventivos y actuando de manera efectiva frente a situaciones de riesgo o emergencia?</p>
                        <div class="options mt-5">
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section8', 'Sobresaliente')">Sobresaliente</button>
								<button type="button" class="info-button" onclick="openModal('modalSobresaliente8')">?</button>
							</div>
							<!-- Modal para la opción "Sobresaliente" -->
							<div id="modalSobresaliente8" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalSobresaliente8')">&times;</span>
									<p>El colaborador excede consistentemente las expectativas en este criterio, demostrando un compromiso excepcional, creatividad y resultados que trascienden los estándares establecidos. Su desempeño genera impacto positivo y es un referente de excelencia para sus compañeros y el equipo.</p>
								</div>
							</div>
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section8', 'Acorde')">Acorde</button>
								<button type="button" class="info-button" onclick="openModal('modalAcorde8')">?</button>
							</div>
							<!-- Modal para la opción "Sobresaliente" -->
							<div id="modalAcorde8" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalAcorde8')">&times;</span>
									<p>El colaborador cumple con las expectativas y estándares definidos, manteniendo un desempeño consistente y adecuado en sus responsabilidades. Contribuye de manera efectiva al logro de los objetivos organizacionales.</p>
								</div>
							</div>
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section8', 'Aceptable')">Aceptable</button>
								<button type="button" class="info-button" onclick="openModal('modalAceptable8')">?</button>
							</div>
							<!-- Modal para la opción "Sobresaliente" -->
							<div id="modalAceptable8" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalAceptable8')">&times;</span>
									<p>El colaborador cumple con los aspectos básicos del criterio, pero su desempeño podría beneficiarse de un mayor enfoque, compromiso o consistencia para alcanzar su máximo potencial.</p>
								</div>
							</div>
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section8', 'Necesita Mejorar')">Necesita Mejorar</button>
								<button type="button" class="info-button" onclick="openModal('modalMejorar8')">?</button>
							</div>
							<!-- Modal para la opción "Sobresaliente" -->
							<div id="modalMejorar8" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalMejorar8')">&times;</span>
									<p>El desempeño del colaborador está por debajo de lo esperado, lo que afecta la calidad de los resultados o el cumplimiento de sus responsabilidades. Se requiere un plan de acción para abordar las áreas de mejora.</p>
								</div>
							</div>
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section8', 'Insuficiente')">Insuficiente</button>
								<button type="button" class="info-button" onclick="openModal('modalInsuficiente8')">?</button>
							</div>
							<!-- Modal para la opción "Sobresaliente" -->
							<div id="modalInsuficiente8" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalInsuficiente8')">&times;</span>
									<p>El desempeño del colaborador es significativamente inferior a los estándares esperados, con un impacto negativo en el equipo o los resultados organizacionales. Se requiere intervención inmediata para corregir la situación.</p>
								</div>
							</div>
                        </div>
                        <input type="hidden" name="pregunta8" id="section8-input" required>
                        <div class="sm:flex justify-center gap-4 mt-8">
                            <button type="button" class="back-button" onclick="previousSection(8)">Anterior</button>
                            <button type="button" class="next-button" onclick="nextSection(8)">Siguiente</button>
                        </div>
                    </div>

                    <div class="section" id="section9" style="text-align: center;"> 
                        <h1 class="md:text-40 text-32 font-bold text-link dark:text-white leading-tight text-center" style="color: #0058af;">Gestión de Relaciones Interpersonales</h1>
                        <p class="mt-5 text-center w-fit mx-auto py-1 px-2 rounded-md border-2 border-dashed border-border dark:border-darkborder text-sm font-medium justify-center text-lightmuted dark:text-darklink flex items-center flex-wrap gap-1">En su día a día, el colaborador demuestra la capacidad de construir puentes de confianza y comunicación que fortalecen las relaciones con compañeros, líderes y otras partes interesadas. ¿Cómo calificarías su habilidad para resolver conflictos de forma creativa y generar un entorno laboral armónico que impulse la cultura organizacional?</p>
                        <div class="options mt-5">
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section9', 'Sobresaliente')">Sobresaliente</button>
								<button type="button" class="info-button" onclick="openModal('modalSobresaliente9')">?</button>
							</div>
							<!-- Modal para la opción "Sobresaliente" -->
							<div id="modalSobresaliente9" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalSobresaliente9')">&times;</span>
									<p>El colaborador excede consistentemente las expectativas en este criterio, demostrando un compromiso excepcional, creatividad y resultados que trascienden los estándares establecidos. Su desempeño genera impacto positivo y es un referente de excelencia para sus compañeros y el equipo.</p>
								</div>
							</div>
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section9', 'Acorde')">Acorde</button>
								<button type="button" class="info-button" onclick="openModal('modalAcorde9')">?</button>
							</div>
							<!-- Modal para la opción "Sobresaliente" -->
							<div id="modalAcorde9" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalAcorde9')">&times;</span>
									<p>El colaborador cumple con las expectativas y estándares definidos, manteniendo un desempeño consistente y adecuado en sus responsabilidades. Contribuye de manera efectiva al logro de los objetivos organizacionales.</p>
								</div>
							</div>
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section9', 'Aceptable')">Aceptable</button>
								<button type="button" class="info-button" onclick="openModal('modalAceptable9')">?</button>
							</div>
							<!-- Modal para la opción "Sobresaliente" -->
							<div id="modalAceptable9" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalAceptable9')">&times;</span>
									<p>El colaborador cumple con los aspectos básicos del criterio, pero su desempeño podría beneficiarse de un mayor enfoque, compromiso o consistencia para alcanzar su máximo potencial.</p>
								</div>
							</div>
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section9', 'Necesita Mejorar')">Necesita Mejorar</button>
								<button type="button" class="info-button" onclick="openModal('modalMejorar9')">?</button>
							</div>
							<!-- Modal para la opción "Sobresaliente" -->
							<div id="modalMejorar9" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalMejorar9')">&times;</span>
									<p>El desempeño del colaborador está por debajo de lo esperado, lo que afecta la calidad de los resultados o el cumplimiento de sus responsabilidades. Se requiere un plan de acción para abordar las áreas de mejora.</p>
								</div>
							</div>
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section9', 'Insuficiente')">Insuficiente</button>
								<button type="button" class="info-button" onclick="openModal('modalInsuficiente9')">?</button>
							</div>
							<!-- Modal para la opción "Sobresaliente" -->
							<div id="modalInsuficiente9" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalInsuficiente9')">&times;</span>
									<p>El desempeño del colaborador es significativamente inferior a los estándares esperados, con un impacto negativo en el equipo o los resultados organizacionales. Se requiere intervención inmediata para corregir la situación.</p>
								</div>
							</div>
                        </div>
                        <input type="hidden" name="pregunta9" id="section9-input" required>
                        <div class="sm:flex justify-center gap-4 mt-8">
                            <button type="button" class="back-button" onclick="previousSection(9)">Anterior</button>
                            <button type="button" class="next-button" onclick="nextSection(9)">Siguiente</button>
                        </div>
                    </div>

                    <div class="section" id="section10" style="text-align: center;"> 
                        <h1 class="md:text-40 text-32 font-bold text-link dark:text-white leading-tight text-center" style="color: #0058af;">Eficacia en la Ejecución de Responsabilidades y Contribución a los Resultados</h1>
                        <p class="mt-5 text-center w-fit mx-auto py-1 px-2 rounded-md border-2 border-dashed border-border dark:border-darkborder text-sm font-medium justify-center text-lightmuted dark:text-darklink flex items-center flex-wrap gap-1">¿De qué manera el colaborador traduce sus responsabilidades en acciones que generan resultados tangibles, demostrando puntualidad, precisión y un impacto significativo en el cumplimiento de las metas organizacionales?</p>
                        <div class="options mt-5">
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section10', 'Sobresaliente')">Sobresaliente</button>
								<button type="button" class="info-button" onclick="openModal('modalSobresaliente10')">?</button>
							</div>
							<!-- Modal para la opción "Sobresaliente" -->
							<div id="modalSobresaliente10" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalSobresaliente10')">&times;</span>
									<p>El colaborador excede consistentemente las expectativas en este criterio, demostrando un compromiso excepcional, creatividad y resultados que trascienden los estándares establecidos. Su desempeño genera impacto positivo y es un referente de excelencia para sus compañeros y el equipo.</p>
								</div>
							</div>
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section10', 'Acorde')">Acorde</button>
								<button type="button" class="info-button" onclick="openModal('modalAcorde10')">?</button>
							</div>
							<!-- Modal para la opción "Sobresaliente" -->
							<div id="modalAcorde10" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalAcorde10')">&times;</span>
									<p>El colaborador cumple con las expectativas y estándares definidos, manteniendo un desempeño consistente y adecuado en sus responsabilidades. Contribuye de manera efectiva al logro de los objetivos organizacionales.</p>
								</div>
							</div>
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section10', 'Aceptable')">Aceptable</button>
								<button type="button" class="info-button" onclick="openModal('modalAceptable10')">?</button>
							</div>
							<!-- Modal para la opción "Sobresaliente" -->
							<div id="modalAceptable10" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalAceptable10')">&times;</span>
									<p>El colaborador cumple con los aspectos básicos del criterio, pero su desempeño podría beneficiarse de un mayor enfoque, compromiso o consistencia para alcanzar su máximo potencial.</p>
								</div>
							</div>
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section10', 'Necesita Mejorar')">Necesita Mejorar</button>
								<button type="button" class="info-button" onclick="openModal('modalMejorar10')">?</button>
							</div>
							<!-- Modal para la opción "Sobresaliente" -->
							<div id="modalMejorar10" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalMejorar10')">&times;</span>
									<p>El desempeño del colaborador está por debajo de lo esperado, lo que afecta la calidad de los resultados o el cumplimiento de sus responsabilidades. Se requiere un plan de acción para abordar las áreas de mejora.</p>
								</div>
							</div>
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section10', 'Insuficiente')">Insuficiente</button>
								<button type="button" class="info-button" onclick="openModal('modalInsuficiente10')">?</button>
							</div>
							<!-- Modal para la opción "Sobresaliente" -->
							<div id="modalInsuficiente10" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalInsuficiente10')">&times;</span>
									<p>El desempeño del colaborador es significativamente inferior a los estándares esperados, con un impacto negativo en el equipo o los resultados organizacionales. Se requiere intervención inmediata para corregir la situación.</p>
								</div>
							</div>
                        </div>
                        <input type="hidden" name="pregunta10" id="section10-input" required>
                        <div class="sm:flex justify-center gap-4 mt-8">
                            <button type="button" class="back-button" onclick="previousSection(10)">Anterior</button>
                            <button type="button" class="next-button" onclick="nextSection(10)">Siguiente</button>
                        </div>
                    </div>

                    <div class="section" id="section11" style="text-align: center;"> 
                        <h1 class="md:text-40 text-32 font-bold text-link dark:text-white leading-tight text-center" style="color: #0058af;">Gestión Eficiente del Tiempo y los Recursos</h1>
                        <p class="mt-5 text-center w-fit mx-auto py-1 px-2 rounded-md border-2 border-dashed border-border dark:border-darkborder text-sm font-medium justify-center text-lightmuted dark:text-darklink flex items-center flex-wrap gap-1">En su rol, el colaborador organiza su tiempo y recursos con una visión estratégica, priorizando tareas clave y minimizando el desperdicio. ¿Cómo evaluarías su capacidad para cumplir plazos establecidos, optimizar recursos disponibles y entregar resultados alineados con las demandas del cargo?</p>
                        <div class="options mt-5">
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section11', 'Sobresaliente')">Sobresaliente</button>
								<button type="button" class="info-button" onclick="openModal('modalSobresaliente11')">?</button>
							</div>
							<!-- Modal para la opción "Sobresaliente" -->
							<div id="modalSobresaliente11" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalSobresaliente11')">&times;</span>
									<p>El colaborador excede consistentemente las expectativas en este criterio, demostrando un compromiso excepcional, creatividad y resultados que trascienden los estándares establecidos. Su desempeño genera impacto positivo y es un referente de excelencia para sus compañeros y el equipo.</p>
								</div>
							</div>
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section11', 'Acorde')">Acorde</button>
								<button type="button" class="info-button" onclick="openModal('modalAcorde11')">?</button>
							</div>
							<!-- Modal para la opción "Sobresaliente" -->
							<div id="modalAcorde11" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalAcorde11')">&times;</span>
									<p>El colaborador cumple con las expectativas y estándares definidos, manteniendo un desempeño consistente y adecuado en sus responsabilidades. Contribuye de manera efectiva al logro de los objetivos organizacionales.</p>
								</div>
							</div>
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section11', 'Aceptable')">Aceptable</button>
								<button type="button" class="info-button" onclick="openModal('modalAceptable11')">?</button>
							</div>
							<!-- Modal para la opción "Sobresaliente" -->
							<div id="modalAceptable11" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalAceptable11')">&times;</span>
									<p>El colaborador cumple con los aspectos básicos del criterio, pero su desempeño podría beneficiarse de un mayor enfoque, compromiso o consistencia para alcanzar su máximo potencial.</p>
								</div>
							</div>
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section11', 'Necesita Mejorar')">Necesita Mejorar</button>
								<button type="button" class="info-button" onclick="openModal('modalMejorar11')">?</button>
							</div>
							<!-- Modal para la opción "Sobresaliente" -->
							<div id="modalMejorar11" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalMejorar11')">&times;</span>
									<p>El desempeño del colaborador está por debajo de lo esperado, lo que afecta la calidad de los resultados o el cumplimiento de sus responsabilidades. Se requiere un plan de acción para abordar las áreas de mejora.</p>
								</div>
							</div>
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section11', 'Insuficiente')">Insuficiente</button>
								<button type="button" class="info-button" onclick="openModal('modalInsuficiente11')">?</button>
							</div>
							<!-- Modal para la opción "Sobresaliente" -->
							<div id="modalInsuficiente11" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalInsuficiente11')">&times;</span>
									<p>El desempeño del colaborador es significativamente inferior a los estándares esperados, con un impacto negativo en el equipo o los resultados organizacionales. Se requiere intervención inmediata para corregir la situación.</p>
								</div>
							</div>
                        </div>
                        <input type="hidden" name="pregunta11" id="section11-input" required>
                        <div class="sm:flex justify-center gap-4 mt-8">
                            <button type="button" class="back-button" onclick="previousSection(11)">Anterior</button>
                            <button type="submit" class="next-button">Guardar y enviar</button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
</main>

<script>
	function selectOption(button, section, value) {
		// Deseleccionar todos los botones en la sección completa
		var sectionButtons = document.querySelectorAll(`#${section} .option-button`);
		sectionButtons.forEach(btn => btn.classList.remove('selected'));

		// Seleccionar el botón actual
		button.classList.add('selected');

		// Establecer el valor del input oculto
		document.getElementById(section + '-input').value = value;
	}

    function nextSection(currentSection) {
        var currentInput = document.getElementById('section' + currentSection + '-input');
        if (currentInput.value === '') {
/*             alert('Por favor, selecciona una opción.');
            return; */
            Swal.fire({
                icon: 'error',
                title: 'Dato Requerido',
                text: 'Por favor, selecciona una opción',
                showConfirmButton: false,
                timer: 2000
            });
            exit();
        }
        document.getElementById('section' + currentSection).classList.remove('active');
        document.getElementById('section' + (currentSection + 1)).classList.add('active');
        updateProgressBar(currentSection + 1);
    }

    function updateProgressBar(section) {
        var progressBar = document.getElementById('progress-bar');
        var totalSections = document.getElementsByClassName('section').length;
        var progress = (section / totalSections) * 100;
        progressBar.style.width = progress + '%';
    }

    function previousSection(currentSection) {
        document.getElementById('section' + currentSection).classList.remove('active');
        document.getElementById('section' + (currentSection - 1)).classList.add('active');
        updateProgressBar(currentSection - 1);
    }
	
	function openModal(modalId) {
		document.getElementById(modalId).style.display = "block";
	}

	function closeModal(modalId) {
		document.getElementById(modalId).style.display = "none";
	}

	window.onclick = function(event) {
		let modals = document.querySelectorAll(".modal");
		modals.forEach(modal => {
			if (event.target === modal) {
				modal.style.display = "none";
			}
		});
	};
</script>