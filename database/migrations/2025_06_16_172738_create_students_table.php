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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('firstAndLastName');
            $table->string('fatherName');
            $table->string('phoneNumber');
            $table->string('password');
            $table->string('birthDate');
            $table->string('address');
            $table->string('studyOrCareer');
            $table->boolean('magazeh')->default(false);
            $table->string('PreviousCoursesInOtherPlace');
            $table->boolean('isPreviousStudent')->default(false);
            $table->string('previousCourses')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
