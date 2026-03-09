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
            // Nambahin kolom untuk metode bayar (midtrans, manual_transfer, manual_qris)
            $table->string('payment_method')->default('midtrans')->after('booking_id');
            
            // Nambahin kolom untuk nama file gambar struk
            $table->string('proof_image')->nullable()->after('status');
            
            // Nambahin kolom untuk catatan (misal: "Atas nama Budi")
            $table->text('notes')->nullable()->after('proof_image');
            
            // Nambahin kolom untuk token pop-up Midtrans
            $table->string('snap_token')->nullable()->after('midtrans_transaction_id');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Hapus kolom kalau di-rollback
            $table->dropColumn(['payment_method', 'proof_image', 'notes', 'snap_token']);
        });
    }
};
