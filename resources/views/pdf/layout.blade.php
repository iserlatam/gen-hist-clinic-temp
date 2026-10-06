<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>@yield('title')</title>
    <style>
        @page { margin: 32pt 28pt 58pt 28pt; }
        body { font-family: Helvetica, Arial, sans-serif; font-size: 8pt; color: #000; line-height: 1.3; }
        table { width: 100%; border-collapse: collapse; }
        td, th { vertical-align: top; padding: 0; }

        /* Tablas con cuadrícula (datos generales, órdenes) */
        .grid td, .grid th { border: 0.6pt solid #000; padding: 2.5pt 4pt; vertical-align: middle; }
        .spacer td { border: none !important; padding: 0 !important; height: 0; font-size: 0; line-height: 0; }

        /* Bandas grises centradas (DATOS GENERALES, ATENCIÓN CLÍNICA, ...) */
        .band { background: #d9d9d9; border: 0.6pt solid #000; text-align: center; font-weight: bold; font-size: 8.5pt; padding: 2pt 0; margin-top: 7pt; }

        .lbl { font-weight: normal; }
        .val { font-weight: bold; }
        .sub { text-align: center; font-weight: bold; font-size: 8.5pt; padding: 3pt 0; }
        .rule { border-top: 0.6pt solid #000; height: 0; }
        .p { margin: 0 0 8pt 4pt; line-height: 1.4; }
        .avoid { page-break-inside: avoid; }

        /* Encabezado */
        .hdr td { vertical-align: middle; }
        .title { text-align: center; font-size: 13pt; font-weight: bold; }
        .addr { text-align: right; font-size: 6.5pt; line-height: 1.35; }

        /* Fijos en cada página */
        #pagenum { position: fixed; right: 0; top: -24pt; font-size: 7pt; font-weight: bold; font-style: italic; }
        .pn:before { content: "Pág " counter(page) " de " counter(pages); }
        #footer { position: fixed; left: 0; right: 0; bottom: -40pt; font-size: 7.5pt; }
    </style>
</head>
<body>
    <div id="pagenum"><span class="pn"></span></div>

    <div id="footer">
        <table>
            <tr>
                <td style="width:50%"><span class="lbl">Paciente:</span>&nbsp; {{ $record->paciente_nombre }}</td>
                <td style="width:24%"><span class="lbl">Impreso por:</span>&nbsp; {{ $impresoPor }}</td>
                <td style="width:14%"><span class="lbl">el</span>&nbsp; {{ $impresoEn->format('d.m.Y') }}</td>
                <td style="width:12%; text-align:right">{{ $impresoEn->format('H:i:s') }}</td>
            </tr>
        </table>
    </div>

    {{-- ENCABEZADO: espacio reservado para el logo (public/images/logo-institucion.png) --}}
    <table class="hdr">
        <tr>
            <td style="width:30%">
                @if(!empty($inst['logo']) && file_exists(public_path($inst['logo'])))
                    <img src="{{ public_path($inst['logo']) }}" style="height:46pt">
                @else
                    <div style="width:165pt; height:46pt"></div>
                @endif
            </td>
            <td class="title" style="width:40%">@yield('doc_title')</td>
            <td class="addr" style="width:30%">
                @foreach($inst['direccion'] as $linea){{ $linea }}<br>@endforeach
                Conmutador {{ $inst['conmutador'] }}<br>
                Fax {{ $inst['fax'] }}<br>
                Nit. {{ $inst['nit'] }}<br>
                {{ $inst['web'] }}<br>
                {{ $inst['ciudad'] }}
            </td>
        </tr>
    </table>

    {{-- DATOS GENERALES (igual en ambos documentos) --}}
    <div class="band">DATOS GENERALES</div>
    <table class="grid">
        <tr class="spacer">
            <td style="width:25%"></td><td style="width:14%"></td><td style="width:15%"></td><td style="width:15%"></td><td style="width:31%"></td>
        </tr>
        <tr>
            <td colspan="4"><span class="lbl">Paciente:</span>&nbsp; <span class="val">{{ $record->paciente_nombre }}</span></td>
            <td><span class="lbl">Doc.Identificación:</span>&nbsp; <span class="val">{{ $record->doc_identificacion }}</span></td>
        </tr>
        <tr>
            <td><span class="lbl">Fecha Nacimiento:</span>&nbsp; <span class="val">{{ $record->fecha_nacimiento?->format('d.m.Y') }}</span></td>
            <td colspan="2"><span class="lbl">Edad:</span>&nbsp;&nbsp; <span class="val">{{ $record->edad !== null ? $record->edad.' Años' : '' }}</span></td>
            <td><span class="lbl">Sexo:</span>&nbsp; <span class="val">{{ $record->sexo }}</span></td>
            <td><span class="lbl">Nº. Episodio:</span>&nbsp; <span class="val">{{ $record->numero_episodio }}</span></td>
        </tr>
        <tr>
            <td colspan="4"><span class="lbl">Aseguradora:</span>&nbsp; <span class="val">{{ $record->aseguradora_entidad }}</span></td>
            <td><span class="lbl">Nº. Historia Clínica:</span>&nbsp; <span class="val">{{ $record->numero_historia_clinica }}</span></td>
        </tr>
        <tr>
            <td colspan="2"><span class="lbl">Médico Tratante:</span>&nbsp; <span class="val">{{ $record->medico_tratante }}</span></td>
            <td colspan="2"><div style="width:70pt; font-size:6.5pt; font-weight:bold; line-height:1.2">{{ $record->especialidad_servicio }}</div></td>
            <td>&nbsp;</td>
        </tr>
    </table>

    @yield('content')
</body>
</html>
