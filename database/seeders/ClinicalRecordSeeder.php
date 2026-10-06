<?php

namespace Database\Seeders;

use App\Models\ClinicalOrder;
use App\Models\ClinicalRecord;
use Illuminate\Database\Seeder;

/**
 * Reproduce EXACTAMENTE la Historia Clínica General (2 págs.) y la Incapacidad del PDF adjunto.
 * Los datos del paciente / médico salen del PDF (el JSON tenía datos de ejemplo distintos).
 */
class ClinicalRecordSeeder extends Seeder
{
    public function run(): void
    {
        $medico = 'GORDILLO RODRIGUEZ, CARLOS ANDRES';
        $especialidad = 'ORTOPEDIA Y TRAUMATOLOGIA';

        $enfermedadActual = 'DX FRACTURA DE BASE DE 5TO MTT MANEJO REDUCCION ABIERTA OSTEOISINTESIS CON TORNILLO REFIERE DOLOR EN AREA DE LA CIRUGIA';
        $examenFisico = "AL EXAMEN FISICO MOVILIDAD COMPLETA DE TOBILLO\nDOLOR HIPERSENSIBILIDAD EN AREA DE CICATRIZ HERIDA QUIRURGICA";
        $rx = 'SE OBSERVA FRACTURA CONSOLIDADA';
        $plan = "SE ENVIA TERAPIA FISICA 20 SESIONES\nSE ENVIA INCAPACIDAD MEDICA POR 30DIAS\nPOSTERIOR A ESTA PUEDE RETORNAR A LABORAR SIN RESTRICCIONES, PREVIA VALORACION POR MEDICINA LABORAL DE LA EMPRESA";

        $record = ClinicalRecord::updateOrCreate(
            ['numero_episodio' => '8428031'],
            [
                'estado' => ClinicalRecord::ESTADO_FINALIZADA,
                'finalizada_at' => '2021-07-28 13:19:27',

                // 1. Datos generales
                'paciente_nombre' => 'FERNANDO GIRALDO PARRA',
                'doc_identificacion' => 'CC 14837059',
                'fecha_nacimiento' => '1980-07-27',
                'sexo' => 'M',
                'aseguradora_entidad' => 'COOMEVA MP S.A. ORO',
                'numero_historia_clinica' => '595640',
                'medico_tratante' => $medico,
                'especialidad_servicio' => $especialidad,

                // 2. Atención clínica / anamnesis
                'tipo_atencion' => 'consulta_externa',
                'tipo_evento' => 'enfermedad_general',
                'fecha_atencion' => '2021-07-28',
                'hora_atencion' => '13:18:52',
                'motivo_consulta' => 'CONTROL',
                'enfermedad_actual' => $enfermedadActual,
                'examen_fisico' => $examenFisico,
                'rayos_x_imagenologia' => $rx,
                'plan_conducta' => $plan,

                'antecedentes_alergicos' => 'NO',
                'antecedentes_farmacologicos' => 'NO',
                'antecedentes_patologicos' => 'NO',
                'antecedentes_quirurgicos' => 'LIPECTOMIA',

                'profesional_responsable_nombre' => $medico,
                'profesional_responsable_especialidad' => $especialidad,
                'profesional_responsable_cedula' => '0094512155',
                'profesional_responsable_rm' => '766312005',
                'firma_electronica' => true,
                'factor_aislamiento' => null,

                // 3. Diagnóstico y conducta
                'diagnostico_cie10_codigo' => 'S929',
                'diagnostico_cie10_descripcion' => 'FRACTURA DEL PIE, NO ESPECIFICADA',
                'analisis_y_conducta' => implode("\n\n", [$enfermedadActual, $examenFisico, 'RX '.$rx, 'PLAN', $plan]),

                // 4. Incapacidad
                'incapacidad_fecha_inicio' => '2021-07-24',
                'incapacidad_fecha_fin' => '2021-08-22',
                'incapacidad_dias' => 30,
                'incapacidad_es_prorroga' => true,
                'incapacidad_tipo' => 'ambulatoria',
                'incapacidad_clase' => 'enfermedad_general',
                'incapacidad_diagnostico_cie10' => 'S929',

                'impreso_por' => 'M60003196',
            ]
        );

        $record->ordenes()->delete();

        // Órdenes Generales (PDF pág. 2)
        $record->ordenes()->create([
            'tipo' => ClinicalOrder::TIPO_ORDEN,
            'fecha_hora' => '2021-07-28 13:19:00',
            'codigo' => '890280',
            'nombre_prescripcion' => 'CONSULTA DE PRIMERA VEZ POR ESPECIALISTA EN ORTOPEDIA Y TRAUMATOLOGIA',
            'unidad_organizativa' => 'UT Ortopedia',
            'medico_responsable' => 'MELISSA BECERRA VELASQUEZ',
        ]);

        // Fórmulas / órdenes ambulatorias (filas de ejemplo del JSON, adaptadas al plan del PDF)
        $record->ordenes()->createMany([
            [
                'tipo' => ClinicalOrder::TIPO_FORMULA,
                'fecha_hora' => '2021-07-28 13:20:00',
                'codigo' => 'MED-0482',
                'nombre_prescripcion' => 'Naproxeno 500 mg Tabletas',
                'instrucciones' => '1 tableta vía oral cada 12 horas con las comidas por 10 días.',
                'unidad_organizativa' => 'Farmacia Ambulatoria',
                'medico_responsable' => $medico,
            ],
            [
                'tipo' => ClinicalOrder::TIPO_FORMULA,
                'fecha_hora' => '2021-07-28 13:21:00',
                'codigo' => 'MED-0119',
                'nombre_prescripcion' => 'Acetaminofén + Codeína 325mg/30mg Tabletas',
                'instrucciones' => '1 tableta vía oral cada 8 horas únicamente en caso de dolor moderado a severo.',
                'unidad_organizativa' => 'Farmacia Ambulatoria',
                'medico_responsable' => $medico,
            ],
            [
                'tipo' => ClinicalOrder::TIPO_FORMULA,
                'fecha_hora' => '2021-07-28 13:22:00',
                'codigo' => 'PRC-9351',
                'nombre_prescripcion' => 'Terapia física - 20 sesiones',
                'instrucciones' => 'Según plan de manejo. Cita de control al finalizar el período de incapacidad de 30 días.',
                'unidad_organizativa' => 'Fisioterapia',
                'medico_responsable' => $medico,
            ],
        ]);
    }
}
