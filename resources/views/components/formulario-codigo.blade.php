@props(['grande' => false])
{{-- Campo para ingresar el código de cliente que envía el negocio (RN-06). --}}
<form method="POST" action="{{ route('vincular') }}" novalidate data-envio-unico {{ $attributes }}>
    @csrf
    <label for="codigo" class="etiqueta">Código de cliente</label>
    <div @class(['flex gap-2', 'flex-col sm:flex-row' => $grande])>
        <input id="codigo" name="codigo" value="{{ old('codigo') }}" placeholder="XXXX-XXXX" autocomplete="off" maxlength="9"
               autocapitalize="characters" spellcheck="false" data-codigo
               @class([
                   'campo font-mono tracking-[0.2em] uppercase',
                   'min-h-12 text-lg sm:min-h-12 sm:text-lg' => $grande,
                   'campo-error' => $errors->has('codigo'),
               ])
               @error('codigo') aria-invalid="true" aria-describedby="codigo-error" @enderror>
        <button type="submit" @class(['btn btn-primario shrink-0', 'min-h-12 sm:min-h-12 sm:px-6' => $grande]) data-texto-envio="Buscando…">Ver mis pedidos</button>
    </div>
    @error('codigo')<p id="codigo-error" class="error-campo">{{ $message }}</p>@enderror
</form>
