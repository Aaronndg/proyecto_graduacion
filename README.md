# Sistema web para administración y seguimiento automatizado del proceso de pedidos

Proyecto de Graduación — Universidad Mariano Gálvez de Guatemala, sede Jutiapa.
Autor: Aarón Emilio Díaz González (0905-22-9382).

Aplicación web en **Laravel 12 + PHP 8.2 + MySQL (MariaDB de XAMPP)** con Blade y Tailwind CSS,
organizada con el patrón **MVC** (capítulo 5.2.2).

## Requisitos

- XAMPP (PHP 8.2 y MySQL) con las extensiones `intl`, `zip`, `gd` y `pdo_mysql` activas en `C:\xampp\php\php.ini`
- Composer 2
- Node.js 20 o superior

## Puesta en marcha (primera vez)

```powershell
composer install
npm install
copy .env.example .env      # solo si no existe .env
php artisan key:generate
# Encender MySQL desde el panel de XAMPP y crear la base de datos:
#   CREATE DATABASE pedidos_jutiapa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
php artisan migrate --seed
npm run build
```

## Uso diario

1. Encender **MySQL** desde el panel de control de XAMPP (Apache no es necesario).
2. En la carpeta del proyecto: `php artisan serve`
3. Abrir http://127.0.0.1:8000

Durante el desarrollo de estilos, `npm run dev` en otra terminal recarga los cambios automáticamente.

## Cuentas de demostración

| Rol           | Correo                    | Contraseña  |
|---------------|---------------------------|-------------|
| Administrador | admin@pedidos.test        | Password123 |
| Emprendedor   | emprendedor@pedidos.test  | Password123 |
| Cliente       | cliente@pedidos.test      | Password123 |

> Cambie estas contraseñas antes de publicar el sistema en producción.

## Pruebas automatizadas

Usan la base de datos `pedidos_jutiapa_test` (debe existir):

```powershell
php artisan test
```

## Avance por Sprint (4.7.8)

| Sprint | Contenido | Estado |
|--------|-----------|--------|
| 1 | Autenticación, registro, roles, gestión de usuarios, panel principal, perfil, esquema completo de base de datos | ✅ Completado |
| 2 | Gestión de clientes, productos y pedidos (con registro rápido de cliente y datos de demostración) | ✅ Completado |
| 3 | Estados (avance y cancelación con motivo), tablero de seguimiento, barra de progreso, historial y vista del cliente | ✅ Completado |
| 4 | Ventas y reportes (resumen, ventas por día/mes, pedidos por estado, productos más vendidos, detalle), exportación a Excel (CSV), impresión/PDF, validación de filtros y pruebas | 🔄 En curso |

## Ajustes al modelo de datos respecto al documento (5.4.3)

Se agregaron campos necesarios para cumplir las reglas de negocio:

- `usuarios.negocio` y `usuarios.activo`: nombre del emprendimiento y desactivación de cuentas (RF-04).
- `clientes.id_emprendedor`, `productos.id_emprendedor`, `pedidos.id_emprendedor`: cada emprendedor
  tiene su propio espacio de datos (RN-10).
- `clientes.id_usuario`: vincula el registro del cliente con su cuenta para que consulte sus pedidos (RN-06).
  La vinculación se hace con un código (ver abajo), no por correo.
- `clientes.codigo_vinculacion`: código de uso único que el emprendedor entrega al cliente.
- `historial_estado.id_usuario`: registra quién realizó cada cambio de estado (Figura 40).
- `estados_pedido.orden`: orden de los estados Nuevo → En proceso → Listo → Entregado (y Cancelado).
- `created_at` / `updated_at` en usuarios, clientes, productos y pedidos (auditoría).

## Vinculación de clientes con código

Para que un cliente vea sus pedidos, su cuenta debe estar vinculada con el registro que creó el emprendedor.
No se vincula por correo, porque el sistema no puede comprobar que el correo pertenezca a quien se registra.

1. Cada cliente registrado recibe un código de 8 caracteres (p. ej. `K7QM-4XPA`), visible en su ficha.
2. El emprendedor se lo envía con el botón **Enviar por WhatsApp**, que incluye un enlace al registro con el código ya escrito.
3. El cliente ingresa el código al crear su cuenta o después, en **Mis pedidos**.
4. El código es de uso único; tras 5 intentos incorrectos se bloquea la vinculación por 10 minutos.
5. El emprendedor puede desvincular una cuenta y generar un código nuevo.

## Reportes y ventas (Sprint 4)

Menú **Reportes** (solo emprendedor, Figura 41):

- **Venta** = pedido en estado *Entregado*. Se ubica en el período por la fecha del pedido.
- Sin fechas, muestra los últimos 30 días; hay accesos rápidos (7 días, 30 días, este mes, mes anterior). Período máximo: un año.
- Resumen: pedidos del período, ventas, en curso, cancelados, monto vendido y venta promedio.
- Ventas por día (o por mes si el período supera 62 días), incluyendo los días sin ventas; también se puede ver como tabla.
- Pedidos por estado, productos más vendidos y detalle de pedidos (el filtro de estado aplica al detalle).
- **Descargar Excel (CSV)** con el detalle del período, y **Imprimir** (o guardar como PDF desde el navegador).

## Datos de demostración

En el entorno local, `php artisan migrate:fresh --seed` carga un catálogo de 8 productos, 6 clientes y 13 pedidos
en distintos estados para la cuenta del emprendedor. El cliente demo (cliente@pedidos.test) queda vinculado a sus pedidos.
