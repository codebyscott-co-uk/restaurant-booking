<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->boolean('is_vip')->default(false)->after('marketing_opt_in');
            $table->text('allergies')->nullable()->after('is_vip');
            $table->text('dietary_requirements')->nullable()->after('allergies');
            $table->text('preferences')->nullable()->after('dietary_requirements');
            $table->foreignId('favourite_dining_area_id')->nullable()->after('preferences')->constrained('dining_areas')->nullOnDelete();
            $table->foreignId('favourite_restaurant_table_id')->nullable()->after('favourite_dining_area_id')->constrained('restaurant_tables')->nullOnDelete();
            $table->index(['venue_id', 'phone']);
            $table->index(['venue_id', 'is_vip']);
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['venue_id', 'phone']);
            $table->dropIndex(['venue_id', 'is_vip']);
            $table->dropConstrainedForeignId('favourite_restaurant_table_id');
            $table->dropConstrainedForeignId('favourite_dining_area_id');
            $table->dropColumn([
                'is_vip',
                'allergies',
                'dietary_requirements',
                'preferences',
            ]);
        });
    }
};
