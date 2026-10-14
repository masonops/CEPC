<?php
/**
 * CEPC · Sitio público
 * includes/secciones/calculadora/explicacion.php
 *
 * Cómo se hace el cálculo, en simple.
 */

declare(strict_types=1);
?>
<section class="seccion explicacion" aria-labelledby="explicacion-titulo">
  <div class="contenedor contenedor--angosto">
    <h2 id="explicacion-titulo" class="seccion__titulo">Así lo calculamos</h2>
    <div class="explicacion__pasos">
      <div>
        <h3>Nota final</h3>
        <p>Cada nota se multiplica por su peso y se suman: <code>NF = Σ peso × nota</code>. Con pesos 20%, 20%, 20% y 40%, un 5,0 en el examen aporta 2,0 puntos.</p>
      </div>
      <div>
        <h3>Lo que necesitas</h3>
        <p>Restamos a la meta lo que ya llevas y lo repartimos en el peso que falta: <code>(meta − lo que llevas) ÷ peso pendiente</code>. Como las notas van con un decimal, redondeamos hacia arriba.</p>
      </div>
      <div>
        <h3>Ojo</h3>
        <p>Algunos ramos piden nota mínima en el examen o en los controles, o eximen con cierto promedio. Esta calculadora no lo sabe: confírmalo en el programa del curso o en U-Cursos.</p>
      </div>
    </div>
  </div>
</section>
