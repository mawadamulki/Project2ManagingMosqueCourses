<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('level_student_pivot', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('studentID');
            $table->foreign('studentID')
              ->references('id')
              ->on('students')
              ->onDelete('cascade');

            $table->unsignedBigInteger('levelID');
            $table->foreign('levelID')
              ->references('id')
              ->on('levels')
              ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('level_student_pivot');
    }
};
