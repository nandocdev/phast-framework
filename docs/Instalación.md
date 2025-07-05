# Instalación

> Guía paso a paso para instalar y configurar Phast Framework

## 📋 Requisitos del Sistema

-  **PHP** >= 8.1
-  **Composer** >= 2.0
-  **Extensiones PHP:**
   -  `pdo`
   -  `pdo_mysql` o `pdo_pgsql`
   -  `mbstring`
   -  `json`
   -  `openssl`

## 🚀 Instalación

### 1. Clonar el Repositorio

```bash
git clone https://github.com/phast-framework/phast.git mi-proyecto
cd mi-proyecto
```

### 2. Instalar Dependencias

```bash
composer install
```

### 3. Configurar Entorno

```bash
# Copiar archivo de configuración
cp .env.example .env

# Configurar variables de entorno
nano .env
```

### 4. Configurar Base de Datos

Edita el archivo `.env` con tu configuración de base de datos:

```env
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=phast_app
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Ejecutar Migraciones

```bash
php phast migrate
```

### 6. Servir la Aplicación

```bash
php phast serve
```

Tu aplicación estará disponible en: `http://localhost:8000`

## 🐳 Instalación con Docker

```bash
# Clonar repositorio
git clone https://github.com/phast-framework/phast.git mi-proyecto
cd mi-proyecto

# Construir y levantar contenedores
docker-compose up -d

# Instalar dependencias
docker-compose exec app composer install

# Ejecutar migraciones
docker-compose exec app php phast migrate
```

## 🔧 Configuración de Servidor Web

### Apache

Crear archivo `.htaccess` en el directorio `public/`:

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^(.*)$ index.php [QSA,L]
```

### Nginx

```nginx
server {
    listen 80;
    server_name tu-dominio.com;
    root /path/to/phast/public;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

## ✅ Verificación de Instalación

### Verificar Comandos CLI

```bash
php phast list
```

### Verificar Rutas

```bash
php phast routes:list
```

### Crear Módulo de Prueba

```bash
php phast make:module Test
```

## 🚨 Solución de Problemas

### Error de Permisos

```bash
chmod -R 755 storage/
chmod -R 755 public/
```

### Error de Composer

```bash
composer clear-cache
composer install --no-cache
```

### Error de Base de Datos

1. Verificar que el servicio MySQL/PostgreSQL esté ejecutándose
2. Confirmar credenciales en `.env`
3. Crear base de datos manualmente si no existe

## 📚 Siguiente Paso

Una vez instalado correctamente, continúa con [[Primeros Pasos]] para crear tu primera aplicación.

---

#instalación #configuración #setup #phast

[[README]] | [[Primeros Pasos]]
