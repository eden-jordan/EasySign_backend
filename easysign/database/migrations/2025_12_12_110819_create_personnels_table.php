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
    Schema::create('personnel', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('organisation_id');
        $table->string('nom');
        $table->string('prenom');
        $table->string('email')->nullable();
        $table->string('tel')->nullable();
        $table->string('qr_code');
        $table->timestamps();

        $table->foreign('organisation_id')->references('id')->on('organisations')->onDelete('cascade');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personnels');
    }
};
