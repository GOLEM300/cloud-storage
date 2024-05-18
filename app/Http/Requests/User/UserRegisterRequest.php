<?php

namespace App\Http\Requests\User;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UserRegisterRequest extends FormRequest
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
            'email' => ['email', 'required', 'unique:users,email'],
            'password' => ['string', 'min:8', 'required' ,'max:255'],
            'name' => ['string', 'required', 'max:255']
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
            'email.unique' => 'Email занят',
            'email.required' => 'Поле email обязательно',
            'email.email' => 'Невалидный адрес электронный почты',
            'name.string' => 'Имя должно быть строкой',
            'name.required' => 'Поле name обязательно',
            'name.max' => 'Превышена максимальная длинна имени',
            'password.string' => 'Поле password должно быть строкой',
            'password.required' => 'Поле password обязательно',
            'password.max' => 'Превышена максимальная длинна пароля',
            'password.min' => 'Пароль не должен быть меньше 8 символов',
        ];
    }

    /**
     * @param Validator $validator
     * @return mixed
     */
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success'   => false,
            'message'   => 'Validation errors',
            'data' => $validator->errors()
        ],422));
    }
}
