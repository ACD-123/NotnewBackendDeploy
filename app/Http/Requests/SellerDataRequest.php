<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SellerDataRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => 'required|string',
            'email' => 'required',
            'address' => 'required',
            "phone" => 'required',
            "country" => 'required',
            "state" => 'required',
            "city" => 'required',
            "zip" => 'required'
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Name is required!',
            'email.required' => 'Email is required!',
            'address.required' => 'Address is required!',
            'phone.required' => 'Phone is required!',
            'country.required' => 'Country is required!',
            'state.required' => 'State is required!',
            'city.required' => 'City is required!',
            'zip.required' => 'Zip is required!'
        ];
    }
}
