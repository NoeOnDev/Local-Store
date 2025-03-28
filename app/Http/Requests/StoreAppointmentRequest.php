<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

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
            'start' => 'required|date_format:Y-m-d H:i:s',
            'end' => 'required|date_format:Y-m-d H:i:s|after:start',
            'status' => 'required|in:pending,attended,cancelled'
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $this->validateAppointmentOverlap($validator);
        });
    }

    /**
     * Validate that the appointment doesn't overlap with existing appointments.
     *
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     */
    protected function validateAppointmentOverlap($validator)
    {
        $start = $this->start;
        $end = $this->end;
        $appointmentId = $this->route('id');

        $query = DB::table('appointments')
            ->where('user_id', $this->user()->id)
            ->where(function ($query) use ($start, $end) {
                $query->where(function ($q) use ($start) {
                    $q->where('start', '<=', $start)
                        ->where('end', '>', $start);
                })
                    ->orWhere(function ($q) use ($end) {
                        $q->where('start', '<', $end)
                            ->where('end', '>=', $end);
                    })
                    ->orWhere(function ($q) use ($start, $end) {
                        $q->where('start', '>=', $start)
                            ->where('end', '<=', $end);
                    });
            });

        if ($appointmentId) {
            $query->where('id', '!=', $appointmentId);
        }

        $overlappingAppointment = $query->first();

        if ($overlappingAppointment) {
            $validator->errors()->add(
                'overlap',
                'Ya existe una cita programada en este horario. Por favor selecciona otro horario.'
            );
        }
    }
}
