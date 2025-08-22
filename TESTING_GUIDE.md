# 🧪 Guía de Pruebas - Sistema EcoPlagas

## Configuración Inicial

### 1. Variables de Entorno Backend
Asegúrate de tener estas variables en tu `.env`:
```env
APP_NAME=EcoPlagas
APP_ENV=local
APP_DEBUG=true
DB_CONNECTION=mysql
DB_DATABASE=ecoplagas
MAIL_ENABLED=false  # Para desarrollo, usar logging
```

### 2. Variables de Entorno Frontend
Verifica tu `.env` en FrontEnd/:
```env
VITE_API_BASE_URL=http://127.0.0.1:8000/api
VITE_APP_ENV=development
```

## Comandos de Inicio

### Backend
```bash
cd Backend
composer install
php artisan migrate
php artisan serve
```

### Frontend
```bash
cd FrontEnd
npm install
npm run dev
```

## ✅ Lista de Verificación

### Backend API
- [ ] `GET /api/health` - Verificar API funcional
- [ ] `POST /api/auth/login` - Login de usuario
- [ ] `GET /api/admin/dashboard` - Dashboard administrativo
- [ ] `GET /api/client/dashboard` - Dashboard de cliente
- [ ] `GET /api/admin/reports?type=summary` - Reportes básicos
- [ ] `GET /api/gallery/featured` - Galería pública
- [ ] `POST /api/admin/gallery` - Crear elemento galería

### Frontend
- [ ] Carga inicial sin errores de consola
- [ ] Navegación entre páginas públicas
- [ ] Login funcional (admin/client)
- [ ] Dashboard admin con datos
- [ ] Dashboard client con datos
- [ ] Galería pública funcional
- [ ] Filtros de galería funcionando

### Seguridad
- [ ] Configuración DomPDF segura
- [ ] Errores sanitizados (no exponer stack traces)
- [ ] Validación de parámetros API
- [ ] Protección de rutas admin/client

## 🐛 Errores Comunes y Soluciones

### "CORS Error"
- Verificar que `HandleCors` esté configurado
- Comprobar URLs en variables de entorno

### "Token Expired"
- Limpiar localStorage del navegador
- Verificar configuración Sanctum

### "Database Connection Error"
- Verificar configuración de base de datos
- Ejecutar migraciones

### "Permission Denied"
- Verificar middleware de roles
- Comprobar que el usuario tenga el rol correcto

## 🔍 Logs a Revisar

### Backend (storage/logs/laravel.log)
```bash
tail -f storage/logs/laravel.log
```

### Frontend (Consola del navegador)
- Errores de JavaScript
- Requests fallidas
- Warnings de React

## 📊 Métricas de Performance

- Tiempo de carga inicial: < 3 segundos
- Respuesta API promedio: < 500ms
- Memoria RAM backend: < 256MB
- Sin memory leaks en frontend

## 🚀 Deployment Checklist

- [ ] Variables de entorno de producción configuradas
- [ ] Build de frontend sin warnings
- [ ] Migraciones ejecutadas
- [ ] Permisos de archivos configurados
- [ ] HTTPS configurado
- [ ] Logs de error configurados