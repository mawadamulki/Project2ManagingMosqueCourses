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
        Schema::create('curriculumPlans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('levelID')->constrained()->onDelete('cascade');
            $table->string('sessionDate');
            $table->string('sessionContent');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('curriculumPlans');
    }
};
