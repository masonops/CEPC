<?php
/**
 * CEPC · Sitio público
 * mapa.php — Mapa interactivo del campus Beauchef (850 y 851).
 *
 * Explorar edificios, pisos y salas; calcular rutas desde una entrada o un
 * edificio; ver dónde comer cerca. Los datos están en includes/mapa/ y la
 * interacción en assets/js/mapa.js.
 *
 * Enlaces directos:
 *   mapa.php?edificio=fisica      abre un edificio
 *   mapa.php?sala=B12             abre la sala (edificio y piso)
 *   mapa.php?lugar=vegano         abre un lugar para comer
 *   mapa.php?desde=acceso-850&hasta=fisica   muestra una ruta
 *   mapa.php?coordenadas=1        cuadrícula para editar los datos
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/arranque.php';

$verCoordenadas = !sitio('estatico', false) && !empty($_GET['coordenadas']);

$titulo       = 'Mapa del campus · ' . sitio('sigla');
$descripcion  = 'Mapa de Beauchef 850 y 851: edificios, salas, rutas desde la entrada y dónde comer cerca.';
$paginaActual = 'mapa';
$estilos      = ['pagina', 'mapa-dibujo', 'mapa'];
$scripts      = ['mapa'];

require __DIR__ . '/includes/partes/encabezado.php';
?>

<main id="contenido" class="mapa-pagina">
  <h1 class="oculto">Mapa del campus Beauchef</h1>
  <?php require __DIR__ . '/includes/secciones/mapa/lienzo.php'; ?>
  <?php require __DIR__ . '/includes/secciones/mapa/panel.php'; ?>
</main>

<?php require __DIR__ . '/includes/partes/pie.php'; ?>
