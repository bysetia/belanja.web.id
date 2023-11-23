<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
         return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required',
            'description' => 'nullable',
            'address_one' => 'nullable',
            'address_two' => 'nullable',
            'provinces' => 'nullable',
            'regencies' => 'nullable',
            'zip_code' => 'nullable',
            'country' => 'nullable',
            'logo' => 'nullable|image'
        ];
    }
}
