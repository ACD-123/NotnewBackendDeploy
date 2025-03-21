<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
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
            //
            'name' => 'required|string|max:20',
            'category_id' => 'required',
            'price' => 'required',
            'active' => 'required'
        ];
    }
    public function messages()
    {
        return [
            'name.required' => 'Name is required!',
            'category_id.required' => 'Category is required!',
            'active.required' => 'Status is required!',
            'price.required' => 'Price is required'
        ];
    }
}
