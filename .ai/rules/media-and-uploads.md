---
paths:
  - "app/Http/Requests/StoreBookRequest.php"
  - "resources/views/books/**"
  - "custom-php.ini"
---

# Media & Document Upload Conventions

- **Upload Limits:** Enforce a strict 100 MB (`102400` KB) file ceiling across the entire stack:
  - Nginx Gateway: `client_max_body_size 100M;`
  - PHP Runtime: `upload_max_filesize = 100M`, `post_max_size = 100M`
  - Laravel Form Request: `pdf_file => ...|max:102400`
  - Client-side: JavaScript pre-check before submission using `showNoticeModal()`.
- **Supported Formats:** Support universal conversion for PDF, DOCX, DOC, TXT, MD, images (OCR) and audio/video (STT).
- **Asynchronous Processing:** Heavy conversion, PyMuPDF extraction, and edge-tts synthesis must be dispatched as queued jobs (`ProcessBookJob`).
