<?php
/**
 * CEPC · Sitio público
 * includes/secciones/inicio/transparencia.php
 *
 * «Cuentas claras»: el balance del año y accesos directos a la página de
 * transparencia (rendición de cuentas, asambleas, votaciones y estatutos).
 * Los números salen de Finanzas en el panel (en modo lista no se muestran:
 * todavía no administramos plata).
 */

declare(strict_types=1);

$anioCuentas = transparencia_anio();
$resumen     = transparencia_resumen($anioCuentas);
$accesos     = [
    ['ancla' => 'cuentas',    'icono' => 'receipt',      'titulo' => 'Rendición de cuentas', 'texto' => 'Cada ingreso y gasto del año, con su detalle.'],
    ['ancla' => 'asambleas',  'icono' => 'users-three',  'titulo' => 'Asambleas y actas',    'texto' => 'Fechas, asistencia y lo que se acordó.'],
    ['ancla' => 'votaciones', 'icono' => 'check-square', 'titulo' => 'Votaciones',           'texto' => 'Resultados de plebiscitos y consultas.'],
    ['ancla' => 'documentos', 'icono' => 'scales',       'titulo' => 'Estatutos',            'texto' => 'Las reglas con las que funciona el CEPC.'],
];
?>
<section class="seccion" id="transparencia" aria-labelledby="transparencia-titulo">
  <div class="contenedor cuentas">
    <div class="cuentas__resumen revelar">
      <?php if (es_lista()): ?>
        <h2 id="transparencia-titulo" class="seccion__titulo">Cuentas claras, desde el día uno.</h2>
        <p class="seccion__bajada">Nuestra propuesta: si somos CEPC, todo lo que entre y salga será público, junto con las asambleas, votaciones y estatutos.</p>
      <?php else: ?>
        <h2 id="transparencia-titulo" class="seccion__titulo">Cuentas claras.</h2>
        <p class="seccion__bajada">Todo lo que entra y sale del CEPC es público. Esto va de <?= esc($anioCuentas) ?>.</p>
      <?php endif; ?>

      <?php if (es_lista()): ?>
        <a class="enlace-flecha" href="<?= esc(pagina('transparencia')) ?>">Ver cómo funcionaría <?= icono('arrow-right') ?></a>
      <?php elseif ($resumen['movimientos'] === 0): ?>
        <p class="cuentas__vacio">Aún no hay movimientos publicados este año.</p>
      <?php else: ?>
        <dl class="cifras">
          <div>
            <dt>Ingresos</dt>
            <dd><?= esc(pesos($resumen['ingresos'])) ?></dd>
          </div>
          <div>
            <dt>Gastos</dt>
            <dd><?= esc(pesos($resumen['gastos'])) ?></dd>
          </div>
          <div>
            <dt>Saldo</dt>
            <dd><?= esc(pesos($resumen['saldo'])) ?></dd>
          </div>
        </dl>
      <?php endif; ?>
    </div>

    <ul class="accesos">
      <?php foreach ($accesos as $i => $acceso): ?>
        <li class="revelar" style="--i: <?= (int) $i ?>">
          <a class="acceso" href="<?= esc(pagina('transparencia', $acceso['ancla'])) ?>">
            <span class="acceso__icono"><?= icono($acceso['icono']) ?></span>
            <span class="acceso__texto">
              <strong><?= esc($acceso['titulo']) ?></strong>
              <span><?= esc($acceso['texto']) ?></span>
            </span>
            <?= icono('arrow-right', 'acceso__flecha') ?>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
