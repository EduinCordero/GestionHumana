<div class="container">

    <form id="evaluationForm" action="<?php echo APP_URL; ?>app/ajax/formAjax.php" method="POST" enctype="multipart/form-data">
       
        <input type="hidden" name="modulo_formulario" value="registrar">

        <?php
            $idempleado = $_POST['employee_id'];
        ?>

        <input type="hidden" name="empleadoevaluado" value="<?php echo htmlspecialchars($idempleado); ?>">

        <div class="section active" id="section1">
        <h1>Propósito</h1>
        <p>¿Cómo tu líder traduce la visión y objetivos de la organización en metas claras y alcanzables para su equipo, y de qué manera asegura que cada miembro comprenda el impacto de su contribución al éxito colectivo de la clínica?</p>
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
            <h1>Colaboración</h1>
            <p>
                ¿Cómo tu líder fomenta la colaboración, resolviendo conflictos de manera proactiva y creando un entorno 
                donde los equipos trabajan unidos para alcanzar metas comunes, sin barreras entre procesos y funciones?
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
            <h1>Consistencia</h1>
            <p>
                ¿En qué medida tu líder actúa con ética y coherencia, 
                tomando decisiones que reflejan los valores y políticas de la organización, y cómo eso inspira confianza y seguridad en el equipo?
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
            <h1>Adaptabilidad</h1>
            <p>
                ¿Cómo tu líder ajusta su enfoque y estrategia en respuesta a los cambios organizacionales 
                o del entorno, y en qué medida fomenta la creatividad, la innovación y la 
                flexibilidad dentro de su equipo para mantener el impulso hacia la excelencia?
            </p>
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
            <h1>Amor</h1>
            <p>
                ¿De qué manera tu líder demuestra un compromiso genuino con el bienestar emocional y físico del equipo, atendiendo tanto a las necesidades 
                individuales como colectivas, y guiando sus acciones con principios de amor y compasión, 
                en consonancia con la sabiduría y la paz que nos inspira nuestro amor a Dios?
            </p>
            <div class="options">
                <button type="button" class="option-button" onclick="selectOption(this, 'section5', 'Sobresaliente')">Sobresaliente</button>
                <button type="button" class="option-button" onclick="selectOption(this, 'section5', 'Acorde')">Acorde</button>
                <button type="button" class="option-button" onclick="selectOption(this, 'section5', 'Aceptable')">Aceptable</button>
                <button type="button" class="option-button" onclick="selectOption(this, 'section5', 'Necesita Mejorar')">Necesita Mejorar</button>
                <button type="button" class="option-button" onclick="selectOption(this, 'section5', 'Insuficiente')">Insuficiente</button>
            </div>
            <input type="hidden" name="section5" id="section5-input" required>
            <button type="button" class="back-button" onclick="previousSection(4)"><i class="fas fa-arrow-left"></i></button>
            <button type="button" onclick="nextSection(5)">Siguiente</button>
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