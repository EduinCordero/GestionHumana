<?php /* login-view.php — Enterprise UI v2 */ ?>
<style>
/* SweetAlert2 debe quedar por encima del overlay */
.swal2-container { z-index: 10000 !important; }

/* ── Overlay: cubre el viewport completo, ignora el pt-16 del layout padre ── */
#lp-overlay {
    position: fixed;
    inset: 0;
    z-index: 9999;
    display: flex;
    font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    background: #f8fafc;
}

/* ══════════════════════════════════════════════════
   PANEL IZQUIERDO — branding corporativo
══════════════════════════════════════════════════ */
.lp-left {
    width: 46%;
    background: linear-gradient(145deg, #003d82 0%, #0058af 55%, #1a6fd4 100%);
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 56px 60px;
    position: relative;
    overflow: hidden;
}
/* Círculos decorativos — misma lógica del sidebar del sistema */
.lp-left::before {
    content: '';
    position: absolute;
    width: 380px; height: 380px; border-radius: 50%;
    background: rgba(255,255,255,0.07);
    top: -100px; right: -80px;
    pointer-events: none;
}
.lp-left::after {
    content: '';
    position: absolute;
    width: 220px; height: 220px; border-radius: 50%;
    background: rgba(255,255,255,0.05);
    bottom: -60px; left: -50px;
    pointer-events: none;
}
.lp-circle-mid {
    position: absolute;
    width: 130px; height: 130px; border-radius: 50%;
    background: rgba(255,255,255,0.04);
    bottom: 170px; right: -24px;
    pointer-events: none;
}

/* Frase rotante */
.lp-phrase-wrap {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    margin-top: 8px;
    min-height: 72px;
}
.lp-phrase-icon {
    font-size: 22px;
    color: rgba(255,255,255,0.35);
    flex-shrink: 0;
    margin-top: 2px;
}
.lp-phrase {
    font-size: 0.95rem;
    color: rgba(255,255,255,0.82);
    line-height: 1.65;
    font-style: italic;
    margin: 0;
    transition: opacity 0.6s ease;
}
.lp-phrase.lp-fade { opacity: 0; }
.lp-logo {
    height: 48px;
    width: fit-content;
    border-radius: 12px;
    background: rgba(255,255,255,0.15);
    border: 1px solid rgba(255,255,255,0.22);
    display: flex; align-items: center; justify-content: center;
    padding: 8px 16px;
    margin-bottom: 28px;
    backdrop-filter: blur(8px);
    position: relative;
}

.lp-title {
    font-size: 2rem; font-weight: 800;
    color: #fff;
    line-height: 1.2; letter-spacing: -0.5px;
    margin-bottom: 12px;
    position: relative;
}
.lp-sub {
    font-size: 0.93rem;
    color: rgba(255,255,255,0.7);
    line-height: 1.7;
    max-width: 340px;
    margin-bottom: 44px;
    position: relative;
}


/* ══════════════════════════════════════════════════
   PANEL DERECHO — formulario
══════════════════════════════════════════════════ */
.lp-right {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 40px 32px;
    background: #f8fafc;
    overflow-y: auto;
}

/* ── Card ── */
.lp-card {
    background: #ffffff;
    border-radius: 20px;
    box-shadow:
        0 0 0 1px rgba(0,0,0,0.04),
        0 4px 6px -1px rgba(0,0,0,0.06),
        0 12px 40px -8px rgba(0,0,0,0.11);
    padding: 40px 40px 36px;
    width: 100%;
    max-width: 400px;
}

.lp-card-title {
    font-size: 1.3rem; font-weight: 800;
    color: #0f172a;
    margin-bottom: 4px;
}
.lp-card-sub {
    font-size: 13px; color: #64748b;
    margin-bottom: 28px;
}

/* ── Transición entre formularios ── */
.lp-form { display: block; }
.lp-form.lp-hidden { display: none; }
.lp-form.lp-entering {
    animation: lpFadeSlide 0.28s cubic-bezier(0.4,0,0.2,1) forwards;
}
@keyframes lpFadeSlide {
    from { opacity: 0; transform: translateY(12px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* ── Campo ── */
.lp-field { margin-bottom: 16px; }
.lp-label {
    display: block;
    font-size: 13px; font-weight: 600;
    color: #374151;
    margin-bottom: 7px;
}
.lp-input-wrap {
    position: relative;
    display: flex; align-items: center;
}
.lp-input-icon {
    position: absolute; left: 13px;
    color: #94a3b8; font-size: 17px;
    pointer-events: none; z-index: 1;
}
.lp-input {
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
.lp-input:focus {
    border-color: #0058af;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(0,88,175,0.1);
}
.lp-input::placeholder { color: #cbd5e1; }
.lp-input.has-toggle { padding-right: 44px; }

/* Botón ojo */
.lp-pw-toggle {
    position: absolute; right: 12px;
    background: none; border: none;
    cursor: pointer; color: #94a3b8;
    font-size: 17px; padding: 4px;
    display: flex; align-items: center;
    transition: color 0.15s; z-index: 1;
    line-height: 1;
}
.lp-pw-toggle:hover { color: #0058af; }

/* ── Error inline ── */
.lp-error {
    font-size: 12px; color: #dc2626;
    margin-top: 6px;
    display: none; align-items: center; gap: 4px;
}
.lp-error.visible { display: flex; }
.lp-error i { font-size: 14px; }

/* ── Info box ── */
.lp-info {
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 10px;
    padding: 11px 14px;
    font-size: 12.5px; color: #1d4ed8;
    line-height: 1.55;
    margin-bottom: 18px;
    display: flex; gap: 9px; align-items: flex-start;
}
.lp-info i { font-size: 16px; flex-shrink: 0; margin-top: 1px; }

/* ── Checkbox ── */
.lp-check-row {
    display: flex; align-items: center; gap: 9px;
    margin-bottom: 20px;
}
.lp-check-row input[type="checkbox"] {
    width: 16px; height: 16px;
    accent-color: #0058af; cursor: pointer;
    flex-shrink: 0;
}
.lp-check-row label {
    font-size: 13px; color: #64748b;
    cursor: pointer; user-select: none;
}

/* ── Botón principal ── */
.lp-btn {
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
    margin-bottom: 4px;
    position: relative;
}
.lp-btn:hover:not(:disabled) {
    background: #003d82;
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(0,88,175,0.35);
}
.lp-btn:active:not(:disabled) { transform: translateY(0); }
.lp-btn:disabled { opacity: 0.7; cursor: not-allowed; }

/* Loading spinner */
.lp-spinner {
    width: 16px; height: 16px;
    border: 2px solid rgba(255,255,255,0.4);
    border-top-color: #fff;
    border-radius: 50%;
    animation: lpSpin 0.7s linear infinite;
    display: none; flex-shrink: 0;
}
.lp-btn.loading .lp-spinner  { display: block; }
.lp-btn.loading .lp-btn-text { display: none; }
@keyframes lpSpin { to { transform: rotate(360deg); } }

/* ── Divisor ── */
.lp-divider {
    border: none;
    border-top: 1px solid #f1f5f9;
    margin: 24px 0 20px;
}

/* ── Link alternativo ── */
.lp-alt {
    text-align: center;
    font-size: 13px; color: #64748b;
}
.lp-alt a {
    color: #0058af; font-weight: 600;
    text-decoration: none; margin-left: 4px;
}
.lp-alt a:hover { text-decoration: underline; }

/* ── Footer ── */
.lp-footer {
    margin-top: 28px;
    font-size: 11.5px; color: #94a3b8;
    text-align: center;
}

/* ══════════════════════════════════════════════════
   RESPONSIVE
══════════════════════════════════════════════════ */
@media (max-width: 900px) {
    .lp-left { display: none; }
    .lp-right { background: #fff; }
    .lp-card { box-shadow: none; padding: 32px 24px; }
}
@media (max-width: 480px) {
    .lp-right { padding: 24px 16px; }
    .lp-card { padding: 28px 20px; }
    .lp-card-title { font-size: 1.15rem; }
}
</style>

<div id="lp-overlay">

    <!-- ══════════ PANEL IZQUIERDO ══════════ -->
    <div class="lp-left">
        <div class="lp-circle-mid"></div>

        <div class="lp-logo">
            <img src="<?php echo APP_URL; ?>app/views/img/logos/logo-zayma.png"
                 alt="Clínica Zayma"
                 style="filter: brightness(0) invert(1); max-height: 30px; width: auto;" />
        </div>

        <h1 class="lp-title">Evaluación de<br>Desempeño</h1>
        <p class="lp-sub">Gestión Humana y Cultura · Clínica Zayma SAS</p>

        <div class="lp-phrase-wrap">
            <i class="ti ti-quote lp-phrase-icon"></i>
            <p class="lp-phrase" id="lp-phrase"></p>
        </div>
    </div>

    <!-- ══════════ PANEL DERECHO ══════════ -->
    <div class="lp-right">
        <div class="lp-card">

            <!-- ── FORMULARIO LOGIN ── -->
            <div class="lp-form" id="loginForm">
                <div class="lp-card-title">Bienvenido de nuevo</div>
                <div class="lp-card-sub">Ingresa tus credenciales para continuar</div>

                <form action="" method="POST" autocomplete="off" id="formLogin">

                    <div class="lp-field">
                        <label class="lp-label" for="lp_id">Número de Identificación</label>
                        <div class="lp-input-wrap">
                            <i class="ti ti-id-badge lp-input-icon"></i>
                            <input type="text"
                                   inputmode="numeric"
                                   pattern="[0-9]+"
                                   class="lp-input"
                                   id="lp_id"
                                   name="identificacion"
                                   maxlength="20"
                                   placeholder="Ej. 1234567890"
                                   required />
                        </div>
                    </div>

                    <div class="lp-field">
                        <label class="lp-label" for="lp_pw">Contraseña</label>
                        <div class="lp-input-wrap">
                            <i class="ti ti-lock lp-input-icon"></i>
                            <input type="password"
                                   class="lp-input has-toggle"
                                   id="lp_pw"
                                   name="password"
                                   maxlength="20"
                                   placeholder="Tu contraseña"
                                   required />
                            <button type="button" class="lp-pw-toggle"
                                    onclick="lpTogglePw('lp_pw', this)"
                                    tabindex="-1"
                                    aria-label="Mostrar u ocultar contraseña">
                                <i class="ti ti-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="lp-check-row">
                        <input type="checkbox" id="lp_remember" checked>
                        <label for="lp_remember">Recuerda este dispositivo</label>
                    </div>

                    <button class="lp-btn" type="submit" id="btnLogin">
                        <div class="lp-spinner"></div>
                        <span class="lp-btn-text">
                            <i class="ti ti-login"></i> Iniciar Sesión
                        </span>
                    </button>

                    <input type="hidden" name="formType" value="login">
                </form>

                <hr class="lp-divider">

                <div class="lp-alt">
                    ¿Primera vez aquí?
                    <a href="#" onclick="mostrarActivacion(); return false;">Activar mi cuenta</a>
                </div>
            </div>

            <!-- ── FORMULARIO ACTIVACIÓN ── -->
            <div class="lp-form lp-hidden" id="activationForm">
                <div class="lp-card-title">Crear cuenta</div>
                <div class="lp-card-sub">Activa tu acceso a la plataforma</div>

                <form action="" method="POST" autocomplete="off" id="formActivacion">

                    <div class="lp-field">
                        <label class="lp-label" for="lp_act_id">Número de Identificación</label>
                        <div class="lp-input-wrap">
                            <i class="ti ti-id-badge lp-input-icon"></i>
                            <input type="text"
                                   inputmode="numeric"
                                   pattern="[0-9]+"
                                   class="lp-input"
                                   id="lp_act_id"
                                   name="identificacion"
                                   maxlength="20"
                                   placeholder="Ej. 1234567890"
                                   required />
                        </div>
                    </div>

                    <div class="lp-field">
                        <label class="lp-label" for="lp_act_pw">Contraseña</label>
                        <div class="lp-input-wrap">
                            <i class="ti ti-lock lp-input-icon"></i>
                            <input type="password"
                                   class="lp-input has-toggle"
                                   id="lp_act_pw"
                                   name="password"
                                   minlength="6"
                                   placeholder="Mínimo 6 caracteres"
                                   required />
                            <button type="button" class="lp-pw-toggle"
                                    onclick="lpTogglePw('lp_act_pw', this)"
                                    tabindex="-1"
                                    aria-label="Mostrar u ocultar contraseña">
                                <i class="ti ti-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="lp-field">
                        <label class="lp-label" for="confirmPassword">Confirmar Contraseña</label>
                        <div class="lp-input-wrap">
                            <i class="ti ti-lock-check lp-input-icon"></i>
                            <input type="password"
                                   class="lp-input has-toggle"
                                   id="confirmPassword"
                                   placeholder="Repite tu contraseña"
                                   required />
                            <button type="button" class="lp-pw-toggle"
                                    onclick="lpTogglePw('confirmPassword', this)"
                                    tabindex="-1"
                                    aria-label="Mostrar u ocultar contraseña">
                                <i class="ti ti-eye"></i>
                            </button>
                        </div>
                        <div class="lp-error" id="passError">
                            <i class="ti ti-alert-circle"></i> Las contraseñas no coinciden.
                        </div>
                    </div>

                    <div class="lp-info">
                        <i class="ti ti-info-circle"></i>
                        <span>Recuerda esta contraseña — la necesitarás para ingresar cada vez. Si la olvidas, contacta a Gestión Humana.</span>
                    </div>

                    <button class="lp-btn" type="submit" id="btnActivar">
                        <div class="lp-spinner"></div>
                        <span class="lp-btn-text">
                            <i class="ti ti-rocket"></i> Crear mi cuenta
                        </span>
                    </button>

                    <input type="hidden" name="formType" value="activation">
                </form>

                <hr class="lp-divider">

                <div class="lp-alt">
                    ¿Ya tienes cuenta?
                    <a href="#" onclick="mostrarLogin(); return false;">Volver al login</a>
                </div>
            </div>

        </div>

        <div class="lp-footer">
            © 2026 Clínica Zayma SAS &mdash; Gestión Humana y Cultura
        </div>
    </div>

</div>

<script>
(function () {
    /* ── Mostrar / ocultar formularios con animación ── */
    function mostrarActivacion() {
        var l = document.getElementById('loginForm');
        var a = document.getElementById('activationForm');
        l.classList.add('lp-hidden');
        a.classList.remove('lp-hidden');
        a.classList.add('lp-entering');
        setTimeout(function () { a.classList.remove('lp-entering'); }, 300);
    }

    function mostrarLogin() {
        var a = document.getElementById('activationForm');
        var l = document.getElementById('loginForm');
        a.classList.add('lp-hidden');
        document.getElementById('passError').classList.remove('visible');
        l.classList.remove('lp-hidden');
        l.classList.add('lp-entering');
        setTimeout(function () { l.classList.remove('lp-entering'); }, 300);
    }

    /* ── Validar contraseñas ── */
    function validarFormActivacion() {
        var pwd     = document.querySelector('#formActivacion input[name="password"]').value;
        var confirm = document.getElementById('confirmPassword').value;
        var errEl   = document.getElementById('passError');
        if (pwd !== confirm) {
            errEl.classList.add('visible');
            return false;
        }
        errEl.classList.remove('visible');
        return true;
    }

    /* ── Toggle mostrar/ocultar contraseña ── */
    function lpTogglePw(inputId, btn) {
        var input = document.getElementById(inputId);
        var icon  = btn.querySelector('i');
        if (input.type === 'password') {
            input.type  = 'text';
            icon.className = 'ti ti-eye-off';
        } else {
            input.type  = 'password';
            icon.className = 'ti ti-eye';
        }
    }

    /* ── Loading state en botón ── */
    function lpSetLoading(btn) {
        btn.classList.add('loading');
        btn.disabled = true;
    }

    /* ── Listeners de submit (disparan loading solo cuando el form pasa validación HTML5) ── */
    document.getElementById('formLogin').addEventListener('submit', function () {
        sessionStorage.clear();
        lpSetLoading(document.getElementById('btnLogin'));
    });

    document.getElementById('formActivacion').addEventListener('submit', function (e) {
        if (!validarFormActivacion()) {
            e.preventDefault();
            return;
        }
        lpSetLoading(document.getElementById('btnActivar'));
    });

    /* ── Frases rotantes sobre competencias ── */
    var frases = [
        "Las competencias no se declaran, se demuestran cada día.",
        "Un equipo que se evalúa honestamente es un equipo que evoluciona.",
        "Cada competencia fortalecida es un paso hacia la excelencia colectiva.",
        "La retroalimentación honesta es el mejor combustible del desarrollo profesional.",
        "Liderar con competencia es inspirar a otros a descubrir las suyas.",
        "El talento se cultiva con evaluación, compromiso y mejora continua.",
        "Conocerse a sí mismo es el primer paso hacia el crecimiento profesional."
    ];

    var fraseEl = document.getElementById('lp-phrase');
    if (fraseEl) {
        var idx = Math.floor(Math.random() * frases.length);
        fraseEl.textContent = frases[idx];

        setInterval(function () {
            fraseEl.classList.add('lp-fade');
            setTimeout(function () {
                idx = (idx + 1) % frases.length;
                fraseEl.textContent = frases[idx];
                fraseEl.classList.remove('lp-fade');
            }, 600);
        }, 5000);
    }

    /* ── Exponer funciones que el HTML inline necesita ── */
    window.mostrarActivacion    = mostrarActivacion;
    window.mostrarLogin         = mostrarLogin;
    window.validarFormActivacion = validarFormActivacion;
    window.lpTogglePw           = lpTogglePw;
})();
</script>

<?php
    if (isset($_POST['formType'])) {
        if ($_POST['formType'] === 'login' && isset($_POST['identificacion'], $_POST['password'])) {
            $loginController->iniciarSesionController();
        } elseif ($_POST['formType'] === 'activation' && isset($_POST['identificacion'], $_POST['password'])) {
            $loginController->activarCuentaController();
        }
    }
?>
