<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->text('event_location_3')->nullable()->after('event_lng_2');
            $table->string('event_lat_3')->nullable()->after('event_location_3');
            $table->string('event_lng_3')->nullable()->after('event_lat_3');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['event_location_3', 'event_lat_3', 'event_lng_3']);
        });
    }
};