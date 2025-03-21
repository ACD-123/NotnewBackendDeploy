<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
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
            'name' => 'required|string',
            'type' => 'required',
            'active' => 'required',
            "file" => 'required'
        ];
    }
    public function messages()
    {
        return [
            'name.required' => 'Name is required!',
            'type.required' => 'Type is required!',
            'active.required' => 'Status is required!',
            'file.required' => 'File is required for Uploading!'
        ];
    }
}
