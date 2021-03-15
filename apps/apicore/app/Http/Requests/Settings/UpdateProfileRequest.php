<?php

namespace App\Http\Requests\Settings;
use App\Helpers\Form\FormRequest;

class UpdateProfileRequest extends FormRequest
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
            'name' => 'min:3|max:32|unique:users,name,' . request()->user()->id,
            // 'email' => 'email|unique:users,email,'.$this->user->id,
            'photo_url' => 'min:6|max:256'
        ];
    }
}
