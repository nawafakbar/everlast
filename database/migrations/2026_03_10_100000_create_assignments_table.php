<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke tabel bookings (Acara yang mana?)
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            
            // Relasi ke tabel users (Freelancer-nya siapa?)
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Deskripsi tugas (misal: "Lead Videographer")
            $table->string('task');
            
            // Honor freelancer (Penting buat fitur Laporan Keuangan nanti)
            $table->integer('fee')->default(0);
            
            // Status penugasan (Default-nya pending nunggu jawaban freelancer)
            $table->enum('status', ['pending', 'accepted', 'rejected', 'completed'])->default('pending');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};