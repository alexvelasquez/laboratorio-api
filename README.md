# API DESARROLLO DE SOTWARE EN SISTEMAS DISTRIBUIDOS
API REST que brinda servicios web requeridos para el trabajo integrador de la materia.

## Comenzando 🚀

_Estas instrucciones te permitirán obtener una copia del proyecto en funcionamiento en tu máquina local para propósitos de desarrollo y pruebas._

Mira **Deployment** para conocer como desplegar el proyecto.


### Pre-requisitos 📋


```
PHP >= 7.1.3
```

### Instalación 🔧

_Clonar_

```
git clone https://github.com/alexvelasquez/laboratorio-api.git
cd  laboratorio-api
```

_Ejecutar composer install_

```
php -d memory_limit = -1 composer.phar install
```

_Crear claves publicas y privadas_

```
mkdir -p config\jwt,
openssl genrsa -out config/jwt/private.pem 4096,
openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem
```

_Crear Database_

```
php bin/console doctrine:database:create
php bin/console doctrine:schema:update --force
```
_LEVANTAR SERVIDOR_

```
php bin/console server:run ó  localhost/laboratorio-api/public/index.php/api/doc
```

## Autores ✒️

* **Rodrigo Naranja** - *Fronted*
* **Velasquez Alex** - *Backend*
