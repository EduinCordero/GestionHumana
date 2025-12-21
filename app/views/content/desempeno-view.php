<div class="container">

    <form id="evaluationForm" action="<?php echo APP_URL; ?>app/ajax/formAjax.php" method="POST" enctype="multipart/form-data">
       
        <input type="hidden" name="modulo_formulario" value="registrar">

        <?php
            $idempleado = $_SESSION['idempleado'];;
        ?>

        <input type="hidden" name="empleadoevaluado" value="<?php echo htmlspecialchars($idempleado); ?>">

        <div class="section active" id="section1">
        <h1>Gestión de Relaciones Interpersonales</h1>
        <p>
            En su día a día, el colaborador demuestra la capacidad de construir puentes de confianza y comunicación que 
            fortalecen las relaciones con compañeros, líderes y otras partes interesadas. ¿Cómo calificarías su habilidad 
            para resolver conflictos de forma creativa y generar un entorno laboral armónico que impulse la cultura organizacional?
        </p>
            <div class="options">
                <button type="button" class="option-button" onclick="selectOption(this, 'section1', 'Sobresaliente')">Sobresaliente</button>
                <button type="button" class="option-button" onclick="selectOption(this, 'section1', 'Acorde')">Acorde</button>
                <button type="button" class="option-button" onclick="selectOption(this, 'section1', 'Aceptable')">Aceptable</button>
                <button type="button" class="option-button" onclick="selectOption(this, 'section1', 'Necesita Mejorar')">Necesita Mejorar</button>
                <button type="button" class="option-button" onclick="selectOption(this, 'section1', 'Insuficiente')">Insuficiente</button>
            </div>
            <input type="hidden" name="section1" id="section1-input" required>
            <button type="button" onclick="nextSection(1)">Siguiente</button>
        </div>

        <div class="section" id="section2">
            <h1>Eficacia en la Ejecución de Responsabilidades y Contribución a los Resultados</h1>
            <p>
                ¿De qué manera el colaborador traduce sus responsabilidades en acciones que generan resultados tangibles, 
                demostrando puntualidad, precisión y un impacto significativo en el cumplimiento de las metas organizacionales?
            </p>
            <div class="options">
                <button type="button" class="option-button" onclick="selectOption(this, 'section2', 'Sobresaliente')">Sobresaliente</button>
                <button type="button" class="option-button" onclick="selectOption(this, 'section2', 'Acorde')">Acorde</button>
                <button type="button" class="option-button" onclick="selectOption(this, 'section2', 'Aceptable')">Aceptable</button>
                <button type="button" class="option-button" onclick="selectOption(this, 'section2', 'Necesita Mejorar')">Necesita Mejorar</button>
                <button type="button" class="option-button" onclick="selectOption(this, 'section2', 'Insuficiente')">Insuficiente</button>
            </div>
            <input type="hidden" name="section2" id="section2-input" required>
            <button type="button" class="back-button" onclick="previousSection(2)"><i class="fas fa-arrow-left"></i></button>
            <button type="button" onclick="nextSection(2)">Siguiente</button>
        </div>

        <div class="section" id="section3">
            <h1>Gestión Eficiente del Tiempo y los Recursos</h1>
            <p>
                En su rol, el colaborador organiza su tiempo y recursos con una visión estratégica, priorizando tareas clave y minimizando el desperdicio. 
                ¿Cómo evaluarías su capacidad para cumplir plazos establecidos, 
                optimizar recursos disponibles y entregar resultados alineados con las demandas del cargo?
            </p>
            <div class="options">
                <button type="button" class="option-button" onclick="selectOption(this, 'section3', 'Sobresaliente')">Sobresaliente</button>
                <button type="button" class="option-button" onclick="selectOption(this, 'section3', 'Acorde')">Acorde</button>
                <button type="button" class="option-button" onclick="selectOption(this, 'section3', 'Aceptable')">Aceptable</button>
                <button type="button" class="option-button" onclick="selectOption(this, 'section3', 'Necesita Mejorar')">Necesita Mejorar</button>
                <button type="button" class="option-button" onclick="selectOption(this, 'section3', 'Insuficiente')">Insuficiente</button>
            </div>
            <input type="hidden" name="section3" id="section3-input" required>
            <button type="button" class="back-button" onclick="previousSection(3)"><i class="fas fa-arrow-left"></i></button>
            <button type="button" onclick="nextSection(3)">Siguiente</button>
        </div>

        <div class="section" id="section4">
            <h1>Comunicación Asertiva y Sentido de Pertenencia</h1>
            <p>¿El evaluado fomenta una comunicación abierta y efectiva, participa activamente en las iniciativas institucionales y contribuye con sentido de pertenencia al propósito y cuidado de la institución?</p>
            <div class="options">
                <button type="button" class="option-button" onclick="selectOption(this, 'section4', 'Sobresaliente')">Sobresaliente</button>
                <button type="button" class="option-button" onclick="selectOption(this, 'section4', 'Acorde')">Acorde</button>
                <button type="button" class="option-button" onclick="selectOption(this, 'section4', 'Aceptable')">Aceptable</button>
                <button type="button" class="option-button" onclick="selectOption(this, 'section4', 'Necesita Mejorar')">Necesita Mejorar</button>
                <button type="button" class="option-button" onclick="selectOption(this, 'section4', 'Insuficiente')">Insuficiente</button>
            </div>
            <input type="hidden" name="section4" id="section4-input" required>
            <button type="button" class="back-button" onclick="previousSection(4)"><i class="fas fa-arrow-left"></i></button>
            <button type="button" onclick="nextSection(4)">Siguiente</button>
        </div>

        <div class="section" id="section5">
            <button type="button" class="back-button" onclick="previousSection(5)"><i class="fas fa-arrow-left"></i></button>
            <h1>¿Deseas enviar la evaluación?</h1>
            <button type="submit">Enviar</button>
        </div>


    </form>
</div>

<script>
    function selectOption(button, section, value) {
        // Deseleccionar todos los botones en la sección
        var buttons = button.parentElement.getElementsByClassName('option-button');
        for (var i = 0; i < buttons.length; i++) {
            buttons[i].classList.remove('selected');
        }
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
</script>