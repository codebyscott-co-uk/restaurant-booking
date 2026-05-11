<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('customer_manage_token')->nullable()->unique()->after('booking_reference');
        });

        Schema::table('venues', function (Blueprint $table) {
            $table->unsignedSmallInteger('cancellation_notice_hours')->default(24)->after('allow_joined_tables');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('customer_manage_token');
        });

        Schema::table('venues', function (Blueprint $table) {
            $table->dropColumn('cancellation_notice_hours');
        });
    }
};
