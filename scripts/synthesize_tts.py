#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
AudioLab MZ - Neural TTS Synthesis Engine with edge-tts
Produces high-quality MP3 audio and calculates exact duration.
"""

import sys
import os
import json
import asyncio
import argparse

def get_audio_duration(file_path: str) -> int:
    """Calculates exact duration in seconds using mutagen or fallback."""
    try:
        from mutagen.mp3 import MP3
        audio = MP3(file_path)
        return int(round(audio.info.length))
    except Exception:
        # Fallback approximation for 48kbps or 64kbps MP3
        if os.path.exists(file_path):
            size_bytes = os.path.getsize(file_path)
            # 48kbps = 6000 bytes/sec
            return max(1, int(round(size_bytes / 6000)))
        return 0

async def synthesize(text: str, voice: str, rate: str, pitch: str, output_path: str):
    import edge_tts
    
    # Ensure directory exists
    os.makedirs(os.path.dirname(os.path.abspath(output_path)), exist_ok=True)
    
    communicate = edge_tts.Communicate(text=text, voice=voice, rate=rate, pitch=pitch)
    await communicate.save(output_path)

def main():
    parser = argparse.ArgumentParser(description="Synthesize text to MP3 using edge-tts.")
    parser.add_argument("--text", help="Text to speak", default=None)
    parser.add_argument("--text-file", help="Path to file containing text", default=None)
    parser.add_argument("--output", help="Output MP3 file path", required=True)
    parser.add_argument("--voice", help="Neural voice name", default="es-VE-SebastianNeural")
    parser.add_argument("--rate", help="Speed adjustment (e.g. +0%%, +10%%)", default="+0%")
    parser.add_argument("--pitch", help="Pitch adjustment (e.g. +0Hz)", default="+0Hz")
    
    args = parser.parse_args()
    
    text = ""
    if args.text_file and os.path.exists(args.text_file):
        with open(args.text_file, "r", encoding="utf-8") as f:
            text = f.read().strip()
    elif args.text:
        text = args.text.strip()
    else:
        res = {"success": False, "error": "No se proporcionó texto ni archivo de texto válido."}
        print(json.dumps(res))
        sys.exit(1)
        
    if not text:
        res = {"success": False, "error": "El texto proporcionado está vacío."}
        print(json.dumps(res))
        sys.exit(1)
        
    try:
        asyncio.run(synthesize(text, args.voice, args.rate, args.pitch, args.output))
        
        duration = get_audio_duration(args.output)
        file_size = os.path.getsize(args.output) if os.path.exists(args.output) else 0
        
        res = {
            "success": True,
            "audio_path": args.output,
            "duration_seconds": duration,
            "file_size": file_size,
            "voice": args.voice
        }
        print(json.dumps(res, ensure_ascii=False))
        
    except Exception as e:
        res = {
            "success": False,
            "error": f"Error en síntesis TTS: {str(e)}"
        }
        print(json.dumps(res, ensure_ascii=False))
        sys.exit(1)

if __name__ == "__main__":
    main()
