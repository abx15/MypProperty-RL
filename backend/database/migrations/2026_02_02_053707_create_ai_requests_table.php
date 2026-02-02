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
        Schema::create('ai_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('request_type', ['price', 'description', 'market', 'enquiry']);
            $table->json('input_data');
            $table->json('output_data')->nullable();
            $table->integer('tokens_used')->default(0);
            $table->text('error')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'request_type']);
            $table->index(['request_type', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_requests');
    }
};
