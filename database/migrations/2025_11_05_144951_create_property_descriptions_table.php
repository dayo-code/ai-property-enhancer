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
        Schema::create('property_descriptions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('property_type');
            $table->string('location');
            $table->decimal('price', 15, 2);
            $table->text('key_features');
            $table->string('tone', 20)->default('formal');
            $table->text('generated_description');
            $table->integer('readability_score')->nullable();
            $table->integer('seo_score')->nullable();
            $table->integer('overall_score')->nullable();
            $table->integer('word_count')->nullable();
            $table->integer('character_count')->nullable();
            $table->integer('sentence_count')->nullable();
            $table->decimal('average_sentence_length', 5, 1)->nullable();
            $table->integer('keyword_mentions')->nullable();
            $table->timestamps();

            // Indexes for better query performance
            $table->index('property_type');
            $table->index('created_at');
            $table->index('overall_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_descriptions');
    }
};
