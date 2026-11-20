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
        $audioMimes = 'mp3,wav,m4a,ogg,opus,aac,flac,mp4,mkv,mov,avi,webm';
        $isAdmin = $this->user()?->isAdmin() ?? false;

        return [
            'audio' => $isAdmin
                ? "required_without:chunked_upload_id|nullable|file|mimes:{$audioMimes}"
                : "required|file|mimes:{$audioMimes}|max:102400", // 100MB para usuarios estándar e invitados
            'chunked_upload_id' => $isAdmin ? 'nullable|uuid' : 'prohibited',
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
