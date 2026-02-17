<?php
// Configuración centralizada del evento para "Cangrejos Albinos"
// Modifica estos valores para cambiar la capacidad máxima y el máximo de entradas por persona en todo el sistema.

define('EVENTO_CAPACIDAD_MAXIMA', 450); // Capacidad máxima total del evento

define('EVENTO_MAXIMO_POR_PERSONA', 4); // Máximo de entradas que puede reservar una sola persona

// Configuración de eventos Edición 2026
// Estados posibles: 'proximamente' | 'reservar' | 'agotado' | 'ver_evento'
$eventos_2026 = [
    [
        'nombre'        => 'Pendiente de confirmar',
        'titulo_charla' => 'Por anunciar',
        'categoria'     => 'Cultura',
        'fecha'         => 'Primavera 2026',
        'descripcion'   => 'Un encuentro único en Jameos del Agua donde la cultura se funde con el paisaje volcánico de Lanzarote. Próximamente desvelaremos todos los detalles.',
        'imagen'        => 'assets/img/schedule/schedule-4/bg.jpg',
        'estado'        => 'proximamente',
        'link'          => '#',
    ],
    [
        'nombre'        => 'Pendiente de confirmar',
        'titulo_charla' => 'Por anunciar',
        'categoria'     => 'Gastronomía',
        'fecha'         => 'Verano 2026',
        'descripcion'   => 'La gastronomía como vehículo de conexión con el territorio. Una conversación que promete despertar todos los sentidos.',
        'imagen'        => 'assets/img/schedule/schedule-4/bg.jpg',
        'estado'        => 'proximamente',
        'link'          => '#',
    ],
    [
        'nombre'        => 'Pendiente de confirmar',
        'titulo_charla' => 'Por anunciar',
        'categoria'     => 'Deportes',
        'fecha'         => 'Otoño 2026',
        'descripcion'   => 'El deporte como metáfora de la superación personal. Un diálogo inspirador en un entorno incomparable.',
        'imagen'        => 'assets/img/schedule/schedule-4/bg.jpg',
        'estado'        => 'proximamente',
        'link'          => '#',
    ],
    [
        'nombre'        => 'Pendiente de confirmar',
        'titulo_charla' => 'Por anunciar',
        'categoria'     => 'Perfil con tirón',
        'fecha'         => 'Invierno 2026',
        'descripcion'   => 'Una personalidad con capacidad de conectar, inspirar y emocionar. Muy pronto conoceremos quién protagonizará esta velada.',
        'imagen'        => 'assets/img/schedule/schedule-4/bg.jpg',
        'estado'        => 'proximamente',
        'link'          => '#',
    ],
];

?>
