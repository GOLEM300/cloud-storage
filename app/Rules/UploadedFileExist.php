<?php

namespace App\Rules;

use App\Models\UploadedFile;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UploadedFileExist implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $uploaded_file = request()->route('uploaded_file');
        $name = $value.'.'.explode('.',$uploaded_file->name)[1];
        if (UploadedFile::where('name',$name)->exists()) {
            $fail('Файл '.$value.' уже существует.');
        }
    }
}
