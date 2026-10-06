@php
    use App\Models\ClinicalOrder;
    use App\Models\ClinicalRecord as R;

    $card = 'rounded-lg border border-slate-200 bg-white p-5 shadow-sm';
    $h2 = 'text-[15px] font-semibold uppercase tracking-wide text-slate-800';
    $h3 = 'mb-3 mt-6 border-b border-slate-200 pb-1 text-[13px] font-semibold uppercase tracking-wide text-cyan-800';
    $btn = 'rounded-md px-4 py-2 text-sm font-medium transition disabled:opacity-50';
@endphp

<div class="mx-auto max-w-[1280px] space-y-5 px-6 py-6">
    {{-- Breadcrumb + barra de acciones --}}
    <div class="flex items-center justify-between">
        <nav class="text-xs text-slate-500">
            Inicio &gt; Órdenes Médicas &gt;
            <span class="font-medium text-slate-700">
                Orden #{{ $form->record?->id ?? 'Nueva' }}
            </span>
            @if($this->locked)
                <span class="ml-2 rounded bg-emerald-100 px-2 py-0.5 text-emerald-800">Finalizada</span>
            @else
                <span class="ml-2 rounded bg-amber-100 px-2 py-0.5 text-amber-800">Borrador</span>
            @endif
        </nav>

        <div class="flex gap-2">
            <button type="button" wire:click="previewHistoria" class="{{ $btn }} border border-slate-300 bg-white text-slate-700 hover:bg-slate-50">
                Historia clínica (PDF)
            </button>
            <button type="button" wire:click="previewFormula" class="{{ $btn }} border border-slate-300 bg-white text-slate-700 hover:bg-slate-50">
                Fórmula médica (PDF)
            </button>
            @unless($this->locked)
                <button type="button" wire:click="saveDraft" wire:loading.attr="disabled" class="{{ $btn }} border border-cyan-700 bg-white text-cyan-800 hover:bg-cyan-50">
                    Guardar Borrador
                </button>
                <button type="button" wire:click="finalize" wire:loading.attr="disabled"
                        wire:confirm="Al finalizar, la orden quedará bloqueada. ¿Continuar?"
                        class="{{ $btn }} bg-cyan-700 text-white hover:bg-cyan-800">
                    Finalizar Orden
                </button>
            @endunless
        </div>
    </div>

    @if($status)
        <div class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm text-emerald-800">{{ $status }}</div>
    @endif
    @if($errors->any())
        <div class="rounded-md border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-800">
            Hay {{ $errors->count() }} campo(s) con errores. Revise el formulario.
        </div>
    @endif

    <fieldset @disabled($this->locked) class="space-y-5">

        {{-- ═══ 1. DATOS GENERALES ═══ --}}
        <section class="{{ $card }}">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="{{ $h2 }}">1. Datos generales del paciente y atención</h2>
                <span class="rounded bg-slate-100 px-2 py-1 text-xs text-slate-600">
                    {{ $form->record?->codigo_documento ?? 'HC-DOC-?????' }}
                </span>
            </div>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-4 lg:grid-cols-6">
                <x-clinic.field class="lg:col-span-2" label="Paciente" name="paciente_nombre" required placeholder="Nombres y Apellidos completos" />
                <x-clinic.field label="Doc. Identificación" name="doc_identificacion" required placeholder="Ej: CC 14837059" />
                <x-clinic.field label="Fecha Nacimiento" name="fecha_nacimiento" type="date" live />
                <x-clinic.field label="Edad" name="edad" display :value="$this->edad ?? ''" />
                <x-clinic.field label="Sexo" name="sexo" type="select" :options="R::SEXOS" />

                <x-clinic.field label="Nº Episodio" name="numero_episodio" />
                <x-clinic.field class="lg:col-span-2" label="Aseguradora / Entidad" name="aseguradora_entidad" placeholder="EPS o Empresa Aseguradora" />
                <x-clinic.field label="Nº Historia Clínica" name="numero_historia_clinica" />
                <x-clinic.field label="Médico Tratante" name="medico_tratante" />
                <x-clinic.field label="Especialidad / Servicio" name="especialidad_servicio" />
            </div>
        </section>

        {{-- ═══ 2. ATENCIÓN CLÍNICA Y ANAMNESIS ═══ --}}
        <section class="{{ $card }}">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="{{ $h2 }}">2. Atención clínica y anamnesis</h2>
                <span class="rounded bg-slate-100 px-2 py-1 text-xs text-slate-600">Protocolo Formato F-02-HC</span>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <x-clinic.field label="Tipo de Atención" name="tipo_atencion" type="select" :options="R::TIPOS_ATENCION" required />
                <x-clinic.field label="Tipo de Evento" name="tipo_evento" type="select" :options="R::TIPOS_EVENTO" required />
                <x-clinic.field label="Vía de Ingreso" name="via_ingreso" type="select" :options="R::VIAS_INGRESO" />
                <x-clinic.field label="Nivel de Triage" name="nivel_triage" type="select" :options="R::NIVELES_TRIAGE" />
            </div>

            <h3 class="{{ $h3 }}">Subsección: Anamnesis</h3>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <x-clinic.field label="Fecha de Atención" name="fecha_atencion" type="date" required live />
                <x-clinic.field label="Hora de Atención" name="hora_atencion" type="time" required />
                <div class="md:col-span-4 grid gap-4">
                    <x-clinic.field label="Motivo de Consulta" name="motivo_consulta" type="textarea" :rows="2" required help="Palabras textuales del paciente" />
                    <x-clinic.field label="Enfermedad Actual" name="enfermedad_actual" type="textarea" :rows="4" required placeholder="Cronología y evolución de los síntomas..." />
                    <x-clinic.field label="Examen Físico" name="examen_fisico" type="textarea" :rows="4" required placeholder="Signos vitales, exploración segmentaria y hallazgos..." />
                    <x-clinic.field label="Rayos X / Imagenología (RX)" name="rayos_x_imagenologia" type="textarea" :rows="3" />
                    <x-clinic.field label="Plan" name="plan_conducta" type="textarea" :rows="3" required />
                </div>
            </div>

            <h3 class="{{ $h3 }}">Subsección: Antecedentes</h3>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <x-clinic.field label="Alérgicos" name="antecedentes_alergicos" placeholder="Ej: Penicilina o NO" />
                <x-clinic.field label="Farmacológicos" name="antecedentes_farmacologicos" />
                <x-clinic.field label="Patológicos" name="antecedentes_patologicos" />
                <x-clinic.field label="Quirúrgicos" name="antecedentes_quirurgicos" />
            </div>

            <h3 class="{{ $h3 }}">Subsección: Profesional Responsable</h3>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <x-clinic.field label="Responsable" name="profesional_responsable_nombre" required />
                <x-clinic.field label="Especialidad" name="profesional_responsable_especialidad" />
                <x-clinic.field label="Cédula" name="profesional_responsable_cedula" required />
                <x-clinic.field label="RM / Registro Médico" name="profesional_responsable_rm" required />
                <x-clinic.field class="md:col-span-2" label="Factor de Aislamiento" name="factor_aislamiento" />
                <label class="flex items-center gap-2 self-end pb-2 text-[13px] text-slate-700">
                    <input type="checkbox" wire:model="form.firma_electronica" class="rounded border-slate-300 text-cyan-700">
                    Válido como Firma Electrónica
                </label>
            </div>
        </section>

        {{-- ═══ 3. DIAGNÓSTICOS & CONDUCTA ═══ --}}
        <section class="{{ $card }}">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="{{ $h2 }}">3. Diagnósticos &amp; conducta médica</h2>
                <span class="rounded bg-slate-100 px-2 py-1 text-xs text-slate-600">Clasificación Internacional CIE-10</span>
            </div>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <x-clinic.field label="Diagnóstico Principal (código CIE-10)" name="diagnostico_cie10_codigo" required placeholder="S929" />
                <x-clinic.field class="md:col-span-3" label="Descripción" name="diagnostico_cie10_descripcion" placeholder="FRACTURA DEL PIE, NO ESPECIFICADA" />
                <div class="md:col-span-4 grid gap-4">
                    <x-clinic.field label="Análisis y Conducta" name="analisis_y_conducta" type="textarea" :rows="3" />
                    <x-clinic.field label="Examen Físico y Evolución de Control" name="examen_fisico_control" type="textarea" :rows="3" />
                    <x-clinic.field label="Plan de Manejo / Incapacidad Médica" name="plan_manejo_incapacidad_texto" type="textarea" :rows="3" />
                </div>
            </div>
        </section>

        {{-- ═══ 4. INCAPACIDAD & FÓRMULAS ═══ --}}
        <section class="{{ $card }}">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="{{ $h2 }}">4. Incapacidad médica &amp; fórmulas clínicas</h2>
                <span class="rounded bg-slate-100 px-2 py-1 text-xs text-slate-600">{{ config('clinica.institucion.nombre') }} | Formato Institucional</span>
            </div>

            <h3 class="{{ $h3 }} mt-0">Certificado de Incapacidad</h3>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <x-clinic.field label="Fecha Inicio" name="incapacidad_fecha_inicio" type="date" live />
                <x-clinic.field label="Fecha Fin" name="incapacidad_fecha_fin" type="date" live />
                <x-clinic.field label="Días de Incapacidad" name="incapacidad_dias" type="number" />
                <x-clinic.field label="Es Prórroga" name="incapacidad_es_prorroga" type="select" :options="['1' => 'Sí', '0' => 'No']" />
                <x-clinic.field label="Tipo de Incapacidad" name="incapacidad_tipo" type="select" :options="R::INCAPACIDAD_TIPOS" />
                <x-clinic.field label="Clase de Incapacidad" name="incapacidad_clase" type="select" :options="R::INCAPACIDAD_CLASES" />
                <x-clinic.field class="md:col-span-2" label="Diagnóstico Incapacidad (CIE-10)" name="incapacidad_diagnostico_cie10" />

                {{-- Solo lectura: reflejan al instante lo escrito en 2. Profesional Responsable (se digita a mano) --}}
                <x-clinic.field label="Médico Tratante" name="_m" mirror="$wire.form.profesional_responsable_nombre ?? ''" />
                <x-clinic.field label="Especialidad" name="_e" mirror="$wire.form.profesional_responsable_especialidad ?? ''" />
                <x-clinic.field label="Cédula Profesional" name="_c" mirror="($wire.form.profesional_responsable_cedula ?? '').replace(/^0+/, '')" />
                <x-clinic.field label="Registro Médico (RM / T.P.)" name="_r" mirror="$wire.form.profesional_responsable_rm ?? ''" />
            </div>

            <div class="mb-3 mt-6 flex items-center justify-between border-b border-slate-200 pb-1">
                <h3 class="text-[13px] font-semibold uppercase tracking-wide text-cyan-800">Órdenes &amp; Fórmulas Médicas Ambulatorias</h3>
                @unless($this->locked)
                    <button type="button" wire:click="openOrderModal" class="rounded-md bg-cyan-700 px-3 py-1.5 text-xs font-medium text-white hover:bg-cyan-800">
                        + Agregar Fórmula / Orden
                    </button>
                @endunless
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                        <tr>
                            <th class="px-3 py-2 w-[180px]">Fecha / Hora</th>
                            <th class="px-3 py-2 w-[120px]">Código</th>
                            <th class="px-3 py-2">Nombre / Prescripción médica</th>
                            <th class="px-3 py-2 w-[180px]">U. Organizativa</th>
                            <th class="px-3 py-2 w-[200px]">Médico responsable</th>
                            <th class="px-3 py-2 w-[80px] text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($form->ordenes as $i => $o)
                            <tr wire:key="orden-{{ $i }}-{{ $o['codigo'] }}">
                                <td class="px-3 py-2">{{ \Illuminate\Support\Carbon::parse($o['fecha_hora'])->format('d/m/Y H:i') }}</td>
                                <td class="px-3 py-2"><span class="rounded bg-cyan-50 px-2 py-0.5 text-xs font-medium text-cyan-800">{{ $o['codigo'] }}</span></td>
                                <td class="px-3 py-2">
                                    <div class="font-medium text-slate-800">{{ $o['nombre_prescripcion'] }}
                                        <span class="ml-1 text-[10px] uppercase text-slate-400">{{ ClinicalOrder::TIPOS[$o['tipo']] ?? '' }}</span>
                                    </div>
                                    @if(!empty($o['instrucciones']))<div class="text-xs text-slate-500">{{ $o['instrucciones'] }}</div>@endif
                                </td>
                                <td class="px-3 py-2">{{ $o['unidad_organizativa'] }}</td>
                                <td class="px-3 py-2">{{ $o['medico_responsable'] }}</td>
                                <td class="px-3 py-2 text-right">
                                    @unless($this->locked)
                                        <button type="button" wire:click="removeOrder({{ $i }})" class="text-xs text-red-600 hover:underline">Eliminar</button>
                                    @endunless
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-3 py-6 text-center text-sm text-slate-400">Sin fórmulas ni órdenes registradas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </fieldset>

    {{-- ═══ Modal: agregar fórmula / orden ═══ --}}
    @if($showOrderModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/65 p-4 backdrop-blur-sm">
            <div class="w-full max-w-2xl rounded-lg bg-white p-6 shadow-xl">
                <h3 class="mb-4 text-lg font-semibold text-slate-800">Agregar fórmula / orden</h3>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <x-clinic.field label="Tipo" name="tipo" model="newOrder.tipo" type="select" :options="ClinicalOrder::TIPOS" required />
                    <x-clinic.field label="Fecha / Hora" name="fecha_hora" model="newOrder.fecha_hora" type="datetime-local" required />
                    <x-clinic.field label="Código" name="codigo" model="newOrder.codigo" required placeholder="MED-0482" />
                    <x-clinic.field label="U. Organizativa" name="uo" model="newOrder.unidad_organizativa" />
                    <x-clinic.field class="md:col-span-2" label="Nombre / Prescripción" name="np" model="newOrder.nombre_prescripcion" required />
                    <x-clinic.field class="md:col-span-2" label="Instrucciones" name="ins" model="newOrder.instrucciones" type="textarea" :rows="2" />
                    <x-clinic.field class="md:col-span-2" label="Médico responsable" name="mr" model="newOrder.medico_responsable" />
                </div>
                <div class="mt-5 flex justify-end gap-2">
                    <button type="button" wire:click="$set('showOrderModal', false)" class="{{ $btn }} border border-slate-300 text-slate-700 hover:bg-slate-50">Cancelar</button>
                    <button type="button" wire:click="addOrder" class="{{ $btn }} bg-cyan-700 text-white hover:bg-cyan-800">Agregar</button>
                </div>
            </div>
        </div>
    @endif
</div>
