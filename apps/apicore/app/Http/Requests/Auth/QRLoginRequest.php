<?php

namespace App\Http\Requests\Auth;
use App\Helpers\Form\FormRequest;

class QRLoginRequest extends FormRequest
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
            'uid' => 'required',
            'public_key' => 'required',
            'message' => 'required',
        ];
    }
}
