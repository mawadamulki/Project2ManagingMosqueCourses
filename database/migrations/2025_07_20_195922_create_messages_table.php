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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('senderID');
            $table->unsignedBigInteger('receiverID');
            $table->unsignedBigInteger('parentID')->nullable();
            $table->text('content');
            $table->foreign('senderID')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('receiverID')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('parentID')->references('id')->on('messages')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
