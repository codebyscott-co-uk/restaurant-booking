<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('bookings')
            ->whereNull('customer_manage_token')
            ->orderBy('id')
            ->each(function ($booking) {
                DB::table('bookings')
                    ->where('id', $booking->id)
                    ->update(['customer_manage_token' => Str::random(48)]);
            });
    }

    public function down(): void
    {
        DB::table('bookings')->update(['customer_manage_token' => null]);
    }
};
