import './bootstrap';
import { iniciarFormularioPedido } from './pedido';

// Menú lateral en pantallas pequeñas (RNF-05: diseño adaptable).
document.addEventListener('click', (evento) => {
    const boton = evento.target.closest('[data-menu-toggle]');
    const menu = document.getElementById('menu-lateral');
    const fondo = document.getElementById('menu-fondo');

    if (!menu) return;

    if (boton || evento.target === fondo) {
        const abierto = menu.classList.toggle('-translate-x-full') === false;
        fondo?.classList.toggle('hidden', !abierto);
        document.querySelectorAll('[data-menu-toggle]').forEach((b) => b.setAttribute('aria-expanded', String(abierto)));
    }
});

// Confirmación antes de acciones sensibles: <form data-confirmar="¿Seguro?">
// y bloqueo del doble envío para evitar registros duplicados: <form data-envio-unico>
document.addEventListener('submit', (evento) => {
    const formulario = evento.target;
    const mensaje = formulario.dataset.confirmar;

    if (mensaje && !window.confirm(mensaje)) {
        evento.preventDefault();
        return;
    }

    if ('envioUnico' in formulario.dataset) {
        if (formulario.dataset.enviando) {
            evento.preventDefault();
            return;
        }
        formulario.dataset.enviando = '1';
        formulario.querySelectorAll('button[type="submit"]').forEach((b) => {
            b.disabled = true;
            b.textContent = 'Guardando…';
        });
    }
});

document.querySelectorAll('[data-formulario-pedido]').forEach(iniciarFormularioPedido);

// Actualización de estado: cancelar exige motivo y confirmación.
document.querySelectorAll('[data-formulario-estado]').forEach((formulario) => {
    const estado = formulario.querySelector('[name="id_estado"]');
    const ayuda = formulario.querySelector('[data-observacion-ayuda]');
    const esCancelacion = () => estado.selectedOptions[0]?.textContent.trim() === 'Cancelado';

    const actualizar = () => {
        ayuda.textContent = esCancelacion() ? '(obligatoria: indique el motivo)' : '(opcional)';
        formulario.dataset.confirmar = esCancelacion() ? '¿Cancelar este pedido? Esta acción no se puede deshacer.' : '';
    };
    estado.addEventListener('change', actualizar);
    actualizar();
});
