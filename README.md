# DentalTech - Sistema de Gesti�n Dental

Sistema completo para la gesti�n de laboratorios dentales y relaci�n con odont�logos.

## Requisitos del Sistema

- PHP 7.4+
- MySQL 5.7+
- Composer

## Instalaci�n

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
+-- app/                  # L�gica de la aplicaci�n
+-- config/               # Configuraciones
+-- public/               # Archivos p�blicos
+-- resources/            # Vistas y assets
+-- storage/              # Storage
+-- tests/                # Pruebas automatizadas
+-- vendor/               # Dependencias
+-- .env.example          # Variables de entorno
```

## Configuraci�n

Editar `.env` con tus credenciales:
```ini
DB_HOST=localhost
DB_NAME=dentaltech
DB_USER=dentaltech_user
DB_PASS=tucontrase�a
```

## Licencia

Este proyecto es de c�digo abierto bajo la licencia MIT.

## Soporte

Para soporte t�cnico, contactar a:
- soporte@dentaltech.com