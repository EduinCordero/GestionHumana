<?php /* seguridad-view.php — Enterprise UI v2 */ ?>
<style>
.sec-wrap {
    min-height: calc(100vh - 64px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 68px 20px;
    font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}

/* ── Card ── */
.sec-card {
    background: #ffffff;
    border-radius: 20px;
    box-shadow:
        0 0 0 1px rgba(0,0,0,0.04),
        0 4px 6px -1px rgba(0,0,0,0.06),
        0 12px 40px -8px rgba(0,0,0,0.11);
    width: 100%;
    max-width: 460px;
    overflow: hidden;
}

/* ── Header del card ── */
.sec-header {
    background: linear-gradient(135deg, #003d82 0%, #0058af 100%);
    padding: 28px 36px;
    position: relative;
    overflow: hidden;
}
.sec-header::before {
    content: '';
    position: absolute;
    width: 200px; height: 200px; border-radius: 50%;
    background: rgba(255,255,255,0.06);
    top: -60px; right: -40px;
    pointer-events: none;
}
.sec-header::after {
    content: '';
    position: absolute;
    width: 100px; height: 100px; border-radius: 50%;
    background: rgba(255,255,255,0.04);
    bottom: -30px; left: 20px;
    pointer-events: none;
}
.sec-header-icon {
    width: 44px; height: 44px;
    border-radius: 12px;
    background: rgba(255,255,255,0.15);
    border: 1px solid rgba(255,255,255,0.25);
    display: flex; align-items: center; justify-content: center;
    margin-bottom: 14px;
    position: relative;
}
.sec-header-icon i { font-size: 22px; color: #fff; }
.sec-header-title {
    font-size: 1.2rem; font-weight: 800;
    color: #fff;
    margin-bottom: 4px;
    position: relative;
}
.sec-header-sub {
    font-size: 12.5px;
    color: rgba(255,255,255,0.72);
    line-height: 1.5;
    position: relative;
}

/* ── Banner de aviso ── */
.sec-notice {
    margin: 24px 36px 0;
    background: #fffbeb;
    border: 1px solid #fcd34d;
    border-radius: 10px;
    padding: 12px 14px;
    display: flex;
    align-items: flex-start;
    gap: 10px;
    font-size: 13px;
    color: #92400e;
    line-height: 1.55;
}
.sec-notice i { font-size: 17px; flex-shrink: 0; margin-top: 1px; color: #d97706; }

/* ── Body del form ── */
.sec-body {
    padding: 24px 36px 32px;
}

/* ── Campo ── */
.sec-field { margin-bottom: 18px; }
.sec-label {
    display: block;
    font-size: 13px; font-weight: 600;
    color: #374151;
    margin-bottom: 7px;
}
.sec-input-wrap {
    position: relative;
    display: flex; align-items: center;
}
.sec-input-icon {
    position: absolute; left: 13px;
    color: #94a3b8; font-size: 17px;
    pointer-events: none; z-index: 1;
}
.sec-input {
    width: 100%;
    padding: 11px 14px 11px 40px;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    font-size: 14px; font-family: inherit;
    color: #1e293b;
    background: #f8fafc;
    outline: none;
    transition: border-color 0.18s, box-shadow 0.18s, background 0.18s;
    -webkit-appearance: none;
    appearance: none;
}
.sec-input:focus {
    border-color: #0058af;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(0,88,175,0.1);
}
.sec-input:disabled,
.sec-input[readonly] {
    background: #f1f5f9;
    color: #94a3b8;
    cursor: not-allowed;
    border-color: #e2e8f0;
}
.sec-input::placeholder { color: #cbd5e1; }
.sec-input.has-toggle { padding-right: 44px; }

/* Botón ojo */
.sec-pw-toggle {
    position: absolute; right: 12px;
    background: none; border: none;
    cursor: pointer; color: #94a3b8;
    font-size: 17px; padding: 4px;
    display: flex; align-items: center;
    transition: color 0.15s; z-index: 1;
    line-height: 1;
}
.sec-pw-toggle:hover { color: #0058af; }

/* ── Error inline ── */
.sec-error {
    font-size: 12px; color: #dc2626;
    margin-top: 6px;
    display: none; align-items: center; gap: 4px;
}
.sec-error.visible { display: flex; }
.sec-error i { font-size: 14px; }

/* ── Botón principal ── */
.sec-btn {
    width: 100%;
    padding: 12px;
    border: none; border-radius: 10px;
    background: #0058af;
    color: #fff;
    font-size: 14px; font-weight: 700; font-family: inherit;
    cursor: pointer;
    transition: background 0.2s, transform 0.15s, box-shadow 0.2s;
    display: flex; align-items: center; justify-content: center; gap: 8px;
    box-shadow: 0 4px 14px rgba(0,88,175,0.28);
    margin-top: 8px;
}
.sec-btn:hover:not(:disabled) {
    background: #003d82;
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(0,88,175,0.35);
}
.sec-btn:active:not(:disabled) { transform: translateY(0); }
.sec-btn:disabled { opacity: 0.7; cursor: not-allowed; }

/* Loading spinner */
.sec-spinner {
    width: 16px; height: 16px;
    border: 2px solid rgba(255,255,255,0.4);
    border-top-color: #fff;
    border-radius: 50%;
    animation: secSpin 0.7s linear infinite;
    display: none; flex-shrink: 0;
}
.sec-btn.loading .sec-spinner  { display: block; }
.sec-btn.loading .sec-btn-text { display: none; }
@keyframes secSpin { to { transform: rotate(360deg); } }

@media (max-width: 520px) {
    .sec-header { padding: 24px 24px; }
    .sec-body   { padding: 20px 24px 28px; }
    .sec-notice { margin: 20px 24px 0; }
}
</style>

<div class="sec-wrap">
    <div class="sec-card">

        <!-- ── Header ── -->
        <div class="sec-header">
            <div class="sec-header-icon">
                <i class="ti ti-lock-cog"></i>
            </div>
            <div class="sec-header-title">Establece tu nueva contraseña</div>
            <div class="sec-header-sub">Gestión Humana y Cultura · Clínica Zayma SAS</div>
        </div>

        <!-- ── Aviso de contexto ── -->
        <div class="sec-notice">
            <i class="ti ti-alert-triangle"></i>
            <span>Si tu contraseña fue reseteada por un administrador. Por seguridad, debes establecer una nueva antes de continuar.</span>
        </div>

        <!-- ── Formulario ── -->
        <div class="sec-body">
            <form action="" method="POST" autocomplete="off" id="formSeguridad">

                <div class="sec-field">
                    <label class="sec-label" for="sec_id">Número de Identificación</label>
                    <div class="sec-input-wrap">
                        <i class="ti ti-id-badge sec-input-icon"></i>
                        <input type="text"
                               class="sec-input"
                               id="sec_id"
                               name="identificacion"
                               value="<?php echo htmlspecialchars($_SESSION['identificacion'] ?? '', ENT_QUOTES); ?>"
                               readonly />
                    </div>
                </div>

                <div class="sec-field">
                    <label class="sec-label" for="sec_pw">Nueva Contraseña</label>
                    <div class="sec-input-wrap">
                        <i class="ti ti-lock sec-input-icon"></i>
                        <input type="password"
                               class="sec-input has-toggle"
                               id="sec_pw"
                               name="password"
                               minlength="6"
                               placeholder="Mínimo 6 caracteres"
                               required />
                        <button type="button" class="sec-pw-toggle"
                                onclick="secTogglePw('sec_pw', this)"
                                tabindex="-1"
                                aria-label="Mostrar u ocultar contraseña">
                            <i class="ti ti-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="sec-field">
                    <label class="sec-label" for="sec_pw_confirm">Confirmar Nueva Contraseña</label>
                    <div class="sec-input-wrap">
                        <i class="ti ti-lock-check sec-input-icon"></i>
                        <input type="password"
                               class="sec-input has-toggle"
                               id="sec_pw_confirm"
                               placeholder="Repite tu nueva contraseña"
                               required />
                        <button type="button" class="sec-pw-toggle"
                                onclick="secTogglePw('sec_pw_confirm', this)"
                                tabindex="-1"
                                aria-label="Mostrar u ocultar contraseña">
                            <i class="ti ti-eye"></i>
                        </button>
                    </div>
                    <div class="sec-error" id="secPassError">
                        <i class="ti ti-alert-circle"></i> Las contraseñas no coinciden.
                    </div>
                </div>

                <button class="sec-btn" type="submit" id="btnSeguridad">
                    <div class="sec-spinner"></div>
                    <span class="sec-btn-text">
                        <i class="ti ti-shield-check"></i> Establecer nueva contraseña
                    </span>
                </button>

                <input type="hidden" name="formType" value="activation">
            </form>
        </div>

    </div>
</div>

<script>
(function () {
    function secTogglePw(inputId, btn) {
        var input = document.getElementById(inputId);
        var icon  = btn.querySelector('i');
        if (input.type === 'password') {
            input.type     = 'text';
            icon.className = 'ti ti-eye-off';
        } else {
            input.type     = 'password';
            icon.className = 'ti ti-eye';
        }
    }

    document.getElementById('formSeguridad').addEventListener('submit', function (e) {
        var pwd     = document.getElementById('sec_pw').value;
        var confirm = document.getElementById('sec_pw_confirm').value;
        var errEl   = document.getElementById('secPassError');

        if (pwd !== confirm) {
            e.preventDefault();
            errEl.classList.add('visible');
            return;
        }
        errEl.classList.remove('visible');

        var btn = document.getElementById('btnSeguridad');
        btn.classList.add('loading');
        btn.disabled = true;
    });

    window.secTogglePw = secTogglePw;
})();
</script>

<?php
    if (isset($_POST['formType'])) {
        if (isset($_POST['identificacion'], $_POST['password'])) {
            $loginController->ActualizarCuentaController();
        }
    }
?>
