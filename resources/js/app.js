import './bootstrap';

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
document.addEventListener('submit', (evento) => {
    const mensaje = evento.target.dataset.confirmar;
    if (mensaje && !window.confirm(mensaje)) {
        evento.preventDefault();
    }
});
