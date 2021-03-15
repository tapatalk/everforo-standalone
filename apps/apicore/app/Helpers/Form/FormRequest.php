<?php

namespace App\Helpers\Form;

use Illuminate\Http\Request;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;

abstract class FormRequest extends Request
{
    public function validate()
    {
        if (false === $this->authorize()) {
            throw new UnauthorizedException();
        }

        $validator = app('validator')->make($this->all(), $this->rules(), $this->messages());

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    public function resolveUser($app)
    {

        if (method_exists($this, 'setUserResolver')) {
            
            if($app['request']->user()){
                
                 $this->setUserResolver(function() use($app) {
         
                    return $app['request']->user();
                });
            } else {

                $this->setUserResolver(function () {
                    return Auth::user();
                });
            }
        }
    }

   

    protected function authorize()
    {
        return true;
    }

    abstract protected function rules ();

    protected function messages ()
    {
        return [];
    }
}
