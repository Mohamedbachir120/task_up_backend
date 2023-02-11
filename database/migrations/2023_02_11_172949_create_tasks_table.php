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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status')->default("en cours");
            $table->text("description")->nullable();
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('dependance_id');

            $table->foreign('dependance_id')->references('id')->on('tasks')
            ->onDelete('cascade');

            $table->foreign('project_id')->references('id')->on('projects')
            ->onDelete('cascade');

            


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
        Schema::dropIfExists('tasks');
    }
};
