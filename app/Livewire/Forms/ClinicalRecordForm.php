<?php

namespace App\Livewire\Forms;

use App\Models\ClinicalOrder;
use App\Models\ClinicalRecord;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Form;

class ClinicalRecordForm extends Form
{
    public ?ClinicalRecord $record = null;

    /** true => valida todos los campos "required" del JSON; false => solo lo mínimo para un borrador */
    public bool $finalizing = false;

    // ── 1. Datos generales ────────────────────────────────────────────────
    public ?string $paciente_nombre = null;
    public ?string $doc_identificacion = null;
    public ?string $fecha_nacimiento = null;
    public ?string $sexo = null;
    public ?string $numero_episodio = null;
    public ?string $aseguradora_entidad = null;
    public ?string $numero_historia_clinica = null;
    public ?string $medico_tratante = null;
    public ?string $especialidad_servicio = null;

    // ── 2. Atención clínica ───────────────────────────────────────────────
    public ?string $tipo_atencion = null;
    public ?string $tipo_evento = null;
    public ?string $via_ingreso = null;
    public ?string $nivel_triage = null;

    public ?string $fecha_atencion = null;
    public ?string $hora_atencion = null;
    public ?string $motivo_consulta = null;
    public ?string $enfermedad_actual = null;
    public ?string $examen_fisico = null;
    public ?string $rayos_x_imagenologia = null;
    public ?string $plan_conducta = null;

    public ?string $antecedentes_alergicos = null;
    public ?string $antecedentes_farmacologicos = null;
    public ?string $antecedentes_patologicos = null;
    public ?string $antecedentes_quirurgicos = null;

    public ?string $profesional_responsable_nombre = null;
    public ?string $profesional_responsable_especialidad = null;
    public ?string $profesional_responsable_cedula = null;
    public ?string $profesional_responsable_rm = null;
    public bool $firma_electronica = true;
    public ?string $factor_aislamiento = null;

    // ── 3. Diagnósticos y conducta ────────────────────────────────────────
    public ?string $diagnostico_cie10_codigo = null;
    public ?string $diagnostico_cie10_descripcion = null;
    public ?string $analisis_y_conducta = null;
    public ?string $examen_fisico_control = null;
    public ?string $plan_manejo_incapacidad_texto = null;

    // ── 4. Incapacidad ────────────────────────────────────────────────────
    public ?string $incapacidad_fecha_inicio = null;
    public ?string $incapacidad_fecha_fin = null;
    public ?int $incapacidad_dias = null;
    public bool $incapacidad_es_prorroga = false;
    public ?string $incapacidad_tipo = null;
    public ?string $incapacidad_clase = null;
    public ?string $incapacidad_diagnostico_cie10 = null;

    /** Filas de la tabla "Órdenes & Fórmulas Médicas Ambulatorias" */
    public array $ordenes = [];

    // ──────────────────────────────────────────────────────────────────────
    public function rules(): array
    {
        $req = $this->finalizing ? 'required' : 'nullable';

        return [
            // Siempre obligatorios (aun en borrador, para poder identificar el documento)
            'paciente_nombre' => ['required', 'string', 'max:150'],
            'doc_identificacion' => ['required', 'string', 'max:30'],

            'fecha_nacimiento' => ['nullable', 'date', 'before_or_equal:today'],
            'sexo' => ['nullable', Rule::in(array_keys(ClinicalRecord::SEXOS))],
            'numero_episodio' => ['nullable', 'string', 'max:30'],
            'aseguradora_entidad' => ['nullable', 'string', 'max:150'],
            'numero_historia_clinica' => ['nullable', 'string', 'max:30'],
            'medico_tratante' => ['nullable', 'string', 'max:150'],
            'especialidad_servicio' => ['nullable', 'string', 'max:100'],

            'tipo_atencion' => [$req, Rule::in(array_keys(ClinicalRecord::TIPOS_ATENCION))],
            'tipo_evento' => [$req, Rule::in(array_keys(ClinicalRecord::TIPOS_EVENTO))],
            'via_ingreso' => ['nullable', Rule::in(array_keys(ClinicalRecord::VIAS_INGRESO))],
            'nivel_triage' => ['nullable', Rule::in(array_keys(ClinicalRecord::NIVELES_TRIAGE))],

            'fecha_atencion' => [$req, 'date'],
            'hora_atencion' => [$req, 'date_format:H:i,H:i:s'],
            'motivo_consulta' => [$req, 'string', 'max:2000'],
            'enfermedad_actual' => [$req, 'string', 'max:10000'],
            'examen_fisico' => [$req, 'string', 'max:10000'],
            'rayos_x_imagenologia' => ['nullable', 'string', 'max:10000'],
            'plan_conducta' => [$req, 'string', 'max:10000'],

            'antecedentes_alergicos' => ['nullable', 'string', 'max:255'],
            'antecedentes_farmacologicos' => ['nullable', 'string', 'max:255'],
            'antecedentes_patologicos' => ['nullable', 'string', 'max:255'],
            'antecedentes_quirurgicos' => ['nullable', 'string', 'max:255'],

            'profesional_responsable_nombre' => [$req, 'string', 'max:150'],
            'profesional_responsable_especialidad' => ['nullable', 'string', 'max:100'],
            'profesional_responsable_cedula' => [$req, 'string', 'max:20'],
            'profesional_responsable_rm' => [$req, 'string', 'max:30'],
            'firma_electronica' => ['boolean'],
            'factor_aislamiento' => ['nullable', 'string', 'max:100'],

            'diagnostico_cie10_codigo' => [$req, 'string', 'max:10'],
            'diagnostico_cie10_descripcion' => ['nullable', 'string', 'max:255'],
            'analisis_y_conducta' => ['nullable', 'string', 'max:10000'],
            'examen_fisico_control' => ['nullable', 'string', 'max:10000'],
            'plan_manejo_incapacidad_texto' => ['nullable', 'string', 'max:10000'],

            // La incapacidad es opcional, pero si se diligencia una parte, se exige el resto
            'incapacidad_fecha_inicio' => ['nullable', 'required_with:incapacidad_fecha_fin,incapacidad_dias', 'date'],
            'incapacidad_fecha_fin' => ['nullable', 'required_with:incapacidad_fecha_inicio,incapacidad_dias', 'date', 'after_or_equal:incapacidad_fecha_inicio'],
            'incapacidad_dias' => ['nullable', 'required_with:incapacidad_fecha_inicio,incapacidad_fecha_fin', 'integer', 'between:1,365'],
            'incapacidad_es_prorroga' => ['boolean'],
            'incapacidad_tipo' => ['nullable', Rule::in(array_keys(ClinicalRecord::INCAPACIDAD_TIPOS))],
            'incapacidad_clase' => ['nullable', Rule::in(array_keys(ClinicalRecord::INCAPACIDAD_CLASES))],
            'incapacidad_diagnostico_cie10' => ['nullable', 'string', 'max:255'],

            'ordenes' => ['array'],
            'ordenes.*.tipo' => ['required', Rule::in(array_keys(ClinicalOrder::TIPOS))],
            'ordenes.*.fecha_hora' => ['required', 'date'],
            'ordenes.*.codigo' => ['required', 'string', 'max:20'],
            'ordenes.*.nombre_prescripcion' => ['required', 'string', 'max:255'],
            'ordenes.*.instrucciones' => ['nullable', 'string', 'max:2000'],
            'ordenes.*.unidad_organizativa' => ['nullable', 'string', 'max:100'],
            'ordenes.*.medico_responsable' => ['nullable', 'string', 'max:150'],
        ];
    }

    // ── Carga ─────────────────────────────────────────────────────────────
    public function setDefaults(): void
    {
        $this->tipo_atencion = 'consulta_externa';
        $this->tipo_evento = 'enfermedad_general';
        $this->fecha_atencion = today()->format('Y-m-d');
        $this->hora_atencion = now()->format('H:i:s');
        $this->incapacidad_tipo = 'ambulatoria';
        $this->incapacidad_clase = 'enfermedad_general';
    }

    public function setRecord(ClinicalRecord $record): void
    {
        $this->record = $record;

        foreach (array_keys($this->fields()) as $key) {
            $value = $record->{$key};
            $this->{$key} = $value instanceof \DateTimeInterface ? $value->format('Y-m-d') : $value;
        }

        $this->ordenes = $record->ordenes
            ->map(fn (ClinicalOrder $o) => [
                'tipo' => $o->tipo,
                'fecha_hora' => $o->fecha_hora->format('Y-m-d\TH:i'),
                'codigo' => $o->codigo,
                'nombre_prescripcion' => $o->nombre_prescripcion,
                'instrucciones' => $o->instrucciones,
                'unidad_organizativa' => $o->unidad_organizativa,
                'medico_responsable' => $o->medico_responsable,
            ])->all();
    }

    // ── Cálculos ──────────────────────────────────────────────────────────
    /** Días = (fin - inicio) + 1, inclusive. 24/07 → 22/08 = 30 días. */
    public function recalcularDias(): void
    {
        if ($this->incapacidad_fecha_inicio && $this->incapacidad_fecha_fin) {
            $dias = Carbon::parse($this->incapacidad_fecha_inicio)
                ->diffInDays(Carbon::parse($this->incapacidad_fecha_fin), false) + 1;
            $this->incapacidad_dias = $dias >= 1 ? (int) $dias : null;
        }
    }

    // ── Persistencia ──────────────────────────────────────────────────────
    public function persist(bool $finalize = false): ClinicalRecord
    {
        $this->finalizing = $finalize;
        $this->validate();

        $data = array_map(fn ($v) => $v === '' ? null : $v, $this->fields());

        if ($finalize) {
            $data['estado'] = ClinicalRecord::ESTADO_FINALIZADA;
            $data['finalizada_at'] = now();
        }

        return DB::transaction(function () use ($data) {
            if ($this->record) {
                $this->record->update($data);
            } else {
                $this->record = ClinicalRecord::create($data + ['estado' => ClinicalRecord::ESTADO_BORRADOR]);
            }

            $this->record->ordenes()->delete();
            $this->record->ordenes()->createMany(
                collect($this->ordenes)->map(fn ($o) => Arr::only($o, [
                    'tipo', 'fecha_hora', 'codigo', 'nombre_prescripcion',
                    'instrucciones', 'unidad_organizativa', 'medico_responsable',
                ]))->all()
            );

            return $this->record->refresh();
        });
    }

    /** Solo las propiedades que son columnas del modelo. */
    protected function fields(): array
    {
        return $this->except(['record', 'finalizing', 'ordenes']);
    }
}
