<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAppointmentRequest;
use App\Models\AppointmentField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        if ($request->has('field_values')) {
            foreach ($request->field_values as $fieldId => $value) {
                $appointment->fieldValues()->create([
                    'appointment_field_id' => $fieldId,
                    'value' => $value
                ]);
            }
        }

        $appointment->load(['contact:id,first_name,last_name', 'fieldValues']);

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
        $appointment->update($request->validated());
        $appointment->load('contact:id,first_name,last_name');

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

    public function getFormStructure()
    {
        $user = Auth::user();
        $customFields = [];

        if ($user->business_type_id) {
            $customFields = AppointmentField::where('business_type_id', $user->business_type_id)
                ->where('active', true)
                ->orderBy('order')
                ->get(['id', 'name', 'type', 'required', 'options']);
        } elseif ($user->has_custom_fields) {
            $customFields = $user->appointmentFields()
                ->where('active', true)
                ->orderBy('order')
                ->get(['id', 'name', 'type', 'required', 'options']);
        } else {
            return response()->json([
                'message' => 'Debes seleccionar un tipo de negocio o crear campos personalizados',
            ], 400);
        }

        $structure = [
            'default_fields' => [
                [
                    'name' => 'contact_id',
                    'type' => 'select',
                    'label' => 'Contacto',
                    'required' => true
                ],
                [
                    'name' => 'title',
                    'type' => 'text',
                    'label' => 'Título',
                    'required' => true
                ],
                [
                    'name' => 'start',
                    'type' => 'datetime',
                    'label' => 'Fecha y hora de inicio',
                    'required' => true
                ],
                [
                    'name' => 'end',
                    'type' => 'datetime',
                    'label' => 'Fecha y hora de fin',
                    'required' => true
                ],
                [
                    'name' => 'status',
                    'type' => 'select',
                    'label' => 'Estado',
                    'required' => true,
                    'options' => ['pending', 'confirmed', 'cancelled', 'completed']
                ]
            ],
            'custom_fields' => $customFields
        ];

        return response()->json($structure);
    }
}
