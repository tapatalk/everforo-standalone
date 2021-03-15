<?php

namespace App\Http\Requests\Web;
use App\Helpers\Form\FormRequest;

class CreateERC20TokenRequest extends FormRequest
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
            'name' => 'min:2|max:10',
            'symbol' => 'min:2|max:10',
        ];
    }
}
