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

If you need informations about a specific route or entity, you can take a look at [the docs](./docs/app.md).