<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->text('cancel_reason')->nullable()->after('status');
            $table->string('cancel_bank_name')->nullable()->after('cancel_reason');
            $table->string('cancel_account_number')->nullable()->after('cancel_bank_name');
            $table->string('cancel_account_holder')->nullable()->after('cancel_account_number');
            $table->timestamp('cancelled_at')->nullable()->after('cancel_account_holder');
            $table->string('refund_status')->nullable()->after('cancelled_at');
            $table->timestamp('refunded_at')->nullable()->after('refund_status');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'cancel_reason',
                'cancel_bank_name',
                'cancel_account_number',
                'cancel_account_holder',
                'cancelled_at',
                'refund_status',
                'refunded_at',
            ]);
        });
    }
};