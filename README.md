# 🎧 MotaCastAudio `v0.7.0`
> **Conversor y Reproductor Universal de Documentos a Audiolibros con Voz Neuronal AI**  
> Desarrollado por **Héctor Mota** ([MotaZorrilla](https://motazorrilla.com)) & [Neobranding](https://neobranding.cl).

![MotaCastAudio Banner](public/images/motacast-logo.svg)

---

## 🌟 Características Principales

* **Motor Universal Multi-formato:** Soporte nativo para conversión de archivos **PDF**, **DOCX (Word)**, **Markdown (.md)**, **Texto Plano (.txt)** y **Pegado Directo de Texto**.
* **OCR Automático de Rescate:** Reconocimiento óptico de caracteres para PDFs rasterizados/escaneados mediante **Tesseract OCR (spa + eng)** y Pillow.
* **Síntesis Neuronal con Edge-TTS:** Voces ultra-naturales en español e internacional (incluyendo `es-VE-SebastianNeural`), con modulación de velocidad (`speed_rate`) y tono (`pitch`).
* **Resumen Ejecutivo Inteligente (NLP):** Extracción automatizada de sinopsis de alta densidad informativa y síntesis independiente de pista de audio (`summary.mp3`) para escucha rápida.
* **Visor Universal Integrado (In-App Reader):**
  - **Modo PDF:** Renderizado en Canvas HTML5 en ultra alta resolución (HiDPI Retina) con navegación por miniaturas y zoom.
  - **Modo Editorial:** Tipografía optimizada para Markdown, DOCX y TXT con navegación por capítulos, ajuste dinámico de fuente (`A-` / `A+`) y reproducción sincronizada pista por pista.
* **Modo "Pegar Texto Directo":** Pega artículos, minutas o ensayos desde el portapapeles sin adjuntar archivos, con conteo dinámico de palabras/caracteres y estimación de tiempo de audio en tiempo real.
* **Streaming HTTP 206 (Byte-Range):** Reproducción fluida y scrubbing instantáneo con control de cuotas por usuario y aislamiento estricto de documentos.
* **Arquitectura de Base de Datos Relacional:** Desplegado sobre **MariaDB 11.4 LTS** con orquestación en contenedores Docker y respaldos automatizados.

---

## 🏗️ Stack Tecnológico

| Capa | Tecnología |
| :--- | :--- |
| **Backend** | PHP 8.3 / Laravel 11 / Eloquent ORM |
| **Base de Datos** | MariaDB 11.4 LTS (Docker `audiolibros-db`) |
| **Frontend** | Tailwind CSS v3 / Vanilla JavaScript / Vite / Blade |
| **Lector In-App** | PDF.js (HiDPI Canvas) + Vanilla Markdown/Typography Engine |
| **Motor de Audio / TTS**| Python 3 / `edge-tts` / `mutagen` |
| **Extracción y OCR** | `PyMuPDF` (fitz), `python-docx`, `pytesseract`, `Pillow` |
| **Infraestructura** | Docker Compose / Ubuntu 24.04 LTS Homelab / Tailscale Mesh |

---

## 🚀 Puesta en Marcha Rápida (Desarrollo Local)

```bash
# 1. Clonar el repositorio
git clone https://github.com/MotaZorrilla/motacast-audio.git
cd motacast-audio

# 2. Instalar dependencias de PHP y Node
composer install
npm install

# 3. Configurar entorno
cp .env.example .env
php artisan key:generate

# 4. Compilar assets frontend
npm run build

# 5. Ejecutar migraciones y seeders
php artisan migrate --seed

# 6. Iniciar servidor local
php artisan serve
```

---

## 🧪 Batería de Pruebas Automatizadas

El proyecto cuenta con una cobertura integral de pruebas de Feature y Unitarias con **100% de éxito**:

```bash
php artisan test
```

```text
  Tests:    48 passed (156 assertions)
  Duration: ~7s
```

---

## 📄 Licencia

Desarrollado bajo licencia propietaria para el ecosistema **MotaZorrilla Innovation Lab** y **Neobranding**.
