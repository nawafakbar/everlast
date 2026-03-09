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
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('category', ['All In', 'Wedding', 'Prewedding', 'Akad', 'Engagement', 'Other']);
            $table->integer('price');
            $table->text('description');
            $table->integer('duration_hours')->default(4);
            $table->integer('total_locations')->default(1);
            $table->string('thumbnail_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
