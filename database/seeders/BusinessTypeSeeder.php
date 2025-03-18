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
                    ['name' => 'Preferencias', 'type' => 'text', 'required' => false],
                    ['name' => 'Estilista', 'type' => 'select', 'required' => false, 'options' => ['Sin preferencia', 'Estilista 1', 'Estilista 2', 'Estilista 3']],
                    ['name' => 'Observaciones', 'type' => 'text', 'required' => false],

                ]
            ],
            [
                'name' => 'Consultorio Médico',
                'slug' => 'consultorio-medico',
                'description' => 'Servicios médicos generales',
                'fields' => [
                    ['name' => 'Síntomas', 'type' => 'text', 'required' => true],
                    ['name' => 'Especialidad', 'type' => 'select', 'required' => true, 'options' => ['General', 'Pediatría', 'Cardiología']],
                    ['name' => 'Medicación', 'type' => 'text', 'required' => false],
                    ['name' => 'Observaciones', 'type' => 'text', 'required' => false]
                ]
            ],
            [
                'name' => 'Salón de Belleza',
                'slug' => 'salon-belleza',
                'description' => 'Servicios de estética, manicura, pedicura y tratamientos de belleza',
                'fields' => [
                    ['name' => 'Tipo de Servicio', 'type' => 'select', 'required' => true, 'options' => [
                        'Corte de pelo',
                        'Coloración',
                        'Manicura',
                        'Pedicura',
                        'Tratamiento facial',
                        'Maquillaje',
                        'Depilación'
                    ]],
                    ['name' => 'Profesional', 'type' => 'select', 'required' => true, 'options' => [
                        'Estilista',
                        'Manicurista',
                        'Esteticista',
                        'Maquillista'
                    ]],
                    ['name' => 'Tiempo Estimado', 'type' => 'select', 'required' => true, 'options' => [
                        '30 minutos',
                        '45 minutos',
                        '1 hora',
                        '1.5 horas',
                        '2 horas',
                        '2.5 horas'
                    ]],
                    ['name' => 'Instrucciones Especiales', 'type' => 'text', 'required' => false]
                ]
            ],
            [
                'name' => 'Gimnasio/Centro Deportivo',
                'slug' => 'gimnasio',
                'description' => 'Reservas para clases, entrenadores personales o instalaciones deportivas',
                'fields' => [
                    ['name' => 'Actividad', 'type' => 'select', 'required' => true, 'options' => [
                        'Entrenamiento personal',
                        'Clase grupal',
                        'Uso de instalaciones',
                        'Evaluación física',
                        'Nutrición'
                    ]],
                    ['name' => 'Entrenador', 'type' => 'select', 'required' => false, 'options' => [
                        'Sin preferencia',
                        'Entrenador principiantes',
                        'Entrenador avanzado',
                        'Especialista en rehabilitación'
                    ]],
                    ['name' => 'Nivel del Cliente', 'type' => 'select', 'required' => true, 'options' => [
                        'Principiante',
                        'Intermedio',
                        'Avanzado',
                        'Rehabilitación'
                    ]],
                    ['name' => 'Equipamiento Requerido', 'type' => 'text', 'required' => false],
                    ['name' => 'Objetivo', 'type' => 'select', 'required' => true, 'options' => [
                        'Pérdida de peso',
                        'Ganancia muscular',
                        'Resistencia',
                        'Rehabilitación',
                        'Mantenimiento'
                    ]]
                ]
            ],
            [
                'name' => 'Restaurante',
                'slug' => 'restaurante',
                'description' => 'Reservas de mesas y eventos en restaurantes',
                'fields' => [
                    ['name' => 'Número de Personas', 'type' => 'number', 'required' => true],
                    ['name' => 'Tipo de Mesa', 'type' => 'select', 'required' => true, 'options' => [
                        'Interior',
                        'Terraza',
                        'Privada',
                        'Barra',
                        'VIP'
                    ]],
                    ['name' => 'Ocasión Especial', 'type' => 'select', 'required' => false, 'options' => [
                        'No',
                        'Cumpleaños',
                        'Aniversario',
                        'Reunión de negocios',
                        'Celebración'
                    ]],
                    ['name' => 'Alérgenos o Restricciones', 'type' => 'text', 'required' => false],
                    ['name' => 'Solicitud Especial', 'type' => 'text', 'required' => false],
                    ['name' => 'Menú Predefinido', 'type' => 'boolean', 'required' => false]
                ]
            ],
            [
                'name' => 'Centro de Terapias y Spa',
                'slug' => 'centro-spa',
                'description' => 'Gestión de citas para masajes, tratamientos corporales y terapias de bienestar',
                'fields' => [
                    ['name' => 'Tipo de Servicio', 'type' => 'select', 'required' => true, 'options' => [
                        'Masaje relajante',
                        'Masaje descontracturante',
                        'Tratamiento facial',
                        'Tratamiento corporal',
                        'Exfoliación',
                        'Terapia con piedras calientes',
                        'Reflexología',
                        'Aromaterapia'
                    ]],
                    ['name' => 'Terapeuta', 'type' => 'select', 'required' => false, 'options' => [
                        'Sin preferencia',
                        'Especialista en masajes',
                        'Esteticista',
                        'Fisioterapeuta'
                    ]],
                    ['name' => 'Duración', 'type' => 'select', 'required' => true, 'options' => [
                        '30 minutos',
                        '45 minutos',
                        '60 minutos',
                        '90 minutos',
                        '120 minutos'
                    ]],
                    ['name' => 'Área de enfoque', 'type' => 'select', 'required' => false, 'options' => [
                        'Espalda',
                        'Cuello y hombros',
                        'Piernas',
                        'Cuerpo completo',
                        'Facial'
                    ]],
                    ['name' => 'Intensidad preferida', 'type' => 'select', 'required' => false, 'options' => [
                        'Suave',
                        'Media',
                        'Intensa'
                    ]],
                    ['name' => 'Condiciones médicas', 'type' => 'text', 'required' => false],
                    ['name' => 'Alergias', 'type' => 'text', 'required' => false]
                ]
            ],
            [
                'name' => 'Academia de Enseñanza',
                'slug' => 'academia',
                'description' => 'Gestión de clases particulares, tutorías y sesiones de aprendizaje',
                'fields' => [
                    ['name' => 'Materia', 'type' => 'select', 'required' => true, 'options' => [
                        'Matemáticas',
                        'Física',
                        'Química',
                        'Biología',
                        'Historia',
                        'Literatura',
                        'Idiomas',
                        'Programación',
                        'Música',
                        'Arte'
                    ]],
                    ['name' => 'Nivel', 'type' => 'select', 'required' => true, 'options' => [
                        'Primaria',
                        'Secundaria',
                        'Bachillerato',
                        'Universidad',
                        'Profesional',
                        'Adultos'
                    ]],
                    ['name' => 'Tipo de Sesión', 'type' => 'select', 'required' => true, 'options' => [
                        'Clase regular',
                        'Refuerzo',
                        'Preparación de examen',
                        'Proyecto',
                        'Evaluación'
                    ]],
                    ['name' => 'Formato', 'type' => 'select', 'required' => true, 'options' => [
                        'Presencial',
                        'Online',
                        'Mixto'
                    ]],
                    ['name' => 'Profesor', 'type' => 'select', 'required' => false, 'options' => [
                        'Sin preferencia',
                        'Licenciado',
                        'Máster',
                        'Doctor'
                    ]],
                    ['name' => 'Material necesario', 'type' => 'text', 'required' => false],
                    ['name' => 'Objetivos específicos', 'type' => 'text', 'required' => false]
                ]
            ],
            [
                'name' => 'Servicio de Fotografía',
                'slug' => 'fotografia',
                'description' => 'Gestión de sesiones fotográficas, eventos y producción audiovisual',
                'fields' => [
                    ['name' => 'Tipo de Sesión', 'type' => 'select', 'required' => true, 'options' => [
                        'Retrato individual',
                        'Retrato familiar',
                        'Fotografía de producto',
                        'Evento social',
                        'Evento corporativo',
                        'Fotografía inmobiliaria',
                        'Sesión exterior',
                        'Sesión en estudio'
                    ]],
                    ['name' => 'Duración estimada', 'type' => 'select', 'required' => true, 'options' => [
                        '1 hora',
                        '2 horas',
                        '3 horas',
                        'Media jornada (4h)',
                        'Jornada completa (8h)'
                    ]],
                    ['name' => 'Ubicación', 'type' => 'select', 'required' => true, 'options' => [
                        'Estudio',
                        'Domicilio del cliente',
                        'Exteriores urbanos',
                        'Naturaleza',
                        'Lugar del evento'
                    ]],
                    ['name' => 'Número de personas', 'type' => 'number', 'required' => false],
                    ['name' => 'Formato de entrega', 'type' => 'select', 'required' => true, 'options' => [
                        'Digital básico',
                        'Digital completo',
                        'Impresiones',
                        'Álbum físico',
                        'Todo incluido'
                    ]],
                    ['name' => 'Requisitos especiales', 'type' => 'text', 'required' => false],
                    ['name' => 'Referencias visuales', 'type' => 'text', 'required' => false],
                    ['name' => 'Incluye maquillaje', 'type' => 'boolean', 'required' => false],
                    ['name' => 'Edición avanzada', 'type' => 'boolean', 'required' => false]
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
