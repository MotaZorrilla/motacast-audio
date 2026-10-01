#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
AudioLab MZ - Universal Document Extraction Engine v2.0
Supports:
  - PDF (PyMuPDF, with OCR fallback via Tesseract for scanned/rasterized PDFs)
  - DOCX (python-docx)
  - TXT / Markdown (direct UTF-8 read, segmented by headings or blank lines)
"""

import sys
import os
import json
import re
import argparse
import tempfile


# ──────────────────────────────────────────────────────────────────────────────
# TEXT CLEANING
# ──────────────────────────────────────────────────────────────────────────────

def clean_extracted_text(text: str) -> str:
    if not text:
        return ""

    text = text.replace('\r\n', '\n').replace('\r', '\n')

    # Fix hyphenated words at line breaks (e.g., transfor-\nmación → transformación)
    text = re.sub(r'([a-zA-ZáéíóúÁÉÍÓÚñÑ]+)-\n([a-zA-ZáéíóúÁÉÍÓÚñÑ]+)', r'\1\2', text)

    # Fix soft hyphens and unusual whitespace
    text = text.replace('\xad', '')
    text = re.sub(r'[ \t\f\v]+', ' ', text)

    # Remove standalone page numbers
    text = re.sub(r'^\s*(?:Página|Pág\.?|Page)?\s*\d+\s*(?:de|\/)\s*\d+\s*$', '', text,
                  flags=re.MULTILINE | re.IGNORECASE)
    text = re.sub(r'^\s*\d+\s*$', '', text, flags=re.MULTILINE)

    # Remove control characters
    text = re.sub(r'[\x00-\x08\x0b\x0c\x0e-\x1f\x7f]', '', text)

    # Consolidate multiple linebreaks
    text = re.sub(r'\n{3,}', '\n\n', text)

    # Bridge single linebreaks into a space (NLP continuity)
    text = re.sub(r'(?<!\n)\n(?!\n)', ' ', text)

    # Consolidate consecutive spaces
    text = re.sub(r'[ \t]+', ' ', text)

    return text.strip()


# ──────────────────────────────────────────────────────────────────────────────
# PDF EXTRACTION (PyMuPDF + OCR fallback)
# ──────────────────────────────────────────────────────────────────────────────

def is_scanned_pdf(pdf_path: str, threshold: int = 50) -> bool:
    """Returns True if the PDF has fewer than `threshold` total text characters (likely scanned)."""
    try:
        import pymupdf as fitz
        doc = fitz.open(pdf_path)
        total_chars = sum(len(page.get_text("text").strip()) for page in doc)
        doc.close()
        return total_chars < threshold
    except Exception:
        return False


def extract_with_pymupdf(pdf_path: str):
    import pymupdf as fitz
    doc = fitz.open(pdf_path)
    metadata = doc.metadata or {}
    title = metadata.get('title', '').strip()
    author = metadata.get('author', '').strip()

    pages_text = []
    for page in doc:
        txt = page.get_text("text")
        cleaned = clean_extracted_text(txt)
        if cleaned:
            pages_text.append(cleaned)
    doc.close()
    return title, author, pages_text


def extract_with_ocr(pdf_path: str):
    """Extract text from rasterized PDFs using Tesseract OCR."""
    import pymupdf as fitz
    import pytesseract
    from PIL import Image
    import io

    doc = fitz.open(pdf_path)
    metadata = doc.metadata or {}
    title = metadata.get('title', '').strip()
    author = metadata.get('author', '').strip()

    pages_text = []
    # Render at 200 DPI for good OCR accuracy without being too slow
    matrix = fitz.Matrix(200 / 72, 200 / 72)

    for page_num, page in enumerate(doc):
        try:
            pix = page.get_pixmap(matrix=matrix)
            img_bytes = pix.tobytes("png")
            img = Image.open(io.BytesIO(img_bytes))

            # OCR with Spanish + English
            custom_config = r'--oem 3 --psm 3'
            txt = pytesseract.image_to_string(img, lang='spa+eng', config=custom_config)
            cleaned = clean_extracted_text(txt)
            if cleaned:
                pages_text.append(cleaned)
        except Exception as e:
            # Skip pages that fail OCR individually
            sys.stderr.write(f"[OCR] Página {page_num + 1} falló: {e}\n")
            continue

    doc.close()
    return title, author, pages_text


def extract_pdf(pdf_path: str):
    """Main PDF extraction: tries normal text, falls back to OCR if scanned."""
    scanned = is_scanned_pdf(pdf_path, threshold=50)

    if scanned:
        sys.stderr.write("[INFO] PDF escaneado detectado — activando OCR con Tesseract...\n")
        try:
            return extract_with_ocr(pdf_path), True  # (title, author, pages), ocr_used
        except ImportError as e:
            raise Exception(
                f"El PDF está escaneado pero Tesseract/pytesseract no está instalado en este entorno. "
                f"Instala con: pip install pytesseract pillow  y  apk add tesseract-ocr tesseract-ocr-data-spa. "
                f"Detalle: {e}"
            )
    else:
        try:
            return extract_with_pymupdf(pdf_path), False
        except Exception as e1:
            try:
                import pypdf
                reader = pypdf.PdfReader(pdf_path)
                title = ""
                author = ""
                if reader.metadata:
                    title = reader.metadata.get('/Title', '') or ''
                    author = reader.metadata.get('/Author', '') or ''
                pages_text = []
                for page in reader.pages:
                    txt = page.extract_text() or ''
                    cleaned = clean_extracted_text(txt)
                    if cleaned:
                        pages_text.append(cleaned)
                return (title, author, pages_text), False
            except Exception as e2:
                raise Exception(f"Error al procesar el PDF: {e1} / {e2}")


# ──────────────────────────────────────────────────────────────────────────────
# DOCX EXTRACTION
# ──────────────────────────────────────────────────────────────────────────────

def extract_docx(docx_path: str):
    from docx import Document
    doc = Document(docx_path)

    title = ""
    author = ""

    # Try to get core properties
    try:
        props = doc.core_properties
        title = props.title or ""
        author = props.author or ""
    except Exception:
        pass

    # Extract paragraphs preserving headings
    paragraphs = []
    current_section = []

    for para in doc.paragraphs:
        text = para.text.strip()
        if not text:
            continue
        style_name = para.style.name if para.style else ""
        if style_name.startswith(('Heading', 'Título', 'Title', 'Subtitle')):
            if current_section:
                paragraphs.append('\n\n'.join(current_section))
                current_section = []
            current_section.append(f"\n{text}\n")
        else:
            current_section.append(text)

    # Also extract text from tables inside docx
    for table in doc.tables:
        table_rows = []
        for row in table.rows:
            row_cells = [c.text.strip() for c in row.cells if c.text.strip()]
            if row_cells:
                deduped = []
                for cell in row_cells:
                    if not deduped or deduped[-1] != cell:
                        deduped.append(cell)
                table_rows.append(' | '.join(deduped))
        if table_rows:
            paragraphs.append('\n'.join(table_rows))

    if current_section:
        paragraphs.append('\n\n'.join(current_section))

    pages_text = [clean_extracted_text(p) for p in paragraphs if p.strip()]
    return title, author, pages_text


# ──────────────────────────────────────────────────────────────────────────────
# TXT / MARKDOWN EXTRACTION
# ──────────────────────────────────────────────────────────────────────────────

def extract_text_or_markdown(file_path: str, is_markdown: bool = False):
    """Extract text from .txt or .md files."""
    try:
        with open(file_path, 'r', encoding='utf-8') as f:
            raw = f.read()
    except UnicodeDecodeError:
        with open(file_path, 'r', encoding='latin-1') as f:
            raw = f.read()

    title = ""
    author = ""

    if is_markdown:
        # Extract title from first H1 heading
        h1_match = re.search(r'^#\s+(.+)$', raw, re.MULTILINE)
        if h1_match:
            title = h1_match.group(1).strip()

        # Split by headings to create logical sections
        parts = re.split(r'(?=^#{1,3}\s+)', raw, flags=re.MULTILINE)
        pages_text = [clean_extracted_text(p) for p in parts if p.strip()]
    else:
        # Split by blank lines (double newlines) for plain text
        parts = raw.split('\n\n')
        pages_text = [clean_extracted_text(p) for p in parts if p.strip()]

    return title, author, pages_text


# ──────────────────────────────────────────────────────────────────────────────
# IMAGE OCR EXTRACTION (Tesseract)
# ──────────────────────────────────────────────────────────────────────────────

def extract_image_ocr(image_path: str):
    """
    Extract text from an image (.png, .jpg, .jpeg, .webp, .bmp) using Tesseract OCR.
    """
    import pytesseract
    from PIL import Image

    title = ""
    author = "OCR de Imagen"

    try:
        img = Image.open(image_path)
        if img.mode not in ('L', 'RGB'):
            img = img.convert('RGB')

        # Run OCR with Spanish and English trained models
        custom_config = r'--oem 3 --psm 3'
        raw_text = pytesseract.image_to_string(img, lang='spa+eng', config=custom_config)
        cleaned = clean_extracted_text(raw_text)

        if not cleaned or len(cleaned.split()) < 3:
            # Fallback with PSM 6 (single uniform block of text)
            raw_text_fallback = pytesseract.image_to_string(img, lang='spa+eng', config=r'--oem 3 --psm 6')
            cleaned_fallback = clean_extracted_text(raw_text_fallback)
            if cleaned_fallback:
                cleaned = cleaned_fallback

        pages_text = [cleaned] if cleaned else []
        return title, author, pages_text
    except Exception as e:
        raise Exception(f"Error procesando OCR de la imagen: {e}")


# ──────────────────────────────────────────────────────────────────────────────
# AUDIO SPEECH-TO-TEXT EXTRACTION (ffmpeg + SpeechRecognition)
# ──────────────────────────────────────────────────────────────────────────────

def extract_audio_stt(audio_path: str):
    """
    Transcribes spoken audio (.mp3, .wav, .m4a, .ogg, .aac, .flac) into text
    using ffmpeg chunking and SpeechRecognition (Google STT es-ES).
    """
    import subprocess
    import tempfile
    import glob
    import speech_recognition as sr

    title = ""
    author = "Transcripción de Audio"

    # Check ffmpeg availability
    try:
        subprocess.run(["ffmpeg", "-version"], stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL, check=True)
    except Exception:
        raise Exception("ffmpeg no está disponible en el sistema para procesar el archivo de audio.")

    with tempfile.TemporaryDirectory() as tmpdir:
        chunk_template = os.path.join(tmpdir, "chunk_%03d.wav")
        cmd = [
            "ffmpeg", "-y",
            "-i", audio_path,
            "-f", "segment",
            "-segment_time", "45",
            "-c:a", "pcm_s16le",
            "-ar", "16000",
            "-ac", "1",
            chunk_template
        ]

        proc = subprocess.run(cmd, stdout=subprocess.PIPE, stderr=subprocess.PIPE)
        if proc.returncode != 0:
            raise Exception(f"ffmpeg no pudo decodificar el audio: {proc.stderr.decode('utf-8', errors='ignore')[:300]}")

        chunk_files = sorted(glob.glob(os.path.join(tmpdir, "chunk_*.wav")))
        if not chunk_files:
            raise Exception("No se pudieron generar segmentos de audio para transcribir.")

        recognizer = sr.Recognizer()
        transcribed_chunks = []

        for idx, chunk_file in enumerate(chunk_files):
            try:
                with sr.AudioFile(chunk_file) as source:
                    audio_data = recognizer.record(source)
                text = recognizer.recognize_google(audio_data, language="es-ES")
                text = text.strip()
                if text:
                    transcribed_chunks.append(text)
            except sr.UnknownValueError:
                sys.stderr.write(f"[STT] Segmento {idx+1}/{len(chunk_files)} sin voz inteligible.\n")
                continue
            except sr.RequestError as e:
                sys.stderr.write(f"[STT Error] Fallo al consultar servicio STT en segmento {idx+1}: {e}\n")
                continue
            except Exception as e:
                sys.stderr.write(f"[STT Error] Error procesando segmento {idx+1}: {e}\n")
                continue

        if not transcribed_chunks:
            raise Exception("No se detectó voz o habla comprensible en el archivo de audio.")

        # Group chunks into paragraphs (~2-3 chunks per section)
        grouped_sections = []
        current_section = []
        for chunk in transcribed_chunks:
            formatted = chunk[0].upper() + chunk[1:]
            if not formatted.endswith(('.', '!', '?')):
                formatted += '.'
            current_section.append(formatted)
            if len(current_section) >= 3:
                grouped_sections.append(" ".join(current_section))
                current_section = []

        if current_section:
            grouped_sections.append(" ".join(current_section))

        pages_text = [clean_extracted_text(s) for s in grouped_sections if s.strip()]

    return title, author, pages_text


# ──────────────────────────────────────────────────────────────────────────────
# DOCUMENT SUMMARIZATION (Extractive NLP)
# ──────────────────────────────────────────────────────────────────────────────

def generate_summary(text: str, max_sentences: int = 3, max_words: int = 140) -> str:
    """
    Generates a concise executive summary from the document text using
    an extractive ranking algorithm based on keyword frequency, sentence position,
    and lead paragraph weighting.
    """
    if not text or len(text.strip()) < 50:
        return text.strip()

    # Split into candidate sentences
    raw_sentences = re.split(r'(?<=[.!?])\s+', text)
    sentences = [
        s.strip() for s in raw_sentences
        if len(s.strip()) > 25 and not s.strip().startswith(('http', 'www', '#', 'Capítulo', 'Chapter', 'Parte', 'Sección'))
    ]

    if not sentences:
        words = text.split()
        return ' '.join(words[:max_words])

    if len(sentences) <= max_sentences:
        return ' '.join(sentences)[:700]

    # Stopwords to ignore in frequency scoring (Spanish + English common)
    stopwords = {
        'de', 'la', 'que', 'el', 'en', 'y', 'a', 'los', 'del', 'se', 'las', 'por', 'un', 'para', 'con', 'no',
        'una', 'su', 'al', 'lo', 'como', 'más', 'pero', 'sus', 'le', 'ya', 'o', 'este', 'sí', 'porque', 'esta',
        'entre', 'cuando', 'muy', 'sin', 'sobre', 'también', 'me', 'hasta', 'hay', 'donde', 'quien', 'desde',
        'todo', 'nos', 'durante', 'todos', 'uno', 'les', 'ni', 'contra', 'otros', 'ese', 'eso', 'ante', 'ellos',
        'estos', 'estas', 'ser', 'es', 'son', 'fue', 'era', 'sido', 'tener', 'tiene', 'tienen', 'hacer', 'hace',
        'the', 'of', 'and', 'to', 'in', 'is', 'for', 'that', 'with', 'on', 'as', 'at', 'this', 'by', 'an', 'be'
    }

    # Calculate word frequency
    words = [w.lower() for w in re.findall(r'\b[a-zA-ZáéíóúÁÉÍÓÚñÑ]{3,}\b', text)]
    word_freq = {}
    for w in words:
        if w not in stopwords:
            word_freq[w] = word_freq.get(w, 0) + 1

    if not word_freq:
        return ' '.join(sentences[:max_sentences])

    # Score each sentence
    scored_sentences = []
    for idx, sentence in enumerate(sentences):
        s_words = [w.lower() for w in re.findall(r'\b[a-zA-ZáéíóúÁÉÍÓÚñÑ]{3,}\b', sentence)]
        if not s_words:
            continue

        # Word significance score normalized
        score = sum(word_freq.get(w, 0) for w in s_words) / len(s_words)

        # Position weight: first 20% of sentences get a boost (lead paragraph bias)
        position_ratio = idx / len(sentences)
        if position_ratio < 0.20:
            score *= 1.45
        elif position_ratio > 0.80:
            score *= 1.20  # Conclusion bias

        # Ideal sentence length between 10 and 35 words
        word_count = len(s_words)
        if 10 <= word_count <= 35:
            score *= 1.25
        elif word_count < 8:
            score *= 0.60

        scored_sentences.append((score, idx, sentence))

    # Pick top scoring sentences
    scored_sentences.sort(key=lambda x: x[0], reverse=True)
    top_candidates = scored_sentences[:max_sentences * 2]

    # Re-order top candidates chronologically as they appeared in original text
    top_candidates.sort(key=lambda x: x[1])

    summary_sentences = []
    current_words = 0
    for _, _, sentence in top_candidates:
        s_words = len(sentence.split())
        if current_words + s_words > max_words and summary_sentences:
            break
        summary_sentences.append(sentence)
        current_words += s_words
        if len(summary_sentences) >= max_sentences:
            break

    return ' '.join(summary_sentences).strip()


# ──────────────────────────────────────────────────────────────────────────────
# CHAPTER SEGMENTATION (shared)
# ──────────────────────────────────────────────────────────────────────────────

def segment_into_chapters(full_text: str, default_title: str = "Capítulo"):
    """
    Intelligently splits full text into logical chapters / tracks:
    1. By explicit heading markers (Capítulo X, Chapter X, Sección X, Tema X).
    2. Or chunking into pleasant reading units (~750 words per track).
    """
    chapter_pattern = re.compile(
        r'(?:\n|^)(Capítulo|Capitulo|Chapter|Sección|Seccion|Módulo|Modulo|Tema|Parte|Unidad)\s+'
        r'([0-9IVXLCDM]+|[A-Z])[\.\:\s\-—]+([^\n]{2,80})',
        re.IGNORECASE
    )

    matches = list(chapter_pattern.finditer(full_text))
    chapters = []

    if len(matches) >= 2:
        for i, match in enumerate(matches):
            chap_type = match.group(1).capitalize()
            chap_num = match.group(2)
            chap_sub = match.group(3).strip()
            chap_title = f"{chap_type} {chap_num}: {chap_sub}"

            start_pos = match.start()
            end_pos = matches[i + 1].start() if i + 1 < len(matches) else len(full_text)

            chunk_text = full_text[start_pos:end_pos].strip()
            words = chunk_text.split()
            if len(words) >= 5:
                chapters.append({
                    "chapter_number": len(chapters) + 1,
                    "title": chap_title[:100],
                    "text": chunk_text,
                    "word_count": len(words)
                })

    if not chapters:
        paragraphs = full_text.split('\n\n')
        current_chunk = []
        current_words = 0
        target_words = 750

        chunk_idx = 1
        for p in paragraphs:
            p = p.strip()
            if not p:
                continue
            p_words = len(p.split())
            current_chunk.append(p)
            current_words += p_words

            if current_words >= target_words:
                chunk_text = '\n\n'.join(current_chunk)
                preview = p[:60].replace('\n', ' ')
                chap_title = f"Parte {chunk_idx}: {preview}..." if len(preview) > 10 else f"Parte {chunk_idx}"
                chapters.append({
                    "chapter_number": chunk_idx,
                    "title": chap_title,
                    "text": chunk_text,
                    "word_count": current_words
                })
                current_chunk = []
                current_words = 0
                chunk_idx += 1

        if current_chunk:
            chunk_text = '\n\n'.join(current_chunk)
            preview = current_chunk[0][:60].replace('\n', ' ')
            chap_title = f"Parte {chunk_idx}: {preview}..." if len(preview) > 10 else f"Parte {chunk_idx}"
            chapters.append({
                "chapter_number": chunk_idx,
                "title": chap_title,
                "text": chunk_text,
                "word_count": current_words
            })

    return chapters


# ──────────────────────────────────────────────────────────────────────────────
# MAIN
# ──────────────────────────────────────────────────────────────────────────────

def main():
    parser = argparse.ArgumentParser(description="AudioLab MZ - Universal Document Extractor v2.0")
    parser.add_argument("pdf_path", help="Path to input file (PDF, DOCX, TXT, MD)")
    parser.add_argument("--output", help="Optional output JSON path", default=None)

    args = parser.parse_args()

    file_path = args.pdf_path
    if not os.path.exists(file_path):
        res = {"success": False, "error": f"Archivo no encontrado: {file_path}"}
        print(json.dumps(res, ensure_ascii=False))
        sys.exit(1)

    ext = os.path.splitext(file_path)[1].lower()
    ocr_used = False

    title = ""
    author = ""
    pages_text = []

    try:
        if ext == '.pdf':
            (title, author, pages_text), ocr_used = extract_pdf(file_path)
        elif ext == '.docx':
            title, author, pages_text = extract_docx(file_path)
        elif ext in ('.txt',):
            title, author, pages_text = extract_text_or_markdown(file_path, is_markdown=False)
        elif ext in ('.md', '.markdown'):
            title, author, pages_text = extract_text_or_markdown(file_path, is_markdown=True)
        elif ext in ('.png', '.jpg', '.jpeg', '.webp', '.bmp'):
            title, author, pages_text = extract_image_ocr(file_path)
            ocr_used = True
        elif ext in ('.mp3', '.wav', '.m4a', '.ogg', '.aac', '.flac'):
            title, author, pages_text = extract_audio_stt(file_path)
        else:
            res = {"success": False, "error": f"Formato de archivo no soportado: {ext}"}
            print(json.dumps(res, ensure_ascii=False))
            sys.exit(1)
    except Exception as e:
        res = {"success": False, "error": str(e)}
        print(json.dumps(res, ensure_ascii=False))
        sys.exit(1)

    total_text = "\n\n".join(pages_text).strip()
    total_words = len(total_text.split()) if total_text else 0

    if total_words < 2:
        msg = (
            "El documento no contiene texto legible ni voz reconocible. "
            "Si es una imagen o PDF escaneado, asegúrate de que el texto sea claro y que Tesseract OCR esté disponible."
        )
        res = {"success": False, "error": msg}
        print(json.dumps(res, ensure_ascii=False))
        sys.exit(0)

    # Fallback title from filename
    if not title:
        base_name = os.path.splitext(os.path.basename(file_path))[0]
        title = base_name.replace('_', ' ').replace('-', ' ').title()

    chapters = segment_into_chapters(total_text, default_title="Capítulo")

    summary = generate_summary(total_text, max_sentences=3, max_words=140)

    result = {
        "success": True,
        "title": title,
        "author": author or "Autor Desconocido",
        "summary": summary,
        "total_pages": len(pages_text),
        "total_words": total_words,
        "total_chapters": len(chapters),
        "ocr_used": ocr_used,
        "file_type": ext.lstrip('.'),
        "chapters": chapters
    }

    json_output = json.dumps(result, ensure_ascii=False, indent=2)

    if args.output:
        with open(args.output, "w", encoding="utf-8") as f:
            f.write(json_output)

    print(json_output)


if __name__ == "__main__":
    main()
