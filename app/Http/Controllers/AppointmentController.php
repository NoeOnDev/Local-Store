<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAppointmentRequest;
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
            ->with(['contact:id,first_name,last_name', 'fieldValues.field'])
            ->findOrFail($id);

        return response()->json(['appointment' => $appointment]);
    }

    public function update(StoreAppointmentRequest $request, $id)
    {
        $appointment = Auth::user()->appointments()->findOrFail($id);

        $appointment->update($request->validated());

        $appointment->load(['contact:id,first_name,last_name', 'fieldValues.field']);

        return response()->json(['appointment' => $appointment]);
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $appointment = $user->appointments()->findOrFail($id);
        $appointment->delete();

        // Obtener citas actualizadas ordenadas por fecha de inicio (más recientes primero)
        $appointments = $user->appointments()
            ->with(['contact:id,first_name,last_name'])
            ->orderBy('start', 'desc')
            ->get();

        return response()->json([
            'message' => 'Appointment deleted successfully',
            'appointments' => $appointments
        ]);
    }

    public function attend(Request $request, $id)
    {
        $appointment = Auth::user()->appointments()->findOrFail($id);

        if ($appointment->status !== 'pending') {
            return response()->json([
                'message' => 'Solo se pueden atender citas pendientes'
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
                'status' => 'attended'
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

    public function search(Request $request)
    {
        $request->validate([
            'query' => 'nullable|string',
            'status' => 'nullable|string|in:pending,attended,cancelled',
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
            'contact_id' => 'nullable|integer|exists:contacts,id',
            'per_page' => 'integer|min:1|max:100',
            'page' => 'integer|min:1',
            'sort_by' => 'string|in:title,start,end,status,created_at',
            'sort_order' => 'string|in:asc,desc'
        ]);

        $user = Auth::user();
        $input = $request->input('query');
        $perPage = $request->input('per_page', 15);
        $sortBy = $request->input('sort_by', 'start');
        $sortOrder = $request->input('sort_order', 'desc');

        $query = $user->appointments()
            ->with(['contact:id,first_name,last_name', 'fieldValues.field']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has(['start_date', 'end_date'])) {
            $query->whereBetween('start', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        } elseif ($request->has('start_date')) {
            $query->where('start', '>=', $request->start_date . ' 00:00:00');
        } elseif ($request->has('end_date')) {
            $query->where('start', '<=', $request->end_date . ' 23:59:59');
        }

        if ($request->has('contact_id')) {
            $query->where('contact_id', $request->contact_id);
        }

        if ($input) {
            $query->where(function($q) use ($input) {
                $q->where('title', 'LIKE', "%{$input}%")
                  ->orWhereHas('contact', function($subQuery) use ($input) {
                      $subQuery->where('first_name', 'LIKE', "%{$input}%")
                               ->orWhere('last_name', 'LIKE', "%{$input}%");
                  });
            });
        }

        $query->orderBy($sortBy, $sortOrder);

        $appointments = $query->paginate($perPage);

        return response()->json($appointments);
    }

    public function exportCsv(Request $request)
    {
        $request->validate([
            'columns' => 'nullable|array',
            'columns.*' => 'string'
        ]);

        $user = Auth::user();

        // Obtener las citas con sus relaciones
        $appointments = $user->appointments()
            ->with(['contact', 'fieldValues.field'])
            ->orderBy('start', 'desc')
            ->get();

        // Obtener campos personalizados
        $customFields = $user->appointmentFields()
            ->where('active', true)
            ->orderBy('order')
            ->get();

        // Definir todas las columnas disponibles
        $availableColumns = [
            'id' => 'ID',
            'title' => 'Título',
            'contact' => 'Contacto',
            'email' => 'Email',
            'phone' => 'Teléfono',
            'start' => 'Inicio',
            'end' => 'Fin',
            'status' => 'Estado',
            'created_at' => 'Fecha de Creación'
        ];

        // Añadir campos personalizados a las columnas disponibles
        foreach ($customFields as $field) {
            $availableColumns['field_' . $field->id] = $field->name;
        }

        // Si no se especificaron columnas, usar todas las disponibles
        $selectedColumns = $request->has('columns') ? $request->columns : array_keys($availableColumns);

        // Crear array de cabeceras para las columnas seleccionadas
        $headers = [];
        foreach ($selectedColumns as $columnKey) {
            if (isset($availableColumns[$columnKey])) {
                $headers[] = $availableColumns[$columnKey];
            }
        }

        // Crear el archivo CSV en memoria
        $callback = function() use ($appointments, $customFields, $headers, $selectedColumns, $availableColumns) {
            $file = fopen('php://output', 'w');

            // Escribir las cabeceras
            fputcsv($file, $headers);

            // Escribir cada cita
            foreach ($appointments as $appointment) {
                // Mapear valores de campos personalizados
                $fieldValueMap = [];
                foreach ($appointment->fieldValues as $fieldValue) {
                    $fieldValueMap[$fieldValue->appointment_field_id] = $fieldValue->value;
                }

                // Preparar fila según las columnas seleccionadas
                $row = [];
                foreach ($selectedColumns as $columnKey) {
                    if (!isset($availableColumns[$columnKey])) {
                        $row[] = '';
                        continue;
                    }

                    switch ($columnKey) {
                        case 'id':
                            $row[] = $appointment->id;
                            break;
                        case 'title':
                            $row[] = $appointment->title;
                            break;
                        case 'contact':
                            $row[] = $appointment->contact ? $appointment->contact->first_name . ' ' . $appointment->contact->last_name : 'N/A';
                            break;
                        case 'email':
                            $row[] = $appointment->contact ? $appointment->contact->email : 'N/A';
                            break;
                        case 'phone':
                            $row[] = $appointment->contact ? $appointment->contact->phone : 'N/A';
                            break;
                        case 'start':
                            $row[] = date('Y-m-d H:i', strtotime($appointment->start));
                            break;
                        case 'end':
                            $row[] = date('Y-m-d H:i', strtotime($appointment->end));
                            break;
                        case 'status':
                            $row[] = $this->translateStatus($appointment->status);
                            break;
                        case 'created_at':
                            $row[] = date('Y-m-d', strtotime($appointment->created_at));
                            break;
                        default:
                            if (strpos($columnKey, 'field_') === 0) {
                                $fieldId = substr($columnKey, 6);
                                $row[] = $fieldValueMap[$fieldId] ?? 'N/A';
                            } else {
                                $row[] = '';
                            }
                    }
                }

                fputcsv($file, $row);
            }

            fclose($file);
        };

        $filename = 'citas_' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        return response()->stream($callback, 200, $headers);
    }


    public function getExportColumns()
    {
        $user = Auth::user();

        // Columnas estándar
        $columns = [
            ['key' => 'id', 'label' => 'ID'],
            ['key' => 'title', 'label' => 'Título'],
            ['key' => 'contact', 'label' => 'Contacto'],
            ['key' => 'email', 'label' => 'Email'],
            ['key' => 'phone', 'label' => 'Teléfono'],
            ['key' => 'start', 'label' => 'Inicio'],
            ['key' => 'end', 'label' => 'Fin'],
            ['key' => 'status', 'label' => 'Estado'],
            ['key' => 'created_at', 'label' => 'Fecha de Creación']
        ];

        // Añadir campos personalizados
        $customFields = $user->appointmentFields()
            ->where('active', true)
            ->orderBy('order')
            ->get();

        foreach ($customFields as $field) {
            $columns[] = [
                'key' => 'field_' . $field->id,
                'label' => $field->name,
                'custom' => true
            ];
        }

        return response()->json(['columns' => $columns]);
    }

    /**
     * Traduce los estados de las citas a español para el CSV
     */
    private function translateStatus($status)
    {
        $translations = [
            'pending' => 'Pendiente',
            'attended' => 'Atendida',
            'cancelled' => 'Cancelada'
        ];

        return $translations[$status] ?? $status;
    }

    private function getAttendValidationRules()
    {
        $rules = [
            'field_values' => 'required|array'
        ];

        $user = Auth::user();
        $fields = $user->appointmentFields()
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
