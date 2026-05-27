<?php
use app\models\competenciaModel;
use app\models\mainModel;

// Colaborador evaluado viene en sesión
$idEvaluado = (int)($_SESSION['ea_idempleado_evaluado'] ?? 0);
$idEvaluador = (int)($_SESSION['idempleado'] ?? 0);

if (!$idEvaluado || !$idEvaluador) {
    echo "<script>window.location.href='" . APP_URL . "evaluarList/';</script>"; exit;
}

// Verificar que el colaborador aplica Exp. Azul
$tooltipDesc = [];
try {
    $compM = new competenciaModel();
    $tooltipDesc = $compM->getOpcionesCompetencia();
    $mainM  = new mainModel();
    $connEA = $mainM->conectar();
    $sqlEA  = "SELECT APLICA_EXP_AZUL, NOMBRE FROM VAADINWEB.HUMEMPLEADOEVAL
               WHERE IDEMPLEADO = $idEvaluado AND ACTIVO = 1 AND ROWNUM = 1";
    $qEA = oci_parse($connEA, $sqlEA);
    oci_execute($qEA);
    $rEA = oci_fetch_assoc($qEA);
    $aplicaEA      = (int)($rEA['APLICA_EXP_AZUL'] ?? 0);
    $nombreEvaluado = mb_convert_encoding($rEA['NOMBRE'] ?? '', 'UTF-8', 'ISO-8859-1');
    oci_free_statement($qEA);
} catch (Exception $e) { $aplicaEA = 0; }

if (!$aplicaEA) {
    echo "<script>window.location.href='" . APP_URL . "evaluarList/';</script>"; exit;
}

// Cargar competencias 23-27
$connEA2 = (new mainModel())->conectar();
$sqlComp = "SELECT C.IDCOMPETENCIA, C.NOMBRE, C.PREGUNTA, C.NUM_PREGUNTA
            FROM VAADINWEB.HUMCOMPETENCIA C
            WHERE C.NUM_PREGUNTA BETWEEN 23 AND 27
              AND C.ACTIVO = 1
            ORDER BY C.NUM_PREGUNTA ASC";
$qComp = oci_parse($connEA2, $sqlComp);
oci_execute($qComp);
$competenciasEA = [];
while ($r = oci_fetch_assoc($qComp)) {
    $r['NOMBRE']   = fromOracleEncoding($r['NOMBRE']   ?? '');
    $r['PREGUNTA'] = fromOracleEncoding($r['PREGUNTA'] ?? '');
    $competenciasEA[] = $r;
}
oci_free_statement($qComp);

$escalaEA = [
    ['clase'=>'ev-opt-referente',    'label'=>'⭐ Referente',    'idopcion'=>6],
    ['clase'=>'ev-opt-consistente',  'label'=>'✓ Consistente',   'idopcion'=>7],
    ['clase'=>'ev-opt-esperado',     'label'=>'○ Esperado',      'idopcion'=>8],
    ['clase'=>'ev-opt-inconsistente','label'=>'△ Inconsistente', 'idopcion'=>9],
    ['clase'=>'ev-opt-critico',      'label'=>'✗ Crítico',       'idopcion'=>10],
];
$totalEA = count($competenciasEA);
?>
<style>
:root{--ev-accent:#0058af;--ev-accent2:#0074e0;--ev-bg:#f0f6ff;--ev-card:#fff;--ev-border:#bfdbfe;--ev-muted:#64748b;--ev-text:#1e293b;--ev-radius:16px;}
.ev-wrap{min-height:100vh;background:var(--ev-bg);display:flex;align-items:flex-start;justify-content:center;padding:32px 16px 60px;}
.ev-card{background:var(--ev-card);border-radius:var(--ev-radius);box-shadow:0 4px 24px rgba(0,88,175,.12);width:100%;max-width:700px;}
.ev-header{padding:28px 32px 20px;border-bottom:1.5px solid var(--ev-border);}
.ev-body{padding:24px 32px;}
.ev-section{display:none;} .ev-section.active{display:block;}
.ev-pregunta{font-size:.95rem;font-weight:700;color:var(--ev-text);margin-bottom:20px;line-height:1.5;}
.ev-pregunta-num{display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;background:var(--ev-accent);color:#fff;border-radius:50%;font-size:.8rem;font-weight:800;margin-right:10px;flex-shrink:0;}
.ev-options{display:flex;flex-direction:column;gap:10px;margin-bottom:24px;}
.ev-option{display:flex;align-items:center;gap:12px;padding:12px 16px;border:1.5px solid var(--ev-border);border-radius:12px;cursor:pointer;transition:all .2s;background:#fff;font-size:.88rem;font-weight:500;color:var(--ev-text);position:relative;}
.ev-option:hover{border-color:var(--ev-accent);background:#eff6ff;color:#1e293b;}
.ev-option-dot{width:18px;height:18px;border:2px solid #cbd5e1;border-radius:50%;flex-shrink:0;transition:all .2s;}
.ev-option.selected .ev-option-dot{background:var(--ev-accent);border-color:var(--ev-accent);}
.ev-option.ev-opt-referente.selected{border-color:#0058af;background:#dbeafe;color:#1e40af;}
.ev-option.ev-opt-consistente.selected{border-color:#0284c7;background:#e0f2fe;color:#0369a1;}
.ev-option.ev-opt-esperado.selected{border-color:#059669;background:#d1fae5;color:#065f46;}
.ev-option.ev-opt-inconsistente.selected{border-color:#d97706;background:#fef3c7;color:#92400e;}
.ev-option.ev-opt-critico.selected{border-color:#dc2626;background:#fee2e2;color:#991b1b;}
.ev-nav{display:flex;gap:12px;justify-content:flex-end;margin-top:24px;}
.ev-btn{padding:10px 24px;border-radius:10px;font-weight:700;font-size:.88rem;cursor:pointer;border:none;transition:all .2s;}
.ev-btn-back{background:#f1f5f9;color:#475569;} .ev-btn-primary{background:var(--ev-accent);color:#fff;} .ev-btn-submit{background:#059669;color:#fff;}
.ev-progress{height:6px;background:#e2e8f0;border-radius:20px;margin-bottom:8px;overflow:hidden;}
.ev-progress-fill{height:100%;background:linear-gradient(90deg,var(--ev-accent),var(--ev-accent2));border-radius:20px;transition:width .4s ease;}
.ev-tooltip{position:absolute;bottom:calc(100% + 12px);left:50%;transform:translateX(-50%) translateY(4px);width:min(320px,90vw);background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:10px 14px;font-size:.76rem;color:#475569;line-height:1.5;box-shadow:0 8px 24px rgba(0,0,0,.12);pointer-events:none;opacity:0;transition:opacity .2s ease,transform .2s ease;z-index:999;text-align:left;}
.ev-tooltip::after{content:'';position:absolute;top:100%;left:50%;transform:translateX(-50%);border:7px solid transparent;border-top-color:#fff;}
.ev-option:hover .ev-tooltip{opacity:1;transform:translateX(-50%) translateY(0);}
@keyframes evModalIn{from{opacity:0;transform:translateY(16px) scale(.97);}to{opacity:1;transform:translateY(0) scale(1);}}

/* Justificación Exp. Azul */
.ev-justif {
    display:none;margin-top:14px;background:#fffbeb;
    border:1.5px solid #f59e0b;border-radius:10px;padding:14px 16px;
}
.ev-justif label { font-size:.82rem;font-weight:700;color:#92400e;display:block;margin-bottom:8px; }
.ev-justif textarea {
    width:100%;padding:9px 12px;border:1.5px solid #f59e0b;border-radius:8px;
    font-size:.84rem;font-family:inherit;resize:vertical;outline:none;
    background:#fff;min-height:72px;color:#1e293b;
}

/* ── Mejoras visuales Exp. Azul ───────────────────────────────────────── */
.ev-wrap { background: linear-gradient(135deg, #e8f4ff 0%, #f0f6ff 50%, #e8f4ff 100%); }

.ev-card {
    border-radius: 20px;
    box-shadow: 0 8px 40px rgba(0,88,175,.15), 0 2px 8px rgba(0,88,175,.08);
    border: 1px solid rgba(0,88,175,.1);
}

.ev-header {
    background: linear-gradient(135deg, #0058af 0%, #0074e0 100%);
    border-radius: 20px 20px 0 0;
    border-bottom: none;
    padding: 28px 32px 24px;
}

.ev-header * { color: #fff !important; }
.ev-header #ea-progress-label { opacity: .75; }
.ev-progress { background: rgba(255,255,255,.25); }
.ev-progress-fill { background: #fff; }

.ev-pregunta {
    background: #f8faff;
    border: 1.5px solid #dbeafe;
    border-radius: 14px;
    padding: 16px 18px;
    margin-bottom: 20px;
}

.ev-option {
    border-radius: 14px;
    padding: 14px 18px;
    box-shadow: 0 1px 3px rgba(0,0,0,.06);
}

.ev-option:hover {
    transform: translateX(4px);
    box-shadow: 0 4px 12px rgba(0,88,175,.15);
}

.ev-btn-primary {
    background: linear-gradient(135deg, #0058af, #0074e0);
    box-shadow: 0 4px 12px rgba(0,88,175,.3);
    padding: 11px 28px;
}

.ev-btn-submit {
    background: linear-gradient(135deg, #059669, #10b981);
    box-shadow: 0 4px 12px rgba(5,150,105,.3);
    padding: 11px 28px;
}

.ev-confirm-title {
    font-size: 1.15rem;
    font-weight: 800;
    color: #1e3a5f;
    text-align: center;
    margin-bottom: 12px;
}

/* Escala de colores mejorada */
.ev-option.ev-opt-referente:hover    { background: #eff6ff; border-color: #0058af; }
.ev-option.ev-opt-consistente:hover  { background: #f0f9ff; border-color: #0284c7; }
.ev-option.ev-opt-esperado:hover     { background: #f0fdf4; border-color: #059669; }
.ev-option.ev-opt-inconsistente:hover{ background: #fffbeb; border-color: #d97706; }
.ev-option.ev-opt-critico:hover      { background: #fef2f2; border-color: #dc2626; }

</style>

<div class="ev-wrap">
    <div class="ev-card">
        <div class="ev-header">
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
                <div style="width:44px;height:44px;border-radius:12px;background:linear-gradient(135deg,#0058af,#0074e0);display:flex;align-items:center;justify-content:center;">
                    <?= icon('star', 22) ?>
                </div>
                <div>
                    <div style="font-size:1.1rem;font-weight:800;color:var(--ev-text);">Experiencia Azul — Rol Líder</div>
                    <div style="font-size:.82rem;color:var(--ev-muted);">
                        Evaluando a: <strong><?= htmlspecialchars($nombreEvaluado) ?></strong> · 5 preguntas
                    </div>
                </div>
            </div>
            <div class="ev-progress">
                <div class="ev-progress-fill" id="ea-progress" style="width:0%"></div>
            </div>
            <div style="font-size:.75rem;color:var(--ev-muted);text-align:right;" id="ea-progress-label">Pregunta 1 de 5</div>
        </div>

        <form method="POST" action="<?= APP_URL ?>registrarEvaluacion/" id="formExpAzulLider">
            <input type="hidden" name="tipoEval"           value="EXPERIENCIA_LIDER">
            <input type="hidden" name="empleadoevaluado"   value="<?= $idEvaluado ?>">
            <input type="hidden" name="modulo_evaluacion"  value="registrarEvaluacionDesempeno">
            <div class="ev-body">

<?php foreach ($competenciasEA as $idx => $comp):
    $secNum  = $idx + 1;
    $numPreg = (int)$comp['NUM_PREGUNTA'];
?>
                <div class="ev-section <?= $idx === 0 ? 'active' : '' ?>" id="ea-section-<?= $secNum ?>">
                    <div class="ev-pregunta">
                        <span class="ev-pregunta-num"><?= $secNum ?></span>
                        <span style="font-size:.78rem;font-weight:600;color:#0058af;
                                     text-transform:uppercase;letter-spacing:.06em;
                                     display:block;margin-bottom:4px;margin-left:38px;">
                            <?= htmlspecialchars($comp['NOMBRE']) ?>
                        </span>
                        <span style="margin-left:38px;display:block;">
                            <?= htmlspecialchars($comp['PREGUNTA'] ?: $comp['NOMBRE']) ?>
                        </span>
                    </div>
                    <div class="ev-options">
                        <?php foreach ($escalaEA as $opc):
                            // Exp. Azul escala: IDOPCION 6-10 mapea a VALOR 5-1
                        $valorOpcion = [6=>5, 7=>4, 8=>3, 9=>2, 10=>1][$opc['idopcion']] ?? 0;
                        $desc = $tooltipDesc[$numPreg][$valorOpcion] ?? '';
                        ?>
                        <button type="button"
                                class="ev-option <?= $opc['clase'] ?>"
                                onclick="eaSelectOption(this, <?= $secNum ?>, '<?= $opc['label'] ?>', <?= $comp['IDCOMPETENCIA'] ?>, <?= $opc['idopcion'] ?>)">
                            <span class="ev-option-dot"></span>
                            <span><?= $opc['label'] ?></span>
                            <?php if ($desc): ?>
                            <span class="ev-tooltip"><?= htmlspecialchars($desc) ?></span>
                            <?php endif; ?>
                        </button>
                        <?php endforeach; ?>
                    </div>
                    <div class="ev-justif" id="ea-justif-<?= $secNum ?>">
                        <label><?= icon('alert-circle', 13) ?> Calificación extrema — justificación obligatoria</label>
                        <textarea id="ea-jtext-<?= $secNum ?>"
                                  name="justificacion_<?= $comp['IDCOMPETENCIA'] ?>"
                                  maxlength="500"
                                  placeholder="Explica brevemente el motivo de esta calificación..."></textarea>
                    </div>
                    <input type="hidden" name="competencia_<?= $comp['IDCOMPETENCIA'] ?>" id="ea-val-<?= $secNum ?>" value="">
                    <div class="ev-nav">
                        <?php if ($secNum > 1): ?>
                        <button type="button" class="ev-btn ev-btn-back" onclick="eaPrev(<?= $secNum ?>)">← Anterior</button>
                        <?php endif; ?>
                        <button type="button" class="ev-btn ev-btn-primary"
                                onclick="eaNext(<?= $secNum ?>, <?= $totalEA ?>)">
                            <?= $secNum < $totalEA ? 'Siguiente →' : 'Revisar →' ?>
                        </button>
                    </div>
                </div>
<?php endforeach; ?>

                <div class="ev-section" id="ea-section-confirm">
                    <div class="ev-confirm-title" style="text-align:center;font-size:1.2rem;font-weight:800;margin-bottom:12px;">
                        ¿Deseas enviar la Experiencia Azul?
                    </div>
                    <div style="text-align:center;color:var(--ev-muted);margin-bottom:24px;font-size:.88rem;">
                        Has completado las 5 preguntas de Experiencia Azul para<br>
                        <strong><?= htmlspecialchars($nombreEvaluado) ?></strong>
                    </div>
                    <div class="ev-nav" style="justify-content:center;">
                        <button type="button" class="ev-btn ev-btn-back" onclick="eaPrevFromConfirm(<?= $totalEA ?>)">← Revisar</button>
                        <button type="submit" class="ev-btn ev-btn-submit"><?= icon('check-circle', 14) ?> Enviar Experiencia Azul</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal bienvenida -->
<div id="eaBienvenidaModal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.7);
     z-index:10000;align-items:center;justify-content:center;padding:16px;">
    <div style="background:#fff;border-radius:20px;padding:36px 32px;width:min(520px,95vw);
                box-shadow:0 24px 64px rgba(0,0,0,.25);animation:evModalIn .3s ease;text-align:center;">
        <div style="font-size:2.4rem;margin-bottom:14px;">🔵</div>
        <div style="font-size:1.05rem;font-weight:800;color:#1e3a5f;margin-bottom:14px;">Experiencia Azul — Rol Líder</div>
        <div style="font-size:.85rem;color:#475569;line-height:1.75;margin-bottom:28px;text-align:left;
                    background:#f0f6ff;border-radius:12px;padding:16px 20px;border:1px solid #bfdbfe;">
            Ahora evaluarás la <strong>gestión de Experiencia Azul</strong> de
            <strong><?= htmlspecialchars($nombreEvaluado) ?></strong> como líder.
            Esta sección evalúa cómo el líder gestiona la experiencia del paciente en tu equipo.
            La escala es: <strong>Referente · Consistente · Esperado · Inconsistente · Crítico</strong>.
        </div>
        <button onclick="document.getElementById('eaBienvenidaModal').style.display='none'"
                style="padding:12px 36px;background:#0058af;color:#fff;border:none;
                       border-radius:12px;font-size:.9rem;font-weight:700;cursor:pointer;">
            Comenzar →
        </button>
    </div>
</div>

<script>
const eaTotal = <?= $totalEA ?>;

document.addEventListener('DOMContentLoaded', function() {
    const key = 'evModal_expAzulLider_<?= $idEvaluado ?>';
    if (!sessionStorage.getItem(key)) {
        sessionStorage.setItem(key, '1');
        document.getElementById('eaBienvenidaModal').style.display = 'flex';
    }
    eaUpdateProgress(1, eaTotal);
});

function eaSelectOption(btn, secNum, label, idComp, idOpcion) {
    document.querySelectorAll('#ea-section-' + secNum + ' .ev-option').forEach(b => b.classList.remove('selected'));
    btn.classList.add('selected');
    const etiquetas = {6:'Referente',7:'Consistente',8:'Esperado',9:'Inconsistente',10:'Crítico'};
    document.getElementById('ea-val-' + secNum).value = etiquetas[idOpcion] || label;
    // Mostrar justificación para calificaciones extremas
    const justif = document.getElementById('ea-justif-' + secNum);
    if (justif) justif.style.display = (idOpcion === 6 || idOpcion === 10) ? 'block' : 'none';
}

function eaNext(current, total) {
    if (!document.getElementById('ea-val-' + current)?.value) {
        Swal.fire({icon:'warning',title:'Selección requerida',text:'Debes seleccionar una opción antes de continuar.',confirmButtonText:'Entendido'});
        return;
    }
    const justif = document.getElementById('ea-justif-' + current);
    if (justif && justif.style.display === 'block') {
        const jtext = document.getElementById('ea-jtext-' + current);
        if (!jtext?.value?.trim()) {
            Swal.fire({icon:'warning',title:'Justificación requerida',text:'Debes justificar las calificaciones extremas.',confirmButtonText:'Entendido'});
            return;
        }
    }
    document.getElementById('ea-section-' + current).classList.remove('active');
    var nextId = current < total ? 'ea-section-' + (current+1) : 'ea-section-confirm';
    document.getElementById(nextId).classList.add('active');
    eaUpdateProgress(current+1, total+1);
    window.scrollTo({top:0,behavior:'smooth'});
}

function eaPrev(current) {
    document.getElementById('ea-section-' + current).classList.remove('active');
    document.getElementById('ea-section-' + (current-1)).classList.add('active');
    eaUpdateProgress(current-1, eaTotal+1);
    window.scrollTo({top:0,behavior:'smooth'});
}

function eaPrevFromConfirm(total) {
    document.getElementById('ea-section-confirm').classList.remove('active');
    document.getElementById('ea-section-' + total).classList.add('active');
    eaUpdateProgress(total, eaTotal+1);
}

function eaUpdateProgress(current, total) {
    const pct = Math.round((current-1)/(total-1)*100);
    document.getElementById('ea-progress').style.width = pct + '%';
    document.getElementById('ea-progress-label').textContent =
        current <= eaTotal ? 'Pregunta ' + current + ' de ' + eaTotal : 'Confirmación';
}
</script>
