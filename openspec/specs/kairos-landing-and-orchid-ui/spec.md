# OpenSpec Specification: MotaCastAudio v2.7 - Landing Page Kairos & Sistema In-App Orchid UI

- **ID de Especificación:** `SPEC-MCA-002`
- **Característica:** `kairos-landing-and-orchid-ui`
- **Versión:** `1.0.0`
- **Fecha:** `2026-10-08`
- **Estado:** `APROBADO POR COMITÉ COLEGIADO (FASE AUDITORÍA P2)`
- **Autores:** Equipo Colegiado de las 7 Disciplinas (Héctor Mota & Antigravity)
- **Ámbito:** `C:\xampp\htdocs\audiolibros` / `https://lab.motazorrilla.com/audiolibros/` / `https://lab.motazorrilla.com/`

---

## 1. Declaración de Alcance y Propósito

El presente documento establece las especificaciones normativas, arquitectónicas y de diseño para la modernización integral de **MotaCastAudio (v2.7)** mediante dos pilares visuales y funcionales:
1. **Landing Page Pública de Alta Conversión (Inspirada en Kairos10):** Arquitectura web de presentación para aplicaciones móviles basada en `C:\xampp\htdocs\Kairos10`, integrando una sección Hero de alto impacto con maqueta móvil 3D del reproductor, propuesta de valor, flujo de 3 pasos ("Cómo funciona"), grilla de características táctiles, muestra de audio interactiva en vivo, tabla de planes y pie de página institucional con co-branding MotaZorrilla & neobranding.cl.
2. **Sistema de Diseño In-App Modular Mobile-First (Inspirado en Orchid Software):** Modernización de la experiencia de usuario dentro de la plataforma (biblioteca, reproductor, creación de libros y módulos administrativos) siguiendo los patrones de Orchid Platform (`orchidsoftware/platform`): navegación lateral estructurada en grupos lógicos, drawer deslizante táctil para móviles, tarjetas de métricas analíticas KPI, filtros en píldoras reactivas y menús contextuales no intrusivos.
3. **Integración al Hub del Homelab (`lab.motazorrilla.com`):** Inclusión de la tarjeta oficial de MotaCastAudio dentro del catálogo de aplicaciones activas del Innovation Lab.

Esta especificación preserva de forma inquebrantable:
- La totalidad de las bases de datos, audiolibros procesados y credenciales activas en MariaDB 11.4 LTS.
- El 100% de la suite de pruebas unitarias y funcionales (132 tests aprobados).
- El estándar **Protocolo 4 Craft Floor** (0 diálogos nativos `alert`, `confirm`, `prompt`).
- El modelo de costes operativos en $0 USD con assets compilados localmente vía Vite.

---

## 2. Auditoría Colegiada de las 7 Disciplinas (Protocolo 2)

### 2.1. UI/UX & Psicología de Conversión (Disciplina 1)
- **Diagnóstico:** Los visitantes que llegaban a la raíz `/` caían directamente en un formulario de subida sin explicación visual de lo que la plataforma es capaz de hacer (síntesis neuronal, sincronía Read-Along, OCR).
- **Decisión:** Implementar la estructura narrativa probada de Kairos10:
  - *Problema $\rightarrow$ Solución $\rightarrow$ Demostración $\rightarrow$ Características $\rightarrow$ Prueba Gratuita Inmediata*.
  - En móviles, el pulgar debe poder accionar el botón de prueba rápida en el 1er tercio de pantalla sin scroll forzado.

### 2.2. Frontend Architecture (Disciplina 2)
- **Diagnóstico:** Evitar dependencias pesadas de terceros (como scripts CDN obsoletos de plantillas antiguas).
- **Decisión:**
  - Extraer los conceptos visuales de Kairos10 pero implementarlos con **Tailwind CSS v4 nativo**, compilado a través de Vite (`public/build/assets/`).
  - Utilizar componentes Blade reutilizables para el sidebar estilo Orchid, las KPI cards y la barra de navegación móvil.

### 2.3. Backend & Lógica de Negocio (Disciplina 3)
- **Diagnóstico:** Las rutas raíz `/` deben discernir inteligentemente entre:
  1. Usuario autenticado $\rightarrow$ redirige a su biblioteca personal (`/books`).
  2. Invitado con audiolibro de prueba activo en sesión $\rightarrow$ acceso prioritario a su libro o a la landing con banner de retorno.
  3. Visitante nuevo $\rightarrow$ visualiza la Landing Page Kairos con botón de prueba gratuita directa.

### 2.4. Base de Datos & Persistencia (Disciplina 4)
- **Diagnóstico:** La base de datos MariaDB 11.4 no requiere alteraciones destructivas; se mantiene la integridad referencial y las columnas existentes (`guest_fingerprint`, `country_code`, `status`, `book_limit`).

### 2.5. DevOps & Infraestructura (Disciplina 5)
- **Diagnóstico:** Contenedor `audiolibros-app` mapeado en puerto 8096, gateway Nginx en `lab-gateway` sirviendo `/audiolibros/`.
- **Decisión:** Actualizar el archivo `index.html` del host `lab.motazorrilla.com` para publicar la tarjeta de MotaCastAudio e incrementar el contador a 14 aplicaciones.

### 2.6. Seguridad, QA & Negocio (Disciplina 6)
- **Diagnóstico:** Conservar el rate limiting de login y recuperación de contraseña recién implementado en la v2.6.
- **Decisión:** Todo botón de la landing page hacia funcionalidades dinámicas debe respetar los límites de sesión y cuotas.

### 2.7. Dirección de Arte & Craft Floor Impeccable (Disciplina 7)
- **Modos Visuales:** Modo *Persuade* en la Landing Page (energía, gradientes, tipografía impactante) y Modo *Operate* / *Read* dentro de la App (enfoque, limpieza, contraste Orchid, paleta dual cian `#00f0ff` y verde neón `#00ff87`).
- **Ergonomía Móvil:** Botones táctiles con altura mínima de 44px, zonas seguras de interacción inferior para el pulgar.

---

## 3. Requisitos Normativos del Sistema (RFC 2119)

### REQ-LANDING-01: Entrega Condicional de la Landing Page
- El sistema **MUST** servir la nueva vista de Landing Page en la ruta raíz `/` para todo visitante no autenticado que no haya iniciado una conversión activa.
- Si el usuario está autenticado, el sistema **MUST** redirigirlo directamente a la biblioteca (`/books`).
- Si un visitante no autenticado posee un audiolibro generado en sesión (`session('guest_book_id')`), la landing page **MUST** desplegar un banner flotante superior con acceso directo a su audiolibro.

### REQ-LANDING-02: Secciones Semánticas de la Landing Page Kairos
- La landing page **MUST** incluir las siguientes secciones adaptadas:
  1. Header responsivo con isotipo, selector de modo y CTA.
  2. Hero con titular de conversión, subtítulo y maqueta 3D del reproductor.
  3. "Cómo funciona" en 3 pasos secuenciales ilustrados.
  4. Grilla de 6 funcionalidades clave.
  5. Muestra interactiva de audio in-page para escuchar voces neuronales.
  6. Tabla de cuotas y planes (Prueba, Registrado, Ilimitado).
  7. Pie de página con co-branding Mota Zorrilla y neobranding.cl.

### REQ-ORCHID-01: Navegación Estructurada y Drawer Lateral
- La interfaz in-app **MUST** estructurarse con una barra lateral (Sidebar) en pantallas medianas y grandes, agrupada en categorías (*Biblioteca*, *Herramientas*, *Administración*, *Cuenta*).
- En dispositivos móviles (< 768px), el sistema **MUST** ofrecer un Drawer deslizante táctil accionado por botón hamburguesa y un Dock inferior táctil para las 4 acciones más frecuentes.

### REQ-ORCHID-02: Dashboard de Métricas Analíticas (KPI Cards)
- La vista de la biblioteca **MUST** incorporar tarjetas de estadísticas compactas estilo Orchid (Total Documentos, Horas de Audio, Listos, En Proceso) y barra de progreso de cuota asignada.

### REQ-ORCHID-03: Filtros en Píldoras y Búsqueda Instantánea
- El catálogo de audiolibros **MUST** permitir filtrado rápido mediante píldoras interactivas (*Todos*, *Listos*, *En Proceso*, *Con Resumen*) y campo de búsqueda textual.

### REQ-HUB-01: Tarjeta Oficial en `lab.motazorrilla.com`
- El portal principal de Homelab **MUST** incorporar la tarjeta oficial de MotaCastAudio enlazando a `/audiolibros/`, con sus tecnologías destacadas e insignia de estado activo.

---

## 4. Escenarios BDD Ejecutables (Gherkin)

```gherkin
Feature: Landing Page Pública Kairos & Sistema In-App Orchid UI

  Scenario: Un visitante nuevo ingresa a la raíz de la plataforma
    Given que el usuario no ha iniciado sesión
    And no tiene ningún audiolibro previo cargado en su sesión
    When accede a la ruta "/"
    Then visualiza la Landing Page con la sección Hero de MotaCastAudio
    And ve el botón de llamada a la acción "Probar Gratis"
    And ve la maqueta interactiva del reproductor y las características

  Scenario: Un usuario autenticado accede a la raíz de la plataforma
    Given que el usuario ha iniciado sesión con credenciales válidas
    When accede a la ruta "/"
    Then es redirigido automáticamente a la biblioteca in-app "/books"
    And visualiza el nuevo diseño modular estilo Orchid con tarjetas KPI y barra lateral

  Scenario: Un invitado con libro activo navega a la landing page
    Given que un visitante ha subido un libro de prueba en su sesión
    When accede a la ruta "/" con el parámetro de vista landing
    Then observa un banner superior de continuidad con enlace a su audiolibro activo
    And puede regresar al reproductor en un solo clic

  Scenario: Un usuario móvil utiliza la interfaz in-app
    Given que el usuario accede desde una pantalla de 390px de ancho
    When se encuentra en la biblioteca "/books"
    Then el sidebar se oculta automáticamente
    And se visualiza el dock inferior táctil con acceso a Libros, Subir y Menú
    And al pulsar el botón de menú se despliega el Drawer lateral sin recargar la página
```

---

## 5. Criterios de Aceptación y Compuertas de Calidad (Protocolo 3)

1. **Suite de Pruebas:** Los 132 tests existentes y las nuevas pruebas de la Landing Page y Orchid UI **MUST** pasar al 100% en `php artisan test`.
2. **Estilo de Código:** Validación limpia con `vendor/bin/pint --dirty` con 0 errores.
3. **Despliegue Homelab:** Sincronización exitosa en el contenedor `audiolibros-app` y en el hub `lab.motazorrilla.com`.
4. **Verificación Visual:** Inspección satisfactoria con Chrome DevTools MCP en vistas móviles y de escritorio.
