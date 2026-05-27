<?php
/**
 * evaluacion-view.php — Vista dinámica unificada V2
 *
 * Reemplaza: autoevaluacion-view.php, subEvaluacion-view.php,
 *            evaluacionLiderazgo-view.php
 *
 * Lee competencias desde BD vía competenciaModel.
 * El tipo de evaluación llega por sesión: $_SESSION['tipoEval']
 * El empleado evaluado llega por POST o sesión: $idEvaluado
 */

use app\models\competenciaModel;

$compModelo = new competenciaModel();

// ── Datos del contexto ────────────────────────────────────────────────────────
$idEvaluador = (int)$_SESSION['idempleado'];
$nivelCargo  = $_SESSION['nivelcargo'] ?? 'NC001';
$tipoEval    = $_SESSION['tipoEval']   ?? 'AUTO';
$idEvaluado  = $tipoEval === 'AUTO'
    ? $idEvaluador
    : (int)($_SESSION['idEvaluado'] ?? $idEvaluador);

// ── Período activo ────────────────────────────────────────────────────────────
$sqlPer = "SELECT IDPERIODO, NOMBRE FROM VAADINWEB.HUMPERIODOEVALUACION
           WHERE ESTADO = 1
             AND TRUNC(SYSDATE) BETWEEN TRUNC(FECHAAPERTURA) AND TRUNC(FECHACIERRE)
             AND ROWNUM = 1";
$resPer  = (new \app\models\mainModel)->ejecutarConsulta($sqlPer);
$periodo = $resPer ? oci_fetch_assoc($resPer) : null;
if (!$periodo) {
    echo "<script>sessionStorage.setItem('sinPeriodo','1');window.location.href='" . APP_URL . "home/';</script>";
    exit;
}
$idPeriodo = (int)$periodo['IDPERIODO'];

// ── Competencias según rol y tipo ─────────────────────────────────────────────
$idRol        = $compModelo->getRolPorNivelCargo($nivelCargo);
$competencias = $compModelo->getCompetenciasPorRolTipo($idRol, $tipoEval);
$totalPregs   = count($competencias);

if (empty($competencias)) {
    echo "<script>alert('No hay competencias configuradas para este tipo de evaluación.');window.history.back();</script>";
    exit;
}

// ── Títulos según tipo ────────────────────────────────────────────────────────
$titulos = [
    'AUTO'             => ['titulo' => 'Autoevaluación de Desempeño',       'sub' => 'Evalúa tu propio desempeño con sinceridad'],
    'LIDER_A_COLAB'    => ['titulo' => 'Evaluación de Colaborador',          'sub' => 'Evalúa el desempeño de tu colaborador'],
    'COLAB_A_LIDER'    => ['titulo' => 'Evaluación de Liderazgo',            'sub' => 'Evalúa el liderazgo de tu líder'],
    'EXPERIENCIA_COLAB'=> ['titulo' => 'Experiencia Azul — Colaborador',     'sub' => 'Evalúa la experiencia al paciente de tu colaborador'],
    'EXPERIENCIA_LIDER'=> ['titulo' => 'Experiencia Azul — Líder',           'sub' => 'Evalúa la gestión de experiencia de tu líder'],
];
$tituloInfo = $titulos[$tipoEval] ?? $titulos['AUTO'];

// ── Valores extremos que requieren justificación por escala ───────────────────
// Escala 1 (Desempeño): extremos = Sobresaliente(5) e Insuficiente(1)
// Escala 2 (Experiencia Azul): extremos = Referente(5) y Crítico(1)
$extremosPorEscala = [
    1 => ['Sobresaliente', 'Insuficiente'],
    2 => ['Referente', 'Crítico'],
];
?>

<style>
:root {
    --ev-accent:  #005EB8;
    --ev-accent2: #0074E0;
    --ev-bg:      #f8fafc;
    --ev-card:    #ffffff;
    --ev-border:  #e2e8f0;
    --ev-muted:   #64748b;
    --ev-text:    #1e293b;
    --ev-success: #059669;
    --ev-warning: #d97706;
    --ev-radius:  16px;
}
.ev-wrap { min-height:100vh; background:var(--ev-bg); display:flex; align-items:flex-start; justify-content:center; padding:32px 16px 60px; }
.ev-card { background:var(--ev-card); border-radius:var(--ev-radius); box-shadow:0 4px 24px rgba(0,94,184,.08); width:100%; max-width:700px; padding:40px 40px 32px; position:relative; }
@media(max-width:600px) { .ev-card { padding:24px 18px 20px; } }
.ev-progress-wrap { background:#e2e8f0; border-radius:99px; height:6px; margin-bottom:28px; overflow:hidden; }
.ev-progress-bar  { height:100%; background:linear-gradient(90deg,#005EB8,#0074E0); border-radius:99px; transition:width .4s ease; }
.ev-step    { font-size:.78rem; font-weight:700; color:var(--ev-muted); text-transform:uppercase; letter-spacing:.06em; }
.ev-section { display:none; }
.ev-section.active { display:block; }
/* Cabecera de dimensión */
.ev-dim-header { background:linear-gradient(135deg,#e8f0fb,#dbeafe); border-radius:12px; padding:14px 18px; margin-bottom:20px; border-left:4px solid var(--ev-accent); }
.ev-dim-title { font-size:.82rem; font-weight:800; color:var(--ev-accent); text-transform:uppercase; letter-spacing:.05em; margin-bottom:4px; }
.ev-dim-desc  { font-size:.78rem; color:var(--ev-muted); line-height:1.5; }
/* Pregunta */
.ev-title    { font-size:1.05rem; font-weight:800; color:var(--ev-text); margin-bottom:10px; line-height:1.4; }
.ev-question { font-size:.88rem; color:var(--ev-muted); line-height:1.6; margin-bottom:20px; }
/* Opciones */
.ev-options { display:flex; flex-direction:column; gap:8px; margin-bottom:20px; }
.ev-option  { display:flex; align-items:flex-start; gap:12px; padding:12px 16px; border-radius:10px; border:1.5px solid var(--ev-border); background:var(--ev-card); cursor:pointer; text-align:left; transition:all .15s; width:100%; font-family:inherit; }
.ev-option:hover { border-color:var(--ev-accent); background:#f0f6ff; }
.ev-option.selected { border-color:var(--ev-accent); background:#e8f0fb; }
.ev-option-dot { width:16px; height:16px; border-radius:50%; border:2px solid #cbd5e1; flex-shrink:0; margin-top:2px; transition:all .15s; }
.ev-option.selected .ev-option-dot { background:var(--ev-accent); border-color:var(--ev-accent); }
.ev-option-content { flex:1; }
.ev-option-label { font-size:.88rem; font-weight:700; color:var(--ev-text); display:block; margin-bottom:2px; }
.ev-option-desc  { font-size:.78rem; color:var(--ev-muted); line-height:1.4; }
/* Estilos por calificación */
.ev-option[data-valor="5"].selected { border-color:#059669; background:#f0fdf4; }
.ev-option[data-valor="5"].selected .ev-option-dot { background:#059669; border-color:#059669; }
.ev-option[data-valor="4"].selected { border-color:#0074E0; background:#eff6ff; }
.ev-option[data-valor="4"].selected .ev-option-dot { background:#0074E0; border-color:#0074E0; }
.ev-option[data-valor="3"].selected { border-color:#d97706; background:#fffbeb; }
.ev-option[data-valor="3"].selected .ev-option-dot { background:#d97706; border-color:#d97706; }
.ev-option[data-valor="2"].selected { border-color:#f97316; background:#fff7ed; }
.ev-option[data-valor="2"].selected .ev-option-dot { background:#f97316; border-color:#f97316; }
.ev-option[data-valor="1"].selected { border-color:#dc2626; background:#fef2f2; }
.ev-option[data-valor="1"].selected .ev-option-dot { background:#dc2626; border-color:#dc2626; }
/* Justificación */
.ev-justif { display:none; background:#fffbeb; border:1.5px solid #fcd34d; border-radius:10px; padding:14px; margin-bottom:16px; }
.ev-justif label { font-size:.78rem; font-weight:700; color:#92400e; display:flex; align-items:center; gap:6px; margin-bottom:8px; }
.ev-justif textarea { width:100%; border:1px solid #fcd34d; border-radius:8px; padding:10px; font-size:.82rem; font-family:inherit; resize:vertical; min-height:72px; background:#fff; }
/* Navegación */
.ev-nav { display:flex; gap:10px; margin-top:20px; }
.ev-btn { padding:10px 22px; border-radius:10px; font-weight:700; font-size:.88rem; cursor:pointer; border:none; font-family:inherit; transition:all .2s; }
.ev-btn-primary  { background:var(--ev-accent); color:#fff; }
.ev-btn-primary:hover { background:var(--ev-accent2); }
.ev-btn-back     { background:#f1f5f9; color:var(--ev-muted); }
.ev-btn-back:hover { background:#e2e8f0; }
.ev-btn-submit   { background:#059669; color:#fff; }
.ev-btn-submit:hover { background:#047857; }
/* Pantalla de confirmación */
.ev-confirm { text-align:center; padding:20px 0; }
.ev-confirm-title { font-size:1.3rem; font-weight:800; color:var(--ev-text); margin-bottom:8px; }
.ev-confirm-sub   { color:var(--ev-muted); font-size:.88rem; margin-bottom:24px; }
.ev-confirm-summary { background:#f8fafc; border-radius:12px; padding:16px; margin-bottom:24px; text-align:left; max-height:320px; overflow-y:auto; }
.ev-summary-row { display:flex; justify-content:space-between; align-items:center; padding:7px 0; border-bottom:1px solid #e2e8f0; font-size:.82rem; }
.ev-summary-row:last-child { border-bottom:none; }
.ev-summary-comp  { color:var(--ev-muted); flex:1; padding-right:12px; }
.ev-summary-badge { font-weight:700; font-size:.75rem; padding:3px 10px; border-radius:99px; white-space:nowrap; }
</style>

<div class="ev-wrap">
<div class="ev-card">

    <!-- Header -->
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;
                padding-bottom:20px;border-bottom:1.5px solid var(--ev-border);">
        <div style="width:44px;height:44px;border-radius:12px;background:#e8f0fb;
                    display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <?= icon('clipboard', 22) ?>
        </div>
        <div>
            <div style="font-size:1rem;font-weight:800;color:var(--ev-text);">
                <?= htmlspecialchars($tituloInfo['titulo']) ?>
            </div>
            <div style="font-size:.8rem;color:var(--ev-muted);">
                Clínica Zayma · <?= htmlspecialchars($tituloInfo['sub']) ?>
            </div>
        </div>
    </div>

    <!-- Progreso -->
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
        <span class="ev-step" id="ev-step">Pregunta 1 de <?= $totalPregs ?></span>
        <span style="font-size:.78rem;color:var(--ev-muted);" id="ev-pct">0%</span>
    </div>
    <div class="ev-progress-wrap">
        <div class="ev-progress-bar" id="ev-progress" style="width:0%"></div>
    </div>

    <!-- Formulario dinámico -->
    <form id="evaluationForm"
          action="<?= APP_URL ?>app/ajax/formulariosAjax.php"
          method="POST">

        <input type="hidden" name="modulo_evaluacion" value="registrarEvaluacionV2">
        <input type="hidden" name="tipoEval"          value="<?= htmlspecialchars($tipoEval) ?>">
        <input type="hidden" name="empleadoevaluado"  value="<?= $idEvaluado ?>">

        <?php
        $seccionActual = 1;
        $dimAnterior   = null;

        foreach ($competencias as $idx => $comp):
            $numSec      = $seccionActual;
            $idComp      = $comp['IDCOMPETENCIA'];
            $idDim       = $comp['IDDIMENSION'];
            $idEscala    = $comp['IDESCALA'];
            $opciones    = $compModelo->getOpcionesPorCompetencia($idComp);
            $extremos    = $extremosPorEscala[$idEscala] ?? ['Sobresaliente', 'Insuficiente'];
            $esUltima    = ($idx === $totalPregs - 1);
            $esActiva    = $idx === 0 ? ' active' : '';
        ?>

        <div class="ev-section<?= $esActiva ?>" id="ev-section-<?= $numSec ?>">

            <?php if ($idDim !== $dimAnterior): ?>
            <div class="ev-dim-header">
                <div class="ev-dim-title"><?= htmlspecialchars($comp['NOMBRE_DIMENSION']) ?></div>
                <?php if (!empty($comp['DESC_DIMENSION'])): ?>
                <div class="ev-dim-desc"><?= htmlspecialchars($comp['DESC_DIMENSION']) ?></div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <h2 class="ev-title"><?= htmlspecialchars($comp['NOMBRE']) ?></h2>
            <div class="ev-question"><?= htmlspecialchars($comp['PREGUNTA']) ?></div>

            <div class="ev-options">
                <?php foreach ($opciones as $valor => $opc): ?>
                <button type="button"
                        class="ev-option"
                        data-valor="<?= $valor ?>"
                        onclick="evSelectOption(this, <?= $numSec ?>, '<?= htmlspecialchars($opc['ETIQUETA'], ENT_QUOTES) ?>', <?= json_encode($extremos) ?>)">
                    <span class="ev-option-dot"></span>
                    <div class="ev-option-content">
                        <span class="ev-option-label">
                            <?= icon(evIconPorValor($valor, $idEscala), 13) ?>
                            <?= htmlspecialchars($opc['ETIQUETA']) ?>
                        </span>
                        <?php if (!empty($opc['DESCRIPCION'])): ?>
                        <span class="ev-option-desc"><?= htmlspecialchars($opc['DESCRIPCION']) ?></span>
                        <?php endif; ?>
                    </div>
                </button>
                <?php endforeach; ?>
            </div>

            <!-- Justificación para calificaciones extremas -->
            <div class="ev-justif" id="ev-justif-<?= $numSec ?>">
                <label><?= icon('alert-circle', 13) ?> Calificación extrema — justificación obligatoria</label>
                <textarea id="ev-jtext-<?= $numSec ?>"
                          name="justificacion_<?= $idComp ?>"
                          maxlength="500"
                          placeholder="Explica brevemente el motivo de esta calificación..."></textarea>
            </div>

            <input type="hidden" name="competencia_<?= $idComp ?>" id="ev-input-<?= $numSec ?>" required>

            <div class="ev-nav">
                <?php if ($numSec > 1): ?>
                <button type="button" class="ev-btn ev-btn-back" onclick="evPrev(<?= $numSec ?>)">
                    ← Anterior
                </button>
                <?php endif; ?>
                <?php if (!$esUltima): ?>
                <button type="button" class="ev-btn ev-btn-primary"
                        onclick="evNext(<?= $numSec ?>, <?= $totalPregs ?>)">
                    Siguiente →
                </button>
                <?php else: ?>
                <button type="button" class="ev-btn ev-btn-primary"
                        onclick="evNext(<?= $numSec ?>, <?= $totalPregs ?>)">
                    Revisar y enviar
                </button>
                <?php endif; ?>
            </div>

        </div>

        <?php
            $dimAnterior = $idDim;
            $seccionActual++;
        endforeach;
        ?>

        <!-- Pantalla de confirmación -->
        <div class="ev-section" id="ev-section-confirm">
            <div class="ev-confirm">
                <div><?= icon('check-circle', 48) ?></div>
                <div class="ev-confirm-title">¿Deseas enviar la evaluación?</div>
                <div class="ev-confirm-sub">
                    Has completado las <?= $totalPregs ?> preguntas.<br>
                    Revisa el resumen antes de enviar — una vez confirmado no podrás editar.
                </div>
                <div class="ev-confirm-summary" id="ev-summary"></div>
                <div class="ev-nav" style="justify-content:center;">
                    <button type="button" class="ev-btn ev-btn-back"
                            onclick="evPrevFromConfirm(<?= $totalPregs ?>)">
                        ← Revisar
                    </button>
                    <button type="submit" class="ev-btn ev-btn-submit">
                        <?= icon('check-circle', 14) ?> Enviar evaluación
                    </button>
                </div>
            </div>
        </div>

    </form>
</div>
</div>

<?php
// Helper PHP para ícono por valor
function evIconPorValor(int $valor, int $idEscala): string {
    if ($idEscala === 2) {
        // Experiencia Azul
        return match($valor) {
            5 => 'award',
            4 => 'check-circle',
            3 => 'check',
            2 => 'alert-circle',
            1 => 'x-circle',
            default => 'check'
        };
    }
    // Desempeño General
    return match($valor) {
        5 => 'award',
        4 => 'check-circle',
        3 => 'thumbs-up',
        2 => 'alert-circle',
        1 => 'x-circle',
        default => 'check'
    };
}
?>

<script>
// Mapa de competencias para el resumen final
const evCompetencias = <?= json_encode(
    array_map(fn($c) => ['nombre' => $c['NOMBRE'], 'num' => $c['NUM_PREGUNTA']], $competencias),
    JSON_UNESCAPED_UNICODE
) ?>;

// Valores seleccionados [seccion] => etiqueta
const evRespuestas = {};

function evSelectOption(btn, seccion, valor, extremos) {
    // Deseleccionar todas las opciones de la sección
    document.querySelectorAll('#ev-section-' + seccion + ' .ev-option')
            .forEach(b => b.classList.remove('selected'));
    btn.classList.add('selected');
    document.getElementById('ev-input-' + seccion).value = valor;
    evRespuestas[seccion] = valor;

    // Mostrar justificación si es extremo
    const justifBox  = document.getElementById('ev-justif-' + seccion);
    const justifText = document.getElementById('ev-jtext-' + seccion);
    if (justifBox) {
        const esExtremo = extremos.includes(valor);
        justifBox.style.display = esExtremo ? 'block' : 'none';
        if (justifText) {
            if (esExtremo) {
                justifText.setAttribute('required', 'required');
                setTimeout(() => justifText.focus(), 100);
            } else {
                justifText.removeAttribute('required');
                justifText.value = '';
            }
        }
    }
}

function evNext(current, total) {
    const inp = document.getElementById('ev-input-' + current);
    if (!inp || inp.value === '') {
        Swal.fire({ icon:'error', title:'Dato requerido',
                    text:'Selecciona una opción para continuar.',
                    timer:2000, showConfirmButton:false });
        return;
    }
    // Verificar justificación si aplica
    const justifBox  = document.getElementById('ev-justif-' + current);
    const justifText = document.getElementById('ev-jtext-' + current);
    if (justifBox && justifBox.style.display === 'block' && justifText) {
        if (justifText.value.trim() === '') {
            Swal.fire({ icon:'warning', title:'Justificación requerida',
                        text:'Debes justificar la calificación de "' + inp.value + '" antes de continuar.',
                        confirmButtonText:'Entendido' });
            justifText.focus();
            return;
        }
    }
    document.getElementById('ev-section-' + current).classList.remove('active');
    const nextId = (current < total)
        ? 'ev-section-' + (current + 1)
        : 'ev-section-confirm';
    document.getElementById(nextId).classList.add('active');
    evUpdateProgress(current + 1, total + 1);
    if (nextId === 'ev-section-confirm') evRenderSummary();
    window.scrollTo({ top:0, behavior:'smooth' });
}

function evPrev(current) {
    document.getElementById('ev-section-' + current).classList.remove('active');
    document.getElementById('ev-section-' + (current - 1)).classList.add('active');
    evUpdateProgress(current - 1, <?= $totalPregs ?> + 1);
    window.scrollTo({ top:0, behavior:'smooth' });
}

function evPrevFromConfirm(total) {
    document.getElementById('ev-section-confirm').classList.remove('active');
    document.getElementById('ev-section-' + total).classList.add('active');
    evUpdateProgress(total, total + 1);
    window.scrollTo({ top:0, behavior:'smooth' });
}

function evUpdateProgress(current, total) {
    const bar = document.getElementById('ev-progress');
    const pct = Math.round((current / total) * 100);
    if (bar) bar.style.width = pct + '%';
    const step = document.getElementById('ev-step');
    if (step) step.textContent = 'Pregunta ' + Math.min(current, total - 1) + ' de ' + (total - 1);
    const pctEl = document.getElementById('ev-pct');
    if (pctEl) pctEl.textContent = pct + '%';
}

// Colores por etiqueta para el resumen
const evColoresBadge = {
    'Sobresaliente': 'background:#d1fae5;color:#065f46',
    'Referente':     'background:#d1fae5;color:#065f46',
    'Acorde':        'background:#dbeafe;color:#1e40af',
    'Consistente':   'background:#dbeafe;color:#1e40af',
    'Aceptable':     'background:#fef3c7;color:#92400e',
    'Esperado':      'background:#fef3c7;color:#92400e',
    'Requiere mejora': 'background:#ffedd5;color:#9a3412',
    'Inconsistente': 'background:#ffedd5;color:#9a3412',
    'Insuficiente':  'background:#fee2e2;color:#991b1b',
    'Crítico':       'background:#fee2e2;color:#991b1b',
};

function evRenderSummary() {
    const container = document.getElementById('ev-summary');
    if (!container) return;
    let html = '';
    evCompetencias.forEach((comp, idx) => {
        const sec   = idx + 1;
        const resp  = evRespuestas[sec] || '—';
        const estilo = evColoresBadge[resp] || 'background:#f1f5f9;color:#64748b';
        html += `<div class="ev-summary-row">
            <span class="ev-summary-comp">${comp.nombre}</span>
            <span class="ev-summary-badge" style="${estilo}">${resp}</span>
        </div>`;
    });
    container.innerHTML = html;
}

// Inicializar progreso
evUpdateProgress(1, <?= $totalPregs ?> + 1);
</script>
