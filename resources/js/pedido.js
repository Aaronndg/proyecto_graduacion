// Formulario de registro de pedido (Figura 39): líneas de productos, subtotales, total y resumen en vivo.
// El servidor recalcula todos los montos al guardar; aquí solo se muestran como referencia.

const formatoQuetzales = new Intl.NumberFormat('es-GT', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const quetzales = (valor) => `Q ${formatoQuetzales.format(valor)}`;

export function iniciarFormularioPedido(formulario) {
    const catalogo = JSON.parse(formulario.dataset.catalogo || '{}');
    const cuerpo = formulario.querySelector('[data-lineas]');
    const plantilla = formulario.querySelector('[data-plantilla-linea]');
    const total = formulario.querySelector('[data-total]');
    const resumenCliente = formulario.querySelector('[data-resumen-cliente]');
    const resumenProductos = formulario.querySelector('[data-resumen-productos]');
    let siguienteIndice = cuerpo ? cuerpo.querySelectorAll('[data-linea]').length : 0;

    const modoCliente = () => formulario.querySelector('[data-modo-cliente]:checked')?.value ?? 'existente';

    // Resumen: nombre del cliente elegido o escrito.
    const mostrarCliente = () => {
        if (!resumenCliente) return;
        let nombre = '';
        if (modoCliente() === 'nuevo') {
            nombre = formulario.querySelector('[data-nombre-nuevo]')?.value.trim() ?? '';
        } else {
            const select = formulario.querySelector('[data-cliente]');
            // El texto de la opción incluye « · teléfono»: en el resumen basta el nombre.
            nombre = select?.value ? select.selectedOptions[0].textContent.split(' · ')[0].trim() : '';
        }
        resumenCliente.textContent = nombre || '—';
    };

    // Cliente registrado / cliente nuevo
    const alternarCliente = () => {
        const modo = modoCliente();
        formulario.querySelectorAll('[data-seccion-cliente]').forEach((seccion) => {
            const visible = seccion.dataset.seccionCliente === modo;
            seccion.classList.toggle('hidden', !visible);
            // Los campos ocultos no se envían, así no generan errores de validación.
            seccion.querySelectorAll('input, select').forEach((campo) => (campo.disabled = !visible));
        });
        mostrarCliente();
    };
    formulario.querySelectorAll('[data-modo-cliente]').forEach((radio) => radio.addEventListener('change', alternarCliente));
    formulario.querySelector('[data-cliente]')?.addEventListener('change', mostrarCliente);
    formulario.querySelector('[data-nombre-nuevo]')?.addEventListener('input', mostrarCliente);
    alternarCliente();

    if (!cuerpo) return;

    const recalcular = () => {
        let suma = 0;
        let unidades = 0;
        cuerpo.querySelectorAll('[data-linea]').forEach((linea) => {
            const producto = catalogo[linea.querySelector('[data-producto]').value];
            const cantidad = Math.max(0, parseInt(linea.querySelector('[data-cantidad]').value, 10) || 0);
            const subtotal = producto ? Math.round(producto.precio * 100) * cantidad / 100 : 0;

            linea.querySelector('[data-precio]').textContent = producto ? quetzales(producto.precio) : '—';
            linea.querySelector('[data-subtotal]').textContent = producto ? quetzales(subtotal) : '—';
            suma += subtotal;
            if (producto) unidades += cantidad;
        });
        total.textContent = quetzales(suma);
        if (resumenProductos) {
            resumenProductos.textContent = unidades ? `${unidades} ${unidades === 1 ? 'producto' : 'productos'}` : '—';
        }

        const unica = cuerpo.querySelectorAll('[data-linea]').length === 1;
        cuerpo.querySelectorAll('[data-quitar-linea]').forEach((b) => (b.hidden = unica));
    };

    formulario.querySelector('[data-agregar-linea]')?.addEventListener('click', () => {
        const html = plantilla.innerHTML.replaceAll('__INDICE__', String(siguienteIndice++));
        cuerpo.insertAdjacentHTML('beforeend', html);
        cuerpo.lastElementChild.querySelector('[data-producto]').focus();
        recalcular();
    });

    cuerpo.addEventListener('click', (evento) => {
        const quitar = evento.target.closest('[data-quitar-linea]');
        if (quitar && cuerpo.querySelectorAll('[data-linea]').length > 1) {
            quitar.closest('[data-linea]').remove();
            recalcular();
            return;
        }

        // Botones − / + de la cantidad (entre 1 y 9999, igual que la validación).
        const paso = evento.target.closest('[data-restar], [data-sumar]');
        if (paso) {
            const campo = paso.closest('[data-linea]').querySelector('[data-cantidad]');
            const actual = parseInt(campo.value, 10) || 0;
            campo.value = Math.min(9999, Math.max(1, actual + ('sumar' in paso.dataset ? 1 : -1)));
            recalcular();
        }
    });

    cuerpo.addEventListener('input', recalcular);
    cuerpo.addEventListener('change', recalcular);
    recalcular();
}
