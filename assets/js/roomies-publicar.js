/* CEPC · Sitio público
   roomies-publicar.js — Formulario para publicar en Roomies:
   · Mapa para marcar el lugar (clic o búsqueda de la calle) y cálculo en
     vivo de la distancia a Beauchef y el metro más cercano.
   · Fotos: vista previa, quitar, y se achican a 1600 px antes de subir
     (así funcionan aunque el servidor acepte archivos chicos).
   · Contadores de letras, total del precio e índice que sigue al scroll. */

(function () {
  'use strict';

  var form = document.querySelector('[data-publicar]');
  if (!form) return;

  /* --- Vista previa: no se envía --------------------------------------- */

  if (form.hasAttribute('data-solo-vista')) {
    form.addEventListener('submit', function (e) { e.preventDefault(); });
  }

  /* --- Contadores de letras -------------------------------------------- */

  form.querySelectorAll('[data-contador]').forEach(function (campo) {
    var max = Number(campo.getAttribute('maxlength'));
    var contador = document.createElement('span');
    contador.className = 'contador';
    contador.setAttribute('aria-hidden', 'true');
    campo.insertAdjacentElement('afterend', contador);
    var pintar = function () {
      contador.textContent = campo.value.length + ' / ' + max;
      contador.classList.toggle('contador--cerca', campo.value.length > max * 0.9);
    };
    campo.addEventListener('input', pintar);
    pintar();
  });

  /* --- Total del precio ------------------------------------------------- */

  var precio = form.querySelector('[data-precio]');
  var gastos = form.querySelector('[data-gastos]');
  var incluyeGastos = form.querySelector('[data-incluye-gastos]');
  var total = form.querySelector('[data-total]');
  var formato = new Intl.NumberFormat('es-CL');
  function pintarTotal() {
    var p = Number(precio.value) || 0;
    var g = incluyeGastos && incluyeGastos.checked ? 0 : (Number(gastos.value) || 0);
    total.hidden = p < 1000;
    total.querySelector('strong').textContent = '$' + formato.format(p + g) + (g === 0 && p > 0 && incluyeGastos.checked ? ' (gastos incluidos)' : '');
  }
  if (precio && gastos && total) {
    [precio, gastos, incluyeGastos].forEach(function (el) { el && el.addEventListener('input', pintarTotal); });
    if (incluyeGastos) incluyeGastos.addEventListener('change', pintarTotal);
    pintarTotal();
  }

  /* --- Correo de la Universidad ---------------------------------------- */

  var correo = form.querySelector('[data-correo-uchile]');
  if (correo) {
    var dominios = correo.getAttribute('data-correo-uchile').split(',');
    correo.addEventListener('blur', function () {
      var dominio = (correo.value.split('@')[1] || '').toLowerCase();
      correo.setCustomValidity(correo.value && dominios.indexOf(dominio) === -1 ? 'Usa tu correo de la Universidad (@' + dominios.join(' o @') + ').' : '');
      correo.closest('.campo').classList.toggle('campo--error', !!correo.validationMessage && correo.value !== '');
    });
  }

  /* --- Mapa para ubicar -------------------------------------------------- */

  var lienzo = form.querySelector('[data-ubicador]');
  var datosEl = document.getElementById('datos-ubicador');
  var lat = form.querySelector('[data-lat]');
  var lng = form.querySelector('[data-lng]');
  var resultado = form.querySelector('[data-ubicador-resultado]');

  if (lienzo && datosEl && window.RoomiesMapa && window.L) {
    var M = window.RoomiesMapa;
    var d = JSON.parse(datosEl.textContent);
    var mapa = M.crear(lienzo);
    var marca = null;
    M.beauchef(mapa, d.beauchef);

    var describir = function (punto) {
      var metros = Math.round(M.metros(punto.lat, punto.lng, d.beauchef.lat, d.beauchef.lng) * 1.25);
      var cercana = null;
      d.estaciones.forEach(function (e) {
        var m = M.metros(punto.lat, punto.lng, e.lat, e.lng) * 1.25;
        if (!cercana || m < cercana.m) cercana = { e: e, m: m };
      });
      var texto = '<strong>' + Math.max(1, Math.ceil(metros / 80)) + ' min a pie</strong> de Beauchef';
      if (cercana) {
        texto += ' · Metro ' + cercana.e.lineas.map(function (l) { return M.lineaHtml(l, d.lineas); }).join('') +
          ' ' + M.escapar(cercana.e.nombre) + ' a ' + Math.max(1, Math.ceil(cercana.m / 80)) + ' min';
      }
      resultado.innerHTML = texto;
    };

    var poner = function (punto, centrar) {
      if (!marca) {
        marca = L.marker(punto, {
          draggable: true,
          icon: L.divIcon({ className: 'pin-casa', html: '<span>Aquí</span>', iconSize: null, iconAnchor: [22, 14] })
        }).addTo(mapa);
        marca.on('dragend', function () { poner(marca.getLatLng(), false); });
      }
      marca.setLatLng(punto);
      lat.value = punto.lat.toFixed(6);
      lng.value = punto.lng.toFixed(6);
      lienzo.closest('.campo').classList.remove('campo--error');
      describir(punto);
      if (centrar) mapa.setView(punto, 16);
    };

    if (lat.value && lng.value) {
      poner(L.latLng(Number(lat.value), Number(lng.value)), true);
    } else {
      mapa.setView([d.beauchef.lat, d.beauchef.lng], 14);
    }
    mapa.on('click', function (e) { poner(e.latlng, false); });

    // Buscar la calle (OpenStreetMap Nominatim; sólo al apretar el botón).
    var buscar = form.querySelector('[data-buscar-direccion]');
    buscar.addEventListener('click', function () {
      var calle = form.querySelector('[data-calle]').value.trim();
      var comuna = form.querySelector('[data-comuna]').value;
      if (!calle && !comuna) {
        resultado.textContent = 'Escribe la calle y elige la comuna primero.';
        return;
      }
      resultado.textContent = 'Buscando…';
      buscar.disabled = true;
      var q = [calle, comuna, 'Región Metropolitana', 'Chile'].filter(Boolean).join(', ');
      fetch('https://nominatim.openstreetmap.org/search?format=json&limit=1&countrycodes=cl&viewbox=-70.95,-33.25,-70.45,-33.75&bounded=1&q=' + encodeURIComponent(q), {
        headers: { 'Accept-Language': 'es' }
      })
        .then(function (r) { return r.json(); })
        .then(function (lista) {
          if (!lista.length) {
            resultado.textContent = 'No la encontramos. Toca el mapa para marcarla a mano.';
            return;
          }
          poner(L.latLng(Number(lista[0].lat), Number(lista[0].lon)), true);
        })
        .catch(function () { resultado.textContent = 'No pudimos buscar. Toca el mapa para marcarla a mano.'; })
        .then(function () { setTimeout(function () { buscar.disabled = false; }, 1200); });
    });
  }

  /* --- Fotos ------------------------------------------------------------ */

  var zona = form.querySelector('[data-fotos]');
  var entrada = form.querySelector('[data-fotos-input]');
  var listaFotos = form.querySelector('[data-fotos-lista]');
  var archivos = [];
  var max = zona ? Number(zona.getAttribute('data-max')) || 8 : 8;
  var procesando = 0;
  var puedeTransferir = (function () {
    try { return !!new DataTransfer(); } catch (e) { return false; }
  })();

  // Achica a 1600 px y convierte a JPEG; si algo falla, deja el original.
  function achicar(archivo) {
    return new Promise(function (listo) {
      if (!/^image\//.test(archivo.type) || !window.createImageBitmap) return listo(archivo);
      createImageBitmap(archivo, { imageOrientation: 'from-image' }).then(function (img) {
        var escala = Math.min(1, 1600 / Math.max(img.width, img.height));
        var lienzoFoto = document.createElement('canvas');
        lienzoFoto.width = Math.round(img.width * escala);
        lienzoFoto.height = Math.round(img.height * escala);
        lienzoFoto.getContext('2d').drawImage(img, 0, 0, lienzoFoto.width, lienzoFoto.height);
        lienzoFoto.toBlob(function (blob) {
          if (!blob) return listo(archivo);
          listo(new File([blob], archivo.name.replace(/\.[^.]+$/, '') + '.jpg', { type: 'image/jpeg' }));
        }, 'image/jpeg', 0.86);
      }, function () { listo(archivo); });
    });
  }

  function sincronizar() {
    if (puedeTransferir) {
      var dt = new DataTransfer();
      archivos.forEach(function (a) { dt.items.add(a.archivo); });
      entrada.files = dt.files;
    }
    listaFotos.innerHTML = '';
    archivos.forEach(function (a, i) {
      var li = document.createElement('li');
      li.className = 'fotos-subir__item';
      li.innerHTML = '<img alt="" src="' + a.url + '">' + (i === 0 ? '<span class="fotos-subir__portada">Portada</span>' : '') +
        '<button type="button" class="fotos-subir__quitar" aria-label="Quitar foto ' + (i + 1) + '">×</button>' +
        (i > 0 ? '<button type="button" class="fotos-subir__primera">Usar de portada</button>' : '');
      li.querySelector('.fotos-subir__quitar').addEventListener('click', function () {
        URL.revokeObjectURL(a.url);
        archivos.splice(i, 1);
        sincronizar();
      });
      var primera = li.querySelector('.fotos-subir__primera');
      if (primera) primera.addEventListener('click', function () {
        archivos.unshift(archivos.splice(i, 1)[0]);
        sincronizar();
      });
      listaFotos.appendChild(li);
    });
    zona.classList.toggle('fotos-subir--lleno', archivos.length >= max);
  }

  if (zona && entrada) {
    entrada.addEventListener('change', function () {
      if (!puedeTransferir) return; // sin DataTransfer: se suben tal cual
      var nuevos = Array.prototype.slice.call(entrada.files).slice(0, max - archivos.length);
      procesando += nuevos.length;
      zona.classList.add('fotos-subir--procesando');
      Promise.all(nuevos.map(achicar)).then(function (listos) {
        listos.forEach(function (f) { archivos.push({ archivo: f, url: URL.createObjectURL(f) }); });
        procesando -= nuevos.length;
        zona.classList.toggle('fotos-subir--procesando', procesando > 0);
        sincronizar();
      });
    });
  }

  /* --- Envío ------------------------------------------------------------ */

  var enviar = form.querySelector('[data-publicar-enviar]');
  form.addEventListener('submit', function (e) {
    if (form.hasAttribute('data-solo-vista')) return;
    if (procesando > 0) { e.preventDefault(); return; }
    enviar.disabled = true;
    enviar.lastChild.textContent = 'Enviando…';
  });

  var errores = document.querySelector('[data-errores]');
  if (errores) errores.focus();

  /* --- Índice que sigue al scroll ---------------------------------------- */

  var enlaces = document.querySelectorAll('[data-indice]');
  if ('IntersectionObserver' in window && enlaces.length) {
    var observador = new IntersectionObserver(function (entradas) {
      entradas.forEach(function (en) {
        if (!en.isIntersecting) return;
        var id = en.target.getAttribute('data-parte');
        enlaces.forEach(function (a) {
          if (a.getAttribute('data-indice') === id) a.setAttribute('aria-current', 'step');
          else a.removeAttribute('aria-current');
        });
      });
    }, { rootMargin: '-35% 0px -60% 0px' });
    form.querySelectorAll('[data-parte]').forEach(function (p) { observador.observe(p); });
  }
})();
