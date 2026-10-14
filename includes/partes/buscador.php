<?php
/**
 * CEPC · Sitio público
 * includes/partes/buscador.php — Ventana del buscador (estilo Spotlight).
 *
 * Se abre con el botón «Buscar» de la barra, con la tecla «/» o con
 * Ctrl/⌘ + K. Sin JavaScript, el botón lleva a buscar.php.
 * El índice viaja como JSON al final de la página (ver includes/busqueda.php).
 */

declare(strict_types=1);

$gruposBusqueda = [];
foreach (BUSQUEDA_GRUPOS as $clave => $grupo) {
    $gruposBusqueda[$clave] = ['titulo' => $grupo['titulo'], 'icono' => icono($grupo['icono'])];
}
?>
<div class="buscador" data-buscador hidden>
  <div class="buscador__fondo" data-buscador-cerrar></div>
  <div class="buscador__ventana" role="dialog" aria-modal="true" aria-label="Buscar en el sitio">
    <form class="buscador__campo" action="<?= esc(pagina('buscar')) ?>" method="get" role="search">
      <?= icono('magnifying-glass') ?>
      <input type="search" name="q" data-buscador-entrada
             placeholder="Ramos, salas, solemnes, entregas…"
             aria-label="Buscar ramos, salas, solemnes o entregas"
             autocomplete="off" spellcheck="false"
             role="combobox" aria-expanded="false" aria-controls="buscador-resultados" aria-autocomplete="list">
      <button class="buscador__cerrar" type="button" data-buscador-cerrar>
        <span>Esc</span><span class="oculto">Cerrar el buscador</span>
      </button>
    </form>
    <div class="buscador__resultados" id="buscador-resultados" role="listbox" aria-label="Resultados" data-buscador-resultados></div>
    <p class="buscador__ayuda" data-buscador-ayuda>
      Prueba con <button type="button" data-sugerencia="MA1001">MA1001</button>,
      <button type="button" data-sugerencia="B12">B12</button>,
      <button type="button" data-sugerencia="solemne">solemne</button> o
      <button type="button" data-sugerencia="vegano">vegano</button>.
    </p>
    <p class="oculto" aria-live="polite" data-buscador-estado></p>
  </div>
</div>
<script type="application/json" id="indice-busqueda"><?= json_encode(
    ['grupos' => $gruposBusqueda, 'items' => busqueda_indice()],
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP
) ?></script>
