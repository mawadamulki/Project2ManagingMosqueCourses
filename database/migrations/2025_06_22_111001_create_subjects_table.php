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
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('subjectName');
            $table->unsignedBigInteger('levelID');
            $table->foreign('levelID')
              ->references('id')
              ->on('levels')
              ->onDelete('cascade');

            $table->unsignedBigInteger('teacherID');
            $table->foreign('teacherID')
              ->references('id')
              ->on('teachers')
              ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
