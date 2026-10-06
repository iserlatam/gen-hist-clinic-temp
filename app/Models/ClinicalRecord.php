<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClinicalRecord extends Model
{
    use SoftDeletes;

    public const ESTADO_BORRADOR = 'borrador';
    public const ESTADO_FINALIZADA = 'finalizada';

    // Catálogos (valor => etiqueta). Compartidos por el formulario, la validación y los PDF.
    public const SEXOS = ['F' => 'Femenino', 'M' => 'Masculino', 'O' => 'Intersexual / Otro'];

    public const TIPOS_ATENCION = [
        'urgencias' => 'Urgencias',
        'consulta_externa' => 'Consulta Externa',
        'hospitalizacion' => 'Hospitalización',
        'cirugia_ambulatoria' => 'Cirugía Ambulatoria',
    ];

    public const TIPOS_EVENTO = [
        'soat' => 'Accidente de tránsito (SOAT)',
        'enfermedad_general' => 'Enfermedad general',
        'arl' => 'Accidente laboral (ARL)',
        'catastrofico' => 'Evento catastrófico',
    ];

    public const VIAS_INGRESO = [
        'espontaneo_remitido' => 'Espontáneo / Remitido',
        'ambulancia' => 'Ambulancia medicalizada',
        'interconsulta' => 'Interconsulta hospitalaria',
    ];

    public const NIVELES_TRIAGE = [
        'triage_1' => 'Triage I (Reanimación Crítica)',
        'triage_2' => 'Triage II (Urgencia Prioritaria)',
        'triage_3' => 'Triage III (Urgencia Menor)',
        'triage_4' => 'Triage IV (No Urgente)',
        'triage_5' => 'Triage V (Atención Rápida / Consulta)',
    ];

    public const INCAPACIDAD_TIPOS = [
        'ambulatoria' => 'Ambulatoria',
        'hospitalaria' => 'Hospitalaria',
        'reposo_domiciliario' => 'Reposo Domiciliario',
    ];

    public const INCAPACIDAD_CLASES = [
        'enfermedad_general' => 'Enfermedad General',
        'accidente_trabajo' => 'Accidente de Trabajo',
        'accidente_transito' => 'Accidente de Tránsito',
        'licencia' => 'Maternidad / Paternidad',
    ];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
            'fecha_atencion' => 'date',
            'incapacidad_fecha_inicio' => 'date',
            'incapacidad_fecha_fin' => 'date',
            'incapacidad_dias' => 'integer',
            'incapacidad_es_prorroga' => 'boolean',
            'firma_electronica' => 'boolean',
            'finalizada_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        // HC-DOC-00999 a partir del id
        static::created(function (self $record) {
            if (! $record->codigo_documento) {
                $record->updateQuietly([
                    'codigo_documento' => 'HC-DOC-'.str_pad((string) $record->id, 5, '0', STR_PAD_LEFT),
                ]);
            }
        });
    }

    // ── Relaciones ────────────────────────────────────────────────────────
    public function ordenes(): HasMany
    {
        return $this->hasMany(ClinicalOrder::class)->orderBy('fecha_hora');
    }

    public function ordenesGenerales(): HasMany
    {
        return $this->ordenes()->where('tipo', ClinicalOrder::TIPO_ORDEN);
    }

    public function formulas(): HasMany
    {
        return $this->ordenes()->where('tipo', ClinicalOrder::TIPO_FORMULA);
    }

    // ── Accessors ─────────────────────────────────────────────────────────
    /** Edad en años cumplidos a la fecha de atención (o a hoy). Solo lectura. */
    protected function edad(): Attribute
    {
        return Attribute::get(function () {
            if (! $this->fecha_nacimiento) {
                return null;
            }
            $referencia = $this->fecha_atencion ?? now();

            return (int) $this->fecha_nacimiento->diffInYears($referencia);
        });
    }

    protected function diagnosticoPrincipal(): Attribute
    {
        return Attribute::get(fn () => trim($this->diagnostico_cie10_codigo.' - '.$this->diagnostico_cie10_descripcion, ' -'));
    }

    // ── Helpers ───────────────────────────────────────────────────────────
    public function esFinalizada(): bool
    {
        return $this->estado === self::ESTADO_FINALIZADA;
    }

    public function tieneIncapacidad(): bool
    {
        return $this->incapacidad_fecha_inicio !== null && $this->incapacidad_fecha_fin !== null;
    }

    public static function etiqueta(array $catalogo, ?string $valor): ?string
    {
        return $valor ? ($catalogo[$valor] ?? $valor) : null;
    }
}
