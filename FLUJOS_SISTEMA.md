# FLUJOS DEL SISTEMA — GestionHumana
> **Clínica Zayma SAS** · Diagramas técnicos y funcionales  
> Versión: 1.0 · Fecha: 2026-05-14

---

## 1. Arquitectura General del Sistema

```mermaid
graph TB
    subgraph Browser["🌐 Navegador"]
        UI[HTML / CSS / JS\nTailwind · SweetAlert2 · Preline]
    end

    subgraph WebServer["🖥 Servidor Web (Apache/XAMPP)"]
        IDX["index.php\nFront Controller"]

        subgraph Controllers["Controladores"]
            LC[loginController]
            VC[viewsController]
            EC[evaluarController]
            RC[reportController]
            AC[adminController]
            FC[feedbackController]
        end

        subgraph Models["Modelos"]
            MM["mainModel\n(Base OCI8)"]
            CM[competenciaModel]
            AM[adminModel]
            RM[reportModel]
            FM[feedbackModel]
            AcM[acuerdoModel]
        end

        subgraph Views["Vistas"]
            V_H[home-view]
            V_E[evaluarList-view]
            V_R[reportes-view]
            V_A[admin-view]
            V_F[feedback-view]
        end

        subgraph Config["Configuración"]
            CONF[config/app.php]
            DBCONF[config/pdoconfig.php]
            ENC[config/encoding.php]
        end
    end

    subgraph OracleDB["🗄 Oracle Database (10.2.202.215)"]
        VAADIN[(VAADINWEB\nHUM* tables)]
        ZAYMA[(ZAYMAWEB\nGH* tables)]
    end

    UI -->|"HTTP ?views=X"| IDX
    IDX --> LC & VC & EC & RC & AC & FC
    LC & EC & RC & AC & FC --> MM
    AM & CM & RM & FM & AcM --> MM
    MM -->|"oci_connect()"| VAADIN & ZAYMA
    IDX -->|"require_once"| V_H & V_E & V_R & V_A & V_F
    IDX --> CONF --> DBCONF & ENC
```

---

## 2. Flujo de Autenticación y Login

```mermaid
sequenceDiagram
    actor U as 👤 Usuario
    participant B as Navegador
    participant I as index.php
    participant LC as loginController
    participant ORA as Oracle DB

    U->>B: Accede a la URL del sistema
    B->>I: GET /?views=login
    I->>B: HTML formulario de login

    U->>B: Ingresa cédula + contraseña → clic Ingresar
    B->>I: POST /?views=login (identificacion, password)
    I->>LC: iniciarSesionController()
    LC->>ORA: SELECT HUMUSUARIOS JOIN GHEMPEMPLEADOS\nWHERE IDENTIFICACION=? AND CUENTA_ACTIVA=1
    ORA-->>LC: Fila con datos del usuario

    alt Usuario encontrado
        LC->>LC: password_verify(input, hash_bcrypt)
        alt Contraseña correcta
            LC->>LC: Carga $_SESSION[id, nombres,\nidempleado, rol, esadmin…]
            alt CONTRASENA_TEMP = 1
                LC->>B: redirect /seguridad/ (cambio obligatorio)
            else Normal
                LC->>B: redirect /home/?welcome=1
            end
        else Contraseña incorrecta
            LC->>B: SweetAlert "Usuario o clave incorrectos"
        end
    else Usuario no encontrado / inactivo
        LC->>B: SweetAlert "Cuenta no activa"
    end
```

---

## 3. Flujo MVC — Petición Estándar (GET página)

```mermaid
flowchart TD
    A([HTTP GET\n?views=reportes&tab=autoeval]) --> B[index.php]
    B --> C{¿Es petición AJAX\ninterceptable?}
    C -->|Sí| D[Controller directo\njson_encode → exit]
    C -->|No| E{¿Sesión válida?\n_SESSION id + identificacion}
    E -->|No| F[redirect /login/]
    E -->|Sí| G[viewsController\ngetViewsController]
    G --> H[viewsModel\nwhitelist check]
    H --> I{¿Vista autorizada?}
    I -->|No| J[404-view.php]
    I -->|Sí| K[Renderizar header\ninc/header.php]
    K --> L[Controller específico\ninstanciar + ejecutar]
    L --> M[Model: consultas OCI8\nconstruir array data]
    M --> N[Oracle DB\nresultados]
    N --> M
    M --> O[extract data\nrequire vista PHP]
    O --> P([HTML Response\nal navegador])
```

---

## 4. Flujo de Evaluación — Guardar Respuestas

```mermaid
flowchart TD
    A([Colaborador hace clic\nen 'Evaluar']) --> B[JS: iniciarEvaluacion\nidEvaluado, tipoEval]
    B --> C[POST form → vista de formulario\nautoevaluacion / subEvaluacion /\nevaluacionLiderazgo / experienciaAzul]
    C --> D[Muestra formulario\ncon competencias dinámicas]
    D --> E([Usuario responde\ntodas las competencias])
    E --> F[POST ?views=registrarEvaluacion]
    F --> G[evaluarController\nregistrarEvaluacion]

    G --> H{¿Período\nactivo?}
    H -->|No| I[redirect home + mensaje]
    H -->|Sí| J{¿Tipo eval\nválido?}
    J -->|No| K[SweetAlert error]
    J -->|Sí| L[competenciaModel\ngetCompetenciasPorRolTipo]

    L --> M[For cada competencia\nObtener opción del POST]
    M --> N[guardarRespuesta\nMERGE HUMRESPUESTA]
    N --> O[confirmarEvaluacion\nUPDATE CONFIRMADO=1]
    O --> P{¿Hay\njustificaciones?}
    P -->|Sí| Q[guardarJustificacion\npor respuesta]
    P -->|No| R{¿tipoEval es\nEXPERIENCIA_*?}
    Q --> R

    R -->|Sí| S[unset ea_idempleado\nredirect evaluarList]
    R -->|No| T{¿LIDER_A_COLAB y\nevaluado es asistencial?}
    T -->|Sí| U[Guardar ea_pendiente\nen _SESSION]
    T -->|No| V{¿COLAB_A_LIDER y\nevaluador es asistencial?}
    U --> W[redirect evaluarList]
    V -->|Sí| X[Guardar ea_pendiente\nen _SESSION]
    V -->|No| W
    X --> W
```

---

## 5. Flujo de Navegación del Usuario (por módulo)

```mermaid
stateDiagram-v2
    [*] --> Login: Acceso al sistema
    Login --> Home: Auth exitosa
    Login --> Seguridad: Contraseña temporal

    Seguridad --> Home: Contraseña actualizada

    Home --> Evaluaciones: Clic en menú
    Home --> Reportes: Clic en menú
    Home --> Feedback: Si es líder
    Home --> Admin: Si es admin

    Evaluaciones --> Autoevaluacion: Iniciar autoevaluación
    Evaluaciones --> EvalColaborador: Evaluar colaborador (líderes)
    Evaluaciones --> EvalLider: Evaluar a mi líder
    Evaluaciones --> ExpAzulColab: Exp. Azul colaborador
    Evaluaciones --> ExpAzulLider: Exp. Azul líder

    Autoevaluacion --> Evaluaciones: Guardar
    EvalColaborador --> Evaluaciones: Guardar
    EvalLider --> Evaluaciones: Guardar
    ExpAzulColab --> Evaluaciones: Guardar
    ExpAzulLider --> Evaluaciones: Guardar

    Reportes --> TabAutoeval: Pestaña Autoevaluación
    Reportes --> TabRecibidas: Pestaña Recibidas
    Reportes --> TabRealizadas: Pestaña Realizadas
    Reportes --> TabEquipo: Pestaña Mi Equipo (líderes)
    Reportes --> TabPlan: Pestaña Mi Plan de Mejora

    Feedback --> RegistrarFeedback: Registrar reunión
    Feedback --> FirmarFeedback: Firmar

    Admin --> PeriodosTab: Gestión de períodos
    Admin --> SeguimientoTab: Seguimiento global
    Admin --> UsuariosTab: Gestión de usuarios
    Admin --> ColaboradoresTab: Gestión de colaboradores
    Admin --> ObjetivosTab: Catálogo de objetivos
    Admin --> CompetenciasTab: Catálogo de competencias
```

---

## 6. Flujo Frontend → Backend → Base de Datos

```mermaid
sequenceDiagram
    participant JS as JavaScript (Navegador)
    participant IDX as index.php
    participant CTRL as Controller
    participant MOD as Model
    participant OCI as OCI8 Extension
    participant ORA as Oracle DB

    JS->>IDX: fetch/XHR (AJAX) o form POST
    IDX->>IDX: Detectar $url[0] y tipo de petición
    IDX->>CTRL: new Controller() → método()

    CTRL->>MOD: new Model()
    MOD->>OCI: $sql = query string
    OCI->>OCI: oci_parse($conn, $sql)
    OCI->>OCI: oci_bind_by_name() × N params
    OCI->>ORA: oci_execute($stmt)
    ORA-->>OCI: Resultset
    OCI->>OCI: oci_fetch_assoc() × N rows
    OCI->>MOD: array de filas (Windows-1252)
    MOD->>MOD: fromOracleEncoding() → UTF-8
    MOD-->>CTRL: array $data (UTF-8)

    alt AJAX
        CTRL->>IDX: header JSON + echo json_encode + exit
        IDX-->>JS: JSON response
    else Página completa
        CTRL->>IDX: extract($data) + require vista
        IDX-->>JS: HTML completo
    end
```

---

## 7. Relación entre Módulos

```mermaid
graph LR
    subgraph Autenticacion["Autenticación"]
        L[loginController]
        S[session_start.php]
    end

    subgraph Evaluacion["Evaluación"]
        EV[evaluarController]
        CM[competenciaModel]
    end

    subgraph Reporte["Reportes"]
        RC[reportController]
        RM[reportModel]
    end

    subgraph Acuerdos["Acuerdos / Mejora"]
        AC[acuerdoModel]
    end

    subgraph Feedback["Feedback"]
        FC[feedbackController]
        FM[feedbackModel]
    end

    subgraph Admin["Administración"]
        AD[adminController]
        AM[adminModel]
    end

    subgraph Base["Base"]
        MM[mainModel\nOCI8]
    end

    subgraph Oracle["Oracle DB"]
        VAADIN[(VAADINWEB)]
        ZAYMA[(ZAYMAWEB)]
    end

    L --> MM
    EV --> CM --> MM
    RC --> RM --> MM
    RC --> AC --> MM
    FC --> FM --> MM
    AD --> AM --> MM
    MM --> VAADIN & ZAYMA

    EV -.->|Completa eval| RC
    RC -.->|Acuerdos| AC
    FC -.->|Plan activo| RC
    AD -.->|Período activo| EV & RC & FC
```

---

## 8. Flujo de Consultas SQL por Módulo

```mermaid
flowchart LR
    subgraph Login
        L1["SELECT HUMUSUARIOS\nJOIN GHEMPEMPLEADOS\nJOIN GHEMPUBICACION\nJOIN GHEMPCARGOS"]
    end

    subgraph Autoevaluacion
        A1["SELECT HUMRESPUESTA\nWHERE TIPO_EVAL='AUTO'\nAND IDPERIODO=?"]
        A2["MERGE HUMRESPUESTA\n(guardar respuesta)"]
        A3["UPDATE HUMRESPUESTA\nSET CONFIRMADO=1"]
    end

    subgraph Evaluacion
        E1["SELECT HUMEMPLEADOEVAL\nWHERE IDEMPLEADO_EVAL=:lider\n(subordinados)"]
        E2["SELECT HUMROLCOMPETENCIA\nWHERE IDROL=? AND TIPO_EVAL=?\n(competencias del rol)"]
        E3["SELECT COUNT(*) HUMRESPUESTA\nWHERE CONFIRMADO=1\n(¿ya evaluado?)"]
    end

    subgraph Reportes
        R1["SELECT HUMRESPUESTA\nWHERE IDEMPLEADO=?\nAND TIPO_EVAL='AUTO'"]
        R2["SELECT HUMRESPUESTA\nWHERE IDEMPLEADO=?\nAND TIPO_EVAL IN\n('LIDER_A_COLAB',...)"]
        R3["SELECT HUMEMPLEADOEVAL\nJOIN HUMRESPUESTA\n(estado equipo)"]
    end

    subgraph Admin
        AD1["SELECT HUMPERIODOEVALUACION\nWHERE ESTADO=1"]
        AD2["UPDATE HUMPERIODOEVALUACION\nSET ESTADO=1 WHERE ID=?"]
        AD3["SELECT HUMEMPLEADOEVAL\n(estado seguimiento)"]
        AD4["BEGIN PKT_NOTIFICACION_MAIL\n.SEND_MAIL_RECORDATORIO\nEND"]
    end

    Login --> L1
    Autoevaluacion --> A1 --> A2 --> A3
    Evaluacion --> E1 & E2 & E3
    Reportes --> R1 & R2 & R3
    Admin --> AD1 & AD2 & AD3 & AD4
```

---

## 9. Dependencias Principales del Sistema

```mermaid
graph TD
    IDX[index.php] --> APP[config/app.php]
    IDX --> AUTO[autoload.php]
    IDX --> SS[session_start.php]
    IDX --> HEAD[inc/head.php]
    IDX --> HEADER[inc/header.php]
    IDX --> FOOTER[inc/footer.php]

    AUTO --> ENC[config/encoding.php]

    HEADER --> MM2[mainModel\nOCI8 para notifs]

    subgraph FrontEnd["Assets Frontend"]
        TAIL[Tailwind CSS]
        TAB[Tabler Icons CDN]
        SWLA[SweetAlert2]
        PRE[Preline UI]
    end

    HEAD --> TAIL & TAB & SWLA & PRE

    subgraph BackEnd["Backend PHP"]
        MM[mainModel]
        DBCONF[pdoconfig.php]
        OCI[PHP OCI8 Extension]
    end

    MM --> DBCONF
    MM --> OCI
    OCI --> ORA[(Oracle 11g+\nWE8MSWIN1252)]

    subgraph Modelos["Modelos de dominio"]
        AM[adminModel]
        RM[reportModel]
        CM[competenciaModel]
        FM[feedbackModel]
        AcM[acuerdoModel]
    end

    AM & RM & CM & FM & AcM --> MM
```

---

## 10. Flujo del Proceso de Evaluación Completo (Vista Funcional)

```mermaid
flowchart TD
    START([Inicio del Período]) --> ADMIN_ACTIVA[Admin activa período\nen panel Admin → Períodos]
    ADMIN_ACTIVA --> NOTIF[Admin envía notificación masiva\nPKT_NOTIFICACION_MAIL]
    NOTIF --> COLAB_AUTO[Colaborador realiza\nAutoevaluación\nTIPO=AUTO]
    COLAB_AUTO --> LIDER_EVAL[Líder evalúa a\ncada colaborador\nTIPO=LIDER_A_COLAB]
    COLAB_AUTO --> COLAB_LIDER[Colaborador evalúa\na su líder\nTIPO=COLAB_A_LIDER]

    LIDER_EVAL --> EA_CHECK{¿Colaborador\nes asistencial?}
    EA_CHECK -->|Sí| EA_COLAB[Líder completa\nExperiencia Azul\nTIPO=EXPERIENCIA_COLAB]
    EA_CHECK -->|No| ESTADO_OK

    COLAB_LIDER --> EA_CHECK2{¿Colaborador\nes asistencial?}
    EA_CHECK2 -->|Sí| EA_LIDER[Colaborador completa\nExperiencia Azul\nTIPO=EXPERIENCIA_LIDER]
    EA_CHECK2 -->|No| ESTADO_OK

    EA_COLAB --> ESTADO_OK
    EA_LIDER --> ESTADO_OK

    ESTADO_OK{¿Todo completado?}
    ESTADO_OK -->|No| NOTIF2[Recordatorio\nindividual o masivo]
    NOTIF2 --> COLAB_AUTO

    ESTADO_OK -->|Sí| FEEDBACK[Líder registra\nReunión de Feedback]
    FEEDBACK --> LIDER_FIRMA[Líder firma\nel feedback]
    LIDER_FIRMA --> COLAB_FIRMA[Colaborador firma\nel feedback]
    COLAB_FIRMA --> PLAN_ACTIVO[Plan de mejora\nqueda activo]
    PLAN_ACTIVO --> ACUERDOS[Líder asigna\nacuerdos de mejora]
    ACUERDOS --> PLAN_COLAB[Colaborador ingresa\nsu plan de acción]
    PLAN_COLAB --> APROBACION[Líder aprueba\nel plan]
    APROBACION --> CIERRE[Admin cierra\nel período]
    CIERRE --> EXPORT[Exportar reportes\nCSV / análisis]
    EXPORT --> END([Fin del Período])
```

---

## 11. Flujo de Notificaciones

```mermaid
sequenceDiagram
    participant A as Admin
    participant CTRL as adminController
    participant MOD as adminModel
    participant ORA as Oracle (PKT_NOTIFICACION_MAIL)
    participant EMAIL as Servidor de Correo

    A->>CTRL: POST action=enviarNotifMasiva
    CTRL->>MOD: getEstadoEquipoCompleto(periodo)
    MOD-->>CTRL: Lista de colaboradores con estado != 'completo'

    loop Para cada colaborador pendiente
        CTRL->>ORA: BEGIN PKT_NOTIFICACION_MAIL\n.SEND_MAIL_RECORDATORIO\n(:idColab, :idColab, 'COLABORADOR')
        ORA->>EMAIL: Envío correo al colaborador
        CTRL->>MOD: getLiderDeColaborador(idColab)
        MOD-->>CTRL: IDEMPLEADO del líder
        CTRL->>ORA: BEGIN PKT_NOTIFICACION_MAIL\n.SEND_MAIL_RECORDATORIO\n(:idLider, :idColab, 'LIDER')
        ORA->>EMAIL: Envío correo al líder
        CTRL->>MOD: actualizarUltimaNotif(idColab)
    end

    CTRL-->>A: Mensaje: "X recordatorios enviados"
```

---

## 12. Flujo de Exportación CSV

```mermaid
flowchart LR
    A([Admin hace clic\nen Exportar CSV]) -->|"GET ?action=exportar\n&tipo=seguimiento"| B[adminController\npanelAdmin]
    B --> C[while ob_get_level > 0\nob_end_clean — limpiar buffer]
    C --> D[getPeriodoActivo]
    D --> E{tipo=?}
    E -->|seguimiento| F[getEstadoEquipoCompleto\nFila por colaborador]
    E -->|avance| G[getAvancePorProceso\nFila por área]
    E -->|promedios_colab| H[getPromedioCompetenciasPorProceso\nP1–P11]
    E -->|promedios_lider| I[getPromedioLiderazgoPorProceso\nP12–P16]
    F & G & H & I --> J[header Content-Type: text/csv\nheader Content-Disposition: attachment]
    J --> K[echo BOM UTF-8\n0xEF 0xBB 0xBF]
    K --> L[fopen php://output\nfputcsv con delimitador ;]
    L --> M([Descarga del archivo\nen el navegador])
```

---

## 13. Flujo de Seguridad de Sesión

```mermaid
stateDiagram-v2
    [*] --> Sin_Sesion
    Sin_Sesion --> Iniciando: session_start()\ncookie httponly/secure/samesite=Lax
    Iniciando --> Activa: session_regenerate_id(true)\ncada 30 minutos
    Activa --> Activa: request normal\n_SESSION válida
    Activa --> Contrasena_Temp: CONTRASENA_TEMP=1\ndetectado en login
    Contrasena_Temp --> Activa: ActualizarCuentaController\nCONTRASENA_TEMP=0
    Activa --> Sin_Sesion: cerrarSesionControlador\nsession_destroy()
    Activa --> Sin_Sesion: Expiración cookie\n(1 hora)
    Sin_Sesion --> Login_Form: redirect /login/
```
