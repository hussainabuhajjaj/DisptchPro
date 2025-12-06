<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PublicLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'whatsapp' => 'nullable|string|max:255',
            'preferred_contact' => 'nullable|string|max:50',
            'timezone' => 'nullable|string|max:100',
            'mc_number' => 'nullable|string|max:50',
            'dot_number' => 'nullable|string|max:50',
            'equipment' => 'nullable',
            'preferred_lanes' => 'nullable',
            'preferred_load_types' => 'nullable',
            'notes' => 'nullable|string',
            'lead_source_id' => 'nullable|integer|exists:lead_sources,id',
            'lead_source' => 'nullable|string|max:255',
        ];
    }
}
