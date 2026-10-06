<?php

namespace App\Livewire\ClinicalRecords;

use App\Livewire\Forms\ClinicalRecordForm;
use App\Models\ClinicalOrder;
use App\Models\ClinicalRecord;
use Carbon\Carbon;
use Illuminate\Support\Js;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::app')]
#[Title('Orden Médica')]
class EditClinicalRecord extends Component
{
    public ClinicalRecordForm $form;

    public bool $showOrderModal = false;

    /** Fila temporal del modal "+ Agregar Fórmula / Orden" */
    public array $newOrder = [];

    public ?string $status = null;

    public function mount(?ClinicalRecord $record = null): void
    {
        if ($record?->exists) {
            $this->form->setRecord($record->load('ordenes'));
        } else {
            $this->form->setDefaults();
        }
    }

    // ── Estado derivado ───────────────────────────────────────────────────
    #[Computed]
    public function locked(): bool
    {
        return (bool) $this->form->record?->esFinalizada();
    }

    /** Edad calculada (solo lectura) a la fecha de atención. */
    #[Computed]
    public function edad(): ?string
    {
        if (! $this->form->fecha_nacimiento) {
            return null;
        }

        $ref = $this->form->fecha_atencion ? Carbon::parse($this->form->fecha_atencion) : now();

        return (int) Carbon::parse($this->form->fecha_nacimiento)->diffInYears($ref).' Años';
    }

    // ── Hooks ─────────────────────────────────────────────────────────────
    public function updatedFormIncapacidadFechaInicio(): void
    {
        $this->form->recalcularDias();
    }

    public function updatedFormIncapacidadFechaFin(): void
    {
        $this->form->recalcularDias();
    }

    // ── Acciones: documento ───────────────────────────────────────────────
    public function saveDraft(): void
    {
        $this->guardLocked();
        $this->form->persist(false);
        $this->status = 'Borrador guardado ('.$this->form->record->codigo_documento.').';
    }

    public function finalize(): void
    {
        $this->guardLocked();
        $this->form->persist(true);
        $this->status = 'Orden finalizada. El documento quedó bloqueado para edición.';
    }

    public function previewHistoria(): void
    {
        $this->openPdf('clinical-records.pdf.historia');
    }

    public function previewFormula(): void
    {
        $this->openPdf('clinical-records.pdf.formula');
    }

    // ── Acciones: tabla de órdenes / fórmulas ─────────────────────────────
    public function openOrderModal(): void
    {
        $this->guardLocked();
        $this->newOrder = [
            'tipo' => ClinicalOrder::TIPO_FORMULA,
            'fecha_hora' => now()->format('Y-m-d\TH:i'),
            'codigo' => '',
            'nombre_prescripcion' => '',
            'instrucciones' => '',
            'unidad_organizativa' => '',
            'medico_responsable' => $this->form->profesional_responsable_nombre ?? '',
        ];
        $this->resetErrorBag();
        $this->showOrderModal = true;
    }

    public function addOrder(): void
    {
        $this->guardLocked();

        $this->validate([
            'newOrder.tipo' => ['required', 'in:'.implode(',', array_keys(ClinicalOrder::TIPOS))],
            'newOrder.fecha_hora' => ['required', 'date'],
            'newOrder.codigo' => ['required', 'string', 'max:20'],
            'newOrder.nombre_prescripcion' => ['required', 'string', 'max:255'],
            'newOrder.instrucciones' => ['nullable', 'string', 'max:2000'],
            'newOrder.unidad_organizativa' => ['nullable', 'string', 'max:100'],
            'newOrder.medico_responsable' => ['nullable', 'string', 'max:150'],
        ]);

        $this->form->ordenes[] = $this->newOrder;
        $this->showOrderModal = false;
    }

    public function removeOrder(int $index): void
    {
        $this->guardLocked();
        unset($this->form->ordenes[$index]);
        $this->form->ordenes = array_values($this->form->ordenes);
    }

    // ── Internos ──────────────────────────────────────────────────────────
    protected function guardLocked(): void
    {
        abort_if($this->locked, 403, 'La orden está finalizada y no puede modificarse.');
    }

    /** Guarda como borrador (si aún es editable) y abre el PDF en una pestaña nueva. */
    protected function openPdf(string $route): void
    {
        $record = $this->locked ? $this->form->record : $this->form->persist(false);

        $this->js('window.open('.Js::from(route($route, $record)).", '_blank')");
    }

    public function render()
    {
        return view('livewire.clinical-records.edit-clinical-record');
    }
}
