<?php
/**
 * CEPC · Sitio público
 * includes/secciones/transparencia/movimientos.php
 *
 * El detalle de cada ingreso y gasto del año. Sin JavaScript se ve la
 * tabla completa; con JavaScript se puede filtrar y buscar.
 */

declare(strict_types=1);

$movimientos = transparencia_movimientos($anioCuentas);
?>
<section class="seccion" id="movimientos" aria-labelledby="movimientos-titulo">
  <div class="contenedor">
    <h2 id="movimientos-titulo" class="seccion__titulo">Movimientos</h2>
    <p class="seccion__bajada">Cada ingreso y gasto de <?= esc($anioCuentas) ?>, tal como está en nuestro libro de cuentas.</p>

    <?php if ($movimientos === []): ?>
      <div class="vacio"><?= icono('receipt') ?><p><strong>Todavía no hay movimientos registrados.</strong></p></div>
    <?php else: ?>
      <div class="libro" data-libro>
        <div class="libro__controles">
          <div class="segmentos" role="group" aria-label="Mostrar">
            <button type="button" aria-pressed="true" data-libro-tipo="todos">Todos</button>
            <button type="button" aria-pressed="false" data-libro-tipo="ingreso">Ingresos</button>
            <button type="button" aria-pressed="false" data-libro-tipo="gasto">Gastos</button>
          </div>
          <label class="libro__buscar">
            <?= icono('magnifying-glass') ?>
            <span class="oculto">Buscar en los movimientos</span>
            <input type="search" placeholder="Buscar…" data-libro-buscar autocomplete="off">
          </label>
        </div>

        <div class="libro__tabla">
          <table>
            <thead>
              <tr>
                <th scope="col">Fecha</th>
                <th scope="col">Detalle</th>
                <th scope="col">Categoría</th>
                <th scope="col" class="libro__monto">Monto</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($movimientos as $m): $f = fecha_partes((string) $m['fecha']); ?>
                <tr data-tipo="<?= esc($m['tipo']) ?>">
                  <td class="libro__fecha"><time datetime="<?= esc($m['fecha']) ?>"><?= esc($f['dia'] . ' ' . $f['mes']) ?></time></td>
                  <td>
                    <?= esc($m['descripcion']) ?>
                    <?php if ($m['proyecto']): ?><span class="libro__proyecto"><?= esc($m['proyecto']) ?></span><?php endif; ?>
                  </td>
                  <td class="libro__categoria"><?= esc($m['categoria_texto']) ?></td>
                  <td class="libro__monto libro__monto--<?= esc($m['tipo']) ?>">
                    <span class="oculto"><?= $m['tipo'] === 'ingreso' ? 'Ingreso de' : 'Gasto de' ?></span>
                    <?= $m['tipo'] === 'ingreso' ? '+' : '−' ?><?= esc(ltrim(pesos($m['monto']), '−')) ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <p class="libro__estado" data-libro-estado aria-live="polite"></p>
      </div>
    <?php endif; ?>
  </div>
</section>
