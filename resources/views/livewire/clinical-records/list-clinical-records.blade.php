@php
    use App\Models\ClinicalRecord as R;
@endphp

<div class="mx-auto max-w-[1400px] space-y-5 px-6 py-6">
    {{-- Encabezado --}}
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl" level="1">Historias clínicas</flux:heading>
            <flux:text class="mt-1">
                {{ $this->totals['finalizada'] }} finalizadas · {{ $this->totals['borrador'] }} en borrador
            </flux:text>
        </div>

        <flux:button variant="primary" icon="plus" :href="route('clinical-records.create')" wire:navigate>
            Nueva orden
        </flux:button>
    </div>

    {{-- Filtros --}}
    <div class="flex flex-wrap items-center gap-3">
        <div class="w-full max-w-sm">
            <flux:input
                wire:model.live.debounce.300ms="search"
                icon="magnifying-glass"
                placeholder="Buscar por paciente, documento, HC, episodio o código..."
                clearable
            />
        </div>

        <div class="w-48">
            <flux:select wire:model.live="estado">
                <flux:select.option value="">Todos los estados</flux:select.option>
                <flux:select.option value="borrador">Borrador</flux:select.option>
                <flux:select.option value="finalizada">Finalizada</flux:select.option>
            </flux:select>
        </div>
    </div>

    {{-- Tabla --}}
    <flux:table :paginate="$this->records" pagination:scroll-to>
        <flux:table.columns>
            <flux:table.column sortable :sorted="$sortBy === 'codigo_documento'" :direction="$sortDirection" wire:click="sort('codigo_documento')">Código</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'paciente_nombre'" :direction="$sortDirection" wire:click="sort('paciente_nombre')">Paciente</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'doc_identificacion'" :direction="$sortDirection" wire:click="sort('doc_identificacion')">Documento</flux:table.column>
            <flux:table.column>Nº HC</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'tipo_atencion'" :direction="$sortDirection" wire:click="sort('tipo_atencion')">Tipo de atención</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'fecha_atencion'" :direction="$sortDirection" wire:click="sort('fecha_atencion')">Fecha de atención</flux:table.column>
            <flux:table.column>Médico</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'estado'" :direction="$sortDirection" wire:click="sort('estado')">Estado</flux:table.column>
            <flux:table.column align="end"></flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->records as $record)
                <flux:table.row :key="$record->id">
                    <flux:table.cell class="whitespace-nowrap">{{ $record->codigo_documento }}</flux:table.cell>

                    <flux:table.cell variant="strong">{{ $record->paciente_nombre }}</flux:table.cell>

                    <flux:table.cell class="whitespace-nowrap">{{ $record->doc_identificacion }}</flux:table.cell>

                    <flux:table.cell>{{ $record->numero_historia_clinica }}</flux:table.cell>

                    <flux:table.cell>{{ R::etiqueta(R::TIPOS_ATENCION, $record->tipo_atencion) }}</flux:table.cell>

                    <flux:table.cell class="whitespace-nowrap">
                        {{ $record->fecha_atencion?->format('d/m/Y') }}
                        <span class="text-zinc-400">{{ $record->hora_atencion ? substr($record->hora_atencion, 0, 5) : '' }}</span>
                    </flux:table.cell>

                    <flux:table.cell>{{ $record->profesional_responsable_nombre ?? $record->medico_tratante }}</flux:table.cell>

                    <flux:table.cell class="py-0">
                        @if ($record->esFinalizada())
                            <flux:badge size="sm" color="green" icon="check-circle">Finalizada</flux:badge>
                        @else
                            <flux:badge size="sm" color="amber" icon="pencil-square">Borrador</flux:badge>
                        @endif
                    </flux:table.cell>

                    <flux:table.cell align="end" class="py-0">
                        <flux:dropdown position="bottom" align="end">
                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom" />

                            <flux:menu>
                                <flux:menu.item
                                    :icon="$record->esFinalizada() ? 'eye' : 'pencil-square'"
                                    :href="route('clinical-records.edit', $record)"
                                    wire:navigate
                                >
                                    {{ $record->esFinalizada() ? 'Ver' : 'Editar' }}
                                </flux:menu.item>

                                <flux:menu.item icon="document-text" :href="route('clinical-records.pdf.historia', $record)" target="_blank">
                                    PDF historia clínica
                                </flux:menu.item>

                                @if ($record->tieneIncapacidad() || $record->formulas_exists)
                                    <flux:menu.item icon="document-text" :href="route('clinical-records.pdf.formula', $record)" target="_blank">
                                        PDF fórmula médica
                                    </flux:menu.item>
                                @endif

                                @unless ($record->esFinalizada())
                                    <flux:menu.separator />

                                    <flux:menu.item
                                        variant="danger"
                                        icon="trash"
                                        wire:click="delete({{ $record->id }})"
                                        wire:confirm="¿Eliminar este borrador? Esta acción no se puede deshacer."
                                    >
                                        Eliminar borrador
                                    </flux:menu.item>
                                @endunless
                            </flux:menu>
                        </flux:dropdown>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="9" class="py-10 text-center text-zinc-400">
                        No hay historias clínicas que coincidan con el filtro.
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</div>
