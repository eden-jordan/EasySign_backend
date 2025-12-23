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
    Schema::create('action_emargements', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('presence_id');
        $table->enum('type_action', ['arrivee','depart','pause_debut','pause_fin']);
        $table->dateTime('timestamp');
        $table->timestamps();

        $table->foreign('presence_id')->references('id')->on('presences')->onDelete('cascade');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('action_emargements');
    }
};
