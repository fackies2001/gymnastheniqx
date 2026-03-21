<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Laravel\Pennant\Feature;

class StoreSupplierRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',      // ✅ ADDED
            'contact_number' => 'nullable|string|max:50',       // ✅ CHANGED FROM 'phone'
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
        ];

        if (Feature::active('is_api')) {
            $rules['api_url'] = 'nullable|url|max:255';
            $rules['headers'] = 'nullable|string|max:500';
            $rules['service_class'] = 'nullable|string|max:255';
        }

        return $rules;
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages()
    {
        return [
            'name.required' => 'Supplier name is required.',
            'email.email' => 'Please provide a valid email address.',
            'contact_number.max' => 'Contact number cannot exceed 50 characters.',
        ];
    }
}
