<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('venues', function (Blueprint $table) {
            $table->string('stripe_id')->nullable()->index()->after('slug');
            $table->string('stripe_status')->nullable()->after('stripe_id');
            $table->string('pm_type')->nullable()->after('stripe_status');
            $table->string('pm_last_four', 4)->nullable()->after('pm_type');
            $table->string('stripe_price_id')->nullable()->after('pm_last_four');
            $table->string('stripe_price_name')->nullable()->after('stripe_price_id');
            $table->timestamp('stripe_current_period_start')->nullable()->after('stripe_price_name');
            $table->timestamp('stripe_current_period_end')->nullable()->after('stripe_current_period_start');
            $table->timestamp('trial_ends_at')->nullable()->after('stripe_current_period_end');
        });
    }

    public function down(): void
    {
        Schema::table('venues', function (Blueprint $table) {
            $table->dropColumn([
                'stripe_id',
                'stripe_status',
                'pm_type',
                'pm_last_four',
                'stripe_price_id',
                'stripe_price_name',
                'stripe_current_period_start',
                'stripe_current_period_end',
                'trial_ends_at',
            ]);
        });
    }
};
