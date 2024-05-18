<?php

namespace App\Http\Requests\Upload;

use App\Rules\StorageDirNotExist;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UploadedFileStoreRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'folder_name' => ['required', 'string', new StorageDirNotExist()],
            'files' => ['required'],
            'files.*' => ['file']
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'folder_name.required' => 'Поле folder_name обязательно',
            'folder_name.string' => 'Имя папки должно быть строкой',
            'files.required' => 'Поле files обязательно',
            'files.*.file' => 'Неверный тип поля file'
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation errors',
            'errors' => $validator->errors()
        ], 422));
    }
}
