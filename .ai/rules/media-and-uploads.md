---
paths:
  - "app/Http/Requests/StoreBookRequest.php"
  - "resources/views/books/**"
  - "custom-php.ini"
---

# Media & Document Upload Conventions

- **Upload Limits:**
  - Standard users & guests: strict **100 MB (`102400` KB)** ceiling validated in `StoreBookRequest` and client-side `showNoticeModal()`.
  - Admin accounts (`User::isAdmin()`): unlimited total size via **Chunked Upload (`ChunkedUploadController` / `ChunkedUploadService`)** in `<= 25 MB` fragments (`max:5000` chunks) bypassing Cloudflare's 100 MB single-POST cap; PHP `custom-php.ini` allows up to `2048M` for direct uploads.
- **Supported Formats:** Support universal conversion for PDF, DOCX, DOC, TXT, MD, images (OCR) and audio/video (STT).
- **Asynchronous Processing:** Heavy conversion, PyMuPDF extraction, and edge-tts synthesis must be dispatched as queued jobs (`ProcessBookJob`).
