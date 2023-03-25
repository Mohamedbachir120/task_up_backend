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
        Schema::create('invitations', function (Blueprint $table) {
            $table->id();
            $table->string('status')->default('PENDING');
            $table->unsignedBigInteger('collaboration_id');
            $table->unsignedBigInteger('departement_id');
            
            $table->foreign('collaboration_id')->references('id')
            ->on('collaborations')->onDelete('cascade');

            $table->foreign('departement_id')->references('id')
            ->on('departements')->onDelete('cascade');

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
        Schema::dropIfExists('invitations');
    }
};
