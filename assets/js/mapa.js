/* ==========================================================================
   CEPC · Sitio público
   mapa.js — Mapa interactivo del campus.

   Partes:
     1. Vista: zoom y arrastre cambiando el viewBox del SVG (rueda, pellizco,
        arrastre, botones y teclas + / −).
     2. Edificios: elegir uno muestra su ficha con pisos y salas.
     3. Búsqueda dentro del mapa: edificios, salas y lugares.
     4. Rutas: Dijkstra sobre la red de includes/mapa/rutas.php.
     5. Comer: filtros y «abierto ahora» según el horario de cada lugar.

   Los datos llegan en <script id="datos-mapa">; las formas se leen del SVG.
   ========================================================================== */

(function () {
  'use strict';

  var lienzo = document.querySelector('[data-lienzo]');
  var svg = document.querySelector('[data-mapa-svg]');
  var panel = document.querySelector('[data-panel-mapa]');
  var fuente = document.getElementById('datos-mapa');
  if (!lienzo || !svg || !panel || !fuente) return;

  var datos = JSON.parse(fuente.textContent);
  var sinMovimiento = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var NS = 'http://www.w3.org/2000/svg';

  function esc(t) {
    return String(t == null ? '' : t).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  function normalizar(t) {
    return String(t || '').normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase().trim();
  }

  function nombrePiso(n) {
    return n < 0 ? 'Subterráneo ' + Math.abs(n) : 'Piso ' + n;
  }

  /* =======================================================================
     1. Vista: zoom y arrastre
     ======================================================================= */

  var completo = svg.viewBox.baseVal;
  var TODO = { x: completo.x, y: completo.y, w: completo.width, h: completo.height };
  var vista = Object.assign({}, TODO);
  var ANCHO_MIN = 140;
  var ANCHO_MAX = TODO.w * 1.4;

  function aplicarVista() {
    svg.setAttribute('viewBox', vista.x + ' ' + vista.y + ' ' + vista.w + ' ' + vista.h);
    ajustarTamanos();
  }

  /** Unidades del mapa por píxel de pantalla (para mantener textos y pines de tamaño fijo). */
  function escala() {
    var r = svg.getBoundingClientRect();
    if (!r.width || !r.height) return 1;
    return Math.max(vista.w / r.width, vista.h / r.height);
  }

  var etiquetas = svg.querySelectorAll('.mapa-etiquetas text');
  var nombresCalles = svg.querySelectorAll('.mapa-nombres-calles text');
  var pines = svg.querySelectorAll('.mapa-lugar');
  var entradas = svg.querySelectorAll('.mapa-entradas circle');

  /** Textos de 12 px en pantalla; se ocultan los que no caben en su edificio. */
  function ajustarTamanos() {
    var k = escala();
    etiquetas.forEach(function (t) {
      t.style.fontSize = (12 * k) + 'px';
      var id = t.getAttribute('data-etiqueta');
      var forma = formas[id];
      if (!forma) return;
      var caja = forma.getBBox();
      // El texto es horizontal: debe caber en el ancho del edificio.
      var cabe = caja.width * 1.15 / k > t.textContent.length * 6.4;
      t.style.opacity = cabe || id === elegido ? 1 : 0;
      t.classList.toggle('etiqueta--elegida', id === elegido);
    });
    nombresCalles.forEach(function (t) { t.style.fontSize = (10.5 * k) + 'px'; });
    pines.forEach(function (p) {
      var base = p.getAttribute('data-base') || p.getAttribute('transform');
      p.setAttribute('data-base', base);
      p.setAttribute('transform', base + ' scale(' + Math.min(Math.max(k, 0.35), 2.2) + ')');
    });
    entradas.forEach(function (c) { c.setAttribute('r', 5 * Math.min(Math.max(k, 0.4), 2)); });
    svg.style.setProperty('--trazo', k);
  }

  /** Ancho en píxeles que tapa el panel flotante (0 en el teléfono, donde va abajo). */
  function tapadoPorPanel() {
    if (getComputedStyle(panel).position !== 'absolute') return 0;
    return panel.getBoundingClientRect().right - svg.getBoundingClientRect().left + 12;
  }

  /** Lleva la vista a un rectángulo, con animación suave. */
  function irA(destino, rapido) {
    // El objetivo se centra en la parte visible (a la derecha del panel) y se
    // respeta la proporción del lienzo.
    var r = svg.getBoundingClientRect();
    var tapado = Math.min(tapadoPorPanel(), r.width * 0.6);
    var libre = Math.max(r.width - tapado, 1);
    var proporcion = libre / Math.max(r.height, 1);
    var w = Math.max(destino.w, ANCHO_MIN * libre / r.width);
    var h = destino.h;
    if (w / h > proporcion) h = w / proporcion; else w = h * proporcion;
    var wTotal = Math.min(w * r.width / libre, ANCHO_MAX);
    w = wTotal * libre / r.width;
    h = wTotal / (r.width / Math.max(r.height, 1));
    var corrimiento = wTotal - w;
    var fin = {
      x: destino.x + destino.w / 2 - w / 2 - corrimiento,
      y: destino.y + destino.h / 2 - h / 2,
      w: wTotal,
      h: h,
    };

    if (sinMovimiento || rapido) { vista = fin; aplicarVista(); return; }
    var inicio = Object.assign({}, vista);
    var t0 = performance.now();
    var dur = 420;
    function paso(t) {
      var p = Math.min((t - t0) / dur, 1);
      var e = 1 - Math.pow(1 - p, 3);
      vista = {
        x: inicio.x + (fin.x - inicio.x) * e, y: inicio.y + (fin.y - inicio.y) * e,
        w: inicio.w + (fin.w - inicio.w) * e, h: inicio.h + (fin.h - inicio.h) * e,
      };
      aplicarVista();
      if (p < 1) requestAnimationFrame(paso);
    }
    requestAnimationFrame(paso);
  }

  function verTodo(rapido) {
    irA(TODO, rapido);
  }

  /** Zoom manteniendo fijo el punto (px, py) del mapa. */
  function zoom(factor, px, py) {
    var w = Math.min(Math.max(vista.w * factor, ANCHO_MIN), ANCHO_MAX);
    var real = w / vista.w;
    if (px === undefined) { px = vista.x + vista.w / 2; py = vista.y + vista.h / 2; }
    vista = { x: px - (px - vista.x) * real, y: py - (py - vista.y) * real, w: w, h: vista.h * real };
    aplicarVista();
  }

  function aMapa(clientX, clientY) {
    var r = svg.getBoundingClientRect();
    var k = escala();
    // Con preserveAspectRatio «meet» el contenido queda centrado.
    var dx = (r.width - vista.w / k) / 2;
    var dy = (r.height - vista.h / k) / 2;
    return { x: vista.x + (clientX - r.left - dx) * k, y: vista.y + (clientY - r.top - dy) * k };
  }

  svg.addEventListener('wheel', function (evento) {
    evento.preventDefault();
    var p = aMapa(evento.clientX, evento.clientY);
    zoom(Math.exp(evento.deltaY * (evento.ctrlKey ? 0.01 : 0.0022)), p.x, p.y);
  }, { passive: false });

  // Arrastre y pellizco con Pointer Events.
  var punteros = new Map();
  var arrastre = null;
  var movido = false;

  svg.addEventListener('pointerdown', function (evento) {
    punteros.set(evento.pointerId, { x: evento.clientX, y: evento.clientY });
    movido = false;
    arrastre = { vista: Object.assign({}, vista), x: evento.clientX, y: evento.clientY, distancia: null };
    if (punteros.size === 2) {
      var ps = Array.from(punteros.values());
      arrastre.distancia = Math.hypot(ps[0].x - ps[1].x, ps[0].y - ps[1].y);
      arrastre.centro = aMapa((ps[0].x + ps[1].x) / 2, (ps[0].y + ps[1].y) / 2);
    }
  });

  svg.addEventListener('pointermove', function (evento) {
    if (coordenadas) {
      var c = aMapa(evento.clientX, evento.clientY);
      coordenadas.textContent = 'x ' + Math.round(c.x) + ', y ' + Math.round(c.y);
    }
    if (!arrastre || !punteros.has(evento.pointerId)) return;
    punteros.set(evento.pointerId, { x: evento.clientX, y: evento.clientY });

    if (punteros.size === 2 && arrastre.distancia) {
      var ps = Array.from(punteros.values());
      var d = Math.hypot(ps[0].x - ps[1].x, ps[0].y - ps[1].y);
      vista = Object.assign({}, arrastre.vista);
      zoom(arrastre.distancia / d, arrastre.centro.x, arrastre.centro.y);
      movido = true;
      return;
    }
    var k = escala();
    var dx = evento.clientX - arrastre.x;
    var dy = evento.clientY - arrastre.y;
    if (!movido && Math.abs(dx) + Math.abs(dy) > 4) {
      // Se captura el puntero sólo al empezar a arrastrar: si se capturara al
      // tocar, el clic llegaría al SVG y no al edificio.
      movido = true;
      try { svg.setPointerCapture(evento.pointerId); } catch (e) { /* sin captura */ }
    }
    vista = { x: arrastre.vista.x - dx * k, y: arrastre.vista.y - dy * k, w: arrastre.vista.w, h: arrastre.vista.h };
    aplicarVista();
  });

  function soltar(evento) {
    punteros.delete(evento.pointerId);
    if (punteros.size === 0) arrastre = null;
    else if (punteros.size === 1) {
      var p = Array.from(punteros.values())[0];
      arrastre = { vista: Object.assign({}, vista), x: p.x, y: p.y, distancia: null };
    }
  }
  svg.addEventListener('pointerup', soltar);
  svg.addEventListener('pointercancel', soltar);

  lienzo.addEventListener('click', function (evento) {
    var boton = evento.target.closest('[data-zoom]');
    if (!boton) return;
    var n = +boton.getAttribute('data-zoom');
    if (n === 0) verTodo(); else zoom(n > 0 ? 0.7 : 1 / 0.7);
  });

  lienzo.addEventListener('keydown', function (evento) {
    if (evento.target.closest('input, select')) return;
    if (evento.key === '+' || evento.key === '=') { zoom(0.75); evento.preventDefault(); }
    else if (evento.key === '-') { zoom(1 / 0.75); evento.preventDefault(); }
  });

  var coordenadas = lienzo.querySelector('[data-coordenadas]');

  /* =======================================================================
     2. Edificios y su ficha
     ======================================================================= */

  var formas = {};
  svg.querySelectorAll('[data-edificio]').forEach(function (g) {
    formas[g.getAttribute('data-edificio')] = g;
  });

  var ficha = panel.querySelector('[data-mapa-ficha]');
  var listaEdificios = panel.querySelector('[data-mapa-lista]');
  var resultados = panel.querySelector('[data-mapa-resultados]');
  var elegido = null;
  var pisoElegido = null;
  var salaElegida = null;

  function cajaDe(id, margen) {
    var b = formas[id].getBBox();
    margen = margen === undefined ? 60 : margen;
    return { x: b.x - margen, y: b.y - margen, w: b.width + margen * 2, h: b.height + margen * 2 };
  }

  function elegirEdificio(id, opciones) {
    opciones = opciones || {};
    var e = datos.edificios[id];
    if (!e) return;
    elegido = id;
    salaElegida = opciones.sala || null;
    Object.keys(formas).forEach(function (k) { formas[k].classList.toggle('mapa-edificio--elegido', k === id); });

    var pisos = e.pisos || [];
    if (opciones.piso !== undefined) pisoElegido = opciones.piso;
    else {
      var conSalas = pisos.filter(function (p) { return p.salas.length; });
      pisoElegido = pisos.length ? (conSalas[0] || pisos[0]).nivel : null;
    }

    dibujarFicha();
    mostrarExplorar('ficha');
    if (opciones.mover !== false) irA(cajaDe(id, 80));
    actualizarUrl({ edificio: salaElegida ? null : id, sala: salaElegida, lugar: null });
  }

  function dibujarFicha() {
    var e = datos.edificios[elegido];
    var pisos = e.pisos || [];
    var html = '<button type="button" class="ficha__volver" data-volver>' + '‹ Todos los edificios</button>'
      + '<p class="ficha__campus">Beauchef ' + esc(e.campus) + '</p>'
      + '<h2 class="ficha__nombre">' + esc(e.nombre) + '</h2>'
      + (e.descripcion ? '<p class="ficha__descripcion">' + esc(e.descripcion) + '</p>' : '');

    if (e.servicios && e.servicios.length) {
      html += '<ul class="ficha__servicios">' + e.servicios.map(function (s) { return '<li>' + esc(s) + '</li>'; }).join('') + '</ul>';
    }

    if (pisos.length) {
      html += '<p class="ficha__subtitulo">Pisos</p><div class="ficha__pisos" role="group" aria-label="Elegir piso">';
      pisos.slice().sort(function (a, b) { return b.nivel - a.nivel; }).forEach(function (p) {
        html += '<button type="button" data-piso="' + p.nivel + '" aria-pressed="' + (p.nivel === pisoElegido) + '" aria-label="' + nombrePiso(p.nivel) + '">'
          + (p.nivel < 0 ? 'S' + Math.abs(p.nivel) : p.nivel) + '</button>';
      });
      html += '</div>';
      var piso = pisos.filter(function (p) { return p.nivel === pisoElegido; })[0];
      html += '<p class="ficha__subtitulo">' + (piso ? nombrePiso(piso.nivel) : '') + '</p>';
      if (piso && piso.salas.length) {
        html += '<ul class="ficha__salas">';
        piso.salas.forEach(function (s) {
          html += '<li class="' + (s.codigo === salaElegida ? 'ficha__sala--elegida' : '') + '"><span class="ficha__sala-icono">'
            + (s.tipo === 'auditorio' ? '◐' : s.tipo === 'laboratorio' ? '◇' : '▢') + '</span>'
            + '<span><strong>' + esc(s.nombre) + '</strong><span>' + esc(tipoSala(s.tipo))
            + (s.capacidad ? ' · ' + s.capacidad + ' personas' : '') + '</span></span></li>';
        });
        html += '</ul>';
      } else {
        html += '<p class="ficha__vacio">Aún no cargamos las salas de este piso.</p>';
      }
    }

    if (e.puerta) {
      html += '<button type="button" class="boton boton--primario ficha__ruta" data-ruta-a="' + esc(elegido) + '">'
        + 'Cómo llegar</button>';
    }
    ficha.innerHTML = html;
  }

  function tipoSala(t) {
    return { sala: 'Sala de clases', auditorio: 'Auditorio', laboratorio: 'Laboratorio', oficina: 'Oficina', servicio: 'Servicio' }[t] || 'Sala';
  }

  function soltarEdificio() {
    elegido = null;
    salaElegida = null;
    Object.keys(formas).forEach(function (k) { formas[k].classList.remove('mapa-edificio--elegido'); });
    mostrarExplorar('lista');
    ajustarTamanos();
    actualizarUrl({ edificio: null, sala: null });
  }

  function mostrarExplorar(que) {
    ficha.hidden = que !== 'ficha';
    resultados.hidden = que !== 'resultados';
    listaEdificios.hidden = que !== 'lista';
  }

  svg.addEventListener('click', function (evento) {
    if (movido) return;
    var g = evento.target.closest('[data-edificio]');
    if (g) { cambiarPestana('explorar'); elegirEdificio(g.getAttribute('data-edificio')); return; }
    var pin = evento.target.closest('[data-lugar]');
    if (pin) { cambiarPestana('comer'); elegirLugar(pin.getAttribute('data-lugar')); }
  });

  svg.addEventListener('keydown', function (evento) {
    if (evento.key !== 'Enter' && evento.key !== ' ') return;
    var g = evento.target.closest('[data-edificio]');
    var pin = evento.target.closest('[data-lugar]');
    if (g) { evento.preventDefault(); cambiarPestana('explorar'); elegirEdificio(g.getAttribute('data-edificio')); }
    else if (pin) { evento.preventDefault(); cambiarPestana('comer'); elegirLugar(pin.getAttribute('data-lugar')); }
  });

  panel.addEventListener('click', function (evento) {
    var ir = evento.target.closest('[data-ir-edificio]');
    if (ir) {
      evento.preventDefault(); // el <details> es sólo para quien no tiene JavaScript
      elegirEdificio(ir.getAttribute('data-ir-edificio'));
      return;
    }
    if (evento.target.closest('[data-volver]')) { soltarEdificio(); verTodo(); return; }
    var piso = evento.target.closest('[data-piso]');
    if (piso) { pisoElegido = +piso.getAttribute('data-piso'); dibujarFicha(); return; }
    var ruta = evento.target.closest('[data-ruta-a]');
    if (ruta) {
      cambiarPestana('ruta');
      selHasta.value = 'e:' + ruta.getAttribute('data-ruta-a');
      calcularRuta();
      return;
    }
    var res = evento.target.closest('[data-resultado]');
    if (res) { abrirResultado(JSON.parse(res.getAttribute('data-resultado'))); return; }
    var lugar = evento.target.closest('[data-ir-lugar]');
    if (lugar) { elegirLugar(lugar.getAttribute('data-ir-lugar')); return; }
    var pest = evento.target.closest('[data-pestana]');
    if (pest) { cambiarPestana(pest.getAttribute('data-pestana')); return; }
    var filtro = evento.target.closest('[data-filtro-comida]');
    if (filtro) {
      filtro.setAttribute('aria-pressed', String(filtro.getAttribute('aria-pressed') !== 'true'));
      filtrarComida();
    }
  });

  /* =======================================================================
     3. Búsqueda dentro del mapa
     ======================================================================= */

  var buscador = panel.querySelector('[data-mapa-buscar]');
  var indice = [];
  Object.keys(datos.edificios).forEach(function (id) {
    var e = datos.edificios[id];
    indice.push({ tipo: 'edificio', id: id, titulo: e.nombre, detalle: 'Beauchef ' + e.campus, texto: normalizar(e.nombre + ' ' + e.corto) });
    (e.pisos || []).forEach(function (p) {
      p.salas.forEach(function (s) {
        indice.push({ tipo: 'sala', id: id, sala: s.codigo, piso: p.nivel, titulo: s.nombre,
          detalle: e.corto + ', ' + nombrePiso(p.nivel).toLowerCase(), texto: normalizar(s.codigo + ' ' + s.nombre) });
      });
    });
  });
  Object.keys(datos.lugares).forEach(function (id) {
    var l = datos.lugares[id];
    indice.push({ tipo: 'lugar', id: id, titulo: l.nombre, detalle: 'Para comer', texto: normalizar(l.nombre + ' ' + (l.etiquetas || []).join(' ') + ' comer') });
  });

  function buscarEnMapa() {
    var q = normalizar(buscador.value);
    if (!q) {
      if (elegido) mostrarExplorar('ficha'); else mostrarExplorar('lista');
      return;
    }
    cambiarPestana('explorar');
    var palabras = q.split(/\s+/);
    var hallados = indice.filter(function (i) {
      return palabras.every(function (p) { return i.texto.indexOf(p) !== -1; });
    }).slice(0, 12);
    var html = '<p class="panel-mapa__grupo">Resultados</p>';
    if (!hallados.length) html += '<p class="ficha__vacio">No encontramos «' + esc(buscador.value) + '» en el mapa.</p>';
    else {
      html += '<ul class="lista-resultados">';
      hallados.forEach(function (h) {
        html += '<li><button type="button" data-resultado="' + esc(JSON.stringify(h)) + '">'
          + '<strong>' + esc(h.titulo) + '</strong><span>' + esc(h.detalle) + '</span></button></li>';
      });
      html += '</ul>';
    }
    resultados.innerHTML = html;
    mostrarExplorar('resultados');
  }

  function abrirResultado(h) {
    buscador.value = '';
    if (h.tipo === 'lugar') { cambiarPestana('comer'); elegirLugar(h.id); }
    else if (h.tipo === 'sala') elegirEdificio(h.id, { piso: h.piso, sala: h.sala });
    else elegirEdificio(h.id);
  }

  buscador.addEventListener('input', buscarEnMapa);
  buscador.addEventListener('keydown', function (evento) {
    if (evento.key === 'Enter') {
      evento.preventDefault();
      var primero = resultados.querySelector('[data-resultado]');
      if (primero) primero.click();
    }
  });

  /* =======================================================================
     4. Rutas
     ======================================================================= */

  var selDesde = panel.querySelector('[data-ruta-desde]');
  var selHasta = panel.querySelector('[data-ruta-hasta]');
  var resultadoRuta = panel.querySelector('[data-ruta-resultado]');
  var capaRuta = svg.querySelector('[data-mapa-ruta]');

  // Grafo no dirigido con el largo de cada tramo.
  var vecinos = {};
  datos.aristas.forEach(function (a) {
    var n1 = datos.nodos[a[0]], n2 = datos.nodos[a[1]];
    var d = Math.hypot(n1.x - n2.x, n1.y - n2.y);
    (vecinos[a[0]] = vecinos[a[0]] || []).push([a[1], d]);
    (vecinos[a[1]] = vecinos[a[1]] || []).push([a[0], d]);
  });

  /** Camino más corto (Dijkstra). Devuelve la lista de nodos o null. */
  function camino(desde, hasta) {
    var dist = {}, previo = {}, pendientes = new Set(Object.keys(datos.nodos));
    Object.keys(datos.nodos).forEach(function (n) { dist[n] = Infinity; });
    dist[desde] = 0;
    while (pendientes.size) {
      var actual = null;
      pendientes.forEach(function (n) { if (actual === null || dist[n] < dist[actual]) actual = n; });
      if (dist[actual] === Infinity) break;
      pendientes.delete(actual);
      if (actual === hasta) break;
      (vecinos[actual] || []).forEach(function (v) {
        var alt = dist[actual] + v[1];
        if (alt < dist[v[0]]) { dist[v[0]] = alt; previo[v[0]] = actual; }
      });
    }
    if (dist[hasta] === Infinity) return null;
    var lista = [hasta];
    while (lista[0] !== desde) lista.unshift(previo[lista[0]]);
    return { nodos: lista, largo: dist[hasta] };
  }

  function nombreNodo(id) {
    var n = datos.nodos[id];
    if (n.tipo === 'entrada') return n.nombre;
    if (n.edificio && datos.edificios[n.edificio]) return datos.edificios[n.edificio].nombre;
    return '';
  }

  function calcularRuta() {
    var desde = selDesde.value;
    var destino = selHasta.value;
    var edificioId, sala = null, piso = null;
    if (destino.indexOf('s:') === 0) {
      var codigo = destino.slice(2);
      Object.keys(datos.edificios).some(function (id) {
        return (datos.edificios[id].pisos || []).some(function (p) {
          return p.salas.some(function (s) {
            if (s.codigo === codigo) { edificioId = id; sala = s; piso = p.nivel; return true; }
            return false;
          });
        });
      });
    } else {
      edificioId = destino.slice(2);
    }
    var e = datos.edificios[edificioId];
    if (!e || !e.puerta) return;

    var r = camino(desde, e.puerta);
    capaRuta.innerHTML = '';
    if (!r) {
      resultadoRuta.innerHTML = '<p class="ficha__vacio">No encontramos un camino entre esos puntos.</p>';
      return;
    }

    // Línea de la ruta.
    var puntos = r.nodos.map(function (id) { return datos.nodos[id].x + ',' + datos.nodos[id].y; }).join(' ');
    var linea = document.createElementNS(NS, 'polyline');
    linea.setAttribute('points', puntos);
    linea.setAttribute('class', 'mapa-ruta__linea');
    capaRuta.appendChild(linea);
    [r.nodos[0], r.nodos[r.nodos.length - 1]].forEach(function (id, i) {
      var c = document.createElementNS(NS, 'circle');
      c.setAttribute('cx', datos.nodos[id].x);
      c.setAttribute('cy', datos.nodos[id].y);
      c.setAttribute('r', 7);
      c.setAttribute('class', i ? 'mapa-ruta__fin' : 'mapa-ruta__inicio');
      capaRuta.appendChild(c);
    });
    if (!sinMovimiento) {
      var largo = linea.getTotalLength();
      linea.style.strokeDasharray = largo;
      linea.style.strokeDashoffset = largo;
      requestAnimationFrame(function () { linea.style.strokeDashoffset = 0; });
    }

    Object.keys(formas).forEach(function (k) { formas[k].classList.toggle('mapa-edificio--elegido', k === edificioId); });
    elegido = edificioId;

    var metros = Math.round(r.largo * datos.metros / 10) * 10;
    var minutos = Math.max(1, Math.round(metros / 75)); // 75 m por minuto, paso tranquilo
    var pasos = ['Sales desde <strong>' + esc(nombreNodo(r.nodos[0])) + '</strong>.'];
    var cruzaCalle = r.nodos.indexOf('acceso-850') !== -1 && r.nodos.indexOf('acceso-851') !== -1;
    if (cruzaCalle) pasos.push('Cruza Beauchef entre el 850 y el 851.');
    pasos.push('Camina unos ' + metros + ' m.');
    pasos.push('Llegas a <strong>' + esc(e.nombre) + '</strong>.');
    if (sala) pasos.push((piso === 1 ? 'La sala está en el primer piso' : 'Ve al ' + nombrePiso(piso).toLowerCase()) + ': <strong>' + esc(sala.nombre) + '</strong>.');

    resultadoRuta.innerHTML = '<p class="ruta-resultado__tiempo"><strong>≈ ' + minutos + ' min</strong> caminando · ' + metros + ' m</p>'
      + '<ol class="ruta-resultado__pasos">' + pasos.map(function (p) { return '<li>' + p + '</li>'; }).join('') + '</ol>';

    // Encuadrar toda la ruta.
    var xs = r.nodos.map(function (id) { return datos.nodos[id].x; });
    var ys = r.nodos.map(function (id) { return datos.nodos[id].y; });
    var minX = Math.min.apply(null, xs), maxX = Math.max.apply(null, xs);
    var minY = Math.min.apply(null, ys), maxY = Math.max.apply(null, ys);
    irA({ x: minX - 80, y: minY - 80, w: maxX - minX + 160, h: maxY - minY + 160 });
    actualizarUrl({ desde: desde, hasta: destino, edificio: null, sala: null, lugar: null });
  }

  panel.querySelector('[data-ruta-form]').addEventListener('change', calcularRuta);
  panel.querySelector('[data-ruta-invertir]').addEventListener('click', function () {
    // Sólo se puede invertir si el destino es un edificio (las salas no son punto de partida).
    var hasta = selHasta.value;
    if (hasta.indexOf('e:') !== 0) return;
    var e = datos.edificios[hasta.slice(2)];
    var desdeNodo = datos.nodos[selDesde.value];
    var nuevoHasta = desdeNodo.edificio ? 'e:' + desdeNodo.edificio : null;
    if (!nuevoHasta || !e.puerta) return;
    selDesde.value = e.puerta;
    selHasta.value = nuevoHasta;
    calcularRuta();
  });

  /* =======================================================================
     5. Comer
     ======================================================================= */

  var DIAS = ['dom', 'lun', 'mar', 'mie', 'jue', 'vie', 'sab'];

  /** { abierto: bool, texto: 'Abierto hasta las 17:00' } según la hora actual. */
  function estadoLugar(l) {
    if (!l.horario) return { abierto: null, texto: 'Horario por confirmar' };
    var ahora = new Date();
    var hoy = l.horario[DIAS[ahora.getDay()]];
    var minutos = ahora.getHours() * 60 + ahora.getMinutes();
    if (!hoy) return { abierto: false, texto: 'Cerrado hoy' };
    var p = hoy.split('-');
    var abre = p[0].split(':'), cierra = p[1].split(':');
    var a = +abre[0] * 60 + +abre[1], c = +cierra[0] * 60 + +cierra[1];
    if (minutos >= a && minutos < c) return { abierto: true, texto: 'Abierto hasta las ' + p[1] };
    if (minutos < a) return { abierto: false, texto: 'Abre hoy a las ' + p[0] };
    return { abierto: false, texto: 'Cerrado · hoy fue ' + p[0] + '–' + p[1] };
  }

  panel.querySelectorAll('[data-horario-de]').forEach(function (span) {
    var l = datos.lugares[span.getAttribute('data-horario-de')];
    var est = estadoLugar(l);
    span.textContent = est.texto;
    span.classList.toggle('lugar__horario--abierto', est.abierto === true);
  });

  var vacioComida = panel.querySelector('[data-lugares-vacio]');

  function filtrarComida() {
    var activos = Array.prototype.filter.call(panel.querySelectorAll('[data-filtro-comida]'), function (b) {
      return b.getAttribute('aria-pressed') === 'true';
    }).map(function (b) { return b.getAttribute('data-filtro-comida'); });
    var visibles = 0;
    panel.querySelectorAll('[data-ir-lugar]').forEach(function (b) {
      var id = b.getAttribute('data-ir-lugar');
      var l = datos.lugares[id];
      var etiquetasLugar = l.etiquetas || [];
      var ok = activos.every(function (f) {
        return f === 'abierto' ? estadoLugar(l).abierto === true : etiquetasLugar.indexOf(f) !== -1;
      });
      b.parentElement.hidden = !ok;
      var pin = svg.querySelector('[data-lugar="' + id + '"]');
      if (pin) pin.classList.toggle('mapa-lugar--oculto', !ok);
      if (ok) visibles++;
    });
    vacioComida.hidden = visibles > 0;
  }

  function elegirLugar(id) {
    var l = datos.lugares[id];
    if (!l) return;
    svg.querySelectorAll('.mapa-lugar').forEach(function (p) {
      p.classList.toggle('mapa-lugar--elegido', p.getAttribute('data-lugar') === id);
    });
    panel.querySelectorAll('[data-ir-lugar]').forEach(function (b) {
      b.classList.toggle('lugar--elegido', b.getAttribute('data-ir-lugar') === id);
    });
    irA({ x: l.x - 110, y: l.y - 90, w: 220, h: 180 });
    var boton = panel.querySelector('[data-ir-lugar="' + id + '"]');
    if (boton) boton.scrollIntoView({ block: 'nearest' });
    actualizarUrl({ lugar: id, edificio: null, sala: null, desde: null, hasta: null });
  }

  /* =======================================================================
     Pestañas, dirección y arranque
     ======================================================================= */

  function cambiarPestana(nombre) {
    panel.querySelectorAll('[data-pestana]').forEach(function (b) {
      var si = b.getAttribute('data-pestana') === nombre;
      b.setAttribute('aria-selected', String(si));
      b.setAttribute('aria-pressed', String(si));
    });
    panel.querySelectorAll('[data-panel]').forEach(function (p) {
      p.hidden = p.getAttribute('data-panel') !== nombre;
    });
    lienzo.classList.toggle('lienzo--comer', nombre === 'comer');
    if (nombre !== 'ruta') capaRuta.innerHTML = '';
    if (nombre === 'ruta' && !capaRuta.childNodes.length) calcularRuta();
  }

  function actualizarUrl(cambios) {
    var url = new URL(location.href);
    Object.keys(cambios).forEach(function (k) {
      if (cambios[k] === null || cambios[k] === undefined) url.searchParams.delete(k);
      else url.searchParams.set(k, cambios[k]);
    });
    history.replaceState(null, '', url.pathname + url.search);
  }

  // Panel listo para JavaScript: la lista se vuelve botones y aparecen las fichas.
  panel.classList.add('panel-mapa--js');
  filtrarComida();
  aplicarVista();

  var q = new URLSearchParams(location.search);
  requestAnimationFrame(function () {
    if (q.get('sala')) {
      var s = indice.filter(function (i) { return i.tipo === 'sala' && normalizar(i.sala) === normalizar(q.get('sala')); })[0];
      if (s) abrirResultado(s); else { buscador.value = q.get('sala'); buscarEnMapa(); }
    } else if (q.get('edificio') && datos.edificios[q.get('edificio')]) {
      elegirEdificio(q.get('edificio'));
    } else if (q.get('lugar') && datos.lugares[q.get('lugar')]) {
      cambiarPestana('comer');
      elegirLugar(q.get('lugar'));
    } else if (q.get('hasta')) {
      if (q.get('desde') && datos.nodos[q.get('desde')]) selDesde.value = q.get('desde');
      var hasta = q.get('hasta');
      if (hasta.indexOf(':') === -1) hasta = 'e:' + hasta;
      selHasta.value = hasta;
      cambiarPestana('ruta');
    } else if (q.get('buscar')) {
      buscador.value = q.get('buscar');
      buscarEnMapa();
    } else {
      verTodo(true);
    }
  });

  window.addEventListener('resize', function () { aplicarVista(); });
})();
