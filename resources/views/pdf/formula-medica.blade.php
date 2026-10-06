@extends('pdf.layout')

@php
    use App\Models\ClinicalRecord as R;

    $tieneInc = $record->tieneIncapacidad();
    $tieneFor = $record->formulas->isNotEmpty();
    $titulo = match (true) {
        $tieneInc && $tieneFor => 'INCAPACIDAD Y FÓRMULA MÉDICA',
        $tieneInc => 'INCAPACIDAD',
        default => 'FÓRMULA MÉDICA',
    };
@endphp

@section('title', $titulo)
@section('doc_title', $titulo)

@section('content')

    @if($tieneInc)
        <table style="margin-top:12pt">
            <tr class="spacer"><td style="width:24%"></td><td style="width:30%"></td><td style="width:30%"></td><td></td></tr>
            <tr>
                <td style="padding:2pt 4pt"><span class="lbl">Fecha inicio:</span> {{ $record->incapacidad_fecha_inicio->format('d.m.Y') }}</td>
                <td style="padding:2pt 0"><span class="lbl">Fecha fin:</span> {{ $record->incapacidad_fecha_fin->format('d.m.Y') }}</td>
                <td style="padding:2pt 0"><span class="lbl">Días de incapacidad:</span> {{ $record->incapacidad_dias }}</td>
                <td style="padding:2pt 0"><b>{{ $record->incapacidad_es_prorroga ? 'Es prórroga' : '' }}</b></td>
            </tr>
            <tr>
                <td colspan="2" style="padding:5pt 4pt 2pt"><span class="lbl">Tipo de incapacidad:</span> {{ R::etiqueta(R::INCAPACIDAD_TIPOS, $record->incapacidad_tipo) }}</td>
                <td colspan="2" style="padding:5pt 0 2pt"><span class="lbl">Clase de incapacidad:</span> {{ R::etiqueta(R::INCAPACIDAD_CLASES, $record->incapacidad_clase) }}</td>
            </tr>
            <tr>
                <td colspan="4" style="padding:5pt 4pt 2pt"><span class="lbl">Diagnóstico incapacidad:</span>{{ $record->incapacidad_diagnostico_cie10 }}</td>
            </tr>
        </table>

        <table style="margin-top:14pt" class="avoid">
            <tr>
                <td style="width:40%; padding-left:4pt">{{ $record->profesional_responsable_nombre }}</td>
                <td>{{ $record->profesional_responsable_especialidad }}</td>
            </tr>
        </table>
        <table class="avoid" style="margin-top:8pt">
            <tr><td style="width:9%; padding:2.5pt 4pt">Cédula:</td><td>{{ ltrim((string) $record->profesional_responsable_cedula, '0') }}</td></tr>
            <tr><td style="padding:2.5pt 4pt">RM:</td><td>{{ $record->profesional_responsable_rm }}</td></tr>
            @if($record->firma_electronica)
                <tr><td colspan="2" style="padding:2.5pt 4pt">Valido como Firma Electrónica</td></tr>
            @endif
        </table>
    @endif

    {{-- FÓRMULAS / ÓRDENES AMBULATORIAS --}}
    @if($tieneFor)
        <div class="band avoid" style="margin-top:16pt">FÓRMULAS Y ÓRDENES MÉDICAS AMBULATORIAS</div>
        <table class="grid">
            <tr class="spacer">
                <td style="width:14%"></td><td style="width:10%"></td><td style="width:36%"></td><td style="width:18%"></td><td style="width:22%"></td>
            </tr>
            <tr style="text-align:center">
                <td><b>Fecha / Hora</b></td><td><b>Código</b></td><td><b>Nombre / Prescripción médica</b></td><td><b>U. Organizativa</b></td><td><b>Médico responsable</b></td>
            </tr>
            @foreach($record->formulas as $f)
                <tr class="avoid">
                    <td>{{ $f->fecha_hora->format('d.m.Y H:i') }}</td>
                    <td style="text-align:center">{{ $f->codigo }}</td>
                    <td>
                        <b>{{ $f->nombre_prescripcion }}</b>
                        @if($f->instrucciones)<div style="font-size:7pt; margin-top:1.5pt">{{ $f->instrucciones }}</div>@endif
                    </td>
                    <td>{{ $f->unidad_organizativa }}</td>
                    <td style="text-align:center">{{ $f->medico_responsable }}</td>
                </tr>
            @endforeach
        </table>

        @unless($tieneInc)
            @if($record->firma_electronica)
                <div style="margin-top:10pt; padding-left:4pt">Valido como Firma Electrónica</div>
            @endif
        @endunless
    @endif
@endsection
