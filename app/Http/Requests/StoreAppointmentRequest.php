<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class StoreAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->business_type_id !== null || $this->user()->has_custom_fields;
    }

    protected function prepareForValidation()
    {
        if ($this->user()->business_type_id === null && !$this->user()->has_custom_fields) {
            throw ValidationException::withMessages([
                'business_type' => ['Debes completar la configuración del tipo de negocio antes de crear citas.']
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'contact_id' => 'required|exists:contacts,id',
            'title' => 'required|string|max:255',
            'start' => 'required|date_format:Y-m-d H:i:s|after:now',
            'end' => 'required|date_format:Y-m-d H:i:s|after:start',
            'status' => 'required|in:pending,confirmed,cancelled,completed'
        ];
    }
}
