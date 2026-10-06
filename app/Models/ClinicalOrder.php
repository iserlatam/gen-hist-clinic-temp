<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClinicalOrder extends Model
{
    public const TIPO_ORDEN = 'orden';     // "Órdenes Generales" de la historia clínica (PDF pág. 2)
    public const TIPO_FORMULA = 'formula'; // fórmulas / órdenes ambulatorias (PDF de fórmula médica)

    public const TIPOS = [
        self::TIPO_ORDEN => 'Orden general',
        self::TIPO_FORMULA => 'Fórmula / Orden ambulatoria',
    ];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['fecha_hora' => 'datetime'];
    }

    public function clinicalRecord(): BelongsTo
    {
        return $this->belongsTo(ClinicalRecord::class);
    }
}
