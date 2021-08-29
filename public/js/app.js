import { newGame, getGame, updateGame } from './api/api.js';

import { fieldWidth, fieldHeight, cellWidth, cellHeight, newPageSelectValues } from './config.js';

import { Cell } from './models/cell.js';
import { Field } from './models/field.js';
import { Player } from './models/player.js';
import { Game } from './models/game.js';
import { colors } from './models/colors.js';

// Percent bar padding for proper progress display
const percentPadding = 6;

const gameIdKey = "gameId";

const newPageStateId        = 'new',
      newPageButtonId       = 'new-button',
      newPageSelectId       = 'new-select',
      gamePageStateId       = 'game',
      gamePageCanvasId      = 'game-canvas',
      gameButtonsId         = 'game-buttons',
      gamePlayerStateId     = 'game-players-state',
      gameProgress1Id       = 'progress-1',
      gameProgress2Id       = 'progress-2',
      gameProgressBar1Id    = 'progress-bar-1',
      gameProgressBar2Id    = 'progress-bar-2',
      gameProgressCircle1Id = 'progress-circle-1',
      gameProgressCircle2Id = 'progress-circle-2';

const newPageState          = document.getElementById(newPageStateId),
      newPageButton         = document.getElementById(newPageButtonId),
      newPageSelect         = document.getElementById(newPageSelectId),
      gamePageState         = document.getElementById(gamePageStateId),
      gamePageCanvas        = document.getElementById(gamePageCanvasId),
      gameButtons           = document.getElementById(gameButtonsId),
      gamePlayerState       = document.getElementById(gamePlayerStateId),
      gameProgress1         = document.getElementById(gameProgress1Id),
      gameProgress2         = document.getElementById(gameProgress2Id),
      gameProgressBar1      = document.getElementById(gameProgressBar1Id),
      gameProgressBar2      = document.getElementById(gameProgressBar2Id),
      gameProgressCircle1   = document.getElementById(gameProgressCircle1Id),
      gameProgressCircle2   = document.getElementById(gameProgressCircle2Id);

var gameCanvasContext;
var game;

hideGame();

function hideGame() {
    newPageState.hidden  = false;
    gamePageState.hidden = true;
    gameProgress1.hidden = true;
    gameProgress2.hidden = true;
    gameProgressBar1.hidden = true;
    gameProgressBar2.hidden = true;
    gameProgressBar1.hidden = true;
    gameProgressBar2.hidden = true;
}

function unhideGame() {
    newPageState.hidden  = true;
    gamePageState.hidden = false;
    gameProgress1.hidden = false;
    gameProgress2.hidden = false;
    gameProgressBar1.hidden = false;
    gameProgressBar2.hidden = false;
    gameProgressBar1.hidden = false;
    gameProgressBar2.hidden = false;
}


// TODO: No `game over` state

function assignButtonColors(button, color) {
    if (Game.isUsefulColor(game, color)) {
        button.style["border"] = "30px solid " + Cell._adjustColor(color, -25);
        button.style["border-right-color"] = Cell._adjustColor(color, -50);
        button.style["border-bottom-color"] = Cell._adjustColor(color, -25);
        button.style["border-left-color"] = color;
    } else {
        button.style["border"] = "30px solid " + Cell._adjustColor(color, -175);
        button.style["border-right-color"] = Cell._adjustColor(color, -200);
        button.style["border-bottom-color"] = Cell._adjustColor(color, -175);
        button.style["border-left-color"] = Cell._adjustColor(color, -150);
    }
}

function addGameButtons() {
    if (gameButtons.childElementCount == 0) { 
        colors.forEach(function(color, index) {
            var button = document.createElement('div');
            button.id = gameButtonsId + "-" + index;

            assignButtonColors(button, color);

            button.addEventListener(
                "click",
                function() {
                    console.log("Game button: ", button.id, " was pressed");

                    // current player's color can't be used & other player's color can't be used
                    if (Game.isUsefulColor(game, color)) {
                        // PUT a player move
                        updateGame(game, color).then(json => {
                            if (json) {
                                console.debug(json);

                                // update the game
                                game.currentPlayerId = json.currentPlayerId;
                                game.winnerPlayerId = json.winnerPlayerId;
                                game.players = {
                                    1 : Player.from(json.players[1]),
                                    2 : Player.from(json.players[2]),
                                };
                                game.field = Field.from(json.field);
                                
                                // redraw the game
                                draw(game);
                            } else {
                                console.debug("Button with index: ", index, "request failed");
                            }
                        });
                    } else {
                        console.log("Color: ", color, "can't be used right now");
                    }

                },
                false
            );

            gameButtons.appendChild(button);
        });
    } else {
        console.log("Buttons have been added before");
        
        colors.forEach(function(color, index) {
            assignButtonColors(gameButtons.children[index], color);
        });
    }
}

function init() {
    if (gamePageCanvas.getContext) {
        gameCanvasContext  = gamePageCanvas.getContext("2d");
        return null;
    } else {
        return Error("canvas:" + gamePageCanvasId + " has no context"); 
    }
}


function main() {
    //sessionStorage.clear();
    
    var gameId = sessionStorage.getItem(gameIdKey);
    if (gameId == null) {
        console.log("no game id is set");

        hideGame();

        newPageButton.addEventListener(
            "click",
            function() {
                console.log("Next button was pressed");
                
                if (newPageSelect.value in newPageSelectValues) {
                    newGame(
                        newPageSelectValues[newPageSelect.value].width,
                        newPageSelectValues[newPageSelect.value].height,
                    ).then(json => {
                        console.log("POST returned: ", json);

                        if (json.id) {
                            sessionStorage.setItem(gameIdKey, json.id);
                              
                            // TODO: Test game accessibility via Id

                            main();
                        } else {
                            console.error("can't get game id value");
                        }
                    });
                } else {
                    console.error("incorrect field size was provided");
                }
            },
            false
        );
    }
    else {
        unhideGame();

        console.log(gameId);

        getGame(gameId).then(json => {
            console.log("GET returned: ", json);

            game = Game.from(json);
            if (game != null) {
                console.log("Game state: ", game);
                
                // change canvas size to fit game field
                gamePageCanvas.width  = cellWidth * game.field.width + 100; //fieldWidth;
                gamePageCanvas.height = cellHeight * game.field.height + 100 
                                        - ((~~game.field.height / 2) * cellHeight); //fieldHeight;
               
                console.log("Canvas width: ", gamePageCanvas.width,
                            ", Canvas height: ", gamePageCanvas.height);
                
                // draw the game
                draw(game);
            } else {
                console.error("Failed to initialize game");
            }
        });

    }
}

function draw(game) {
    // Draw game field
    game.field.draw(
        gameCanvasContext,
        0, // x 
        0, // y
        cellWidth,
        cellHeight
    );

    // Add game buttons to button container
    addGameButtons();

    // Display which player turn is right now
    gamePlayerState.innerHTML = "Player's " + game.currentPlayerId + " turn";
    // TODO: Make black background (so it won't be necessary to change white to black)
    gamePlayerState.style.color = 
        game.players[game.currentPlayerId].color === '#ffffff' ?
        '#000000' : game.players[game.currentPlayerId].color;

    // Update progress bar
    console.debug("Progress-1: ", game.field.playersCells[1].size / game.field.cells.length * 100);
    console.debug("Progress-2: ", game.field.playersCells[2].size / game.field.cells.length * 100);
    
    gameProgress1.style.background = game.players[1].color;
    gameProgress1.style.width = 100 * (game.field.playersCells[1].size / game.field.cells.length) + percentPadding + "%";
    gameProgress2.style.background = game.players[2].color;
    gameProgress2.style.width = 100 * (game.field.playersCells[2].size / game.field.cells.length) + percentPadding + "%";

    gameProgressCircle1.style.background = game.players[1].color;
    gameProgressCircle2.style.background = game.players[2].color;
    // TODO: Handle gameover here: ..
}

const err = init();
if (err) {
    console.error(err);
} else {
    main();
}
