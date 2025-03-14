<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\AppointmentField;
use Illuminate\Validation\ValidationException;

class StoreAppointmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->business_type_id !== null;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        if ($this->user()->business_type_id === null) {
            throw ValidationException::withMessages([
                'business_type' => ['Debes completar la configuración del tipo de negocio antes de crear citas.']
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'contact_id' => 'required|exists:contacts,id',
            'title' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'start' => 'required|date_format:Y-m-d H:i:s|after:now',
            'end' => 'required|date_format:Y-m-d H:i:s|after:start',
            'status' => 'required|in:pending,confirmed,cancelled,completed',
            'field_values' => 'array'
        ];

        $fields = AppointmentField::where('business_type_id', $this->user()->business_type_id)
            ->where('active', true)
            ->get();

        foreach ($fields as $field) {
            $fieldRule = $field->required ? 'required' : 'nullable';

            switch ($field->type) {
                case 'select':
                    $fieldRule .= '|in:' . implode(',', $field->options);
                    break;
                case 'boolean':
                    $fieldRule .= '|boolean';
                    break;
                default:
                    $fieldRule .= '|string';
            }

            $rules["field_values.{$field->id}"] = $fieldRule;
        }

        return $rules;
    }
}
