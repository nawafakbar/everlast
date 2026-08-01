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
        Schema::table('payments', function (Blueprint $table) {
            $table->string('payment_method')->default('midtrans')->after('booking_id');
            
            $table->string('proof_image')->nullable()->after('status');
            
            $table->text('notes')->nullable()->after('proof_image');
            
            $table->string('snap_token')->nullable()->after('midtrans_transaction_id');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'proof_image', 'notes', 'snap_token']);
        });
    }
};
