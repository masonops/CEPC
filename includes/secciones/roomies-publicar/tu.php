<?php
/**
 * CEPC · Sitio público
 * includes/secciones/roomies-publicar/tu.php — Quién publica: nombre,
 * correo U. de Chile (se verifica), carrera, generación y hábitos.
 */

declare(strict_types=1);

$dominios = (array) (sitio('roomies')['dominios'] ?? []);
?>
<fieldset class="parte" id="parte-tu" data-parte="tu">
  <legend><span class="parte__numero">1</span>Sobre ti</legend>
  <p class="parte__ayuda">Quienes lean tu aviso quieren saber con quién vivirían. Tu correo no se muestra.</p>
  <div class="rejilla rejilla--2">
    <label class="campo<?= $conError('autor_nombre') ?>">
      <span>Nombre</span>
      <input type="text" name="autor_nombre" maxlength="40" autocomplete="given-name" required placeholder="Como te dicen" value="<?= $valor('autor_nombre') ?>">
      <?= $error('autor_nombre') ?>
    </label>
    <label class="campo<?= $conError('autor_correo') ?>">
      <span>Correo U. de Chile <span class="campo__ayuda">· lo verificamos</span></span>
      <input type="email" name="autor_correo" maxlength="120" autocomplete="email" inputmode="email" required placeholder="nombre@<?= esc($dominios[0] ?? 'ug.uchile.cl') ?>" value="<?= $valor('autor_correo') ?>" data-correo-uchile="<?= esc(implode(',', $dominios)) ?>">
      <?= $error('autor_correo') ?>
    </label>
    <label class="campo">
      <span>Carrera</span>
      <input type="text" name="autor_carrera" maxlength="60" list="carreras" placeholder="Ingeniería · Plan Común" value="<?= $valor('autor_carrera') ?>">
      <datalist id="carreras">
        <?php foreach (['Ingeniería · Plan Común', 'Ingeniería Civil', 'Ingeniería Civil Industrial', 'Ingeniería Civil en Computación', 'Ingeniería Civil Eléctrica', 'Ingeniería Civil Mecánica', 'Ingeniería Civil Química', 'Ingeniería Civil en Minas', 'Ingeniería Civil en Biotecnología', 'Geología', 'Geofísica', 'Astronomía', 'Licenciatura en Física', 'Licenciatura en Matemáticas'] as $c): ?>
          <option value="<?= esc($c) ?>"></option>
        <?php endforeach; ?>
      </datalist>
    </label>
    <div class="rejilla rejilla--2 rejilla--sin-margen">
      <label class="campo">
        <span>Generación</span>
        <input type="number" name="autor_ingreso" min="1990" max="<?= (int) date('Y') + 1 ?>" inputmode="numeric" placeholder="<?= date('Y') ?>" value="<?= $valor('autor_ingreso') ?>">
      </label>
      <label class="campo">
        <span>Edad <span class="campo__ayuda">· opcional</span></span>
        <input type="number" name="autor_edad" min="16" max="99" inputmode="numeric" value="<?= $valor('autor_edad') ?>">
      </label>
    </div>
  </div>
  <label class="campo">
    <span>Cuéntales de ti</span>
    <textarea name="autor_sobre" rows="3" maxlength="600" placeholder="De dónde eres, qué estudias, cómo es un día normal en la casa…" data-contador><?= $valor('autor_sobre') ?></textarea>
  </label>
  <div class="campo">
    <span>¿Cómo eres en la casa?</span>
    <div class="casillas">
      <?php foreach (ROOMIES_HABITOS as $clave => $texto): ?>
        <label><input type="checkbox" name="autor_habitos[]" value="<?= esc($clave) ?>"<?= $marcado('autor_habitos', $clave) ?>><span><?= esc($texto) ?></span></label>
      <?php endforeach; ?>
    </div>
  </div>
</fieldset>
