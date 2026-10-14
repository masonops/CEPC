<?php
/**
 * CEPC · Sitio público
 * includes/mapa/marcadores.php — Escaleras y otros puntos dentro de los
 * edificios. Se dibujan como íconos chicos sobre el mapa.
 *
 * Cada marcador:
 *   'tipo'      sube (escalera a un piso de arriba) · baja (a un subterráneo)
 *   'x', 'y'    posición en el mapa (mapa.php?coordenadas=1 muestra la cuadrícula)
 *   'texto'     lo que se lee al pasar el mouse y en la ficha del edificio
 *   'edificio'  a qué edificio pertenece
 */

declare(strict_types=1);

return [
    // Pasillo central de la Escuela: una escalera a cada lado y, más adentro
    // (pasadas las que suben, hacia la Sobria), la bajada a la Cafeta.
    ['tipo' => 'sube', 'x' => 336, 'y' => 405, 'texto' => 'Escalera al 2.º piso (lado norte del pasillo)', 'edificio' => 'escuela'],
    ['tipo' => 'sube', 'x' => 336, 'y' => 435, 'texto' => 'Escalera al 2.º piso (lado sur del pasillo)', 'edificio' => 'escuela'],
    ['tipo' => 'baja', 'x' => 355, 'y' => 405, 'texto' => 'Bajada a la Cafeta (piso −1)', 'edificio' => 'escuela'],

    // Sobria: bajadas a la Cafeta por ambos extremos.
    ['tipo' => 'baja', 'x' => 386, 'y' => 374, 'texto' => 'Bajada a la Cafeta (extremo norte)', 'edificio' => 'sobria'],
    ['tipo' => 'baja', 'x' => 386, 'y' => 467, 'texto' => 'Bajada a la Cafeta (extremo sur)', 'edificio' => 'sobria'],
];
