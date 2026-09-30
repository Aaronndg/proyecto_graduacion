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
        // Se deshabilitan después de iniciar el envío para que viaje el valor del botón pulsado.
        setTimeout(() => {
            formulario.querySelectorAll('button[type="submit"]').forEach((b) => (b.disabled = true));
            if (evento.submitter) evento.submitter.textContent = 'Guardando…';
        }, 0);
    }
});

document.querySelectorAll('[data-formulario-pedido]').forEach(iniciarFormularioPedido);

// Código de cliente: mayúsculas, solo caracteres válidos y guion automático (XXXX-XXXX).
document.addEventListener('input', (evento) => {
    const campo = evento.target.closest('[data-codigo]');
    if (!campo) return;

    const limpio = campo.value.toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 8);
    campo.value = limpio.length > 4 ? `${limpio.slice(0, 4)}-${limpio.slice(4)}` : limpio;
});

// Botón "Copiar" (código de cliente).
document.addEventListener('click', async (evento) => {
    const boton = evento.target.closest('[data-copiar]');
    if (!boton) return;

    try {
        await navigator.clipboard.writeText(boton.dataset.copiar);
        const texto = boton.textContent;
        boton.textContent = '¡Copiado!';
        setTimeout(() => (boton.textContent = texto), 1800);
    } catch {
        window.prompt('Copie el código:', boton.dataset.copiar);
    }
});

// Actualización de estado: cancelar exige escribir el motivo y confirmar.
document.addEventListener('click', (evento) => {
    const boton = evento.target.closest('[data-cancelar]');
    if (!boton) return;

    const nota = boton.form.querySelector('[name="observacion"]');
    if (!nota.value.trim()) {
        evento.preventDefault();
        nota.placeholder = 'Escriba aquí el motivo de la cancelación';
        nota.classList.add('campo-error');
        nota.focus();
        return;
    }

    if (!window.confirm('¿Cancelar este pedido? Esta acción no se puede deshacer.')) {
        evento.preventDefault();
    }
});
