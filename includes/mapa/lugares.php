<?php
/**
 * CEPC · Sitio público
 * includes/mapa/lugares.php — Dónde comer en el campus y cerca.
 *
 * Cada lugar:
 *   'nombre'     cómo se llama
 *   'tipo'       almuerzo · cafeteria · snack · minimarket
 *   'x', 'y'     posición en el mapa (mapa.php?coordenadas=1 muestra la cuadrícula)
 *   'etiquetas'  cualquiera de: vegano · vegetariano · junaeb · barato
 *   'horario'    por día: 'HH:MM-HH:MM', o null si cierra. Usa horario() para
 *                no repetir: horario('08:30-17:00', sabado: '09:00-14:00')
 *   'nota'       texto libre (precio del menú, dónde queda exactamente…)
 *   'ejemplo'    true mientras el dato no esté confirmado (el mapa lo avisa)
 *
 * IMPORTANTE: los lugares de afuera del campus son DE EJEMPLO. Reemplázalos
 * por los reales, con su horario, y quita 'ejemplo' => true.
 */

declare(strict_types=1);

if (!function_exists('horario')) {
    /** Mismo horario de lunes a viernes; sábado y domingo opcionales. */
    function horario(string $semana, ?string $sabado = null, ?string $domingo = null): array
    {
        return [
            'lun' => $semana, 'mar' => $semana, 'mie' => $semana, 'jue' => $semana, 'vie' => $semana,
            'sab' => $sabado, 'dom' => $domingo,
        ];
    }
}

return [
    'casino' => [
        'nombre'    => 'Casino de la facultad',
        'tipo'      => 'almuerzo',
        'x'         => 458, 'y' => 97,
        'etiquetas' => ['junaeb'],
        'horario'   => null,
        'nota'      => 'Horario y menú por confirmar.',
        'edificio'  => 'casino',
    ],
    'cafeteria' => [
        'nombre'    => 'Cafetería del patio',
        'tipo'      => 'cafeteria',
        'x'         => 386, 'y' => 440,
        'etiquetas' => [],
        'horario'   => null,
        'nota'      => 'Horario por confirmar.',
        'edificio'  => 'cafeteria',
    ],
    'almuerzos' => [
        'nombre'    => 'Almuerzos caseros',
        'tipo'      => 'almuerzo',
        'x'         => 560, 'y' => 92,
        'etiquetas' => ['junaeb', 'barato'],
        'horario'   => horario('12:00-16:00'),
        'nota'      => 'Menú del día con postre.',
        'ejemplo'   => true,
    ],
    'cafe' => [
        'nombre'    => 'Café de especialidad',
        'tipo'      => 'cafeteria',
        'x'         => 760, 'y' => 92,
        'etiquetas' => ['vegetariano'],
        'horario'   => horario('08:00-19:00', '10:00-14:00'),
        'nota'      => 'Café, sándwiches y queques.',
        'ejemplo'   => true,
    ],
    'vegano' => [
        'nombre'    => 'Cocina vegana',
        'tipo'      => 'almuerzo',
        'x'         => 470, 'y' => 712,
        'etiquetas' => ['vegano', 'vegetariano', 'junaeb'],
        'horario'   => horario('12:30-17:00'),
        'nota'      => 'Bowls, wraps y menú vegano diario.',
        'ejemplo'   => true,
    ],
    'minimarket' => [
        'nombre'    => 'Minimarket',
        'tipo'      => 'minimarket',
        'x'         => 820, 'y' => 712,
        'etiquetas' => ['barato'],
        'horario'   => horario('08:00-21:00', '09:00-20:00', '10:00-14:00'),
        'nota'      => 'Snacks, fruta y bebidas.',
        'ejemplo'   => true,
    ],
    'completos' => [
        'nombre'    => 'Completos y sándwiches',
        'tipo'      => 'snack',
        'x'         => 120, 'y' => 712,
        'etiquetas' => ['junaeb', 'barato'],
        'horario'   => horario('11:00-20:00', '12:00-16:00'),
        'nota'      => 'A la salida de Beauchef 851.',
        'ejemplo'   => true,
    ],
];
