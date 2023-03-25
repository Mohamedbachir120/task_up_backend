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
        Schema::create('steps', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default('EN COURS');
            $table->integer('order')->default(1);
            $table->datetime('due_date')->nullable();
            $table->unsignedBigInteger('collaboration_id');
            $table->unsignedBigInteger('departement_id');
            
            $table->foreign('collaboration_id')->references('id')
            ->on('collaborations')->onDelete('cascade');

            $table->foreign('departement_id')->references('id')
            ->on('departements')->onDelete('cascade');

            $table->unsignedBigInteger('dependance_id')->nullable();

            $table->foreign('dependance_id')->references('id')->on('steps')
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
        Schema::dropIfExists('steps');
    }
};
