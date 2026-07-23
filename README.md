# Sistema Integral de Gestión (SIG) - inventario_ventas

**Versión:** 1.0.0  
**Fecha:** 2026-07-23  
**Estado:** 🚧 En desarrollo  

---

## 📋 Descripción

Sistema POS completo para gestión de inventario, ventas (facturación normal y electrónica DIAN Colombia), clientes, proveedores, créditos, egresos y reportes.

Desarrollado con **PHP 8.2+**, **MySQL/MariaDB**, **Bootstrap 5**, **jQuery 3.x** y **JavaScript moderno**.

## 🚀 Stack Tecnológico

| Componente | Tecnología |
|---|---|
| Backend | PHP 8.2+ (MVC propio) |
| Frontend | Bootstrap 5 + jQuery 3.x + DataTables |
| Base de Datos | MySQL 8 / MariaDB 11 |
| Facturación Electrónica | API |
| PDF | mpdf/mpdf ^8.x |
| Offline | IndexedDB + localStorage |
| Logs | Monolog |
| Análisis Estático | PHPStan, Psalm |

## 📁 Estructura del Proyecto

```
inventario_ventas/
├── public/              → Punto de entrada (DocumentRoot)
│   ├── index.php       → Front Controller
│   ├── assets/         → CSS, JS, imágenes
│   ├── views/          → Plantillas PHP
│   └── .htaccess       → Rewrite rules
├── src/                 → Código fuente PHP
│   ├── Controllers/    → Controladores
│   ├── Models/         → Modelos (DAO)
│   ├── Core/           → Router, Database, Request, Response
│   ├── Middleware/      → Auth, CSRF, roles
│   ├── Services/       → Lógica de negocio
│   ├── Helpers/        → Utilidades
│   └── config/         → Configuración
├── app/                 → APIs internas
├── sql/                 → Esquemas y migraciones
└── vendor/              → Composer dependencies
```

## 🗄️ Base de Datos

- **Prefijo**: `vb_` (ventas básicas)
- **37 tablas** organizadas en 8 grupos
- **Foreign Keys** con relaciones sólidas
- **Triggers** para actualización automática de inventario
- Ver `sql/schema_vb.sql` para el esquema completo

## 🔒 Seguridad

- ✅ Contraseñas con `password_hash()` (bcrypt)
- ✅ Prepared statements (PDO)
- ✅ CSRF tokens en formularios
- ✅ Escape de salida (`htmlspecialchars`)
- ✅ Headers de seguridad (CSP, HSTS, X-Frame-Options)
- ✅ Timeout de sesión
- ✅ 3 roles: Admin, Cajero, Inventario

## 📡 Módulo Offline

El sistema cuenta con un módulo offline que:
1. Detecta automáticamente la pérdida/restauración de conexión
2. Almacena operaciones en cola local (IndexedDB)
3. Sincroniza automáticamente al恢复 la conexión
4. **No bloquea ni recarga el software**

## 🧾 Facturación Electrónica (DIAN)

Toda la configuración de facturación electrónica se maneja desde la interfaz de administrador:
- NIT, razón social, régimen tributario
- Credenciales API 
- Rangos de facturación y resolución DIAN
- Certificado digital (Software ID, clave, llave envío)
- Botón de prueba de conexión

## 📊 Etapas de Desarrollo

| Fase | Fecha Inicio | Fecha Fin | Descripción |
|---|---|---|---|
| Fase 1 | 2026-07-23 | 2026-07-30 | Fundación del proyecto |
| Fase 2 | 2026-07-30 | 2026-08-06 | Autenticación y seguridad |
| Fase 3 | 2026-08-06 | 2026-08-20 | Módulos Core |
| Fase 4 | 2026-08-20 | 2026-09-05 | Facturación (normal + FE) |
| Fase 5 | 2026-09-05 | 2026-09-12 | Módulos Secundarios |
| Fase 6 | 2026-09-12 | 2026-09-19 | Configuración y Admin |
| Fase 7 | 2026-09-19 | 2026-09-30 | Reportes y Dashboard |
| Fase 8 | 2026-10-01 | 2026-10-10 | Pulido y pruebas |
