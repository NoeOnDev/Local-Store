<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAppointmentRequest;
use App\Models\AppointmentField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'per_page' => 'integer|min:1|max:100',
            'page' => 'integer|min:1',
            'status' => 'string|in:pending,confirmed,cancelled,completed',
            'start_date' => 'date_format:Y-m-d',
            'end_date' => 'date_format:Y-m-d|after_or_equal:start_date'
        ]);

        $query = Auth::user()->appointments()
            ->with(['contact:id,first_name,last_name', 'fieldValues.field']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $query->when($request->has(['start_date', 'end_date']), function ($q) use ($request) {
            return $q->whereBetween('start', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        });

        $appointments = $query->orderBy('start', 'desc')
            ->paginate($request->input('per_page', 15));

        return response()->json($appointments);
    }

    public function store(StoreAppointmentRequest $request)
    {
        $appointment = Auth::user()->appointments()->create($request->validated());

        $appointment->load(['contact:id,first_name,last_name']);

        return response()->json(['appointment' => $appointment], 201);
    }

    public function show($id)
    {
        $appointment = Auth::user()->appointments()
            ->with(['contact:id,first_name,last_name', 'fieldValues'])
            ->findOrFail($id);

        return response()->json(['appointment' => $appointment]);
    }

    public function update(StoreAppointmentRequest $request, $id)
    {
        $appointment = Auth::user()->appointments()->findOrFail($id);

        if ($appointment->is_attended) {
            return response()->json([
                'message' => 'No se pueden editar citas que ya han sido atendidas'
            ], 400);
        }

        $appointment->update($request->validated());

        $appointment->load(['contact:id,first_name,last_name', 'fieldValues.field']);

        return response()->json(['appointment' => $appointment]);
    }

    public function destroy($id)
    {
        $appointment = Auth::user()->appointments()->findOrFail($id);
        $appointment->delete();

        return response()->json([
            'message' => 'Appointment deleted successfully'
        ]);
    }

    public function attend(Request $request, $id)
    {
        $appointment = Auth::user()->appointments()->findOrFail($id);

        if (!in_array($appointment->status, ['pending', 'confirmed'])) {
            return response()->json([
                'message' => 'Solo se pueden atender citas pendientes o confirmadas'
            ], 400);
        }

        $request->validate([
            'field_values' => 'required|array'
        ]);

        $validator = Validator::make($request->all(), $this->getAttendValidationRules());

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::transaction(function () use ($appointment, $request) {
            $appointment->update([
                'status' => 'attended',
                'is_attended' => true
            ]);

            $appointment->fieldValues()->delete();

            foreach ($request->field_values as $fieldId => $value) {
                $appointment->fieldValues()->create([
                    'appointment_field_id' => $fieldId,
                    'value' => $value
                ]);
            }
        });

        $appointment->load(['contact:id,first_name,last_name', 'fieldValues.field']);

        return response()->json(['appointment' => $appointment]);
    }

    private function getAttendValidationRules()
    {
        $rules = [
            'field_values' => 'required|array'
        ];

        $user = Auth::user();

        if ($user->business_type_id) {
            $fields = AppointmentField::where('business_type_id', $user->business_type_id)
                ->where('active', true)
                ->get();
        } else {
            $fields = $user->appointmentFields()
                ->where('active', true)
                ->get();
        }

        foreach ($fields as $field) {
            $fieldRule = $field->required ? 'required' : 'nullable';

            switch ($field->type) {
                case 'select':
                    $fieldRule .= '|in:' . implode(',', $field->options);
                    break;
                case 'boolean':
                    $fieldRule .= '|boolean';
                    break;
                case 'date':
                    $fieldRule .= '|date_format:Y-m-d';
                    break;
                case 'number':
                    $fieldRule .= '|numeric';
                    break;
                default:
                    $fieldRule .= '|string';
            }

            $rules["field_values.{$field->id}"] = $fieldRule;
        }

        return $rules;
    }
}
