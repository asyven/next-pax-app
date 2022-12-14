## test assignment

### Install via docker
1. Rename .env:

    In Windows: `copy .env.example .env`
    
    In Linux/Mac: `cp .env.example .env`

2. Install composer: `composer install`
3. Generate key: `php artisan key:generate`
4. Run Docker: `./vendor/bin/sail up`
5. Seed the data:`./vendor/bin/sail artisan db:seed`

You can edit default ports for database `FORWARD_DB_PORT` and for app `APP_PORT` in .env

Run tests
`./vendor/bin/sail artisan test --testsuite=Unit`

Explanations:
Home page can take 3 arguments
    `http://0.0.0.0:80/{propertyId}/{?dateFrom}{?dateTo}`
