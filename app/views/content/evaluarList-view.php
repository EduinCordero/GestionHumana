<?php
    use app\controllers\evaluarController;
    $insUsuario = new evaluarController();

    // Mensaje de evaluación completada
    $evalCompletada = $_SESSION['eval_completada'] ?? '';
    unset($_SESSION['eval_completada']);
?>

<?php
// ── Variables de sesión para modales ─────────────────────────────────────
$eaPendiente      = $_SESSION['ea_pendiente']       ?? null;
$eaNombreEvaluado = $_SESSION['ea_nombre_evaluado'] ?? '';
$eaCompletada     = $_SESSION['ea_completada']      ?? false;
unset($_SESSION['ea_pendiente'], $_SESSION['ea_nombre_evaluado'], $_SESSION['ea_completada']);
?>

<?php if ($evalCompletada || $eaPendiente || $eaCompletada): ?>
<script>
// Envoltorio seguro para evitar que errores JS impidan mostrar la página
(function(){
    try {
        document.addEventListener('DOMContentLoaded', function() {

            <?php if ($eaCompletada): ?>
            // Exp. Azul completada
            Swal.fire({
                icon: 'success',
                title: '¡Exp. Azul completada!',
                text: 'La evaluación de Experiencia Azul fue guardada exitosamente.',
                confirmButtonText: 'Continuar',
                confirmButtonColor: '#0058af',
                timer: 3000,
                timerProgressBar: true
            });

            <?php elseif ($eaPendiente === 'EXPERIENCIA_COLAB'): ?>
            // Evaluación normal guardada + Exp. Azul pendiente (líder evalúa colaborador asistencial)
            Swal.fire({
                icon: 'success',
                title: '¡Evaluación guardada!',
                text: '<?= addslashes($evalCompletada ?: 'La evaluación fue guardada correctamente.') ?>',
                confirmButtonText: 'Continuar',
                confirmButtonColor: '#0058af',
                allowOutsideClick: false
            }).then(function() {
                Swal.fire({
                    icon: 'info',
                    title: '🔵 Exp. Azul',
                    html: '<?= $eaNombreEvaluado ? '<b>' . addslashes($eaNombreEvaluado) . '</b> hace parte del equipo asistencial.<br><br>' : '' ?>¿Deseas continuar con la evaluación de <b>Experiencia Azul</b>?',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, continuar',
                    cancelButtonText: 'Ahora no',
                    confirmButtonColor: '#0058af',
                    cancelButtonColor: '#94a3b8',
                    allowOutsideClick: false
                }).then(function(result) {
                    if (result.isConfirmed) {
                        window.location.href = '<?= APP_URL ?>experienciaAzulColab/';
                    }
                });
            });

            <?php elseif ($eaPendiente === 'EXPERIENCIA_LIDER'): ?>
            // Evaluación normal guardada + Exp. Azul pendiente (colaborador asistencial evalúa líder)
            Swal.fire({
                icon: 'success',
                title: '¡Evaluación guardada!',
                text: '<?= addslashes($evalCompletada ?: 'La evaluación fue guardada correctamente.') ?>',
                confirmButtonText: 'Continuar',
                confirmButtonColor: '#0058af',
                allowOutsideClick: false
            }).then(function() {
                Swal.fire({
                    icon: 'info',
                    title: '🔵 Exp. Azul',
                    html: 'Como colaborador asistencial puedes evaluar la gestión de Experiencia Azul de tu líder.<br><br>¿Deseas continuar?',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, continuar',
                    cancelButtonText: 'Ahora no',
                    confirmButtonColor: '#0058af',
                    cancelButtonColor: '#94a3b8',
                    allowOutsideClick: false
                }).then(function(result) {
                    if (result.isConfirmed) {
                        window.location.href = '<?= APP_URL ?>experienciaAzulLider/';
                    }
                });
            });

            <?php else: ?>
            // Evaluación normal guardada sin Exp. Azul
            Swal.fire({
                icon: 'success',
                title: '¡Evaluación guardada!',
                text: '<?= addslashes($evalCompletada) ?>',
                confirmButtonText: 'Continuar',
                confirmButtonColor: '#0058af',
                showConfirmButton: false,
                timer: 2500,
                timerProgressBar: true
            });
            <?php endif; ?>

            // Asegurar que la página sea visible incluso si hay errores posteriores
            try { document.body.style.visibility = 'visible'; } catch(e){}
        });
    } catch (e) {
        console.error('Error en script de notificaciones:', e);
        try { document.body.style.visibility = 'visible'; } catch (e){}
    }
})();
</script>
<?php endif; ?>

<style>
:root {
    --el-accent: #005EB8;
    --el-bg:     #f8fafc;
    --el-card:   #ffffff;
    --el-border: #e2e8f0;
    --el-muted:  #64748b;
    --el-text:   #1e293b;
}

.el-wrap {
    padding: 68px 24px 56px;
    max-width: 960px;
    margin: 0 auto;
}

/* Hero card */
.el-hero {
    background: linear-gradient(135deg, #005EB8 0%, #0074E0 100%);
    border-radius: 20px;
    padding: 32px 36px;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 24px;
    margin-bottom: 28px;
    position: relative;
    overflow: hidden;
}
.el-hero::before {
    content: '';
    position: absolute;
    width: 260px; height: 260px;
    border-radius: 50%;
    background: rgba(255,255,255,.07);
    top: -80px; right: -60px;
}
.el-hero-text h2 {
    font-size: 1.4rem;
    font-weight: 800;
    margin-bottom: 6px;
    color: #fff;
}
.el-hero-text p {
    font-size: .86rem;
    color: rgba(255,255,255,0.75);
    max-width: 480px;
    line-height: 1.6;
}
.el-hero-img {
    width: 130px;
    flex-shrink: 0;
    position: relative;
    z-index: 1;
}
@media(max-width:600px) {
    .el-hero { flex-direction: column; padding: 24px 20px; }
    .el-hero-img { display: none; }
}

/* Status badge */
.el-status-wrap {
    margin-top: 20px;
}
.el-status-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    border-radius: 99px;
    font-weight: 700;
    font-size: .9rem;
    text-decoration: none;
    border: none;
    cursor: pointer;
    font-family: inherit;
    transition: all .2s;
}
.el-status-done {
    background: rgba(255,255,255,.2);
    color: #fff;
    border: 2px solid rgba(255,255,255,.4);
}
.el-status-start {
    background: #fff;
    color: var(--el-accent);
}
.el-status-start:hover { background: #e8f0fb; }

/* Section header */
.el-section-head {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 16px;
}
.el-section-dot {
    width: 10px; height: 10px;
    border-radius: 50%;
    background: var(--el-accent);
    flex-shrink: 0;
}
.el-section-title {
    font-size: 1rem;
    font-weight: 800;
    color: var(--el-text);
}

/* Table card */
.el-table-card {
    background: var(--el-card);
    border-radius: 16px;
    box-shadow: 0 2px 16px rgba(0,94,184,.06);
    overflow: hidden;
}

.el-table {
    width: 100%;
    border-collapse: collapse;
}
.el-table thead th {
    background: #f8fafc;
    padding: 12px 20px;
    font-size: .75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: var(--el-muted);
    text-align: left;
    border-bottom: 1.5px solid var(--el-border);
}
.el-table tbody tr {
    border-bottom: 1px solid var(--el-border);
    transition: background .15s;
}
.el-table tbody tr:last-child { border-bottom: none; }
.el-table tbody tr:hover { background: #f8fafc; }
.el-table td {
    padding: 14px 20px;
    vertical-align: middle;
}

.el-emp-name { font-weight: 700; font-size: .9rem; color: var(--el-text); }
.el-emp-cargo { font-size: .75rem; color: var(--el-muted); margin-top: 2px; }

/* Progress bar inline */
.el-prog-wrap {
    display: flex;
    align-items: center;
    gap: 10px;
}
.el-prog-bar-bg {
    flex: 1;
    height: 6px;
    background: #e2e8f0;
    border-radius: 99px;
    overflow: hidden;
    min-width: 80px;
}
.el-prog-bar-fill {
    height: 100%;
    border-radius: 99px;
    background: linear-gradient(90deg, #005EB8, #0074E0);
    transition: width .6s ease;
}
.el-prog-pct {
    font-size: .78rem;
    font-weight: 700;
    color: var(--el-muted);
    width: 36px;
    text-align: right;
}

/* Status badges */
.el-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 12px;
    border-radius: 99px;
    font-size: .75rem;
    font-weight: 700;
}
.el-badge-done    { background: #d1fae5; color: #065f46; }
.el-badge-pending { background: #fef3c7; color: #92400e; }
.el-badge-none    { background: #f1f5f9; color: #64748b; }

/* Action button */
.el-action-btn {
    padding: 7px 16px;
    border-radius: 8px;
    font-size: .8rem;
    font-weight: 700;
    cursor: pointer;
    border: none;
    font-family: inherit;
    transition: all .2s;
    white-space: nowrap;
}
.el-action-primary { background: var(--el-accent); color: #fff; }
.el-action-primary:hover { background: #0074E0; }
.el-action-done { background: #f1f5f9; color: #94a3b8; cursor: default; }

/* Empty state */
.el-empty {
    text-align: center;
    padding: 48px 24px;
    color: var(--el-muted);
}
.el-empty-icon { font-size: 2.5rem; margin-bottom: 10px; }
</style>

<div class="el-wrap">

    <!-- Hero Banner -->
    <div class="el-hero">
        <div class="el-hero-text">
            <h2>Evaluación de Desempeño</h2>
            <p>Buscamos destacar cómo tu talento convierte tareas en resultados extraordinarios,
               creando valor e inspirando a otros en nuestra organización.</p>
            <div class="el-status-wrap">
                <?php echo $insUsuario->autoEvaluacion(); ?>
            </div>
        </div>
        <img class="el-hero-img"
             src="<?php echo APP_URL; ?>app/views/img/backgrounds/welcome-bg.svg"
             alt="">
    </div>

    <!-- Colaboradores a Evaluar -->
    <div class="el-section-head" id="el-seccion-equipo">
        <div class="el-section-dot"></div>
        <div class="el-section-title">Colaboradores a Evaluar</div>
    </div>

    <div class="el-table-card">
        <?php echo $insUsuario->listarColaboradoresEvaluar(); ?>
    </div>

</div>

<!-- ══════════════════════════════════════════════════════════
     TOUR INTERACTIVO — Fase 3 · Módulo Evaluación
     Driver.js 1.3.1 · Carga diferida · Reversible sin impacto
══════════════════════════════════════════════════════════ -->

<button id="el-tour-btn"
        onclick="iniciarTourEvaluacion()"
        title="Tour guiado — cómo usar este módulo"
        aria-label="Iniciar tour guiado"
        style="position:fixed;bottom:28px;right:28px;z-index:890;
               width:50px;height:50px;border-radius:50%;border:none;
               background:linear-gradient(135deg,#005EB8,#0074e0);
               color:#fff;cursor:pointer;
               box-shadow:0 4px 16px rgba(0,94,184,.40);
               display:flex;align-items:center;justify-content:center;
               transition:transform .18s,box-shadow .18s;font-family:inherit;">
    <i class="ti ti-help" style="font-size:22px;pointer-events:none;line-height:1;"></i>
</button>

<div id="el-tour-tip"
     style="position:fixed;bottom:36px;right:88px;z-index:889;
            background:#1e293b;color:#fff;font-size:11px;font-weight:600;
            padding:5px 12px;border-radius:7px;white-space:nowrap;
            pointer-events:none;opacity:0;transition:opacity .18s;
            font-family:'Plus Jakarta Sans',sans-serif;">
    Tour guiado
    <div style="position:absolute;right:-5px;top:50%;transform:translateY(-50%);
                width:0;height:0;border:5px solid transparent;
                border-left-color:#1e293b;"></div>
</div>

<script>
(function(){
    var btn = document.getElementById('el-tour-btn');
    var tip = document.getElementById('el-tour-tip');
    if (!btn || !tip) return;
    btn.addEventListener('mouseenter', function(){
        tip.style.opacity   = '1';
        btn.style.transform = 'scale(1.08)';
        btn.style.boxShadow = '0 6px 24px rgba(0,94,184,.55)';
    });
    btn.addEventListener('mouseleave', function(){
        tip.style.opacity   = '0';
        btn.style.transform = '';
        btn.style.boxShadow = '0 4px 16px rgba(0,94,184,.40)';
    });
})();

function _elLoadDriver(cb) {
    if (window._fbDriverReady) { cb(); return; }
    var cssLink  = document.createElement('link');
    cssLink.rel  = 'stylesheet';
    cssLink.href = 'https://cdn.jsdelivr.net/npm/driver.js@1.3.1/dist/driver.css';
    document.head.appendChild(cssLink);
    var style    = document.createElement('style');
    style.id     = 'gh-driver-overrides';
    style.textContent =
        '.driver-popover{border-radius:16px!important;padding:0!important;' +
        'box-shadow:0 24px 64px rgba(0,0,0,.20),0 2px 8px rgba(0,0,0,.08)!important;' +
        'font-family:"Plus Jakarta Sans",-apple-system,sans-serif!important;' +
        'max-width:420px!important;min-width:300px!important;overflow:hidden!important;' +
        'border:1px solid #e2e8f0!important;}' +
        '.driver-popover *{box-sizing:border-box!important;}' +
        '.driver-popover-title{font-size:14px!important;font-weight:700!important;' +
        'color:#1e293b!important;padding:16px 40px 10px 20px!important;margin:0!important;' +
        'line-height:1.35!important;border-bottom:1px solid #f1f5f9!important;' +
        'display:flex!important;align-items:center!important;gap:8px!important;}' +
        '.driver-popover-description{font-size:12.5px!important;color:#475569!important;' +
        'line-height:1.75!important;padding:12px 20px 16px!important;margin:0!important;}' +
        '.driver-popover-footer{padding:10px 20px 12px!important;' +
        'border-top:1px solid #f1f5f9!important;display:flex!important;align-items:center!important;' +
        'justify-content:space-between!important;gap:8px!important;' +
        'background:#f8fafc!important;border-radius:0 0 16px 16px!important;}' +
        '.driver-popover-progress-text{font-size:10.5px!important;color:#94a3b8!important;' +
        'font-family:"DM Mono",monospace!important;letter-spacing:.04em!important;flex:1!important;}' +
        '.driver-popover-prev-btn{border:1.5px solid #e2e8f0!important;border-radius:8px!important;' +
        'padding:6px 15px!important;font-size:12px!important;font-weight:600!important;' +
        'cursor:pointer!important;background:#fff!important;color:#64748b!important;}' +
        '.driver-popover-prev-btn:hover{background:#f1f5f9!important;}' +
        '.driver-popover-next-btn{border:none!important;border-radius:8px!important;' +
        'padding:9px 22px!important;font-size:13px!important;font-weight:700!important;' +
        'letter-spacing:0!important;cursor:pointer!important;' +
        '-webkit-font-smoothing:antialiased!important;text-shadow:none!important;' +
        'background:#0058af!important;color:#fff!important;}' +
        '.driver-popover-next-btn:hover{background:#004a9a!important;}' +
        '.driver-popover-close-btn{color:#94a3b8!important;background:none!important;' +
        'border:none!important;cursor:pointer!important;font-size:17px!important;' +
        'line-height:1!important;opacity:.7!important;}' +
        '.driver-popover-close-btn:hover{opacity:1!important;}' +
        '.driver-popover-arrow-side-left .driver-popover-arrow{border-left-color:#fff!important;}' +
        '.driver-popover-arrow-side-right .driver-popover-arrow{border-right-color:#fff!important;}' +
        '.driver-popover-arrow-side-top .driver-popover-arrow{border-top-color:#fff!important;}' +
        '.driver-popover-arrow-side-bottom .driver-popover-arrow{border-bottom-color:#fff!important;}';
    document.head.appendChild(style);
    var scr    = document.createElement('script');
    scr.src    = 'https://cdn.jsdelivr.net/npm/driver.js@1.3.1/dist/driver.js.iife.js';
    scr.onload = function() { window._fbDriverReady = true; cb(); };
    document.head.appendChild(scr);
}

function iniciarTourEvaluacion() {
    _elLoadDriver(function() {
        var driverFn = window.driver.js.driver;

        function ic(name, color) {
            color = color || '#005EB8';
            return '<i class="ti ti-' + name + '" style="color:' + color +
                   ';font-size:16px;flex-shrink:0;"></i>';
        }

        var helpBtn =
            '<span style="display:inline-flex;align-items:center;justify-content:center;' +
            'width:20px;height:20px;border-radius:50%;vertical-align:middle;margin:0 2px;' +
            'background:linear-gradient(135deg,#005EB8,#0074e0);' +
            'color:#fff;font-size:11px;font-weight:700;line-height:1;">?</span>';

        var pasos = [];

        // ── 0. Bienvenida ───────────────────────────────────────────────────
        pasos.push({
            popover: {
                title:       ic('award') + ' Módulo de Evaluación',
                description: 'Aquí gestionas todas las evaluaciones del período activo: ' +
                             'tu <strong>autoevaluación</strong>, la evaluación a tu <strong>líder</strong> ' +
                             'y la evaluación a los <strong>colaboradores</strong> a tu cargo.',
                side: 'over', align: 'center'
            }
        });

        // ── 1. Hero ─────────────────────────────────────────────────────────
        if (document.querySelector('.el-hero')) {
            pasos.push({
                element: '.el-hero',
                popover: {
                    title:       ic('file-description') + ' Evaluación de Desempeño',
                    description: 'El banner describe el propósito de la evaluación del período. ' +
                                 'Debajo encontrarás el botón para iniciar o ver el estado de tu <strong>autoevaluación</strong>.',
                    side: 'bottom', align: 'start'
                }
            });
        }

        // ── 2. Botón autoevaluación ─────────────────────────────────────────
        if (document.querySelector('.el-status-wrap')) {
            pasos.push({
                element: '.el-status-wrap',
                popover: {
                    title:       ic('pencil') + ' Tu autoevaluación',
                    description: 'Este botón refleja el estado de tu autoevaluación.<br><br>' +
                                 'Si dice <strong>Iniciar autoevaluación</strong> aún está pendiente.<br>' +
                                 'Si dice <strong>Ver mi autoevaluación</strong> ya la completaste.',
                    side: 'bottom', align: 'start'
                }
            });
        }

        // ── 3. Sección colaboradores ────────────────────────────────────────
        if (document.querySelector('#el-seccion-equipo')) {
            pasos.push({
                element: '#el-seccion-equipo',
                popover: {
                    title:       ic('users') + ' Colaboradores a evaluar',
                    description: 'Lista de personas que debes evaluar en este período.<br><br>' +
                                 'Incluye tu <strong>líder directo</strong> y, si eres líder, ' +
                                 'también los <strong>colaboradores</strong> a tu cargo.',
                    side: 'bottom', align: 'start'
                }
            });
        }

        // ── 4. Tabla de evaluaciones ────────────────────────────────────────
        if (document.querySelector('.el-table-card')) {
            pasos.push({
                element: '.el-table-card',
                popover: {
                    title:       ic('layout-rows') + ' Estado de evaluaciones',
                    description: 'Cada fila muestra una persona a evaluar con:<br><br>' +
                                 ic('user','#64748b') + ' Nombre y cargo<br>' +
                                 ic('chart-bar','#64748b') + ' Porcentaje de avance de la evaluación<br>' +
                                 ic('circle-check','#15803d') + ' Badge de estado (Pendiente / Completada)<br>' +
                                 ic('player-play','#005EB8') + ' Botón para iniciar o continuar',
                    side: 'top', align: 'start'
                }
            });
        }

        // ── 5. Primera fila accionable ──────────────────────────────────────
        var primerBtn = document.querySelector('.el-table tbody tr .el-action-btn, .el-table tbody tr .el-action-primary');
        if (primerBtn) {
            pasos.push({
                element: primerBtn,
                popover: {
                    title:       ic('player-play') + ' Iniciar evaluación',
                    description: 'Haz clic en <strong>Evaluar</strong> para abrir el formulario de evaluación ' +
                                 'de esa persona. Las respuestas no se guardan automáticamente. Solo se registran al hacer clic en "Confirmar y guardar".',
                    side: 'left', align: 'center'
                }
            });
        }

        // ── Final ───────────────────────────────────────────────────────────
        pasos.push({
            popover: {
                title:       ic('circle-check','#15803d') + ' Listo para evaluar',
                description: 'Ya conoces el módulo de Evaluación. Recuerda completar todas las evaluaciones ' +
                             'antes del cierre del período. Puedes repetir este tour pulsando el botón ' +
                             helpBtn + ' en la esquina inferior derecha.',
                side: 'over', align: 'center'
            }
        });

        var tour;
        tour = driverFn({
            animate:      true,
            overlayColor: 'rgba(5, 15, 45, 0.60)',
            smoothScroll: true,
            showProgress: true,
            progressText: 'Paso {{current}} de {{total}}',
            nextBtnText:  'Siguiente',
            prevBtnText:  'Anterior',
            doneBtnText:  'Entendido',
            steps:        pasos,
            onNextClick: function() {
                if (tour.hasNextStep()) {
                    tour.moveNext();
                } else {
                    tour.destroy();
                }
            },
            onCloseClick: function() {
                tour.destroy();
            }
        });

        tour.drive();
    });
}
</script>
