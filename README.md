# Iniciar el proyecto

1. Cambiar el nombre y la descripcion del proyecto en `composer.json`
2. `composer update` `composer run-script init-app`
3. Verificar el `.env` que hayan configurado las siguentes constantes:
    - APP_NAME
    - APP_KEY
    - APP_URL
    - JWT_SECRET
    - JWT_ALGO
    - JWT_PRIVATE_KEY
    - JWT_PUBLIC_KEY

<b>En caso de de que no se hayan generado las constantes de JWT ejecutar:</b>

`php artisan jwt:secret`
`php artisan jwt:generate-certs`

4. Configurar las constantes `BASE_ADMIN_URL` y `STORAGE_PATH`

5. Eliminar todo lo relacionado con `TestController`, o tomar lo que requiera: 

6. Una vez completadada toda la configuracion iniciar, eliminar todo el contenido del `README.md`, iniciarlo con un titulo de la siguente manera:

`### {Nombre del proyecto}`

7. Ejecutar el primer commit, recomendado usar el `git commit -m "Initial Commit"`
<hr>

### JWT

https://laravel-jwt-auth.readthedocs.io/

### Laravel-permission

https://spatie.be/docs/laravel-permission/v5/introduction
