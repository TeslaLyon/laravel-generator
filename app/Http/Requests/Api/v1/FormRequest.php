<?php

namespace App\Http\Requests\Api\v1;

use Illuminate\Foundation\Http\FormRequest as baseFormRequest;

class FormRequest extends baseFormRequest
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
            // switch ($this->method()) {
            //     // CREATE
            //     case 'POST':
            //     {
            //         return [];
            //     }
            //     // UPDATE
            //     case 'PUT':
            //     case 'PATCH':
            //     {
            //         return [];
            //     }
            //     case 'GET':
            //     case 'DELETE':
            //     default:
            //     {
            //         return [];
            //     }
            // }
        ];
    }
}
