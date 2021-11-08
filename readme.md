# Bilemo API

[![Code Climate](https://api.codeclimate.com/v1/badges/1e36667fe5b8bb985332/maintainability)](https://codeclimate.com/github/Zuruuh/bilemo-api/maintainability)
[![Codacy](https://app.codacy.com/project/badge/Grade/4959d3714c074cef9139d6a2876a1873)](https://www.codacy.com/gh/Zuruuh/bilemo-api/dashboard)

<p align="center">BileMo is a B2B networking platform service which provides phone retailers an access to their rich catalog</p>

## Installation

### Requirements

To run the project, you will need Docker (and Docker-Compose), and Make installed on your computer.   
To check if you meet the requirements, run the following commands:  
```bash
$ docker -v
$ docker-compose -v
$ make -v
```

### Running the project

To run the project locally, just run the following command:
```bash
$ make install
```

If you need help or don't know which make commands can be used, just run:
```bash
$ make help
```
Or even simpler:
```bash
$ make
```

Once all the docker containers are up and running, just head to https://app.bilemo to see the project.

## Usage

### Authentication

While running the project locally, you can login using the test admin account.   
You can get your JsonWebToken by running the following request (example using cURL).   
```bash
$ curl --location --insecure --request POST 'https://app.bilemo/api/login' \
--header 'Content-Type: application/json' \
--data-raw '{
    "username": "admin",
    "password": "password"
}'
```

The token will always be in the "token" property of the response's json object.   

To use the token, add an "Authorization" header to your request, and set it's value to be "Bearer \<your-token\>".   
Here is another example request using cURL showing how to use the authorization header.
```bash
$ curl --location --insecure --request GET 'https://app.bilemo/api/client' \
--header 'Content-Type: application/json' \
--header 'Authorization: Bearer <JsonWebToken>'
```

When making an api call with a valid jwt, the server will always return a new token so you don't have to create a new one when yours expire. This can be used for api automation.   
Be careful, since the server will not return a new token if:   
- An uncatched error is thrown (Server will return a 500 http code).
- The token in your request was invalid.

### Docs

If you need informations about a specific route or entity, you can take a look at [the docs](./docs/app.md).