<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->foreignId('venue_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->index(['venue_id', 'email']);
        });

        DB::table('customers')
            ->whereNull('venue_id')
            ->orderBy('id')
            ->chunkById(100, function ($customers): void {
                foreach ($customers as $customer) {
                    $venueId = DB::table('bookings')
                        ->where('customer_id', $customer->id)
                        ->orderBy('id')
                        ->value('venue_id');

                    if ($venueId) {
                        DB::table('customers')
                            ->where('id', $customer->id)
                            ->update(['venue_id' => $venueId]);
                    }
                }
            });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['venue_id', 'email']);
            $table->dropConstrainedForeignId('venue_id');
        });
    }
};
