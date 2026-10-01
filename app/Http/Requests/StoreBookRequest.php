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
        $allowedMimes = 'pdf,docx,doc,txt,md,markdown,png,jpg,jpeg,webp,bmp,mp3,wav,m4a,ogg,aac,flac';

        return [
            'pdf_file' => "required_without:raw_text|nullable|file|mimes:{$allowedMimes}|max:102400", // 100MB
            'raw_text' => 'required_without:pdf_file|nullable|string|min:10|max:50000',
            'title' => 'nullable|string|max:255',
            'author' => 'nullable|string|max:255',
            'voice' => 'required|string',
            'speed_rate' => 'required|string',
            'pitch' => 'required|string',
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
            'raw_text.required_without' => 'Debes adjuntar un archivo o ingresar texto en el modo de pegado directo.',
            'raw_text.min' => 'El texto pegado debe contener al menos 10 caracteres.',
            'raw_text.max' => 'El texto pegado excede el límite de 50,000 caracteres (~10,000 palabras). Para documentos extensos o libros completos, por favor súbelo como archivo (PDF, Word, TXT) en la pestaña "Subir Archivo".',
        ];
    }
}
