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
        Schema::create('joining_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('studentID')->constrained()->onDelete('cascade');
            $table->foreignId('courseID')->constrained()->onDelete('cascade');
            $table->enum('status',['pending','approved','rejected'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('joining_requests');
    }
};
