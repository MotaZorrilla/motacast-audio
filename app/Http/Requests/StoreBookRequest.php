<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookRequest extends FormRequest
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
        $allowedMimes = 'pdf,docx,doc,txt,md,markdown,png,jpg,jpeg,webp,bmp,mp3,wav,m4a,ogg,opus,aac,flac,mp4,mkv,mov,avi,webm';
        $isAdmin = $this->user()?->isAdmin() ?? false;

        $fileRule = $isAdmin
            ? "required_without_all:raw_text,chunked_upload_id|nullable|file|mimes:{$allowedMimes}"
            : "required_without:raw_text|nullable|file|mimes:{$allowedMimes}|max:102400";

        $rawTextRule = $isAdmin
            ? 'required_without_all:pdf_file,chunked_upload_id|nullable|string|min:10|max:50000'
            : 'required_without:pdf_file|nullable|string|min:10|max:50000';

        return [
            'pdf_file' => $fileRule,
            'raw_text' => $rawTextRule,
            'chunked_upload_id' => $isAdmin ? 'nullable|uuid' : 'prohibited',
            'chunked_filename' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'author' => 'nullable|string|max:255',
            'voice' => 'required_unless:transcription_only,1|nullable|string',
            'speed_rate' => 'required_unless:transcription_only,1|nullable|string',
            'pitch' => 'required_unless:transcription_only,1|nullable|string',
            'keep_original_media' => 'nullable|boolean',
            'transcription_only' => 'nullable|boolean',
            'assigned_user_id' => 'nullable|exists:users,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'pdf_file.required_without' => 'Debes adjuntar un archivo o ingresar texto en el modo de pegado directo.',
            'pdf_file.max' => 'El archivo adjunto excede el límite máximo permitido de 100 MB. Por favor optimiza el documento o comprime el archivo antes de cargarlo.',
            'raw_text.required_without' => 'Debes adjuntar un archivo o ingresar texto en el modo de pegado directo.',
            'raw_text.min' => 'El texto pegado debe contener al menos 10 caracteres.',
            'raw_text.max' => 'El texto pegado excede el límite de 50,000 caracteres (~10,000 palabras). Para documentos extensos o libros completos, por favor súbelo como archivo (PDF, Word, TXT) en la pestaña "Subir Archivo".',
        ];
    }
}
