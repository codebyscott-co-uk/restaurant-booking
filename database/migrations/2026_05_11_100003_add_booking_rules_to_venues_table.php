<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('venues', function (Blueprint $table) {
            $table->unsignedSmallInteger('minimum_lead_time_minutes')->default(60)->after('website_url');
            $table->unsignedSmallInteger('maximum_advance_booking_days')->default(60)->after('minimum_lead_time_minutes');
            $table->unsignedSmallInteger('maximum_party_size')->default(8)->after('maximum_advance_booking_days');
            $table->unsignedSmallInteger('maximum_covers_per_slot')->nullable()->after('maximum_party_size');
            $table->boolean('allow_joined_tables')->default(true)->after('maximum_covers_per_slot');
        });
    }

    public function down(): void
    {
        Schema::table('venues', function (Blueprint $table) {
            $table->dropColumn([
                'minimum_lead_time_minutes',
                'maximum_advance_booking_days',
                'maximum_party_size',
                'maximum_covers_per_slot',
                'allow_joined_tables',
            ]);
        });
    }
};

