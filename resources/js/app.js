import './bootstrap';
import { iniciarFormularioPedido } from './pedido';

/**
 * Confirmación con el diálogo del Design System (reemplaza window.confirm).
 * Explica la consecuencia y nombra el botón por la acción. Devuelve una promesa con true/false.
 */
function confirmar({ titulo = '¿Está seguro?', texto = '', accion = 'Confirmar', peligro = false } = {}) {
    const dialogo = document.getElementById('dialogo-confirmar');
    if (!dialogo?.showModal) return Promise.resolve(window.confirm(texto || titulo));

    dialogo.querySelector('#dialogo-titulo').textContent = titulo;
    dialogo.querySelector('#dialogo-texto').textContent = texto;
    const aceptar = dialogo.querySelector('[data-dialogo-aceptar]');
    aceptar.textContent = accion;
    aceptar.className = `btn ${peligro ? 'btn-peligro' : 'btn-primario'}`;

    return new Promise((resolver) => {
        dialogo.addEventListener('close', () => resolver(dialogo.returnValue === 'si'), { once: true });
        dialogo.returnValue = '';
        dialogo.showModal();
        // El foco empieza en «Volver»: la opción segura.
        dialogo.querySelector('button[value="no"]').focus();
    });
}

// Formularios que piden confirmación: <form data-confirmar="Consecuencia" data-confirmar-titulo="…"
// data-confirmar-accion="Eliminar cliente" data-confirmar-peligro>
// y bloqueo del doble envío para evitar registros duplicados: <form data-envio-unico>
document.addEventListener('submit', async (evento) => {
    const formulario = evento.target;

    if (formulario.dataset.confirmar !== undefined && !formulario.dataset.confirmado) {
        evento.preventDefault();
        const ok = await confirmar({
            titulo: formulario.dataset.confirmarTitulo,
            texto: formulario.dataset.confirmar,
            accion: formulario.dataset.confirmarAccion,
            peligro: 'confirmarPeligro' in formulario.dataset,
        });
        if (ok) {
            formulario.dataset.confirmado = '1';
            formulario.requestSubmit(evento.submitter ?? undefined);
        }
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
            if (evento.submitter) evento.submitter.textContent = evento.submitter.dataset.textoEnvio ?? 'Guardando…';
        }, 0);
    }
});

document.querySelectorAll('[data-formulario-pedido]').forEach(iniciarFormularioPedido);

// Menús «⋯» y «Más» (<details data-menu>): uno abierto a la vez; se cierran al hacer clic fuera o con Escape.
document.addEventListener('click', (evento) => {
    document.querySelectorAll('details[data-menu][open]').forEach((menu) => {
        if (!menu.contains(evento.target)) menu.open = false;
    });
});
document.addEventListener('keydown', (evento) => {
    if (evento.key !== 'Escape') return;
    document.querySelectorAll('details[data-menu][open]').forEach((menu) => {
        menu.open = false;
        menu.querySelector('summary')?.focus();
    });
});

// «Hoy» en el teléfono: una etapa a la vez (en escritorio se ven las tres).
document.querySelectorAll('[data-pestanas]').forEach((grupo) => {
    grupo.addEventListener('click', (evento) => {
        const boton = evento.target.closest('[data-pestana]');
        if (!boton) return;
        grupo.querySelectorAll('[data-pestana]').forEach((b) => b.setAttribute('aria-pressed', String(b === boton)));
        document.querySelectorAll('[data-etapa]').forEach((etapa) => {
            const visible = etapa.dataset.etapa === boton.dataset.pestana;
            etapa.classList.toggle('flex', visible);
            etapa.classList.toggle('hidden', !visible);
            etapa.classList.toggle('lg:flex', !visible);
        });
    });
});

// Filas de pestañas desplazables (teléfono): la pestaña actual siempre queda a la vista.
document.querySelectorAll('[data-desplazable]').forEach((fila) => {
    const actual = fila.querySelector('[aria-current="page"]');
    if (actual) fila.scrollLeft = actual.offsetLeft - fila.clientWidth / 2 + actual.offsetWidth / 2;
});

// Avisos de éxito: se retiran solos a los 5 segundos o con el botón cerrar.
document.querySelectorAll('[data-aviso]').forEach((aviso) => {
    const cerrar = () => aviso.parentElement?.remove();
    aviso.querySelector('[data-cerrar-aviso]')?.addEventListener('click', cerrar);
    setTimeout(cerrar, 5000);
});

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

// Diálogos propios de una pantalla (p. ej. cancelar pedido con su motivo).
// <button data-abrir-dialogo="id">, <button data-cerrar-dialogo> y <dialog data-abrir> para abrirlo al cargar
// (cuando el servidor devolvió un error en ese formulario).
document.addEventListener('click', (evento) => {
    const abrir = evento.target.closest('[data-abrir-dialogo]');
    if (abrir) {
        abrir.closest('details[data-menu]')?.removeAttribute('open');
        const dialogo = document.getElementById(abrir.dataset.abrirDialogo);
        dialogo?.showModal();
        dialogo?.querySelector('textarea, input:not([type="hidden"])')?.focus();
    }
    evento.target.closest('[data-cerrar-dialogo]')?.closest('dialog')?.close();
});
document.querySelectorAll('dialog[data-abrir]').forEach((dialogo) => dialogo.showModal());

// Reportes: imprimir o guardar como PDF desde el navegador.
document.addEventListener('click', (evento) => {
    if (evento.target.closest('[data-imprimir]')) window.print();
});
