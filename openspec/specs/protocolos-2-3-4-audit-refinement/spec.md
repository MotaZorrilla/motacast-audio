# SPEC-MCA-003: Auditoría y Refinamiento Integral según Protocolos 2, 3 y 4

**Estado:** Aprobado para Ejecución  
**Fecha:** 2026-10-08  
**Autor:** Antigravity (Google DeepMind) & Héctor Mota Zorrilla  
**Proyecto:** MotaCastAudio (`v2.7`)  
**Ecosistema:** Homelab Mota Zorrilla (`lab.motazorrilla.com`)

---

## 1. Contexto y Objetivos

El usuario solicitó una revisión exhaustiva según los **Protocolos 2, 3 y 4** del Ecosistema Mota Zorrilla y la ejecución inmediata de todas las sugerencias identificadas:
- **Protocolo 2 (Auditoría Colegiada de las 7 Disciplinas & OpenSpec):**
  Diagnóstico 360° en Arquitectura Limpia, Usabilidad Móvil, Craft Floor, Seguridad, Persistencia y SRE.
- **Protocolo 3 (Ingeniería Robusta & Trofeo de Testing):**
  Prioridad en Feature Tests, cobertura exhaustiva de Unhappy Paths (RBAC, fallos de entrada, rate limiting, tokens), y cumplimiento de la compuerta CI/CD.
- **Protocolo 4 (Craft Floor & Despliegue Transversal):**
  Erradicación total de diálogos nativos (`alert`, `confirm`), aseguramiento de los 7 estados interactivos, WCAG AA y despliegue automático con sincronización en Homelab Ubuntu.

---

## 2. Auditoría Colegiada de las 7 Disciplinas (Protocolo 2)

### Disciplina 1: UI/UX & Ergonomía Móvil
- **Diagnóstico:** El rediseño de Orchid incorporó el dock táctil y el slide-over drawer con touch targets $\ge 44$px. No obstante, en la vista del reproductor (`books/show.blade.php`), en la administración de usuarios (`admin/users/index.blade.php`), telemetría (`admin/telemetry.blade.php`) y tickets (`admin/tickets.blade.php`), aún persistían llamadas a `confirm(...)` nativas del navegador, lo que congela el renderizado y quiebra la inmersión en teléfonos.
- **Dictamen:** Implementar un motor universal de confirmación modal no bloqueante (`window.showAppConfirm`) con backdrop táctil, animación de escala y soporte completo de teclado (Escape / Enter).

### Disciplina 2: Frontend Architecture & Performance
- **Diagnóstico:** En la landing page (`#demo`), el botón de reproducción de muestras de voz carecía de un estado deshabilitado visual con spinner mientras descargaba el buffer de audio. El formulario de subida carecía de protección visual contra doble submit.
- **Dictamen:** Integrar spinners animados y bloqueo temporal del botón (`disabled` state) durante la carga de audio y el despacho de formularios. Asegurar anillos de enfoque `focus-visible:ring-2 focus-visible:ring-[#00ff87]` en todos los controles interactivos.

### Disciplina 3: Backend & Lógica de Negocio (Clean Architecture & SRP)
- **Diagnóstico:** Los controladores delegan adecuadamente en Services y FormRequests. Se identificó la necesidad de validar que cualquier parámetro malicioso o fuzzeado en la API pública de preescucha (`/voices/preview`) caiga siempre en valores por defecto seguros sin generar excepciones ni fugas de rutas.
- **Dictamen:** Mantener la sanitización estricta de parámetros en `VoicePreviewController` y proteger las rutas de administración con políticas RBAC rígidas probadas contra usuarios estándar.

### Disciplina 4: Base de Datos & Persistencia
- **Diagnóstico:** Esquema relacional con integridad referencial e índices en `books.status`, `books.user_id` y `books.guest_fingerprint`. La eliminación atómica en cascada (`DeleteBookAction`) remueve registros y archivos físicos.
- **Dictamen:** Preservar la consistencia y verificar en tests que la eliminación de un usuario o documento no deje huérfanos en almacenamiento.

### Disciplina 5: DevOps & Infraestructura (Docker & Homelab SRE)
- **Diagnóstico:** Mapeo de volumen bidireccional entre `/home/motazorrilla/apps/audiolibros` y `/app` en el contenedor `audiolibros-app`.
- **Dictamen:** Mantener sincronización atómica con `git pull origin main`, limpieza de vistas con `php artisan view:clear` y verificación por cURL de HTTP/2 200 en `https://lab.motazorrilla.com/audiolibros/`.

### Disciplina 6: Seguridad, QA & Negocio
- **Diagnóstico:** Multi-tenancy implementado vía `BookPolicy`. Invitados anónimos solo pueden ver su propio libro de sesión.
- **Dictamen:** Crear pruebas de Feature para "Unhappy Paths": denegación 403 / redirección a login si un usuario regular intenta ver o eliminar libros ajenos o acceder a endpoints de administración.

### Disciplina 7: Dirección de Arte & Craft Floor Impeccable (Protocolo 4)
- **Diagnóstico:** Los estilos respetan el modo oscuro/claro y la paleta neón (`#00ff87`, `#00f0ff`, `slate-950`). Se detectó la necesidad de garantizar los 7 estados interactivos (Default, Hover, Focus-visible, Active, Disabled, Loading, Empty) en todos los módulos de interacción.
- **Dictamen:** Cero alertas y confirmaciones nativas en el 100% de las vistas Blade del proyecto.

---

## 3. Requisitos Normativos (RFC 2119)

1. El sistema **MUST NOT** invocar `alert()`, `confirm()` o `prompt()` nativos en ninguna plantilla Blade ni archivo JavaScript del proyecto.
2. El sistema **SHALL** disponer de un componente modal global `id="appGlobalConfirmModal"` invocado por `window.showAppConfirm({ title, message, confirmText, confirmClass, onConfirm })`.
3. Todos los botones de confirmación destructiva (Eliminar Documento, Reiniciar Prueba, Vaciar Logs, Eliminar Usuario, Eliminar Ticket) **MUST** utilizar el nuevo modal táctil no bloqueante.
4. El reproductor de muestras de voz en la Landing Page **SHALL** mostrar un estado de carga ("Loading...") con spinner animado y bloquear clics redundantes mientras el audio se obtiene del servidor.
5. Los endpoints de la aplicación **MUST** rechazar solicitudes no autorizadas de usuarios regulares que intenten manipular recursos ajenos o paneles de administración con un código de respuesta HTTP 403 o redirección controlada.
6. La suite de pruebas automatizadas **SHALL** incluir una prueba estática y funcional que garantice la ausencia de `confirm(` y `alert(` en todas las vistas Blade.

---

## 4. Escenarios BDD Ejecutables (GIVEN-WHEN-THEN)

### Escenario 1: Erradicación total de diálogos nativos en la interfaz
- **GIVEN** que un usuario o administrador interactúa con cualquier pantalla de la aplicación
- **WHEN** acciona una función de eliminación, reseteo o limpieza de datos
- **THEN** el sistema no debe abrir ningún diálogo nativo del navegador (`confirm`/`alert`)
- **AND** debe mostrar el modal accesible y estilizado `#appGlobalConfirmModal` solicitando confirmación con opciones "Cancelar" y botón de acción táctil $\ge 44$px.

### Escenario 2: Protección RBAC y Unhappy Paths de Usuarios
- **GIVEN** un usuario autenticado estándar "Usuario A"
- **WHEN** intenta acceder o eliminar un libro perteneciente a "Usuario B"
- **THEN** la aplicación debe denegar la acción con respuesta 403 o redirección segura con mensaje informativo
- **AND** ningún dato del libro ajeno debe ser expuesto ni alterado.

### Escenario 3: Resiliencia ante parámetros inválidos en muestras de voz
- **GIVEN** un visitante público en la landing page
- **WHEN** realiza una petición GET a `/voices/preview` con parámetros no válidos (`voice=malicious_input`, `speed_rate=999%`, `pitch=invalid`)
- **THEN** el controlador debe normalizar los valores a las opciones por defecto (`es-VE-SebastianNeural`, `+0%`, `+0Hz`)
- **AND** entregar el archivo de audio MP3 con código HTTP 200 sin arrojar error 500.

### Escenario 4: Estado de carga táctil en la Landing Page
- **GIVEN** un visitante en la sección `#demo` de la Landing Page
- **WHEN** hace clic en "Escuchar Muestra de Audio"
- **THEN** el botón debe deshabilitarse temporalmente, mostrar un spinner de carga y el texto "Cargando muestra..."
- **AND** al comenzar la reproducción, cambiar a "⏸ Pausar Muestra" habilitando nuevamente la interacción.
