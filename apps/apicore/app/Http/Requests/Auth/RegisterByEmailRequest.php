<?php

namespace App\Http\Requests\Auth;
use App\Helpers\Form\FormRequest;

class RegisterByEmailRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'min:3|max:32|unique:users,name',
            'email' => 'required|max:255|unique:users,email',
            'password' => 'required|min:6',
            'token' => 'required',
        ];
    }
}
