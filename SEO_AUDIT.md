# Auditoría SEO — index.php · Cangrejos Albinos

**Fecha:** 18 de febrero de 2026
**URL:** https://cangrejosalbinos.com/
**Archivo analizado:** `index.php`

---

## 1. Meta Tags y Head SEO

| Aspecto | Estado | Impacto | Detalle |
|---|---|---|---|
| **Title tag** | ⚠️ MEJORAR | ALTO | `Cangrejos Albinos \| CACT Lanzarote` — Demasiado genérico (38 chars). Falta keywords como "charlas", "Jameos del Agua", "Lanzarote", "eventos 2026" |
| **Meta description** | ⚠️ MEJORAR | ALTO | Menciona **2025** pero estamos en **2026**. Falta call-to-action. Longitud OK (~155 chars) |
| **Open Graph tags** | ❌ FALTA | ALTO | No existen `og:title`, `og:description`, `og:image`, `og:url`, `og:type`, `og:site_name`. Las comparticiones en redes sociales se ven mal |
| **Twitter Card tags** | ❌ FALTA | MEDIO | No existen `twitter:card`, `twitter:title`, `twitter:description`, `twitter:image` |
| **Canonical URL** | ❌ FALTA | ALTO | No hay `<link rel="canonical">`. Riesgo de contenido duplicado |
| **Hreflang** | N/A | BAJO | Solo contenido en español, no es necesario |
| **Favicon** | ✅ OK | BAJO | Configurado correctamente como PNG |
| **Meta robots** | ❌ FALTA | MEDIO | No hay meta robots tag. Debería estar `index, follow` explícitamente |
| **Viewport** | ✅ OK | ALTO | Correctamente configurado |
| **Charset** | ✅ OK | BAJO | UTF-8 correcto |

---

## 2. Datos Estructurados (Schema.org / JSON-LD)

| Aspecto | Estado | Impacto | Detalle |
|---|---|---|---|
| **JSON-LD** | ❌ FALTA | ALTO | No existe ningún dato estructurado en todo el sitio |
| **Schema Event** | ❌ FALTA | ALTO | Imprescindible para rich snippets de eventos en Google. Cada charla debería tener su propio Event schema |
| **Schema Organization** | ❌ FALTA | ALTO | CACT Lanzarote debería tener Organization schema con logo, URL, redes sociales |
| **Schema WebSite** | ❌ FALTA | MEDIO | Permite sitelinks y searchbox en Google |
| **BreadcrumbList** | ❌ FALTA | BAJO | Solo una página principal, menos relevante |

### Rich results perdidos

- Tarjetas de eventos en Google Search
- Knowledge Panel de la organización
- Carrusel de eventos

---

## 3. Estructura HTML Semántica

| Aspecto | Estado | Impacto | Detalle |
|---|---|---|---|
| **H1** | ❌ FALTA | ALTO | **No hay ningún `<h1>`** en toda la página. El título principal usa `<h2>`. Error crítico de SEO |
| **Jerarquía headings** | ⚠️ MEJORAR | ALTO | Múltiples `<h2>` sin un `<h1>` padre. Falta jerarquía lógica `h1 > h2 > h3` |
| **HTML semántico** | ⚠️ MEJORAR | MEDIO | Usa `<header>`, `<main>`, `<footer>` correctamente. Pero `<nav>` está embebido dentro de divs genéricos. Faltan `<section>` y `<article>` |
| **Alt en imágenes** | ⚠️ MEJORAR | MEDIO | La mayoría tienen alt pero son genéricos. Podrían ser más descriptivos e incluir keywords |
| **Accesibilidad ARIA** | ⚠️ MEJORAR | MEDIO | Hay algunos `aria-label` y `role="tabpanel"`, pero faltan roles en navegación y formulario |
| **HTML comentado** | ⚠️ MEJORAR | BAJO | Cientos de líneas de HTML comentado (secciones enteras de eventos 2025). Incrementa peso de la página |
| **SVG inline masivo** | ⚠️ MEJORAR | MEDIO | ~70 líneas de SVG path data oculto (`display:none`) en el hero. Peso innecesario |
| **Links externos** | ⚠️ MEJORAR | MEDIO | Algunos links a redes sociales usan `target="_blank"` sin `rel="noopener noreferrer"`. Pinterest usa `http://` en vez de `https://` |

---

## 4. SEO Técnico y Rendimiento

| Aspecto | Estado | Impacto | Detalle |
|---|---|---|---|
| **robots.txt** | ❌ FALTA | ALTO | No existe. Google no tiene guía de rastreo |
| **sitemap.xml** | ❌ FALTA | ALTO | No existe. Las páginas de eventos individuales no se descubren fácilmente |
| **HTTPS redirect** | ⚠️ MEJORAR | ALTO | Comentado en `.htaccess`. HTTPS es factor de ranking |
| **CSS (11 archivos)** | ⚠️ MEJORAR | MEDIO | 11 archivos CSS cargados sin combinar. Sin preload para CSS crítico |
| **JS (13 archivos)** | ⚠️ MEJORAR | MEDIO | 13 archivos JS. Solo Klaro usa `defer`. jQuery carga bloqueante al final del body |
| **Lazy loading** | ❌ FALTA | MEDIO | Ninguna imagen usa `loading="lazy"` |
| **WebP** | ❌ FALTA | BAJO | Todas las imágenes son PNG/JPG. WebP reduciría peso |
| **Cache headers** | ❌ FALTA | MEDIO | No hay directivas de cache en `.htaccess` |
| **Compresión gzip** | ❌ FALTA | MEDIO | No hay `mod_deflate` / gzip configurado en `.htaccess` |
| **Preconnect** | ❌ FALTA | BAJO | No hay preconnect para CDN externos (`kiprotect.com`, `turitop.com`) |
| **Copyright** | ⚠️ MEJORAR | BAJO | Footer dice "2025", debería ser "2026" o dinámico con PHP |
| **Inline CSS/JS** | ⚠️ MEJORAR | BAJO | Estilos del formulario inline en `<style>`, script de validación inline |

---

## Resumen de Prioridades

### Prioridad ALTA — Impacto inmediato en rankings

1. Agregar un **`<h1>`** (actualmente no existe ninguno)
2. Crear **robots.txt** y **sitemap.xml**
3. Agregar **canonical URL** (`<link rel="canonical">`)
4. Actualizar **meta description** (fecha 2025 → 2026)
5. Mejorar **title tag** con keywords relevantes
6. Implementar **Open Graph tags**
7. Agregar **JSON-LD Schema Event** para cada evento
8. Habilitar **redirección HTTPS** en `.htaccess`

### Prioridad MEDIA

9. Agregar **Twitter Card tags**
10. Implementar **Schema Organization** para CACT Lanzarote
11. Agregar **lazy loading** a imágenes (`loading="lazy"`)
12. Configurar **cache headers** y **gzip** en `.htaccess`
13. Corregir links externos (Pinterest HTTP → HTTPS, añadir `rel="noopener noreferrer"`)
14. Eliminar **HTML comentado** innecesario (~500 líneas)

### Prioridad BAJA

15. Eliminar SVG oculto del hero
16. Convertir imágenes a WebP
17. Externalizar CSS/JS inline
18. Actualizar copyright a 2026
19. Agregar `preconnect` para recursos externos
