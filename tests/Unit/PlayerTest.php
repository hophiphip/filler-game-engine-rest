<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Player;

class PlayerTest extends TestCase 
{
   /**
    * Test next player id function.
    *
    * @return void
    */
   public function testNextPlayerId()
   {
      $this->assertTrue(1 == Player::nextPlayerId(2)); 
      $this->assertTrue(2 == Player::nextPlayerId(1)); 
      
      $this->assertTrue(-1 == Player::nextPlayerId(3)); 
      $this->assertTrue(-1 == Player::nextPlayerId(0)); 
      $this->assertTrue(-1 == Player::nextPlayerId(-1)); 
   }
}
