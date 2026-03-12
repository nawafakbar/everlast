<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Kita set nullable() karena klien yang pilih paket "Wedding" doang nggak butuh isi ini
            $table->date('prewed_date')->nullable()->after('end_time');
            $table->time('prewed_start_time')->nullable()->after('prewed_date');
            $table->time('prewed_end_time')->nullable()->after('prewed_start_time');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['prewed_date', 'prewed_start_time', 'prewed_end_time']);
        });
    }
};