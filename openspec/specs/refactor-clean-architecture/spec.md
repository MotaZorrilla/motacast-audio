# OpenSpec Specification: Refactorización a Clean Architecture & Craft Floor (MotaCastAudio)

- **ID de Especificación:** `SPEC-MCA-001`
- **Característica:** `refactor-clean-architecture`
- **Versión:** `1.0.0`
- **Fecha:** `2026-10-06`
- **Estado:** `APROBADO POR COMITÉ COLEGIADO (FASE AUDITORÍA P2)`
- **Autores:** Equipo Colegiado de las 7 Disciplinas (Antigravity & Héctor Mota)
- **Ámbito:** `C:\xampp\htdocs\audiolibros` / `https://lab.motazorrilla.com/audiolibros/`

---

## 1. Declaración de Alcance y Propósito

El presente documento establece las especificaciones formales y ejecutables para la transición de la arquitectura actual de **MotaCastAudio** hacia un modelo de **Clean Architecture**, alta cohesión, bajo acoplamiento y estricta adhesión al **Principio de Responsabilidad Única (SRP)** y **Segregación de Interfaces / Inversión de Dependencias (DIP)**.

Esta especificación preservará rigurosamente:
1. La persistencia íntegra de los 46 libros, archivos de audio MP3, usuarios y registros de tráfico existentes en MariaDB 11.4 LTS.
2. Los contratos públicos REST y endpoints HTTP consumidos por el reproductor web, la API de telemetría y las vistas Blade.
3. El 100% de la suite de pruebas unitarias y funcionales (92 tests, 352 aserciones).

---

## 2. Requisitos Normativos del Sistema (RFC 2119)

### REQ-ARCH-01: Desacoplamiento de Autorización en Políticas de Dominio (Policies)
- **Declaración:** El controlador `BookController` **SHALL NOT** ejecutar inspecciones de cabeceras `User-Agent`, lógica de sesiones de invitados ni comprobaciones de rol administrativo de manera procedural en métodos internos como `authorizeBookAccess()`.
- **Obligación:** El sistema **MUST** delegar todas las comprobaciones de autorización a una política dedicada `App\Policies\BookPolicy` y hacer uso del método nativo `$this->authorize('view', $book)` o gates registrados de Laravel.
- **Excepción de Crawlers:** La autorización de bots y crawlers de redes sociales (Open Graph) **SHOULD** resolverse mediante un middleware de detección de agentes (`SocialCrawlerMiddleware`) o regla de política formalizada para garantizar trazabilidad.

### REQ-ARCH-02: Extracción de Almacenamiento Físico y Ciclo de Vida (Actions / Observers)
- **Declaración:** Los métodos del controlador `BookController::store()` y `BookController::destroy()` **SHALL NOT** invocar directamente métodos de bajo nivel de `Illuminate\Support\Facades\Storage` para borrado recursivo de directorios o resolución MIME en crudo.
- **Obligación:** El ciclo de eliminación física de archivos (`pdf_path`, `audiobooks/{id}`) **MUST** ser coordinado por una acción de dominio (`App\Actions\Book\DeleteBookAction`) o mediante el evento de modelo `BookObserver::deleting`, garantizando atomicidad transaccional con la base de datos.
- **Resolución MIME:** La resolución de tipos de contenido y cabeceras de streaming **MUST** delegarse a una clase de servicio (`App\Services\MimeTypeResolver`) o métodos canónicos de Laravel.

### REQ-ARCH-03: Desacoplamiento de Dependencias e I/O en Workers Asíncronos (`ProcessBookJob`)
- **Declaración:** El worker `App\Jobs\ProcessBookJob` **SHALL NOT** utilizar el patrón *Service Locator* procedural (`app(\App\Services\TtsTextNormalizerService::class)`) dentro del cuerpo de ejecución de `handle()`.
- **Obligación:** Todas las dependencias de servicio **MUST** ser inyectadas mediante inyección de dependencias en el constructor del Job o como argumentos del método `handle()`.
- **Optimización de Persistencia:** El bucle de síntesis de capítulos **SHALL NOT** emitir dos consultas de escritura a la tabla `books` (`increment` y `update`) en cada iteración individual. El Job **MUST** acumular la duración total en memoria y emitir actualizaciones consolidadas por lotes o al finalizar la fase de síntesis.

### REQ-ARCH-04: Integridad Referencial e Indexación en Base de Datos
- **Declaración:** El esquema de persistencia en MariaDB 11.4 LTS **SHALL NOT** realizar barridos completos de tabla (*full table scans*) al filtrar audiolibros por estado, usuario o correlación de capítulos.
- **Obligación:**
  1. Se **MUST** añadir un índice simple en la columna `books(status)`.
  2. Se **MUST** añadir un índice compuesto único en la tabla `chapters(book_id, chapter_number)` para evitar duplicidad ordinal ante reintentos de jobs.
  3. Se **MUST** añadir un índice simple en la columna `chapters(status)`.
  4. Los campos de búsqueda textual (`title`, `author`) **SHOULD** disponer de índices compuestos o índices FullText optimizados.

### REQ-ARCH-05: Tipado Estricto de Dominio y Enums Respaldados (Backed Enums)
- **Declaración:** La lógica de negocio y las máquinas de estados de los modelos `Book` y `Chapter` **SHALL NOT** depender de cadenas de texto mágicas dispersas (`'pending'`, `'extracting'`, `'synthesizing'`, `'ready'`, `'failed'`).
- **Obligación:**
  1. El sistema **MUST** definir `App\Enums\BookStatus` y `App\Enums\ChapterStatus` como enums respaldados por strings en PHP 8.2+.
  2. Los modelos **MUST** utilizar el atributo `$casts` para convertir automáticamente las columnas a sus Enums correspondientes.
  3. Todos los métodos de relación Eloquent (`user()`, `chapters()`, `trafficLogs()`) **MUST** declarar explícitamente sus tipos de retorno (`BelongsTo`, `HasMany`).

### REQ-ARCH-06: Estructuración y Claridad de la Suite de Pruebas (Trofeo de Testing)
- **Declaración:** La suite de pruebas funcionales **SHALL NOT** alojar pruebas de producción críticas dentro de archivos de plantilla por defecto (`tests/Feature/ExampleTest.php`).
- **Obligación:**
  1. Las 21 pruebas de `ExampleTest.php` **MUST** ser segregadas en archivos semánticos: `GuestTrialWorkflowTest.php` y `BookUploadValidationTest.php`.
  2. `ExampleTest.php` **MUST** quedar exclusivamente como un test de verificación básica de conectividad o eliminarse conforme al estándar BoozLab.

### REQ-ARCH-07: Modularización de Frontend, Eliminación de Inline Scripts y Tokens P4
- **Declaración:** Las vistas Blade (`resources/views/books/show.blade.php`, `create.blade.php`) **SHALL NOT** contener bloques monolíticos de JavaScript en línea superiores a 100 líneas ni estilos de tamaño arbitrario (`text-[10px]`, `text-[11px]`).
- **Obligación:**
  1. El controlador del reproductor web, la lógica del modal de lectura y el renderizado PDF.js **MUST** ser extraídos a módulos ES6 independientes en `resources/js/controllers/` empaquetados por Vite.
  2. La tipografía y espaciados **MUST** migrar a tokens semánticos del Craft Floor con tipografía fluida `clamp()` y clases Tailwind canónicas.

---

## 3. Escenarios BDD Ejecutables (GIVEN / WHEN / THEN)

### Escenario 1: Autorización de Audiolibros Privados mediante `BookPolicy`
```gherkin
GIVEN un usuario no autenticado (Guest)
  AND un audiolibro privado perteneciente a un usuario registrado
WHEN el usuario intenta acceder a la ruta GET /books/{id}
THEN el sistema ejecuta la comprobación a través de BookPolicy::view
  AND redirige al usuario a la ruta /login con el mensaje informativo de autenticación requerida
  AND no se expone ningún contenido multimedia ni texto de capítulos.
```

### Escenario 2: Acceso de Crawlers de Redes Sociales para Open Graph Previews
```gherkin
GIVEN una petición HTTP entrante con cabecera User-Agent identificada como "facebookexternalhit/1.1" o "WhatsApp/2.24"
  AND un audiolibro con estado "ready"
WHEN la petición consulta la ruta pública /books/{id}
THEN la política BookPolicy y el middleware permiten el paso inmediato
  AND la respuesta retorna código HTTP 200
  AND se renderizan todas las etiquetas meta Open Graph (og:title, og:description, og:image, og:audio)
  AND no se bloquea ni se solicita inicio de sesión al crawler.
```

### Escenario 3: Eliminación Segura y Atómica de Audiolibros con `DeleteBookAction`
```gherkin
GIVEN un audiolibro con id 99 con archivo PDF en "pdfs/manual.pdf" y 5 pistas MP3 en "audiobooks/99/"
  AND el usuario autenticado es el propietario del libro
WHEN el usuario envía una petición DELETE a /books/99
THEN se ejecuta DeleteBookAction dentro de una transacción de base de datos
  AND se eliminan los archivos físicos del disco "public"
  AND se eliminan en cascada los registros de chapters y traffic_logs asociados
  AND el audiolibro 99 ya no existe en la base de datos
  AND el usuario es redirigido a /books con mensaje de confirmación exitoso.
```

### Escenario 4: Acumulación de Duración en Memoria durante Procesamiento Asíncrono
```gherkin
GIVEN un libro con 20 capítulos encolado para síntesis en ProcessBookJob
WHEN el worker procesa secuencialmente cada capítulo a través de SynthesizerService
THEN el normalizador fonético es inyectado por el contenedor de servicios
  AND los capítulos se guardan individualmente con estado "ready"
  AND la actualización de "total_duration" y "processed_chapters" en la tabla books se realiza de forma agrupada al concluir el ciclo
  AND la base de datos recibe un 80% menos de consultas de actualización concurrentes.
```

### Escenario 5: Validación de Estados mediante Backed Enums de PHP 8.2
```gherkin
GIVEN una instancia de modelo Book en estado BookStatus::Pending
WHEN el administrador intenta asignar un estado inválido como "en_progreso"
THEN PHP lanza un ValueError en tiempo de ejecución
  AND la base de datos nunca almacena un estado no catalogado
  AND el método $book->status->label() retorna la descripción humana formateada.
```

### Escenario 6: Validación de Integridad de Índices en Base de Datos
```gherkin
GIVEN la tabla books y la tabla chapters con las nuevas migraciones aplicadas
WHEN se ejecuta una consulta con cláusula WHERE status = 'ready' y WHERE book_id = X AND chapter_number = Y
THEN el plan de ejecución de MySQL (EXPLAIN) confirma el uso del índice `books_status_index`
  AND el índice compuesto `chapters_book_id_chapter_number_unique` impide registros duplicados con costo de búsqueda O(log N).
```

---

## 4. Compuerta de Despliegue Automatizado (CI/CD Homelab Gate)

Para promover las refactorizaciones de esta especificación hacia el servidor de producción en `lab.motazorrilla.com`, la suite de CI/CD **MUST** validar de forma estricta:

1. **Pruebas Automatizadas:** `php artisan test` con 100% de tests en verde (mínimo 92 tests, 0 fallos).
2. **Estilo de Código:** `vendor/bin/pint --dirty` con 0 advertencias de estilo PSR-12.
3. **Validación de Especificaciones:** Validación de escenarios BDD cumplidos.
4. **Verificación en Vivo:** Inspección de 0 errores en consola vía Chrome DevTools MCP en `https://lab.motazorrilla.com/audiolibros/`.
