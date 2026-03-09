<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portfolios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Relasi ke Freelancer
            $table->string('cover_image'); // Link GDrive untuk gambar depan
            $table->string('category'); // Cth: PHOTOGRAPHY : TYING THE KNOT
            $table->date('event_date'); 
            $table->string('title'); // Cth: A HOLY DAY, A ROYAL TOUCH...
            $table->string('client_name'); // Cth: DAVIN & VANESSA WEDDING BY TONNY
            $table->string('quote')->nullable(); // Cth: GUIDED BY GRACE...
            $table->json('gallery_links')->nullable(); // Array Link GDrive untuk detail galeri
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portfolios');
    }
};