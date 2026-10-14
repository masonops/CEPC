<?php
/**
 * CEPC · Sitio público
 * includes/partes/cabecera-pagina.php — Título grande de las páginas
 * interiores (Calendario, Mapa, Calculadora, Transparencia, Buscar).
 *
 * Usa $cabecera = ['titulo' => …, 'bajada' => …, 'icono' => …].
 */

declare(strict_types=1);
?>
<header class="cabecera-pagina">
  <div class="contenedor">
    <p class="cabecera-pagina__icono" aria-hidden="true"><?= icono($cabecera['icono'] ?? 'arrow-right') ?></p>
    <h1 class="cabecera-pagina__titulo"><?= esc($cabecera['titulo']) ?></h1>
    <?php if (!empty($cabecera['bajada'])): ?>
      <p class="cabecera-pagina__bajada"><?= esc($cabecera['bajada']) ?></p>
    <?php endif; ?>
  </div>
</header>
