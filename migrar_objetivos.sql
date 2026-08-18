-- ============================================================
-- HUMOBJETIVOMEJORA — Actualización de objetivos SMART (P1–P22)
-- Ejecutar como el usuario propietario en SQL Developer.
-- IDs 88 y 89 (P16 Amor) también corrigen MODELO que estaba sin tilde.
-- ============================================================

SET DEFINE OFF;

-- -- Competencia 1: Calidez Humana y Servicio con Propósito -----------
UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET
  MODELO='Cierre de brecha',
  OBJETIVO='Durante los próximos 12 meses me comprometo a aplicar en al menos el 90% de mis interacciones con pacientes, familias o clientes internos un trato basado en saludo adecuado, escucha activa, lenguaje respetuoso y cierre claro.',
  INDICADOR='Porcentaje de interacciones observadas con el protocolo de trato esperado.',
  META='>= 90% de cumplimiento sostenido.',
  PLAZO='12 meses.',
  EVIDENCIA='Observación directa, retroalimentación registrada o encuesta interna.',
  SEGUIMIENTO='Cortes a 90, 180, 270 y 360 días.',
  USO_RECOMENDADO='Aceptable / Necesita mejorar / Requiere mejora / Insuficiente'
WHERE IDOBJETIVO=36;

UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET
  MODELO='Consolidación',
  OBJETIVO='Durante los próximos 12 meses me comprometo a mantener una experiencia de atención consistente, logrando una valoración favorable mínima de 4,5/5 o equivalente y corrigiendo en máximo 15 días cualquier observación asociada a trato o servicio.',
  INDICADOR='Calificación de servicio y tiempo de cierre de observaciones.',
  META='>= 4,5/5 y cierre de observaciones <= 15 días.',
  PLAZO='12 meses.',
  EVIDENCIA='Encuestas, PQR, felicitaciones o actas de seguimiento.',
  SEGUIMIENTO='Seguimiento trimestral.',
  USO_RECOMENDADO='Acorde'
WHERE IDOBJETIVO=38;

UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET
  MODELO='Expansión / referente',
  OBJETIVO='Durante los próximos 12 meses me comprometo a liderar o proponer al menos 2 acciones de humanización del servicio en mi área, documentando su implementación y socializando resultados o aprendizajes con el equipo.',
  INDICADOR='Número de acciones de humanización implementadas y socializadas.',
  META='>= 2 acciones ejecutadas.',
  PLAZO='12 meses.',
  EVIDENCIA='Plan de acción, evidencias de ejecución y socialización.',
  SEGUIMIENTO='Seguimiento semestral y cierre anual.',
  USO_RECOMENDADO='Sobresaliente'
WHERE IDOBJETIVO=39;

-- -- Competencia 2: Integridad ----------------------------------------
UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET
  MODELO='Cierre de brecha',
  OBJETIVO='Durante los próximos 12 meses me comprometo a fortalecer la coherencia entre lo que comunico y lo que ejecuto, cumpliendo al menos el 95% de los compromisos adquiridos con mi equipo o líder y reportando oportunamente cualquier desviación.',
  INDICADOR='Porcentaje de compromisos cumplidos en tiempo.',
  META='>= 95% de cumplimiento.',
  PLAZO='12 meses.',
  EVIDENCIA='Bitácora de compromisos, correos o actas.',
  SEGUIMIENTO='Seguimiento trimestral.',
  USO_RECOMENDADO='Aceptable / Necesita mejorar / Requiere mejora / Insuficiente'
WHERE IDOBJETIVO=40;

UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET
  MODELO='Consolidación',
  OBJETIVO='Durante los próximos 12 meses me comprometo a tomar decisiones alineadas con los valores y normas institucionales, dejando trazabilidad de las decisiones sensibles o relevantes y revisándolas mensualmente con mi líder.',
  INDICADOR='Decisiones relevantes documentadas y revisiones realizadas.',
  META='100% de decisiones sensibles con trazabilidad y 12 revisiones.',
  PLAZO='12 meses.',
  EVIDENCIA='Actas, correos, minutas o registros de revisión.',
  SEGUIMIENTO='Seguimiento mensual.',
  USO_RECOMENDADO='Acorde'
WHERE IDOBJETIVO=41;

UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET
  MODELO='Expansión / referente',
  OBJETIVO='Durante los próximos 12 meses me comprometo a ser referente de integridad, realizando al menos 4 espacios formales sobre ética, respeto, transparencia o conducta esperada, con acuerdos documentados.',
  INDICADOR='Número de espacios realizados y acuerdos documentados.',
  META='>= 4 espacios ejecutados.',
  PLAZO='12 meses.',
  EVIDENCIA='Actas, listas de asistencia y acuerdos.',
  SEGUIMIENTO='Seguimiento trimestral.',
  USO_RECOMENDADO='Sobresaliente'
WHERE IDOBJETIVO=42;

-- -- Competencia 3: Innovación ----------------------------------------
UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET
  MODELO='Cierre de brecha',
  OBJETIVO='Durante los próximos 12 meses me comprometo a adoptar en máximo 30 días los nuevos lineamientos, herramientas o ajustes de proceso que me sean asignados, demostrando su aplicación en el trabajo.',
  INDICADOR='Tiempo de adopción de cambios y evidencia de uso.',
  META='Adopción <= 30 días por cambio asignado.',
  PLAZO='12 meses.',
  EVIDENCIA='Registros de formación, evidencia de uso o validación del líder.',
  SEGUIMIENTO='Seguimiento trimestral.',
  USO_RECOMENDADO='Aceptable / Necesita mejorar / Requiere mejora / Insuficiente'
WHERE IDOBJETIVO=43;

UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET
  MODELO='Consolidación',
  OBJETIVO='Durante los próximos 12 meses me comprometo a presentar al menos 3 propuestas de mejora orientadas a eficiencia, servicio o calidad, de las cuales al menos 1 será implementada o piloteada.',
  INDICADOR='Propuestas presentadas e iniciativas implementadas o piloteadas.',
  META='>= 3 propuestas y >= 1 implementada o probada.',
  PLAZO='12 meses.',
  EVIDENCIA='Formatos de mejora, actas o resultados del piloto.',
  SEGUIMIENTO='Seguimiento semestral.',
  USO_RECOMENDADO='Acorde'
WHERE IDOBJETIVO=44;

UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET
  MODELO='Expansión / referente',
  OBJETIVO='Durante los próximos 12 meses me comprometo a liderar o coliderar al menos 1 iniciativa de innovación con impacto medible en tiempos, calidad, costos o experiencia, y a socializar sus resultados.',
  INDICADOR='Iniciativas lideradas y variación del indicador intervenido.',
  META='>= 1 iniciativa con impacto medible.',
  PLAZO='12 meses.',
  EVIDENCIA='Indicadores antes y después, actas y presentación de resultados.',
  SEGUIMIENTO='Seguimiento trimestral.',
  USO_RECOMENDADO='Sobresaliente'
WHERE IDOBJETIVO=45;

-- -- Competencia 4: Comunicación Asertiva -----------------------------
UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET
  MODELO='Cierre de brecha',
  OBJETIVO='Durante los próximos 12 meses me comprometo a comunicar información clave de forma clara, respetuosa y oportuna, respondiendo solicitudes internas en máximo 48 horas hábiles y confirmando comprensión cuando la información afecte la operación o el servicio.',
  INDICADOR='Tiempo de respuesta y porcentaje de comunicaciones críticas con confirmación.',
  META='Respuesta <= 48 horas hábiles y >= 90% con confirmación.',
  PLAZO='12 meses.',
  EVIDENCIA='Correos, tickets, minutas o seguimiento del líder.',
  SEGUIMIENTO='Seguimiento trimestral.',
  USO_RECOMENDADO='Aceptable / Necesita mejorar / Requiere mejora / Insuficiente'
WHERE IDOBJETIVO=46;

UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET
  MODELO='Consolidación',
  OBJETIVO='Durante los próximos 12 meses me comprometo a participar en al menos el 90% de los espacios institucionales obligatorios y aplicar los lineamientos comunicados en mi trabajo diario.',
  INDICADOR='Porcentaje de asistencia y evidencias de aplicación.',
  META='>= 90% de asistencia y aplicación evidenciada.',
  PLAZO='12 meses.',
  EVIDENCIA='Listas de asistencia, evaluaciones o validación del líder.',
  SEGUIMIENTO='Seguimiento semestral.',
  USO_RECOMENDADO='Acorde'
WHERE IDOBJETIVO=47;

UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET
  MODELO='Expansión / referente',
  OBJETIVO='Durante los próximos 12 meses me comprometo a realizar al menos 4 acciones de fortalecimiento de la cultura y el sentido de pertenencia, como socializar buenas prácticas, acompañar nuevos ingresos o apoyar campañas internas.',
  INDICADOR='Número de acciones de cultura ejecutadas.',
  META='>= 4 acciones realizadas.',
  PLAZO='12 meses.',
  EVIDENCIA='Correos, actas, registros o evidencias de participación.',
  SEGUIMIENTO='Seguimiento trimestral.',
  USO_RECOMENDADO='Sobresaliente'
WHERE IDOBJETIVO=48;

-- -- Competencia 5: Compromiso con la calidad ------------------------
UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET
  MODELO='Cierre de brecha',
  OBJETIVO='Durante los próximos 12 meses me comprometo a cumplir los procedimientos, estándares y registros aplicables a mi cargo, alcanzando al menos un 95% de adherencia en auditorías o seguimientos de calidad.',
  INDICADOR='Porcentaje de adherencia en auditorías o revisiones.',
  META='>= 95% de adherencia.',
  PLAZO='12 meses.',
  EVIDENCIA='Resultados de auditoría, listas de chequeo y planes de acción.',
  SEGUIMIENTO='Seguimiento trimestral.',
  USO_RECOMENDADO='Aceptable / Necesita mejorar / Requiere mejora / Insuficiente'
WHERE IDOBJETIVO=49;

UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET
  MODELO='Consolidación',
  OBJETIVO='Durante los próximos 12 meses me comprometo a reportar oportunamente el 100% de los incidentes, fallas, riesgos o no conformidades que identifique mediante los canales institucionales.',
  INDICADOR='Porcentaje de eventos detectados y reportados.',
  META='100% de eventos identificados reportados.',
  PLAZO='12 meses.',
  EVIDENCIA='Formatos de reporte, sistema institucional o bitácora.',
  SEGUIMIENTO='Seguimiento trimestral.',
  USO_RECOMENDADO='Acorde'
WHERE IDOBJETIVO=50;

UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET
  MODELO='Expansión / referente',
  OBJETIVO='Durante los próximos 12 meses me comprometo a proponer o participar en al menos 2 acciones de mejora relacionadas con estandarización, reducción de errores o experiencia del usuario.',
  INDICADOR='Número de acciones de mejora ejecutadas.',
  META='>= 2 acciones implementadas.',
  PLAZO='12 meses.',
  EVIDENCIA='Actas, planes de mejora y evidencias.',
  SEGUIMIENTO='Seguimiento semestral.',
  USO_RECOMENDADO='Sobresaliente'
WHERE IDOBJETIVO=51;

-- -- Competencia 6: Disciplina institucional --------------------------
UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET
  MODELO='Cierre de brecha',
  OBJETIVO='Durante los próximos 12 meses me comprometo a cumplir los horarios, normas, lineamientos de presentación, conducta y uso adecuado de recursos, manteniendo al menos 95% de cumplimiento en los controles aplicables.',
  INDICADOR='Porcentaje de cumplimiento de controles internos.',
  META='>= 95% de cumplimiento.',
  PLAZO='12 meses.',
  EVIDENCIA='Registros de asistencia, controles administrativos o reportes.',
  SEGUIMIENTO='Seguimiento trimestral.',
  USO_RECOMENDADO='Aceptable / Necesita mejorar / Requiere mejora / Insuficiente'
WHERE IDOBJETIVO=52;

UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET
  MODELO='Consolidación',
  OBJETIVO='Durante los próximos 12 meses me comprometo a asistir al 100% de las actividades obligatorias y responder los requerimientos administrativos dentro de los plazos definidos.',
  INDICADOR='Asistencia y cumplimiento oportuno de requerimientos.',
  META='100% de asistencia y respuestas en plazo.',
  PLAZO='12 meses.',
  EVIDENCIA='Listas de asistencia, correos y registros administrativos.',
  SEGUIMIENTO='Seguimiento trimestral.',
  USO_RECOMENDADO='Acorde'
WHERE IDOBJETIVO=53;

UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET
  MODELO='Expansión / referente',
  OBJETIVO='Durante los próximos 12 meses me comprometo a apoyar al equipo en la correcta aplicación de normas internas y a realizar al menos 3 acciones de socialización, acompañamiento o mejora de lineamientos.',
  INDICADOR='Acciones de acompañamiento o socialización realizadas.',
  META='>= 3 acciones ejecutadas.',
  PLAZO='12 meses.',
  EVIDENCIA='Actas, correos o material de socialización.',
  SEGUIMIENTO='Seguimiento semestral.',
  USO_RECOMENDADO='Sobresaliente'
WHERE IDOBJETIVO=54;

-- -- Competencia 7: Participación y formación continua ----------------
UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET
  MODELO='Cierre de brecha',
  OBJETIVO='Durante los próximos 12 meses me comprometo a completar el 100% de las capacitaciones obligatorias definidas para mi cargo dentro del plazo establecido y a demostrar su aplicación en mi labor.',
  INDICADOR='Capacitaciones obligatorias completadas y aplicadas.',
  META='100% de cumplimiento.',
  PLAZO='12 meses.',
  EVIDENCIA='Certificados, registros y evidencia de aplicación.',
  SEGUIMIENTO='Seguimiento trimestral.',
  USO_RECOMENDADO='Aceptable / Necesita mejorar / Requiere mejora / Insuficiente'
WHERE IDOBJETIVO=55;

UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET
  MODELO='Consolidación',
  OBJETIVO='Durante los próximos 12 meses me comprometo a cursar al menos 2 actividades de formación complementaria relacionadas con mi rol y compartir un aprendizaje práctico por cada una con el equipo.',
  INDICADOR='Actividades cursadas y socializaciones realizadas.',
  META='>= 2 actividades y >= 2 socializaciones.',
  PLAZO='12 meses.',
  EVIDENCIA='Certificados, presentaciones o actas.',
  SEGUIMIENTO='Seguimiento semestral.',
  USO_RECOMENDADO='Acorde'
WHERE IDOBJETIVO=56;

UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET
  MODELO='Expansión / referente',
  OBJETIVO='Durante los próximos 12 meses me comprometo a liderar o cofacilitar al menos 2 espacios internos de transferencia de conocimiento sobre buenas prácticas, lecciones aprendidas o actualizaciones del proceso.',
  INDICADOR='Espacios de transferencia realizados.',
  META='>= 2 espacios ejecutados.',
  PLAZO='12 meses.',
  EVIDENCIA='Listas de asistencia, material y actas.',
  SEGUIMIENTO='Seguimiento semestral.',
  USO_RECOMENDADO='Sobresaliente'
WHERE IDOBJETIVO=57;

-- -- Competencia 8: Gestión SST ---------------------------------------
UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET
  MODELO='Cierre de brecha',
  OBJETIVO='Durante los próximos 12 meses me comprometo a cumplir rigurosamente las normas de seguridad y autocuidado, participar en el 100% de las actividades obligatorias de SST y evitar incumplimientos reiterados.',
  INDICADOR='Participación en SST e incumplimientos reiterados.',
  META='100% de participación y 0 incumplimientos reiterados.',
  PLAZO='12 meses.',
  EVIDENCIA='Listas de asistencia, inspecciones y reportes de SST.',
  SEGUIMIENTO='Seguimiento trimestral.',
  USO_RECOMENDADO='Aceptable / Necesita mejorar / Requiere mejora / Insuficiente'
WHERE IDOBJETIVO=58;

UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET
  MODELO='Consolidación',
  OBJETIVO='Durante los próximos 12 meses me comprometo a reportar en máximo 24 horas las condiciones inseguras, incidentes o actos inseguros que identifique.',
  INDICADOR='Tiempo de reporte y número de reportes preventivos.',
  META='Reporte <= 24 horas por caso detectado.',
  PLAZO='12 meses.',
  EVIDENCIA='Formatos de reporte, sistema SST o bitácora.',
  SEGUIMIENTO='Seguimiento trimestral.',
  USO_RECOMENDADO='Acorde'
WHERE IDOBJETIVO=59;

UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET
  MODELO='Expansión / referente',
  OBJETIVO='Durante los próximos 12 meses me comprometo a participar en al menos 2 acciones preventivas como inspecciones, pausas de seguridad, campañas o socialización de riesgos y controles.',
  INDICADOR='Número de acciones preventivas realizadas.',
  META='>= 2 acciones preventivas.',
  PLAZO='12 meses.',
  EVIDENCIA='Actas, reportes o evidencia fotográfica.',
  SEGUIMIENTO='Seguimiento semestral.',
  USO_RECOMENDADO='Sobresaliente'
WHERE IDOBJETIVO=60;

-- -- Competencia 9: Relaciones Interpersonales ------------------------
UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET
  MODELO='Cierre de brecha',
  OBJETIVO='Durante los próximos 12 meses me comprometo a gestionar desacuerdos por canales adecuados, mantener interacciones respetuosas y reducir los conflictos recurrentes asociados a mi relacionamiento laboral.',
  INDICADOR='Incidentes relacionales recurrentes y valoración del líder.',
  META='Tendencia decreciente y mejora observable.',
  PLAZO='12 meses.',
  EVIDENCIA='Retroalimentación, acuerdos o actas.',
  SEGUIMIENTO='Seguimiento trimestral.',
  USO_RECOMENDADO='Aceptable / Necesita mejorar / Requiere mejora / Insuficiente'
WHERE IDOBJETIVO=61;

UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET
  MODELO='Consolidación',
  OBJETIVO='Durante los próximos 12 meses me comprometo a solicitar y brindar retroalimentación constructiva al menos una vez por trimestre a personas clave con las que interactúo.',
  INDICADOR='Ciclos de retroalimentación realizados.',
  META='>= 4 ciclos de retroalimentación.',
  PLAZO='12 meses.',
  EVIDENCIA='Actas breves, correos o formatos de seguimiento.',
  SEGUIMIENTO='Seguimiento trimestral.',
  USO_RECOMENDADO='Acorde'
WHERE IDOBJETIVO=62;

UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET
  MODELO='Expansión / referente',
  OBJETIVO='Durante los próximos 12 meses me comprometo a promover al menos 3 acciones de cooperación o integración entre compañeros o procesos que contribuyan a un ambiente laboral armónico.',
  INDICADOR='Acciones de cooperación o integración impulsadas.',
  META='>= 3 acciones implementadas.',
  PLAZO='12 meses.',
  EVIDENCIA='Actas, evidencias o reportes del área.',
  SEGUIMIENTO='Seguimiento semestral.',
  USO_RECOMENDADO='Sobresaliente'
WHERE IDOBJETIVO=63;

-- -- Competencia 10: Eficacia -----------------------------------------
UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET
  MODELO='Cierre de brecha',
  OBJETIVO='Durante los próximos 12 meses me comprometo a entregar al menos el 90% de mis tareas o productos en el tiempo y con la calidad esperada, según la priorización acordada con mi líder.',
  INDICADOR='Porcentaje de entregables oportunos y conformes.',
  META='>= 90% de cumplimiento.',
  PLAZO='12 meses.',
  EVIDENCIA='Tablero de tareas, actas o validación del líder.',
  SEGUIMIENTO='Seguimiento mensual y corte trimestral.',
  USO_RECOMENDADO='Aceptable / Necesita mejorar / Requiere mejora / Insuficiente'
WHERE IDOBJETIVO=64;

UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET
  MODELO='Consolidación',
  OBJETIVO='Durante los próximos 12 meses me comprometo a revisar semanalmente mis pendientes y corregir desviaciones antes de que afecten los resultados del área.',
  INDICADOR='Seguimientos realizados y desviaciones corregidas oportunamente.',
  META='100% de seguimiento semanal y corrección temprana de desviaciones críticas.',
  PLAZO='12 meses.',
  EVIDENCIA='Tablero, agenda o bitácora de control.',
  SEGUIMIENTO='Seguimiento mensual.',
  USO_RECOMENDADO='Acorde'
WHERE IDOBJETIVO=65;

UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET
  MODELO='Expansión / referente',
  OBJETIVO='Durante los próximos 12 meses me comprometo a liderar o apoyar al menos 2 mejoras que eleven productividad, oportunidad o calidad, con medición comparativa antes y después.',
  INDICADOR='Mejoras ejecutadas y variación del indicador.',
  META='>= 2 mejoras con medición.',
  PLAZO='12 meses.',
  EVIDENCIA='Indicadores antes y después, reportes y actas.',
  SEGUIMIENTO='Seguimiento semestral.',
  USO_RECOMENDADO='Sobresaliente'
WHERE IDOBJETIVO=66;

-- -- Competencia 11: Gestión Eficiente del Tiempo --------------------
UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET
  MODELO='Cierre de brecha',
  OBJETIVO='Durante los próximos 12 meses me comprometo a planificar semanalmente mis prioridades y cumplir al menos el 90% del cronograma acordado, reduciendo reprocesos y tiempos improductivos.',
  INDICADOR='Cumplimiento del cronograma y reprocesos.',
  META='>= 90% de cumplimiento y reducción verificable de reprocesos.',
  PLAZO='12 meses.',
  EVIDENCIA='Cronograma, agenda o tablero de seguimiento.',
  SEGUIMIENTO='Seguimiento mensual.',
  USO_RECOMENDADO='Aceptable / Necesita mejorar / Requiere mejora / Insuficiente'
WHERE IDOBJETIVO=67;

UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET
  MODELO='Consolidación',
  OBJETIVO='Durante los próximos 12 meses me comprometo a ejecutar al menos 2 acciones que disminuyan desperdicios, tiempos muertos o uso inadecuado de insumos, equipos o capacidad.',
  INDICADOR='Acciones de eficiencia ejecutadas y mejora estimada.',
  META='>= 2 acciones implementadas.',
  PLAZO='12 meses.',
  EVIDENCIA='Reportes de mejora e indicadores de uso.',
  SEGUIMIENTO='Seguimiento semestral.',
  USO_RECOMENDADO='Acorde'
WHERE IDOBJETIVO=68;

UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET
  MODELO='Expansión / referente',
  OBJETIVO='Durante los próximos 12 meses me comprometo a implementar al menos 1 mejora de organización o flujo de trabajo que genere evidencia de mayor eficiencia en tiempo, costo o aprovechamiento de recursos.',
  INDICADOR='Mejoras implementadas y resultado medido.',
  META='>= 1 mejora con evidencia de eficiencia.',
  PLAZO='12 meses.',
  EVIDENCIA='Comparación antes y después, procedimiento o tablero.',
  SEGUIMIENTO='Seguimiento semestral.',
  USO_RECOMENDADO='Sobresaliente'
WHERE IDOBJETIVO=69;

-- -- Competencia 12: Propósito (Liderazgo) ----------------------------
UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET
  MODELO='Cierre de brecha',
  OBJETIVO='Durante los próximos 12 meses me comprometo a realizar una reunión mensual de alineación para traducir los objetivos institucionales en prioridades concretas y verificables para el equipo.',
  INDICADOR='Reuniones de alineación realizadas y metas comunicadas.',
  META='12 reuniones con acuerdos claros.',
  PLAZO='12 meses.',
  EVIDENCIA='Actas, presentaciones o minutas.',
  SEGUIMIENTO='Seguimiento mensual.',
  USO_RECOMENDADO='Aceptable / Requiere mejora'
WHERE IDOBJETIVO=72;

UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET
  MODELO='Consolidación',
  OBJETIVO='Durante los próximos 12 meses me comprometo a definir y revisar con cada integrante al menos una meta individual trimestral conectada con los objetivos del proceso.',
  INDICADOR='Integrantes con metas definidas y revisadas.',
  META='100% del equipo con metas trimestrales.',
  PLAZO='12 meses.',
  EVIDENCIA='Formatos de metas y actas de seguimiento.',
  SEGUIMIENTO='Seguimiento trimestral.',
  USO_RECOMENDADO='Acorde'
WHERE IDOBJETIVO=73;

UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET
  MODELO='Expansión / referente',
  OBJETIVO='Durante los próximos 12 meses me comprometo a desarrollar 4 espacios de reflexión y alineación que conecten resultados, experiencia del paciente, cultura y contribución de cada rol.',
  INDICADOR='Espacios de propósito realizados.',
  META='>= 4 espacios ejecutados.',
  PLAZO='12 meses.',
  EVIDENCIA='Agenda, materiales y actas.',
  SEGUIMIENTO='Seguimiento trimestral.',
  USO_RECOMENDADO='Sobresaliente'
WHERE IDOBJETIVO=74;

-- -- Competencia 13: Colaboración (Liderazgo) -------------------------
UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET
  MODELO='Cierre de brecha',
  OBJETIVO='Durante los próximos 12 meses me comprometo a establecer acuerdos claros de trabajo con al menos 3 procesos clave y hacer seguimiento bimestral a su cumplimiento.',
  INDICADOR='Acuerdos interáreas definidos y revisados.',
  META='>= 3 acuerdos con seguimiento bimestral.',
  PLAZO='12 meses.',
  EVIDENCIA='Actas interáreas, matrices de acuerdos o reportes.',
  SEGUIMIENTO='Seguimiento bimestral.',
  USO_RECOMENDADO='Aceptable / Requiere mejora'
WHERE IDOBJETIVO=75;

UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET
  MODELO='Consolidación',
  OBJETIVO='Durante los próximos 12 meses me comprometo a desarrollar al menos 6 espacios de coordinación para resolver barreras, distribuir cargas y alinear acciones frente a metas comunes.',
  INDICADOR='Espacios de coordinación realizados.',
  META='>= 6 espacios ejecutados.',
  PLAZO='12 meses.',
  EVIDENCIA='Actas, agenda y acuerdos.',
  SEGUIMIENTO='Seguimiento trimestral.',
  USO_RECOMENDADO='Acorde'
WHERE IDOBJETIVO=76;

UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET
  MODELO='Expansión / referente',
  OBJETIVO='Durante los próximos 12 meses me comprometo a liderar o coliderar al menos 2 iniciativas interproceso que mejoren la coordinación, el servicio o los resultados institucionales.',
  INDICADOR='Iniciativas interproceso ejecutadas.',
  META='>= 2 iniciativas implementadas.',
  PLAZO='12 meses.',
  EVIDENCIA='Plan de trabajo, actas y resultados.',
  SEGUIMIENTO='Seguimiento semestral.',
  USO_RECOMENDADO='Sobresaliente'
WHERE IDOBJETIVO=77;

-- -- Competencia 14: Consistencia (Liderazgo) -------------------------
UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET
  MODELO='Cierre de brecha',
  OBJETIVO='Durante los próximos 12 meses me comprometo a mantener criterios claros frente al equipo y asegurar que al menos el 95% de los compromisos y lineamientos definidos tengan seguimiento y cierre.',
  INDICADOR='Compromisos y lineamientos con seguimiento y cierre.',
  META='>= 95% de cumplimiento.',
  PLAZO='12 meses.',
  EVIDENCIA='Bitácora, actas o tablero de seguimiento.',
  SEGUIMIENTO='Seguimiento mensual.',
  USO_RECOMENDADO='Aceptable / Requiere mejora'
WHERE IDOBJETIVO=78;

UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET
  MODELO='Consolidación',
  OBJETIVO='Durante los próximos 12 meses me comprometo a realizar seguimiento mensual a acuerdos, prioridades y decisiones del área para fortalecer una gestión predecible y alineada con los valores institucionales.',
  INDICADOR='Seguimientos mensuales realizados.',
  META='12 seguimientos ejecutados.',
  PLAZO='12 meses.',
  EVIDENCIA='Actas, tablero de gestión y retroalimentación.',
  SEGUIMIENTO='Seguimiento mensual.',
  USO_RECOMENDADO='Acorde'
WHERE IDOBJETIVO=79;

UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET
  MODELO='Expansión / referente',
  OBJETIVO='Durante los próximos 12 meses me comprometo a incorporar al menos 6 revisiones sobre coherencia, valores y cumplimiento de acuerdos en los espacios de gestión del equipo.',
  INDICADOR='Revisiones de coherencia y valores realizadas.',
  META='>= 6 revisiones.',
  PLAZO='12 meses.',
  EVIDENCIA='Agendas, actas y compromisos.',
  SEGUIMIENTO='Seguimiento bimestral.',
  USO_RECOMENDADO='Sobresaliente'
WHERE IDOBJETIVO=80;

-- -- Competencia 15: Adaptabilidad (Liderazgo) ------------------------
UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET
  MODELO='Cierre de brecha',
  OBJETIVO='Durante los próximos 12 meses me comprometo a ajustar planes, cargas o prioridades del equipo en máximo 15 días después de recibir lineamientos o cambios relevantes.',
  INDICADOR='Tiempo de ajuste de planes ante cambios.',
  META='Ajuste <= 15 días.',
  PLAZO='12 meses.',
  EVIDENCIA='Planes actualizados, actas o comunicaciones.',
  SEGUIMIENTO='Seguimiento trimestral.',
  USO_RECOMENDADO='Aceptable / Requiere mejora'
WHERE IDOBJETIVO=81;

UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET
  MODELO='Consolidación',
  OBJETIVO='Durante los próximos 12 meses me comprometo a desarrollar al menos 4 espacios con el equipo para abordar cambios, aprendizajes y acciones requeridas ante nuevos retos.',
  INDICADOR='Espacios de conversación sobre cambio.',
  META='>= 4 espacios ejecutados.',
  PLAZO='12 meses.',
  EVIDENCIA='Actas, agendas y evidencias.',
  SEGUIMIENTO='Seguimiento trimestral.',
  USO_RECOMENDADO='Acorde'
WHERE IDOBJETIVO=82;

UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET
  MODELO='Expansión / referente',
  OBJETIVO='Durante los próximos 12 meses me comprometo a implementar al menos 2 mejoras derivadas de cambios institucionales, tecnológicos o del servicio, con evidencia de apropiación y resultado.',
  INDICADOR='Mejoras implementadas y resultados evidenciados.',
  META='>= 2 mejoras implementadas.',
  PLAZO='12 meses.',
  EVIDENCIA='Comparación antes y después, actas y validación.',
  SEGUIMIENTO='Seguimiento semestral.',
  USO_RECOMENDADO='Sobresaliente'
WHERE IDOBJETIVO=83;

-- -- Competencia 16: Amor (Liderazgo) — corrige MODELO sin tilde ------
UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET
  MODELO='Cierre de brecha',
  OBJETIVO='Durante los próximos 12 meses me comprometo a realizar un espacio mensual de escucha y seguimiento al bienestar del equipo, documentando acuerdos y gestionando dentro de los 15 días siguientes al menos el 90% de las necesidades que estén bajo mi alcance.',
  INDICADOR='Espacios de escucha realizados y necesidades gestionadas en plazo.',
  META='12 espacios y >= 90% de necesidades gestionables atendidas <= 15 días.',
  PLAZO='12 meses.',
  EVIDENCIA='Minutas confidenciales de acuerdos, remisiones o planes de apoyo sin registrar información clínica sensible.',
  SEGUIMIENTO='Seguimiento mensual.',
  USO_RECOMENDADO='Aceptable / Requiere mejora'
WHERE IDOBJETIVO=87;

UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET
  MODELO='Consolidación',
  OBJETIVO='Durante los próximos 12 meses me comprometo a incorporar el bienestar individual y colectivo en la gestión del equipo, reconociendo al menos una contribución positiva cada mes y activando oportunamente las rutas institucionales para el 100% de las necesidades que excedan mi alcance.',
  INDICADOR='Reconocimientos realizados y necesidades canalizadas oportunamente.',
  META='>= 12 reconocimientos y 100% de casos pertinentes canalizados.',
  PLAZO='12 meses.',
  EVIDENCIA='Registros de reconocimiento, remisiones y seguimiento de gestión.',
  SEGUIMIENTO='Seguimiento trimestral.',
  USO_RECOMENDADO='Acorde'
WHERE IDOBJETIVO=88;

UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET
  MODELO='Expansión / referente',
  OBJETIVO='Durante los próximos 12 meses me comprometo a liderar al menos 2 acciones de bienestar, cuidado o compasión para el equipo, midiendo su percepción mediante una encuesta breve y logrando al menos 85% de valoración favorable.',
  INDICADOR='Acciones implementadas y percepción favorable del equipo.',
  META='>= 2 acciones y >= 85% de valoración favorable.',
  PLAZO='12 meses.',
  EVIDENCIA='Plan de acción, evidencias y resultados de encuesta.',
  SEGUIMIENTO='Seguimiento semestral.',
  USO_RECOMENDADO='Sobresaliente'
WHERE IDOBJETIVO=89;

-- -- Competencia 17: Bienvenida y Trato Memorable (EA Colaborador) ----
UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET
  MODELO='Cierre de brecha',
  OBJETIVO='Durante los próximos 12 meses me comprometo a aplicar en al menos el 90% de las interacciones observadas una bienvenida de cuatro pasos: saludar, presentarme, llamar al usuario por su nombre cuando sea posible y ofrecer orientación o ayuda.',
  INDICADOR='Porcentaje de interacciones observadas con los cuatro pasos de bienvenida.',
  META='>= 90% de adherencia.',
  PLAZO='12 meses.',
  EVIDENCIA='Lista de observación, paciente incógnito o retroalimentación del líder.',
  SEGUIMIENTO='Seguimiento trimestral.',
  USO_RECOMENDADO='Inconsistente / Crítico'
WHERE IDOBJETIVO=93;

UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET
  MODELO='Consolidación',
  OBJETIVO='Durante los próximos 12 meses me comprometo a mantener una primera impresión cálida y profesional en al menos el 95% de las observaciones, sin reincidencias en quejas por trato frío, apresurado o poco cercano.',
  INDICADOR='Adherencia a bienvenida y reincidencia de quejas asociadas.',
  META='>= 95% de adherencia y 0 reincidencias justificadas.',
  PLAZO='12 meses.',
  EVIDENCIA='Observaciones, felicitaciones, PQR y retroalimentación.',
  SEGUIMIENTO='Seguimiento trimestral.',
  USO_RECOMENDADO='Esperado / Consistente'
WHERE IDOBJETIVO=92;

UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET
  MODELO='Expansión / referente',
  OBJETIVO='Durante los próximos 12 meses me comprometo a ser referente de bienvenida, acompañando al menos a 2 compañeros en la aplicación del protocolo y proponiendo 1 mejora para fortalecer la primera impresión en el servicio.',
  INDICADOR='Compañeros acompañados y mejora implementada.',
  META='>= 2 acompañamientos y >= 1 mejora.',
  PLAZO='12 meses.',
  EVIDENCIA='Registros de acompañamiento y evidencia de mejora.',
  SEGUIMIENTO='Seguimiento semestral.',
  USO_RECOMENDADO='Referente'
WHERE IDOBJETIVO=91;

-- -- Competencia 18: Comunicación Empática y Clara (EA Colaborador) ---
UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET
  MODELO='Cierre de brecha',
  OBJETIVO='Durante los próximos 12 meses me comprometo a utilizar en al menos el 90% de las interacciones observadas la secuencia escuchar, validar, explicar en lenguaje comprensible y verificar comprensión.',
  INDICADOR='Porcentaje de interacciones con la secuencia completa.',
  META='>= 90% de adherencia.',
  PLAZO='12 meses.',
  EVIDENCIA='Lista de observación, retroalimentación y encuestas.',
  SEGUIMIENTO='Seguimiento trimestral.',
  USO_RECOMENDADO='Inconsistente / Crítico'
WHERE IDOBJETIVO=96;

UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET
  MODELO='Consolidación',
  OBJETIVO='Durante los próximos 12 meses me comprometo a resolver dudas con lenguaje claro y tono respetuoso, logrando al menos 90% de comprensión verificada o valoración favorable en las interacciones evaluadas.',
  INDICADOR='Comprensión verificada o valoración favorable de la comunicación.',
  META='>= 90% favorable.',
  PLAZO='12 meses.',
  EVIDENCIA='Teach-back, encuestas o auditoría de interacción.',
  SEGUIMIENTO='Seguimiento trimestral.',
  USO_RECOMENDADO='Esperado / Consistente'
WHERE IDOBJETIVO=95;

UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET
  MODELO='Expansión / referente',
  OBJETIVO='Durante los próximos 12 meses me comprometo a liderar al menos 2 espacios de práctica con casos reales sobre comunicación empática y a lograr una mejora mínima de 10 puntos porcentuales en la adherencia del equipo al comportamiento esperado.',
  INDICADOR='Espacios realizados y variación de adherencia del equipo.',
  META='>= 2 espacios y mejora >= 10 puntos porcentuales.',
  PLAZO='12 meses.',
  EVIDENCIA='Listas de asistencia, rúbricas antes y después.',
  SEGUIMIENTO='Seguimiento semestral.',
  USO_RECOMENDADO='Referente'
WHERE IDOBJETIVO=94;

-- -- Competencia 19: Personalización del Servicio (EA Colaborador) ----
UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET
  MODELO='Cierre de brecha',
  OBJETIVO='Durante los próximos 12 meses me comprometo a identificar y considerar al menos una necesidad, preferencia o condición relevante del paciente o familia en el 90% de las atenciones aplicables, ajustando la orientación o el trato.',
  INDICADOR='Atenciones aplicables con necesidad o preferencia identificada y atendida.',
  META='>= 90% de cumplimiento.',
  PLAZO='12 meses.',
  EVIDENCIA='Observación, registro permitido en la historia o formato de servicio y retroalimentación.',
  SEGUIMIENTO='Seguimiento trimestral.',
  USO_RECOMENDADO='Inconsistente / Crítico'
WHERE IDOBJETIVO=99;

UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET
  MODELO='Consolidación',
  OBJETIVO='Durante los próximos 12 meses me comprometo a adaptar de manera consistente la atención a las características del paciente o familia, logrando al menos 90% de valoración favorable en personalización o trato individualizado.',
  INDICADOR='Valoración favorable de personalización.',
  META='>= 90% favorable.',
  PLAZO='12 meses.',
  EVIDENCIA='Encuestas, observaciones o felicitaciones.',
  SEGUIMIENTO='Seguimiento trimestral.',
  USO_RECOMENDADO='Esperado / Consistente'
WHERE IDOBJETIVO=98;

UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET
  MODELO='Expansión / referente',
  OBJETIVO='Durante los próximos 12 meses me comprometo a diseñar o implementar al menos 1 mejora de personalización para un grupo de usuarios con necesidades específicas y alcanzar una valoración mínima de 4,5/5 en el piloto.',
  INDICADOR='Mejora implementada y valoración del grupo piloto.',
  META='>= 1 mejora y >= 4,5/5.',
  PLAZO='12 meses.',
  EVIDENCIA='Diseño del piloto, resultados y lecciones aprendidas.',
  SEGUIMIENTO='Seguimiento semestral.',
  USO_RECOMENDADO='Referente'
WHERE IDOBJETIVO=97;

-- -- Competencia 20: Eficiencia con Calidez (EA Colaborador) ----------
UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET
  MODELO='Cierre de brecha',
  OBJETIVO='Durante los próximos 12 meses me comprometo a cumplir los tiempos estándar de mi proceso en al menos el 90% de los casos, manteniendo simultáneamente al menos 90% de adherencia a los comportamientos de trato cálido.',
  INDICADOR='Cumplimiento de tiempo y adherencia a calidez.',
  META='>= 90% en ambos indicadores.',
  PLAZO='12 meses.',
  EVIDENCIA='Registros de tiempo, observaciones y encuestas.',
  SEGUIMIENTO='Seguimiento mensual y corte trimestral.',
  USO_RECOMENDADO='Inconsistente / Crítico'
WHERE IDOBJETIVO=102;

UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET
  MODELO='Consolidación',
  OBJETIVO='Durante los próximos 12 meses me comprometo a reducir en al menos 10% los tiempos muertos, esperas o reprocesos bajo mi control, sin disminuir la valoración de trato y calidez respecto a la línea base.',
  INDICADOR='Variación de tiempo o reproceso y satisfacción de trato.',
  META='Reducción >= 10% y satisfacción igual o superior a la línea base.',
  PLAZO='12 meses.',
  EVIDENCIA='Indicadores antes y después, encuestas y reportes.',
  SEGUIMIENTO='Seguimiento trimestral.',
  USO_RECOMENDADO='Esperado / Consistente'
WHERE IDOBJETIVO=101;

UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET
  MODELO='Expansión / referente',
  OBJETIVO='Durante los próximos 12 meses me comprometo a liderar al menos 1 mejora que reduzca en 15% o más una fricción o tiempo del servicio y mantenga una valoración de calidez mínima de 4,5/5.',
  INDICADOR='Reducción de fricción o tiempo y valoración de calidez.',
  META='Reducción >= 15% y calidez >= 4,5/5.',
  PLAZO='12 meses.',
  EVIDENCIA='Proyecto de mejora, indicadores y encuestas.',
  SEGUIMIENTO='Seguimiento trimestral.',
  USO_RECOMENDADO='Referente'
WHERE IDOBJETIVO=100;

-- -- Competencia 21: Cierre y Continuidad del Servicio (EA Colaborador)
UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET
  MODELO='Cierre de brecha',
  OBJETIVO='Durante los próximos 12 meses me comprometo a aplicar en al menos el 90% de las atenciones observadas un cierre de tres pasos: resumir lo realizado, explicar los siguientes pasos y verificar comprensión o dudas.',
  INDICADOR='Porcentaje de cierres con los tres pasos.',
  META='>= 90% de adherencia.',
  PLAZO='12 meses.',
  EVIDENCIA='Lista de observación, auditoría o retroalimentación.',
  SEGUIMIENTO='Seguimiento trimestral.',
  USO_RECOMENDADO='Inconsistente / Crítico'
WHERE IDOBJETIVO=105;

UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET
  MODELO='Consolidación',
  OBJETIVO='Durante los próximos 12 meses me comprometo a mantener cierres claros y seguros en al menos el 95% de las interacciones evaluadas y reducir en 10% las consultas repetidas atribuibles a información de cierre incompleta.',
  INDICADOR='Adherencia al cierre y consultas repetidas asociadas.',
  META='>= 95% de adherencia y reducción >= 10%.',
  PLAZO='12 meses.',
  EVIDENCIA='Observaciones, registros de recontacto y encuestas.',
  SEGUIMIENTO='Seguimiento trimestral.',
  USO_RECOMENDADO='Esperado / Consistente'
WHERE IDOBJETIVO=104;

UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET
  MODELO='Expansión / referente',
  OBJETIVO='Durante los próximos 12 meses me comprometo a diseñar, pilotear o fortalecer un guion o lista de chequeo de cierre y lograr al menos 90% de adherencia en el equipo o servicio intervenido.',
  INDICADOR='Herramienta implementada y adherencia del equipo.',
  META='>= 1 herramienta implementada y >= 90% de adherencia.',
  PLAZO='12 meses.',
  EVIDENCIA='Guion, lista de chequeo, piloto y resultados.',
  SEGUIMIENTO='Seguimiento semestral.',
  USO_RECOMENDADO='Referente'
WHERE IDOBJETIVO=103;

-- -- Competencia 22: Trabajo en Equipo Invisible (EA Colaborador) -----
UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET
  MODELO='Cierre de brecha',
  OBJETIVO='Durante los próximos 12 meses me comprometo a realizar entregas o remisiones con información completa y confirmación de recepción en al menos el 95% de los casos aplicables, evitando traslados innecesarios.',
  INDICADOR='Entregas completas con confirmación y traslados innecesarios.',
  META='>= 95% de entregas completas.',
  PLAZO='12 meses.',
  EVIDENCIA='Formatos de entrega, trazabilidad y auditoría de casos.',
  SEGUIMIENTO='Seguimiento trimestral.',
  USO_RECOMENDADO='Inconsistente / Crítico'
WHERE IDOBJETIVO=108;

UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET
  MODELO='Consolidación',
  OBJETIVO='Durante los próximos 12 meses me comprometo a acordar con al menos 2 áreas clave criterios de articulación y reducir en al menos 10% los reprocesos o devoluciones asociados a la coordinación.',
  INDICADOR='Acuerdos interáreas y variación de reprocesos.',
  META='>= 2 acuerdos y reducción >= 10%.',
  PLAZO='12 meses.',
  EVIDENCIA='Actas, matriz de acuerdos e indicadores.',
  SEGUIMIENTO='Seguimiento bimestral.',
  USO_RECOMENDADO='Esperado / Consistente'
WHERE IDOBJETIVO=107;

UPDATE VAADINWEB.HUMOBJETIVOMEJORA SET
  MODELO='Expansión / referente',
  OBJETIVO='Durante los próximos 12 meses me comprometo a liderar al menos 1 mejora transversal que acompañe el recorrido del paciente y reduzca en 15% o más una fricción, traslado o reproceso entre áreas.',
  INDICADOR='Mejora transversal y variación de la fricción intervenida.',
  META='>= 1 mejora y reducción >= 15%.',
  PLAZO='12 meses.',
  EVIDENCIA='Mapa de recorrido, indicadores y actas interáreas.',
  SEGUIMIENTO='Seguimiento trimestral.',
  USO_RECOMENDADO='Referente'
WHERE IDOBJETIVO=106;

COMMIT;

-- Verificar:
-- SELECT IDOBJETIVO, NUM_COMPETENCIA, MODELO, SUBSTR(OBJETIVO,1,60), USO_RECOMENDADO
-- FROM VAADINWEB.HUMOBJETIVOMEJORA
-- WHERE IDOBJETIVO IN (36,38,39,40,41,42,43,44,45,46,47,48,49,50,51,
--   52,53,54,55,56,57,58,59,60,61,62,63,64,65,66,67,68,69,
--   72,73,74,75,76,77,78,79,80,81,82,83,87,88,89,
--   91,92,93,94,95,96,97,98,99,100,101,102,103,104,105,106,107,108)
-- ORDER BY NUM_COMPETENCIA, IDOBJETIVO;
-- Debe retornar 66 filas.
