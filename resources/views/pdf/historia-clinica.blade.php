@extends('pdf.layout')

@php
    use App\Models\ClinicalRecord as R;

    // En la historia impresa, examen físico / RX / plan van continuos dentro de "Enfermedad Actual"
    $enfermedad = array_filter([
        $record->enfermedad_actual,
        $record->examen_fisico,
        $record->rayos_x_imagenologia ? 'RX '.$record->rayos_x_imagenologia : null,
        $record->plan_conducta ? "PLAN\n".$record->plan_conducta : null,
    ]);

    $analisis = array_filter([
        $record->analisis_y_conducta,
        $record->examen_fisico_control,
        $record->plan_manejo_incapacidad_texto,
    ]);
@endphp

@section('title', 'Historia Clínica General')
@section('doc_title', 'HISTORIA CLÍNICA GENERAL')

@section('content')

    {{-- ATENCIÓN CLÍNICA --}}
    <div class="band">ATENCIÓN CLÍNICA</div>
    <table style="margin-top:4pt">
        <tr>
            <td style="width:33%"><span class="lbl">Tipo de Atención:</span> <span class="val">{{ R::etiqueta(R::TIPOS_ATENCION, $record->tipo_atencion) }}</span></td>
            <td><span class="lbl">Tipo de Evento:</span> <span class="val">{{ R::etiqueta(R::TIPOS_EVENTO, $record->tipo_evento) }}</span></td>
        </tr>
        @if($record->via_ingreso || $record->nivel_triage)
            <tr>
                <td style="padding-top:2pt"><span class="lbl">Vía de Ingreso:</span> <span class="val">{{ R::etiqueta(R::VIAS_INGRESO, $record->via_ingreso) }}</span></td>
                <td style="padding-top:2pt"><span class="lbl">Nivel de Triage:</span> <span class="val">{{ R::etiqueta(R::NIVELES_TRIAGE, $record->nivel_triage) }}</span></td>
            </tr>
        @endif
    </table>
    <div class="rule" style="margin-top:4pt"></div>

    {{-- ANAMNESIS --}}
    <div class="sub">Anamnesis</div>
    <table>
        <tr>
            <td style="width:33%"><span class="lbl">Fecha:</span> {{ $record->fecha_atencion?->format('d.m.Y') }}</td>
            <td>{{ $record->hora_atencion }}</td>
        </tr>
    </table>
    <div class="rule" style="margin-top:5pt"></div>

    <div style="margin-top:14pt"><b>Motivo de consulta:</b></div>
    <div class="p" style="margin-top:7pt; margin-bottom:12pt">{!! nl2br(e($record->motivo_consulta)) !!}</div>

    <div><b>Enfermedad Actual:</b></div>
    <div style="margin-top:5pt">
        @foreach($enfermedad as $parrafo)
            <div class="p">{!! nl2br(e($parrafo)) !!}</div>
        @endforeach
    </div>

    {{-- ANTECEDENTES --}}
    <div class="band avoid">Antecedentes</div>
    <table class="avoid" style="margin-top:3pt">
        <tr><td style="width:20%; padding:3.5pt 4pt"><span class="lbl">Alérgicos :</span></td><td style="padding:3.5pt 0">{{ $record->antecedentes_alergicos }}</td></tr>
        <tr><td style="padding:3.5pt 4pt"><span class="lbl">Farmacológicos :</span></td><td style="padding:3.5pt 0">{{ $record->antecedentes_farmacologicos }}</td></tr>
        <tr><td style="padding:3.5pt 4pt"><span class="lbl">Patológicos :</span></td><td style="padding:3.5pt 0">{{ $record->antecedentes_patologicos }}</td></tr>
        <tr><td style="padding:3.5pt 4pt"><span class="lbl">Quirúrgicos :</span></td><td style="padding:3.5pt 0">{{ $record->antecedentes_quirurgicos }}</td></tr>
    </table>

    {{-- PROFESIONAL RESPONSABLE --}}
    <table class="avoid" style="margin-top:22pt; font-size:7.5pt">
        <tr>
            <td style="width:9%; padding-left:4pt">Responsable:</td>
            <td style="width:24%"><div style="width:100pt">{{ $record->profesional_responsable_nombre }}</div></td>
            <td>{{ $record->profesional_responsable_especialidad }}</td>
        </tr>
        <tr>
            <td style="padding:6pt 0 0 4pt">Cédula:</td>
            <td style="padding-top:6pt">{{ $record->profesional_responsable_cedula }}</td>
            <td style="padding-top:6pt">RM:{{ $record->profesional_responsable_rm }}</td>
        </tr>
    </table>

    {{-- DIAGNÓSTICOS --}}
    <div class="band avoid" style="margin-top:6pt">Diagnósticos</div>
    <table class="avoid" style="border-bottom:0.6pt solid #000">
        <tr>
            <td style="width:7%; padding:3pt 4pt" class="val">{{ $record->diagnostico_cie10_codigo }}</td>
            <td style="padding:3pt 0">{{ $record->diagnostico_cie10_descripcion }}</td>
        </tr>
    </table>

    {{-- ANÁLISIS Y CONDUCTA --}}
    <div style="margin-top:5pt"><b>Análisis y Conducta</b></div>
    <div style="margin-top:6pt">
        @foreach($analisis as $parrafo)
            <div class="p">{!! nl2br(e($parrafo)) !!}</div>
        @endforeach
    </div>

    <table class="avoid" style="margin-top:2pt">
        <tr>
            <td style="width:33%; padding-left:4pt">{{ $record->profesional_responsable_nombre }}</td>
            <td>{{ $record->profesional_responsable_especialidad }}</td>
        </tr>
    </table>

    {{-- FIRMA / FACTOR DE AISLAMIENTO / ÓRDENES CLÍNICAS --}}
    <div class="avoid">
        <table class="grid" style="margin-top:10pt">
            <tr class="spacer"><td style="width:8%"></td><td style="width:28%"></td><td></td></tr>
            <tr>
                <td>Cédula:</td>
                <td>{{ $record->profesional_responsable_cedula }}</td>
                <td>RM:{{ $record->profesional_responsable_rm }}</td>
            </tr>
            @if($record->firma_electronica)
                <tr><td colspan="3">Válido como Firma Electrónica</td></tr>
            @endif
        </table>

        <div class="band" style="margin-top:0">Factor de Aislamiento</div>
        @if($record->factor_aislamiento)
            <div class="p" style="margin-top:4pt">{{ $record->factor_aislamiento }}</div>
        @endif

        <div class="band" style="margin-top:0">Ordenes Clínicas</div>
        <table class="grid">
            <tr class="spacer">
                <td style="width:14%"></td><td style="width:9.5%"></td><td style="width:31%"></td><td style="width:20.5%"></td><td style="width:25%"></td>
            </tr>
            <tr><td colspan="5"><b>Ordenes Generales</b></td></tr>
            <tr style="text-align:center">
                <td><b>Fecha</b></td><td><b>Código</b></td><td><b>Nombre</b></td><td><b>U. Organizativa</b></td><td><b>Responsable</b></td>
            </tr>
            @forelse($record->ordenesGenerales as $o)
                <tr>
                    <td>{{ $o->fecha_hora->format('d.m.Y') }}</td>
                    <td style="text-align:center">{{ $o->codigo }}</td>
                    <td>{{ $o->nombre_prescripcion }}</td>
                    <td>{{ $o->unidad_organizativa }}</td>
                    <td style="text-align:center">{{ $o->medico_responsable }}</td>
                </tr>
            @empty
                <tr><td colspan="5" style="text-align:center">Sin órdenes generales.</td></tr>
            @endforelse
        </table>
    </div>
@endsection
