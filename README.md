# DentalTech - Sistema de Gestión Dental

Sistema completo para la gestión de laboratorios dentales y relación con odontólogos.

## Requisitos del Sistema

- PHP 7.4+
- MySQL 5.7+
- Composer

## Instalación

1. Clonar repositorio:
```bash
git clone https://github.com/tudominio/dentaltech.git
cd dentaltech
```

2. Instalar dependencias:
```bash
composer install
```

3. Configurar entorno:
```bash
cp .env.example .env
```

4. Configurar base de datos:
```bash
mysql -u root -p < sql/install.sql
```

5. Configurar permisos:
```bash
chmod -R 775 storage
chown -R www-data:www-data public/uploads
```

## Estructura del Proyecto

```
dentaltech/
+-- app/                  # Lógica de la aplicación
+-- config/               # Configuraciones
+-- public/               # Archivos públicos
+-- resources/            # Vistas y assets
+-- storage/              # Storage
+-- tests/                # Pruebas automatizadas
+-- vendor/               # Dependencias
+-- .env.example          # Variables de entorno
```

## Configuración

Editar `.env` con tus credenciales:
```ini
DB_HOST=localhost
DB_NAME=dentaltech
DB_USER=dentaltech_user
DB_PASS=tucontraseña
```

## Licencia

Este proyecto es de código abierto bajo la licencia MIT.

## Soporte

Para soporte técnico, contactar a:
- soporte@dentaltech.com