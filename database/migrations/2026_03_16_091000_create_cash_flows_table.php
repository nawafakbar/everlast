<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cash_flows', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->enum('type', ['income', 'expense']);
            $table->string('category'); // cth: 'booking_payment', 'freelancer_fee', 'operational', 'equipment', dll
            $table->decimal('amount', 15, 2);
            $table->string('description');
            $table->string('reference_id')->nullable(); // Buat nyimpen ID Booking/Payment kalau otomatis
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cash_flows');
    }
};