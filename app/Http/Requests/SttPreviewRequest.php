<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SttPreviewRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'audio' => 'required|file|mimes:mp3,wav,m4a,ogg,aac,flac,mp4,mkv,mov,avi,webm|max:102400', // 100MB
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'audio.required' => 'Debes adjuntar un archivo de audio o video para transcribir.',
            'audio.file' => 'El archivo adjunto no es válido.',
            'audio.mimes' => 'El formato debe ser un archivo de audio o video válido (MP3, WAV, M4A, OGG, AAC, FLAC, MP4, MKV, MOV, AVI, WEBM).',
            'audio.max' => 'El archivo multimedia no debe exceder los 100 MB.',
        ];
    }
}
