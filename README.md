## test assignment

Install
`composer install`

Setup database

- `.env`
- import `data.sql`

Run tests
`artisan test --testsuite=Unit`


Explanations:
Home page can take 3 arguments
    `http://127.0.0.1:8000/{propertyId}/{?dateFrom}{?dateTo}`
