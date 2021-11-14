# Bilemo API

[![Code Climate](https://api.codeclimate.com/v1/badges/1e36667fe5b8bb985332/maintainability)](https://codeclimate.com/github/Zuruuh/bilemo-api/maintainability)
[![Codacy](https://app.codacy.com/project/badge/Grade/4959d3714c074cef9139d6a2876a1873)](https://www.codacy.com/gh/Zuruuh/bilemo-api/dashboard)
![](https://img.shields.io/github/issues-raw/Zuruuh/bilemo-api?color=bright-green&label=Open%20Issues)
<p align="center">BileMo is a B2B networking platform service which provides phone retailers an access to their rich catalog</p>

## Installation

### Requirements

To run the project, you will need Docker, docker-compose, and make installed on your computer.   
To check if you meet the requirements, run the following commands:  
```bash
docker -v
docker-compose -v
make -v
```
If these 3 softwares are installed properly, you don't have to worry about any other php or postgres dependencies, as everything will be managed in docker containers. (Having php, composer, symfony cli, etc... installed on your computer will help writing commands faster, but is completely optionnal).  

### Running the project

To run the project locally, just run the following command:
```bash
make install
```

If you need help or don't know which make commands can be used, just run:
```bash
make help
# or
make
```

Once all the docker containers are up and running, just head to https://app.bilemo to see the project.

## Usage

To use and test the api, you'll have to login with the default admin account. Here are the credentials (dev env only):
**Username**: "admin"
**Password**: "password"

If you need informations about a specific route or entity, you can take a look at [the docs](./docs/app.md), or hit the /api/docs api endpoint.
