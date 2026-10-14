<?php
/**
 * CEPC · Sitio público
 * roomies.php — Roomies: piezas y deptos para compartir, publicados por
 * estudiantes con su correo de la Universidad verificado.
 *
 * Secciones (includes/secciones/roomies/):
 *   cabecera.php     título, qué es y botón para publicar
 *   filtros.php      buscador, tipo, precio, distancia y más filtros
 *   resultados.php   tarjetas y mapa (assets/js/roomies.js)
 *   como-funciona.php pasos y consejos de seguridad
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/arranque.php';

$roomies      = datos_roomies();
$titulo       = 'Roomies · ' . marca();
$descripcion  = 'Piezas y departamentos para compartir cerca de Beauchef, publicados por estudiantes verificados de la Universidad de Chile.';
$paginaActual = 'roomies';
$estilos      = ['pagina', 'roomies'];
$scripts      = ['roomies-mapa', 'roomies'];
$conMapa      = true;

require __DIR__ . '/includes/partes/encabezado.php';
?>

<main id="contenido">
  <?php require __DIR__ . '/includes/secciones/roomies/cabecera.php'; ?>
  <?php if (sitio('roomies')['activo'] ?? true): ?>
    <?php require __DIR__ . '/includes/secciones/roomies/filtros.php'; ?>
    <?php require __DIR__ . '/includes/secciones/roomies/resultados.php'; ?>
  <?php endif; ?>
  <?php require __DIR__ . '/includes/secciones/roomies/como-funciona.php'; ?>
</main>

<?php require __DIR__ . '/includes/partes/pie.php'; ?>
