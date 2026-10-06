@props([
    'label',
    'name',
    'type' => 'text',        // text | date | time | number | select | textarea
    'options' => [],         // [valor => etiqueta] para select
    'required' => false,
    'readonly' => false,
    'rows' => 3,
    'help' => null,
    'live' => false,         // true => wire:model.live | 'blur' => wire:model.live.blur
    'model' => null,         // por defecto "form.{name}"
    'placeholder' => null,
    'display' => false,      // campo de solo lectura calculado en el servidor (p. ej. edad)
    'value' => '',           // valor mostrado cuando display = true
    'mirror' => null,        // expresión Alpine: refleja otro campo al instante, sin request
])

@php
    $model ??= 'form.'.$name;
    $wire = match (true) {
        $live === 'blur' => 'wire:model.live.blur',
        (bool) $live => 'wire:model.live',
        default => 'wire:model',
    };
    $base = 'w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 '
          .'placeholder:text-slate-400 focus:border-cyan-700 focus:outline-none focus:ring-1 focus:ring-cyan-700 '
          .'disabled:bg-slate-50 read-only:bg-slate-50 read-only:text-slate-600';
@endphp

<div {{ $attributes->class('flex flex-col gap-1') }}>
    <label class="text-[13px] font-medium text-slate-700">
        {{ $label }} @if($required)<span class="text-red-600">*</span>@endif
    </label>

    @if($mirror)
        {{-- Espejo en el navegador: sin wire:model, nunca se envía al servidor --}}
        <input type="text" readonly tabindex="-1" x-data x-bind:value="{{ $mirror }}" class="{{ $base }}">
    @elseif($display)
        <input type="text" readonly tabindex="-1" value="{{ $value }}" wire:key="display-{{ $name }}" class="{{ $base }}">
    @elseif($type === 'select')
        <select {{ $wire }}="{{ $model }}" class="{{ $base }}">
            <option value="">— Seleccione —</option>
            @foreach($options as $val => $text)
                <option value="{{ $val }}">{{ $text }}</option>
            @endforeach
        </select>
    @elseif($type === 'textarea')
        <textarea {{ $wire }}="{{ $model }}" rows="{{ $rows }}" placeholder="{{ $placeholder }}" class="{{ $base }}"></textarea>
    @else
        <input type="{{ $type }}" {{ $wire }}="{{ $model }}" placeholder="{{ $placeholder }}"
               @if($type === 'time') step="1" @endif
               @if($readonly) readonly @endif
               class="{{ $base }}">
    @endif

    @if($help)<p class="text-[11px] text-slate-500">{{ $help }}</p>@endif
    @unless($mirror || $display)
        @error($model)<p class="text-xs text-red-600">{{ $message }}</p>@enderror
    @endunless
</div>
