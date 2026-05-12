<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('subscriptions')) {
            Schema::create('subscriptions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('venue_id')->constrained()->cascadeOnDelete();
                $table->string('type');
                $table->string('stripe_id')->unique();
                $table->string('stripe_status');
                $table->string('stripe_price')->nullable();
                $table->integer('quantity')->nullable();
                $table->timestamp('trial_ends_at')->nullable();
                $table->timestamp('ends_at')->nullable();
                $table->timestamps();

                $table->index(['venue_id', 'stripe_status']);
            });
        } else {
            if (! Schema::hasColumn('subscriptions', 'venue_id')) {
                Schema::table('subscriptions', function (Blueprint $table) {
                    $table->foreignId('venue_id')->nullable()->after('id')->constrained()->nullOnDelete();
                });
            }

            if (Schema::hasColumn('subscriptions', 'user_id')) {
                DB::table('subscriptions')
                    ->whereNull('venue_id')
                    ->orderBy('id')
                    ->chunkById(100, function ($subscriptions): void {
                        foreach ($subscriptions as $subscription) {
                            $venueId = DB::table('users')->where('id', $subscription->user_id)->value('venue_id');

                            if ($venueId) {
                                DB::table('subscriptions')
                                    ->where('id', $subscription->id)
                                    ->update(['venue_id' => $venueId]);
                            }
                        }
                    });
            }

            $indexes = collect(DB::select("PRAGMA index_list('subscriptions')"))
                ->pluck('name')
                ->all();

            if (! in_array('subscriptions_venue_id_stripe_status_index', $indexes, true)) {
                Schema::table('subscriptions', function (Blueprint $table) {
                    $table->index(['venue_id', 'stripe_status']);
                });
            }
        }

        if (! Schema::hasTable('subscription_items')) {
            Schema::create('subscription_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('subscription_id')->constrained()->cascadeOnDelete();
                $table->string('stripe_id')->unique();
                $table->string('stripe_product');
                $table->string('stripe_price');
                $table->integer('quantity')->nullable();
                $table->string('meter_event_name')->nullable();
                $table->string('meter_id')->nullable();
                $table->timestamps();

                $table->index(['subscription_id', 'stripe_price']);
            });
        } else {
            Schema::table('subscription_items', function (Blueprint $table) {
                if (! Schema::hasColumn('subscription_items', 'meter_event_name')) {
                    $table->string('meter_event_name')->nullable()->after('quantity');
                }

                if (! Schema::hasColumn('subscription_items', 'meter_id')) {
                    $table->string('meter_id')->nullable()->after('stripe_price');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_items');
        Schema::dropIfExists('subscriptions');
    }
};
