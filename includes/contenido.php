<?php
/**
 * CEPC · Sitio público
 * includes/contenido.php — Textos fijos de la portada.
 *
 * Aquí está todo lo que se escribe a mano: el destacado manual, los textos
 * por tipo de evento, las tarjetas de ayuda y las preguntas frecuentes.
 * Se puede editar sin saber PHP: sólo hay que cambiar lo que está entre
 * comillas y respetar las comas.
 */

declare(strict_types=1);

/**
 * Destacado manual de la portada.
 *
 * Mientras esté en null, la portada elige sola: el evento más importante de
 * las próximas semanas o, si no hay, la última publicación de Instagram.
 * Para fijar una noticia, reemplaza null por algo así:
 *
 *     return [
 *         'tipo'         => 'noticia',              // noticia · evento · instagram
 *         'etiqueta'     => 'Comunicado',
 *         'titulo'       => 'Calendario de solemnes actualizado',
 *         'resumen'      => 'La Escuela movió dos fechas. Revisa los cambios.',
 *         'fecha'        => '2026-10-20',           // opcional
 *         'hora'         => null,                   // opcional
 *         'lugar'        => null,                   // opcional
 *         'imagen'       => 'assets/img/mi-foto.jpg', // opcional, se usa de fondo
 *         'enlace'       => 'https://instagram.com/p/...',
 *         'enlace_texto' => 'Leer el comunicado',
 *     ];
 */
function contenido_destacado_manual(): ?array
{
    return null;
}

/**
 * Resumen por defecto según el tipo de evento, para cuando el evento no
 * tiene descripción en el panel.
 */
function contenido_resumen_evento(string $tipo): string
{
    return match ($tipo) {
        'asamblea'  => 'Un espacio abierto para conversar y votar lo que importa en Plan Común. Tu opinión cuenta.',
        'marcha'    => 'Nos encontramos para marchar juntos. Revisa el punto de encuentro y cuídate en el trayecto.',
        'paro'      => 'Jornada de reflexión sin clases. Habrá actividades y espacios de conversación en la facultad.',
        'sunset'    => 'Música, comida y tarde al aire libre para despejarse entre evaluaciones.',
        'actividad' => 'Actividad abierta a todo Plan Común. Llega con tus amigos o ven a conocer gente nueva.',
        'solemne'   => 'Revisa sala y horario con tiempo. En la semana previa hay reforzamientos abiertos.',
        'control'   => 'Revisa sala y horario con tiempo, y aprovecha las tutorías de la semana.',
        'entrega'   => 'Recuerda el plazo de entrega. Si tienes un problema, escríbenos antes de la fecha.',
        default     => 'Más detalles en nuestro Instagram.',
    };
}

/** Las cuatro tarjetas de ayuda de la sección «Lo que necesitas». */
function contenido_ayuda(): array
{
    return [
        'apuntes' => [
            'titulo' => 'Apuntes y solemnes antiguas',
            'texto'  => 'Material revisado por Docencia, ordenado por ramo: apuntes, guías, controles y solemnes resueltas.',
        ],
        'tutorias' => [
            'titulo' => 'Tutorías y reforzamientos',
            'texto'  => 'Clases entre pares antes de cada solemne. Son gratis y abiertas.',
        ],
        'bienestar' => [
            'titulo' => '¿Necesitas apoyo?',
            'texto'  => 'Salud mental, becas, BAES o una situación difícil. Te escuchamos con reserva y te ayudamos a encontrar a quién acudir.',
        ],
        'docencia' => [
            'titulo' => 'Problemas académicos',
            'texto'  => 'Topes de solemnes, notas que no llegan o un trato injusto. Lo llevamos a la Escuela por ti.',
        ],
    ];
}

/** Preguntas frecuentes. La respuesta admite HTML simple (enlaces, <strong>). */
function contenido_preguntas(): array
{
    return [
        [
            'pregunta'  => '¿Qué es el CEPC?',
            'respuesta' => 'Somos el Centro de Estudiantes de Plan Común: estudiantes de primer y segundo año elegidos para representar a la generación ante la Escuela de Ingeniería y la Facultad. Organizamos actividades, acompañamos casos académicos y de bienestar, y mantenemos el banco de apuntes.',
        ],
        [
            'pregunta'  => '¿Dónde está la sede y cuándo está abierta?',
            'respuesta' => 'La sede está en Beauchef 851. Normalmente hay alguien de la directiva durante el horario de clases; si necesitas algo puntual, escríbenos antes para asegurarnos de estar.',
        ],
        [
            'pregunta'  => 'Tengo un problema con una nota o un profesor. ¿Qué hago?',
            'respuesta' => 'Escríbele a Docencia contándonos el ramo, la sección y qué pasó. Mientras más personas afectadas, mejor: si es un problema de todo el curso, avísanos y lo llevamos juntos a la Escuela. Todo se maneja con reserva.',
        ],
        [
            'pregunta'  => '¿Cómo funcionan las asambleas?',
            'respuesta' => 'Las asambleas son abiertas a todo Plan Común. Ahí se discuten y votan las posturas del CEPC. Las fechas aparecen en la agenda de esta página y las publicamos en Instagram con anticipación.',
        ],
        [
            'pregunta'  => '¿Puedo participar sin ser parte de la directiva?',
            'respuesta' => 'Sí, y nos encanta. Puedes ser delegado o delegada de tu sección, hacer tutorías, ayudar en actividades o proponer ideas. Escríbenos por Instagram o correo.',
        ],
    ];
}
