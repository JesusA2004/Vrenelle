# Vrenelle — Boutique digital de joyería de lujo

Plataforma de e-commerce completa construida sobre Laravel 13 + Inertia.js v3 + Vue 3
(Composition API, TypeScript) + Tailwind CSS v4 + shadcn-vue, con panel administrativo,
inventario, promociones/cupones, pasarelas de pago y reportes.

## Stack

- **Backend:** Laravel 13, PHP 8.4, MySQL 8, spatie/laravel-permission, Laravel Fortify.
- **Frontend:** Inertia.js v3, Vue 3 (`<script setup>`), TypeScript, Tailwind CSS v4,
  shadcn-vue (estilo `new-york-v4`), Reka UI, SweetAlert2, Vite, Laravel Wayfinder.
- **Idioma:** toda la interfaz (tienda, cuenta, panel admin, correos, validaciones) en
  español (`APP_LOCALE=es`).

## 1. Requisitos

- PHP 8.3+ (probado en 8.4) con extensiones habituales de Laravel (`pdo_mysql`, `mbstring`,
  `intl`, `gd` o `imagick` para imágenes, `fileinfo`).
- Composer 2.
- Node.js 20+ y npm.
- MySQL 8 (o MariaDB 10.6+). **Importante:** el servidor debe usar **InnoDB** como motor de
  almacenamiento. Si tu servidor MySQL tiene `default_storage_engine=MyISAM` (algunos XAMPP/WAMP
  antiguos), no necesitas cambiar la configuración del servidor: `config/database.php` ya fuerza
  `'engine' => 'InnoDB'` en la conexión `mysql`.

## 2. Instalación

```bash
composer install
npm install

cp .env.example .env
php artisan key:generate
```

Edita `.env` con tus credenciales de base de datos (`DB_DATABASE=vrenelle`, `DB_USERNAME`,
`DB_PASSWORD`) y crea la base de datos vacía en MySQL:

```sql
CREATE DATABASE vrenelle CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Migraciones y datos de demostración

```bash
php artisan migrate
php artisan db:seed
```

El seeder crea:

- Roles y permisos (`RolesAndPermissionsSeeder`).
- Configuración inicial de la tienda (`SettingsSeeder`) — **debes revisar y actualizar**
  el número de WhatsApp, redes sociales y datos de contacto desde `/admin/configuracion`.
- Métodos de envío de ejemplo.
- Páginas legales (placeholders — **deben sustituirse por texto real antes de producción**).
- Catálogo de demostración: 6 categorías, 3 colecciones, 5 materiales, 5 piedras y 17
  productos con variantes, imágenes de marcador de posición (SVG generados localmente) y
  precios realistas en MXN.
- Un cupón (`BIENVENIDA10`) y una promoción de envío gratis, ambos de ejemplo.
- Reglas de MSI (meses sin intereses) precargadas **inactivas** — actívalas solo cuando
  tengas credenciales reales y el plan esté soportado por tu cuenta con el proveedor.

### Storage y build

```bash
php artisan storage:link
npm run build      # producción
# o
npm run dev        # desarrollo con hot-reload
```

### Colas y tareas programadas

Los correos (pedido recibido, pago confirmado, envío, etc.) se envían mediante `Notifications`
en cola. En desarrollo:

```bash
php artisan queue:listen --tries=1
```

En producción usa Supervisor (o equivalente) para mantener `php artisan queue:work` corriendo,
y agrega el scheduler de Laravel al cron del servidor:

```
* * * * * cd /ruta/al/proyecto && php artisan schedule:run >> /dev/null 2>&1
```

### Todo en un comando (desarrollo)

```bash
composer dev
```

Levanta `php artisan serve`, `queue:listen` y `npm run dev` en paralelo.

## 3. Cuentas de acceso (solo desarrollo)

El seeder crea las siguientes cuentas **exclusivamente para desarrollo**. Cambia sus
contraseñas o elimínalas antes de pasar a producción.

| Rol | Correo | Contraseña |
|---|---|---|
| Super Admin | `admin@vrenelle.mx` | `Vrenelle#2026` |
| Ventas | `ventas@vrenelle.mx` | `Vrenelle#2026` |
| Cliente demo | `cliente@vrenelle.mx` | `Vrenelle#2026` |

Panel administrativo: `/admin`. Cuenta del cliente: `/mi-cuenta`.

## 4. Roles y permisos

Implementados con `spatie/laravel-permission`. Roles creados por el seeder:

- **super_admin** — todos los permisos, único rol que puede asignar el rol `super_admin` a
  otros usuarios (validado en backend, no solo oculto en la interfaz).
- **administrador** — todos los permisos excepto `settings.manage` y `roles.manage`.
- **ventas** — pedidos, clientes, reportes.
- **inventario** — productos, inventario, materiales.
- **marketing** — colecciones, categorías, banners, promociones, cupones, newsletter.
- **soporte** — clientes, pedidos (solo lectura), productos.

Los permisos siguen la convención `entidad.accion` (`products.view`, `orders.cancel`,
`settings.manage`, etc.) y se validan en cada método de controlador — nunca solo ocultando
botones en el frontend.

## 5. Pagos

La arquitectura de pagos usa el patrón *Strategy* (`PaymentGatewayInterface`) con tres
implementaciones que llaman directamente a la API REST de cada proveedor (sin SDK):
`StripePaymentGateway`, `PayPalPaymentGateway`, `MercadoPagoPaymentGateway`.

**Ninguna pasarela funciona hasta que configures sus credenciales reales en `.env`.**
Mientras tanto, el checkout sigue siendo 100% funcional mediante la opción **"Transferencia
/ pago manual"**, que crea el pedido con estado `pago pendiente` para confirmación manual
desde el panel.

### Stripe

1. Obtén tus llaves en <https://dashboard.stripe.com/apikeys>.
2. En `.env`: `STRIPE_KEY`, `STRIPE_SECRET`.
3. Registra un webhook en <https://dashboard.stripe.com/webhooks> apuntando a
   `https://tu-dominio.com/webhooks/stripe`, eventos `checkout.session.completed` y
   `checkout.session.async_payment_failed` / `payment_intent.payment_failed`.
4. Copia el "Signing secret" del webhook a `STRIPE_WEBHOOK_SECRET`.

### PayPal

1. Crea una app en <https://developer.paypal.com/dashboard/applications>.
2. En `.env`: `PAYPAL_CLIENT_ID`, `PAYPAL_CLIENT_SECRET`, `PAYPAL_MODE=sandbox|live`.
3. Registra un webhook apuntando a `https://tu-dominio.com/webhooks/paypal`, eventos
   `PAYMENT.CAPTURE.COMPLETED` y `PAYMENT.CAPTURE.DENIED`.
4. Copia el Webhook ID a `PAYPAL_WEBHOOK_ID` (necesario para verificar la firma).

### Mercado Pago

1. Obtén tu access token en <https://www.mercadopago.com.mx/developers/panel/app>.
2. En `.env`: `MERCADOPAGO_ACCESS_TOKEN`, `MERCADOPAGO_PUBLIC_KEY`.
3. Configura la URL de notificaciones (webhook) a `https://tu-dominio.com/webhooks/mercadopago`
   para el evento `payment`.
4. Si tu cuenta expone un secreto de firma de webhook, colócalo en
   `MERCADOPAGO_WEBHOOK_SECRET` (recomendado); si no está disponible, el sistema siempre
   revalida el pago consultando la API de pagos de Mercado Pago directamente — nunca confía
   en el cuerpo del webhook por sí solo.

### Meses sin intereses (MSI)

Se administran desde `/admin/msi`. Cada regla define el proveedor,
número de meses, montos mínimo/máximo y el porcentaje de comisión que absorbe la tienda vs.
el cliente. **El sistema nunca ofrece un plazo que no esté explícitamente activado aquí.**

### Idempotencia y seguridad

- El checkout usa una `idempotency_key` para evitar pedidos duplicados por doble clic o
  reintento de red.
- Los webhooks verifican firma criptográfica antes de procesar cualquier evento.
- Cada transacción se guarda en `payment_transactions` con una restricción única
  `(provider, provider_transaction_id)` — un webhook reenviado nunca duplica el efecto.
- Una orden **nunca** se marca como pagada solo porque el navegador regresó a la página de
  confirmación; solo `PaymentService::handleWebhook()` puede hacerlo.

## 6. WhatsApp

El número se configura en `/admin/configuracion` (tabla `settings`, clave
`whatsapp_number`). Todos los enlaces `wa.me` se generan en el backend
(`App\Support\WhatsAppHelper`) — nunca hay números hardcodeados en el frontend.

## 7. Inventario

- El stock se descuenta únicamente dentro de `CheckoutService::placeOrder()`, con
  `lockForUpdate()` por fila y dentro de una transacción — dos compradores no pueden llevarse
  la misma última pieza.
- Cada movimiento (venta, ajuste, devolución, cancelación) se registra en
  `inventory_movements` con el usuario responsable.
- El stock exacto **nunca** se expone al público. Cuando un cliente intenta seleccionar más
  piezas de las disponibles, ve un mensaje elegante con la opción de solicitar más piezas
  por WhatsApp.

## 8. SEO

- `robots.txt` bloquea `/admin`, `/checkout`, `/cuenta`, `/mi-cuenta`, `/carrito`.
- `/sitemap.xml` se genera dinámicamente con productos, colecciones y páginas legales activas.
- Cada producto/colección expone `meta_title`/`meta_description` editables desde el panel.

## 9. Pruebas

```bash
php artisan test
```

Incluye pruebas de: prevención de sobreventa de inventario, flujo completo de checkout
(incluida idempotencia y rechazo por stock insuficiente), validación de cupones (vencidos,
mínimo de compra, límite de uso, primera compra), permisos de administración (incluida la
protección contra auto-escalado al rol `super_admin`) y transacciones de pago.

## 10. Comandos útiles

```bash
composer lint          # Pint (estilo de código PHP)
composer types:check   # Larastan / PHPStan
npm run lint           # ESLint
npm run format         # Prettier
npm run types:check    # vue-tsc
```

## 11. Despliegue a producción

1. `composer install --no-dev --optimize-autoloader`
2. `npm ci && npm run build`
3. Configurar `.env` de producción: `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL`
   correcta, credenciales reales de base de datos y de las pasarelas de pago que se vayan a
   habilitar.
4. `php artisan migrate --force`
5. `php artisan config:cache && php artisan route:cache && php artisan view:cache`
6. `php artisan storage:link`
7. Configurar Supervisor para `php artisan queue:work` y el cron para `schedule:run`.
8. Registrar los webhooks de las pasarelas de pago habilitadas (ver sección 5).
9. Cambiar las contraseñas de las cuentas de demostración o eliminarlas.
10. Sustituir el contenido de las páginas legales desde `/admin/paginas-legales`.

## 12. Pendientes que requieren información o credenciales reales del negocio

Ver el resumen técnico entregado al finalizar la sesión de desarrollo para el detalle
completo. En resumen:

- Credenciales reales de Stripe / PayPal / Mercado Pago (arquitectura lista, sin ellas el
  checkout usa el flujo de pago manual).
- Número de WhatsApp, redes sociales, dirección y datos fiscales reales de la tienda.
- Fotografía real de producto (el catálogo de demostración usa marcadores de posición SVG).
- Texto legal definitivo (términos, privacidad, garantía, etc.) revisado por un abogado.
- Configuración fiscal real (tasa de IVA, si los precios ya la incluyen) junto con un contador.
