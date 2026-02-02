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
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2);
            $table->foreignId('location_id')->constrained()->onDelete('restrict');
            $table->enum('property_type', ['sale', 'rent']);
            $table->enum('category', ['house', 'apartment', 'commercial', 'land']);
            $table->integer('bedrooms')->nullable();
            $table->integer('bathrooms')->nullable();
            $table->integer('area_sqft');
            $table->integer('year_built')->nullable();
            $table->json('amenities')->nullable();
            $table->enum('status', ['active', 'pending', 'sold', 'rented'])->default('active');
            $table->boolean('is_featured')->default(false);
            $table->unsignedBigInteger('views_count')->default(0);
            $table->decimal('ai_price_suggestion', 12, 2)->nullable();
            $table->boolean('ai_description_generated')->default(false);
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->text('address');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['status', 'is_featured']);
            $table->index(['property_type', 'category']);
            $table->index(['price']);
            $table->index(['location_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
