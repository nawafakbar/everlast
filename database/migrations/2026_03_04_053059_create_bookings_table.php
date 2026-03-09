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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Relasi ke tabel users (pemesan)
            $table->foreignId('package_id')->constrained('packages')->onDelete('cascade'); // Relasi ke paket
            $table->string('partner_name');
            $table->text('couple_address');
            $table->decimal('couple_lat', 10, 8)->nullable();
            $table->decimal('couple_lng', 11, 8)->nullable();
            $table->text('event_location');
            $table->decimal('event_lat', 10, 8)->nullable();
            $table->decimal('event_lng', 11, 8)->nullable();
            $table->date('booking_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('status', ['pending', 'dp_paid', 'paid_in_full', 'completed', 'cancelled'])->default('pending');
            $table->text('event_location_2')->nullable();
            $table->decimal('event_lat_2', 10, 8)->nullable();
            $table->decimal('event_lng_2', 11, 8)->nullable();
            $table->string('google_calendar_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
