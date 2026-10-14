/* ==========================================================================
   CEPC · Sitio público
   calendario.js — Vista mensual del calendario.

   Lee las fechas de <script id="datos-calendario"> (las pone
   includes/secciones/calendario/mes.php) y dibuja el mes, los filtros y el
   detalle del día elegido. Recuerda el día en la dirección (?fecha=…) para
   poder compartirlo.

   Teclado: las flechas mueven el día elegido; Re Pág / Av Pág cambian de mes.
   ========================================================================== */

(function () {
  'use strict';

  var raiz = document.querySelector('[data-calendario]');
  var fuente = document.getElementById('datos-calendario');
  if (!raiz || !fuente) return;

  var datos = JSON.parse(fuente.textContent);
  raiz.hidden = false;

  var MESES = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
  var DIAS = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
  var MAX_EN_CELDA = 3;

  var titulo = raiz.querySelector('[data-cal-titulo]');
  var tituloAccesible = raiz.querySelector('[data-cal-titulo-accesible]');
  var contenedorDias = raiz.querySelector('[data-cal-dias]');
  var detalle = raiz.querySelector('[data-cal-detalle]');

  /* --- Fechas como texto «AAAA-MM-DD», sin líos de zona horaria --------- */

  function aTexto(d) {
    return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
  }

  function aFecha(texto) {
    var p = texto.split('-');
    return new Date(+p[0], +p[1] - 1, +p[2]);
  }

  function sumarDias(texto, n) {
    var d = aFecha(texto);
    d.setDate(d.getDate() + n);
    return aTexto(d);
  }

  function esc(t) {
    return String(t == null ? '' : t).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  /* --- Estado -------------------------------------------------------------- */

  var parametros = new URLSearchParams(location.search);
  var pedida = parametros.get('fecha');
  var seleccion = /^\d{4}-\d{2}-\d{2}$/.test(pedida || '') ? pedida : datos.hoy;
  var visible = aFecha(seleccion);
  visible.setDate(1);
  var filtros = {};
  Object.keys(datos.grupos).forEach(function (g) { filtros[g] = true; });

  // Eventos por día.
  var porDia = {};
  datos.eventos.forEach(function (ev) {
    (porDia[ev.fecha] = porDia[ev.fecha] || []).push(ev);
  });

  function eventosDe(fecha) {
    return (porDia[fecha] || []).filter(function (ev) { return filtros[ev.grupo]; });
  }

  /* --- Dibujo del mes -------------------------------------------------------- */

  function dibujarMes() {
    var anio = visible.getFullYear();
    var mes = visible.getMonth();
    var nombre = MESES[mes].charAt(0).toUpperCase() + MESES[mes].slice(1);
    titulo.innerHTML = nombre + ' <span>' + anio + '</span>';
    tituloAccesible.textContent = nombre + ' de ' + anio;

    // Primer lunes visible.
    var inicio = new Date(anio, mes, 1);
    var desfase = (inicio.getDay() + 6) % 7;
    inicio.setDate(1 - desfase);

    var html = '';
    var cursor = new Date(inicio);
    for (var semana = 0; semana < 6; semana++) {
      html += '<div class="calendario__fila" role="row">';
      for (var d = 0; d < 7; d++) {
        var fecha = aTexto(cursor);
        var lista = eventosDe(fecha);
        var fuera = cursor.getMonth() !== mes;
        var clases = 'dia' + (fuera ? ' dia--fuera' : '') + (fecha === datos.hoy ? ' dia--hoy' : '')
          + (fecha === seleccion ? ' dia--elegido' : '') + (lista.length ? ' dia--con-eventos' : '');
        var etiqueta = DIAS[cursor.getDay()] + ' ' + cursor.getDate() + ' de ' + MESES[cursor.getMonth()]
          + (lista.length ? ', ' + lista.length + (lista.length === 1 ? ' evento' : ' eventos') : ', sin eventos');

        html += '<div role="gridcell" class="' + clases + '">'
          + '<button type="button" class="dia__boton" data-fecha="' + fecha + '" tabindex="' + (fecha === seleccion ? '0' : '-1') + '"'
          + ' aria-label="' + esc(etiqueta) + '"' + (fecha === seleccion ? ' aria-current="date"' : '') + '>'
          + '<span class="dia__numero">' + cursor.getDate() + '</span>';

        if (lista.length) {
          html += '<span class="dia__eventos" aria-hidden="true">';
          lista.slice(0, MAX_EN_CELDA).forEach(function (ev) {
            html += '<span class="chip chip--' + ev.grupo + '"><span class="punto punto--' + ev.grupo + '"></span>'
              + (ev.hora ? '<span class="chip__hora">' + ev.hora + '</span>' : '')
              + '<span class="chip__titulo">' + esc(ev.titulo) + '</span></span>';
          });
          if (lista.length > MAX_EN_CELDA) {
            html += '<span class="chip chip--mas">+' + (lista.length - MAX_EN_CELDA) + ' más</span>';
          }
          html += '</span><span class="dia__puntos" aria-hidden="true">';
          lista.slice(0, 4).forEach(function (ev) { html += '<span class="punto punto--' + ev.grupo + '"></span>'; });
          html += '</span>';
        }
        html += '</button></div>';
        cursor.setDate(cursor.getDate() + 1);
      }
      html += '</div>';
    }
    contenedorDias.innerHTML = html;
  }

  /* --- Detalle del día --------------------------------------------------- */

  function dibujarDetalle() {
    var d = aFecha(seleccion);
    var lista = eventosDe(seleccion);
    var html = '<p class="detalle__dia">' + DIAS[d.getDay()] + '</p>'
      + '<h3 class="detalle__fecha">' + d.getDate() + ' de ' + MESES[d.getMonth()] + '</h3>';

    if (!lista.length) {
      html += '<p class="detalle__vacio">Nada programado para este día.</p>';
      // Sugerir lo que viene después del día elegido.
      var siguientes = datos.eventos.filter(function (ev) { return ev.fecha > seleccion && filtros[ev.grupo]; }).slice(0, 3);
      if (siguientes.length) {
        html += '<p class="detalle__subtitulo">Lo próximo</p><ul class="detalle__siguientes">';
        siguientes.forEach(function (ev) {
          var f = aFecha(ev.fecha);
          html += '<li><button type="button" data-fecha="' + ev.fecha + '">'
            + '<span class="punto punto--' + ev.grupo + '" aria-hidden="true"></span>'
            + '<span><strong>' + esc(ev.titulo) + '</strong>'
            + '<span>' + DIAS[f.getDay()] + ' ' + f.getDate() + ' de ' + MESES[f.getMonth()] + (ev.hora ? ', ' + ev.hora + ' h' : '') + '</span></span>'
            + '</button></li>';
        });
        html += '</ul>';
      }
    } else {
      html += '<ol class="detalle__lista">';
      lista.forEach(function (ev) {
        var meta = [ev.tipo, ev.hora ? ev.hora + (ev.horaFin ? '–' + ev.horaFin : '') + ' h' : null, ev.lugar].filter(Boolean).join(' · ');
        html += '<li class="evento-detalle" id="detalle-evento-' + ev.id + '">'
          + '<span class="evento-detalle__barra punto--' + ev.grupo + '" aria-hidden="true"></span>'
          + '<div><strong>' + esc(ev.titulo) + '</strong>'
          + '<p class="evento-detalle__meta">' + esc(meta) + '</p>'
          + (ev.resumen ? '<p class="evento-detalle__resumen">' + esc(ev.resumen) + '</p>' : '')
          + '<a class="enlace-flecha" href="' + esc(ev.ics) + '" download>Agregar al calendario</a></div>'
          + '</li>';
      });
      html += '</ol>';
    }
    detalle.innerHTML = html;
  }

  function elegir(fecha, opciones) {
    opciones = opciones || {};
    seleccion = fecha;
    var d = aFecha(fecha);
    if (d.getMonth() !== visible.getMonth() || d.getFullYear() !== visible.getFullYear()) {
      visible = new Date(d.getFullYear(), d.getMonth(), 1);
    }
    dibujarMes();
    dibujarDetalle();
    if (opciones.foco !== false) {
      var boton = contenedorDias.querySelector('[data-fecha="' + fecha + '"]');
      if (boton && opciones.foco) boton.focus();
    }
    var url = new URL(location.href);
    url.searchParams.set('fecha', fecha);
    history.replaceState(null, '', url.pathname + url.search + url.hash);
  }

  function moverMes(n) {
    var d = aFecha(seleccion);
    var destino = new Date(visible.getFullYear(), visible.getMonth() + n, 1);
    var ultimo = new Date(destino.getFullYear(), destino.getMonth() + 1, 0).getDate();
    destino.setDate(Math.min(d.getDate(), ultimo));
    elegir(aTexto(destino), { foco: false });
  }

  /* --- Eventos de la página ---------------------------------------------- */

  raiz.addEventListener('click', function (evento) {
    var dia = evento.target.closest('[data-fecha]');
    if (dia) { elegir(dia.getAttribute('data-fecha'), { foco: false }); return; }
    var mover = evento.target.closest('[data-cal-mover]');
    if (mover) { moverMes(+mover.getAttribute('data-cal-mover')); return; }
    if (evento.target.closest('[data-cal-hoy]')) { elegir(datos.hoy, { foco: false }); return; }
    var filtro = evento.target.closest('[data-cal-filtro]');
    if (filtro) {
      var g = filtro.getAttribute('data-cal-filtro');
      filtros[g] = !filtros[g];
      // Nunca dejar todo apagado: si se apaga el último, se vuelven a prender todos.
      if (!Object.keys(filtros).some(function (k) { return filtros[k]; })) {
        Object.keys(filtros).forEach(function (k) { filtros[k] = true; });
      }
      raiz.querySelectorAll('[data-cal-filtro]').forEach(function (b) {
        b.setAttribute('aria-pressed', String(filtros[b.getAttribute('data-cal-filtro')]));
      });
      dibujarMes();
      dibujarDetalle();
    }
  });

  contenedorDias.addEventListener('keydown', function (evento) {
    var pasos = { ArrowLeft: -1, ArrowRight: 1, ArrowUp: -7, ArrowDown: 7 };
    if (pasos[evento.key] !== undefined) {
      evento.preventDefault();
      elegir(sumarDias(seleccion, pasos[evento.key]), { foco: true });
    } else if (evento.key === 'PageUp' || evento.key === 'PageDown') {
      evento.preventDefault();
      moverMes(evento.key === 'PageUp' ? -1 : 1);
      var b = contenedorDias.querySelector('[data-fecha="' + seleccion + '"]');
      if (b) b.focus();
    } else if (evento.key === 'Home') {
      evento.preventDefault();
      elegir(datos.hoy, { foco: true });
    }
  });

  elegir(seleccion, { foco: false });

  // Si se llegó desde el buscador (#evento-N), resaltar ese evento.
  var ancla = location.hash.match(/^#evento-(\d+)$/);
  if (ancla) {
    var tarjeta = document.getElementById('detalle-evento-' + ancla[1]);
    if (tarjeta) {
      tarjeta.classList.add('evento-detalle--destacado');
      raiz.scrollIntoView({ block: 'start' });
    }
  }
})();
