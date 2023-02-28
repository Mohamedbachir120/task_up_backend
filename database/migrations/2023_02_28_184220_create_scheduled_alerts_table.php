<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('scheduled_alerts', function (Blueprint $table) {
            $table->id();
           
            $table->string("destination");
            $table->unsignedBigInteger('task_id');
            $table->foreign('task_id')->references('id')->on('tasks')
            ->onDelete('cascade');
            $table->datetime('send_time');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('scheduled_alerts');
    }
};
