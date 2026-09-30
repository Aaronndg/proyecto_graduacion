@props(['grande' => false])
{{-- Campo para ingresar el código de cliente que envía el negocio (RN-06). --}}
<form method="POST" action="{{ route('vincular') }}" novalidate data-envio-unico {{ $attributes }}>
    @csrf
    <label for="codigo" @class(['etiqueta', 'sr-only' => $grande])>Código de cliente</label>
    <div @class(['flex gap-2', 'flex-col sm:flex-row' => $grande])>
        <input id="codigo" name="codigo" value="{{ old('codigo') }}" placeholder="XXXX-XXXX" autocomplete="off" maxlength="9"
               autocapitalize="characters" spellcheck="false" data-codigo
               @class([
                   'campo font-mono tracking-[0.2em] uppercase',
                   'py-3.5 text-center text-xl sm:text-left' => $grande,
                   'campo-error' => $errors->has('codigo'),
               ])
               @error('codigo') aria-invalid="true" aria-describedby="codigo-error" @enderror>
        <button type="submit" @class(['btn btn-primario shrink-0', 'py-3.5 text-base sm:px-6' => $grande])>Ver mis pedidos</button>
    </div>
    @error('codigo')<p id="codigo-error" class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
</form>
