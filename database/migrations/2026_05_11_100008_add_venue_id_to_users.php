<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('venue_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });

        $venueId = DB::table('venues')->value('id');

        if ($venueId) {
            DB::table('users')->whereNull('venue_id')->update(['venue_id' => $venueId]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('venue_id');
        });
    }
};
