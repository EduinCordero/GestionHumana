# MANUAL TÉCNICO — Sistema GestionHumana
> **Clínica Zayma SAS** · Módulo de Evaluación de Desempeño V2  
> Versión: 1.0 · Fecha: 2026-05-14

---

## 1. Requisitos del Entorno

| Componente | Versión mínima | Notas |
|------------|---------------|-------|
| PHP | 8.0+ | Requiere extensión `oci8` |
| Oracle Client | 11.2+ | `NLS_LANG=SPANISH_SPAIN.WE8MSWIN1252` |
| Oracle DB | 11g+ | Schemas `VAADINWEB` y `ZAYMAWEB` |
| Servidor web | Apache 2.4+ / XAMPP | `mod_rewrite` no requerido (query string) |
| Sistema operativo | Windows / Linux | XAMPP en Windows para desarrollo |

---

## 2. Estructura del Proyecto

```
GestionHumana/
│
├── index.php                  Front controller — único punto de entrada HTTP
├── autoload.php               Registro PSR-0 manual con spl_autoload_register
├── icons.php                  Función global icon($name, $size) — SVG inline
├── exportar.php               Script de exportación CSV standalone (legacy)
├── activarCuenta.php          Formulario/endpoint de autoactivación de cuenta
│
├── config/
│   ├── app.php                Constantes APP_URL, APP_NAME, APP_SESSION_NAME, timezone
│   ├── pdoconfig.php          ⚠ Credenciales Oracle (DB_HOST, DB_USER, DB_PASSWORD)
│   └── encoding.php           Funciones toOracleEncoding() / fromOracleEncoding()
│
├── app/
│   ├── controllers/
│   │   ├── loginController.php    Login, logout, activar cuenta, cambiar contraseña
│   │   ├── viewsController.php    Enrutador de vistas (delega a viewsModel)
│   │   ├── evaluarController.php  Motor de evaluaciones, guardar respuestas, confirmar
│   │   ├── reportController.php   Reportes, seguimiento, acuerdos, planes de acción
│   │   ├── adminController.php    Panel admin: períodos, usuarios, competencias, exportar
│   │   └── feedbackController.php Feedback líder→colaborador, firma, SMART
│   │
│   ├── models/
│   │   ├── mainModel.php          Clase base: OCI8, helpers encoding, limpiarCadena
│   │   ├── viewsModel.php         Lista blanca de vistas autorizadas
│   │   ├── adminModel.php         CRUD períodos, usuarios, competencias, objetivos, equipo
│   │   ├── reportModel.php        Consultas de autoevaluación, recibidas, realizadas, promedios
│   │   ├── competenciaModel.php   Escalas, opciones, respuestas, confirmaciones (caché estático)
│   │   ├── feedbackModel.php      Registro feedback, firma colaborador/lider, acuerdos lider
│   │   ├── acuerdoModel.php       Acuerdos de mejora, plan de acción, aprobación
│   │   └── EncodingModel.php      Helpers encoding adicionales (utilitario)
│   │
│   ├── views/
│   │   ├── inc/
│   │   │   ├── session_start.php  Configura y arranca sesión PHP (flags seguros)
│   │   │   ├── head.php           <head> con CDN de Tailwind, Tabler Icons, Preline
│   │   │   ├── header.php         Sidebar lateral colapsable + notificaciones
│   │   │   ├── footer.php         Scripts de cierre de página
│   │   │   └── nav.php            (legacy — reemplazado por header.php)
│   │   ├── content/               Vistas por módulo (*-view.php)
│   │   ├── css/
│   │   │   ├── styles.min.css     Tailwind CSS compilado
│   │   │   ├── style.css          Estilos personalizados
│   │   │   ├── theme.css          Variables de tema (Blue_Theme)
│   │   │   └── sweetalert2.min.css SweetAlert2
│   │   └── js/
│   │       ├── ajax.js            Helpers AJAX comunes
│   │       ├── tabs.js            Lógica de pestañas
│   │       ├── theme.js           Toggle dark/light mode
│   │       ├── preline.js         Preline UI components
│   │       └── app.min.js / vendor.min.js Bundles base
│   │
│   └── ajax/
│       ├── formulariosAjax.php    Endpoints AJAX legacy
│       └── formAjax.php           Endpoints AJAX adicionales legacy
```

---

## 3. Configuración del Sistema

### 3.1 `config/app.php`

```php
define("APP_URL",          "http://HOST/GestionHumana/");  // Auto-detectado
define("APP_NAME",         "GestionHumana");
define("APP_SESSION_NAME", "session");
date_default_timezone_set("America/Bogota");
```

`APP_URL` se construye dinámicamente a partir de `$_SERVER['HTTP_HOST']` y la constante `$project_folder = "/GestionHumana"`.

### 3.2 `config/pdoconfig.php`

```php
const DB_HOST     = '10.2.202.215';
const DB_PORT     = '1521';
const DB_NAME     = 'dinamica';    // SID Oracle
const DB_USER     = 'zaymaweb';
const DB_PASSWORD = 'zaymaweb';
```

> **⚠ Riesgo:** Credenciales en texto plano bajo control de versiones. Migrar a variables de entorno o archivo `.env` fuera del webroot.

### 3.3 `autoload.php`

```php
putenv('NLS_LANG=SPANISH_SPAIN.WE8MSWIN1252');  // Encoding Oracle
require_once __DIR__ . '/config/encoding.php';

spl_autoload_register(function($clase) {
    $ruta = __DIR__ . "/" . str_replace("\\", "/", $clase) . ".php";
    if (file_exists($ruta)) require_once $ruta;
});
```

El autoloader convierte el namespace `app\controllers\adminController` en la ruta `app/controllers/adminController.php`. No es PSR-4 estándar pero funciona para la estructura del proyecto.

---

## 4. Flujo MVC Detallado

### 4.1 Ciclo de vida de una petición normal

```
1. Navegador → GET http://host/GestionHumana/?views=reportes&tab=autoeval

2. index.php
   ├── require config/app.php, autoload.php, session_start.php
   ├── $url = explode("/", $_GET['views'])  → ["reportes"]
   ├── ¿Es AJAX de los módulos interceptados? → No
   ├── HTML: <head> → inc/head.php
   ├── Sesión válida? → Sí → inc/header.php (sidebar)
   └── $url[0] === 'reportes' → new reportController() → obtenerMisEvaluaciones()

3. reportController::obtenerMisEvaluaciones()
   ├── Verifica $_SESSION['idempleado']
   ├── Lee $activeTab, $searchTerm
   ├── Ejecuta acciones POST si las hay
   ├── Instancia reportModel, acuerdoModel
   ├── Construye array $data con todos los datos
   └── renderView($data) → extract($data) → require reportes-view.php

4. reportes-view.php
   └── HTML con variables $data disponibles via extract()
```

### 4.2 Ciclo de vida de una petición AJAX

```
1. Navegador → GET ?views=reportes&action=getCompetenciasMejora&idEmpleado=123
               (Header: X-Requested-With: XMLHttpRequest)

2. index.php — interceptor AJAX (líneas 77-85)
   ├── Detecta $url[0]==='reportes' y acción en whitelist
   ├── new reportController()->obtenerMisEvaluaciones()
   └── exit()  ← No se renderiza HTML

3. reportController::obtenerMisEvaluaciones()
   ├── Detecta $isAjax=true y $_GET['action']
   ├── Ejecuta consulta Oracle
   ├── header('Content-Type: application/json')
   ├── echo json_encode($resultado)
   └── exit()
```

---

## 5. Capa de Modelos

### 5.1 `mainModel` — Clase Base

Todos los modelos y controladores heredan de `mainModel`. Provee:

| Método | Descripción |
|--------|-------------|
| `conectar()` | Abre conexión OCI8 con cadena TNS explícita |
| `ejecutarConsulta($sql)` | Parse + execute, devuelve statement handle |
| `ejecutarConsultaUTF8($sql)` | Ejecuta y convierte todo el resultado a UTF-8 |
| `ejecutarConsultaUnicaUTF8($sql)` | Idem pero solo primer registro |
| `toOracle(string $s)` | UTF-8 → Windows-1252 (para bind) |
| `fromOracle(string $s)` | Windows-1252 → UTF-8 (para mostrar) |
| `limpiarCadena($s)` | Sanitización básica por lista negra de palabras |
| `verificarDatos($filtro, $s)` | Validación por regex |

> **⚠ Limitación de `limpiarCadena()`:** Elimina la cadena `:` de los inputs, lo cual puede romper textos legítimos que contengan dos puntos. No es sustituto de consultas parametrizadas.

### 5.2 `competenciaModel` — Motor de Competencias

Modelo central del sistema de evaluación V2. Implementa caché estático por proceso (`static array $cache*`):

| Método | Tabla | Descripción |
|--------|-------|-------------|
| `getEscalas()` | `HUMESCALA` | Catálogo de escalas activas |
| `getOpcionesPorEscala($id)` | `HUMOPCIONESCALA` | Opciones de calificación |
| `getCompetencias()` | `HUMCOMPETENCIA` | Catálogo de competencias |
| `getCompetenciasPorRolTipo($idRol, $tipo)` | `HUMROLCOMPETENCIA` | Competencias según rol y tipo de eval |
| `guardarRespuesta(...)` | `HUMRESPUESTA` | INSERT/UPDATE de respuesta |
| `confirmarEvaluacion(...)` | `HUMRESPUESTA` | Marca evaluación como confirmada |
| `estaConfirmada(...)` | `HUMRESPUESTA` | Verifica si ya fue confirmada |
| `guardarJustificacion($idResp, $texto)` | `HUMRESPUESTA` | Guarda texto de justificación |
| `getRolPorNivelCargo($nivel)` | Lookup | NC001→1, NC002→3, etc. |

### 5.3 Otros modelos

| Modelo | Responsabilidad principal |
|--------|--------------------------|
| `adminModel` | CRUD períodos, gestión equipo admin, búsqueda usuarios, promedios CSV |
| `reportModel` | Autoevaluación, evaluaciones recibidas/realizadas, promedios, seguimiento equipo |
| `feedbackModel` | Registro de reuniones feedback, firma bipartita, acuerdos liderazgo |
| `acuerdoModel` | Objetivos de mejora, asignación de acuerdos, planes de acción, aprobación |
| `viewsModel` | Solo whitelist de rutas — sin acceso a BD |

---

## 6. Sesiones y Seguridad

### 6.1 Variables de sesión

| Variable | Tipo | Descripción |
|----------|------|-------------|
| `$_SESSION['id']` | int | `IDUSUARIO` de `HUMUSUARIOS` |
| `$_SESSION['identificacion']` | string | Número de cédula |
| `$_SESSION['nombres']` | string | Primer nombre (UTF-8) |
| `$_SESSION['apellidos']` | string | Primer apellido (UTF-8) |
| `$_SESSION['idempleado']` | int | `IDEMPLEADO` de `GHEMPEMPLEADOS` |
| `$_SESSION['sexo']` | string | `MASCULINO` / `FEMENINO` |
| `$_SESSION['nivelcargo']` | string | `NC001`..`NC005` |
| `$_SESSION['rol']` | string | Rol textual |
| `$_SESSION['esadmin']` | int | 1 si es administrador |
| `$_SESSION['ver_detalle_rep']` | int | 1 si puede ver detalle de reportes |
| `$_SESSION['ea_idempleado_evaluado']` | int | Temp: empleado para Exp. Azul |
| `$_SESSION['ea_pendiente']` | string | Tipo Exp. Azul pendiente |
| `$_SESSION['admin_ok']` | string | Mensaje de éxito para flash message |
| `$_SESSION['admin_error']` | string | Mensaje de error para flash message |

### 6.2 Configuración de sesión (`session_start.php`)

```php
session_set_cookie_params([
    'lifetime' => 3600,        // 1 hora
    'path'     => '/',
    'secure'   => isset($_SERVER['HTTPS']),
    'httponly' => true,        // No accesible via JS
    'samesite' => 'Lax'        // Protección CSRF parcial
]);
session_name('session');
session_regenerate_id(true);  // Cada 30 minutos
```

### 6.3 Control de acceso

- **Rutas protegidas:** `index.php` verifica `$_SESSION['id']` y `$_SESSION['identificacion']` antes de mostrar cualquier módulo. Si no hay sesión, redirige a `/login/`.
- **Panel Admin:** `adminController` verifica `$_SESSION['esadmin'] == 1`. Si no, redirige a `/home/`.
- **Feedback:** `feedbackController` verifica que el usuario sea líder funcional (`ES_LIDER_FUNCIONAL=1`) o admin.
- **Contraseñas:** Almacenadas con `password_hash(..., PASSWORD_BCRYPT)`. Verificadas con `password_verify()`.
- **Contraseña temporal:** Campo `CONTRASENA_TEMP=1` en `HUMUSUARIOS`. Al detectarlo en login, redirige a `/seguridad/` para cambio obligatorio.

### 6.4 Activación de cuenta

Reglas de negocio para `activarCuentaController()`:
1. Empleado debe existir en `GHEMPEMPLEADOS` con `ESTADOEMPLEADO=1`.
2. Debe tener más de **90 días** de antigüedad (`TRUNC(SYSDATE - FECHAINGRESO) > 90`).
3. No debe existir ya en `HUMUSUARIOS`.
4. Rol asignado automáticamente: `'USER'`.

---

## 7. Módulos — Descripción Técnica

### 7.1 Login (`loginController`)

| Acción | Método | Descripción |
|--------|--------|-------------|
| Mostrar formulario | Vista `login-view.php` | GET |
| Autenticar | `iniciarSesionController()` | POST con identificación + password |
| Cerrar sesión | `cerrarSesionControlador()` | GET `?views=logout` |
| Activar cuenta | `activarCuentaController()` | POST desde `activarCuenta.php` |
| Cambiar contraseña | `ActualizarCuentaController()` | POST desde `seguridad-view.php` |
| Reset admin | `resetearPasswordController()` | POST AJAX desde admin |
| AJAX email enmascarado | En `index.php` | POST `?views=login&action=getCorreo` |

### 7.2 Evaluaciones (`evaluarController`)

Orquesta el flujo completo de evaluación:

```
evaluarList-view.php
  └── listarColaboradoresEvaluar()   ← genera tabla HTML dinámica
        ├── getPersonasAEvaluar()    ← subordinados del líder
        ├── miJefe (query directa)   ← jefe del colaborador
        └── estaConfirmada()         ← estado por evaluado/tipo

Formularios de evaluación:
  autoevaluacion-view.php       → tipoEval = 'AUTO'
  subEvaluacion-view.php        → tipoEval = 'LIDER_A_COLAB'
  evaluacionLiderazgo-view.php  → tipoEval = 'COLAB_A_LIDER'
  experienciaAzulColab-view.php → tipoEval = 'EXPERIENCIA_COLAB'
  experienciaAzulLider-view.php → tipoEval = 'EXPERIENCIA_LIDER'
  
  POST ?views=registrarEvaluacion
    └── registrarEvaluacion()
          ├── getCompetenciasPorRolTipo($idRol, $tipo)
          ├── guardarRespuesta() × N competencias
          ├── confirmarEvaluacion()
          ├── guardarJustificacion() (opcional)
          └── redirect → evaluarList
```

### 7.3 Reportes (`reportController`)

| Pestaña | Datos cargados |
|---------|---------------|
| `autoeval` | `getAutoEvaluacion()` — respuestas propias del período |
| `recibidas` | `getEvaluacionesRecibidasColaborador()` + `getEvaluacionesRecibidasLiderazgo()` |
| `realizadas` | `getEvaluacionesRealizadasColaborador()` + `getEvaluacionesRealizadasLiderazgo()` |
| `equipo` | `getEquipoACargo()` — solo líderes NC002+ |
| `plan` | `getAcuerdosColaborador()` — acuerdos de mejora del propio empleado |

Condición de acceso al panel de equipo: `$_SESSION['nivelcargo']` en `['NC002','NC003','NC004','NC005']`.

### 7.4 Admin (`adminController`)

Pestañas del panel administrador:

| Pestaña `?tab=` | Funcionalidad |
|-----------------|---------------|
| `periodos` | Crear, activar, cerrar, editar fecha cierre de períodos |
| `seguimiento` | Estado de evaluación de todo el equipo, notificaciones individuales y masivas |
| `avance` | Porcentaje de avance por área/proceso |
| `promedios` | Promedios de competencias colaborador y liderazgo por área |
| `usuarios` | Búsqueda de usuarios, actualización de permisos (esadmin, ver_detalle) |
| `objetivos` | CRUD objetivos de mejora por competencia y calificación |
| `competencias` | Edición de nombres, preguntas y opciones de competencias |
| `colaboradores` | Alta, edición, activación/desactivación de empleados en evaluación |

**Exportación CSV:** `?action=exportar&tipo=[seguimiento|avance|promedios_colab|promedios_lider]`  
Usa `fputcsv()` con delimitador `;` y BOM UTF-8 para compatibilidad con Excel.

### 7.5 Feedback (`feedbackController`)

Flujo del módulo de feedback:

```
1. Líder registra reunión de feedback (Proceso 1)
   → INSERT HUMFEEDBACK (TIPO_FEEDBACK=1, FIRMADO_COLAB=0, FIRMADO_LIDER=0)

2. Líder firma → UPDATE HUMFEEDBACK SET FIRMADO_LIDER=1

3. Colaborador firma → UPDATE HUMFEEDBACK SET FIRMADO_COLAB=1
   → Activa plan de mejora en reportes

4. Director hace feedback a líder (Proceso 2, competencias P12-P16)
   → Solo si ES_DIRECTOR=1 en HUMEMPLEADOEVAL
```

---

## 8. Consultas SQL Clave

### 8.1 Login

```sql
SELECT EM.IDEMPLEADO, HUM.IDUSUARIO, HUM.IDENTIFICACION, HUM.ROL,
       PNOMBRE AS NOMBRES, PAPELLIDO AS APELLIDOS,
       CA.CODNIVELCARGO, HUM.PASSWORD, HUM.ESADMIN,
       HUM.VER_DETALLE_REP,
       NVL(HUM.CONTRASENA_TEMP, 0) AS CONTRASENA_TEMP
FROM VAADINWEB.HUMUSUARIOS HUM
INNER JOIN ZAYMAWEB.GHEMPEMPLEADOS EM ON TRIM(HUM.IDENTIFICACION) = TRIM(EM.IDENTIFICACION)
INNER JOIN ZAYMAWEB.GHEMPUBICACION UB ON EM.IDEMPLEADO = UB.IDEMPLEADO AND UB.TIPO = 'ACTUAL'
INNER JOIN ZAYMAWEB.GHEMPCARGOS CA ON UB.IDEMPCARGO = CA.IDEMPCARGO
WHERE ESTADOEMPLEADO = 1 AND HUM.CUENTA_ACTIVA = 1
  AND HUM.IDENTIFICACION = '$identificacion'
```

### 8.2 Período activo

```sql
SELECT IDPERIODO, NOMBRE, ESTADO,
       TO_CHAR(FECHAAPERTURA,'DD/MM/YYYY') AS FECHAAPERTURA,
       TO_CHAR(FECHACIERRE,  'DD/MM/YYYY') AS FECHACIERRE
FROM VAADINWEB.HUMPERIODOEVALUACION
WHERE ESTADO = 1 AND ROWNUM = 1
```

### 8.3 Jerarquía de evaluación — subordinados de un líder

```sql
SELECT HE.IDEMPLEADO,
       NVL(GE.PNOMBRE||' '||GE.PAPELLIDO, HE.NOMBRE) AS EMPLEADO,
       HE.CARGO, NVL(VC.CODNIVELCARGO,'NC001') AS CODNIVELCARGO
FROM VAADINWEB.HUMEMPLEADOEVAL HE
LEFT JOIN ZAYMAWEB.GHEMPEMPLEADOS GE ON HE.IDEMPLEADO = GE.IDEMPLEADO
LEFT JOIN ZAYMAWEB.VST_GHEMPEMPLEADOSCARGOS VC
    ON HE.IDEMPLEADO = VC.IDEMPLEADO AND VC.ESPRINCIPAL = 1
WHERE HE.IDEMPLEADO_EVAL = :idLider
  AND HE.ACTIVO = 1
  AND HE.IDROL IN (1, 2, 3)
ORDER BY EMPLEADO ASC
```

### 8.4 Verificar evaluación confirmada

```sql
SELECT COUNT(*) AS CNT FROM VAADINWEB.HUMRESPUESTA
WHERE IDPERIODO    = :idPeriodo
  AND IDEMPLEADO   = :idEvaluado
  AND IDEMPLEADO_EVAL = :idEvaluador
  AND TIPO_EVAL    = :tipo
  AND CONFIRMADO   = 1
```

### 8.5 Guardar respuesta

```sql
MERGE INTO VAADINWEB.HUMRESPUESTA ...
-- O INSERT si no existe, UPDATE si ya existe
-- Campos: IDPERIODO, IDEMPLEADO, IDEMPLEADO_EVAL, IDCOMPETENCIA,
--         IDOPCION, VALOR, TIPO_EVAL, CONFIRMADO, FECHARESPUESTA
```

### 8.6 Activar período (con transacción manual)

```sql
-- Paso 1: Desactivar todos los otros activos
UPDATE VAADINWEB.HUMPERIODOEVALUACION SET ESTADO = 0
WHERE ESTADO = 1 AND IDPERIODO != :id;

-- Paso 2: Activar el seleccionado
UPDATE VAADINWEB.HUMPERIODOEVALUACION SET ESTADO = 1
WHERE IDPERIODO = :id;
-- OCI_COMMIT / OCI_ROLLBACK manual
```

---

## 9. Manejo de Encoding

El sistema maneja una doble conversión por la discrepancia entre el encoding de Oracle y el del navegador:

```
┌─────────────────────────────────────────────────────────────────────┐
│ autoload.php: putenv('NLS_LANG=SPANISH_SPAIN.WE8MSWIN1252')         │
│ → Oracle envía datos en Windows-1252                                 │
│                                                                      │
│ Lectura:  oci_fetch_assoc() → fromOracleEncoding() → UTF-8 → HTML   │
│ Escritura: HTML form → toOracleEncoding() → oci_bind_by_name()       │
│                                                                      │
│ INCONSISTENCIA DETECTADA: competenciaModel usa 'ISO-8859-1'          │
│ en lugar de 'Windows-1252' en mb_convert_encoding(). Pueden          │
│ producirse caracteres incorrectos con tildes o ñ.                    │
└─────────────────────────────────────────────────────────────────────┘
```

---

## 10. Frontend — Stack de UI

| Librería | Versión | Uso |
|----------|---------|-----|
| Tailwind CSS | Compilado en `styles.min.css` | Utilidades de layout y estilo |
| Tabler Icons | CDN `ti-*` | Iconografía uniforme |
| Preline UI | `preline.js` | Componentes JS (tabs, dropdowns) |
| SweetAlert2 | `sweetalert2.all.min.js` | Alertas y confirmaciones modales |
| Simplebar | `simplebar.min.js` | Scrollbars personalizados |

El tema visual es `Blue_Theme` (azul corporativo `#0058af`). Soporta modo oscuro vía `data-color-theme`.

---

## 11. Riesgos Técnicos y Recomendaciones

### 11.1 Seguridad

| Riesgo | Recomendación |
|--------|---------------|
| Credenciales Oracle en texto plano | Mover a variables de entorno (`.env`) o archivo fuera del webroot |
| SQL de login con concatenación directa | Parametrizar con `oci_bind_by_name()` |
| `FILTER_SANITIZE_STRING` deprecado (PHP 8.1+) | Reemplazar con `htmlspecialchars()` o validación específica |
| Conexión Oracle sin cifrado TLS | Configurar TCP.VALIDNODE_CHECKING o SSL si la red no está aislada |

### 11.2 Rendimiento

| Riesgo | Recomendación |
|--------|---------------|
| header.php abre 5 conexiones Oracle por carga | Consolidar notificaciones en una sola query con UNION ALL |
| Sin pool de conexiones | Evaluar `oci_pconnect()` para reutilizar conexiones persistentes |
| caché estático solo en memoria por proceso | Agregar Redis/APCu para cachear escalas y competencias entre requests |

### 11.3 Refactorización sugerida

| Deuda técnica | Cambio sugerido |
|--------------|----------------|
| Controllers extienden mainModel | Usar composición: `private mainModel $db` |
| Lógica de negocio en vistas (home-view.php) | Mover queries al controller/model |
| Múltiples `new reportModel()` en un controller | Instanciar una sola vez en constructor |
| `eliminarRegistro()` con SQL concatenado | Parametrizar con bind |
| `exportar.php` standalone duplica lógica de adminController | Unificar en adminController |
