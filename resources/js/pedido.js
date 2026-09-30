// Formulario de registro de pedido (Figura 39): líneas de productos, subtotales y total en vivo.
// El servidor recalcula todos los montos al guardar; aquí solo se muestran como referencia.

const formatoQuetzales = new Intl.NumberFormat('es-GT', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const quetzales = (valor) => `Q ${formatoQuetzales.format(valor)}`;

export function iniciarFormularioPedido(formulario) {
    const catalogo = JSON.parse(formulario.dataset.catalogo || '{}');
    const cuerpo = formulario.querySelector('[data-lineas]');
    const plantilla = formulario.querySelector('[data-plantilla-linea]');
    const total = formulario.querySelector('[data-total]');
    let siguienteIndice = cuerpo ? cuerpo.querySelectorAll('[data-linea]').length : 0;

    // Cliente registrado / cliente nuevo
    const alternarCliente = () => {
        const modo = formulario.querySelector('[data-modo-cliente]:checked')?.value ?? 'existente';
        formulario.querySelectorAll('[data-seccion-cliente]').forEach((seccion) => {
            const visible = seccion.dataset.seccionCliente === modo;
            seccion.classList.toggle('hidden', !visible);
            // Los campos ocultos no se envían, así no generan errores de validación.
            seccion.querySelectorAll('input, select').forEach((campo) => (campo.disabled = !visible));
        });
    };
    formulario.querySelectorAll('[data-modo-cliente]').forEach((radio) => radio.addEventListener('change', alternarCliente));
    alternarCliente();

    if (!cuerpo) return;

    const recalcular = () => {
        let suma = 0;
        cuerpo.querySelectorAll('[data-linea]').forEach((linea) => {
            const producto = catalogo[linea.querySelector('[data-producto]').value];
            const cantidad = Math.max(0, parseInt(linea.querySelector('[data-cantidad]').value, 10) || 0);
            const subtotal = producto ? Math.round(producto.precio * 100) * cantidad / 100 : 0;

            linea.querySelector('[data-precio]').textContent = producto ? quetzales(producto.precio) : '—';
            linea.querySelector('[data-subtotal]').textContent = producto ? quetzales(subtotal) : '—';
            suma += subtotal;
        });
        total.textContent = quetzales(suma);

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
        const boton = evento.target.closest('[data-quitar-linea]');
        if (boton && cuerpo.querySelectorAll('[data-linea]').length > 1) {
            boton.closest('[data-linea]').remove();
            recalcular();
        }
    });

    cuerpo.addEventListener('input', recalcular);
    cuerpo.addEventListener('change', recalcular);
    recalcular();
}
