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
    Schema::create('rapports', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('organisation_id');
        $table->date('date');
        $table->integer('total_present')->default(0);
        $table->integer('total_absents')->default(0);
        $table->integer('total_retards')->default(0);
        $table->integer('total_pause_retards')->default(0);
        $table->timestamps();

        $table->foreign('organisation_id')->references('id')->on('organisations')->onDelete('cascade');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rapports');
    }
};
