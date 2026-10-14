/* ==========================================================================
   CEPC · Sitio público
   calculadora.js — Calculadora de notas.

   Lógica:
     llevas     = Σ (peso × nota) de las evaluaciones con nota
     pendiente  = Σ peso de las evaluaciones sin nota
     necesitas  = (meta − llevas) ÷ pendiente   → redondeado hacia arriba a 0,1

   Lo que se escribe se guarda en este dispositivo (localStorage), nunca en
   el servidor.
   ========================================================================== */

(function () {
  'use strict';

  var raiz = document.querySelector('[data-calculadora]');
  if (!raiz) return;

  var CLAVE = 'cepc-calculadora';
  var NOTA_MIN = 1, NOTA_MAX = 7;
  var plantillas = JSON.parse(document.getElementById('plantillas-notas').textContent);

  var filas = raiz.querySelector('[data-calc-filas]');
  var suma = raiz.querySelector('[data-calc-suma]');
  var resultado = raiz.querySelector('[data-calc-resultado]');
  var campoRamo = raiz.querySelector('[data-calc-ramo]');
  var metas = {
    aprobar: raiz.querySelector('[data-calc-meta="aprobar"]'),
    segunda: raiz.querySelector('[data-calc-meta="segunda"]'),
  };
  var simulada = 4.0; // valor del deslizador «si te sacas…»
  var ultimo = { llevas: 0, pendiente: 0 }; // para mover el deslizador sin redibujar

  /* --- Números con coma -------------------------------------------------- */

  /** «4,5» o «4.5» → 4.5; vacío o inválido → null. */
  function leer(texto) {
    var limpio = String(texto || '').trim().replace(',', '.');
    if (limpio === '') return null;
    var n = Number(limpio);
    return isFinite(n) ? n : null;
  }

  /** 4.5 → «4,5». */
  function coma(n, decimales) {
    return n.toFixed(decimales === undefined ? 1 : decimales).replace('.', ',');
  }

  /** Redondea hacia arriba al décimo (una nota de 4,33 exige un 4,4). */
  function arriba(n) {
    return Math.ceil(n * 10 - 1e-9) / 10;
  }

  function esc(t) {
    return String(t).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  /* --- Filas -------------------------------------------------------------- */

  var contador = 0;

  function agregarFila(nombre, peso, nota) {
    contador++;
    var id = 'ev-' + contador;
    var tr = document.createElement('tr');
    tr.innerHTML =
      '<td><label class="oculto" for="' + id + '-n">Nombre de la evaluación</label>'
      + '<input id="' + id + '-n" class="notas__nombre" type="text" data-campo="nombre" maxlength="40" value="' + esc(nombre || '') + '"></td>'
      + '<td><label class="oculto" for="' + id + '-p">Peso en porcentaje</label>'
      + '<div class="notas__peso"><input id="' + id + '-p" type="text" inputmode="decimal" data-campo="peso" value="' + esc(peso == null ? '' : String(peso).replace('.', ',')) + '"><span aria-hidden="true">%</span></div></td>'
      + '<td><label class="oculto" for="' + id + '-c">Nota (vacío si aún no la tienes)</label>'
      + '<input id="' + id + '-c" class="notas__nota" type="text" inputmode="decimal" data-campo="nota" placeholder="–" value="' + esc(nota == null ? '' : coma(nota)) + '"></td>'
      + '<td><button class="notas__quitar" type="button" data-calc-quitar aria-label="Quitar esta evaluación">×</button></td>';
    filas.appendChild(tr);
    return tr;
  }

  function evaluaciones() {
    return Array.prototype.map.call(filas.querySelectorAll('tr'), function (tr) {
      var get = function (c) { return tr.querySelector('[data-campo="' + c + '"]'); };
      return {
        fila: tr,
        nombre: get('nombre').value.trim(),
        peso: leer(get('peso').value),
        nota: leer(get('nota').value),
        campos: { peso: get('peso'), nota: get('nota') },
      };
    });
  }

  /* --- Cálculo -------------------------------------------------------------- */

  function calcular() {
    var evs = evaluaciones();
    var pesoTotal = 0, pesoHecho = 0, llevas = 0, errores = false;

    evs.forEach(function (e) {
      var pesoMalo = e.peso === null || e.peso < 0 || e.peso > 100;
      var notaMala = e.nota !== null && (e.nota < NOTA_MIN || e.nota > NOTA_MAX);
      e.campos.peso.setAttribute('aria-invalid', String(pesoMalo));
      e.campos.nota.setAttribute('aria-invalid', String(notaMala));
      if (pesoMalo || notaMala) { errores = true; return; }
      pesoTotal += e.peso;
      if (e.nota !== null) {
        pesoHecho += e.peso;
        llevas += e.peso / 100 * e.nota;
      }
    });

    var pendiente = pesoTotal - pesoHecho;
    var cuadra = Math.abs(pesoTotal - 100) < 0.01;
    suma.textContent = 'Pesos: ' + coma(pesoTotal, pesoTotal % 1 ? 1 : 0) + '%' + (cuadra ? '' : ' · deberían sumar 100%');
    suma.classList.toggle('calculadora__suma--alerta', !cuadra);

    var metaAprobar = leer(metas.aprobar.value) || 4.0;
    var metaSegunda = leer(metas.segunda.value) || 3.7;

    var html = '';
    if (errores) {
      html = '<p class="resultado-calc__aviso">' + icono('warning-circle') + 'Revisa los campos marcados: el peso va de 0 a 100 y la nota de 1,0 a 7,0.</p>';
      resultado.innerHTML = html;
      guardar();
      return;
    }

    var nombre = campoRamo.value.trim();
    html += '<p class="resultado-calc__ramo">' + esc(nombre || 'Tu ramo') + '</p>';

    if (pesoHecho > 0) {
      var promedioActual = llevas / (pesoHecho / 100);
      html += '<div class="resultado-calc__actual"><span>Promedio de lo que llevas</span>'
        + '<strong>' + coma(promedioActual, 2) + '</strong>'
        + '<span>Llevas ' + coma(pesoHecho, pesoHecho % 1 ? 1 : 0) + '% del ramo</span></div>';
    } else {
      html += '<div class="resultado-calc__actual"><span>Aún no anotas notas</span><strong>–</strong>'
        + '<span>Escribe las que ya tengas; deja en blanco las que faltan.</span></div>';
    }

    if (pendiente <= 0.0001) {
      // Ya están todas las notas: nota final.
      var nf = llevas * (100 / Math.max(pesoTotal, 0.0001));
      var estado = nf >= metaAprobar - 1e-9 ? ['bien', 'Apruebas', 'check-square']
        : nf >= metaSegunda - 1e-9 ? ['medio', 'Vas a segunda', 'info'] : ['mal', 'No alcanza', 'warning-circle'];
      html += '<div class="meta meta--' + estado[0] + '"><span class="meta__titulo">Nota final</span>'
        + '<strong class="meta__nota">' + coma(nf, 2) + '</strong>'
        + '<span class="meta__estado">' + icono(estado[2]) + estado[1] + '</span></div>';
    } else {
      html += bloqueMeta('Para aprobar', metaAprobar, llevas, pendiente);
      html += bloqueMeta('Para ir a segunda', metaSegunda, llevas, pendiente);

      ultimo = { llevas: llevas, pendiente: pendiente };
      var final = llevas + pendiente / 100 * simulada;
      html += '<div class="simulador"><label for="calc-simular">Si en lo que falta te sacas <strong data-calc-sim-valor>' + coma(simulada) + '</strong></label>'
        + '<input id="calc-simular" type="range" min="1" max="7" step="0.1" value="' + simulada + '" data-calc-simular>'
        + '<p>tu nota final sería <strong data-calc-sim-final>' + coma(final, 2) + '</strong></p></div>';
    }

    resultado.innerHTML = html;
    guardar();
  }

  function bloqueMeta(titulo, meta, llevas, pendiente) {
    var necesitas = (meta - llevas) / (pendiente / 100);
    var nota, estado;
    if (necesitas <= NOTA_MIN) {
      nota = 'Listo';
      estado = ['bien', 'Ya lo tienes asegurado', 'check-square'];
    } else if (necesitas > NOTA_MAX) {
      nota = coma(arriba(necesitas));
      estado = ['mal', 'No alcanza ni con 7,0', 'warning-circle'];
    } else {
      nota = coma(arriba(necesitas));
      estado = necesitas > 6 ? ['medio', 'Difícil, pero posible', 'info'] : ['bien', 'Alcanzable', 'check-square'];
    }
    return '<div class="meta meta--' + estado[0] + '">'
      + '<span class="meta__titulo">' + titulo + ' (' + coma(meta) + ')</span>'
      + '<strong class="meta__nota">' + nota + '</strong>'
      + '<span class="meta__detalle">' + (necesitas <= NOTA_MIN ? 'aunque te saques 1,0 en lo que queda' : 'de promedio en lo que queda') + '</span>'
      + '<span class="meta__estado">' + icono(estado[2]) + estado[1] + '</span></div>';
  }

  /** Íconos en línea (los mismos de Phosphor que usa el sitio). */
  function icono(nombre) {
    var plantilla = document.querySelector('template[data-icono="' + nombre + '"]');
    return plantilla ? plantilla.innerHTML : '';
  }

  /* --- Guardar y cargar ------------------------------------------------------ */

  function guardar() {
    try {
      localStorage.setItem(CLAVE, JSON.stringify({
        ramo: campoRamo.value,
        aprobar: metas.aprobar.value,
        segunda: metas.segunda.value,
        evaluaciones: evaluaciones().map(function (e) {
          return [e.nombre, e.campos.peso.value, e.campos.nota.value];
        }),
      }));
    } catch (e) { /* modo privado */ }
  }

  function usarPlantilla(clave) {
    filas.innerHTML = '';
    plantillas[clave].evaluaciones.forEach(function (ev) { agregarFila(ev[0], ev[1], null); });
    raiz.querySelectorAll('[data-calc-plantilla]').forEach(function (b) {
      b.setAttribute('aria-pressed', String(b.getAttribute('data-calc-plantilla') === clave));
    });
    calcular();
  }

  var guardado = null;
  try { guardado = JSON.parse(localStorage.getItem(CLAVE) || 'null'); } catch (e) { guardado = null; }

  if (guardado && guardado.evaluaciones && guardado.evaluaciones.length) {
    campoRamo.value = guardado.ramo || '';
    if (guardado.aprobar) metas.aprobar.value = guardado.aprobar;
    if (guardado.segunda) metas.segunda.value = guardado.segunda;
    guardado.evaluaciones.forEach(function (e) {
      var tr = agregarFila(e[0], null, null);
      tr.querySelector('[data-campo="peso"]').value = e[1];
      tr.querySelector('[data-campo="nota"]').value = e[2];
    });
    calcular();
  } else {
    usarPlantilla('tres-controles');
  }

  /* --- Eventos ---------------------------------------------------------------- */

  raiz.addEventListener('input', function (evento) {
    if (evento.target.matches('[data-calc-simular]')) {
      // Sólo se actualizan los dos números: redibujar cortaría el arrastre.
      simulada = Number(evento.target.value);
      resultado.querySelector('[data-calc-sim-valor]').textContent = coma(simulada);
      resultado.querySelector('[data-calc-sim-final]').textContent = coma(ultimo.llevas + ultimo.pendiente / 100 * simulada, 2);
      return;
    }
    if (evento.target.matches('[data-calc-plantilla]')) return;
    raiz.querySelectorAll('[data-calc-plantilla]').forEach(function (b) { b.setAttribute('aria-pressed', 'false'); });
    calcular();
  });

  raiz.addEventListener('click', function (evento) {
    var plantilla = evento.target.closest('[data-calc-plantilla]');
    if (plantilla) { usarPlantilla(plantilla.getAttribute('data-calc-plantilla')); return; }
    if (evento.target.closest('[data-calc-agregar]')) {
      var tr = agregarFila('Evaluación ' + (filas.children.length + 1), '', null);
      tr.querySelector('input').focus();
      calcular();
      return;
    }
    var quitar = evento.target.closest('[data-calc-quitar]');
    if (quitar) {
      var fila = quitar.closest('tr');
      var siguiente = fila.nextElementSibling || fila.previousElementSibling;
      fila.remove();
      if (siguiente) siguiente.querySelector('input').focus();
      calcular();
    }
  });
})();
