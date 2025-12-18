<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('horaires', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('organisation_id');
        $table->time('heure_arrivee');
        $table->time('heure_depart');
        $table->time('heure_pause_debut');
        $table->time('heure_pause_fin');
        $table->json('jours_travail');
        $table->timestamps();

        $table->foreign('organisation_id')->references('id')->on('organisations')->onDelete('cascade');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('horaires');
    }
};
