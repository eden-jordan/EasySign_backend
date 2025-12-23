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
    Schema::create('presences', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('personnel_id');
        $table->date('date');
        $table->dateTime('arrivee')->nullable();
        $table->dateTime('depart')->nullable();
        $table->dateTime('pause_debut')->nullable();
        $table->dateTime('pause_fin')->nullable();
        $table->enum('statut', ['Present','Absent','En_pause', 'Termine'])
              ->default('Absent');
        $table->timestamps();

        $table->foreign('personnel_id')->references('id')->on('personnel')->onDelete('cascade');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presences');
    }
};
