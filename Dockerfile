FROM php:8.3-cli-alpine

# Install PHP extensions helper
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions pdo_sqlite sqlite3 pdo_mysql intl bcmath zip pcntl opcache gd @composer

# Install Python 3, pip, audio/PDF libraries, ffmpeg, flac and Tesseract OCR for MotaCastAudio
RUN apk add --no-cache python3 py3-pip build-base libffi-dev \
        tesseract-ocr tesseract-ocr-data-spa ffmpeg flac \
    && pip install --no-cache-dir --break-system-packages \
        edge-tts pymupdf mutagen pytesseract Pillow python-docx SpeechRecognition \
    && (ln -sf /usr/bin/flac /usr/lib/python3.*/site-packages/speech_recognition/flac-linux-x86_64 || true)

# Copy custom PHP configuration (100MB upload limits)
COPY custom-php.ini /usr/local/etc/php/conf.d/custom-php.ini

WORKDIR /app

EXPOSE 8000

COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
