<?php

namespace App\Http\Controllers;

use App\Models\ClinicalRecord;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class ClinicalRecordPdfController extends Controller
{
    /** PDF 1: Historia Clínica General (datos generales, anamnesis, antecedentes, diagnóstico, órdenes generales). */
    public function historia(ClinicalRecord $record): Response
    {
        $record->load('ordenesGenerales');

        return Pdf::loadView('pdf.historia-clinica', $this->data($record))
            ->setPaper('letter', 'portrait')
            ->stream("historia-clinica-{$record->numero_historia_clinica}-{$record->id}.pdf");
    }

    /** PDF 2: Incapacidad + fórmulas / órdenes ambulatorias (sección 4 del JSON). */
    public function formula(ClinicalRecord $record): Response
    {
        $record->load('formulas');

        abort_unless(
            $record->tieneIncapacidad() || $record->formulas->isNotEmpty(),
            404,
            'La orden no tiene incapacidad ni fórmulas para imprimir.'
        );

        return Pdf::loadView('pdf.formula-medica', $this->data($record))
            ->setPaper('letter', 'portrait')
            ->stream("formula-medica-{$record->numero_historia_clinica}-{$record->id}.pdf");
    }

    private function data(ClinicalRecord $record): array
    {
        return [
            'record' => $record,
            'inst' => config('clinica.institucion'),
            // Pie de página: "Paciente / Impreso por / fecha / hora" (momento real de impresión)
            'impresoPor' => $record->impreso_por ?: (string) auth()->id(),
            'impresoEn' => now(),
        ];
    }
}
