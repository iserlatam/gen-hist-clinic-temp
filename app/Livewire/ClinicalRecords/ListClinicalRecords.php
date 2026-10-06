<?php

namespace App\Livewire\ClinicalRecords;

use App\Models\ClinicalRecord;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts::app')]
#[Title('Historias Clínicas')]
class ListClinicalRecords extends Component
{
    use WithPagination;

    /** Columnas permitidas para ordenar (evita inyectar nombres de columna arbitrarios). */
    private const SORTABLE = [
        'codigo_documento', 'paciente_nombre', 'doc_identificacion',
        'tipo_atencion', 'fecha_atencion', 'estado',
    ];

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $estado = '';          // '' = todos | borrador | finalizada

    #[Url]
    public string $sortBy = 'fecha_atencion';

    #[Url]
    public string $sortDirection = 'desc';

    public function sort(string $column): void
    {
        if (! in_array($column, self::SORTABLE, true)) {
            return;
        }

        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedEstado(): void
    {
        $this->resetPage();
    }

    /** Solo se pueden eliminar borradores; las finalizadas son inmutables. */
    public function delete(int $id): void
    {
        $record = ClinicalRecord::findOrFail($id);

        abort_if($record->esFinalizada(), 403, 'No se puede eliminar una historia finalizada.');

        $record->delete(); // SoftDeletes
        unset($this->records, $this->totals);
    }

    #[Computed]
    public function records(): LengthAwarePaginator
    {
        $sortBy = in_array($this->sortBy, self::SORTABLE, true) ? $this->sortBy : 'fecha_atencion';
        $direction = $this->sortDirection === 'asc' ? 'asc' : 'desc';

        return ClinicalRecord::query()
            ->withExists('formulas')
            ->when($this->estado !== '', fn (Builder $q) => $q->where('estado', $this->estado))
            ->when(trim($this->search) !== '', function (Builder $q) {
                $term = '%'.trim($this->search).'%';
                $q->where(fn (Builder $w) => $w
                    ->where('paciente_nombre', 'like', $term)
                    ->orWhere('doc_identificacion', 'like', $term)
                    ->orWhere('numero_historia_clinica', 'like', $term)
                    ->orWhere('numero_episodio', 'like', $term)
                    ->orWhere('codigo_documento', 'like', $term));
            })
            ->orderBy($sortBy, $direction)
            ->orderByDesc('id')
            ->paginate(10);
    }

    /** Contadores por estado para el encabezado. */
    #[Computed]
    public function totals(): array
    {
        $counts = ClinicalRecord::query()
            ->selectRaw('estado, count(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado');

        return [
            'borrador' => (int) ($counts[ClinicalRecord::ESTADO_BORRADOR] ?? 0),
            'finalizada' => (int) ($counts[ClinicalRecord::ESTADO_FINALIZADA] ?? 0),
        ];
    }

    public function render()
    {
        return view('livewire.clinical-records.list-clinical-records');
    }
}
