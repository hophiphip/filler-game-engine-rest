<?php

namespace Tests\Feature;

use Illuminate\Testing\TestResponse;
use JetBrains\PhpStorm\ArrayShape;
use Tests\TestCase;

use App\Models\Colors;
use App\Models\Player;
use App\Models\Field;

class ApiTest extends TestCase
{
    /**
     * Get non existent game.
     *
     * @return void
     */
    public function testGetNonExistentGame()
    {
        $response = $this->call('GET', '/api/game/aaaaaaaaaaaaaaaaaaaaaaaa');

        $response
            ->assertStatus(404)
            ->assertJson([
                'error' =>  'incorrect game id',
            ]);
    }

    /**
     * Put a move in non-existent game.
     *
     * @return void
     */
    public function testPutNonExistentGame()
    {
        $response = $this->call('PUT', '/api/game/aaaaaaaaaaaaaaaaaaaaaaaa', [
            'playerId' => 1,
            'color' => 'red',
        ]);

        $response
            ->assertStatus(404)
            ->assertJson([
                'error' =>  'incorrect game id',
            ]);
    }


    /**
     * Test new game creation with missing `width`.
     */
    public function testPostNewGameWithMissingWidth()
    {
        $response = $this->json('POST', '/api/game', [
            'height' => 15,
        ]);

        $response->assertStatus(422);
    }

    /**
     * Test new game creation with missing `height`.
     */
    public function testPostNewGameWithMissingHeight()
    {
        $response = $this->json('POST', '/api/game', [
            'width' => 25,
        ]);

        $response->assertStatus(422);
    }

    /**
     * Test new game creation with incorrect (even) `height`.
     */
    public function testPostNewGameWithIncorrectHeight()
    {
        $response = $this->json('POST', '/api/game', [
            'width' => 25,
            'height' => 14,
        ]);

        $response
            ->assertStatus(400)
            ->assertJson([
                'error' => 'incorrect field size'
            ]);
    }

    /**
     * Test new game creation.
     *
     * @return array
     */
    #[ArrayShape(['id' => "mixed"])]
    public function testPostNewGame(): array
    {
        $response = $this->json('POST', '/api/game', [
            'width' => 15,
            'height' => 11,
        ]);

        $response
            ->assertStatus(201);

        $this->assertTrue($response['id'] != null);

        return [
            'id' => $response['id'],
        ];
    }

    /**
     * Test getting existing game status.
     *
     * @depends testPostNewGame
     *
     * @param array $postResponse
     * @return array
     */
    #[ArrayShape(['id' => "mixed", 'players' => "mixed", 'currentPlayerId' => "mixed"])]
    public function testGetNewGame(array $postResponse): array
    {
        $response = $this->json('GET', '/api/game/' . $postResponse['id']);

        $response
            ->assertStatus(200)
            ->assertJson([
                'id' => $postResponse['id'],
                'currentPlayerId' => 1,
                'winnerPlayerId' => 0,
            ]);

        return [
            'id' => $response['id'],
            'players' => $response['players'],
            'currentPlayerId' => $response['currentPlayerId'],
        ];
    }

    /**
     * Test putting a new game move.
     *
     * @depends testGetNewGame
     *
     * @param array $getResponse
     * @return array
     */
    #[ArrayShape(['id' => "mixed", 'players' => "mixed", 'currentPlayerId' => "mixed"])]
    public function testPutGameMove(array $getResponse): array
    {
        $allowedColor = Colors::allowedColor(
            $getResponse['players'][1]['color'],
            $getResponse['players'][2]['color']
        );

        $this->assertTrue($allowedColor != null);

        $response = $this->json('PUT', '/api/game/' . $getResponse['id'], [
            'playerId' => $getResponse['currentPlayerId'],
            'color' => $allowedColor,
        ]);

        $response
            ->assertStatus(201)
            ->assertJson([
                'id' => $getResponse['id'],
                'currentPlayerId' => Player::nextPlayerId($getResponse['currentPlayerId']),
            ]);

        return [
            'id' => $response['id'],
            'players' => $response['players'],
            'currentPlayerId' => $response['currentPlayerId'],
        ];
    }

    /**
     * Test putting a new game move with current player color.
     *
     * @depends testPutGameMove
     *
     * @param array $putResponse
     * @return array
     */
    #[ArrayShape(['id' => "mixed", 'players' => "mixed", 'currentPlayerId' => "mixed"])]
    public function testPutGameMoveWithCurrentPlayerColor(array $putResponse): array
    {
        $response = $this->json('PUT', '/api/game/' . $putResponse['id'], [
            'playerId' => $putResponse['currentPlayerId'],
            'color' => $putResponse['players'][$putResponse['currentPlayerId']]['color'],
        ]);

        $response
            ->assertStatus(409)
            ->assertJson([
                'error' => 'provided player can\'t choose this color',
            ]);

        return [
            'id' => $putResponse['id'],
            'players' => $putResponse['players'],
            'currentPlayerId' => $putResponse['currentPlayerId'],
        ];
    }

    /**
     * Test putting a new game move with other player color.
     *
     * @depends testPutGameMoveWithCurrentPlayerColor
     *
     * @param array $putResponse
     * @return array
     */
    #[ArrayShape(['id' => "mixed", 'players' => "mixed", 'currentPlayerId' => "mixed"])]
    public function testPutGameMoveWithOtherPlayerColor(array $putResponse): array
    {
        $response = $this->json('PUT', '/api/game/' . $putResponse['id'], [
            'playerId' => $putResponse['currentPlayerId'],
            'color' => $putResponse['players'][Player::nextPlayerId($putResponse['currentPlayerId'])]['color'],
        ]);

        $response
            ->assertStatus(409)
            ->assertJson([
                'error' => 'provided player can\'t choose this color',
            ]);

        return [
            'id' => $putResponse['id'],
            'players' => $putResponse['players'],
            'currentPlayerId' => $putResponse['currentPlayerId'],
        ];
    }


    /**
     * Test putting a new game move with other player.
     *
     * @depends testPutGameMoveWithOtherPlayerColor
     *
     * @param array $putResponse
     * @return array
     */
    #[ArrayShape(['id' => "mixed", 'players' => "mixed", 'currentPlayerId' => "mixed"])]
    public function testPutGameMoveWithOtherPlayer(array $putResponse): array
    {
        $response = $this->json('PUT', '/api/game/' . $putResponse['id'], [
            'playerId' => Player::nextPlayerId($putResponse['currentPlayerId']),
            'color' => $putResponse['players'][Player::nextPlayerId($putResponse['currentPlayerId'])]['color'],
        ]);

        $response
            ->assertStatus(403)
            ->assertJson([
                'error' => 'provided player can\'t move right now',
            ]);

        return [
            'id' => $putResponse['id'],
            'players' => $putResponse['players'],
            'currentPlayerId' => $putResponse['currentPlayerId'],
        ];
    }

    /**
     * Test putting a new game move with unsupported color.
     *
     * @depends testPutGameMoveWithOtherPlayer
     *
     * @param array $putResponse
     * @return array
     */
    #[ArrayShape(['id' => "mixed", 'players' => "mixed", 'currentPlayerId' => "mixed"])]
    public function testPutGameMoveWithUnsupportedColor(array $putResponse): array
    {
        $response = $this->json('PUT', '/api/game/' . $putResponse['id'], [
            'playerId' => $putResponse['currentPlayerId'],
            'color' => 'orange',
        ]);

        $response
            ->assertStatus(400);

        return [
            'id' => $putResponse['id'],
            'players' => $putResponse['players'],
            'currentPlayerId' => $putResponse['currentPlayerId'],
        ];
    }


    /**
     * Perform PUT request (with handling HTTP 429 error)
     *
     * @param array $params PUT request parameters
     * @param int $timeout timeout in microseconds
     *
     * @return TestResponse
     */
    public function performPut(array $params, int $timeout): TestResponse
    {
        $backupResponse = $this->json('PUT', '/api/game/' . $params['id'], [
            'playerId' => $params['currentPlayerId'],
            'color' => $params['nextColor'],
        ]);

        // handle timeout in case of too many requests
        while ($backupResponse->status() == 429) {
            $backupResponse = $this->json('PUT', '/api/game/' . $params['id'], [
                'playerId' => $params['currentPlayerId'],
                'color' => $params['nextColor'],
            ]);

            usleep($timeout);
        }

        return $backupResponse;
    }

    /**
     * Trying to complete a game (not optimal way).
     *
     * @depends testPutGameMoveWithUnsupportedColor
     *
     * @param array $putResponse
     * @return array
     */
    #[ArrayShape(['id' => "mixed"])]
    public function testCompleteAGame(array $putResponse): array
    {
        $response = $this->json('GET', '/api/game/' . $putResponse['id']);

        $response
            ->assertStatus(200);

        $moves = 0;
        while ($response['winnerPlayerId'] == 0) {
            // Initialize player stats
            // NOTE: Can speed this up by storing & updating each player `stats`(player's cells indexes). 
            $stats = [
                1 => [],
                2 => [],
            ];

            foreach ($response['field']['cells'] as $i => $cell) {
                if ($cell['playerId'] != 0) {
                    array_push($stats[$cell['playerId']], $i);
                }
            }

            // map color to cell index
            $colorStats = [
                0 => [],
                1 => [],
                2 => [],
                3 => [],
                4 => [],
                5 => [],
                6 => [],
            ];

            // Get rid of player colors
            unset($colorStats[Colors::$colorsTable[$response['players'][1]['color']]]);
            unset($colorStats[Colors::$colorsTable[$response['players'][2]['color']]]);

            // Not needed, but just in case
            $this->assertTrue(count($colorStats) == 5);

            // Get the next color prediction map
            $time_pre = microtime(true);
            foreach ($colorStats as $colorKey => $colorStat) {
                $field = Field::fromArray($response['field']);

                $currentPlayerId = $response['currentPlayerId'];
                $playerColor = Colors::$colors[$colorKey];
                $playerStats = $stats[$currentPlayerId];

                for ($i = 0; $i < count($playerStats); $i++) {
                    $cellIndex = $playerStats[$i];

                    // left
                    if (!($field->hasNoLeftCell($cellIndex))) {
                        $leftIndex = $cellIndex - $field->width;
                        if ($field->isAssignable($cellIndex, $leftIndex, $playerColor)) {
                            // assign other cell to current player id
                            $field->cells[$leftIndex]["playerId"] = 
                                    $field->cells[$cellIndex]["playerId"];
                            // add other cell to current player cells
                            array_push($playerStats, $leftIndex);
                        }
                    }

                    // top
                    if (!($field->hasNoTopCell($cellIndex))) {
                        $topIndex = $cellIndex - $field->width + 1;
                        if ($field->isAssignable($cellIndex, $topIndex, $playerColor)) {
                            // assign other cell to current player id
                            $field->cells[$topIndex]["playerId"] = 
                                    $field->cells[$cellIndex]["playerId"];
                            // add other cell to current player cells
                            array_push($playerStats, $topIndex);
                        }
                    }

                    // right
                    if (!($field->hasNoRightCell($cellIndex))) {
                        $rightIndex = $cellIndex + $field->width;
                        if ($field->isAssignable($cellIndex, $rightIndex, $playerColor)) {
                            // assign other cell to current player id
                            $field->cells[$rightIndex]["playerId"] = 
                                    $field->cells[$cellIndex]["playerId"];
                            // add other cell to current player cells
                            array_push($playerStats, $rightIndex);
                        }
                    }

                    // bottom
                    if (!($field->hasNoBottomCell($cellIndex))) {
                        $bottomIndex = $cellIndex + $field->width - 1;
                        if ($field->isAssignable($cellIndex, $bottomIndex, $playerColor)) {
                            // assign other cell to current player id
                            $field->cells[$bottomIndex]["playerId"] = 
                                    $field->cells[$cellIndex]["playerId"];
                            // add other cell to current player cells
                            array_push($playerStats, $bottomIndex);
                        }
                    }
                }

                $colorStats[$colorKey] = $playerStats;

                unset($playerStats);
                unset($field);
            }
            $time_post = microtime(true);
            $exec_time = $time_post - $time_pre;
            var_dump(["move number" => $moves, "loop_time" => $exec_time]);

            // Get next color
            $nextColorKey = array_key_first($colorStats);
            foreach ($colorStats as $colorKey => $colorStat) {
                if (count($colorStats[$nextColorKey]) < count($colorStat)) {
                    $nextColorKey = $colorKey;
                }
            }

            // Cleanup
            unset($colorStats);

            // NOTE: this call blocks
            $response = $this->performPut([
                'id' => $response['id'],
                'currentPlayerId' => $response['currentPlayerId'],
                'nextColor' => Colors::$colors[$nextColorKey],
            ], 1000);

            $response
                ->assertStatus(201);

            $moves++;

            // Timeout
            if ($moves > 100) {
                $this->assertTrue($response['winnerPlayerId'] != 0);
                break;
            }
        }

        var_dump(["move count" => $moves]);

        return [
            'id' => $response['id'],
            'number of moves' => $moves,
        ];
    }

}
