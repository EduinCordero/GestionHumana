# Manual de Usuario — Evaluación de Desempeño
> **Clínica Zayma SAS** · Plataforma de Evaluación de Desempeño
> Versión 2.0 · Actualizado: 2026-05-26

---

## ¿Cuál es su perfil?

Seleccione su rol para ir directamente a su guía personalizada:

<div class="mu-role-grid">
  <a class="mu-role-card" href="#a-id-colaborador">
    <div class="mu-role-card-icon" style="background:#dbeafe;"><i class="ti ti-user" style="color:#1d4ed8;"></i></div>
    <div class="mu-role-card-title">Colaborador</div>
    <div class="mu-role-card-desc">Realizo mi autoevaluación, evalúo a mi líder y gestiono mi plan de mejora.</div>
    <div class="mu-role-card-link"><i class="ti ti-arrow-right"></i> Ver mi guía</div>
  </a>
  <a class="mu-role-card" href="#a-id-lider">
    <div class="mu-role-card-icon" style="background:#dcfce7;"><i class="ti ti-users" style="color:#15803d;"></i></div>
    <div class="mu-role-card-title">Líder Funcional</div>
    <div class="mu-role-card-desc">Evalúo a mi equipo, registro el feedback y asigno objetivos SMART.</div>
    <div class="mu-role-card-link"><i class="ti ti-arrow-right"></i> Ver mi guía</div>
  </a>
  <a class="mu-role-card" href="#a-id-director">
    <div class="mu-role-card-icon" style="background:#ede9fe;"><i class="ti ti-crown" style="color:#6d28d9;"></i></div>
    <div class="mu-role-card-title">Director</div>
    <div class="mu-role-card-desc">Adicional al rol de Líder, doy feedback de liderazgo a mis líderes a cargo.</div>
    <div class="mu-role-card-link"><i class="ti ti-arrow-right"></i> Ver mi guía</div>
  </a>
  <a class="mu-role-card" href="#a-id-admin">
    <div class="mu-role-card-icon" style="background:#fee2e2;"><i class="ti ti-settings" style="color:#b91c1c;"></i></div>
    <div class="mu-role-card-title">Administrador</div>
    <div class="mu-role-card-desc">Gestiono períodos, monitoreo el avance global y administro la plataforma.</div>
    <div class="mu-role-card-link"><i class="ti ti-arrow-right"></i> Ver mi guía</div>
  </a>
</div>

---

## Visión general del sistema

**Evaluación de Desempeño** es la plataforma digital de Clínica Zayma SAS para gestionar el proceso de evaluación de sus colaboradores. Integra directamente con el sistema de nómina institucional, garantizando que solo el personal activo y con la antigüedad requerida acceda.

<div class="mu-features">
  <div class="mu-feature">
    <div class="mu-feature-icon"><i class="ti ti-clipboard-check" style="color:#0058af;"></i></div>
    <div><div class="mu-feature-title">Evaluación por competencias</div><div class="mu-feature-desc">Autoevaluación, evaluación de líderes y de colaboradores según perfil de cargo.</div></div>
  </div>
  <div class="mu-feature">
    <div class="mu-feature-icon"><i class="ti ti-message-circle" style="color:#16a34a;"></i></div>
    <div><div class="mu-feature-title">Feedback formal</div><div class="mu-feature-desc">Registro estructurado de la reunión de retroalimentación con firma digital de ambas partes.</div></div>
  </div>
  <div class="mu-feature">
    <div class="mu-feature-icon"><i class="ti ti-target" style="color:#d97706;"></i></div>
    <div><div class="mu-feature-title">Planes de mejora SMART</div><div class="mu-feature-desc">Objetivos específicos, medibles y accionables asignados al colaborador tras el feedback.</div></div>
  </div>
  <div class="mu-feature">
    <div class="mu-feature-icon"><i class="ti ti-chart-histogram" style="color:#7c3aed;"></i></div>
    <div><div class="mu-feature-title">Analítica e indicadores</div><div class="mu-feature-desc">Reportes por competencia, rankings, brechas y seguimiento de acuerdos para directivos y admins.</div></div>
  </div>
</div>

### El proceso de evaluación — panorama completo

```mermaid
flowchart LR
    A([Período activo]) --> B[Autoevaluación\ndel colaborador]
    A --> C[Evaluación de\nliderazgo]
    B --> D[Líder evalúa\nal colaborador]
    C --> D
    D --> E[Sesión de\nFeedback]
    E --> F[Firma del\ncolaborador]
    E --> G[Objetivos\nSMART]
    F --> H([Plan de mejora\nactivo])
    G --> H
    style A fill:#0058af,color:#fff,stroke:none
    style H fill:#16a34a,color:#fff,stroke:none
```

---

## Primeros pasos

### Activar mi cuenta por primera vez

Si es la primera vez que accede a la plataforma, debe activar su cuenta antes de iniciar sesión.

<ol class="mu-steps">
  <li class="mu-step">
    <div class="mu-step-num">1</div>
    <div class="mu-step-body">
      <div class="mu-step-title">Abrir el sistema en su navegador</div>
      <div class="mu-step-desc">Use Chrome, Edge o Firefox. Ingrese a la URL del sistema proporcionada por Gestión Humana
      o, de manera predeterminada, desde la Intranet de la institución, seleccionando el ícono “Evaluación de Desempeño”.</div>
    </div>
  </li>
  <li class="mu-step">
    <div class="mu-step-num">2</div>
    <div class="mu-step-body">
      <div class="mu-step-title">Hacer clic en "Activar mi cuenta"</div>
      <div class="mu-step-desc">En la pantalla de inicio de sesión encontrará esta opción debajo del formulario principal.</div>
    </div>
  </li>
  <li class="mu-step">
    <div class="mu-step-num">3</div>
    <div class="mu-step-body">
      <div class="mu-step-title">Ingresar su cédula y crear una contraseña</div>
      <div class="mu-step-desc">La contraseña debe tener mínimo 6 caracteres. El sistema verifica que usted esté activo en nómina.</div>
    </div>
  </li>
  <li class="mu-step mu-step-done">
    <div class="mu-step-num"><i class="ti ti-check" style="font-size:14px;"></i></div>
    <div class="mu-step-body">
      <div class="mu-step-title">Su cuenta queda activa de inmediato</div>
      <div class="mu-step-desc">Puede iniciar sesión en ese momento. No es necesaria ninguna aprobación adicional.</div>
    </div>
  </li>
</ol>

> ⚠ **Nota:** Si aparece el mensaje "No cumple el tiempo mínimo", es porque tiene menos de 90 días de antigüedad en nómina. Contacte a Gestión Humana si considera que es un error.

### Iniciar sesión

1. Ingrese su **número de cédula** en el campo de identificación.
2. Ingrese su **contraseña**.
3. Haga clic en **"Ingresar"**.

> ⚠ **Contraseña temporal:** Si un administrador reseteó su contraseña, el sistema le pedirá cambiarla de forma obligatoria en el primer ingreso.

### Cambiar mi contraseña

Vaya a **Seguridad** en el menú lateral. Ingrese su cédula y la nueva contraseña, luego haga clic en **"Actualizar"**. El cambio es inmediato.

### Cerrar sesión

Haga clic en el ícono de salida (<i class="ti ti-logout" style="font-size:14px;vertical-align:middle;"></i>) en la parte inferior del menú lateral, junto a su nombre y foto de perfil.

---

## Navegación del sistema

El sistema cuenta con un **menú lateral** fijo a la izquierda. Puede colapsarlo haciendo clic en el botón **`«`** para ganar espacio en pantalla — en modo colapsado, los íconos siguen siendo clickeables.

| Módulo | Disponible para |
|--------|-----------------|
| <i class="ti ti-home"></i> **Inicio** | Todos los usuarios |
| <i class="ti ti-clipboard-check"></i> **Evaluaciones** | Todos los usuarios |
| <i class="ti ti-message-circle"></i> **Feedback** | Líderes funcionales y administradores |
| <i class="ti ti-chart-bar"></i> **Reportes** | Todos los usuarios |
| <i class="ti ti-layout-dashboard"></i> **Panel Líder** | Líderes funcionales y administradores |
| <i class="ti ti-shield-lock"></i> **Seguridad** | Todos los usuarios |
| <i class="ti ti-settings"></i> **Admin** | Solo administradores |
| <i class="ti ti-chart-histogram"></i> **Analítica** | Solo administradores |

### Notificaciones

El ícono de campana <i class="ti ti-bell" style="font-size:14px;vertical-align:middle;"></i> en la esquina superior derecha muestra alertas contextuales:

- **Autoevaluación pendiente** — no ha completado su autoevaluación del período activo.
- **Feedback pendiente de firma** — *(solo líderes)* tiene sesiones de feedback pendientes de firmar con el colaborador.
- **Colaboradores sin evaluar** — *(solo líderes)* tiene evaluaciones de equipo pendientes.
- **Feedback sin registrar** — *(solo líderes)* hay colaboradores sin sesión de feedback documentada.

### Tour de ayuda interactivo

En cada módulo encontrará un botón <span style="display:inline-flex;align-items:center;justify-content:center;width:22px;height:22px;border-radius:50%;background:linear-gradient(135deg,#0058af,#0074e0);color:#fff;font-size:12px;font-weight:700;vertical-align:middle;">?</span> en la esquina inferior derecha de la pantalla. Al hacer clic se activa un **tour guiado paso a paso** que explica cada elemento del módulo. También puede iniciarlo desde el mismo botón en la barra superior derecha.

> ✅ **Recomendación:** Use el tour la primera vez que acceda a un módulo nuevo. Es la forma más rápida de entender su funcionamiento.

---

<a id="a-id-colaborador"></a>
## Colaborador — Guía completa

<div class="mu-section-hero mu-hero-colab">
  <div class="mu-section-hero-icon"><i class="ti ti-user"></i></div>
  <div class="mu-section-hero-body">
    <div class="mu-section-hero-title">Su recorrido en el proceso</div>
    <div class="mu-section-hero-sub">Autoevaluación · Evaluar a su líder · Reportes · Plan de mejora</div>
  </div>
</div>

### Su flujo paso a paso

```mermaid
flowchart TD
    A([Período activo]) --> B[Ir a Evaluaciones]
    B --> C[Iniciar Autoevaluación]
    C --> D{¿Es colaborador\nasistencial?}
    D -- Sí --> E[Completar\nExperiencia Azul]
    D -- No --> F
    E --> F[Evaluar a mi líder\nen Liderazgo]
    F --> G[Ver mis resultados\nen Reportes]
    G --> H{¿Recibí\nfeedback?}
    H -- Sí --> I[Firmar la sesión\nde feedback]
    I --> J[Ingresar mi plan\nde acción personal]
    H -- Esperar --> K[Notificación\nautomática]
    style A fill:#1d4ed8,color:#fff,stroke:none
    style J fill:#16a34a,color:#fff,stroke:none
```

### Realizar la autoevaluación

La autoevaluación es el **primer paso obligatorio** del proceso. Sin ella, su líder no puede completar la evaluación ni el feedback.

1. Vaya a **Evaluaciones** en el menú lateral.
2. En la sección **"Mi evaluación"**, haga clic en **"Iniciar autoevaluación"**.
3. Para cada competencia seleccione la opción que mejor describe su desempeño observado en el período.
4. Opcionalmente, agregue una **justificación** en el campo de texto (aplica para calificaciones altas y bajas).
5. Haga clic en **"Confirmar y guardar"**.

> ⚠ **Importante:** Una vez confirmada, la autoevaluación **no puede modificarse**. Revise bien sus respuestas antes de confirmar.

### Evaluar a mi líder

1. En la pantalla de Evaluaciones, localice la sección **"Mi líder"**.
2. Haga clic en **"Evaluar"** junto al nombre de su jefe directo.
3. Responda las preguntas de competencias de liderazgo (P12–P16).
4. Confirme y guarde.

### Experiencia Azul (solo asistenciales)

Si su cargo es asistencial, después de completar la evaluación a su líder el sistema le ofrecerá la **Evaluación de Experiencia Azul**. Esta evalúa la experiencia del colaborador asistencial en su relación con el líder y viceversa. Es independiente y no bloquea las demás evaluaciones.

### Ver mis resultados

Vaya a **Reportes** y explore las pestañas:

| Pestaña | Qué encontrará |
|---------|----------------|
| **Autoevaluación** | Sus calificaciones por competencia con gráficas de radar y barras |
| **Recibidas** | Cuenta las evaluaciónes recibidas |
| **Realizadas** | Las evaluaciones que usted completó a otros, puede ver el detalles de la evaluación realizada|
| **Mi Plan de Mejora** | Objetivos SMART asignados por su líder tras el feedback |

### Firmar el feedback

La firma del feedback ocurre **durante la reunión presencial** con su líder. Una vez que el líder registre la sesión y asigne los objetivos SMART en el sistema, le pedirá que ingrese sus credenciales directamente en el mismo sistema para confirmar el recibido.

1. Su líder abre el formulario de feedback y completa la sesión.
2. Le indica que ingrese sus credenciales en el campo de firma del colaborador.
3. Ingrese su **cédula y contraseña** y haga clic en **"Confirmar firma"**.
4. El plan de mejora queda activo de inmediato.

> ⚠ **Recuerde:** El plan de mejora solo se activa cuando **ambas partes** han firmado — usted y su líder.

### Ingresar mi plan de acción

En **Reportes → Mi Plan de Mejora**:

- Verá los objetivos SMART asignados por su líder.
- Para cada objetivo, ingrese su **plan de acción personal** (qué hará concretamente para lograrlo).
- El estado cambia de *Pendiente* a *Enviado* cuando ingresa su plan.
- Su líder revisará y aprobará el plan desde el Panel de Liderazgo.

---

<a id="a-id-lider"></a>
## Líder Funcional — Guía completa

<div class="mu-section-hero mu-hero-lider">
  <div class="mu-section-hero-icon"><i class="ti ti-users"></i></div>
  <div class="mu-section-hero-body">
    <div class="mu-section-hero-title">Su responsabilidad en el proceso</div>
    <div class="mu-section-hero-sub">Evaluar a su equipo · Registrar feedback · Asignar objetivos · Seguimiento</div>
  </div>
</div>

### Su flujo completo

```mermaid
flowchart TD
    A([Período activo]) --> B[Autoevaluación\npropia]
    B --> C[Evaluar a cada\ncolaborador]
    C --> D{¿Tiene colaboradores\nasistenciales?}
    D -- Sí --> E[Experiencia\nAzul]
    D -- No --> F
    E --> F[Panel Líder\nrevisar estado]
    F --> G[Módulo Feedback\nregistrar sesión]
    G --> H[Firmar el\nfeedback]
    H --> I[Asignar objetivos\nSMART]
    I --> J[Seguimiento del\nplan de acción]
    style A fill:#15803d,color:#fff,stroke:none
    style J fill:#0058af,color:#fff,stroke:none
```

### Evaluar a su equipo

1. Vaya a **Evaluaciones**.
2. En la sección **"Mi equipo"**, verá la lista de colaboradores a su cargo con su estado (Pendiente / Completado).
3. Haga clic en **"Evaluar"** junto a cada colaborador.
4. Complete las preguntas de competencias de desempeño (P1–P11).
5. Confirme para cada colaborador.

> ✅ **Consejo:** Evalúe a todos sus colaboradores antes de iniciar el proceso de feedback, para que las calificaciones ya estén disponibles durante la sesión.

### Panel de Liderazgo — su centro de mando

Acceda desde **Panel Líder** en el menú. Tiene cuatro pestañas:

| Pestaña | Para qué sirve |
|---------|----------------|
| **Dashboard** | KPIs del equipo: quién completó la autoevaluación, quién fue evaluado, feedback completado |
| **Acuerdos** | Todos los objetivos SMART asignados al equipo y su estado de cumplimiento |
| **Feedback** | Registro histórico de las sesiones de feedback realizadas |
| **Alertas** | Vista rápida de pendientes críticos — colaboradores sin evaluar, sin feedback, con acuerdos vencidos |

Haga clic en cualquier colaborador en la tabla del Dashboard para ver su **perfil completo**: calificaciones, acuerdos asignados y estado del plan de acción.

### Registrar la sesión de feedback

El módulo de **Feedback** formaliza la reunión de retroalimentación con cada colaborador.

<ol class="mu-steps">
  <li class="mu-step">
    <div class="mu-step-num">1</div>
    <div class="mu-step-body">
      <div class="mu-step-title">Seleccionar al colaborador</div>
      <div class="mu-step-desc">En el módulo Feedback, localice al colaborador en la tabla y haga clic en su nombre.</div>
    </div>
  </li>
  <li class="mu-step">
    <div class="mu-step-num">2</div>
    <div class="mu-step-body">
      <div class="mu-step-title">Ver las calificaciones comparadas</div>
      <div class="mu-step-desc">El sistema muestra la autoevaluación del colaborador vs. la evaluación que usted realizó, por competencia.</div>
    </div>
  </li>
  <li class="mu-step">
    <div class="mu-step-num">3</div>
    <div class="mu-step-body">
      <div class="mu-step-title">Registrar la sesión</div>
      <div class="mu-step-desc">Ingrese la fecha de la reunión y las observaciones generales de la sesión de feedback.</div>
    </div>
  </li>
  <li class="mu-step">
    <div class="mu-step-num">4</div>
    <div class="mu-step-body">
      <div class="mu-step-title">Asignar objetivos SMART</div>
      <div class="mu-step-desc">Para cada competencia que requiera atención, seleccione los objetivos del catálogo. Máximo 3 objetivos por sesión.</div>
    </div>
  </li>
  <li class="mu-step">
    <div class="mu-step-num">5</div>
    <div class="mu-step-body">
      <div class="mu-step-title">Firmar digitalmente</div>
      <div class="mu-step-desc">Ingrese su cédula y contraseña para firmar. El colaborador recibirá una notificación para firmar también.</div>
    </div>
  </li>
  <li class="mu-step mu-step-done">
    <div class="mu-step-num"><i class="ti ti-check" style="font-size:14px;"></i></div>
    <div class="mu-step-body">
      <div class="mu-step-title">El plan de mejora queda activo</div>
      <div class="mu-step-desc">Cuando el colaborador también firme, el plan de mejora se activa y puede ingresar su plan de acción personal.</div>
    </div>
  </li>
</ol>

### Hacer seguimiento del plan de acción

Desde **Panel Líder → Acuerdos** puede:

- Ver el estado de cada objetivo (Pendiente / Respondido / Aprobado).
- Revisar el plan de acción personal que ingresó el colaborador.
- **Aprobar** el plan si considera que es adecuado, o dejar comentarios para ajustarlo.
- Exportar un CSV con el resumen de acuerdos del equipo.

---

<a id="a-id-director"></a>
## Director — Feedback de liderazgo

<div class="mu-section-hero mu-hero-dir">
  <div class="mu-section-hero-icon"><i class="ti ti-crown"></i></div>
  <div class="mu-section-hero-body">
    <div class="mu-section-hero-title">Responsabilidades adicionales al rol de Líder</div>
    <div class="mu-section-hero-sub">Feedback de liderazgo (P12–P16) a los líderes a su cargo</div>
  </div>
</div>

Como Director tiene todas las responsabilidades de un Líder Funcional, más la capacidad de dar **feedback de liderazgo** a los líderes que están bajo su cargo.

### Feedback de liderazgo (Flujo 2)

Este proceso evalúa las **competencias de liderazgo** (P12–P16: Propósito, Colaboración, Consistencia, Adaptabilidad, Amor) de cada líder a su cargo, a partir de cómo los evaluaron sus propios colaboradores.

1. En el módulo **Feedback**, aparece una sección adicional: **"Líderes a mi cargo"**.
2. Para cada líder, el sistema muestra el promedio de las calificaciones P12–P16 recibidas de su equipo.
3. Registre la sesión de feedback con sus observaciones y asigne objetivos SMART de liderazgo.
4. Firme la sesión. El líder recibirá la notificación para firmar.

> ✅ **Visibilidad:** El líder puede ver los objetivos de liderazgo que le asignó su director en la sección **"Mi Plan de Liderazgo"** dentro del módulo Feedback.

---

<a id="a-id-admin"></a>
## Administrador — Gestión del proceso

<div class="mu-section-hero mu-hero-admin">
  <div class="mu-section-hero-icon"><i class="ti ti-settings"></i></div>
  <div class="mu-section-hero-body">
    <div class="mu-section-hero-title">Control total del proceso de evaluación</div>
    <div class="mu-section-hero-sub">Períodos · Seguimiento · Notificaciones · Configuración · Analítica</div>
  </div>
</div>

<div class="mu-chips">
  <span class="mu-chip"><i class="ti ti-calendar"></i>Períodos</span>
  <span class="mu-chip"><i class="ti ti-eye"></i>Seguimiento</span>
  <span class="mu-chip"><i class="ti ti-trending-up"></i>Avance</span>
  <span class="mu-chip"><i class="ti ti-chart-bar"></i>Promedios</span>
  <span class="mu-chip"><i class="ti ti-users"></i>Usuarios</span>
  <span class="mu-chip"><i class="ti ti-target"></i>Objetivos</span>
  <span class="mu-chip"><i class="ti ti-award"></i>Competencias</span>
  <span class="mu-chip"><i class="ti ti-id-badge"></i>Colaboradores</span>
</div>

### Gestión de períodos

Un **período de evaluación** define el intervalo de tiempo en que está abierto el proceso. Solo puede haber **un período activo** a la vez.

| Acción | Cuándo usarla |
|--------|--------------|
| **Crear período** | Al inicio de cada ciclo de evaluación. Define nombre, fecha de apertura y cierre. |
| **Activar período** | Cuando el proceso debe comenzar. Esto desactiva el período anterior automáticamente. |
| **Editar fecha de cierre** | Para ampliar el plazo si se necesita más tiempo. |
| **Cerrar período** | Al finalizar el ciclo. Los colaboradores dejan de poder evaluar. |
| **Habilitar Feedback** | Solo cuando las evaluaciones ya estén suficientemente avanzadas. Los líderes solo acceden al módulo Feedback cuando este interruptor está activo. |

> ⚠ **Importante:** Habilite el módulo de Feedback **solo cuando** la mayoría de evaluaciones ya estén completadas. Habilitarlo muy temprano puede generar feedback sin datos suficientes.

### Seguimiento y notificaciones

La pestaña **Seguimiento** muestra el estado de todos los colaboradores del período activo:

- Estado individual: Sin iniciar / En progreso / Completo.
- Botón **"Notificar"** individual para enviar recordatorio por correo.
- Botón **"Notificación masiva"** para notificar a todos los que tengan pendientes.

### Avance por área

La pestaña **Avance** muestra gráficas y tabla con el porcentaje de completado por área o dependencia. Útil para identificar áreas rezagadas antes del cierre del período. Exporte con **"Exportar CSV"**.

### Promedios organizacionales

La pestaña **Promedios** muestra las calificaciones promedio de toda la organización:

- **Desempeño** (P1–P11): promedios por competencia, agrupados por área.
- **Liderazgo** (P12–P16): promedios por competencia de liderazgo, por área.

Ambas tablas se pueden exportar a CSV para análisis externo.

### Usuarios

Busque colaboradores por cédula para gestionar sus permisos:

- Activar/desactivar perfil de **Administrador**.
- Activar/desactivar permiso de **ver detalle en reportes**.
- **Resetear contraseña**: establece la cédula como contraseña temporal. El usuario deberá cambiarla en el próximo ingreso.

### Catálogo de objetivos SMART

En la pestaña **Objetivos** puede mantener el banco de objetivos que los líderes seleccionan durante el feedback:

- Cada objetivo está asociado a una **competencia** y a una **calificación** (para qué nivel de desempeño aplica).
- Campos configurables: objetivo, modelo, indicador, meta, plazo, evidencia, seguimiento.
- Active o desactive objetivos sin eliminarlos permanentemente.

### Competencias

Administre el catálogo de competencias evaluadas:

- Edite el **nombre** y la **pregunta** de cada competencia.
- Modifique las **descripciones de opciones** que ve el evaluador como guía al calificar.
- Active o desactive competencias por período.

### Colaboradores (asignación evaluado/evaluador)

Gestione el listado de personas que participan en el proceso:

- **Agregar**: busque por cédula en nómina y complete los datos de evaluación (rol, jefe, área, email, celular, si aplica Experiencia Azul).
- **Editar**: modifique rol, área, jefe directo o bandera de director.
- **Activar/desactivar**: deshabilite a un colaborador para el período sin eliminarlo del sistema.

### Analítica BI

La sección **Analítica** (menú lateral) ofrece una visión profunda del proceso para análisis estratégico:

| Pestaña | Contenido |
|---------|-----------|
| **Dashboard** | KPIs globales: total evaluados, feedback completado, acuerdos asignados |
| **Individual** | Perfil detallado de calificaciones por colaborador |
| **Ranking** | Clasificación de colaboradores por desempeño promedio |
| **Histórico** | Comparativa entre períodos a lo largo del tiempo |
| **Acuerdos** | Estado de cumplimiento de todos los planes de mejora |
| **Procesos** | Avance desglosado por área/proceso |
| **Alertas** | Colaboradores con pendientes críticos o calificaciones bajas |
| **Colaboradores** | Vista tabular completa del desempeño de todo el equipo |
| **Brechas** | Diferencia entre autoevaluación y calificación del líder por competencia |
| **Exp. Azul** | Resultados del proceso de Experiencia Azul asistencial |

---

## Errores frecuentes y soluciones

| Situación | Causa | Solución |
|-----------|-------|----------|
| "Su cuenta no está activa" al iniciar sesión | La cuenta no fue activada o fue deshabilitada | Contacte al administrador de la plataforma |
| "No cumple el tiempo mínimo" al activar cuenta | Menos de 90 días de antigüedad en nómina | Espere a cumplir 3 meses o contacte a Gestión Humana si cree que es un error |
| "Identificación no encontrada" al activar | La cédula no existe en el sistema de nómina o el empleado no está activo | Verifique la cédula con Gestión Humana |
| El módulo "Feedback" no aparece en el menú | Su perfil no tiene rol de Líder Funcional | Solo líderes con ese permiso activo acceden a Feedback |
| La evaluación no aparece como completada | Se guardó pero no se confirmó | Intente de nuevo; si persiste, contacte soporte |
| "No hay período de evaluación activo" | El administrador no ha activado un período | El administrador debe crear y activar un período en el panel de Admin |
| El botón "Evaluar" no aparece | La evaluación ya fue completada | El estado cambia a "Completado" — no se puede reabrir |
| El plan de mejora no aparece | El feedback aún no tiene la firma de ambas partes | Verifique con su líder que él también haya firmado |
| Caracteres extraños (ñ, tildes) en archivos CSV | Configuración de codificación al abrir en Excel | En Excel: Datos → Desde texto/CSV → seleccione codificación UTF-8 |
| El tour de ayuda no inicia | El módulo aún está cargando | Espere a que la página termine de cargar y vuelva a intentar |

---

## Buenas prácticas

> ✅ **Complete la autoevaluación primero.** Es la base del proceso. Sin ella, su líder no puede generar un feedback con datos completos.

> ✅ **Justifique las calificaciones bajas y altas.** Al calificar con 1 o 5, use el campo de observaciones para dar contexto y orientación al evaluado.

> ✅ **Firme el feedback a tiempo.** El plan de mejora del colaborador solo se activa cuando ambas partes han firmado. Una firma pendiente bloquea todo el proceso de mejora.

> ✅ **Use el tour de ayuda.** Cada módulo tiene un botón <span style="display:inline-flex;align-items:center;justify-content:center;width:20px;height:20px;border-radius:50%;background:linear-gradient(135deg,#0058af,#0074e0);color:#fff;font-size:11px;font-weight:700;vertical-align:middle;">?</span> que explica paso a paso qué hace cada elemento. Es la forma más rápida de aprender.

> ✅ **Respete las fechas del período.** El sistema no permite evaluar fuera del período activo. Si necesita más tiempo, el administrador puede extender la fecha de cierre.

> ⚠ **No cierre el navegador durante una evaluación.** Las respuestas no se guardan automáticamente. Solo se registran al hacer clic en "Confirmar y guardar".

> ⚠ **Use Chrome, Edge o Firefox.** El sistema está optimizado para navegadores modernos. Evite Internet Explorer.

---

*Evaluación de Desempeño · Gestión Humana y Cultura*
