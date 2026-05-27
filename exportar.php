<?php
/**
 * exportar.php — Exportación CSV para el módulo Admin
 * Ubicar en: C:\xampp\htdocs\GestionHumana\exportar.php
 * Usa el mismo arranque que index.php para garantizar sesión y constantes.
 */

// Mismo orden exacto que index.php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/autoload.php';
require_once __DIR__ . '/app/views/inc/session_start.php';

$tipo = $_GET['tipo'] ?? '';

// Tipos exclusivos de admin
$tiposAdmin  = ['seguimiento', 'avance', 'promedios_colab', 'promedios_lider',
                'ranking_comp', 'acuerdos_detalle', 'alertas_admin', 'resumen_procesos',
                'analitica_colaboradores', 'analitica_brechas', 'analitica_exp_azul',
                'analitica_justificaciones', 'analitica_exp_azul_comp', 'analitica_exp_azul_eval'];
// Tipos disponibles para líderes (sin requerir esadmin)
$tiposLider  = ['equipo_lider'];
$todosValidos = array_merge($tiposAdmin, $tiposLider);

if (!in_array($tipo, $todosValidos)) {
    http_response_code(400);
    die('Tipo no válido.');
}

if (in_array($tipo, $tiposAdmin)) {
    // Solo admins
    if (!isset($_SESSION['esadmin']) || (int)$_SESSION['esadmin'] !== 1) {
        http_response_code(403);
        die('Acceso denegado.');
    }
} else {
    // Líderes: sesión válida + nivel de cargo NC002 en adelante
    $nivelesLider = ['NC002', 'NC003', 'NC004', 'NC005'];
    if (empty($_SESSION['idempleado']) || !in_array($_SESSION['nivelcargo'] ?? '', $nivelesLider)) {
        http_response_code(403);
        die('Acceso denegado.');
    }
}

require_once __DIR__ . '/app/models/mainModel.php';
require_once __DIR__ . '/app/models/adminModel.php';
require_once __DIR__ . '/app/models/analiticaModel.php';

$adminModelo    = new app\models\adminModel();
$analiticaModelo = new app\models\analiticaModel();
$periodoActivo = $adminModelo->getPeriodoActivo();
$nombrePeriodo = $periodoActivo
    ? preg_replace('/[^a-zA-Z0-9_-]/', '_', $periodoActivo['NOMBRE'])
    : 'sin_periodo';

// Limpiar buffer del autoload y enviar headers CSV
while (ob_get_level() > 0) ob_end_clean();

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $tipo . '_' . $nombrePeriodo . '_' . date('Ymd') . '.csv"');
header('Pragma: no-cache');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Expires: 0');
echo "\xEF\xBB\xBF";
$out = fopen('php://output', 'w');

// Seguimiento
if ($tipo === 'seguimiento') {
    $rows = $adminModelo->getEstadoEquipoCompleto($periodoActivo);
    fputcsv($out, [
        'Colaborador','Cargo','Área/Proceso','Autoevaluación',
        'Fue evaluado (recibidas)','Fue evaluado (esperadas)',
        'Evaluó a otros (realizadas)','Evaluó a otros (esperados)',
        'Estado','Última notificación'
    ], ';');
    foreach ($rows as $r) {
        $estado = match($r['ESTADO'] ?? '') {
            'completo'    => 'Completo',
            'en_progreso' => 'En progreso',
            default       => 'Sin iniciar'
        };
        fputcsv($out, [
            $r['EMPLEADO']           ?? '',
            $r['CARGO']              ?? '',
            $r['AREAFUNCIONAL']      ?? '',
            ($r['TIENE_AUTOEVAL'] ?? 0) ? 'Sí' : 'No',
            $r['EVAL_RECIBIDAS']     ?? 0,
            $r['EVAL_ESPERADAS']     ?? 0,
            $r['EVAL_REALIZADAS']    ?? 0,
            $r['TOTAL_SUBORDINADOS'] ?? 0,
            $estado,
            $r['ULTIMA_NOTIF']       ?? '',
        ], ';');
    }

// Avance
} elseif ($tipo === 'avance') {
    $rows = $adminModelo->getAvancePorProceso($periodoActivo);
    fputcsv($out, ['Área/Proceso','Total','Autoevaluaron','Completos','% Autoevaluación','% Completo'], ';');
    foreach ($rows as $r) {
        fputcsv($out, [
            $r['DEPENDENCIA']    ?? '',
            $r['TOTAL']          ?? 0,
            $r['CON_AUTOEVAL']   ?? 0,
            $r['COMPLETOS']      ?? 0,
            ($r['PCT_AUTOEVAL']  ?? 0) . '%',
            ($r['PCT_COMPLETO']  ?? 0) . '%',
        ], ';');
    }

// Promedios Colaborador
} elseif ($tipo === 'promedios_colab') {
    $rows = $adminModelo->getPromedioCompetenciasPorProceso($periodoActivo);
    fputcsv($out, ['Área/Proceso','Evaluados',
        'P1 - Calidez Humana','P2 - Integridad','P3 - Innovación',
        'P4 - Comunicación','P5 - Compromiso con la Calidad',
        'P6 - Disciplina','P7 - Participación y Formación',
        'P8 - Seguridad y Salud','P9 - Relaciones Interpersonales',
        'P10 - Eficacia','P11 - Gestión del Tiempo','Promedio General'], ';');
    foreach ($rows as $r) {
        $fila = [$r['DEPENDENCIA'] ?? '', $r['EVALUADOS'] ?? 0];
        $suma = 0; $cnt = 0;
        for ($i = 1; $i <= 11; $i++) {
            $v = (float)($r["PROM_P$i"] ?? 0);
            $fila[] = $v > 0 ? number_format($v, 2, '.', '') : '-';
            if ($v > 0) { $suma += $v; $cnt++; }
        }
        $fila[] = $cnt > 0 ? number_format($suma / $cnt, 2, '.', '') : '-';
        fputcsv($out, $fila, ';');
    }

// Promedios Líder
} elseif ($tipo === 'promedios_lider') {
    $rows = $adminModelo->getPromedioLiderazgoPorProceso($periodoActivo);
    $dictLider = [12=>'Propósito',13=>'Colaboración',14=>'Consistencia',15=>'Adaptabilidad',16=>'Amor'];
    $headers = ['Área/Proceso','Evaluados'];
    foreach ($dictLider as $np => $nombre) $headers[] = "P$np - $nombre";
    $headers[] = 'Promedio General';
    fputcsv($out, $headers, ';');
    foreach ($rows as $r) {
        $fila = [$r['DEPENDENCIA'] ?? '', $r['EVALUADOS'] ?? 0];
        $suma = 0; $cnt = 0;
        for ($i = 12; $i <= 16; $i++) {
            $v = (float)($r["PROM_P$i"] ?? 0);
            $fila[] = $v > 0 ? number_format($v, 2, '.', '') : '-';
            if ($v > 0) { $suma += $v; $cnt++; }
        }
        $fila[] = $cnt > 0 ? number_format($suma / $cnt, 2, '.', '') : '-';
        fputcsv($out, $fila, ';');
    }

// Ranking de competencias
} elseif ($tipo === 'ranking_comp') {
    $rows = $analiticaModelo->getRankingParaExport($periodoActivo);
    fputcsv($out, ['#','Competencia','Dimensión','Promedio','Mínimo','Máximo',
                   'Evaluados','% Bajo (≤2)','% Excelente (5)'], ';');
    foreach ($rows as $r) {
        fputcsv($out, [
            $r['NUM_PREGUNTA']   ?? '',
            $r['NOMBRE_COMP']    ?? '',
            $r['DIMENSION']      ?? '',
            number_format((float)($r['PROMEDIO']     ?? 0), 2, '.', ''),
            $r['MINIMO']         ?? '',
            $r['MAXIMO']         ?? '',
            $r['TOTAL_EVALUADOS']?? 0,
            number_format((float)($r['PCT_BAJO']     ?? 0), 1, '.', '') . '%',
            number_format((float)($r['PCT_EXCELENTE']?? 0), 1, '.', '') . '%',
        ], ';');
    }

// Acuerdos — trazabilidad completa
} elseif ($tipo === 'acuerdos_detalle') {
    $rows = $analiticaModelo->getAcuerdosParaExport($periodoActivo);
    fputcsv($out, ['Período','Colaborador','Cargo','Proceso','Líder',
                   'Competencia #','Objetivo','Plan de Acción',
                   'Estado','Fecha Asignación','Fecha Respuesta','Fecha Aprobación','Días Respuesta'], ';');
    foreach ($rows as $r) {
        fputcsv($out, [
            $r['NOMBRE_PERIODO'] ?? '',
            $r['COLABORADOR']    ?? '',
            $r['CARGO']          ?? '',
            $r['PROCESO']        ?? '',
            $r['LIDER']          ?? '',
            $r['NUM_COMPETENCIA']?? '',
            $r['OBJETIVO']       ?? '',
            $r['PLAN_ACCION']    ?? '',
            $r['ESTADO']         ?? '',
            $r['FECHA_ASIG']     ?? '',
            $r['FECHA_RESP']     ?? '',
            $r['FECHA_APRO']     ?? '',
            $r['DIAS_RESP']      ?? '',
        ], ';');
    }

// Alertas administrativas
} elseif ($tipo === 'alertas_admin') {
    $rows = $analiticaModelo->getAlertasParaExport($periodoActivo);
    fputcsv($out, ['Tipo Alerta','Colaborador / Líder','Cargo','Proceso','Líder','Días Pendiente'], ';');
    foreach ($rows as $r) {
        fputcsv($out, [
            $r['TIPO']             ?? '',
            $r['COLABORADOR']      ?? $r['LIDER'] ?? '',
            $r['CARGO']            ?? '',
            $r['PROCESO']          ?? '',
            $r['LIDER']            ?? '',
            $r['DIAS_PENDIENTE']   ?? $r['PENDIENTES_EVALUAR'] ?? '',
        ], ';');
    }

// Resumen por procesos
} elseif ($tipo === 'resumen_procesos') {
    $rows = $analiticaModelo->getProcesosParaExport($periodoActivo);
    fputcsv($out, ['Proceso','Total','Con Autoeval','Evaluados por Líder',
                   '% Autoeval','% Evaluados','Acuerdos Pendientes','Acuerdos Aprobados'], ';');
    foreach ($rows as $r) {
        fputcsv($out, [
            $r['PROCESO']        ?? '',
            $r['TOTAL']          ?? 0,
            $r['CON_AUTOEVAL']   ?? 0,
            $r['EVALUADOS']      ?? 0,
            ($r['PCT_AUTOEVAL']  ?? 0) . '%',
            ($r['PCT_EVALUADOS'] ?? 0) . '%',
            $r['ACUERDOS_PEND']  ?? 0,
            $r['ACUERDOS_APRO']  ?? 0,
        ], ';');
    }

// Colaboradores — listado completo analítica V2
} elseif ($tipo === 'analitica_colaboradores') {
    $rows = $analiticaModelo->getColaboradoresParaExport($periodoActivo);
    fputcsv($out, ['Colaborador','Cédula','Cargo','Proceso','Líder',
                   'Autoevaluación','Fue evaluado','Evaluó al líder',
                   'Estado','Prom. Líder','Prom. Auto',
                   'Total Acuerdos','Acuerdos Pend.','Acuerdos Apro.'], ';');
    foreach ($rows as $r) {
        fputcsv($out, [
            $r['NOMBRE_COMPLETO']   ?? $r['NOMBRE'] ?? '',
            $r['IDENTIFICACION']    ?? '',
            $r['CARGO']             ?? '',
            $r['PROCESO']           ?? '',
            $r['NOMBRE_LIDER']      ?? '',
            $r['TIENE_AUTO']        ?? 'No',
            $r['TIENE_LIDER']       ?? 'No',
            $r['EVALUO_LIDER']      ?? 'No',
            $r['ESTADO']            ?? '',
            (float)($r['PROM_LIDER'] ?? 0) > 0 ? number_format((float)$r['PROM_LIDER'], 2, '.', '') : '',
            (float)($r['PROM_AUTO']  ?? 0) > 0 ? number_format((float)$r['PROM_AUTO'],  2, '.', '') : '',
            $r['TOTAL_ACUERDOS']    ?? 0,
            $r['ACUERDOS_PEND']     ?? 0,
            $r['ACUERDOS_APRO']     ?? 0,
        ], ';');
    }

// Brechas AUTO vs LIDER
} elseif ($tipo === 'analitica_brechas') {
    $rows = $analiticaModelo->getBrechasParaExport($periodoActivo);
    fputcsv($out, ['#','Competencia','Dimensión','Prom. Líder','Prom. Auto','Brecha','N Líder','N Auto'], ';');
    foreach ($rows as $r) {
        $brecha = (float)($r['BRECHA'] ?? 0);
        fputcsv($out, [
            $r['NUM_PREGUNTA'] ?? '',
            $r['NOMBRE_COMP']  ?? '',
            $r['DIMENSION']    ?? '',
            number_format((float)($r['PROM_LIDER'] ?? 0), 2, '.', ''),
            number_format((float)($r['PROM_AUTO']  ?? 0), 2, '.', ''),
            ($brecha > 0 ? '+' : '') . number_format($brecha, 2, '.', ''),
            $r['N_LIDER']      ?? 0,
            $r['N_AUTO']       ?? 0,
        ], ';');
    }

// Justificaciones del líder por colaborador
} elseif ($tipo === 'analitica_justificaciones') {
    $rows = $analiticaModelo->getJustificacionesParaExport($periodoActivo);
    fputcsv($out, ['Colaborador','Cargo','Proceso','Líder','P#','Competencia','Calificación','Justificación'], ';');
    foreach ($rows as $r) {
        fputcsv($out, [
            $r['COLABORADOR']    ?? '',
            $r['CARGO']          ?? '',
            $r['PROCESO']        ?? '',
            $r['LIDER']          ?? '',
            $r['NUM_PREGUNTA']   ?? '',
            $r['NOMBRE_COMP']    ?? '',
            $r['VALOR']          ?? '',
            $r['JUSTIFICACION']  ?? '',
        ], ';');
    }

// Experiencia Azul — pendientes
} elseif ($tipo === 'analitica_exp_azul') {
    $rows = $analiticaModelo->getExpAzulParaExport($periodoActivo);
    fputcsv($out, ['Colaborador','Cargo','Proceso','Líder','Hizo Colab→Líder','Hizo Líder→Colab'], ';');
    foreach ($rows as $r) {
        fputcsv($out, [
            $r['COLABORADOR']          ?? '',
            $r['CARGO']                ?? '',
            $r['PROCESO']              ?? '',
            $r['LIDER']                ?? '',
            (int)($r['HIZO_COLAB'] ?? 0) ? 'Si' : 'No',
            (int)($r['HIZO_LIDER'] ?? 0) ? 'Si' : 'No',
        ], ';');
    }

// Exp. Azul — Promedios por competencia
} elseif ($tipo === 'analitica_exp_azul_comp') {
    $rows = $analiticaModelo->getExpAzulCompParaExport($periodoActivo);
    fputcsv($out, ['#','Competencia','Tipo','Promedio','Evaluados'], ';');
    foreach ($rows as $r) {
        fputcsv($out, [
            $r['NUM_PREGUNTA'] ?? '',
            $r['NOMBRE_COMP']  ?? '',
            $r['TIPO_EVAL'] === 'EXPERIENCIA_COLAB' ? 'Colab → Líder' : 'Líder → Colab',
            number_format((float)($r['PROMEDIO'] ?? 0), 2, '.', ''),
            $r['EVALUADOS']    ?? 0,
        ], ';');
    }

// Exp. Azul — Calificaciones individuales evaluados
} elseif ($tipo === 'analitica_exp_azul_eval') {
    $rows = $analiticaModelo->getExpAzulEvaluadosParaExport($periodoActivo);
    fputcsv($out, ['Colaborador','Cargo','Proceso','Líder','Colab→Líder','Líder→Colab','Prom. Colab','Prom. Líder'], ';');
    foreach ($rows as $r) {
        fputcsv($out, [
            $r['COLABORADOR']                ?? '',
            $r['CARGO']                      ?? '',
            $r['PROCESO']                    ?? '',
            $r['LIDER']                      ?? '',
            (int)($r['HIZO_COLAB'] ?? 0) ? 'Completó' : 'Pendiente',
            (int)($r['HIZO_LIDER'] ?? 0) ? 'Completó' : 'Pendiente',
            (float)($r['PROM_COLAB'] ?? 0) > 0 ? number_format((float)$r['PROM_COLAB'], 2, '.', '') : '',
            (float)($r['PROM_LIDER'] ?? 0) > 0 ? number_format((float)$r['PROM_LIDER'], 2, '.', '') : '',
        ], ';');
    }

// Equipo del líder
} elseif ($tipo === 'equipo_lider') {
    $idLider    = (int)($_SESSION['idempleado'] ?? 0);
    $nivelCargo = $_SESSION['nivelcargo'] ?? '';
    $idPeriodo  = $periodoActivo ? (int)$periodoActivo['IDPERIODO'] : 0;

    $reportModelo  = new app\models\reportModel();
    $acuerdoModelo = new app\models\acuerdoModel();

    $equipo    = $reportModelo->getEquipoACargo($idLider, $nivelCargo, $periodoActivo);
    $idsEquipo = array_map(fn($r) => (int)$r['IDEMPLEADO'], $equipo);

    $estadoPlan = ($idPeriodo && !empty($idsEquipo))
        ? $acuerdoModelo->getEstadoPlanEquipo($idPeriodo, $idsEquipo, $idLider)
        : [];

    fputcsv($out, [
        'Colaborador', 'Cargo', 'Proceso',
        'Autoevaluación', 'Fue evaluado por líder', 'Estado evaluación',
        'Compromisos asignados', 'Compromisos aprobados', 'Estado compromisos'
    ], ';');

    foreach ($equipo as $col) {
        $idEmp = (int)$col['IDEMPLEADO'];
        $plan  = $estadoPlan[$idEmp] ?? null;

        $estadoEval = match($col['ESTADO'] ?? '') {
            'completo'    => 'Completo',
            'en_progreso' => 'En progreso',
            default       => 'Sin iniciar'
        };

        $totalComp = $plan ? (int)$plan['total']     : 0;
        $aprobComp = $plan ? (int)$plan['aprobados']  : 0;

        if (!(int)($col['TIENE_EVAL_COLAB'] ?? 0)) {
            $estadoComp = 'Sin evaluación';
        } elseif ($totalComp === 0) {
            $estadoComp = 'Pendiente asignar';
        } elseif ($aprobComp === $totalComp && $totalComp >= 3) {
            $estadoComp = 'Completado';
        } else {
            $estadoComp = $aprobComp . '/' . $totalComp . ' En curso';
        }

        fputcsv($out, [
            $col['EMPLEADO']    ?? '',
            $col['CARGO']       ?? '',
            $col['DEPENDENCIA'] ?? '',
            (int)($col['TIENE_AUTOEVAL']   ?? 0) ? 'Sí' : 'No',
            (int)($col['TIENE_EVAL_COLAB'] ?? 0) ? 'Sí' : 'No',
            $estadoEval,
            $totalComp,
            $aprobComp,
            $estadoComp,
        ], ';');
    }
}

fclose($out);
exit();
