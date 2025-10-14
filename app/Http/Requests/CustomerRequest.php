<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'city' => 'required|string|max:100',
            'points' => 'nullable|integer|min:0',
            'reputation' => 'nullable|numeric|min:0|max:5',
        ];
    }
}
