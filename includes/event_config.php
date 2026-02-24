<?php
// Configuración centralizada del evento para "Cangrejos Albinos"
// Modifica estos valores para cambiar la capacidad máxima y el máximo de entradas por persona en todo el sistema.

define('EVENTO_CAPACIDAD_MAXIMA', 450); // Capacidad máxima total del evento

define('EVENTO_MAXIMO_POR_PERSONA', 4); // Máximo de entradas que puede reservar una sola persona

// Configuración de eventos Edición 2026
// Estados posibles: 'proximamente' | 'reservar' | 'agotado' | 'ver_evento'
$eventos_2026 = [
    [
        'nombre' => 'Luz Casal',
        'titulo_charla' => 'La voz que atraviesa décadas: Cómo se sostiene una voz… cuando la vida también exige tono.',
        'categoria' => 'Cultura',
        'fecha' => '21 de Marzo',
        'descripcion' => 'Voz icónica del pop-rock español. De los 80 al bolero, una carrera internacional marcada por fuerza, elegancia y verdad.',
        'imagen' => 'assets/img/schedule/26/cangrejos albinos LUZ CASAL 1400x1400 copia.jpg',
        'estado' => 'proximamente',
        'link' => '#',
    ],
    [
        'nombre' => 'Quique Dacosta',
        'titulo_charla' => 'Cocinar el territorio: Del paisaje al plato, sin perder el alma.',
        'categoria' => 'Gastronomía',
        'fecha' => '02 de Mayo',
        'descripcion' => 'Chef creativo y vanguardista. Tres estrellas Michelin. Convierte el Mediterráneo en relato comestible: técnica, belleza y producto al servicio de una idea.',
        'imagen' => 'assets/img/schedule/26/cangrejos albinos QUIQUE DACOSTA 1400x1400 copia.jpg',
        'estado' => 'proximamente',
        'link' => '#',
    ],
    [
        'nombre' => 'Carolina Marín',
        'titulo_charla' => 'Ganar por dentro: La cabeza decide antes que el marcador.',
        'categoria' => 'Deportes',
        'fecha' => '26 de Septiembre <small>(Fecha provisional)</small>',
        'descripcion' => 'Campeona olímpica y referente mundial del bádminton. Talento, disciplina y mentalidad feroz: compite para ganar y entrena para superarse.',
        'imagen' => 'assets/img/schedule/26/cangrejos albinos CAROLINA MARIN 1400x1400 copia.jpg',
        'estado' => 'proximamente',
        'link' => '#',
    ],
    [
        'nombre' => 'Paco León',
        'titulo_charla' => 'La comedia como bisturí: Reírse para contar verdades, crear sin pedir permiso.',
        'categoria' => 'Comunicación',
        'fecha' => '14 de Noviembre',
        'descripcion' => 'Actor y director con sello propio. De Aída al cine de autor y series premiadas. Humor con filo, emoción sin maquillaje y riesgo creativo.',
        'imagen' => 'assets/img/schedule/26/cangrejos albinos PACO LEON 1400x1400 copia.jpg',
        'estado' => 'proximamente',
        'link' => '#',
    ],
];

?>