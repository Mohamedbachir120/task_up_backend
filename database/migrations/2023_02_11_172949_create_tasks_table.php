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
            $table->datetime('start_date');
            $table->datetime('end_date');
            $table->datetime('finished_at')->nullable();

            $table->string('status')->default("À FAIRE");
            $table->text("description")->nullable();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->unsignedBigInteger('dependance_id')->nullable();

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
