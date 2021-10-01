<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class GameCollection extends Migration
{
    /**
     * The name of the database connection to use.
     *
     * @var string
     */
    protected $connection = 'mongodb';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {        
        Schema::connection($this->connection)
        ->table('game_collection', function (Blueprint $collection) 
        {
            // Expire in 24 hours
            $collection->expire('createdAt', 60 * 60 * 24);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection($this->connection)
        ->table('game_collection', function (Blueprint $collection) 
        {
            $collection->drop();
        });
    }
}
