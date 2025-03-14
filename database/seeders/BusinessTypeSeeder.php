<?php

namespace Database\Seeders;

use App\Models\BusinessType;
use App\Models\AppointmentField;
use Illuminate\Database\Seeder;

class BusinessTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'name' => 'Personalizado',
                'slug' => 'personalizado',
                'description' => 'Crea tu propio formulario desde cero',
                'fields' => []
            ],
            [
                'name' => 'Barbería',
                'slug' => 'barberia',
                'description' => 'Servicios de barbería y estilismo',
                'fields' => [
                    ['name' => 'Tipo de Corte', 'type' => 'select', 'required' => true, 'options' => ['Corte básico', 'Fade', 'Barba']],
                    ['name' => 'Preferencias', 'type' => 'text', 'required' => false]
                ]
            ],
            [
                'name' => 'Consultorio Médico',
                'slug' => 'consultorio-medico',
                'description' => 'Servicios médicos generales',
                'fields' => [
                    ['name' => 'Síntomas', 'type' => 'text', 'required' => true],
                    ['name' => 'Especialidad', 'type' => 'select', 'required' => true, 'options' => ['General', 'Pediatría', 'Cardiología']],
                    ['name' => 'Primera Visita', 'type' => 'boolean', 'required' => true]
                ]
            ],
        ];

        foreach ($types as $type) {
            $fields = $type['fields'];
            unset($type['fields']);

            $businessType = BusinessType::create($type);

            foreach ($fields as $index => $field) {
                $field['business_type_id'] = $businessType->id;
                $field['order'] = $index + 1;
                AppointmentField::create($field);
            }
        }
    }
}
