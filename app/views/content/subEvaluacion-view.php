<?php
use app\models\competenciaModel;
$tooltipDesc = [];
try {
    $compModeloTooltip = new competenciaModel();
    $tooltipDesc       = $compModeloTooltip->getOpcionesCompetencia();
} catch (Exception $e) {
    $tooltipDesc = [];
}
?>

<style>
:root {
    --ev-accent:   #005EB8;
    --ev-accent2:  #0074E0;
    --ev-bg:       #f8fafc;
    --ev-card:     #ffffff;
    --ev-border:   #e2e8f0;
    --ev-muted:    #64748b;
    --ev-text:     #1e293b;
    --ev-success:  #059669;
    --ev-warning:  #d97706;
    --ev-radius:   16px;
}

.ev-wrap {
    min-height: 100vh;
    background: var(--ev-bg);
    display: flex;
    align-items: flex-start;
    justify-content: center;
    padding: 68px 16px 56px;
}

.ev-card {
    background: var(--ev-card);
    border-radius: var(--ev-radius);
    box-shadow: 0 4px 24px rgba(0,94,184,.08);
    width: 100%;
    max-width: 700px;
    padding: 40px 40px 32px;
    position: relative;
}

@media (max-width: 600px) {
    .ev-card { padding: 24px 18px 20px; }
}

/* Progress bar */
.ev-progress-wrap {
    background: #e2e8f0;
    border-radius: 99px;
    height: 6px;
    margin-bottom: 28px;
    overflow: hidden;
}
.ev-progress-bar {
    height: 100%;
    background: linear-gradient(90deg, #005EB8, #0074E0);
    border-radius: 99px;
    transition: width .4s ease;
    width: 0%;
}

/* Step indicator */
.ev-step {
    font-size: .78rem;
    font-weight: 700;
    color: var(--ev-accent);
    text-transform: uppercase;
    letter-spacing: .06em;
    margin-bottom: 8px;
}

/* Section */
.ev-section { display: none; }
.ev-section.active { display: block; }
/* ── Modal informativo de sección ────────────────────────────────── */
@keyframes evModalIn {
    from { opacity:0; transform:translateY(16px) scale(.97); }
    to   { opacity:1; transform:translateY(0)    scale(1);   }
}
/* ── Banner colapsable ───────────────────────────────────────────── */
.ev-banner {
    border:1.5px solid #bfdbfe;
    border-radius:12px;
    background:#eff6ff;
    margin-bottom:20px;
    overflow:hidden;
}
.ev-banner-header {
    display:flex;
    align-items:center;
    gap:10px;
    padding:10px 14px;
    cursor:pointer;
    user-select:none;
}
.ev-banner-header:hover { background:#dbeafe; }
.ev-banner-icon  { font-size:1.1rem; }
.ev-banner-title { font-size:.82rem; font-weight:700; color:#1e40af; flex:1; }
.ev-banner-arrow { font-size:.75rem; color:#3b82f6; transition:transform .3s; }
.ev-banner.open .ev-banner-arrow { transform:rotate(180deg); }
.ev-banner-body  { 
    font-size:.8rem; color:#1e40af; line-height:1.6;
    padding:0 14px; max-height:0; overflow:hidden;
    transition:max-height .3s ease, padding .3s ease;
}
.ev-banner.open .ev-banner-body { max-height:200px; padding:0 14px 12px; }


/* Title */
.ev-title {
    font-size: 1.35rem;
    font-weight: 800;
    color: var(--ev-text);
    margin-bottom: 12px;
    line-height: 1.3;
}

/* Question */
.ev-question {
    font-size: .9rem;
    color: var(--ev-muted);
    line-height: 1.6;
    background: #f1f5f9;
    border-radius: 10px;
    padding: 14px 16px;
    margin-bottom: 24px;
    border-left: 3px solid var(--ev-accent);
}

/* Option buttons */
.ev-options {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-bottom: 20px;
}

.ev-option {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 18px;
    border: 2px solid var(--ev-border);
    border-radius: 12px;
    cursor: pointer;
    background: #fff;
    transition: all .2s;
    text-align: left;
    font-family: inherit;
    font-size: .9rem;
    font-weight: 600;
    color: var(--ev-text);
    width: 100%;
}

.ev-option:hover {
    border-color: var(--ev-accent);
    background: #f0f7ff;
    color: #1e293b;
}

.ev-option.selected {
    border-color: var(--ev-accent);
    background: #e8f0fb;
    color: var(--ev-accent);
}

.ev-option-dot {
    width: 18px;
    height: 18px;
    border-radius: 50%;
    border: 2px solid var(--ev-border);
    flex-shrink: 0;
    transition: all .2s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.ev-option.selected .ev-option-dot {
    border-color: var(--ev-accent);
    background: var(--ev-accent);
}

.ev-option.selected .ev-option-dot::after {
    content: '';
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #fff;
    display: block;
}

/* Calificación badge colors */
.ev-opt-sobresaliente { --c: #065f46; --bg: #d1fae5; --bd: #6ee7b7; }
.ev-opt-acorde        { --c: #1e40af; --bg: #dbeafe; --bd: #93c5fd; }
.ev-opt-aceptable     { --c: #713f12; --bg: #fef9c3; --bd: #fde047; }
.ev-opt-necesita      { --c: #92400e; --bg: #fef3c7; --bd: #fcd34d; }
.ev-opt-insuficiente  { --c: #991b1b; --bg: #fee2e2; --bd: #fca5a5; }

.ev-option.ev-opt-sobresaliente.selected { border-color: #6ee7b7; background: #d1fae5; color: #065f46; }
.ev-option.ev-opt-acorde.selected        { border-color: #93c5fd; background: #dbeafe; color: #1e40af; }
.ev-option.ev-opt-aceptable.selected     { border-color: #fde047; background: #fef9c3; color: #713f12; }
.ev-option.ev-opt-necesita.selected      { border-color: #fcd34d; background: #fef3c7; color: #92400e; }
.ev-option.ev-opt-insuficiente.selected  { border-color: #fca5a5; background: #fee2e2; color: #991b1b; }

/* ── Tooltip nube ─────────────────────────────────────────── */
.ev-option { position: relative; }

.ev-tooltip {
    position: absolute;
    bottom: calc(100% + 12px);
    left: 50%;
    transform: translateX(-50%);
    width: min(320px, 90vw);
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 10px 14px;
    font-size: .76rem;
    font-weight: 400;
    color: #475569;
    line-height: 1.5;
    box-shadow: 0 8px 24px rgba(0,0,0,.12);
    pointer-events: none;
    opacity: 0;
    transition: opacity .2s ease, transform .2s ease;
    transform: translateX(-50%) translateY(4px);
    z-index: 999;
    text-align: left;
}

.ev-tooltip::after {
    content: '';
    position: absolute;
    top: 100%;
    left: 50%;
    transform: translateX(-50%);
    border: 7px solid transparent;
    border-top-color: #fff;
    filter: drop-shadow(0 2px 2px rgba(0,0,0,.08));
}

.ev-tooltip::before {
    content: '';
    position: absolute;
    top: 100%;
    left: 50%;
    transform: translateX(-50%);
    border: 8px solid transparent;
    border-top-color: #e2e8f0;
    margin-top: 1px;
}

.ev-option:hover .ev-tooltip,
.ev-option:focus .ev-tooltip {
    opacity: 1;
    transform: translateX(-50%) translateY(0);
}

/* Justification box */
.ev-justif {
    display: none;
    margin-top: 14px;
    background: #fffbeb;
    border: 1.5px solid #f59e0b;
    border-radius: 10px;
    padding: 14px 16px;
}

.ev-justif label {
    font-size: .82rem;
    font-weight: 700;
    color: #92400e;
    display: block;
    margin-bottom: 8px;
}

.ev-justif textarea {
    width: 100%;
    padding: 9px 12px;
    border: 1.5px solid #f59e0b;
    border-radius: 8px;
    font-size: .84rem;
    font-family: inherit;
    resize: vertical;
    outline: none;
    background: #fff;
    min-height: 72px;
    color: #1e293b;
}

/* Navigation buttons */
.ev-nav {
    display: flex;
    gap: 10px;
    margin-top: 24px;
}

.ev-btn {
    padding: 11px 24px;
    border-radius: 10px;
    font-weight: 700;
    font-size: .88rem;
    cursor: pointer;
    border: none;
    font-family: inherit;
    transition: all .2s;
}

.ev-btn-primary {
    background: var(--ev-accent);
    color: #fff;
    flex: 1;
}
.ev-btn-primary:hover { background: var(--ev-accent2); }

.ev-btn-back {
    background: #f1f5f9;
    color: var(--ev-muted);
    min-width: 100px;
}
.ev-btn-back:hover { background: #e2e8f0; }

.ev-btn-submit {
    background: #059669;
    color: #fff;
    flex: 1;
}
.ev-btn-submit:hover { background: #047857; }

/* Confirm section */
.ev-confirm-icon {
    text-align: center;
    font-size: 3rem;
    margin-bottom: 12px;
}
.ev-confirm-title {
    font-size: 1.3rem;
    font-weight: 800;
    text-align: center;
    color: var(--ev-text);
    margin-bottom: 8px;
}
.ev-confirm-sub {
    text-align: center;
    color: var(--ev-muted);
    font-size: .88rem;
    margin-bottom: 24px;
}
</style>


<!-- Modal informativo de sección -->
<div id="evSectionModal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.6);
     z-index:9999;align-items:center;justify-content:center;padding:16px;">
    <div style="background:#fff;border-radius:20px;padding:32px 28px;width:min(520px,95vw);
                box-shadow:0 24px 64px rgba(0,0,0,.2);animation:evModalIn .3s ease;">
        <div id="evSectionModalIcon"  style="font-size:2.2rem;margin-bottom:12px;text-align:center;"></div>
        <div id="evSectionModalTitle" style="font-size:1rem;font-weight:800;color:#1e3a5f;margin-bottom:10px;text-align:center;"></div>
        <div id="evSectionModalText"  style="font-size:.84rem;color:#475569;line-height:1.7;text-align:center;margin-bottom:24px;"></div>
        <div style="text-align:center;">
            <button onclick="cerrarSectionModal()"
                    style="padding:10px 32px;background:#0058af;color:#fff;border:none;
                           border-radius:10px;font-size:.88rem;font-weight:700;cursor:pointer;">
                Continuar →
            </button>
        </div>
    </div>
</div>

<!-- Modal bienvenida -->
<div id="evBienvenidaModal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.7);
     z-index:10000;align-items:center;justify-content:center;padding:16px;">
    <div style="background:#fff;border-radius:20px;padding:36px 32px;width:min(560px,95vw);
                box-shadow:0 24px 64px rgba(0,0,0,.25);animation:evModalIn .3s ease;text-align:center;">
        <div style="font-size:2.4rem;margin-bottom:14px;"><i class="ti ti-building-hospital" style="color:#0058af;"></i></div>
        <div style="font-size:1.05rem;font-weight:800;color:#1e3a5f;margin-bottom:14px;">
            Evaluación del Desempeño V2 2026
        </div>
        <div style="font-size:.85rem;color:#475569;line-height:1.75;margin-bottom:28px;text-align:left;
                    background:#f8fafc;border-radius:12px;padding:16px 20px;border:1px solid #e2e8f0;">
            En la Clínica Zayma, creemos que cada acción cuenta y cada colaborador es pieza clave en nuestra misión de servir con excelencia y calidez humana, para trascender en la vida de las personas. Este formulario es una oportunidad para evaluar el desempeño de tu colaborador con sinceridad y objetividad, destacando sus fortalezas y trazando juntos el camino hacia la excelencia.
        </div>
        <button onclick="cerrarBienvenidaModal()"
                style="padding:12px 36px;background:#0058af;color:#fff;border:none;
                       border-radius:12px;font-size:.9rem;font-weight:700;cursor:pointer;
                       box-shadow:0 4px 14px rgba(0,88,175,.3);">
            Comenzar evaluación →
        </button>
    </div>
</div>


<div class="ev-wrap">
    <div class="ev-card">

        <!-- Header -->
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;
                    padding-bottom:20px;border-bottom:1.5px solid var(--ev-border);">
            <div style="width:44px;height:44px;border-radius:12px;background:#e8f0fb;
                        display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="22" height="22" fill="#005EB8" viewBox="0 0 24 24">
                    <path d="M9 11l3 3L22 4M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/>
                </svg>
            </div>
            <div>
                <div style="font-size:1rem;font-weight:800;color:var(--ev-text);">Evaluación de Colaborador</div>
                <div style="font-size:.8rem;color:var(--ev-muted);">Clínica Zayma · Evaluación de Desempeño</div>
            </div>
        </div>

        <!-- Progress -->
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
            <span class="ev-step" id="ev-step">Pregunta 1 de 11</span>
            <span style="font-size:.78rem;color:var(--ev-muted);" id="ev-pct">0%</span>
        </div>
        <div class="ev-progress-wrap">
            <div class="ev-progress-bar" id="ev-progress"></div>
        </div>

        <!-- Form -->
        <form id="evaluationForm" class="FormularioAjax"
              action="<?php echo APP_URL; ?>app/ajax/formulariosAjax.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="modulo_evaluacion" value="registrarEvaluacionColaborador">
                    <?php
            $idempleado = $_POST['empleadoevaluado'] 
                        ?? $_SESSION['idempleado_evaluar'] 
                        ?? '';
            if (!empty($idempleado)) {
                $_SESSION['idempleado_evaluar'] = $idempleado;
            }
        ?>
        <input type="hidden" name="empleadoevaluado" value="<?php echo htmlspecialchars($idempleado); ?>">

        <div class="ev-section active" id="ev-section-1">
            <!-- Banner colapsable sección 1 -->
            <div class="ev-banner" id="ev-banner-1">
                <div class="ev-banner-header" onclick="toggleBanner(1)">
                    <span class="ev-banner-icon"><i class="ti ti-sparkles"></i></span>
                    <span class="ev-banner-title">Dimensión 1: Viviendo Nuestros Valores</span>
                    <span class="ev-banner-arrow">▼</span>
                </div>
                <div class="ev-banner-body">En este espacio, reflexionaremos sobre cómo nuestros principios y valores se transforman en acciones diarias que nos definen como equipo y como institución. La calidez humana, la innovación, la integridad y los demás principios y valores no son solo palabras, son el motor que impulsa cada decisión y cada interacción.</div>
            </div>
            <div class="ev-step" id="ev-step">Pregunta 1 de 11</div>
            <h2 class="ev-title">Calidez Humana y Servicio con Propósito</h2>
            <div class="ev-question">¿El evaluado refleja cercanía y disposición en su atención, ofreciendo un servicio humanizado que priorice las necesidades y el bienestar de los demás con un compromiso genuino?</div>
            <div class="ev-options">
                <button type="button" class="ev-option ev-opt-sobresaliente"
                        onclick="evSelectOption(this, 1, 'Sobresaliente')">
                    <span class="ev-option-dot"></span>
                    <span><i class="ti ti-star-filled" style="font-size:13px;"></i> Sobresaliente</span>
                
                    <?php if (!empty($tooltipDesc[1][5])): ?><span class="ev-tooltip"><?= htmlspecialchars($tooltipDesc[1][5]) ?></span><?php endif; ?>
                </button>
                <button type="button" class="ev-option ev-opt-acorde"
                        onclick="evSelectOption(this, 1, 'Acorde')">
                    <span class="ev-option-dot"></span>
                    <span><?= icon('check-circle', 13) ?> Acorde</span>
                
                    <?php if (!empty($tooltipDesc[1][4])): ?><span class="ev-tooltip"><?= htmlspecialchars($tooltipDesc[1][4]) ?></span><?php endif; ?>
                </button>
                <button type="button" class="ev-option ev-opt-aceptable"
                        onclick="evSelectOption(this, 1, 'Aceptable')">
                    <span class="ev-option-dot"></span>
                    <span><?= icon('thumbs-up', 13) ?> Aceptable</span>
                
                    <?php if (!empty($tooltipDesc[1][3])): ?><span class="ev-tooltip"><?= htmlspecialchars($tooltipDesc[1][3]) ?></span><?php endif; ?>
                </button>
                <button type="button" class="ev-option ev-opt-necesita"
                        onclick="evSelectOption(this, 1, 'Requiere mejora')">
                    <span class="ev-option-dot"></span>
                    <span><?= icon('alert-circle', 13) ?> Requiere mejora</span>
                
                    <?php if (!empty($tooltipDesc[1][2])): ?><span class="ev-tooltip"><?= htmlspecialchars($tooltipDesc[1][2]) ?></span><?php endif; ?>
                </button>
                <button type="button" class="ev-option ev-opt-insuficiente"
                        onclick="evSelectOption(this, 1, 'Insuficiente')">
                    <span class="ev-option-dot"></span>
                    <span><?= icon('x-circle', 13) ?> Insuficiente</span>
                
                    <?php if (!empty($tooltipDesc[1][1])): ?><span class="ev-tooltip"><?= htmlspecialchars($tooltipDesc[1][1]) ?></span><?php endif; ?>
                </button>
            </div>
            <div class="ev-justif" id="ev-justif-1">
                <label><?= icon('alert-circle', 13) ?> Calificación extrema — justificación obligatoria</label>
                <textarea id="ev-jtext-1" name="justificacion1" maxlength="500"
                          placeholder="Explica brevemente el motivo de esta calificación..."></textarea>
            </div>
            <input type="hidden" name="pregunta1" id="ev-input-1" required>
            <div class="ev-nav"><button type="button" class="ev-btn ev-btn-primary" onclick="evNext(1, 11)">Siguiente →</button></div>
        </div>
        <div class="ev-section" id="ev-section-2">
            <div class="ev-step" id="ev-step-2">Pregunta 2 de 11</div>
            <h2 class="ev-title">Liderazgo e Integridad en la Acción</h2>
            <div class="ev-question">¿El evaluado actúa con integridad al ser confiable, honesto y ético, liderando con el ejemplo para fomentar un ambiente de respeto y confianza?</div>
            <div class="ev-options">
                <button type="button" class="ev-option ev-opt-sobresaliente"
                        onclick="evSelectOption(this, 2, 'Sobresaliente')">
                    <span class="ev-option-dot"></span>
                    <span><i class="ti ti-star-filled" style="font-size:13px;"></i> Sobresaliente</span>
                
                    <?php if (!empty($tooltipDesc[2][5])): ?><span class="ev-tooltip"><?= htmlspecialchars($tooltipDesc[2][5]) ?></span><?php endif; ?>
                </button>
                <button type="button" class="ev-option ev-opt-acorde"
                        onclick="evSelectOption(this, 2, 'Acorde')">
                    <span class="ev-option-dot"></span>
                    <span><?= icon('check-circle', 13) ?> Acorde</span>
                
                    <?php if (!empty($tooltipDesc[2][4])): ?><span class="ev-tooltip"><?= htmlspecialchars($tooltipDesc[2][4]) ?></span><?php endif; ?>
                </button>
                <button type="button" class="ev-option ev-opt-aceptable"
                        onclick="evSelectOption(this, 2, 'Aceptable')">
                    <span class="ev-option-dot"></span>
                    <span><?= icon('thumbs-up', 13) ?> Aceptable</span>
                
                    <?php if (!empty($tooltipDesc[2][3])): ?><span class="ev-tooltip"><?= htmlspecialchars($tooltipDesc[2][3]) ?></span><?php endif; ?>
                </button>
                <button type="button" class="ev-option ev-opt-necesita"
                        onclick="evSelectOption(this, 2, 'Requiere mejora')">
                    <span class="ev-option-dot"></span>
                    <span><?= icon('alert-circle', 13) ?> Requiere mejora</span>
                
                    <?php if (!empty($tooltipDesc[2][2])): ?><span class="ev-tooltip"><?= htmlspecialchars($tooltipDesc[2][2]) ?></span><?php endif; ?>
                </button>
                <button type="button" class="ev-option ev-opt-insuficiente"
                        onclick="evSelectOption(this, 2, 'Insuficiente')">
                    <span class="ev-option-dot"></span>
                    <span><?= icon('x-circle', 13) ?> Insuficiente</span>
                
                    <?php if (!empty($tooltipDesc[2][1])): ?><span class="ev-tooltip"><?= htmlspecialchars($tooltipDesc[2][1]) ?></span><?php endif; ?>
                </button>
            </div>
            <div class="ev-justif" id="ev-justif-2">
                <label><?= icon('alert-circle', 13) ?> Calificación extrema — justificación obligatoria</label>
                <textarea id="ev-jtext-2" name="justificacion2" maxlength="500"
                          placeholder="Explica brevemente el motivo de esta calificación..."></textarea>
            </div>
            <input type="hidden" name="pregunta2" id="ev-input-2" required>
            <div class="ev-nav"><button type="button" class="ev-btn ev-btn-back" onclick="evPrev(2)">← Anterior</button><button type="button" class="ev-btn ev-btn-primary" onclick="evNext(2, 11)">Siguiente →</button></div>
        </div>
        <div class="ev-section" id="ev-section-3">
            <div class="ev-step" id="ev-step-3">Pregunta 3 de 11</div>
            <h2 class="ev-title">Competitividad, Innovación y Adaptabilidad</h2>
            <div class="ev-question">¿El evaluado muestra iniciativa para proponer mejoras, adaptarse rápidamente a los cambios y ofrecer soluciones innovadoras que impulsen la excelencia?</div>
            <div class="ev-options">
                <button type="button" class="ev-option ev-opt-sobresaliente"
                        onclick="evSelectOption(this, 3, 'Sobresaliente')">
                    <span class="ev-option-dot"></span>
                    <span><i class="ti ti-star-filled" style="font-size:13px;"></i> Sobresaliente</span>
                
                    <?php if (!empty($tooltipDesc[3][5])): ?><span class="ev-tooltip"><?= htmlspecialchars($tooltipDesc[3][5]) ?></span><?php endif; ?>
                </button>
                <button type="button" class="ev-option ev-opt-acorde"
                        onclick="evSelectOption(this, 3, 'Acorde')">
                    <span class="ev-option-dot"></span>
                    <span><?= icon('check-circle', 13) ?> Acorde</span>
                
                    <?php if (!empty($tooltipDesc[3][4])): ?><span class="ev-tooltip"><?= htmlspecialchars($tooltipDesc[3][4]) ?></span><?php endif; ?>
                </button>
                <button type="button" class="ev-option ev-opt-aceptable"
                        onclick="evSelectOption(this, 3, 'Aceptable')">
                    <span class="ev-option-dot"></span>
                    <span><?= icon('thumbs-up', 13) ?> Aceptable</span>
                
                    <?php if (!empty($tooltipDesc[3][3])): ?><span class="ev-tooltip"><?= htmlspecialchars($tooltipDesc[3][3]) ?></span><?php endif; ?>
                </button>
                <button type="button" class="ev-option ev-opt-necesita"
                        onclick="evSelectOption(this, 3, 'Requiere mejora')">
                    <span class="ev-option-dot"></span>
                    <span><?= icon('alert-circle', 13) ?> Requiere mejora</span>
                
                    <?php if (!empty($tooltipDesc[3][2])): ?><span class="ev-tooltip"><?= htmlspecialchars($tooltipDesc[3][2]) ?></span><?php endif; ?>
                </button>
                <button type="button" class="ev-option ev-opt-insuficiente"
                        onclick="evSelectOption(this, 3, 'Insuficiente')">
                    <span class="ev-option-dot"></span>
                    <span><?= icon('x-circle', 13) ?> Insuficiente</span>
                
                    <?php if (!empty($tooltipDesc[3][1])): ?><span class="ev-tooltip"><?= htmlspecialchars($tooltipDesc[3][1]) ?></span><?php endif; ?>
                </button>
            </div>
            <div class="ev-justif" id="ev-justif-3">
                <label><?= icon('alert-circle', 13) ?> Calificación extrema — justificación obligatoria</label>
                <textarea id="ev-jtext-3" name="justificacion3" maxlength="500"
                          placeholder="Explica brevemente el motivo de esta calificación..."></textarea>
            </div>
            <input type="hidden" name="pregunta3" id="ev-input-3" required>
            <div class="ev-nav"><button type="button" class="ev-btn ev-btn-back" onclick="evPrev(3)">← Anterior</button><button type="button" class="ev-btn ev-btn-primary" onclick="evNext(3, 11)">Siguiente →</button></div>
        </div>
        <div class="ev-section" id="ev-section-4">
            <div class="ev-step" id="ev-step-4">Pregunta 4 de 11</div>
            <h2 class="ev-title">Comunicación Asertiva y Sentido de Pertenencia</h2>
            <div class="ev-question">¿El evaluado fomenta una comunicación abierta y efectiva, participa activamente en las iniciativas institucionales y contribuye con sentido de pertenencia al propósito y cuidado de la institución?</div>
            <div class="ev-options">
                <button type="button" class="ev-option ev-opt-sobresaliente"
                        onclick="evSelectOption(this, 4, 'Sobresaliente')">
                    <span class="ev-option-dot"></span>
                    <span><i class="ti ti-star-filled" style="font-size:13px;"></i> Sobresaliente</span>
                
                    <?php if (!empty($tooltipDesc[4][5])): ?><span class="ev-tooltip"><?= htmlspecialchars($tooltipDesc[4][5]) ?></span><?php endif; ?>
                </button>
                <button type="button" class="ev-option ev-opt-acorde"
                        onclick="evSelectOption(this, 4, 'Acorde')">
                    <span class="ev-option-dot"></span>
                    <span><?= icon('check-circle', 13) ?> Acorde</span>
                
                    <?php if (!empty($tooltipDesc[4][4])): ?><span class="ev-tooltip"><?= htmlspecialchars($tooltipDesc[4][4]) ?></span><?php endif; ?>
                </button>
                <button type="button" class="ev-option ev-opt-aceptable"
                        onclick="evSelectOption(this, 4, 'Aceptable')">
                    <span class="ev-option-dot"></span>
                    <span><?= icon('thumbs-up', 13) ?> Aceptable</span>
                
                    <?php if (!empty($tooltipDesc[4][3])): ?><span class="ev-tooltip"><?= htmlspecialchars($tooltipDesc[4][3]) ?></span><?php endif; ?>
                </button>
                <button type="button" class="ev-option ev-opt-necesita"
                        onclick="evSelectOption(this, 4, 'Requiere mejora')">
                    <span class="ev-option-dot"></span>
                    <span><?= icon('alert-circle', 13) ?> Requiere mejora</span>
                
                    <?php if (!empty($tooltipDesc[4][2])): ?><span class="ev-tooltip"><?= htmlspecialchars($tooltipDesc[4][2]) ?></span><?php endif; ?>
                </button>
                <button type="button" class="ev-option ev-opt-insuficiente"
                        onclick="evSelectOption(this, 4, 'Insuficiente')">
                    <span class="ev-option-dot"></span>
                    <span><?= icon('x-circle', 13) ?> Insuficiente</span>
                
                    <?php if (!empty($tooltipDesc[4][1])): ?><span class="ev-tooltip"><?= htmlspecialchars($tooltipDesc[4][1]) ?></span><?php endif; ?>
                </button>
            </div>
            <div class="ev-justif" id="ev-justif-4">
                <label><?= icon('alert-circle', 13) ?> Calificación extrema — justificación obligatoria</label>
                <textarea id="ev-jtext-4" name="justificacion4" maxlength="500"
                          placeholder="Explica brevemente el motivo de esta calificación..."></textarea>
            </div>
            <input type="hidden" name="pregunta4" id="ev-input-4" required>
            <div class="ev-nav"><button type="button" class="ev-btn ev-btn-back" onclick="evPrev(4)">← Anterior</button><button type="button" class="ev-btn ev-btn-primary" onclick="evNext(4, 11)">Siguiente →</button></div>
        </div>
        <div class="ev-section" id="ev-section-5">
            <!-- Banner colapsable sección 5 -->
            <div class="ev-banner" id="ev-banner-5">
                <div class="ev-banner-header" onclick="toggleBanner(5)">
                    <span class="ev-banner-icon"><i class="ti ti-settings"></i></span>
                    <span class="ev-banner-title">Funciones Generales</span>
                    <span class="ev-banner-arrow">▼</span>
                </div>
                <div class="ev-banner-body">En esta sesión evaluaremos aquellos aspectos fundamentales que sustentan el éxito colectivo: el compromiso con la calidad, la adherencia a las normas internas, la participación activa en el desarrollo profesional y el cuidado por la seguridad y salud en el trabajo.</div>
            </div>
            <div class="ev-step" id="ev-step-5">Pregunta 5 de 11</div>
            <h2 class="ev-title">Compromiso con la calidad</h2>
            <div class="ev-question">¿De qué manera el evaluado ha contribuido al cumplimiento de los estándares institucionales (políticas, planes, reglamentos y procedimientos), comunicando oportunamente incidentes o no conformidades y asegurando que su trabajo impacte positivamente en la experiencia de nuestros pacientes?</div>
            <div class="ev-options">
                <button type="button" class="ev-option ev-opt-sobresaliente"
                        onclick="evSelectOption(this, 5, 'Sobresaliente')">
                    <span class="ev-option-dot"></span>
                    <span><i class="ti ti-star-filled" style="font-size:13px;"></i> Sobresaliente</span>
                
                    <?php if (!empty($tooltipDesc[5][5])): ?><span class="ev-tooltip"><?= htmlspecialchars($tooltipDesc[5][5]) ?></span><?php endif; ?>
                </button>
                <button type="button" class="ev-option ev-opt-acorde"
                        onclick="evSelectOption(this, 5, 'Acorde')">
                    <span class="ev-option-dot"></span>
                    <span><?= icon('check-circle', 13) ?> Acorde</span>
                
                    <?php if (!empty($tooltipDesc[5][4])): ?><span class="ev-tooltip"><?= htmlspecialchars($tooltipDesc[5][4]) ?></span><?php endif; ?>
                </button>
                <button type="button" class="ev-option ev-opt-aceptable"
                        onclick="evSelectOption(this, 5, 'Aceptable')">
                    <span class="ev-option-dot"></span>
                    <span><?= icon('thumbs-up', 13) ?> Aceptable</span>
                
                    <?php if (!empty($tooltipDesc[5][3])): ?><span class="ev-tooltip"><?= htmlspecialchars($tooltipDesc[5][3]) ?></span><?php endif; ?>
                </button>
                <button type="button" class="ev-option ev-opt-necesita"
                        onclick="evSelectOption(this, 5, 'Requiere mejora')">
                    <span class="ev-option-dot"></span>
                    <span><?= icon('alert-circle', 13) ?> Requiere mejora</span>
                
                    <?php if (!empty($tooltipDesc[5][2])): ?><span class="ev-tooltip"><?= htmlspecialchars($tooltipDesc[5][2]) ?></span><?php endif; ?>
                </button>
                <button type="button" class="ev-option ev-opt-insuficiente"
                        onclick="evSelectOption(this, 5, 'Insuficiente')">
                    <span class="ev-option-dot"></span>
                    <span><?= icon('x-circle', 13) ?> Insuficiente</span>
                
                    <?php if (!empty($tooltipDesc[5][1])): ?><span class="ev-tooltip"><?= htmlspecialchars($tooltipDesc[5][1]) ?></span><?php endif; ?>
                </button>
            </div>
            <div class="ev-justif" id="ev-justif-5">
                <label><?= icon('alert-circle', 13) ?> Calificación extrema — justificación obligatoria</label>
                <textarea id="ev-jtext-5" name="justificacion5" maxlength="500"
                          placeholder="Explica brevemente el motivo de esta calificación..."></textarea>
            </div>
            <input type="hidden" name="pregunta5" id="ev-input-5" required>
            <div class="ev-nav"><button type="button" class="ev-btn ev-btn-back" onclick="evPrev(5)">← Anterior</button><button type="button" class="ev-btn ev-btn-primary" onclick="evNext(5, 11)">Siguiente →</button></div>
        </div>
        <div class="ev-section" id="ev-section-6">
            <div class="ev-step" id="ev-step-6">Pregunta 6 de 11</div>
            <h2 class="ev-title">Compromiso institucional y cumplimiento de normas internas</h2>
            <div class="ev-question">¿De qué manera el evaluado se convierte en un modelo de respeto y pertenencia, inspirando con su puntualidad, cuidado de la imagen profesional y compromiso con las normas, mientras cultiva un entorno de armonía y excelencia?</div>
            <div class="ev-options">
                <button type="button" class="ev-option ev-opt-sobresaliente"
                        onclick="evSelectOption(this, 6, 'Sobresaliente')">
                    <span class="ev-option-dot"></span>
                    <span><i class="ti ti-star-filled" style="font-size:13px;"></i> Sobresaliente</span>
                
                    <?php if (!empty($tooltipDesc[6][5])): ?><span class="ev-tooltip"><?= htmlspecialchars($tooltipDesc[6][5]) ?></span><?php endif; ?>
                </button>
                <button type="button" class="ev-option ev-opt-acorde"
                        onclick="evSelectOption(this, 6, 'Acorde')">
                    <span class="ev-option-dot"></span>
                    <span><?= icon('check-circle', 13) ?> Acorde</span>
                
                    <?php if (!empty($tooltipDesc[6][4])): ?><span class="ev-tooltip"><?= htmlspecialchars($tooltipDesc[6][4]) ?></span><?php endif; ?>
                </button>
                <button type="button" class="ev-option ev-opt-aceptable"
                        onclick="evSelectOption(this, 6, 'Aceptable')">
                    <span class="ev-option-dot"></span>
                    <span><?= icon('thumbs-up', 13) ?> Aceptable</span>
                
                    <?php if (!empty($tooltipDesc[6][3])): ?><span class="ev-tooltip"><?= htmlspecialchars($tooltipDesc[6][3]) ?></span><?php endif; ?>
                </button>
                <button type="button" class="ev-option ev-opt-necesita"
                        onclick="evSelectOption(this, 6, 'Requiere mejora')">
                    <span class="ev-option-dot"></span>
                    <span><?= icon('alert-circle', 13) ?> Requiere mejora</span>
                
                    <?php if (!empty($tooltipDesc[6][2])): ?><span class="ev-tooltip"><?= htmlspecialchars($tooltipDesc[6][2]) ?></span><?php endif; ?>
                </button>
                <button type="button" class="ev-option ev-opt-insuficiente"
                        onclick="evSelectOption(this, 6, 'Insuficiente')">
                    <span class="ev-option-dot"></span>
                    <span><?= icon('x-circle', 13) ?> Insuficiente</span>
                
                    <?php if (!empty($tooltipDesc[6][1])): ?><span class="ev-tooltip"><?= htmlspecialchars($tooltipDesc[6][1]) ?></span><?php endif; ?>
                </button>
            </div>
            <div class="ev-justif" id="ev-justif-6">
                <label><?= icon('alert-circle', 13) ?> Calificación extrema — justificación obligatoria</label>
                <textarea id="ev-jtext-6" name="justificacion6" maxlength="500"
                          placeholder="Explica brevemente el motivo de esta calificación..."></textarea>
            </div>
            <input type="hidden" name="pregunta6" id="ev-input-6" required>
            <div class="ev-nav"><button type="button" class="ev-btn ev-btn-back" onclick="evPrev(6)">← Anterior</button><button type="button" class="ev-btn ev-btn-primary" onclick="evNext(6, 11)">Siguiente →</button></div>
        </div>
        <div class="ev-section" id="ev-section-7">
            <div class="ev-step" id="ev-step-7">Pregunta 7 de 11</div>
            <h2 class="ev-title">Participación y formación continua</h2>
            <div class="ev-question">¿En qué grado el colaborador demuestra su pasión por aprender y enseñar, integrando conocimientos adquiridos en cada interacción y potenciando la cooperación en su equipo para crear soluciones que trasciendan en nuestra misión de humanización y calidad?</div>
            <div class="ev-options">
                <button type="button" class="ev-option ev-opt-sobresaliente"
                        onclick="evSelectOption(this, 7, 'Sobresaliente')">
                    <span class="ev-option-dot"></span>
                    <span><i class="ti ti-star-filled" style="font-size:13px;"></i> Sobresaliente</span>
                
                    <?php if (!empty($tooltipDesc[7][5])): ?><span class="ev-tooltip"><?= htmlspecialchars($tooltipDesc[7][5]) ?></span><?php endif; ?>
                </button>
                <button type="button" class="ev-option ev-opt-acorde"
                        onclick="evSelectOption(this, 7, 'Acorde')">
                    <span class="ev-option-dot"></span>
                    <span><?= icon('check-circle', 13) ?> Acorde</span>
                
                    <?php if (!empty($tooltipDesc[7][4])): ?><span class="ev-tooltip"><?= htmlspecialchars($tooltipDesc[7][4]) ?></span><?php endif; ?>
                </button>
                <button type="button" class="ev-option ev-opt-aceptable"
                        onclick="evSelectOption(this, 7, 'Aceptable')">
                    <span class="ev-option-dot"></span>
                    <span><?= icon('thumbs-up', 13) ?> Aceptable</span>
                
                    <?php if (!empty($tooltipDesc[7][3])): ?><span class="ev-tooltip"><?= htmlspecialchars($tooltipDesc[7][3]) ?></span><?php endif; ?>
                </button>
                <button type="button" class="ev-option ev-opt-necesita"
                        onclick="evSelectOption(this, 7, 'Requiere mejora')">
                    <span class="ev-option-dot"></span>
                    <span><?= icon('alert-circle', 13) ?> Requiere mejora</span>
                
                    <?php if (!empty($tooltipDesc[7][2])): ?><span class="ev-tooltip"><?= htmlspecialchars($tooltipDesc[7][2]) ?></span><?php endif; ?>
                </button>
                <button type="button" class="ev-option ev-opt-insuficiente"
                        onclick="evSelectOption(this, 7, 'Insuficiente')">
                    <span class="ev-option-dot"></span>
                    <span><?= icon('x-circle', 13) ?> Insuficiente</span>
                
                    <?php if (!empty($tooltipDesc[7][1])): ?><span class="ev-tooltip"><?= htmlspecialchars($tooltipDesc[7][1]) ?></span><?php endif; ?>
                </button>
            </div>
            <div class="ev-justif" id="ev-justif-7">
                <label><?= icon('alert-circle', 13) ?> Calificación extrema — justificación obligatoria</label>
                <textarea id="ev-jtext-7" name="justificacion7" maxlength="500"
                          placeholder="Explica brevemente el motivo de esta calificación..."></textarea>
            </div>
            <input type="hidden" name="pregunta7" id="ev-input-7" required>
            <div class="ev-nav"><button type="button" class="ev-btn ev-btn-back" onclick="evPrev(7)">← Anterior</button><button type="button" class="ev-btn ev-btn-primary" onclick="evNext(7, 11)">Siguiente →</button></div>
        </div>
        <div class="ev-section" id="ev-section-8">
            <div class="ev-step" id="ev-step-8">Pregunta 8 de 11</div>
            <h2 class="ev-title">Gestión de Seguridad y Salud en el Trabajo (SST)</h2>
            <div class="ev-question">¿Cómo evalúas el rol del colaborador en la promoción de un entorno seguro, cumpliendo con las normas de bioseguridad, participando en programas preventivos y actuando de manera efectiva frente a situaciones de riesgo o emergencia?</div>
            <div class="ev-options">
                <button type="button" class="ev-option ev-opt-sobresaliente"
                        onclick="evSelectOption(this, 8, 'Sobresaliente')">
                    <span class="ev-option-dot"></span>
                    <span><i class="ti ti-star-filled" style="font-size:13px;"></i> Sobresaliente</span>
                
                    <?php if (!empty($tooltipDesc[8][5])): ?><span class="ev-tooltip"><?= htmlspecialchars($tooltipDesc[8][5]) ?></span><?php endif; ?>
                </button>
                <button type="button" class="ev-option ev-opt-acorde"
                        onclick="evSelectOption(this, 8, 'Acorde')">
                    <span class="ev-option-dot"></span>
                    <span><?= icon('check-circle', 13) ?> Acorde</span>
                
                    <?php if (!empty($tooltipDesc[8][4])): ?><span class="ev-tooltip"><?= htmlspecialchars($tooltipDesc[8][4]) ?></span><?php endif; ?>
                </button>
                <button type="button" class="ev-option ev-opt-aceptable"
                        onclick="evSelectOption(this, 8, 'Aceptable')">
                    <span class="ev-option-dot"></span>
                    <span><?= icon('thumbs-up', 13) ?> Aceptable</span>
                
                    <?php if (!empty($tooltipDesc[8][3])): ?><span class="ev-tooltip"><?= htmlspecialchars($tooltipDesc[8][3]) ?></span><?php endif; ?>
                </button>
                <button type="button" class="ev-option ev-opt-necesita"
                        onclick="evSelectOption(this, 8, 'Requiere mejora')">
                    <span class="ev-option-dot"></span>
                    <span><?= icon('alert-circle', 13) ?> Requiere mejora</span>
                
                    <?php if (!empty($tooltipDesc[8][2])): ?><span class="ev-tooltip"><?= htmlspecialchars($tooltipDesc[8][2]) ?></span><?php endif; ?>
                </button>
                <button type="button" class="ev-option ev-opt-insuficiente"
                        onclick="evSelectOption(this, 8, 'Insuficiente')">
                    <span class="ev-option-dot"></span>
                    <span><?= icon('x-circle', 13) ?> Insuficiente</span>
                
                    <?php if (!empty($tooltipDesc[8][1])): ?><span class="ev-tooltip"><?= htmlspecialchars($tooltipDesc[8][1]) ?></span><?php endif; ?>
                </button>
            </div>
            <div class="ev-justif" id="ev-justif-8">
                <label><?= icon('alert-circle', 13) ?> Calificación extrema — justificación obligatoria</label>
                <textarea id="ev-jtext-8" name="justificacion8" maxlength="500"
                          placeholder="Explica brevemente el motivo de esta calificación..."></textarea>
            </div>
            <input type="hidden" name="pregunta8" id="ev-input-8" required>
            <div class="ev-nav"><button type="button" class="ev-btn ev-btn-back" onclick="evPrev(8)">← Anterior</button><button type="button" class="ev-btn ev-btn-primary" onclick="evNext(8, 11)">Siguiente →</button></div>
        </div>
        <div class="ev-section" id="ev-section-9">
            <!-- Banner colapsable sección 9 -->
            <div class="ev-banner" id="ev-banner-9">
                <div class="ev-banner-header" onclick="toggleBanner(9)">
                    <span class="ev-banner-icon"><i class="ti ti-rocket"></i></span>
                    <span class="ev-banner-title">Potenciando el Impacto en Funciones Específicas</span>
                    <span class="ev-banner-arrow">▼</span>
                </div>
                <div class="ev-banner-body">En esta sección, queremos ir más allá del simple cumplimiento de tareas: buscamos descubrir cómo el talento del colaborador transforma la rutina en resultados extraordinarios. Evaluaremos cómo su desempeño impulsa un entorno de colaboración positiva y cómo aprovecha al máximo el tiempo y los recursos.</div>
            </div>
            <div class="ev-step" id="ev-step-9">Pregunta 9 de 11</div>
            <h2 class="ev-title">Gestión de Relaciones Interpersonales</h2>
            <div class="ev-question">En su día a día, el colaborador demuestra la capacidad de construir puentes de confianza y comunicación que fortalecen las relaciones con compañeros, líderes y otras partes interesadas. ¿Cómo calificarías su habilidad para resolver conflictos de forma creativa y generar un entorno laboral armónico que impulse la cultura organizacional?</div>
            <div class="ev-options">
                <button type="button" class="ev-option ev-opt-sobresaliente"
                        onclick="evSelectOption(this, 9, 'Sobresaliente')">
                    <span class="ev-option-dot"></span>
                    <span><i class="ti ti-star-filled" style="font-size:13px;"></i> Sobresaliente</span>
                
                    <?php if (!empty($tooltipDesc[9][5])): ?><span class="ev-tooltip"><?= htmlspecialchars($tooltipDesc[9][5]) ?></span><?php endif; ?>
                </button>
                <button type="button" class="ev-option ev-opt-acorde"
                        onclick="evSelectOption(this, 9, 'Acorde')">
                    <span class="ev-option-dot"></span>
                    <span><?= icon('check-circle', 13) ?> Acorde</span>
                
                    <?php if (!empty($tooltipDesc[9][4])): ?><span class="ev-tooltip"><?= htmlspecialchars($tooltipDesc[9][4]) ?></span><?php endif; ?>
                </button>
                <button type="button" class="ev-option ev-opt-aceptable"
                        onclick="evSelectOption(this, 9, 'Aceptable')">
                    <span class="ev-option-dot"></span>
                    <span><?= icon('thumbs-up', 13) ?> Aceptable</span>
                
                    <?php if (!empty($tooltipDesc[9][3])): ?><span class="ev-tooltip"><?= htmlspecialchars($tooltipDesc[9][3]) ?></span><?php endif; ?>
                </button>
                <button type="button" class="ev-option ev-opt-necesita"
                        onclick="evSelectOption(this, 9, 'Requiere mejora')">
                    <span class="ev-option-dot"></span>
                    <span><?= icon('alert-circle', 13) ?> Requiere mejora</span>
                
                    <?php if (!empty($tooltipDesc[9][2])): ?><span class="ev-tooltip"><?= htmlspecialchars($tooltipDesc[9][2]) ?></span><?php endif; ?>
                </button>
                <button type="button" class="ev-option ev-opt-insuficiente"
                        onclick="evSelectOption(this, 9, 'Insuficiente')">
                    <span class="ev-option-dot"></span>
                    <span><?= icon('x-circle', 13) ?> Insuficiente</span>
                
                    <?php if (!empty($tooltipDesc[9][1])): ?><span class="ev-tooltip"><?= htmlspecialchars($tooltipDesc[9][1]) ?></span><?php endif; ?>
                </button>
            </div>
            <div class="ev-justif" id="ev-justif-9">
                <label><?= icon('alert-circle', 13) ?> Calificación extrema — justificación obligatoria</label>
                <textarea id="ev-jtext-9" name="justificacion9" maxlength="500"
                          placeholder="Explica brevemente el motivo de esta calificación..."></textarea>
            </div>
            <input type="hidden" name="pregunta9" id="ev-input-9" required>
            <div class="ev-nav"><button type="button" class="ev-btn ev-btn-back" onclick="evPrev(9)">← Anterior</button><button type="button" class="ev-btn ev-btn-primary" onclick="evNext(9, 11)">Siguiente →</button></div>
        </div>
        <div class="ev-section" id="ev-section-10">
            <div class="ev-step" id="ev-step-10">Pregunta 10 de 11</div>
            <h2 class="ev-title">Eficacia en la Ejecución de Responsabilidades y Contribución a los Resultados</h2>
            <div class="ev-question">¿De qué manera el colaborador traduce sus responsabilidades en acciones que generan resultados tangibles, demostrando puntualidad, precisión y un impacto significativo en el cumplimiento de las metas organizacionales?</div>
            <div class="ev-options">
                <button type="button" class="ev-option ev-opt-sobresaliente"
                        onclick="evSelectOption(this, 10, 'Sobresaliente')">
                    <span class="ev-option-dot"></span>
                    <span><i class="ti ti-star-filled" style="font-size:13px;"></i> Sobresaliente</span>
                
                    <?php if (!empty($tooltipDesc[10][5])): ?><span class="ev-tooltip"><?= htmlspecialchars($tooltipDesc[10][5]) ?></span><?php endif; ?>
                </button>
                <button type="button" class="ev-option ev-opt-acorde"
                        onclick="evSelectOption(this, 10, 'Acorde')">
                    <span class="ev-option-dot"></span>
                    <span><?= icon('check-circle', 13) ?> Acorde</span>
                
                    <?php if (!empty($tooltipDesc[10][4])): ?><span class="ev-tooltip"><?= htmlspecialchars($tooltipDesc[10][4]) ?></span><?php endif; ?>
                </button>
                <button type="button" class="ev-option ev-opt-aceptable"
                        onclick="evSelectOption(this, 10, 'Aceptable')">
                    <span class="ev-option-dot"></span>
                    <span><?= icon('thumbs-up', 13) ?> Aceptable</span>
                
                    <?php if (!empty($tooltipDesc[10][3])): ?><span class="ev-tooltip"><?= htmlspecialchars($tooltipDesc[10][3]) ?></span><?php endif; ?>
                </button>
                <button type="button" class="ev-option ev-opt-necesita"
                        onclick="evSelectOption(this, 10, 'Requiere mejora')">
                    <span class="ev-option-dot"></span>
                    <span><?= icon('alert-circle', 13) ?> Requiere mejora</span>
                
                    <?php if (!empty($tooltipDesc[10][2])): ?><span class="ev-tooltip"><?= htmlspecialchars($tooltipDesc[10][2]) ?></span><?php endif; ?>
                </button>
                <button type="button" class="ev-option ev-opt-insuficiente"
                        onclick="evSelectOption(this, 10, 'Insuficiente')">
                    <span class="ev-option-dot"></span>
                    <span><?= icon('x-circle', 13) ?> Insuficiente</span>
                
                    <?php if (!empty($tooltipDesc[10][1])): ?><span class="ev-tooltip"><?= htmlspecialchars($tooltipDesc[10][1]) ?></span><?php endif; ?>
                </button>
            </div>
            <div class="ev-justif" id="ev-justif-10">
                <label><?= icon('alert-circle', 13) ?> Calificación extrema — justificación obligatoria</label>
                <textarea id="ev-jtext-10" name="justificacion10" maxlength="500"
                          placeholder="Explica brevemente el motivo de esta calificación..."></textarea>
            </div>
            <input type="hidden" name="pregunta10" id="ev-input-10" required>
            <div class="ev-nav"><button type="button" class="ev-btn ev-btn-back" onclick="evPrev(10)">← Anterior</button><button type="button" class="ev-btn ev-btn-primary" onclick="evNext(10, 11)">Siguiente →</button></div>
        </div>
        <div class="ev-section" id="ev-section-11">
            <div class="ev-step" id="ev-step-11">Pregunta 11 de 11</div>
            <h2 class="ev-title">Gestión Eficiente del Tiempo y los Recursos</h2>
            <div class="ev-question">En su rol, el colaborador organiza su tiempo y recursos con una visión estratégica, priorizando tareas clave y minimizando el desperdicio. ¿Cómo evaluarías su capacidad para cumplir plazos establecidos, optimizar recursos disponibles y entregar resultados alineados con las demandas del cargo?</div>
            <div class="ev-options">
                <button type="button" class="ev-option ev-opt-sobresaliente"
                        onclick="evSelectOption(this, 11, 'Sobresaliente')">
                    <span class="ev-option-dot"></span>
                    <span><i class="ti ti-star-filled" style="font-size:13px;"></i> Sobresaliente</span>
                
                    <?php if (!empty($tooltipDesc[11][5])): ?><span class="ev-tooltip"><?= htmlspecialchars($tooltipDesc[11][5]) ?></span><?php endif; ?>
                </button>
                <button type="button" class="ev-option ev-opt-acorde"
                        onclick="evSelectOption(this, 11, 'Acorde')">
                    <span class="ev-option-dot"></span>
                    <span><?= icon('check-circle', 13) ?> Acorde</span>
                
                    <?php if (!empty($tooltipDesc[11][4])): ?><span class="ev-tooltip"><?= htmlspecialchars($tooltipDesc[11][4]) ?></span><?php endif; ?>
                </button>
                <button type="button" class="ev-option ev-opt-aceptable"
                        onclick="evSelectOption(this, 11, 'Aceptable')">
                    <span class="ev-option-dot"></span>
                    <span><?= icon('thumbs-up', 13) ?> Aceptable</span>
                
                    <?php if (!empty($tooltipDesc[11][3])): ?><span class="ev-tooltip"><?= htmlspecialchars($tooltipDesc[11][3]) ?></span><?php endif; ?>
                </button>
                <button type="button" class="ev-option ev-opt-necesita"
                        onclick="evSelectOption(this, 11, 'Requiere mejora')">
                    <span class="ev-option-dot"></span>
                    <span><?= icon('alert-circle', 13) ?> Requiere mejora</span>
                
                    <?php if (!empty($tooltipDesc[11][2])): ?><span class="ev-tooltip"><?= htmlspecialchars($tooltipDesc[11][2]) ?></span><?php endif; ?>
                </button>
                <button type="button" class="ev-option ev-opt-insuficiente"
                        onclick="evSelectOption(this, 11, 'Insuficiente')">
                    <span class="ev-option-dot"></span>
                    <span><?= icon('x-circle', 13) ?> Insuficiente</span>
                
                    <?php if (!empty($tooltipDesc[11][1])): ?><span class="ev-tooltip"><?= htmlspecialchars($tooltipDesc[11][1]) ?></span><?php endif; ?>
                </button>
            </div>
            <div class="ev-justif" id="ev-justif-11">
                <label><?= icon('alert-circle', 13) ?> Calificación extrema — justificación obligatoria</label>
                <textarea id="ev-jtext-11" name="justificacion11" maxlength="500"
                          placeholder="Explica brevemente el motivo de esta calificación..."></textarea>
            </div>
            <input type="hidden" name="pregunta11" id="ev-input-11" required>
            <div class="ev-nav"><button type="button" class="ev-btn ev-btn-back" onclick="evPrev(11)">← Anterior</button><button type="button" class="ev-btn ev-btn-primary" onclick="evNext(11, 11)">Siguiente →</button></div>
        </div>

        <div class="ev-section" id="ev-section-confirm">
            <!-- <div class="ev-confirm-icon">🎯</div> -->
            <div class="ev-confirm-title">¿Deseas enviar la evaluación?</div>
            <div class="ev-confirm-sub">
                Has completado las 11 preguntas.<br>
                Revisa tu evaluación antes de enviarla — una vez enviada no podrá modificarse.
            </div>
            <div class="ev-nav">
                <button type="button" class="ev-btn ev-btn-back" onclick="evPrevFromConfirm(11)">← Revisar</button>
                <button type="submit" class="ev-btn ev-btn-submit"><?= icon('check-circle', 14) ?> Enviar evaluación</button>
            </div>
        </div>
        </form>
    </div>
</div>


<script>
    function evSelectOption(btn, sectionNum, value) {
        // Deselect all options in section
        document.querySelectorAll('#ev-section-' + sectionNum + ' .ev-option').forEach(b => b.classList.remove('selected'));
        btn.classList.add('selected');
        document.getElementById('ev-input-' + sectionNum).value = value;

        // Justification logic
        var justifBox  = document.getElementById('ev-justif-' + sectionNum);
        var justifText = document.getElementById('ev-jtext-' + sectionNum);
        if (justifBox) {
            if (value === 'Sobresaliente' || value === 'Insuficiente') {
                justifBox.style.display = 'block';
                justifText.setAttribute('required', 'required');
                setTimeout(() => justifText.focus(), 100);
            } else {
                justifBox.style.display = 'none';
                if (justifText) { justifText.removeAttribute('required'); justifText.value = ''; }
            }
        }
    }

    function evNext(current, total) {
        var inp = document.getElementById('ev-input-' + current);
        if (!inp || inp.value === '') {
            Swal.fire({ icon:'error', title:'Dato requerido', text:'Selecciona una opción para continuar.', timer:2000, showConfirmButton:false });
            return;
        }
        if (inp.value === 'Sobresaliente' || inp.value === 'Insuficiente') {
            var jt = document.getElementById('ev-jtext-' + current);
            if (!jt || jt.value.trim() === '') {
                Swal.fire({ icon:'warning', title:'Justificación requerida', text:'Debes justificar la calificación de ' + inp.value + ' antes de continuar.', confirmButtonText:'Entendido' });
                if (jt) jt.focus();
                return;
            }
        }
        document.getElementById('ev-section-' + current).classList.remove('active');
        var nextId = (current < total) ? 'ev-section-' + (current + 1) : 'ev-section-confirm';
        document.getElementById(nextId).classList.add('active');
        evUpdateProgress(current + 1, total + 1);
        window.scrollTo({top:0, behavior:'smooth'});
        if (current < total) mostrarSectionModal(current + 1);
    }

    function evPrev(current) {
        document.getElementById('ev-section-' + current).classList.remove('active');
        document.getElementById('ev-section-' + (current - 1)).classList.add('active');
        evUpdateProgress(current - 1, document.querySelectorAll('.ev-section').length);
        window.scrollTo({top:0, behavior:'smooth'});
    }

    function evPrevFromConfirm(total) {
        document.getElementById('ev-section-confirm').classList.remove('active');
        document.getElementById('ev-section-' + total).classList.add('active');
        evUpdateProgress(total, total + 1);
    }

    // ── Modales + banners por dimensión ──────────────────────────────────────
    const evSecciones = {
        1: { icon:'<i class="ti ti-sparkles" style="font-size:2rem;color:#0058af;"></i>', title:'Dimensión 1: Viviendo Nuestros Valores Construyendo Nuestra Identidad', text:'En este espacio, reflexionaremos sobre cómo nuestros principios y valores se transforman en acciones diarias que nos definen como equipo y como institución. La calidez humana, la innovación, la integridad y los demás principios y valores no son solo palabras, son el motor que impulsa cada decisión y cada interacción.' },
        5: { icon:'<i class="ti ti-settings" style="font-size:2rem;color:#0058af;"></i>', title:'Funciones Generales: El Pilar de Nuestro Compromiso Institucional', text:'En esta sesión evaluaremos aquellos aspectos fundamentales que sustentan el éxito colectivo: el compromiso con la calidad, la adherencia a las normas internas, la participación activa en el desarrollo profesional y el cuidado por la seguridad y salud en el trabajo.' },
        9: { icon:'<i class="ti ti-rocket" style="font-size:2rem;color:#0058af;"></i>', title:'Potenciando el Impacto en Funciones Específicas', text:'En esta sección, queremos ir más allá del simple cumplimiento de tareas: buscamos descubrir cómo el talento del colaborador transforma la rutina en resultados extraordinarios. Evaluaremos cómo su desempeño impulsa un entorno de colaboración positiva.' },
    };

    function mostrarSectionModal(seccion) {
        const key  = 'evModal_sub_' + seccion;
        if (sessionStorage.getItem(key)) return;
        const data = evSecciones[seccion];
        if (!data) return;
        document.getElementById('evSectionModalIcon').innerHTML  = data.icon;
        document.getElementById('evSectionModalTitle').textContent = data.title;
        document.getElementById('evSectionModalText').textContent  = data.text;
        document.getElementById('evSectionModal').style.display    = 'flex';
        sessionStorage.setItem(key, '1');
    }

    function cerrarSectionModal() {
        document.getElementById('evSectionModal').style.display = 'none';
    }

    function toggleBanner(sec) {
        const banner = document.getElementById('ev-banner-' + sec);
        if (banner) banner.classList.toggle('open');
    }


    // ── Modal bienvenida ─────────────────────────────────────────────────────
    function cerrarBienvenidaModal() {
        document.getElementById('evBienvenidaModal').style.display = 'none';
        // Después de cerrar bienvenida mostrar modal de primera sección
        setTimeout(function() {
            mostrarSectionModal(1);
        }, 300);
    }

    document.addEventListener('DOMContentLoaded', function() {
        const key = 'evModal_sub_bienvenida';
        if (!sessionStorage.getItem(key)) {
            sessionStorage.setItem(key, '1');
            document.getElementById('evBienvenidaModal').style.display = 'flex';
        } else {
            mostrarSectionModal(1);
        }
    });




    function evUpdateProgress(current, total) {
        var bar = document.getElementById('ev-progress');
        if (bar) bar.style.width = Math.round((current / total) * 100) + '%';
        var step = document.getElementById('ev-step');
        if (step) step.textContent = 'Pregunta ' + Math.min(current, total - 1) + ' de ' + (total - 1);
    }
</script>

<script>
    // Update percentage display
    var _evOrig = evUpdateProgress;
    evUpdateProgress = function(current, total) {
        _evOrig(current, total);
        var pct = document.getElementById('ev-pct');
        if (pct) pct.textContent = Math.round((current / total) * 100) + '%';
        var step = document.getElementById('ev-step');
        if (step) step.textContent = 'Pregunta ' + Math.min(current, total - 1) + ' de ' + (total - 1);
    };
    // Initialize progress
    evUpdateProgress(1, 11 + 1);
</script>
