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
                    <input type="hidden" name="modulo_evaluacion" value="registrarEvaluacionLider">
                    <input type="hidden" name="empleadoevaluado" value="<?php echo $idempleado; ?>">
// Aquí comienza el formulario de evaluación de liderazgo
// Error estructural, el formulario coimenza en la sección 4 y no en la 1.
                    <div class="section active" id="section4" style="text-align: center;"> 
                        <h1 class="md:text-40 text-32 font-bold text-link dark:text-white leading-tight text-center" style="color: #0058af;">Propósito</h1>
                        <p class="mt-5 text-center w-fit mx-auto py-1 px-2 rounded-md border-2 border-dashed border-border dark:border-darkborder text-sm font-medium justify-center text-lightmuted dark:text-darklink flex items-center flex-wrap gap-1">¿Cómo tu líder traduce la visión y objetivos de la organización en metas claras y alcanzables para su equipo, y de qué manera asegura que cada miembro comprenda el impacto de su contribución al éxito colectivo de la clínica?</p>
                        <div class="options mt-5">
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section4', 'Sobresaliente')">Sobresaliente</button>
								<button type="button" class="info-button" onclick="openModal('modalSobresaliente')">?</button>
							</div>	
							<!-- Modal para la opción "Sobresaliente" -->
							<div id="modalSobresaliente" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalSobresaliente')">&times;</span>
									<p>El líder no solo cumple, sino que trasciende las expectativas, transformando el equipo y la organización con su visión clara, su capacidad de adaptación y su amor genuino por el bienestar de los colaboradores. Su liderazgo inspira, motiva y genera un impacto profundo y duradero, elevando el potencial de cada miembro y alineando a todos hacia la excelencia, guiados por principios sólidos.</p>
								</div>
							</div>
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section4', 'Acorde')">Acorde</button>
								<button type="button" class="info-button" onclick="openModal('modalAcorde')">?</button>
							</div>
							<!-- Modal para la opción "Acorde" -->
							<div id="modalAcorde" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalAcorde')">&times;</span>
									<p>El líder cumple de manera consistente con sus responsabilidades, demostrando un fuerte compromiso con los objetivos organizacionales y el bienestar de su equipo. Su capacidad de adaptación y trabajo en equipo es sólida, y mantiene un enfoque ético en sus decisiones, generando un ambiente de respeto y colaboración.</p>
								</div>
							</div>
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section4', 'Aceptable')">Aceptable</button>
								<button type="button" class="info-button" onclick="openModal('modalAceptable')">?</button>
							</div>
							<!-- Modal para la opción "Acorde" -->
							<div id="modalAceptable" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalAceptable')">&times;</span>
									<p>El líder cumple con las funciones básicas de su rol, pero podría mejorar en algunos aspectos clave, como la comunicación o la resolución de conflictos. Su capacidad de adaptarse a los cambios es moderada, y aunque genera un ambiente de trabajo funcional, no siempre inspira al equipo a alcanzar su máximo potencial.</p>
								</div>
							</div>
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section4', 'Necesita Mejorar')">Necesita Mejorar</button>
								<button type="button" class="info-button" onclick="openModal('modalNecesitaMejorar')">?</button>
							</div>
							<!-- Modal para la opción "Acorde" -->
							<div id="modalNecesitaMejorar" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalNecesitaMejorar')">&times;</span>
									<p>El líder enfrenta desafíos para mantener la coherencia y el enfoque, con dificultades para guiar al equipo o adaptarse a los cambios organizacionales. Aunque cumple con algunas de sus responsabilidades, su capacidad para inspirar, resolver conflictos y generar un ambiente positivo requiere refuerzo inmediato.</p>
								</div>
							</div>
                        </div>
                        <input type="hidden" name="pregunta4" id="section4-input" required>
                        <div class="sm:flex justify-center gap-4 mt-8">
                            <!--<button type="button" class="back-button" onclick="previousSection(4)">Anterior</button>-->
                            <button type="button" class="next-button" onclick="nextSection(4)">Siguiente</button>
                        </div>
                    </div>

                    <div class="section" id="section5" style="text-align: center;"> 
                        <h1 class="md:text-40 text-32 font-bold text-link dark:text-white leading-tight text-center" style="color: #0058af;">Colaboración</h1>
                        <p class="mt-5 text-center w-fit mx-auto py-1 px-2 rounded-md border-2 border-dashed border-border dark:border-darkborder text-sm font-medium justify-center text-lightmuted dark:text-darklink flex items-center flex-wrap gap-1">¿Cómo tu líder fomenta la colaboración, resolviendo conflictos de manera proactiva y creando un entorno donde los equipos trabajan unidos para alcanzar metas comunes, sin barreras entre procesos y funciones?</p>
                        <div class="options mt-5">
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section5', 'Sobresaliente')">Sobresaliente</button>
								<button type="button" class="info-button" onclick="openModal('modalSobresaliente5')">?</button>
							</div>
							<!-- Modal para la opción "Acorde" -->
							<div id="modalSobresaliente5" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalSobresaliente5')">&times;</span>
									<p>El líder no solo cumple, sino que trasciende las expectativas, transformando el equipo y la organización con su visión clara, su capacidad de adaptación y su amor genuino por el bienestar de los colaboradores. Su liderazgo inspira, motiva y genera un impacto profundo y duradero, elevando el potencial de cada miembro y alineando a todos hacia la excelencia, guiados por principios sólidos.</p>
								</div>
							</div>
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section5', 'Acorde')">Acorde</button>
								<button type="button" class="info-button" onclick="openModal('modalAcorde5')">?</button>
							</div>
							<!-- Modal para la opción "Acorde" -->
							<div id="modalAcorde5" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalAcorde5')">&times;</span>
									<p>El líder cumple de manera consistente con sus responsabilidades, demostrando un fuerte compromiso con los objetivos organizacionales y el bienestar de su equipo. Su capacidad de adaptación y trabajo en equipo es sólida, y mantiene un enfoque ético en sus decisiones, generando un ambiente de respeto y colaboración.</p>
								</div>
							</div>
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section5', 'Aceptable')">Aceptable</button>
								<button type="button" class="info-button" onclick="openModal('modalAceptable5')">?</button>
							</div>
							<!-- Modal para la opción "Acorde" -->
							<div id="modalAceptable5" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalAceptable5')">&times;</span>
									<p>El líder cumple con las funciones básicas de su rol, pero podría mejorar en algunos aspectos clave, como la comunicación o la resolución de conflictos. Su capacidad de adaptarse a los cambios es moderada, y aunque genera un ambiente de trabajo funcional, no siempre inspira al equipo a alcanzar su máximo potencial.</p>
								</div>
							</div>
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section5', 'Necesita Mejorar')">Necesita Mejorar</button>
								<button type="button" class="info-button" onclick="openModal('modalNecesitaMejorar5')">?</button>
							</div>
							<!-- Modal para la opción "Acorde" -->
							<div id="modalNecesitaMejorar5" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalNecesitaMejorar5')">&times;</span>
									<p>El líder enfrenta desafíos para mantener la coherencia y el enfoque, con dificultades para guiar al equipo o adaptarse a los cambios organizacionales. Aunque cumple con algunas de sus responsabilidades, su capacidad para inspirar, resolver conflictos y generar un ambiente positivo requiere refuerzo inmediato.</p>
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
                        <h1 class="md:text-40 text-32 font-bold text-link dark:text-white leading-tight text-center" style="color: #0058af;">Consistencia</h1>
                        <p class="mt-5 text-center w-fit mx-auto py-1 px-2 rounded-md border-2 border-dashed border-border dark:border-darkborder text-sm font-medium justify-center text-lightmuted dark:text-darklink flex items-center flex-wrap gap-1">¿En qué medida tu líder actúa con ética y coherencia, tomando decisiones que reflejan los valores y políticas de la organización, y cómo eso inspira confianza y seguridad en el equipo?</p>
                        <div class="options mt-5">
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section6', 'Sobresaliente')">Sobresaliente</button>
								<button type="button" class="info-button" onclick="openModal('modalSobresaliente6')">?</button>
							</div>
							<!-- Modal para la opción "Sobresaliente" -->
							<div id="modalSobresaliente6" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalSobresaliente6')">&times;</span>
									<p>El líder no solo cumple, sino que trasciende las expectativas, transformando el equipo y la organización con su visión clara, su capacidad de adaptación y su amor genuino por el bienestar de los colaboradores. Su liderazgo inspira, motiva y genera un impacto profundo y duradero, elevando el potencial de cada miembro y alineando a todos hacia la excelencia, guiados por principios sólidos.</p>
								</div>
							</div>
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section6', 'Acorde')">Acorde</button>
								<button type="button" class="info-button" onclick="openModal('modalAcorde6')">?</button>
							</div>
							<!-- Modal para la opción "Acorde" -->
							<div id="modalAcorde6" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalAcorde6')">&times;</span>
									<p>El líder cumple de manera consistente con sus responsabilidades, demostrando un fuerte compromiso con los objetivos organizacionales y el bienestar de su equipo. Su capacidad de adaptación y trabajo en equipo es sólida, y mantiene un enfoque ético en sus decisiones, generando un ambiente de respeto y colaboración.</p>
								</div>
							</div>
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section6', 'Aceptable')">Aceptable</button>
								<button type="button" class="info-button" onclick="openModal('modalAceptable6')">?</button>
							</div>
							<!-- Modal para la opción "Aceptable" -->
							<div id="modalAceptable6" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalAceptable6')">&times;</span>
									<p>El líder cumple con las funciones básicas de su rol, pero podría mejorar en algunos aspectos clave, como la comunicación o la resolución de conflictos. Su capacidad de adaptarse a los cambios es moderada, y aunque genera un ambiente de trabajo funcional, no siempre inspira al equipo a alcanzar su máximo potencial.</p>
								</div>
							</div>
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section6', 'Necesita Mejorar')">Necesita Mejorar</button>
								<button type="button" class="info-button" onclick="openModal('modalNecesitaMejorar6')">?</button>
							</div>
							<!-- Modal para la opción "Necesita Mejorar" -->
							<div id="modalNecesitaMejorar6" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalNecesitaMejorar6')">&times;</span>
									<p>El líder enfrenta desafíos para mantener la coherencia y el enfoque, con dificultades para guiar al equipo o adaptarse a los cambios organizacionales. Aunque cumple con algunas de sus responsabilidades, su capacidad para inspirar, resolver conflictos y generar un ambiente positivo requiere refuerzo inmediato.</p>
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
                        <h1 class="md:text-40 text-32 font-bold text-link dark:text-white leading-tight text-center" style="color: #0058af;">Adaptabilidad</h1>
                        <p class="mt-5 text-center w-fit mx-auto py-1 px-2 rounded-md border-2 border-dashed border-border dark:border-darkborder text-sm font-medium justify-center text-lightmuted dark:text-darklink flex items-center flex-wrap gap-1">¿Cómo tu líder ajusta su enfoque y estrategia en respuesta a los cambios organizacionales o del entorno, y en qué medida fomenta la creatividad, la innovación y la flexibilidad dentro de su equipo para mantener el impulso hacia la excelencia?</p>
                        <div class="options mt-5">
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section7', 'Sobresaliente')">Sobresaliente</button>
								<button type="button" class="info-button" onclick="openModal('modalSobresaliente7')">?</button>
							</div>
							<!-- Modal para la opción "Sobresaliente" -->
							<div id="modalSobresaliente7" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalSobresaliente7')">&times;</span>
									<p>El líder no solo cumple, sino que trasciende las expectativas, transformando el equipo y la organización con su visión clara, su capacidad de adaptación y su amor genuino por el bienestar de los colaboradores. Su liderazgo inspira, motiva y genera un impacto profundo y duradero, elevando el potencial de cada miembro y alineando a todos hacia la excelencia, guiados por principios sólidos.</p>
								</div>
							</div>
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section7', 'Acorde')">Acorde</button>
								<button type="button" class="info-button" onclick="openModal('modalAcorde7')">?</button>
							</div>
							<!-- Modal para la opción "Acorde" -->
							<div id="modalAcorde7" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalAcorde7')">&times;</span>
									<p>El líder cumple de manera consistente con sus responsabilidades, demostrando un fuerte compromiso con los objetivos organizacionales y el bienestar de su equipo. Su capacidad de adaptación y trabajo en equipo es sólida, y mantiene un enfoque ético en sus decisiones, generando un ambiente de respeto y colaboración.</p>
								</div>
							</div>
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section7', 'Aceptable')">Aceptable</button>
								<button type="button" class="info-button" onclick="openModal('modalAceptable7')">?</button>
							</div>
							<!-- Modal para la opción "Aceptable" -->
							<div id="modalAceptable7" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalAceptable7')">&times;</span>
									<p>El líder cumple con las funciones básicas de su rol, pero podría mejorar en algunos aspectos clave, como la comunicación o la resolución de conflictos. Su capacidad de adaptarse a los cambios es moderada, y aunque genera un ambiente de trabajo funcional, no siempre inspira al equipo a alcanzar su máximo potencial.</p>
								</div>
							</div>
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section7', 'Necesita Mejorar')">Necesita Mejorar</button>
								<button type="button" class="info-button" onclick="openModal('modalNecesitaMejorar7')">?</button>
							</div>
							<!-- Modal para la opción "Necesita Mejorar" -->
							<div id="modalNecesitaMejorar7" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalNecesitaMejorar7')">&times;</span>
									<p>El líder enfrenta desafíos para mantener la coherencia y el enfoque, con dificultades para guiar al equipo o adaptarse a los cambios organizacionales. Aunque cumple con algunas de sus responsabilidades, su capacidad para inspirar, resolver conflictos y generar un ambiente positivo requiere refuerzo inmediato.</p>
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
                        <h1 class="md:text-40 text-32 font-bold text-link dark:text-white leading-tight text-center" style="color: #0058af;">Amor</h1>
                        <p class="mt-5 text-center w-fit mx-auto py-1 px-2 rounded-md border-2 border-dashed border-border dark:border-darkborder text-sm font-medium justify-center text-lightmuted dark:text-darklink flex items-center flex-wrap gap-1">¿De qué manera tu líder demuestra un compromiso genuino con el bienestar emocional y físico del equipo, atendiendo tanto a las necesidades individuales como colectivas, y guiando sus acciones con principios de amor y compasión, en consonancia con la sabiduría y la paz que nos inspira nuestro amor a Dios?</p>
                        <div class="options mt-5">
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section8', 'Sobresaliente')">Sobresaliente</button>
								<button type="button" class="info-button" onclick="openModal('modalSobresaliente8')">?</button>
							</div>
							<!-- Modal para la opción "Sobresaliente" -->
							<div id="modalSobresaliente8" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalSobresaliente8')">&times;</span>
									<p>El líder no solo cumple, sino que trasciende las expectativas, transformando el equipo y la organización con su visión clara, su capacidad de adaptación y su amor genuino por el bienestar de los colaboradores. Su liderazgo inspira, motiva y genera un impacto profundo y duradero, elevando el potencial de cada miembro y alineando a todos hacia la excelencia, guiados por principios sólidos.</p>
								</div>
							</div>
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section8', 'Acorde')">Acorde</button>
								<button type="button" class="info-button" onclick="openModal('modalAcorde8')">?</button>
							</div>
							<!-- Modal para la opción "Acorde" -->
							<div id="modalAcorde8" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalAcorde8')">&times;</span>
									<p>El líder cumple de manera consistente con sus responsabilidades, demostrando un fuerte compromiso con los objetivos organizacionales y el bienestar de su equipo. Su capacidad de adaptación y trabajo en equipo es sólida, y mantiene un enfoque ético en sus decisiones, generando un ambiente de respeto y colaboración.</p>
								</div>
							</div>
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section8', 'Aceptable')">Aceptable</button>
								<button type="button" class="info-button" onclick="openModal('modalAceptable8')">?</button>
							</div>
							<!-- Modal para la opción "Aceptable" -->
							<div id="modalAceptable8" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalAceptable8')">&times;</span>
									<p>El líder cumple con las funciones básicas de su rol, pero podría mejorar en algunos aspectos clave, como la comunicación o la resolución de conflictos. Su capacidad de adaptarse a los cambios es moderada, y aunque genera un ambiente de trabajo funcional, no siempre inspira al equipo a alcanzar su máximo potencial.</p>
								</div>
							</div>
							<div class="option-container">
								<button type="button" class="option-button" onclick="selectOption(this, 'section8', 'Necesita Mejorar')">Necesita Mejorar</button>
								<button type="button" class="info-button" onclick="openModal('modalNecesitaMejorar8')">?</button>
							</div>
							<!-- Modal para la opción "Necesita Mejorar" -->
							<div id="modalNecesitaMejorar8" class="modal">
								<div class="modal-content">
									<span class="close" onclick="closeModal('modalNecesitaMejorar8')">&times;</span>
									<p>El líder enfrenta desafíos para mantener la coherencia y el enfoque, con dificultades para guiar al equipo o adaptarse a los cambios organizacionales. Aunque cumple con algunas de sus responsabilidades, su capacidad para inspirar, resolver conflictos y generar un ambiente positivo requiere refuerzo inmediato.</p>
								</div>
							</div>
                        </div>
                        <input type="hidden" name="pregunta8" id="section8-input" required>
                        <div class="sm:flex justify-center gap-4 mt-8">
                            <button type="button" class="back-button" onclick="previousSection(8)">Anterior</button>
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