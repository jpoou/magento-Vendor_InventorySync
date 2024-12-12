# Vendor_InventorySync

Este módulo para Magento 2 permite sincronizar el inventario y los precios de los productos diariamente mediante un cron job que obtiene datos de una API externa.

## Requisitos

- Magento 2.4.x
- Acceso a la API REST del proveedor externo (URL del API: `https://dummyjson.com/products`)

## Instalación

### 1. Clonar el repositorio o copiar el módulo

Clona el repositorio o copia los archivos del módulo en el directorio correspondiente dentro de tu instalación de Magento:

```bash
cd /var/www/html
mkdir -p app/code/Vendor/InventorySync
cp -R <ruta_local_del_modulo>/* app/code/Vendor/InventorySync
```

### 2. Ejecutar los comandos de configuración

Ejecuta los siguientes comandos para registrar el módulo, compilar el código y actualizar la base de datos:

```bash
php bin/magento module:enable Vendor_InventorySync
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento cache:flush
```

### 3. Verificar la instalación

Asegúrate de que el módulo esté habilitado:

```bash
php bin/magento module:status | grep Vendor_InventorySync
```
Deberías ver el estado como `enabled`.

### 4. Configuración del Cron Job

Magento utiliza cron jobs para ejecutar tareas programadas. Asegúrate de que el cron de Magento esté configurado en tu servidor. Puedes verificarlo o configurarlo ejecutando:

```bash
php bin/magento cron:install
```

El módulo `Vendor_InventorySync` añadirá automáticamente una tarea programada para ejecutarse diariamente y sincronizar el inventario.

### 5. Configuración de la API

Si es necesario, actualiza el endpoint de la API y otros parámetros en el archivo de configuración del módulo:

```bash
app/code/Vendor/InventorySync/etc/config.xml
```

## Uso

El módulo realiza las siguientes acciones:

- Llama a la API `https://dummyjson.com/products` diariamente para obtener el inventario y precios.
- Actualiza los productos existentes en Magento.
- Crea nuevos productos si no existen.
- Maneja el inventario y estado de disponibilidad.
- Registra errores en los logs de Magento (`var/log/system.log`) si hay fallos en la sincronización.

### Extensión de GraphQL

El módulo también extiende el esquema de GraphQL en Magento 2.4.x para crear un nuevo endpoint que devuelve información personalizada de los productos. Esto permite obtener datos adicionales mediante consultas GraphQL personalizadas.

## Personalización

Puedes modificar la lógica de sincronización en el archivo:

```bash
app/code/Vendor/InventorySync/Cron/SyncInventory.php
```

## Desinstalación

Para deshabilitar el módulo:

```bash
php bin/magento module:disable Vendor_InventorySync
php bin/magento setup:upgrade
php bin/magento cache:flush
```

Para eliminar el módulo completamente, elimina la carpeta:

```bash
rm -rf app/code/Vendor/InventorySync
```

## Notas

- Asegúrate de tener configurado el cron de Magento correctamente.
- Verifica que los SKUs en Magento coincidan con los SKUs proporcionados por la API para evitar errores de sincronización.

## Créditos

Desarrollado por Juan Carlos Morales Poou. Contacto: [tusitio.com](https://quickshipping.com)
