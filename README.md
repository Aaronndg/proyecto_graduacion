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
| 2 | Gestión de clientes, productos y pedidos | Pendiente |
| 3 | Estados, seguimiento e historial de pedidos | Pendiente |
| 4 | Ventas, reportes, validaciones generales y pruebas | Pendiente |

## Ajustes al modelo de datos respecto al documento (5.4.3)

Se agregaron campos necesarios para cumplir las reglas de negocio:

- `usuarios.negocio` y `usuarios.activo`: nombre del emprendimiento y desactivación de cuentas (RF-04).
- `clientes.id_emprendedor`, `productos.id_emprendedor`, `pedidos.id_emprendedor`: cada emprendedor
  tiene su propio espacio de datos (RN-10).
- `clientes.id_usuario`: vincula el registro del cliente con su cuenta para que consulte sus pedidos (RN-06).
  La vinculación es automática por correo electrónico.
- `historial_estado.id_usuario`: registra quién realizó cada cambio de estado (Figura 40).
- `estados_pedido.orden`: orden de los estados Nuevo → En proceso → Listo → Entregado (y Cancelado).
- `created_at` / `updated_at` en usuarios, clientes, productos y pedidos (auditoría).
