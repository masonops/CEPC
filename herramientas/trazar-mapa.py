"""
CEPC · Sitio público
herramientas/trazar-mapa.py — Calca los edificios del plano de colores de
Beauchef 850 (referencias/mapas/beauchef-850-colores.png) y escribe
includes/mapa/geometria.php con las formas listas para el mapa.

Qué hace:
  · Gira el plano 90° a la derecha (norte arriba): Beauchef 851 queda a la
    izquierda (poniente) y Beauchef 850 a la derecha (oriente).
  · Limpia cada contorno: endereza los bordes casi rectos, suaviza las
    curvas (las medialunas de Física y Química, el borde de Geología) y
    quita los puntos que sobran.
  · Une la Escuela con la Biblioteca y el Hall Sur en un solo edificio, y
    deja esas partes y el pasillo central como «zonas» interiores.
  · Vectoriza las líneas grises del patio central como trazos simples.
  · Calcula dónde va cada etiqueta (el punto más alejado de los bordes) y
    cuánto espacio libre tiene en horizontal y vertical, para que el texto
    nunca se salga de su edificio.

Sólo hace falta correrlo si cambia el plano o la forma de algo:

    python3 -m venv /tmp/venv && /tmp/venv/bin/pip install opencv-python-headless numpy shapely
    /tmp/venv/bin/python herramientas/trazar-mapa.py

Los nombres, pisos y salas NO van aquí: están en includes/mapa/edificios.php.
"""

import math
import os

import cv2
import numpy as np
from shapely.geometry import LineString, Point, Polygon
from shapely.ops import polylabel, unary_union

RAIZ = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
PLANO = os.path.join(RAIZ, 'referencias', 'mapas', 'beauchef-850-colores.png')
SALIDA = os.path.join(RAIZ, 'includes', 'mapa', 'geometria.php')

ALTO_PLANO = 1049          # alto del PNG original (pasa a ser el ancho al girar)
DX, DY = 30, 110           # margen para calles y alrededores
ANCHO_MAPA, ALTO_MAPA = 1110, 730

# Etiquetas que conviene ubicar a mano (coordenadas del mapa ya girado).
ETIQUETAS_FIJAS = {
    'escuela': (321, 420),   # sobre el pasillo central, que une ambas alas
}


def girar(x, y):
    """Coordenada del plano original -> coordenada del mapa (norte arriba)."""
    return (ALTO_PLANO - y + DX, x + DY)


imagen = cv2.imread(PLANO)
rgb = cv2.cvtColor(imagen, cv2.COLOR_BGR2RGB).astype(int)


# --- Contornos por color ---------------------------------------------------

def mascara_de(hexa, tolerancia=38):
    color = np.array([int(hexa[i:i + 2], 16) for i in (0, 2, 4)])
    m = (np.sqrt(((rgb - color) ** 2).sum(-1)) < tolerancia).astype(np.uint8) * 255
    return cv2.morphologyEx(m, cv2.MORPH_CLOSE, np.ones((7, 7), np.uint8))


def contornos(mascara, area_minima=300):
    cs, _ = cv2.findContours(mascara, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_NONE)
    return [c for c in cs if cv2.contourArea(c) >= area_minima]


def a_poligono(contorno):
    """Contorno de OpenCV (plano) -> lista de puntos del mapa, ya limpia."""
    aprox = cv2.approxPolyDP(contorno, 0.9, True).reshape(-1, 2).astype(float)
    puntos = [girar(x, y) for x, y in aprox]
    return limpiar(puntos)


def el_mayor(hexa):
    return a_poligono(max(contornos(mascara_de(hexa)), key=cv2.contourArea))


def por_caja(hexa, x_min, x_max):
    for c in contornos(mascara_de(hexa)):
        if x_min <= cv2.boundingRect(c)[0] <= x_max:
            return a_poligono(c)
    raise SystemExit('No encontré el color %s entre x=%d y x=%d' % (hexa, x_min, x_max))


def rectangulo(x0, y0, x1, y1):
    return [girar(x0, y0), girar(x1, y0), girar(x1, y1), girar(x0, y1)]


# --- Limpieza de formas ------------------------------------------------------

def limpiar(puntos):
    """Endereza bordes, suaviza curvas y quita puntos sobrantes."""
    p = [list(q) for q in puntos]
    n = len(p)

    # 1. Bordes casi horizontales o verticales quedan exactos.
    for _ in range(2):
        for i in range(n):
            a, b = p[i], p[(i + 1) % n]
            dx, dy = abs(a[0] - b[0]), abs(a[1] - b[1])
            if dx <= 2.6 and dy > 3:
                m = round((a[0] + b[0]) / 2)
                a[0] = b[0] = m
            elif dy <= 2.6 and dx > 3:
                m = round((a[1] + b[1]) / 2)
                a[1] = b[1] = m

    # 2. Curvas: vértices con lados cortos y giro suave (menos de 55°) se
    #    suavizan con dos pasadas de Chaikin; las esquinas rectas no se tocan.
    def giro(q, i):
        a, b, c = q[i - 1], q[i], q[(i + 1) % len(q)]
        v1 = (b[0] - a[0], b[1] - a[1])
        v2 = (c[0] - b[0], c[1] - b[1])
        n1, n2 = math.hypot(*v1), math.hypot(*v2)
        if n1 < 1e-6 or n2 < 1e-6:
            return 0
        cos = max(-1, min(1, (v1[0] * v2[0] + v1[1] * v2[1]) / (n1 * n2)))
        return math.degrees(math.acos(cos))

    def es_curva(q, i):
        a, b, c = q[i - 1], q[i], q[(i + 1) % len(q)]
        corto = math.hypot(a[0] - b[0], a[1] - b[1]) < 11 and math.hypot(c[0] - b[0], c[1] - b[1]) < 11
        return corto and 4 < giro(q, i) < 55

    for _ in range(2):
        n = len(p)
        curva = [es_curva(p, i) for i in range(n)]
        if not any(curva):
            break
        nuevo = []
        for i in range(n):
            a, b = p[i], p[(i + 1) % n]
            if curva[i] or curva[(i + 1) % n]:
                if not curva[i]:
                    nuevo.append(a)
                nuevo.append([a[0] * 0.75 + b[0] * 0.25, a[1] * 0.75 + b[1] * 0.25])
                nuevo.append([a[0] * 0.25 + b[0] * 0.75, a[1] * 0.25 + b[1] * 0.75])
            else:
                nuevo.append(a)
        p = nuevo

    # 3. Esquinas limpias: un lado de menos de 2,4 unidades entre dos lados
    #    largos se reemplaza por la esquina donde se cruzan esos dos lados.
    def cruce(a, b, c, d):
        den = (a[0] - b[0]) * (c[1] - d[1]) - (a[1] - b[1]) * (c[0] - d[0])
        if abs(den) < 1e-6:
            return None
        t = ((a[0] - c[0]) * (c[1] - d[1]) - (a[1] - c[1]) * (c[0] - d[0])) / den
        return [a[0] + t * (b[0] - a[0]), a[1] + t * (b[1] - a[1])]

    cambio = True
    while cambio and len(p) > 4:
        cambio = False
        for i in range(len(p)):
            n2 = len(p)
            a, b = p[i], p[(i + 1) % n2]
            if math.hypot(a[0] - b[0], a[1] - b[1]) >= 3.2:
                continue
            antes, despues = p[i - 1], p[(i + 2) % n2]
            if math.hypot(a[0] - antes[0], a[1] - antes[1]) < 4 or math.hypot(b[0] - despues[0], b[1] - despues[1]) < 4:
                continue
            esquina = cruce(antes, a, b, despues)
            if esquina is None or math.hypot(esquina[0] - a[0], esquina[1] - a[1]) > 4:
                continue
            p[i] = esquina
            del p[(i + 1) % n2]
            cambio = True
            break

    # 4. Quitar puntos casi alineados con sus vecinos.
    def area3(a, b, c):
        return abs((b[0] - a[0]) * (c[1] - a[1]) - (c[0] - a[0]) * (b[1] - a[1])) / 2

    cambio = True
    while cambio and len(p) > 3:
        cambio = False
        for i in range(len(p)):
            a, b, c = p[i - 1], p[i], p[(i + 1) % len(p)]
            if area3(a, b, c) < 1.2 or math.hypot(a[0] - b[0], a[1] - b[1]) < 0.6:
                del p[i]
                cambio = True
                break

    return [(round(x, 1), round(y, 1)) for x, y in p]


# --- Edificios de Beauchef 850 ----------------------------------------------

edificios = {
    'geologia':        el_mayor('c06830'),
    'anexo-idiem':     el_mayor('f07020'),
    'idiem':           el_mayor('c02058'),
    'civil':           el_mayor('007880'),
    'electrica':       el_mayor('8088b0'),
    'justicia-espada': el_mayor('003860'),
    'fisica':          el_mayor('c0d018'),
    'quimica':         el_mayor('28b050'),
    'sobria':          rectangulo(272, 677, 349, 709),
    # Casino: al otro lado de Blanco Encalada; se corre hacia el norte para
    # dejar espacio a la calle.
    'casino':          rectangulo(-32, 600, 5, 803),
    # Beauchef 851 (bloque verde del plano, separado por alas).
    'edificio-norte':   rectangulo(109, 888, 154, 1039),
    'industrial':       rectangulo(155, 888, 433, 935),
    'edificio-oriente': rectangulo(155, 991, 521, 1039),
    'etigny':           rectangulo(432, 939, 521, 986),
}

# Escuela = cuerpo central + Biblioteca (norte) + Hall Sur (sur), un solo edificio.
cuerpo = el_mayor('f02020')
biblioteca = por_caja('f06850', 140, 200)
hall_sur = por_caja('f06850', 400, 430)
union = unary_union([Polygon(cuerpo).buffer(0.8), Polygon(biblioteca).buffer(0.8), Polygon(hall_sur).buffer(0.8)]).buffer(-0.8)
union = union.simplify(0.6)
edificios['escuela'] = limpiar(list(union.exterior.coords)[:-1])

# Zonas interiores de la Escuela. El pasillo central cruza el edificio de
# poniente a oriente, a la altura de la entrada de Beauchef 850; al norte
# queda la Biblioteca y al sur el Hall Sur, cada una hasta casi tocarlo.
PASILLO_Y = (412, 428)
HOLGURA = 2.5
caja = Polygon(edificios['escuela']).bounds
contorno = Polygon(edificios['escuela']).buffer(-1.5, join_style=2)


def mitad(y1, y2):
    franja = Polygon([(caja[0] - 5, y1), (caja[2] + 5, y1), (caja[2] + 5, y2), (caja[0] - 5, y2)])
    parte = franja.intersection(Polygon(edificios['escuela'])).simplify(0.8)
    if parte.geom_type != 'Polygon':
        parte = max(parte.geoms, key=lambda g: g.area)
    return limpiar(list(parte.exterior.coords)[:-1])


zonas = {
    'biblioteca': {'edificio': 'escuela', 'poligono': mitad(caja[1] - 5, PASILLO_Y[0] - HOLGURA)},
    'hall-sur':   {'edificio': 'escuela', 'poligono': mitad(PASILLO_Y[1] + HOLGURA, caja[3] + 5)},
    'pasillo':    {'edificio': 'escuela', 'poligono': []},
}
# El pasillo es una franja de oriente a poniente recortada al contorno de la Escuela.
franja = Polygon([(caja[0] + 4, PASILLO_Y[0]), (caja[2], PASILLO_Y[0]), (caja[2], PASILLO_Y[1]), (caja[0] + 4, PASILLO_Y[1])])
recorte = franja.intersection(contorno).simplify(0.8)
if recorte.geom_type != 'Polygon':
    recorte = max(recorte.geoms, key=lambda g: g.area)
zonas['pasillo']['poligono'] = limpiar(list(recorte.exterior.coords)[:-1])

# Espacios abiertos.
espacios = {
    'cancha': rectangulo(260, 598, 355, 640),
    # «Ebria»: la caja escalonada entre la cancha y Justicia Espada.
    'ebria': [girar(*q) for q in [(261, 553), (281, 553), (281, 543), (336, 543), (336, 551), (355, 551), (355, 581), (261, 581)]],
}


# --- Líneas del patio central -------------------------------------------------

def lineas_del_patio():
    gris = ((abs(rgb[:, :, 0] - rgb[:, :, 1]) < 10) & (abs(rgb[:, :, 1] - rgb[:, :, 2]) < 10)
            & (rgb[:, :, 0] < 225) & (rgb[:, :, 0] > 90)).astype(np.uint8) * 255
    m = np.zeros_like(gris)
    m[300:540, 203:400] = gris[300:540, 203:400]   # sin la caja de Ebria ni la cancha
    n, etiquetas, datos, _ = cv2.connectedComponentsWithStats(m, 8)
    for i in range(1, n):
        if datos[i, cv2.CC_STAT_AREA] < 40:      # flechitas del plano
            m[etiquetas == i] = 0
    # Esqueleto: cada línea queda de 1 px.
    esqueleto = np.zeros_like(m)
    cruz = cv2.getStructuringElement(cv2.MORPH_CROSS, (3, 3))
    t = m.copy()
    while cv2.countNonZero(t):
        e = cv2.erode(t, cruz)
        esqueleto = cv2.bitwise_or(esqueleto, cv2.subtract(t, cv2.dilate(e, cruz)))
        t = e
    crudas = cv2.HoughLinesP(esqueleto, 1, np.pi / 180, 8, minLineLength=7, maxLineGap=3).reshape(-1, 4).tolist()

    # Unir tramos colineales: mismo ángulo (±4°) y misma recta (±1,6 px).
    segs = []
    for x1, y1, x2, y2 in crudas:
        ang = math.atan2(y2 - y1, x2 - x1) % math.pi
        segs.append([ang, float(x1), float(y1), float(x2), float(y2)])
    unidos = []
    usado = [False] * len(segs)
    for i, s in enumerate(segs):
        if usado[i]:
            continue
        grupo = [s]
        usado[i] = True
        ux, uy = math.cos(s[0]), math.sin(s[0])
        for j in range(i + 1, len(segs)):
            if usado[j]:
                continue
            t2 = segs[j]
            dang = abs(t2[0] - s[0])
            dang = min(dang, math.pi - dang)
            dist = abs((t2[1] - s[1]) * -uy + (t2[2] - s[2]) * ux)
            if dang < math.radians(4) and dist < 1.6:
                grupo.append(t2)
                usado[j] = True
        # Cada tramo como intervalo sobre la recta; se unen sólo los que se
        # tocan o están a menos de 4 px (dos trozos lejanos siguen separados).
        intervalos = []
        for g in grupo:
            t1 = (g[1] - s[1]) * ux + (g[2] - s[2]) * uy
            t2 = (g[3] - s[1]) * ux + (g[4] - s[2]) * uy
            intervalos.append([min(t1, t2), max(t1, t2)])
        intervalos.sort()
        actual = intervalos[0]
        juntos = []
        for iv in intervalos[1:]:
            if iv[0] <= actual[1] + 4:
                actual[1] = max(actual[1], iv[1])
            else:
                juntos.append(actual)
                actual = iv
        juntos.append(actual)
        for a0, b0 in juntos:
            unidos.append([s[1] + a0 * ux, s[2] + a0 * uy, s[1] + b0 * ux, s[2] + b0 * uy])

    # Soldar extremos cercanos (esquinas exactas).
    extremos = [(k, e) for k in range(len(unidos)) for e in (0, 1)]
    for k, e in extremos:
        x, y = unidos[k][e * 2], unidos[k][e * 2 + 1]
        cerca = [(k2, e2) for k2, e2 in extremos
                 if math.hypot(unidos[k2][e2 * 2] - x, unidos[k2][e2 * 2 + 1] - y) < 3.2]
        mx = sum(unidos[k2][e2 * 2] for k2, e2 in cerca) / len(cerca)
        my = sum(unidos[k2][e2 * 2 + 1] for k2, e2 in cerca) / len(cerca)
        for k2, e2 in cerca:
            unidos[k2][e2 * 2], unidos[k2][e2 * 2 + 1] = mx, my

    lineas = []
    for x1, y1, x2, y2 in unidos:
        if math.hypot(x2 - x1, y2 - y1) < 6:
            continue
        # Casi horizontal o vertical -> exacta.
        if abs(x2 - x1) < 2:
            x1 = x2 = (x1 + x2) / 2
        if abs(y2 - y1) < 2:
            y1 = y2 = (y1 + y2) / 2
        a, b = girar(x1, y1), girar(x2, y2)
        lineas.append('%g,%g,%g,%g' % (round(a[0], 1), round(a[1], 1), round(b[0], 1), round(b[1], 1)))
    return lineas


patios = lineas_del_patio()


# --- Dónde va cada etiqueta ----------------------------------------------------

def tramo_libre(poligono, punto, horizontal):
    """Tramo dentro del polígono que pasa por el punto: (inicio, fin) en x o en y."""
    x, y = punto
    linea = LineString([(x - 2000, y), (x + 2000, y)] if horizontal else [(x, y - 2000), (x, y + 2000)])
    corte = poligono.intersection(linea)
    for parte in getattr(corte, 'geoms', [corte]):
        if parte.length and parte.distance(Point(x, y)) < 0.5:
            xs = [c[0 if horizontal else 1] for c in parte.coords]
            return min(xs), max(xs)
    return None


def tramo(poligono, punto, horizontal):
    """Largo del tramo libre dentro del polígono que pasa por el punto."""
    t = tramo_libre(poligono, punto, horizontal)
    return round(t[1] - t[0], 1) if t else 0


def centrar(poligono, punto):
    """Corre el punto al medio de su tramo horizontal y luego del vertical.
    En un rectángulo largo, polylabel puede quedar en cualquier punto del eje;
    así la etiqueta queda centrada."""
    x, y = punto
    for _ in range(2):
        h = tramo_libre(poligono, (x, y), True)
        if h:
            x = (h[0] + h[1]) / 2
        v = tramo_libre(poligono, (x, y), False)
        if v:
            y = (v[0] + v[1]) / 2
    return (round(x, 1), round(y, 1))


def etiqueta(id_, puntos):
    poligono = Polygon(puntos).buffer(0)
    if id_ in ETIQUETAS_FIJAS:
        punto = ETIQUETAS_FIJAS[id_]
    else:
        p = polylabel(poligono, tolerance=0.5)
        punto = centrar(poligono, (p.x, p.y))
    return [punto[0], punto[1], tramo(poligono, punto, True), tramo(poligono, punto, False)]


etiquetas = {}
for id_, pts in list(edificios.items()) + list(espacios.items()):
    etiquetas[id_] = etiqueta(id_, pts)
for id_, z in zonas.items():
    if id_ != 'pasillo':
        etiquetas[id_] = etiqueta(id_, z['poligono'])


# --- Salida --------------------------------------------------------------------

def texto_poligono(puntos):
    return ' '.join('%g,%g' % (round(x, 1), round(y, 1)) for x, y in puntos)


def php(valor, sangria=1):
    s = '    ' * sangria
    if isinstance(valor, dict):
        filas = ["%s'%s' => %s," % (s, k, php(v, sangria + 1)) for k, v in valor.items()]
        return '[\n' + '\n'.join(filas) + '\n' + '    ' * (sangria - 1) + ']'
    if isinstance(valor, (list, tuple)):
        if all(isinstance(v, (int, float)) for v in valor):
            return '[' + ', '.join('%g' % v for v in valor) + ']'
        filas = ['%s%s,' % (s, php(v, sangria + 1)) for v in valor]
        return '[\n' + '\n'.join(filas) + '\n' + '    ' * (sangria - 1) + ']'
    if isinstance(valor, (int, float)):
        return '%g' % valor
    return "'" + str(valor).replace("'", "\\'") + "'"


datos = {
    'ancho': ANCHO_MAPA,
    'alto': ALTO_MAPA,
    'edificios': {k: texto_poligono(v) for k, v in edificios.items()},
    'espacios': {k: texto_poligono(v) for k, v in espacios.items()},
    'zonas': {k: {'edificio': v['edificio'], 'poligono': texto_poligono(v['poligono'])} for k, v in zonas.items()},
    'patios': patios,
    'etiquetas': etiquetas,
    'perimetro_850': texto_poligono(rectangulo(82, 52, 533, 848)),
}

os.makedirs(os.path.dirname(SALIDA), exist_ok=True)
with open(SALIDA, 'w', encoding='utf-8') as f:
    f.write("<?php\n/**\n * CEPC · Sitio público\n * includes/mapa/geometria.php — Formas del mapa del campus.\n *\n"
            " * ARCHIVO GENERADO por herramientas/trazar-mapa.py a partir del plano de\n"
            " * referencias/mapas/. No lo edites a mano: volver a correr el script lo\n"
            " * sobrescribe.\n *\n"
            " * Coordenadas en unidades del mapa (norte arriba; 851 a la izquierda,\n"
            " * 850 a la derecha). 'etiquetas' trae, por forma, [x, y, ancho libre,\n"
            " * alto libre] para que el texto quepa. Nombres, pisos y salas:\n"
            " * includes/mapa/edificios.php.\n */\n\n"
            "declare(strict_types=1);\n\nreturn " + php(datos) + ";\n")

print('Listo:', SALIDA, '·', len(edificios), 'edificios ·', len(espacios), 'espacios ·', len(patios), 'líneas de patio')
