<?php

namespace App\Http\Requests\Web;

use App\Helpers\Form\FormRequest;

class ReportRequest extends FormRequest
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
            'post_id' => 'required',
            'reason' => 'required|in:1,2,3,4,5',
        ];
    }
}
