# Sistema de Licenciamiento Esencial Costa Rica

## 🚀 Configuración Inicial

### Requisitos Previos
El proyecto está desarrollado con:
- Laravel
- Inertia.js
- React.js

### Pasos de Instalación

1. **Instalar Dependencias**
   ```bash
   npm install
   composer install
   ```

2. **Configuración del Entorno**
   - Crear archivo `.env`
   - Copiar contenido de `.env.example` a `.env`
   - Configurar variables de entorno según tu entorno local
   - Generar key:
     ```bash
     php artisan key:generate
     ```

3. **Configuración de Base de Datos**
   - Crear base de datos MySQL
   - Importar archivo `db_limpia.sql`
   - Ejecutar migraciones:
     ```bash
     php artisan migrate
     ```

4. **Configuración Adicional**
   ```bash
   # Limpiar caché
   php artisan optimize:clear

   # Crear enlace simbólico de storage
   php artisan storage:link
   ```

5. **Archivos Requeridos**
   Copiar en `storage/app/public`:
   - `lugares.json`
   - `paises.json`

## 🏃‍♂️ Ejecutar el Proyecto

1. **Iniciar Servidor Laravel**
   ```bash
   php artisan serve
   ```

2. **Iniciar Servidor de Desarrollo**
   ```bash
   npm run dev
   ```

3. Acceder a través del localhost

## 🔑 Acceso al Sistema

### Usuario Super Admin
- **Email**: admin@admin.com
- **Contraseña**: password

Este usuario tiene acceso de super administrador.

## 📦 Despliegue en Producción

1. **Generar Build**
   ```bash
   npm run build
   ```
   > Nota: Los archivos de vistas se generarán en la carpeta `public`

2. **Gestión de Caché**
   ```bash
   # Limpiar caché
   php artisan optimize:clear

   # Setear caché
   php artisan optimize
   ```

## 👥 Roles del Sistema

| Rol | Descripción |
|-----|-------------|
| **super_admin** | - Control de elementos en procesos de licenciamiento<br>- Acceso a panel de administración de todas las empresas |
| **admin** | Gestión de procesos de licenciamiento, autoevaluación y evaluación |
| **user** | Participación en procesos de licenciamiento, autoevaluación y evaluación |
| **evaluador** | Evaluación de procesos una vez finalizados por la empresa |

### Nota sobre Roles
Las funcionalidades pueden variar según el rol del usuario. Verificar en el código las condiciones específicas para roles de evaluador, admin o super_admin.

### Rutas
Las rutas y sus correspondientes vistas/acciones se encuentran definidas en `routes/web.php`:
- GET: Renderización de vistas
- POST/PUT/DELETE: Ejecución de acciones

## 🛣️ Documentación de Rutas y Controladores

### 🖥️ Vistas Principales

#### Dashboard
- **Ruta**: `/dashboard`
- **Controlador**: `DashboardController@showEvaluation`
- **Middleware**: `auth, verified, EnsureUserHasCompany`
- **Acciones**:
  - GET: Muestra el dashboard principal

#### Autenticación de Empresa
- **Ruta Base**: `/company-register`
- **Controlador**: `CompanyAuthController`
- **Middleware**: `auth`
- **Acciones**:
  - GET `/regard`: Muestra página de consideración
  - GET `/company-register`: Formulario de registro de empresa
  - POST `/company-register`: Almacena nueva empresa
  - GET `/legal-id`: Muestra formulario de ID legal
  - POST `/legal-id/verify`: Verifica ID legal
  - GET `/company-exists`: Muestra página de empresa existente
  - POST `/company-request-access`: Solicita acceso a empresa

#### Perfil de Usuario
- **Ruta Base**: `/profile`
- **Controlador**: `ProfileController`
- **Middleware**: `auth`
- **Acciones**:
  - GET: Muestra formulario de edición
  - PATCH: Actualiza perfil
  - DELETE: Elimina perfil

### 👑 Panel Super Admin

#### Dashboard Super Admin
- **Ruta**: `/super/dashboard`
- **Controlador**: `SuperAdminController`
- **Middleware**: `auth, EnsureUserIsSuperAdmin`
- **Acciones API**:
  - GET `/api/super/dashboard-stats`: Obtiene estadísticas
  - POST `/api/super/switch-company`: Cambia empresa activa
  - GET `/api/super/active-company`: Obtiene empresa activa

#### Gestión de Usuarios
- **Ruta**: `/super/users`
- **Controlador**: `UsersManagementSuperAdminController`
- **Middleware**: `auth, EnsureUserIsSuperAdmin`
- **Acciones API**:
  - GET `/api/users`: Lista usuarios
  - POST `/api/users`: Crea usuario
  - PUT `/api/users/{user}`: Actualiza usuario
  - DELETE `/api/users/{user}`: Elimina usuario
  - PATCH `/api/users/{user}/status`: Actualiza estado
  - PATCH `/api/users/{user}/role`: Actualiza rol

#### Gestión de Empresas
- **Ruta**: `/super/companies`
- **Controlador**: `CompanyManagementController`
- **Middleware**: `auth, EnsureUserIsSuperAdmin`
- **Acciones API**:
  - GET `/api/companies`: Lista empresas
  - POST `/api/companies`: Crea empresa
  - PUT `/api/companies/{company}`: Actualiza empresa
  - DELETE `/api/companies/{company}`: Elimina empresa

### 📊 Módulo de Evaluación

#### Indicadores
- **Ruta**: `/indicadores/{id}`
- **Controlador**: `IndicadoresController`
- **Middleware**: `auth, verified, EnsureUserHasCompany`
- **Acciones API**:
  - POST `/indicadores/store-answers`: Guarda respuestas
  - POST `/indicadores/save-partial-answers`: Guarda respuestas parciales
  - POST `/indicadores/finalizar-autoevaluacion`: Finaliza autoevaluación

#### Evaluación
- **Ruta**: `/evaluacion/{value_id}`
- **Controlador**: `EvaluationController`
- **Middleware**: `auth, verified, EnsureUserHasCompany, EnsureCompanyIsAuthorized`
- **Acciones API**:
  - POST `/evaluacion/store-answers`: Guarda respuestas
  - POST `/evaluacion/store-answers-by-indicator`: Guarda por indicador
  - DELETE `/evaluacion/delete-file`: Elimina archivo

### 👨‍💼 Módulo de Evaluador

#### Dashboard Evaluador
- **Ruta**: `/evaluador/dashboard`
- **Controlador**: `EvaluadorController`
- **Middleware**: `auth, EnsureUserIsEvaluador`
- **Acciones API**:
  - GET `/api/evaluador/companies`: Lista empresas
  - POST `/api/evaluador/switch-company`: Cambia empresa activa
  - GET `/api/evaluador/active-company`: Obtiene empresa activa

### 📄 Reportes y Documentación

#### Reportes
- **Ruta**: `/super/reportes`
- **Controlador**: `ReportController`
- **Middleware**: `auth, EnsureUserIsSuperAdmin`
- **Acciones API**:
  - GET `/api/empresas-reportes`: Obtiene empresas para reportes
  - PATCH `/api/empresas-reportes/{company}/authorize-exporter`: Autoriza exportador

#### Documentos PDF
- **Controlador**: `PDFController`
- **Middleware**: `auth`
- **Rutas**:
  - GET `/download-indicators-pdf`: Descarga PDF de indicadores
  - GET `/download-company-documentation`: Descarga documentación de empresa
  - GET `/download-evaluation-pdf/{companyId?}`: Descarga PDF de evaluación

### 🔄 API Routes

#### Autenticación API
- **Ruta**: `/api/user`
- **Middleware**: `auth:sanctum`
- **Acciones**:
  - GET: Obtiene usuario actual

#### Gestión de Usuarios API
- **Base**: `/api/users/company`
- **Controlador**: `UserController`
- **Middleware**: `auth:sanctum, web`
- **Acciones**:
  - GET: Lista usuarios de empresa
  - POST: Crea usuario
  - PUT `/{user}`: Actualiza usuario
  - DELETE `/{user}`: Elimina usuario

#### Datos Geográficos API
- **Ruta**: `/api/provincias`
- **Middleware**: Ninguno
- **Acciones**:
  - GET: Obtiene lista de provincias

### 🔒 Middleware Utilizados
- `auth`: Autenticación básica
- `verified`: Usuario verificado
- `EnsureUserHasCompany`: Usuario tiene empresa asignada
- `EnsureUserIsAdmin`: Usuario es administrador
- `EnsureUserIsSuperAdmin`: Usuario es super administrador
- `EnsureUserIsEvaluador`: Usuario es evaluador
- `EnsureCompanyIsAuthorized`: Empresa está autorizada
- `EnsureApplicationSended`: Aplicación enviada

## About Laravel

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[WebReinvent](https://webreinvent.com/)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Jump24](https://jump24.co.uk)**
- **[Redberry](https://redberry.international/laravel/)**
- **[Active Logic](https://activelogic.com)**
- **[byte5](https://byte5.de)**
- **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
