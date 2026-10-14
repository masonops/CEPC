/* CEPC · Sitio público
   roomie.js — Ficha de un aviso: visor de fotos, compartir y el mapa de
   «Cómo llegar a Beauchef». */

(function () {
  'use strict';

  /* --- Visor de fotos --------------------------------------------------- */

  var visor = document.querySelector('[data-visor]');
  var pista = document.querySelector('[data-visor-pista]');
  var contador = document.querySelector('[data-visor-contador]');

  if (visor && pista && typeof visor.showModal === 'function') {
    var total = pista.children.length;
    var actual = function () { return Math.round(pista.scrollLeft / pista.clientWidth); };
    var ir = function (n, suave) {
      n = Math.max(0, Math.min(total - 1, n));
      pista.scrollTo({ left: n * pista.clientWidth, behavior: suave ? 'smooth' : 'auto' });
    };
    var pintar = function () {
      var n = actual();
      contador.textContent = (n + 1) + ' / ' + total;
      visor.classList.toggle('en-primera', n === 0);
      visor.classList.toggle('en-ultima', n === total - 1);
    };

    document.querySelectorAll('[data-abrir-foto]').forEach(function (b) {
      b.addEventListener('click', function () {
        visor.showModal();
        document.documentElement.classList.add('sin-scroll');
        ir(Number(b.getAttribute('data-abrir-foto')), false);
        pintar();
      });
    });
    pista.addEventListener('scroll', pintar, { passive: true });
    visor.querySelectorAll('[data-visor-paso]').forEach(function (b) {
      b.addEventListener('click', function () { ir(actual() + Number(b.getAttribute('data-visor-paso')), true); });
    });
    visor.querySelector('[data-visor-cerrar]').addEventListener('click', function () { visor.close(); });
    visor.addEventListener('close', function () { document.documentElement.classList.remove('sin-scroll'); });
    visor.addEventListener('keydown', function (e) {
      if (e.key === 'ArrowRight') { e.preventDefault(); ir(actual() + 1, true); }
      if (e.key === 'ArrowLeft') { e.preventDefault(); ir(actual() - 1, true); }
    });
    // Clic en el fondo (fuera de la foto) cierra.
    visor.addEventListener('click', function (e) {
      if (e.target.classList.contains('visor__foto')) visor.close();
    });
  }

  // En el teléfono la galería es un carrusel: contador «2 / 5».
  var galeria = document.querySelector('[data-galeria]');
  var contadorGaleria = document.querySelector('[data-galeria-contador]');
  if (galeria && contadorGaleria) {
    var fotos = galeria.querySelectorAll('.galeria__foto').length;
    var totalFotos = (contadorGaleria.textContent.split('/')[1] || '').trim();
    galeria.addEventListener('scroll', function () {
      var n = Math.round(galeria.scrollLeft / galeria.clientWidth);
      contadorGaleria.textContent = Math.min(fotos, n + 1) + ' / ' + totalFotos;
    }, { passive: true });
  }

  /* --- Compartir -------------------------------------------------------- */

  var compartir = document.querySelector('[data-compartir]');
  if (compartir) {
    compartir.addEventListener('click', function () {
      var datos = { title: compartir.getAttribute('data-titulo'), url: location.href };
      if (navigator.share) {
        navigator.share(datos).catch(function () {});
        return;
      }
      var texto = compartir.querySelector('[data-compartir-texto]');
      var listo = function () {
        texto.textContent = 'Enlace copiado';
        setTimeout(function () { texto.textContent = 'Compartir'; }, 2200);
      };
      if (navigator.clipboard) navigator.clipboard.writeText(location.href).then(listo, function () {});
    });
  }

  /* --- Mapa de cómo llegar ---------------------------------------------- */

  var lienzo = document.querySelector('[data-mapa-ficha]');
  var datosEl = document.getElementById('datos-ficha-mapa');
  if (lienzo && datosEl && window.RoomiesMapa && window.L) {
    var M = window.RoomiesMapa;
    var d = JSON.parse(datosEl.textContent);
    var mapa = M.crear(lienzo);
    if (mapa) {
      var punto = L.latLng(d.punto.lat, d.punto.lng);
      var beauchef = L.latLng(d.beauchef.lat, d.beauchef.lng);

      L.polyline([punto, beauchef], { color: '#004aad', weight: 3, opacity: 0.55, dashArray: '2 8', lineCap: 'round', interactive: false }).addTo(mapa);
      L.circle(punto, { radius: 140, color: '#004aad', weight: 2, fillColor: '#4d89ec', fillOpacity: 0.18, interactive: false }).addTo(mapa);
      L.marker(punto, {
        icon: L.divIcon({ className: 'pin-casa', html: '<span>Aquí</span>', iconSize: null, iconAnchor: [22, 14] }),
        keyboard: false, interactive: false
      }).addTo(mapa);
      M.beauchef(mapa, d.beauchef);
      M.estaciones(mapa, d.estaciones, d.lineas);

      var limites = L.latLngBounds([punto, beauchef]);
      d.estaciones.slice(0, 1).forEach(function (e) { limites.extend([e.lat, e.lng]); });
      mapa.fitBounds(limites.pad(0.25), { maxZoom: 16, animate: false });
    }
  }

  /* --- Vista previa: los formularios no se envían ----------------------- */

  document.querySelectorAll('[data-solo-vista]').forEach(function (f) {
    f.addEventListener('submit', function (e) { e.preventDefault(); });
  });
})();
