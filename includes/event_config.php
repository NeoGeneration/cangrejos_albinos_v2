<?php
// Configuración centralizada del evento para "Cangrejos Albinos"
// Modifica estos valores para cambiar la capacidad máxima y el máximo de entradas por persona en todo el sistema.

define('EVENTO_CAPACIDAD_MAXIMA', 450); // Capacidad máxima total del evento

define('EVENTO_MAXIMO_POR_PERSONA', 4); // Máximo de entradas que puede reservar una sola persona

// Configuración de eventos Edición 2026
// Estados posibles: 'proximamente' | 'reservar' | 'agotado' | 'ver_evento' | 'proximamente_mediaset'
// 'disponible_desde' (opcional, 'Y-m-d H:i' hora de Canarias): hasta esa fecha el evento se muestra como 'proximamente'.
// Añadir ?preview=1 a la URL para ver el estado final antes de tiempo.
$eventos_2026 = [
    [
        'nombre' => 'Luz Casal',
        'titulo_charla' => 'La voz que atraviesa décadas: Cómo se sostiene una voz… cuando la vida también exige tono.',
        'categoria' => 'Cultura',
        'fecha' => '21 de Marzo, 20:00h',
        'descripcion' => 'Voz icónica del pop-rock español. De los 80 al bolero, una carrera internacional marcada por fuerza, elegancia y verdad.',
        'imagen' => 'assets/img/schedule/26/cangrejos albinos LUZ CASAL 1400x1400 copia.jpg',
        'estado' => 'ver_evento',
        'link' => 'https://www.mediasetinfinity.es/programas-tv/cangrejos-albinos/temporada-2/episodios/programa-5-40_018714809/player/',
        'turitop_service_id' => 'P318',
    ],
    [
        'nombre' => 'Quique Dacosta',
        'titulo_charla' => 'Cocinar el territorio: Del paisaje al plato, sin perder el alma.',
        'categoria' => 'Gastronomía',
        'fecha' => 'Lunes 6 de Julio, 11:00h',
        'descripcion' => 'Chef creativo y vanguardista. Tres estrellas Michelin. Convierte el Mediterráneo en relato comestible: técnica, belleza y producto al servicio de una idea.',
        'imagen' => 'assets/img/schedule/26/cangrejos albinos QUIQUE DACOSTA 1400x1400 copia.jpg',
        'estado' => 'ver_evento',
        'link' => 'https://www.mediasetinfinity.es/programas-tv/cangrejos-albinos/temporada-2/episodios/programa-6-40_019651422/player/',
        'turitop_service_id' => 'P324',
        'ubicacion' => 'Casa Museo del Campesino, Lanzarote',
        'nota' => 'Aforo reducido y copa networking final',
    ],
    [
        'nombre' => 'Carolina Marín',
        'titulo_charla' => 'Ganar por dentro: La cabeza decide antes que el marcador.',
        'categoria' => 'Deportes',
        'fecha' => '26 de Septiembre, 20:00h',
        'descripcion' => 'Campeona olímpica y referente mundial del bádminton. Talento, disciplina y mentalidad feroz: compite para ganar y entrena para superarse.',
        'imagen' => 'assets/img/schedule/26/cangrejos albinos CAROLINA MARIN 1400x1400 copia.jpg',
        'estado' => 'reservar',
        'link' => '#',
        'turitop_service_id' => 'P323',
    ],
    [
        'nombre' => 'Paco León',
        'titulo_charla' => 'La comedia como bisturí: Reírse para contar verdades, crear sin pedir permiso.',
        'categoria' => 'Comunicación',
        'fecha' => '14 de Noviembre, 20:00h',
        'descripcion' => 'Actor y director con sello propio. De Aída al cine de autor y series premiadas. Humor con filo, emoción sin maquillaje y riesgo creativo.',
        'imagen' => 'assets/img/schedule/26/cangrejos albinos PACO LEON 1400x1400 copia.jpg',
        'estado' => 'reservar',
        'link' => '#',
        'turitop_service_id' => 'P333',
        'disponible_desde' => '2026-09-25 00:00',
    ],
];

$ahora_eventos = new DateTime('now', new DateTimeZone('Atlantic/Canary'));
$preview_eventos = isset($_GET['preview']) && $_GET['preview'] === '1';
foreach ($eventos_2026 as &$evento_cfg) {
    if (!$preview_eventos && !empty($evento_cfg['disponible_desde'])) {
        $disponible = new DateTime($evento_cfg['disponible_desde'], new DateTimeZone('Atlantic/Canary'));
        if ($ahora_eventos < $disponible) {
            $evento_cfg['estado'] = 'proximamente';
        }
    }
}
unset($evento_cfg);

?>
