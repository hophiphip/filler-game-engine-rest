# Filler / 7 Colors game

## Try it out
https://fllr.herokuapp.com/

## Running locally (with [docker compose](https://docs.docker.com/compose/install/))
Update `.env.sample` file if necessary and then run:
```bash
docker-compose -f docker-compose.sample.yml up --build
```
Then it can be accessed on `localhost:8080`. Port can be changed in `.env.sample` file.

## API Routes
Create a new game
* **URL**  
  /api/game
* **Method**  
  `POST`
* **URL Params**  
  None
* **Data Params**  
  **Required**  
  `width=[int]`
  `height=[int]`

Get game status
* **URL**  
    /api/game/:id
* **Method**  
    `GET`
* **URL Params**  
  **Required:**  
    `id=[string]`
* **Data Params**  
     None

Make a player move
* **URL**  
  /api/game/:id
* **Method**  
  `PUT`
* **URL Params**  
  **Required:**  
  `id=[string]`
* **Data Params**  
  `playerId=[int]`
  `color=[string]`
