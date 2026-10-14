"""
CEPC · Sitio público
herramientas/trazar-mapa.py — Calca los edificios del plano de colores de
Beauchef 850 (referencias/mapas/beauchef-850-colores.png) y escribe
includes/mapa/geometria.php con los polígonos listos para el mapa.

El plano original tiene el norte hacia la izquierda; aquí se gira 90° a la
derecha para dejar el norte arriba: Beauchef 851 queda a la izquierda
(poniente) y Beauchef 850 a la derecha (oriente).

Sólo hace falta correrlo si cambia el plano de referencia:

    python3 -m venv /tmp/venv && /tmp/venv/bin/pip install opencv-python-headless numpy
    /tmp/venv/bin/python herramientas/trazar-mapa.py

Los nombres, pisos y salas NO van aquí: están en includes/mapa/edificios.php.
"""

import os
import cv2
import numpy as np

RAIZ = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
PLANO = os.path.join(RAIZ, 'referencias', 'mapas', 'beauchef-850-colores.png')
SALIDA = os.path.join(RAIZ, 'includes', 'mapa', 'geometria.php')

ALTO_PLANO = 1049          # alto del PNG original (pasa a ser el ancho al girar)
DX, DY = 30, 110           # margen para calles y alrededores
ANCHO_MAPA, ALTO_MAPA = 1110, 730


def girar(x, y):
    """Coordenada del plano original -> coordenada del mapa (norte arriba)."""
    return round(ALTO_PLANO - y + DX, 1), round(x + DY, 1)


def poligono(puntos):
    return ' '.join('%g,%g' % girar(x, y) for x, y in puntos)


def rectangulo(x0, y0, x1, y1):
    return poligono([(x0, y0), (x1, y0), (x1, y1), (x0, y1)])


imagen = cv2.imread(PLANO)
rgb = cv2.cvtColor(imagen, cv2.COLOR_BGR2RGB).astype(int)


def contornos_de(hexa, tolerancia=38, area_minima=300):
    color = np.array([int(hexa[i:i + 2], 16) for i in (0, 2, 4)])
    mascara = (np.sqrt(((rgb - color) ** 2).sum(-1)) < tolerancia).astype(np.uint8) * 255
    mascara = cv2.morphologyEx(mascara, cv2.MORPH_CLOSE, np.ones((7, 7), np.uint8))
    contornos, _ = cv2.findContours(mascara, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)
    salida = []
    for c in contornos:
        if cv2.contourArea(c) < area_minima:
            continue
        aprox = cv2.approxPolyDP(c, 1.6, True).reshape(-1, 2)
        x, y, w, h = cv2.boundingRect(c)
        salida.append({'puntos': [tuple(p) for p in aprox], 'caja': (x, y, w, h), 'area': cv2.contourArea(c)})
    return salida


def el_mayor(hexa):
    return max(contornos_de(hexa), key=lambda c: c['area'])['puntos']


def por_caja(hexa, x_min, x_max):
    for c in contornos_de(hexa):
        if x_min <= c['caja'][0] <= x_max:
            return c['puntos']
    raise SystemExit('No encontré el color %s entre x=%d y x=%d' % (hexa, x_min, x_max))


# --- Edificios de Beauchef 850 (calcados por color) --------------------------
edificios = {
    'geologia':      poligono(el_mayor('c06830')),
    'anexo-idiem':   poligono(el_mayor('f07020')),
    'idiem':         poligono(el_mayor('c02058')),
    'civil':         poligono(el_mayor('007880')),
    'electrica':     poligono(el_mayor('8088b0')),
    'torre-central': poligono(el_mayor('003860')),
    'fisica':        poligono(el_mayor('c0d018')),
    'minas':         poligono(el_mayor('28b050')),
    'escuela':       poligono(el_mayor('f02020')),
    'biblioteca':    poligono(por_caja('f06850', 140, 200)),
    'hall-sur':      poligono(por_caja('f06850', 400, 430)),
    'cafeteria':     rectangulo(272, 677, 349, 709),
    # Casino: al otro lado de Blanco Encalada; se corre hacia el norte para
    # dejar espacio a la calle.
    'casino':        poligono([(x - 70, y) for x, y in [(38, 600), (75, 600), (75, 803), (38, 803)]]),
    # --- Beauchef 851 (bloque verde del plano, separado por alas) ----------
    'b851-norte':    rectangulo(109, 888, 154, 1039),
    'b851-oriente':  rectangulo(155, 888, 433, 935),
    'b851-poniente': rectangulo(155, 991, 521, 1039),
    'etigny':        rectangulo(432, 939, 521, 986),
}

# Espacios abiertos (sin pisos ni salas).
espacios = {
    'cancha': rectangulo(260, 598, 355, 640),
}

# Líneas de los patios centrales (los grises del plano), como trazos finos.
gris = ((abs(rgb[:, :, 0] - rgb[:, :, 1]) < 8) & (abs(rgb[:, :, 1] - rgb[:, :, 2]) < 8)
        & (rgb[:, :, 0] < 215) & (rgb[:, :, 0] > 100)).astype(np.uint8) * 255
gris[:300, :] = 0
gris[660:, :] = 0
gris[:, :205] = 0
gris[:, 400:] = 0
patios = []
contornos, _ = cv2.findContours(gris, cv2.RETR_LIST, cv2.CHAIN_APPROX_SIMPLE)
for c in contornos:
    if cv2.arcLength(c, False) < 30:
        continue
    aprox = cv2.approxPolyDP(c, 1.2, False).reshape(-1, 2)
    patios.append(' '.join('%g,%g' % girar(x, y) for x, y in aprox))

# Perímetro de Beauchef 850 (línea punteada del plano) y calles alrededor.
perimetro_850 = rectangulo(82, 52, 533, 848)


def php(valor, sangria=1):
    s = '    ' * sangria
    if isinstance(valor, dict):
        filas = ["%s'%s' => %s," % (s, k, php(v, sangria + 1)) for k, v in valor.items()]
        return '[\n' + '\n'.join(filas) + '\n' + '    ' * (sangria - 1) + ']'
    if isinstance(valor, list):
        filas = ['%s%s,' % (s, php(v, sangria + 1)) for v in valor]
        return '[\n' + '\n'.join(filas) + '\n' + '    ' * (sangria - 1) + ']'
    if isinstance(valor, (int, float)):
        return '%g' % valor
    return "'" + str(valor).replace("'", "\\'") + "'"


datos = {
    'ancho': ANCHO_MAPA,
    'alto': ALTO_MAPA,
    'edificios': edificios,
    'espacios': espacios,
    'patios': patios,
    'perimetro_850': perimetro_850,
}

os.makedirs(os.path.dirname(SALIDA), exist_ok=True)
with open(SALIDA, 'w', encoding='utf-8') as f:
    f.write("<?php\n/**\n * CEPC · Sitio público\n * includes/mapa/geometria.php — Formas del mapa del campus.\n *\n"
            " * ARCHIVO GENERADO por herramientas/trazar-mapa.py a partir del plano de\n"
            " * referencias/mapas/. Si cambias una forma a mano, anótalo aquí, porque\n"
            " * volver a correr el script la sobrescribe.\n *\n"
            " * Coordenadas en unidades del mapa (norte arriba; 851 a la izquierda,\n"
            " * 850 a la derecha). Nombres, pisos y salas: includes/mapa/edificios.php.\n */\n\n"
            "declare(strict_types=1);\n\nreturn " + php(datos) + ";\n")

print('Listo:', SALIDA, '·', len(edificios), 'edificios ·', len(patios), 'trazos de patio')
