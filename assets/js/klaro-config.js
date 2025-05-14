// Configuración de Klaro para el consentimiento de cookies
window.klaroConfig = {
    version: 1,
    elementID: 'klaro',
    storageMethod: 'cookie',
    cookieName: 'klaro',
    htmlTexts: true,
    privacyPolicy: 'https://cactlanzarote.com/politica-de-privacidad/',
    lang: 'es',
    acceptAll: true,
    hideDeclineAll: false,
    translations: {
        es: {
            acceptAll: "Aceptar todas",
            acceptSelected: "Aceptar seleccionadas",
            close: "Cerrar",
            consentModal: {
                title: "Consentimiento de cookies",
                description: "Utilizamos cookies para mejorar tu experiencia y analizar el tráfico. Puedes aceptar o rechazar cada servicio por separado.",
            },
            consentNotice: {
                changeDescription: "Se han producido cambios desde tu última visita, actualiza tu consentimiento.",
                description: "Utilizamos cookies para optimizar las funciones del sitio web y mejorar tu experiencia.",
                learnMore: "Configurar"
            },
            decline: "Rechazar",
            ok: "Aceptar",
            poweredBy: "Realizado con Klaro!",
            privacyPolicy: {
                name: "política de privacidad",
                text: "Para saber más, por favor lee nuestra {privacyPolicy}."
            },
            purposeItem: {
                service: "servicio",
                services: "servicios"
            },
            purposes: {
                analytics: "Analítica",
                video: "Vídeo",
                essential: "Esenciales"
            },
            googleAnalytics: {
                description: "Medición de visitas y analítica web (Google Analytics)."
            },
            vimeo: {
                description: "Reproductor de vídeos de Vimeo."
            },
            phpSession: {
                description: "Cookies técnicas necesarias para el funcionamiento del sitio web."
            }
        }
    },
    services: [
        {
            name: 'phpSession',
            title: 'Cookies de sesión PHP',
            purposes: ['essential'],
            cookies: [/^PHPSESSID/],
            required: true,
            default: true,
            optOut: false,
            contextualConsentOnly: false
        },
        {
            name: 'google-analytics',
            title: 'Google Analytics',
            purposes: ['analytics'],
            cookies: [/^_ga/, /^_gid/, /^_gat/],
            required: false,
            default: false,
            onlyOnce: true,
            callback: function(consent, service) {
                if (consent) {
                    // Cargar Google Analytics cuando se dé el consentimiento
                    window.dataLayer = window.dataLayer || [];
                    function gtag(){dataLayer.push(arguments);}
                    gtag('js', new Date());
                    gtag('config', 'G-96MVM31JD0', {'anonymize_ip': true});
                    
                    // Cargar el script de GA
                    var s = document.createElement('script');
                    s.async = true;
                    s.src = 'https://www.googletagmanager.com/gtag/js?id=G-96MVM31JD0';
                    document.head.appendChild(s);
                } else {
                    // Remover cookies de GA
                    const cookies = ['_ga', '_gat', '_gid'];
                    cookies.forEach(function(cookie) {
                        document.cookie = cookie + '=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
                    });
                }
            }
        },
        {
            name: 'vimeo',
            title: 'Vimeo',
            purposes: ['video'],
            cookies: [/^vuid/],
            required: false,
            default: false,
            callback: function(consent, service) {
                // Este callback se ejecutará cuando el usuario cambie su consentimiento para Vimeo
                if (!consent) {
                    // Eliminar cookies de Vimeo
                    document.cookie = 'vuid=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
                }
            }
        }
    ],
    purposes: {
        essential: {
            title: 'Cookies esenciales',
            description: 'Estas cookies son necesarias para el funcionamiento básico del sitio web y no pueden ser desactivadas.',
        },
        analytics: {
            title: 'Analítica',
            description: 'Cookies para analizar el tráfico y mejorar el sitio.',
        },
        video: {
            title: 'Vídeo',
            description: 'Reproductores de vídeo de terceros (Vimeo).',
        }
    }
};
