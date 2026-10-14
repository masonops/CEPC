<?php
/**
 * CEPC · Sitio público
 * includes/datos-transparencia.php — Lo que la página de transparencia lee
 * del panel: finanzas, proyectos, asambleas y votaciones (sólo lectura).
 *
 * Qué se publica y qué no:
 *   · Movimientos: fecha, tipo, descripción, categoría, monto y proyecto.
 *     Nunca quién lo registró.
 *   · Reembolsos: no se publican (son pagos a personas); sus gastos ya
 *     aparecen como movimientos cuando se pagan.
 *   · Asambleas: todas menos las suspendidas sin acta; sin el padrón.
 *   · Votaciones: sólo las cerradas muestran resultados.
 */

declare(strict_types=1);

/** Año que se rinde: el «año de gestión» del panel o el actual. */
function transparencia_anio(): int
{
    $anio = (int) datos_ajuste('anio_gestion', 'anio_gestion');
    return $anio > 2000 ? $anio : (int) date('Y');
}

/** Nombre legible de una categoría de ingreso o gasto. */
function transparencia_categoria(string $tipo, string $clave): string
{
    if ($tipo === 'ingreso') {
        $mapa = function_exists('cat_categorias_ingreso') ? cat_categorias_ingreso() : [
            'aporte_facultad' => 'Aporte facultad / escuela', 'fecech' => 'Aporte FECh', 'rifa' => 'Rifa',
            'venta' => 'Ventas', 'fiesta' => 'Fiesta / evento', 'auspicio' => 'Auspicio',
            'donacion' => 'Donación', 'devolucion' => 'Devolución', 'otro' => 'Otro ingreso',
        ];
    } else {
        $mapa = function_exists('cat_categorias_gasto') ? cat_categorias_gasto() : [
            'insumos' => 'Insumos y materiales', 'alimentacion' => 'Alimentación',
            'impresiones' => 'Impresiones y papelería', 'difusion' => 'Difusión y gráfica',
            'arriendo' => 'Arriendo (sonido, toldos, salas)', 'transporte' => 'Transporte',
            'bienestar' => 'Apoyo a estudiantes', 'deportes' => 'Deportes', 'cultura' => 'Cultura',
            'mantencion' => 'Mantención sede', 'otro' => 'Otro gasto',
        ];
    }
    return $mapa[$clave] ?? ucfirst(str_replace('_', ' ', $clave));
}

/** Rango de fechas del año que se rinde. */
function transparencia_rango(?int $anio = null): array
{
    $anio ??= transparencia_anio();
    return [$anio . '-01-01', $anio . '-12-31'];
}

/** Ingresos, gastos y saldo del año. */
function transparencia_resumen(?int $anio = null): array
{
    [$desde, $hasta] = transparencia_rango($anio);
    $filas = datos_filas(
        'SELECT tipo, SUM(monto) AS total, COUNT(*) AS cantidad, MAX(fecha) AS ultima
           FROM movimientos WHERE fecha BETWEEN ? AND ? GROUP BY tipo',
        [$desde, $hasta]
    );
    $r = ['ingresos' => 0.0, 'gastos' => 0.0, 'movimientos' => 0, 'ultima' => null];
    foreach ($filas as $f) {
        $r[$f['tipo'] === 'ingreso' ? 'ingresos' : 'gastos'] = (float) $f['total'];
        $r['movimientos'] += (int) $f['cantidad'];
        $r['ultima'] = max((string) $r['ultima'], (string) $f['ultima']) ?: null;
    }
    $r['saldo'] = $r['ingresos'] - $r['gastos'];
    return $r;
}

/** Gastos del año agrupados por categoría, de mayor a menor. */
function transparencia_gastos_por_categoria(?int $anio = null): array
{
    [$desde, $hasta] = transparencia_rango($anio);
    $filas = datos_filas(
        "SELECT categoria, SUM(monto) AS total FROM movimientos
          WHERE tipo = 'gasto' AND fecha BETWEEN ? AND ?
          GROUP BY categoria ORDER BY total DESC",
        [$desde, $hasta]
    );
    return array_map(static fn(array $f): array => [
        'categoria' => transparencia_categoria('gasto', (string) $f['categoria']),
        'total'     => (float) $f['total'],
    ], $filas);
}

/** Todos los movimientos del año, del más reciente al más antiguo. */
function transparencia_movimientos(?int $anio = null): array
{
    [$desde, $hasta] = transparencia_rango($anio);
    $filas = datos_filas(
        'SELECT m.fecha, m.tipo, m.descripcion, m.categoria, m.monto, p.nombre AS proyecto
           FROM movimientos m LEFT JOIN proyectos p ON p.id = m.proyecto_id
          WHERE m.fecha BETWEEN ? AND ?
          ORDER BY m.fecha DESC, m.id DESC',
        [$desde, $hasta]
    );
    foreach ($filas as &$f) {
        $f['categoria_texto'] = transparencia_categoria((string) $f['tipo'], (string) $f['categoria']);
        $f['monto'] = (float) $f['monto'];
    }
    return $filas;
}

/** Proyectos del año con su presupuesto y cuánto se ha gastado. */
function transparencia_proyectos(?int $anio = null): array
{
    $anio ??= transparencia_anio();
    $filas = datos_filas(
        "SELECT p.nombre, p.descripcion, p.presupuesto, p.estado,
                COALESCE(SUM(CASE WHEN m.tipo = 'gasto' THEN m.monto END), 0) AS gastado
           FROM proyectos p LEFT JOIN movimientos m ON m.proyecto_id = p.id
          WHERE p.anio = ?
          GROUP BY p.id, p.nombre, p.descripcion, p.presupuesto, p.estado
          ORDER BY p.presupuesto DESC",
        [$anio]
    );
    $estados = ['planificacion' => 'En planificación', 'en_curso' => 'En curso', 'finalizado' => 'Finalizado', 'pausado' => 'Pausado'];
    foreach ($filas as &$f) {
        $f['presupuesto']  = (float) $f['presupuesto'];
        $f['gastado']      = (float) $f['gastado'];
        $f['estado_texto'] = $estados[$f['estado']] ?? ucfirst((string) $f['estado']);
    }
    return $filas;
}

/** Asambleas, de la más reciente a la más antigua (las convocadas primero). */
function transparencia_asambleas(): array
{
    $filas = datos_filas(
        "SELECT titulo, fecha, hora, lugar, estado, asistentes, quorum_requerido, acta_url
           FROM asambleas WHERE estado <> 'suspendida' OR acta_url IS NOT NULL
          ORDER BY fecha DESC"
    );
    $estados = ['convocada' => 'Convocada', 'realizada' => 'Realizada', 'sin_quorum' => 'Sin quórum', 'suspendida' => 'Suspendida'];
    foreach ($filas as &$f) {
        $f['estado_texto'] = $estados[$f['estado']] ?? ucfirst((string) $f['estado']);
    }
    return $filas;
}

/** Votaciones cerradas con sus resultados. */
function transparencia_votaciones(): array
{
    $votaciones = datos_filas(
        "SELECT id, titulo, descripcion, tipo, fecha_inicio, fecha_fin, padron
           FROM votaciones WHERE estado = 'cerrada' ORDER BY COALESCE(fecha_fin, fecha_inicio) DESC"
    );
    foreach ($votaciones as &$v) {
        $v['opciones'] = datos_filas(
            'SELECT texto, votos FROM opciones_votacion WHERE votacion_id = ? ORDER BY votos DESC, orden ASC',
            [(int) $v['id']]
        );
        $v['total'] = array_sum(array_map(static fn(array $o): int => (int) $o['votos'], $v['opciones']));
    }
    return $votaciones;
}

/** Pesos chilenos: $1.250.000. */
function pesos(float $monto): string
{
    return ($monto < 0 ? '−$' : '$') . number_format(abs($monto), 0, ',', '.');
}
