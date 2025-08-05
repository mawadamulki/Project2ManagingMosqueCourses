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
        Schema::create('answers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('studentID');
            $table->foreign('studentID')->references('id')->on('students')->onDelete('cascade');
            $table->unsignedBigInteger('teacherID');
            $table->foreign('teacherID')->references('id')->on('teachers')->onDelete('cascade');
            $table->unsignedBigInteger('questionID');
            $table->foreign('questionID')->references('id')->on('questions')->onDelete('cascade');
            $table->string('answer');
            $table->boolean('isCorrect')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('answers');
    }
};
