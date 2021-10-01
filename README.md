# Filler / 7 Colors game

## Try it out
https://fllr.herokuapp.com/

## Running locally (with [docker compose](https://docs.docker.com/compose/install/))
  1. Create local config & docker-compose files.
```bash
cp .env.compose .env
cp docker-compose.sample.yml docker-compose.yml
```
  2. Update environment (`.env` file) variables (Optional).
  3. Run it.
```bash
docker-compose up
```
Then it can be accessed on `localhost:8080`. Port can be changed in `.env` file.


