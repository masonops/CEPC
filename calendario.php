<?php
/**
 * CEPC · Sitio público
 * calendario.php — Calendario de evaluaciones, actividades y asambleas.
 *
 * Las fechas salen del calendario del panel (sólo los tipos públicos: ver
 * TIPOS_PUBLICOS en includes/datos.php). El mes se dibuja en el navegador
 * (assets/js/calendario.js); debajo va la lista de próximas fechas, que
 * también funciona sin JavaScript.
 *
 * Enlaces directos: calendario.php?fecha=2026-10-16 abre ese día.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/arranque.php';

// Desde dos meses atrás hasta ocho meses adelante: cubre el semestre completo.
$eventosCalendario = datos_eventos_rango(
    date('Y-m-01', strtotime('-2 months')),
    date('Y-m-t', strtotime('+8 months'))
);

$titulo       = 'Calendario · ' . marca();
$descripcion  = 'Solemnes, controles, entregas, actividades y asambleas de Plan Común.';
$paginaActual = 'calendario';
$estilos      = ['pagina', 'calendario'];
$scripts      = ['calendario'];
$cabecera     = [
    'titulo' => 'Calendario',
    'bajada' => 'Solemnes, controles, entregas, actividades y asambleas. Toca un día para ver el detalle.',
    'icono'  => 'calendar-blank',
];

require __DIR__ . '/includes/partes/encabezado.php';
?>

<main id="contenido">
  <?php require __DIR__ . '/includes/partes/cabecera-pagina.php'; ?>
  <?php require __DIR__ . '/includes/secciones/calendario/mes.php'; ?>
  <?php require __DIR__ . '/includes/secciones/calendario/lista.php'; ?>
</main>

<?php require __DIR__ . '/includes/partes/pie.php'; ?>
